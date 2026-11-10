<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAccount;
use App\Models\Registration;
use App\Services\AdminAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class SettingsController extends Controller
{
    public function show()
    {
        return view('admin.settings', ['occupied' => Registration::whereIn('status', ['pending', 'paid'])->count()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'regular_price' => ['required', 'regex:/^\\d{1,7}(?:\\.\\d{1,2})?$/'],
            'promotion_price' => ['nullable', 'regex:/^\\d{1,7}(?:\\.\\d{1,2})?$/'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100000'],
            'contact' => ['nullable', 'string', 'max:180'],
            'enabled' => ['required', 'in:0,1'],
            'confirm_password' => ['required', 'string', 'max:256'],
            'confirm_code' => ['required', 'digits:6'],
        ]);
        $cents = function (string $price): int {
            [$whole, $fraction] = array_pad(explode('.', $price, 2), 2, '');

            return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
        };
        $regular = $cents($data['regular_price']);
        $promo = isset($data['promotion_price']) ? $cents($data['promotion_price']) : null;
        if ($regular < 1000 || ($promo !== null && ($promo < 1000 || $promo >= $regular))) {
            throw ValidationException::withMessages(['promotion_price' => 'La promoción debe ser menor al precio regular y ambos deben ser al menos $10 MXN.']);
        }
        if ((int) $data['capacity'] < Registration::whereIn('status', ['pending', 'paid'])->count()) {
            throw ValidationException::withMessages(['capacity' => 'El cupo no puede ser menor a las inscripciones pagadas o pendientes.']);
        }
        if ($data['enabled'] === '1' && (! config('registration.stripe_secret') || ! config('registration.stripe_webhook_secret'))) {
            throw ValidationException::withMessages(['enabled' => 'Configura Stripe y el webhook antes de abrir inscripciones.']);
        }
        $admin = $request->attributes->get('admin_account');
        if (! Hash::check($data['confirm_password'], $admin->password_hash)) {
            throw ValidationException::withMessages(['confirm_password' => 'No se pudo confirmar la identidad.']);
        }
        $step = (new Google2FA)->verifyKeyNewer($admin->totp_secret, $data['confirm_code'], $admin->last_totp_step ?? 0, 1);
        if ($step === false || ! AdminAccount::whereKey($admin->id)->where(function ($q) use ($step) {
            $q->whereNull('last_totp_step')->orWhere('last_totp_step', '<', $step);
        })->update(['last_totp_step' => $step])) {
            throw ValidationException::withMessages(['confirm_code' => 'No se pudo confirmar el código. Usa un código nuevo.']);
        }
        DB::transaction(function () use ($data, $regular, $promo, $admin) {
            $occupied = Registration::whereIn('status', ['pending', 'paid'])->count();
            if ((int) $data['capacity'] < $occupied) {
                throw ValidationException::withMessages(['capacity' => 'El cupo no puede ser menor a las inscripciones pagadas o pendientes.']);
            }
            $values = ['regular_amount' => (string) $regular, 'promotion_amount' => $promo === null ? null : (string) $promo, 'capacity' => (string) ((int) $data['capacity']), 'contact' => $data['contact'] ?? null, 'enabled' => $data['enabled']];
            foreach ($values as $key => $value) {
                DB::table('site_settings')->updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => now(), 'created_at' => now()]);
            }
            AdminAudit::record($admin->id, 'settings_updated', [
                'fields' => array_keys($values),
                'before' => [
                    'regular_amount' => config('registration.regular_amount'),
                    'promotion_amount' => config('registration.promotion_amount'),
                    'capacity' => config('registration.capacity'),
                    'enabled' => config('registration.enabled'),
                ],
                'after' => [
                    'regular_amount' => $regular,
                    'promotion_amount' => $promo,
                    'capacity' => (int) $data['capacity'],
                    'enabled' => $data['enabled'] === '1',
                ],
            ]);
        });

        return redirect()->route('admin.settings')->with('success', 'Configuración actualizada.');
    }
}
