<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginLog;
use App\Models\ColorChangeHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class UserActivityReportController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'is_admin']);
        
        return Inertia::render('Admin/UserActivityReport/Index', [
            'users' => $users,
        ]);
    }

    public function getData(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'activity_type' => 'nullable|in:logins,changes,all',
            'page' => 'nullable|integer|min:1',
        ]);

        $userId = $validated['user_id'] ?? null;
        $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? Carbon::now()->endOfMonth()->toDateString();
        $activityType = $validated['activity_type'] ?? 'all';
        $page = $validated['page'] ?? 1;
        $perPage = 25;

        $activities = [];

        if ($activityType === 'logins' || $activityType === 'all') {
            $loginQuery = LoginLog::with('user:id,name,email')
                ->whereBetween('logged_in_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

            if ($userId) {
                $loginQuery->where('user_id', $userId);
            }

            $logins = $loginQuery->get()->map(function ($log) {
                return [
                    'id' => 'login_' . $log->id,
                    'type' => 'login',
                    'user' => $log->user,
                    'datetime' => $log->logged_in_at,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'details' => 'Inicio de sesión',
                ];
            });

            $activities = array_merge($activities, $logins->toArray());
        }

        if ($activityType === 'changes' || $activityType === 'all') {
            $changesQuery = ColorChangeHistory::with([
                'user:id,name,email',
                'station:id,name',
                'attribute:id,name,station_id'
            ])->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

            if ($userId) {
                $changesQuery->where('user_id', $userId);
            }

            $changes = $changesQuery->get()->map(function ($change) {
                return [
                    'id' => 'change_' . $change->id,
                    'type' => 'color_change',
                    'user' => $change->user,
                    'datetime' => $change->created_at,
                    'station' => $change->station,
                    'attribute' => $change->attribute,
                    'previous_color' => $change->previous_color,
                    'new_color' => $change->new_color,
                    'comment' => $change->comment,
                    'details' => "Cambió {$change->attribute->name} de {$change->previous_color} a {$change->new_color}",
                ];
            });

            $activities = array_merge($activities, $changes->toArray());
        }

        usort($activities, function ($a, $b) {
            return strtotime($b['datetime']) - strtotime($a['datetime']);
        });

        $total = count($activities);
        $offset = ($page - 1) * $perPage;
        $paginatedActivities = array_slice($activities, $offset, $perPage);

        $stats = [
            'total_logins' => 0,
            'total_changes' => 0,
            'total_activities' => $total,
        ];

        foreach ($activities as $activity) {
            if ($activity['type'] === 'login') {
                $stats['total_logins']++;
            } elseif ($activity['type'] === 'color_change') {
                $stats['total_changes']++;
            }
        }

        return response()->json([
            'activities' => $paginatedActivities,
            'pagination' => [
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'per_page' => $perPage,
                'total' => $total,
            ],
            'stats' => $stats,
        ]);
    }

    public function getCalendarData(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $userId = $validated['user_id'] ?? null;
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        $loginQuery = LoginLog::whereBetween('logged_in_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $changesQuery = ColorChangeHistory::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($userId) {
            $loginQuery->where('user_id', $userId);
            $changesQuery->where('user_id', $userId);
        }

        $logins = $loginQuery->get()->groupBy(function ($log) {
            return Carbon::parse($log->logged_in_at)->format('Y-m-d');
        });

        $changes = $changesQuery->get()->groupBy(function ($change) {
            return Carbon::parse($change->created_at)->format('Y-m-d');
        });

        $events = [];

        foreach ($logins as $date => $logs) {
            $hasChanges = isset($changes[$date]);
            $events[] = [
                'date' => $date,
                'logins_count' => $logs->count(),
                'changes_count' => $hasChanges ? $changes[$date]->count() : 0,
                'has_logins' => true,
                'has_changes' => $hasChanges,
            ];
        }

        foreach ($changes as $date => $changeList) {
            if (!isset($logins[$date])) {
                $events[] = [
                    'date' => $date,
                    'logins_count' => 0,
                    'changes_count' => $changeList->count(),
                    'has_logins' => false,
                    'has_changes' => true,
                ];
            }
        }

        return response()->json([
            'events' => $events,
        ]);
    }

    public function getDayActivities(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $date = $validated['date'];
        $userId = $validated['user_id'] ?? null;

        $activities = [];

        $loginQuery = LoginLog::with('user:id,name,email')
            ->whereDate('logged_in_at', $date);

        if ($userId) {
            $loginQuery->where('user_id', $userId);
        }

        $logins = $loginQuery->get()->map(function ($log) {
            return [
                'type' => 'login',
                'user' => $log->user,
                'datetime' => $log->logged_in_at,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
            ];
        });

        $changesQuery = ColorChangeHistory::with([
            'user:id,name,email',
            'station:id,name',
            'attribute:id,name'
        ])->whereDate('created_at', $date);

        if ($userId) {
            $changesQuery->where('user_id', $userId);
        }

        $changes = $changesQuery->get()->map(function ($change) {
            return [
                'type' => 'color_change',
                'user' => $change->user,
                'datetime' => $change->created_at,
                'station' => $change->station,
                'attribute' => $change->attribute,
                'previous_color' => $change->previous_color,
                'new_color' => $change->new_color,
                'comment' => $change->comment,
            ];
        });

        $activities = array_merge($logins->toArray(), $changes->toArray());

        usort($activities, function ($a, $b) {
            return strtotime($b['datetime']) - strtotime($a['datetime']);
        });

        return response()->json([
            'activities' => $activities,
            'date' => $date,
        ]);
    }
}
