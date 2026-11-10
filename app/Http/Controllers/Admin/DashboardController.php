<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Services\AdminAudit;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('estado');
        if (! in_array($status, ['paid', 'pending', 'failed', 'expired'], true)) {
            $status = null;
        }
        $query = Registration::query()->orderByDesc('created_at');
        if ($status) {
            $query->where('status', $status);
        }
        $registrations = $query->paginate(25)->withQueryString();
        $counts = Registration::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $occupied = (int) ($counts['paid'] ?? 0) + (int) ($counts['pending'] ?? 0);
        AdminAudit::record($request->attributes->get('admin_account')->id, 'registrations_list_viewed', ['status' => $status]);

        return view('admin.dashboard', compact('registrations', 'counts', 'status', 'occupied'));
    }

    public function show(Request $request, Registration $registration)
    {
        AdminAudit::record($request->attributes->get('admin_account')->id, 'registration_viewed', ['registration_id' => $registration->id]);

        return view('admin.registration', compact('registration'));
    }
}
