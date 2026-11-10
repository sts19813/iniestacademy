<?php

namespace App\Console\Commands;

use App\Models\AdminAccount;
use App\Services\AdminAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class SetupAdmin extends Command
{
    protected $signature = 'admin:setup {username : Nombre de usuario de la primera cuenta}';

    protected $description = 'Crea la primera cuenta administradora desde la consola';

    public function handle(): int
    {
        if (AdminAccount::exists()) {
            $this->error('Ya existe una cuenta administradora.');

            return self::FAILURE;
        }
        $name = strtolower(trim($this->argument('username')));
        if (! preg_match('/^[a-z0-9._-]{3,64}$/', $name)) {
            $this->error('Usa entre 3 y 64 caracteres: letras minúsculas, números, punto, guion o guion bajo.');

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
        $this->newLine();
        $this->line('Añade esta clave a tu aplicación de autenticación:');
        $this->line($secret);
        $this->line('Nombre de la cuenta: Iniesta Academy / '.$name);
        $this->warn('Guarda esta clave de forma segura. No se volverá a mostrar.');
        $code = $this->secret('Código actual de 6 dígitos');
        $step = is_string($code) && preg_match('/^\d{6}$/', $code) ? $totp->verifyKeyNewer($secret, $code, 0, 1) : false;
        if ($step === false) {
            $this->error('Código incorrecto; no se creó la cuenta.');

            return self::FAILURE;
        }
        DB::transaction(function () use ($name, $password, $secret, $step) {
            $admin = AdminAccount::create(['username' => $name, 'password_hash' => Hash::make($password), 'totp_secret' => $secret, 'last_totp_step' => $step]);
            AdminAudit::record($admin->id, 'initial_admin_created');
        });
        $this->info('Cuenta creada. Para entrar, espera al siguiente código de tu autenticador.');

        return self::SUCCESS;
    }
}
