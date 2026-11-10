<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stripe\StripeClient;
use Throwable;

class RegistrationController extends Controller
{
    public function create()
    {
        abort_unless(config('registration.enabled'), 503);

        return view('registration');
    }

    public function store(Request $request)
    {
        abort_unless(config('registration.enabled') && config('registration.stripe_secret') && config('registration.stripe_webhook_secret'), 503);
        $data = $request->validate([
            'buyer_name' => ['required', 'string', 'max:120'],
            'buyer_email' => ['required', 'email', 'max:254'],
            'buyer_phone' => ['required', 'string', 'max:30', 'regex:/^[+0-9 ()-]+$/'],
            'participant_name' => ['required', 'string', 'max:120'],
            'participant_birth_date' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'buyer_city' => ['required', 'string', 'max:120'],
            'participant_position' => ['nullable', 'in:Portero,Defensa,Centrocampista,Delantero,Otra'],
            'football_years' => ['required', 'integer', 'min:0', 'max:100'],
            'birthplace' => ['required', 'string', 'max:120'],
            'uniform_size' => ['required', 'in:CH,M,G,EG'],
            'socks_size' => ['required', 'in:CH,M,G,EG'],
            'shirt_name' => ['nullable', 'string', 'max:40'],
            'privacy_consent' => ['accepted'],
            'price_quote' => ['required', 'integer', 'min:1'],
        ], ['privacy_consent.accepted' => 'Debes aceptar el aviso de privacidad para continuar.']);
        $amount = config('registration.promotion_amount') ?: config('registration.regular_amount');
        if ((int) $data['price_quote'] !== $amount) {
            throw ValidationException::withMessages(['price_quote' => 'El precio cambió. Revisa el importe actualizado antes de continuar.']);
        }
        $capacity = config('registration.capacity');
        abort_unless($amount >= 1000 && $capacity > 0 && (! $this->promotionInvalid()), 503);
        $stripe = new StripeClient(config('registration.stripe_secret'));
        // Release a reservation only after Stripe confirms its Checkout session expired.
        foreach (Registration::where('status', 'pending')->whereNotNull('stripe_session_id')->where('checkout_expires_at', '<', now())->limit(50)->get() as $pending) {
            try {
                $session = $stripe->checkout->sessions->retrieve($pending->stripe_session_id);
                if ($session->status === 'expired' && $session->payment_status === 'unpaid') {
                    Registration::whereKey($pending->id)->where('status', 'pending')->update(['status' => 'expired']);
                }
            } catch (Throwable $e) {
                Log::warning('Could not check expired Checkout session', ['registration_id' => $pending->id]);
            }
        }
        // SQLite serializes this conditional INSERT, so simultaneous requests cannot exceed capacity.
        $id = (string) Str::uuid();
        $now = now();
        $inserted = DB::affectingStatement("INSERT INTO registrations (id, buyer_name, buyer_email, buyer_phone, participant_name, participant_birth_date, participant_details, privacy_accepted_at, privacy_notice_hash, status, amount, currency, created_at, updated_at) SELECT ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ? WHERE (SELECT COUNT(*) FROM registrations WHERE status IN ('pending', 'paid')) < COALESCE((SELECT CAST(value AS INTEGER) FROM site_settings WHERE key = 'capacity'), ?)", [
            $id, Crypt::encryptString($data['buyer_name']), Crypt::encryptString($data['buyer_email']), Crypt::encryptString($data['buyer_phone']), Crypt::encryptString($data['participant_name']), Crypt::encryptString($data['participant_birth_date']), Crypt::encryptString(json_encode(collect($data)->only(['buyer_city', 'participant_position', 'football_years', 'birthplace', 'uniform_size', 'socks_size', 'shirt_name'])->all(), JSON_THROW_ON_ERROR)), $now, hash_file('sha256', resource_path('views/privacy.blade.php')), $amount, config('registration.currency'), $now, $now, $capacity,
        ]);
        if (! $inserted) {
            return back()->withInput()->withErrors(['payment' => 'Por el momento no hay lugares disponibles.']);
        }
        $registration = Registration::findOrFail($id);
        try {
            $checkout = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'customer_email' => $data['buyer_email'],
                'client_reference_id' => $registration->id,
                'metadata' => ['registration_id' => $registration->id],
                'expires_at' => time() + 1860,
                'line_items' => [[
                    'price_data' => ['currency' => $registration->currency, 'unit_amount' => $registration->amount, 'product_data' => ['name' => 'Inscripción Iniesta Academy Mérida — 20 al 24 de marzo de 2027']],
                    'quantity' => 1,
                ]],
                'success_url' => route('registration.return').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('registration.cancel'),
            ], ['idempotency_key' => 'iniesta-registration-'.$registration->id]);
            $registration->update(['stripe_session_id' => $checkout->id, 'checkout_expires_at' => date('Y-m-d H:i:s', $checkout->expires_at)]);

            return redirect()->away($checkout->url, 303);
        } catch (Throwable $e) {
            Log::error('Could not create Stripe Checkout session', ['registration_id' => $registration->id, 'exception_type' => get_class($e)]);

            return back()->withInput($request->except('_token'))->withErrors(['payment' => 'No fue posible iniciar el pago. Inténtalo de nuevo.']);
        }
    }

    private function promotionInvalid(): bool
    {
        $promo = config('registration.promotion_amount');

        return $promo !== null && ($promo < 1000 || $promo > config('registration.regular_amount'));
    }

    public function returned(Request $request)
    {
        return view('registration-result', ['cancelled' => false]);
    }

    public function cancelled()
    {
        return view('registration-result', ['cancelled' => true]);
    }
}
