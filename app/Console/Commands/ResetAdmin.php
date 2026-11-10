<?php

namespace App\Console\Commands;

use App\Models\AdminAccount;
use App\Services\AdminAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class ResetAdmin extends Command
{
    protected $signature = 'admin:reset {username : Cuenta administradora que se recuperará}';

    protected $description = 'Recupera una cuenta administradora y revoca sus sesiones';

    public function handle(): int
    {
        $admin = AdminAccount::where('username', strtolower(trim($this->argument('username'))))->first();
        if (! $admin) {
            $this->error('Cuenta no encontrada.');

            return self::FAILURE;
        }
        if (! $this->confirm('Se reemplazarán la contraseña y el autenticador, y se cerrarán las sesiones. ¿Continuar?', false)) {
            return self::FAILURE;
        }
        $password = $this->secret('Contraseña nueva (mínimo 14 caracteres)');
        if (! is_string($password) || mb_strlen($password) < 14 || mb_strlen($password) > 256) {
            $this->error('La contraseña debe tener entre 14 y 256 caracteres.');

            return self::FAILURE;
        }
        $confirmation = $this->secret('Repite la contraseña');
        if (! is_string($confirmation) || ! hash_equals($password, $confirmation)) {
            $this->error('Las contraseñas no coinciden.');

            return self::FAILURE;
        }
        $totp = new Google2FA;
        $secret = $totp->generateSecretKey(32);
        $this->line('Añade esta clave a tu aplicación de autenticación:');
        $this->line($secret);
        $this->warn('Guarda esta clave de forma segura. No se volverá a mostrar.');
        $code = $this->secret('Código actual de 6 dígitos');
        $step = is_string($code) && preg_match('/^\d{6}$/', $code) ? $totp->verifyKeyNewer($secret, $code, 0, 1) : false;
        if ($step === false) {
            $this->error('Código incorrecto; no se modificó la cuenta.');

            return self::FAILURE;
        }
        DB::transaction(function () use ($admin, $password, $secret, $step) {
            $admin->update(['password_hash' => Hash::make($password), 'totp_secret' => $secret, 'last_totp_step' => $step, 'session_version' => $admin->session_version + 1]);
            AdminAudit::record($admin->id, 'admin_recovered');
        });
        $this->info('Cuenta recuperada. Espera al siguiente código antes de iniciar sesión.');

        return self::SUCCESS;
    }
}
