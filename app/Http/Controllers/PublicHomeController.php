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
            'governorates' => ConstantsHelper::get(1), // رقم ثابت المحافظات
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

        // جلب وحدات التوليد النشطة مع الإحداثيات والمشغلين
        $query = \App\Models\GenerationUnit::with(['operator', 'cityDetail', 'governorateDetail'])
            ->whereHas('operator', function($q) {
                $q->where('status', 'active');
            })
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // إذا كانت القيمة "all" أو فارغة، نجلب جميع وحدات التوليد
        if ($governorateParam === 'all' || $governorateParam === '' || $governorateParam === null) {
            // نجلب جميع وحدات التوليد
        } else {
            // التحقق من صحة رقم المحافظة
            $governorate = (int) $governorateParam;
            if (!\App\Governorate::tryFrom($governorate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'رقم المحافظة غير صحيح',
                ], 400);
            }
            
            // البحث عن ID المحافظة في constant_details
            $governorateDetail = \App\Helpers\ConstantsHelper::get(1)
                ->where('value', (string) $governorate)
                ->first();
            
            if ($governorateDetail) {
                $query->where('governorate_id', $governorateDetail->id);
            } else {
                // إذا لم نجد المحافظة في الثوابت، نرجع مصفوفة فارغة
                return response()->json([
                    'success' => true,
                    'data' => [],
                ]);
            }
        }

        $generationUnits = $query->select(
                'id',
                'operator_id',
                'name',
                'unit_code',
                'unit_number',
                'city_id',
                'governorate_id',
                'latitude', 
                'longitude', 
                'phone', 
                'phone_alt',
                'detailed_address',
                'total_capacity',
            )
            ->orderBy('governorate_id')
            ->orderBy('name')
            ->get()
            ->map(function ($unit) {
                $operator = $unit->operator;
                $governorateDetail = $unit->governorateDetail;
                
                // الحصول على اسم المحافظة من الثوابت أو من enum
                $governorateLabel = 'غير محدد';
                if ($governorateDetail) {
                    $governorateLabel = $governorateDetail->label;
                } elseif ($operator && $operator->governorate) {
                    $governorateLabel = $operator->governorate->label();
                }
                
                return [
                    'id' => $unit->operator_id, // استخدام operator_id للتوافق مع الكود السابق
                    'unit_id' => $unit->id, // إضافة unit_id للتمييز
                    'name' => $operator ? $operator->name : ($unit->name ?? 'غير محدد'),
                    'unit_name' => $unit->name,
                    'unit_code' => $unit->unit_code,
                    'unit_number' => $unit->unit_number,
                    'city' => $unit->getCityName(),
                    'latitude' => (float) $unit->latitude,
                    'longitude' => (float) $unit->longitude,
                    'phone' => $unit->phone ?? ($operator ? $operator->phone : null),
                    'phone_alt' => $unit->phone_alt ?? ($operator ? $operator->phone_alt : null),
                    'address' => $unit->detailed_address ?? ($operator ? $operator->address : null),
                    'detailed_address' => $unit->detailed_address,
                    'governorate' => $governorateLabel,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $generationUnits,
        ])->header('Cache-Control', 'public, max-age=300');
    }

    /**
     * Display operators map page
     */
    public function map(): View
    {
        $governorates = ConstantsHelper::get(1); // رقم ثابت المحافظات
        return view('public.home', compact('governorates'));
    }

    /**
     * Display statistics page
     */
    public function stats(): View
    {
        $stats = [
            'total_operators' => Operator::where('status', 'active')->count(),
            'total_generators' => Generator::count(),
            'active_generators' => Generator::whereHas('statusDetail', function($q) {
                $q->where('code', 'ACTIVE');
            })->count(),
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

    /**
     * Display join request page
     */
    public function join(): View
    {
        return view('front.join');
    }

    /**
     * Store join request
     */
    public function storeJoinRequest(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'id_number' => ['required', 'string', 'max:50', 'regex:/^\d+$/'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^0(59|56)\d{7}$/'],
            'email' => 'nullable|email|max:255',
            'data_accuracy' => 'required|accepted',
        ], [
            'name_ar.required' => 'الاسم بالعربية مطلوب',
            'name_en.required' => 'الاسم بالإنجليزية مطلوب',
            'id_number.required' => 'رقم الهوية مطلوب',
            'id_number.regex' => 'رقم الهوية يجب أن يحتوي على أرقام فقط',
            'phone.required' => 'رقم الموبايل مطلوب',
            'phone.regex' => 'رقم الموبايل غير صحيح. يجب أن يبدأ بـ 059 أو 056',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'data_accuracy.required' => 'يجب الموافقة على الإقرار بصحة البيانات',
            'data_accuracy.accepted' => 'يجب الموافقة على الإقرار بصحة البيانات',
        ]);

        // Clean all validated input to ensure valid UTF-8 encoding
        // Note: cleanInputArray uses AppServiceProvider::cleanStringStatic internally
        $validated = $this->cleanInputArray($validated);

        // تنظيف رقم الجوال للتحقق (phone is numeric only, no UTF-8 issues)
        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);

        // Clean ID number for validation and username generation
        $cleanIdNumber = $this->cleanString($validated['id_number']);

        // التحقق من أن الرقم مصرح به في authorized_phones
        if (!\App\Models\AuthorizedPhone::isAuthorized($cleanPhone)) {
            $errorMessage = $this->cleanString('غير مخول لك بالتسجيل. يرجى التواصل مع الإدارة.');
            return redirect()->back()
                ->withInput()
                ->withErrors(['phone' => $errorMessage]);
        }

        // التحقق من أن الرقم غير مسجل مسبقاً كمستخدم (من خلال phone)
        // التحقق من الرقم المنظف مباشرة
        $existingUserByPhone = \App\Models\User::where('phone', $cleanPhone)
            ->orWhere('phone', $validated['phone']) // التحقق من الرقم الأصلي أيضاً
            ->first();
        
        if ($existingUserByPhone) {
            // التحقق من أن المستخدم لديه مشغل
            $existingOperator = \App\Models\Operator::where('owner_id', $existingUserByPhone->id)->first();
            if ($existingOperator) {
                $errorMessage = $this->cleanString('مسجل مسبقاً. غير مسموح لك التسجيل مرة أخرى.');
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['phone' => $errorMessage]);
            }
        }

        // التحقق من أن الرقم غير مسجل مسبقاً في جدول operators
        // التحقق من الرقم المنظف مباشرة
        $existingOperatorByPhone = \App\Models\Operator::where('phone', $cleanPhone)
            ->orWhere('phone', $validated['phone']) // التحقق من الرقم الأصلي أيضاً
            ->first();
        
        if ($existingOperatorByPhone) {
            $errorMessage = $this->cleanString('رقم الجوال مسجل مسبقاً. غير مسموح لك التسجيل مرة أخرى.');
            return redirect()->back()
                ->withInput()
                ->withErrors(['phone' => $errorMessage]);
        }

        // التحقق من عدم وجود رقم هوية مسجل مسبقاً
        $existingOperatorById = \App\Models\Operator::where('owner_id_number', $cleanIdNumber)->first();
        if ($existingOperatorById) {
            $errorMessage = $this->cleanString('رقم الهوية مسجل مسبقاً');
            return redirect()->back()
                ->withInput()
                ->withErrors(['id_number' => $errorMessage]);
        }

        // Check if email is provided
        $email = !empty($validated['email']) ? trim($this->cleanString($validated['email'])) : null;
        
        // Validate email if provided
        if ($email) {
            // Check if email already exists
            $existingUser = \App\Models\User::where('email', $email)->whereNull('deleted_at')->first();
            if ($existingUser) {
                $errorMessage = $this->cleanString('البريد الإلكتروني مسجل مسبقاً');
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['email' => $errorMessage]);
            }
        }

        // توليد username تلقائياً (op_ + أول حرف من الاسم + اسم العائلة)
        // Note: $validated['name_en'] is already cleaned by cleanInputArray
        $nameEn = trim($validated['name_en']);
        $nameParts = explode(' ', $nameEn);
        
        if (count($nameParts) >= 2) {
            // أول حرف من الاسم الأول + اسم العائلة الكامل
            $firstChar = strtolower(substr($nameParts[0], 0, 1));
            $lastName = strtolower($nameParts[count($nameParts) - 1]);
            // إزالة الأحرف الخاصة والمسافات
            $lastName = preg_replace('/[^a-z0-9]/', '', $lastName);
            $usernameBase = $firstChar . $lastName;
        } else {
            // إذا كان اسم واحد فقط، استخدم أول 8 أحرف
            $usernameBase = strtolower(preg_replace('/[^a-z0-9]/', '', $nameEn));
            $usernameBase = substr($usernameBase, 0, 8);
        }
        
        // التأكد من أن usernameBase ليس فارغاً
        if (empty($usernameBase)) {
            $usernameBase = 'user' . substr($cleanIdNumber, -4);
        }
        
        $username = 'op_' . $usernameBase;
        
        // التأكد من أن username فريد
        $counter = 1;
        $originalUsername = $username;
        while (\App\Models\User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        // Generate unique email if not provided (required in users table)
        if (!$email || empty($email)) {
            // Use username + @gazarased.com
            $email = $username . '@gazarased.com';
            $counter = 1;
            while (\App\Models\User::where('email', $email)->whereNull('deleted_at')->exists()) {
                $email = $username . '_' . $counter . '@gazarased.com';
                $counter++;
            }
        }

        // توليد password تلقائياً (8 أحرف عشوائية - فقط أحرف وأرقام لتجنب مشاكل الترميز)
        // استخدام فقط أحرف صغيرة وأرقام لتجنب مشاكل في SMS
        $password = \Illuminate\Support\Str::random(8);
        // التأكد من أن كلمة المرور لا تحتوي على أحرف خاصة قد تسبب مشاكل
        $password = preg_replace('/[^a-zA-Z0-9]/', '', $password);
        // إذا كانت قصيرة جداً بعد التنظيف، أعد توليدها
        if (strlen($password) < 6) {
            $password = \Illuminate\Support\Str::random(8);
            $password = preg_replace('/[^a-zA-Z0-9]/', '', $password);
        }
        // تأكد من أن الطول 8 أحرف
        if (strlen($password) < 8) {
            $password = str_pad($password, 8, \Illuminate\Support\Str::random(1), STR_PAD_RIGHT);
        }
        $passwordPlain = $password; // حفظ كلمة المرور النصية لإرسالها عبر SMS

        // Log كلمة المرور قبل الحفظ (للتأكد من أنها صحيحة)
        \Log::info('Creating user with password', [
            'username' => $username,
            'password_length' => strlen($passwordPlain),
            'password_preview' => substr($passwordPlain, 0, 2) . '****',
        ]);

        // Ensure all data is clean before saving (note: $cleanIdNumber is already defined above)
        $cleanNameAr = $this->cleanString($validated['name_ar']);
        $cleanNameEn = $this->cleanString($validated['name_en']);
        $cleanOwnerName = $this->cleanString($validated['name_ar']);

        // إنشاء User جديد
        $user = \App\Models\User::create([
            'name' => $cleanNameAr,
            'email' => $email,
            'username' => $username,
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'password_plain' => $passwordPlain,
            'phone' => $cleanPhone, // استخدام الرقم المنظف
            'role' => \App\Role::CompanyOwner,
            'status' => 'active',
        ]);

        // التحقق من أن كلمة المرور محفوظة بشكل صحيح
        \Log::info('User created, verifying password', [
            'user_id' => $user->id,
            'username' => $username,
            'password_check' => \Illuminate\Support\Facades\Hash::check($passwordPlain, $user->password),
        ]);

        // إنشاء Operator جديد
        $operator = \App\Models\Operator::create([
            'name' => $cleanNameAr,
            'name_en' => $cleanNameEn,
            'owner_id' => $user->id,
            'owner_name' => $cleanOwnerName,
            'owner_id_number' => $cleanIdNumber,
            'phone' => $cleanPhone, // استخدام الرقم المنظف
            'email' => $validated['email'] ? $this->cleanString($validated['email']) : null,
            'status' => 'active', // مفعل - يمكنه تسجيل الدخول وإضافة وحدات ومولدات
            'is_approved' => false, // غير معتمد - يحتاج موافقة Admin/Super Admin للوصول الكامل
            'profile_completed' => false,
        ]);

        // Note: Operator::boot() will automatically send notification to all approvers when operator is created
        // No need to send notification here as it's handled in Operator::boot() event listener

        // إرسال SMS بالـ username والـ password
        try {
            $this->sendCredentialsSMS($validated['phone'], $username, $passwordPlain);
        } catch (\Exception $e) {
            // في حالة فشل إرسال SMS، نكمل العملية لكن نسجل الخطأ
            \Log::error('Failed to send SMS credentials', [
                'phone' => $validated['phone'],
                'username' => $username,
                'error' => $e->getMessage(),
            ]);
        }

        // Create 3 welcome messages for the new operator
        // This must be done AFTER user and operator are created
        try {
            $this->createWelcomeMessages($user, $operator, $username);
            
            // Verify that messages were created
            $messageCount = \App\Models\Message::where('receiver_id', $user->id)
                ->where('operator_id', $operator->id)
                ->where('type', 'admin_to_operator')
                ->count();
            
            \Log::info('Welcome messages creation completed', [
                'user_id' => $user->id,
                'operator_id' => $operator->id,
                'messages_count' => $messageCount,
                'expected_count' => 3,
            ]);
            
            if ($messageCount < 3) {
                \Log::warning('Not all welcome messages were created', [
                    'user_id' => $user->id,
                    'operator_id' => $operator->id,
                    'actual_count' => $messageCount,
                    'expected_count' => 3,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create welcome messages', [
                'user_id' => $user->id,
                'operator_id' => $operator->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Continue even if messages creation fails - don't block registration
        }

        // Clean success message to ensure valid UTF-8
        $successMessage = $this->cleanString('تم إرسال طلبك بنجاح! تم إرسال بيانات الدخول إلى رقم الموبايل المسجل.');
        
        return redirect()->route('front.join')->with('success', $successMessage);
    }

    /**
     * Clean UTF-8 string - uses AppServiceProvider's static method for consistency
     */
    private function cleanString(?string $value): string
    {
        return \App\Providers\AppServiceProvider::cleanStringStatic($value);
    }

    /**
     * Clean array of input values recursively
     */
    private function cleanInputArray(array $data): array
    {
        $cleaned = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $cleaned[$key] = $this->cleanInputArray($value);
            } elseif (is_string($value)) {
                $cleaned[$key] = $this->cleanString($value);
            } else {
                $cleaned[$key] = $value;
            }
        }
        return $cleaned;
    }

    /**
     * إرسال بيانات الدخول عبر SMS
     */
    private function sendCredentialsSMS(string $phone, string $username, string $password): void
    {
        // Log كلمة المرور قبل الإرسال
        \Log::info('Preparing SMS with credentials', [
            'phone' => $phone,
            'username' => $username,
            'password_length' => strlen($password),
            'password_chars' => str_split($password), // للتأكد من الأحرف
        ]);

        // رسالة واضحة مع الرابط
        $loginUrl = route('login');
        
        // بناء رسالة مختصرة مع الرابط
        // استخدام صيغة مختصرة لتوفير المساحة للرابط
        $message = "راصد\n";
        $message .= "المستخدم: {$username}\n";
        $message .= "كلمة المرور: {$password}\n";
        $message .= "رابط: {$loginUrl}";

        // Log طول الرسالة
        \Log::info('SMS message length check', [
            'message_length' => mb_strlen($message),
            'max_length' => 70,
            'login_url' => $loginUrl,
            'url_length' => mb_strlen($loginUrl),
        ]);

        // إذا كانت الرسالة طويلة جداً (أكثر من 70 حرف للـ UTF-8)، نحاول تقصيرها
        // لكن نضمن وجود الرابط دائماً
        if (mb_strlen($message) > 70) {
            // استخدام صيغة أقصر مع الحفاظ على الرابط
            $message = "راصد\n";
            $message .= "المستخدم: {$username}\n";
            $message .= "كلمة المرور: {$password}\n";
            $message .= $loginUrl; // بدون كلمة "رابط:" لتوفير المساحة
        }
        
        // إذا كانت لا تزال طويلة جداً، نستخدم صيغة أقصر جداً
        if (mb_strlen($message) > 70) {
            $message = "راصد\n";
            $message .= "المستخدم: {$username}\n";
            $message .= "كلمة المرور: {$password}\n";
            // استخدام فقط domain + path بدلاً من الرابط الكامل
            $parsedUrl = parse_url($loginUrl);
            $shortUrl = ($parsedUrl['host'] ?? '') . ($parsedUrl['path'] ?? '/login');
            $message .= $shortUrl;
        }

        // Log الرسالة النهائية
        \Log::info('SMS message content', [
            'message' => $message,
            'message_length' => mb_strlen($message),
        ]);

        try {
            $smsService = new \App\Services\HotSMSService();
            // استخدام type 2 للرسائل العربية (UTF-8)
            $result = $smsService->sendSMS($phone, $message, 2);

            if ($result['success']) {
                \Log::info('SMS sent successfully', [
                    'phone' => $phone,
                    'username' => $username,
                    'message_id' => $result['message_id'] ?? null,
                ]);
            } else {
                \Log::error('Failed to send SMS', [
                    'phone' => $phone,
                    'username' => $username,
                    'error_code' => $result['code'],
                    'error_message' => $result['message'],
                ]);
                
                // يمكن إضافة إشعار للمستخدم هنا إذا لزم الأمر
            }
        } catch (\Exception $e) {
            \Log::error('SMS service exception', [
                'phone' => $phone,
                'username' => $username,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Create 3 welcome messages for the new operator
     */
    private function createWelcomeMessages(\App\Models\User $user, \App\Models\Operator $operator, string $username): void
    {
        // Get system user (منصة راصد) to send messages from
        $systemUser = \App\Models\User::where('username', 'platform_rased')->first();

        if (!$systemUser) {
            \Log::warning('System user (platform_rased) not found. Cannot send welcome messages', [
                'user_id' => $user->id,
                'operator_id' => $operator->id,
            ]);
            return;
        }

        $loginUrl = route('login');
        $changePasswordUrl = route('admin.profile') . '#change-password';

        \Log::info('Creating welcome messages for new operator', [
            'user_id' => $user->id,
            'operator_id' => $operator->id,
            'sender_id' => $systemUser->id,
            'sender_name' => $systemUser->name,
            'receiver_id' => $user->id,
        ]);

        try {
            // Message 1: Request to change password
            $message1 = \App\Models\Message::create([
                'sender_id' => $systemUser->id,
                'receiver_id' => $user->id,
                'operator_id' => $operator->id,
                'subject' => 'يرجى تغيير كلمة المرور',
                'body' => "عزيزي/عزيزتي {$user->name}،\n\nنرحب بك في منصة راصد لإدارة وحدات التوليد.\n\nلأسباب أمنية، نرجو منك تغيير كلمة المرور الافتراضية بعد تسجيل الدخول لأول مرة.\n\nاسم المستخدم: {$username}\n\nيمكنك تغيير كلمة المرور من صفحة الملف الشخصي بعد تسجيل الدخول.\n\nرابط تسجيل الدخول: {$loginUrl}",
                'type' => 'admin_to_operator',
                'is_read' => false,
            ]);
            \Log::info('Welcome message 1 created', ['message_id' => $message1->id]);

            // Message 2: Welcome
            $message2 = \App\Models\Message::create([
                'sender_id' => $systemUser->id,
                'receiver_id' => $user->id,
                'operator_id' => $operator->id,
                'subject' => 'مرحباً بك في منصة راصد',
                'body' => "عزيزي/عزيزتي {$user->name}،\n\nنرحب بك في منصة راصد لإدارة وحدات التوليد.\n\nنتمنى أن تجد في النظام كل ما تحتاجه لإدارة عملك بكفاءة وفعالية.\n\nيمكنك الآن:\n- إضافة وحدات التوليد والمولدات\n- متابعة سجلات التشغيل والوقود\n- إدارة أعمال الصيانة\n\nملاحظة: حسابك حالياً في حالة انتظار الاعتماد من سلطة الطاقة. بعد الاعتماد، ستحصل على صلاحيات كاملة للوصول لجميع خصائص النظام.\n\nنتمنى لك تجربة ممتعة!",
                'type' => 'admin_to_operator',
                'is_read' => false,
            ]);
            \Log::info('Welcome message 2 created', ['message_id' => $message2->id]);

            // Message 3: Additional welcome
            $message3 = \App\Models\Message::create([
                'sender_id' => $systemUser->id,
                'receiver_id' => $user->id,
                'operator_id' => $operator->id,
                'subject' => 'معلومات مهمة عن النظام',
                'body' => "عزيزي/عزيزتي {$user->name}،\n\nنود إعلامك بأن:\n\n1. يمكنك الآن إضافة وحدات التوليد والمولدات حتى قبل اعتماد حسابك\n2. يمكنك تغيير كلمة المرور في أي وقت من صفحة الملف الشخصي\n3. بعد اعتماد حسابك من قبل سلطة الطاقة، ستحصل على صلاحيات كاملة للوصول لجميع خصائص النظام (الصلاحيات، الشجرة، السجلات الكاملة)\n4. يمكنك التواصل معنا في أي وقت من خلال نظام الرسائل\n\nرابط تسجيل الدخول: {$loginUrl}\n\nنتمنى لك تجربة ناجحة!",
                'type' => 'admin_to_operator',
                'is_read' => false,
            ]);
            \Log::info('Welcome message 3 created', ['message_id' => $message3->id]);

            \Log::info('All welcome messages created successfully', [
                'user_id' => $user->id,
                'operator_id' => $operator->id,
                'messages_count' => 3,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create welcome messages', [
                'user_id' => $user->id,
                'operator_id' => $operator->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
