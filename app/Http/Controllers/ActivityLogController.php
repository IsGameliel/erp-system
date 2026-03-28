<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->hasRole(User::ROLE_ADMIN), 403);

        $monitoringRoles = $this->monitoringRoles();

        $activityLogs = ActivityLog::query()
            ->with('user')
            ->whereIn('user_role', $monitoringRoles)
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))
            ->when($request->filled('module'), fn ($query) => $query->where('module', $request->string('module')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('description', 'like', "%{$search}%")
                        ->orWhere('module', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('activity-logs.index', [
            'activityLogs' => $activityLogs,
            'actions' => ActivityLog::query()
                ->whereIn('user_role', $monitoringRoles)
                ->distinct()
                ->orderBy('action')
                ->pluck('action'),
            'modules' => ActivityLog::query()
                ->whereIn('user_role', $monitoringRoles)
                ->distinct()
                ->orderBy('module')
                ->pluck('module'),
        ]);
    }

    private function monitoringRoles(): array
    {
        return [
            User::ROLE_ADMIN,
            User::ROLE_SALES_OFFICER,
            User::ROLE_PROCUREMENT_OFFICER,
        ];
    }
}
