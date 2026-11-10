<?php

namespace App\Console\Commands;

use App\Models\AdminAccount;
use App\Services\AdminAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IssueAdminSetupLink extends Command
{
    protected $signature = 'admin:activation-link {username : Usuario de la primera cuenta}';

    protected $description = 'Genera un enlace temporal y de un solo uso para crear la primera cuenta';

    public function handle(): int
    {
        if (AdminAccount::exists()) {
            $this->error('Ya existe una cuenta administradora.');

            return self::FAILURE;
        }
        $name = strtolower(trim($this->argument('username')));
        if (! preg_match('/^[a-z0-9._-]{3,64}$/', $name)) {
            $this->error('El nombre de usuario no es válido.');

            return self::FAILURE;
        }
        $token = bin2hex(random_bytes(32));
        DB::transaction(function () use ($name, $token) {
            DB::table('admin_setup_tokens')->whereNull('used_at')->update(['used_at' => now()]);
            DB::table('admin_setup_tokens')->insert(['token_hash' => hash('sha256', $token), 'username' => $name, 'expires_at' => now()->addMinutes(30), 'created_at' => now()]);
            AdminAudit::record(null, 'admin_setup_link_issued');
        });
        $this->line(rtrim(config('app.url'), '/').'/admin/activar#'.$token);

        return self::SUCCESS;
    }
}
