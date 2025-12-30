<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplianceSafety;
use App\Models\ComplaintSuggestion;
use App\Models\FuelEfficiency;
use App\Models\Generator;
use App\Models\MaintenanceRecord;
use App\Models\Notification;
use App\Models\OperationLog;
use App\Models\Operator;
use App\Models\User;
use App\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // التحقق من اكتمال بيانات المشغل
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator && ! $operator->isProfileComplete()) {
                return redirect()->route('admin.operators.profile')
                    ->with('warning', 'يرجى إكمال بيانات المشغل أولاً.');
            }
        }

        // تحديد النطاق حسب نوع المستخدم
        $operatorIds = $this->getOperatorIds($user);
        $generatorIds = $this->getGeneratorIds($user, $operatorIds);

        // إحصائيات عامة
        if ($user->isSuperAdmin()) {
            $stats = $this->getSuperAdminStats();
        } elseif ($user->isAdmin()) {
            $stats = $this->getAdminStats();
        } elseif ($user->isCompanyOwner()) {
            $stats = $this->getCompanyOwnerStats($user, $operatorIds, $generatorIds);
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $stats = $this->getEmployeeStats($user, $operatorIds, $generatorIds);
        } else {
            $stats = [];
        }

        // إحصائيات سجلات التشغيل
        $operationStats = $this->getOperationStats($operatorIds, $generatorIds);

        // بيانات المخططات (آخر 30 يوم)
        $chartData = $this->getChartData($operatorIds, $generatorIds);

        // بيانات المخطط الدائري (المولدات/المشغلين والطاقة المنتجة)
        $pieChartData = $this->getPieChartData($operatorIds, $generatorIds);

        // إحصائيات الصيانة
        $maintenanceStats = $this->getMaintenanceStats($generatorIds);

        // إحصائيات كفاءة الوقود
        $fuelStats = $this->getFuelStats($generatorIds);

        // إحصائيات الامتثال والسلامة
        $complianceStats = $this->getComplianceStats($operatorIds);

        // آخر المولدات المضافة
        $recentGenerators = Generator::with('operator')
            ->when($user->isCompanyOwner(), function ($query) use ($user) {
                $query->whereIn('operator_id', $user->ownedOperators->pluck('id'));
            })
            ->when($user->isEmployee() || $user->isTechnician(), function ($query) use ($user) {
                $query->whereIn('operator_id', $user->operators->pluck('id'));
            })
            ->latest()
            ->limit(5)
            ->get();

        // آخر المشغلين المضافة (Super Admin والأدمن)
        $recentOperators = ($user->isSuperAdmin() || $user->isAdmin())
            ? Operator::with('owner')->latest()->limit(5)->get()
            : collect();

        // آخر سجلات التشغيل
        $recentOperationLogs = OperationLog::with(['generator', 'operator'])
            ->when($operatorIds, fn($q) => $q->whereIn('operator_id', $operatorIds))
            ->when($generatorIds, fn($q) => $q->whereIn('generator_id', $generatorIds))
            ->latest('operation_date')
            ->latest('created_at')
            ->limit(5)
            ->get();

        // المولدات التي تحتاج صيانة
        $generatorsNeedingMaintenance = Generator::with('operator')
            ->when($operatorIds, fn($q) => $q->whereIn('operator_id', $operatorIds))
            ->where(function ($query) {
                $query->whereNull('last_major_maintenance_date')
                    ->orWhere('last_major_maintenance_date', '<', Carbon::now()->subMonths(6));
            })
            ->limit(5)
            ->get();

        // إحصائيات الشكاوى والمقترحات
        $complaintsStats = $this->getComplaintsStats($operatorIds, $generatorIds);

        // الشكاوى والمقترحات غير المرد عليها
        $unansweredComplaints = $this->getUnansweredComplaints($operatorIds, $generatorIds);

        // الشهادات المنتهية أو قريبة من الانتهاء
        $expiringCompliance = $this->getExpiringCompliance($operatorIds);

        $this->createNotifications($user, $operatorIds, $generatorIds, $generatorsNeedingMaintenance, $unansweredComplaints, $expiringCompliance);

        return view('admin.dashboard', compact(
            'stats',
            'operationStats',
            'chartData',
            'pieChartData',
            'maintenanceStats',
            'fuelStats',
            'complianceStats',
            'recentGenerators',
            'recentOperators',
            'recentOperationLogs',
            'generatorsNeedingMaintenance',
            'complaintsStats',
            'unansweredComplaints',
            'expiringCompliance'
        ));
    }

    private function getOperatorIds($user): ?array
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return null; // جميع المشغلين
        } elseif ($user->isCompanyOwner()) {
            return $user->ownedOperators->pluck('id')->toArray();
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            return $user->operators->pluck('id')->toArray();
        }
        return [];
    }

    private function getGeneratorIds($user, ?array $operatorIds): ?array
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return null; // جميع المولدات
        }

        if ($operatorIds) {
            return Generator::whereIn('operator_id', $operatorIds)->pluck('id')->toArray();
        }

        return [];
    }

    private function getSuperAdminStats(): array
    {
        return [
            'users' => [
                'total' => User::count(),
                'super_admins' => User::where('role', Role::SuperAdmin)->count(),
                'company_owners' => User::where('role', Role::CompanyOwner)->count(),
                'employees' => User::where('role', Role::Employee)->count(),
            ],
            'operators' => [
                'total' => Operator::count(),
                'active' => Operator::where('status', 'active')->count(),
            ],
            'generators' => [
                'total' => Generator::count(),
                'active' => Generator::where('status', 'active')->count(),
            ],
        ];
    }

    private function getAdminStats(): array
    {
        return [
            'operators' => [
                'total' => Operator::count(),
                'active' => Operator::where('status', 'active')->count(),
            ],
            'generators' => [
                'total' => Generator::count(),
                'active' => Generator::where('status', 'active')->count(),
            ],
            'company_owners' => [
                'total' => User::where('role', Role::CompanyOwner)->count(),
            ],
        ];
    }

    private function getCompanyOwnerStats($user, ?array $operatorIds, ?array $generatorIds): array
    {
        $ownedOperators = $user->ownedOperators;
        
        return [
            'operators' => [
                'total' => $ownedOperators->count(),
                'active' => $ownedOperators->where('status', 'active')->count(),
            ],
            'generators' => [
                'total' => Generator::whereIn('operator_id', $ownedOperators->pluck('id'))->count(),
                'active' => Generator::whereIn('operator_id', $ownedOperators->pluck('id'))
                    ->where('status', 'active')->count(),
            ],
            'employees' => [
                'total' => User::whereHas('operators', function ($query) use ($ownedOperators) {
                    $query->whereIn('operators.id', $ownedOperators->pluck('id'));
                })->where('role', Role::Employee)->distinct()->count(),
            ],
        ];
    }

    private function getEmployeeStats($user, ?array $operatorIds, ?array $generatorIds): array
    {
        $userOperators = $user->operators;
        
        return [
            'operators' => [
                'total' => $userOperators->count(),
            ],
            'generators' => [
                'total' => Generator::whereIn('operator_id', $userOperators->pluck('id'))->count(),
                'active' => Generator::whereIn('operator_id', $userOperators->pluck('id'))
                    ->where('status', 'active')->count(),
            ],
        ];
    }

    private function getOperationStats(?array $operatorIds, ?array $generatorIds): array
    {
        $baseQuery = function() use ($operatorIds, $generatorIds) {
            $query = OperationLog::query();
            if ($operatorIds) {
                $query->whereIn('operator_id', $operatorIds);
            }
            if ($generatorIds) {
                $query->whereIn('generator_id', $generatorIds);
            }
            return $query;
        };

        $totalLogs = $baseQuery()->count();
        $thisMonth = (clone $baseQuery())->whereMonth('operation_date', Carbon::now()->month)
            ->whereYear('operation_date', Carbon::now()->year)->count();
        $thisWeek = (clone $baseQuery())->whereBetween('operation_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();

        $totalEnergy = (clone $baseQuery())->sum('energy_produced') ?? 0;
        $totalFuel = (clone $baseQuery())->sum('fuel_consumed') ?? 0;
        $avgLoad = (clone $baseQuery())->avg('load_percentage') ?? 0;

        return [
            'total' => $totalLogs,
            'this_month' => $thisMonth,
            'this_week' => $thisWeek,
            'total_energy' => round($totalEnergy, 2),
            'total_fuel' => round($totalFuel, 2),
            'avg_load' => round($avgLoad, 2),
        ];
    }

    private function getChartData(?array $operatorIds, ?array $generatorIds): array
    {
        $baseQuery = function() use ($operatorIds, $generatorIds) {
            $query = OperationLog::query();
            if ($operatorIds) {
                $query->whereIn('operator_id', $operatorIds);
            }
            if ($generatorIds) {
                $query->whereIn('generator_id', $generatorIds);
            }
            return $query;
        };

        // الحصول على بيانات آخر 30 يوم
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        $query = (clone $baseQuery())
            ->whereBetween('operation_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(operation_date) as date'),
                DB::raw('SUM(energy_produced) as total_energy'),
                DB::raw('SUM(fuel_consumed) as total_fuel'),
                DB::raw('COUNT(*) as records_count'),
                DB::raw('AVG(load_percentage) as avg_load')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // إنشاء مصفوفة بجميع الأيام (آخر 30 يوم)
        $dates = [];
        $energyData = [];
        $fuelData = [];
        $recordsData = [];
        $loadData = [];

        // تحويل نتائج الاستعلام إلى مصفوفة مفهرسة بالتاريخ
        $dataByDate = [];
        foreach ($query as $row) {
            $dataByDate[$row->date] = $row;
        }

        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = Carbon::now()->subDays($i)->format('d/m');
            
            $dayData = $dataByDate[$date] ?? null;
            
            $energyData[] = $dayData ? round((float)$dayData->total_energy, 2) : 0;
            $fuelData[] = $dayData ? round((float)$dayData->total_fuel, 2) : 0;
            $recordsData[] = $dayData ? (int)$dayData->records_count : 0;
            $loadData[] = $dayData ? round((float)$dayData->avg_load, 2) : 0;
        }

        return [
            'labels' => $dates,
            'energy' => $energyData,
            'fuel' => $fuelData,
            'records' => $recordsData,
            'load' => $loadData,
        ];
    }

    private function getPieChartData(?array $operatorIds, ?array $generatorIds): array
    {
        $baseQuery = function() use ($operatorIds, $generatorIds) {
            $query = OperationLog::query();
            if ($operatorIds) {
                $query->whereIn('operator_id', $operatorIds);
            }
            if ($generatorIds) {
                $query->whereIn('generator_id', $generatorIds);
            }
            return $query;
        };

        // بيانات المولدات مع الطاقة المنتجة والوقود المستهلك
        $generatorsData = (clone $baseQuery())
            ->join('generators', 'operation_logs.generator_id', '=', 'generators.id')
            ->leftJoin('fuel_tanks', 'generators.id', '=', 'fuel_tanks.generator_id')
            ->select(
                'generators.id',
                'generators.name',
                DB::raw('SUM(operation_logs.energy_produced) as total_energy'),
                DB::raw('SUM(operation_logs.fuel_consumed) as total_fuel_consumed'),
                DB::raw('COALESCE(SUM(fuel_tanks.capacity), 0) as total_tank_capacity')
            )
            ->whereNotNull('operation_logs.energy_produced')
            ->groupBy('generators.id', 'generators.name')
            ->orderByDesc('total_energy')
            ->limit(10)
            ->get()
            ->map(function($item) {
                $totalCapacity = (float)$item->total_tank_capacity;
                $consumed = (float)$item->total_fuel_consumed;
                $fuelSurplus = max(0, $totalCapacity - $consumed);
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'energy' => round((float)$item->total_energy, 2),
                    'fuel_consumed' => round($consumed, 2),
                    'fuel_capacity' => round($totalCapacity, 2),
                    'fuel_surplus' => round($fuelSurplus, 2),
                ];
            });

        // بيانات المشغلين مع الطاقة المنتجة والوقود المستهلك والفائض
        $operatorsData = (clone $baseQuery())
            ->join('operators', 'operation_logs.operator_id', '=', 'operators.id')
            ->join('generators', 'operation_logs.generator_id', '=', 'generators.id')
            ->leftJoin('fuel_tanks', 'generators.id', '=', 'fuel_tanks.generator_id')
            ->select(
                'operators.id',
                'operators.name',
                DB::raw('SUM(operation_logs.energy_produced) as total_energy'),
                DB::raw('SUM(operation_logs.fuel_consumed) as total_fuel_consumed'),
                DB::raw('COALESCE(SUM(fuel_tanks.capacity), 0) as total_tank_capacity')
            )
            ->whereNotNull('operation_logs.energy_produced')
            ->groupBy('operators.id', 'operators.name')
            ->orderByDesc('total_energy')
            ->limit(10)
            ->get()
            ->map(function($item) {
                $totalCapacity = (float)$item->total_tank_capacity;
                $consumed = (float)$item->total_fuel_consumed;
                $fuelSurplus = max(0, $totalCapacity - $consumed);
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'energy' => round((float)$item->total_energy, 2),
                    'fuel_consumed' => round($consumed, 2),
                    'fuel_capacity' => round($totalCapacity, 2),
                    'fuel_surplus' => round($fuelSurplus, 2),
                ];
            });

        // بيانات المحافظات مع الطاقة المنتجة والوقود المستهلك والفائض
        $governoratesData = (clone $baseQuery())
            ->join('operators', 'operation_logs.operator_id', '=', 'operators.id')
            ->join('generators', 'operation_logs.generator_id', '=', 'generators.id')
            ->leftJoin('fuel_tanks', 'generators.id', '=', 'fuel_tanks.generator_id')
            ->select(
                'operators.governorate',
                DB::raw('SUM(operation_logs.energy_produced) as total_energy'),
                DB::raw('SUM(operation_logs.fuel_consumed) as total_fuel_consumed'),
                DB::raw('COALESCE(SUM(fuel_tanks.capacity), 0) as total_tank_capacity')
            )
            ->whereNotNull('operation_logs.energy_produced')
            ->whereNotNull('operators.governorate')
            ->groupBy('operators.governorate')
            ->orderByDesc('total_energy')
            ->get()
            ->map(function($item) {
                $governorate = \App\Governorate::fromValue((int)$item->governorate);
                $totalCapacity = (float)$item->total_tank_capacity;
                $consumed = (float)$item->total_fuel_consumed;
                $fuelSurplus = max(0, $totalCapacity - $consumed);
                
                return [
                    'id' => $item->governorate,
                    'name' => $governorate ? $governorate->label() : 'غير محدد',
                    'code' => $governorate ? $governorate->code() : '',
                    'energy' => round((float)$item->total_energy, 2),
                    'fuel_consumed' => round($consumed, 2),
                    'fuel_capacity' => round($totalCapacity, 2),
                    'fuel_surplus' => round($fuelSurplus, 2),
                ];
            });

        return [
            'generators' => [
                'labels' => $generatorsData->pluck('name')->toArray(),
                'data' => $generatorsData->pluck('energy')->toArray(),
                'details' => $generatorsData->toArray(),
            ],
            'operators' => [
                'labels' => $operatorsData->pluck('name')->toArray(),
                'data' => $operatorsData->pluck('energy')->toArray(),
                'details' => $operatorsData->toArray(),
            ],
            'governorates' => [
                'labels' => $governoratesData->pluck('name')->toArray(),
                'data' => $governoratesData->pluck('energy')->toArray(),
                'details' => $governoratesData->toArray(),
            ],
        ];
    }

    private function getMaintenanceStats(?array $generatorIds): array
    {
        $baseQuery = function() use ($generatorIds) {
            $query = MaintenanceRecord::query();
            if ($generatorIds) {
                $query->whereIn('generator_id', $generatorIds);
            }
            return $query;
        };

        $total = $baseQuery()->count();
        $thisMonth = (clone $baseQuery())->whereMonth('maintenance_date', Carbon::now()->month)
            ->whereYear('maintenance_date', Carbon::now()->year)->count();
        $totalCost = (clone $baseQuery())->sum('maintenance_cost') ?? 0;
        $totalDowntime = (clone $baseQuery())->sum('downtime_hours') ?? 0;

        return [
            'total' => $total,
            'this_month' => $thisMonth,
            'total_cost' => round($totalCost, 2),
            'total_downtime' => round($totalDowntime, 2),
        ];
    }

    private function getFuelStats(?array $generatorIds): array
    {
        $baseQuery = function() use ($generatorIds) {
            $query = FuelEfficiency::query();
            if ($generatorIds) {
                $query->whereIn('generator_id', $generatorIds);
            }
            return $query;
        };

        $total = $baseQuery()->count();
        $avgEfficiency = (clone $baseQuery())->avg('fuel_efficiency_percentage') ?? 0;
        $avgEnergyEfficiency = (clone $baseQuery())->avg('energy_distribution_efficiency') ?? 0;
        $totalCost = (clone $baseQuery())->sum('total_operating_cost') ?? 0;

        return [
            'total' => $total,
            'avg_fuel_efficiency' => round($avgEfficiency, 2),
            'avg_energy_efficiency' => round($avgEnergyEfficiency, 2),
            'total_cost' => round($totalCost, 2),
        ];
    }

    private function getComplianceStats(?array $operatorIds): array
    {
        $baseQuery = function() use ($operatorIds) {
            $query = ComplianceSafety::query();
            if ($operatorIds) {
                $query->whereIn('operator_id', $operatorIds);
            }
            return $query;
        };

        $total = $baseQuery()->count();
        $valid = (clone $baseQuery())->where('safety_certificate_status', 'valid')->count();
        $expired = (clone $baseQuery())->where('safety_certificate_status', 'expired')->count();
        $pending = (clone $baseQuery())->where('safety_certificate_status', 'pending')->count();

        return [
            'total' => $total,
            'valid' => $valid,
            'expired' => $expired,
            'pending' => $pending,
        ];
    }

    private function getComplaintsStats(?array $operatorIds, ?array $generatorIds): array
    {
        $baseQuery = function() use ($operatorIds, $generatorIds) {
            $query = ComplaintSuggestion::query();
            if ($operatorIds) {
                $query->whereHas('generator', function ($q) use ($operatorIds) {
                    $q->whereIn('operator_id', $operatorIds);
                });
            }
            if ($generatorIds) {
                $query->whereIn('generator_id', $generatorIds);
            }
            return $query;
        };

        $total = $baseQuery()->count();
        $pending = (clone $baseQuery())->where('status', 'pending')->count();
        $complaints = (clone $baseQuery())->where('type', 'complaint')->count();
        $suggestions = (clone $baseQuery())->where('type', 'suggestion')->count();
        $unanswered = (clone $baseQuery())->whereNull('response')->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'complaints' => $complaints,
            'suggestions' => $suggestions,
            'unanswered' => $unanswered,
        ];
    }

    private function getUnansweredComplaints(?array $operatorIds, ?array $generatorIds)
    {
        return ComplaintSuggestion::with(['generator.operator'])
            ->when($operatorIds, function ($query) use ($operatorIds) {
                $query->whereHas('generator', function ($q) use ($operatorIds) {
                    $q->whereIn('operator_id', $operatorIds);
                });
            })
            ->when($generatorIds, fn($q) => $q->whereIn('generator_id', $generatorIds))
            ->whereNull('response')
            ->latest()
            ->limit(5)
            ->get();
    }

    private function getExpiringCompliance(?array $operatorIds)
    {
        // الحصول على الشهادات المنتهية أو التي تحتاج متابعة
        $query = ComplianceSafety::with('operator');
        
        if ($operatorIds) {
            $query->whereIn('operator_id', $operatorIds);
        }
        
        return $query->where(function ($q) {
                // الشهادات المنتهية
                $q->where('safety_certificate_status', 'expired')
                    // أو الشهادات السارية التي لم يتم فحصها منذ أكثر من 6 أشهر
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('safety_certificate_status', 'valid')
                            ->where(function ($dateQuery) {
                                $dateQuery->whereNull('last_inspection_date')
                                    ->orWhere('last_inspection_date', '<', Carbon::now()->subMonths(6));
                            });
                    });
            })
            ->orderByRaw("CASE WHEN safety_certificate_status = 'expired' THEN 0 ELSE 1 END")
            ->latest('last_inspection_date')
            ->limit(5)
            ->get();
    }

    /**
     * Create notifications based on dashboard data and user role
     */
    private function createNotifications($user, ?array $operatorIds, ?array $generatorIds, $generatorsNeedingMaintenance, $unansweredComplaints, $expiringCompliance): void
    {
        if ($generatorsNeedingMaintenance->count() > 0) {
            $count = $generatorsNeedingMaintenance->count();
            $firstGeneratorId = $generatorsNeedingMaintenance->first()->id;
            $this->createOrUpdateNotification(
                $user->id,
                'maintenance_needed',
                'مولدات تحتاج صيانة',
                "يوجد {$count} مولد يحتاج إلى صيانة فورية",
                route('admin.maintenance-records.create', ['generator_id' => $firstGeneratorId])
            );
        }

        if ($unansweredComplaints->count() > 0) {
            $count = $unansweredComplaints->count();
            $this->createOrUpdateNotification(
                $user->id,
                'complaint_unanswered',
                'شكاوى ومقترحات غير م responded عليها',
                "يوجد {$count} طلب يحتاج إلى رد",
                route('admin.complaints-suggestions.index')
            );
        }

        if ($expiringCompliance->count() > 0) {
            $count = $expiringCompliance->count();
            $this->createOrUpdateNotification(
                $user->id,
                'compliance_expiring',
                'شهادات منتهية أو قريبة من الانتهاء',
                "يوجد {$count} شهادة تحتاج إلى متابعة",
                route('admin.compliance-safeties.index')
            );
        }
    }

    /**
     * Create or update notification to avoid duplicates of same type
     */
    private function createOrUpdateNotification(int $userId, string $type, string $title, string $message, ?string $link = null): void
    {
        $existing = Notification::where('user_id', $userId)
            ->where('type', $type)
            ->where('read', false)
            ->first();

        if ($existing) {
            $existing->update([
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'created_at' => now(),
            ]);
        } else {
            Notification::createNotification($userId, $type, $title, $message, $link);
        }
    }
}
