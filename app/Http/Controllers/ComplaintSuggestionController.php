<?php

namespace App\Http\Controllers;

use App\Governorate;
use App\Models\ComplaintSuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComplaintSuggestionController extends Controller
{
    /**
     * عرض الصفحة الرئيسية للمقترحات والشكاوى
     */
    public function index()
    {
        return view('complaints-suggestions.index');
    }

    /**
     * عرض صفحة إرسال شكوى/مقترح
     */
    public function create()
    {
        $governorates = Governorate::all();

        return view('complaints-suggestions.create', compact('governorates'));
    }

    /**
     * حفظ شكوى/مقترح جديد
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:complaint,suggestion',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'governorate' => ['required', 'integer', function ($attribute, $value, $fail) {
                if (! Governorate::tryFrom($value)) {
                    $fail('يرجى اختيار محافظة صحيحة');
                }
            }],
            'generator_id' => 'nullable|exists:generators,id',
            'message' => 'required|string|min:10',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ], [
            'type.required' => 'يرجى اختيار نوع الطلب',
            'name.required' => 'يرجى إدخال الاسم',
            'phone.required' => 'يرجى إدخال رقم الهاتف',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'governorate.required' => 'يرجى اختيار المحافظة',
            'governorate.integer' => 'يرجى اختيار محافظة صحيحة',
            'generator_id.exists' => 'يرجى اختيار مولد صحيح',
            'message.required' => 'يرجى إدخال الرسالة',
            'message.min' => 'الرسالة يجب أن تكون على الأقل 10 أحرف',
            'image.image' => 'الملف المرفوع يجب أن يكون صورة',
            'image.mimes' => 'نوع الصورة يجب أن يكون: jpeg, png, jpg, gif',
            'image.max' => 'حجم الصورة يجب أن لا يتجاوز 5 ميجابايت',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('complaints-suggestions', 'public');
        }

        $complaintSuggestion = ComplaintSuggestion::create([
            'type' => $request->type,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'governorate' => Governorate::from($request->governorate),
            'generator_id' => $request->generator_id,
            'subject' => $request->message, // استخدام الرسالة كعنوان
            'message' => $request->message,
            'image' => $imagePath,
            'tracking_code' => ComplaintSuggestion::generateTrackingCode(),
            'status' => 'pending',
        ]);

        return redirect()->route('complaints-suggestions.track', ['code' => $complaintSuggestion->tracking_code])
            ->with('success', 'تم إرسال '.($request->type === 'complaint' ? 'الشكوى' : 'المقترح').' بنجاح. رمز التتبع: '.$complaintSuggestion->tracking_code);
    }

    /**
     * عرض صفحة متابعة الطلب
     */
    public function track(Request $request)
    {
        $code = $request->query('code');
        $complaintSuggestion = null;

        if ($code) {
            $complaintSuggestion = ComplaintSuggestion::with('generator')
                ->where('tracking_code', $code)
                ->first();
        }

        return view('complaints-suggestions.track', compact('complaintSuggestion', 'code'));
    }

    /**
     * الحصول على المولدات حسب المحافظة
     */
    public function getGeneratorsByLocation(Request $request)
    {
        $request->validate([
            'governorate' => ['required', 'integer', function ($attribute, $value, $fail) {
                if (! Governorate::tryFrom((int) $value)) {
                    $fail('يرجى اختيار محافظة صحيحة');
                }
            }],
        ]);

        $governorateValue = (int) $request->governorate;

        // البحث عن المولدات من خلال operators باستخدام join مباشرة
        // لتجنب مشاكل enum casting في whereHas
        $generators = Generator::join('operators', 'generators.operator_id', '=', 'operators.id')
            ->where('operators.governorate', $governorateValue)
            ->where('operators.status', 'active')
            ->where('generators.status', 'active')
            ->select('generators.id', 'generators.name', 'generators.generator_number', 'generators.operator_id', 'operators.name as operator_name')
            ->orderBy('generators.name')
            ->get();

        // تنسيق البيانات للعرض
        $formattedGenerators = $generators->map(function ($generator) {
            return [
                'id' => $generator->id,
                'name' => $generator->name.' ('.$generator->generator_number.') - '.($generator->operator_name ?? 'غير محدد'),
            ];
        });

        return response()->json($formattedGenerators);
    }

    /**
     * البحث عن طلب برمز التتبع
     */
    public function search(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ], [
            'code.required' => 'يرجى إدخال رمز التتبع',
        ]);

        $complaintSuggestion = ComplaintSuggestion::where('tracking_code', $request->code)->first();

        if (! $complaintSuggestion) {
            return back()->with('error', 'لم يتم العثور على طلب بهذا الرمز. يرجى التحقق من الرمز والمحاولة مرة أخرى.');
        }

        return redirect()->route('complaints-suggestions.track', ['code' => $request->code]);
    }
}
