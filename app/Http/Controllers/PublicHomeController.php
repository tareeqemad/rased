<?php

namespace App\Http\Controllers;

use App\Helpers\ConstantsHelper;
use App\Models\Generator;
use App\Models\Operator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    /**
     * Display main homepage for public visitors with statistics
     */
    public function index(): View
    {
        // Get statistics for homepage
        $stats = [
            'total_operators' => Operator::where('status', 'active')->count(),
            'total_generators' => \App\Models\Generator::count(),
            'total_capacity' => Operator::where('status', 'active')->sum('total_capacity') ?? 0,
            'governorates' => ConstantsHelper::getByName('المحافظة'),
        ];

        return view('front.index', compact('stats'));
    }

    /**
     * الحصول على المشغلين حسب المحافظة مع الإحداثيات للخريطة
     */
    public function getOperatorsForMap(Request $request): JsonResponse
    {
        $request->validate([
            'governorate' => 'nullable|string',
        ]);

        $governorateParam = $request->input('governorate');

        // جلب المشغلين النشطين فقط مع الإحداثيات
        $query = Operator::where('status', 'active')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // إذا كانت القيمة "all" أو فارغة، نجلب جميع المشغلين
        if ($governorateParam === 'all' || $governorateParam === '' || $governorateParam === null) {
            // نجلب جميع المشغلين
        } else {
            // التحقق من صحة رقم المحافظة
            $governorate = (int) $governorateParam;
            if (!\App\Governorate::tryFrom($governorate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'رقم المحافظة غير صحيح',
                ], 400);
            }
            $query->where('governorate', $governorate);
        }

        $operators = $query->select(
                'id', 
                'name', 
                'city', 
                'unit_number', 
                'unit_name',
                'latitude', 
                'longitude', 
                'phone', 
                'phone_alt',
                'address',
                'detailed_address',
                'governorate',
                'total_capacity',
                'generators_count',
                'synchronization_available',
                'max_synchronization_capacity',
                'owner_name',
                'operation_entity'
            )
            ->orderBy('governorate')
            ->orderBy('name')
            ->get()
            ->map(function ($operator) {
                // تحسين الأداء بتقليل البيانات المرسلة
                return [
                    'id' => $operator->id,
                    'name' => $operator->name,
                    'city' => $operator->city,
                    'unit_number' => $operator->unit_number,
                    'unit_name' => $operator->unit_name,
                    'latitude' => (float) $operator->latitude,
                    'longitude' => (float) $operator->longitude,
                    'phone' => $operator->phone,
                    'phone_alt' => $operator->phone_alt,
                    'address' => $operator->address,
                    'detailed_address' => $operator->detailed_address,
                    'governorate' => $operator->governorate?->label() ?? 'غير محدد',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $operators,
        ])->header('Cache-Control', 'public, max-age=300');
    }

    /**
     * Display operators map page
     */
    public function map(): View
    {
        $governorates = ConstantsHelper::getByName('المحافظة');
        return view('front.map', compact('governorates'));
    }

    /**
     * Display statistics page
     */
    public function stats(): View
    {
        $stats = [
            'total_operators' => Operator::where('status', 'active')->count(),
            'total_generators' => Generator::count(),
            'active_generators' => Generator::where('status', 'active')->count(),
            'total_capacity' => Operator::where('status', 'active')->sum('total_capacity') ?? 0,
            'operators_by_governorate' => Operator::where('status', 'active')
                ->selectRaw('governorate, COUNT(*) as count')
                ->groupBy('governorate')
                ->get()
                ->mapWithKeys(function ($item) {
                    // الحصول على القيمة الرقمية من enum إذا كان enum object
                    $governorateValue = $item->governorate instanceof \App\Governorate 
                        ? $item->governorate->value 
                        : (int) $item->governorate;
                    
                    $governorate = \App\Governorate::tryFrom($governorateValue);
                    $govLabel = $governorate?->label() ?? 'غير محدد';
                    
                    return [$govLabel => $item->count];
                }),
        ];

        return view('front.stats', compact('stats'));
    }

    /**
     * Display about page
     */
    public function about(): View
    {
        return view('front.about');
    }
}

