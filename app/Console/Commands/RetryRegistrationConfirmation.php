<?php

namespace App\Console\Commands;

use App\Models\Registration;
use App\Services\AdminAudit;
use App\Services\RegistrationConfirmationSender;
use Illuminate\Console\Command;

class RetryRegistrationConfirmation extends Command
{
    protected $signature = 'registrations:retry-confirmation {reference : Referencia de la inscripción}';

    protected $description = 'Reintenta un correo de confirmación pendiente';

    public function handle(RegistrationConfirmationSender $sender): int
    {
        $registration = Registration::find($this->argument('reference'));
        if (! $registration || $registration->status !== 'paid' || ! in_array($registration->confirmation_email_status, ['pending', 'sending'], true)) {
            $this->error('No existe una confirmación pendiente para esta referencia.');

            return self::FAILURE;
        }
        AdminAudit::record(null, 'registration_confirmation_retry', ['registration_id' => $registration->id]);
        if (! $sender->send($registration)) {
            $this->error('El mensaje sigue pendiente o ya se está enviando. Revisa la configuración y los registros del servidor.');

            return self::FAILURE;
        }
        $this->info('El servidor de correo aceptó la confirmación.');

        return self::SUCCESS;
    }
}
