<?php

namespace App\Console\Commands;

use App\Models\Registration;
use Illuminate\Console\Command;

class ListRegistrations extends Command
{
    protected $signature = 'registrations:list {--details : Mostrar datos personales}';

    protected $description = 'Consulta las inscripciones y su estado de pago';

    public function handle(): int
    {
        $details = $this->option('details');
        $headers = ['Referencia', 'Estado', 'Monto MXN', 'Fecha'];
        if ($details) {
            $headers = array_merge($headers, ['Comprador', 'Correo', 'Teléfono', 'Participante', 'Nacimiento']);
        }
        $rows = Registration::orderBy('created_at')->get()->map(function (Registration $r) use ($details) {
            $row = [$r->id, $r->status, number_format($r->amount / 100, 2), $r->created_at];

            return $details ? array_merge($row, [$r->buyer_name, $r->buyer_email, $r->buyer_phone, $r->participant_name, $r->participant_birth_date]) : $row;
        });
        $this->table($headers, $rows->all());

        return self::SUCCESS;
    }
}
