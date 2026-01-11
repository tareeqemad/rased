@php
    $authUser = auth()->user();
    $isCreate = ($mode ?? 'create') === 'create';

    $action = $isCreate
        ? route('admin.users.store')
        : route('admin.users.update', $user);

    $method = $isCreate ? 'POST' : 'PUT';

    $selectedRole = old('role', $isCreate ? ($defaultRole ?? '') : ($user->role?->value ?? ''));
    $isEmpOrTech = in_array($selectedRole, [\App\Role::Employee->value, \App\Role::Technician->value], true);
    $isCompanyOwnerRole = $selectedRole === \App\Role::CompanyOwner->value;
    $isMainRole = in_array($selectedRole, [\App\Role::SuperAdmin->value, \App\Role::Admin->value, \App\Role::EnergyAuthority->value], true);
    
    // تحديد ما إذا كان الدور المحدد دور مخصص (ليس دور نظامي)
    $systemRoles = [\App\Role::SuperAdmin->value, \App\Role::Admin->value, \App\Role::EnergyAuthority->value, \App\Role::CompanyOwner->value, \App\Role::Employee->value, \App\Role::Technician->value];
    $isCustomRole = $selectedRole && !in_array($selectedRole, $systemRoles, true);
    
    // تحديد ما إذا كان username و password يتم توليدهما تلقائياً
    // للمشغل: عند إنشاء مستخدم جديد، username/password يتم توليدهما تلقائياً دائماً (لأنه لا يمكنه إضافة إلا موظف/فني أو أدوار مخصصة)
    // للسوبر أدمن/الآدمن/سلطة الطاقة: عند اختيار أدوار رئيسية أو مشغل، username/password يتم توليدهما تلقائياً
    // عند اختيار أدوار عادية (موظف/فني أو أدوار مخصصة): username/password يجب إدخالهما يدوياً
    $autoGenerateCredentials = $isCreate && (
        $authUser->isCompanyOwner() || // المشغل دائماً يحتاج توليد تلقائي (لأنه يضيف فقط موظف/فني أو أدوار مخصصة)
        (($authUser->isSuperAdmin() || $authUser->isAdmin() || $authUser->isEnergyAuthority()) && ($isMainRole || $isCompanyOwnerRole))
    );
    
    // تحديد ما إذا كانت name_en و phone مطلوبة
    // للسوبر أدمن/الآدمن/سلطة الطاقة: عند إنشاء أدوار رئيسية أو مشغل
    $needNameEnAndPhone = $isCreate && ($authUser->isSuperAdmin() || $authUser->isAdmin() || $authUser->isEnergyAuthority()) && ($isMainRole || $isCompanyOwnerRole);
    
    // للمشغل: إظهار حقل phone (اختياري) عند إنشاء مستخدم جديد
    // المشغل يضيف موظف/فني أو مستخدمين بأدوار مخصصة - كلهم يحتاجون phone لإرسال بيانات الدخول
    // عند اختيار أي دور (employee/technician أو custom role)، username/password يتم توليدهما تلقائياً
    $showPhoneForCompanyOwner = $isCreate && $authUser->isCompanyOwner();
    
    $selectedOp = $selectedOperator ?? null;
@endphp

<form id="userAjaxForm" action="{{ $action }}" method="POST" data-method="{{ $method }}">
    @csrf
    @if(!$isCreate) @method('PUT') @endif

    <div class="row g-3">
        {{-- الاسم --}}
        <div class="col-md-6">
            <label class="form-label">الاسم <span class="text-danger">*</span></label>
            <input name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
            <div class="text-danger small mt-1 d-none" data-error-for="name"></div>
        </div>

        {{-- الاسم بالإنجليزي (للسوبر أدمن وسلطة الطاقة عند إنشاء أدوار رئيسية) --}}
        <div class="col-md-6" id="nameEnField" style="{{ $needNameEnAndPhone ? '' : 'display:none' }}">
            <label class="form-label">
                الاسم بالإنجليزي 
                <span class="text-danger" id="nameEnRequired" style="{{ $needNameEnAndPhone ? '' : 'display:none' }}">*</span>
            </label>
            <input name="name_en" class="form-control" value="{{ old('name_en', $user->name_en ?? '') }}" placeholder="English Name">
            <div class="text-danger small mt-1 d-none" data-error-for="name_en"></div>
            <div class="help small text-muted mt-1" id="nameEnHelp" style="{{ $needNameEnAndPhone ? '' : 'display:none' }}">
                سيتم استخدامه لتوليد username تلقائياً
            </div>
        </div>

        {{-- رقم الجوال --}}
        <div class="col-md-6" id="phoneField" style="{{ ($needNameEnAndPhone || $showPhoneForCompanyOwner) ? '' : 'display:none' }}">
            <label class="form-label">
                رقم الجوال 
                <span class="text-danger" id="phoneRequired" style="{{ $needNameEnAndPhone ? '' : 'display:none' }}">*</span>
            </label>
            <input name="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}" placeholder="059xxxxxxx أو 056xxxxxxx" maxlength="10" {{ $needNameEnAndPhone ? 'required' : '' }}>
            <div class="text-danger small mt-1 d-none" data-error-for="phone"></div>
            <div class="help small text-muted mt-1" id="phoneHelp" style="{{ ($needNameEnAndPhone || $showPhoneForCompanyOwner) ? '' : 'display:none' }}">
                @if($autoGenerateCredentials)
                    سيتم توليد اسم المستخدم وكلمة المرور تلقائياً وإرسالهما عبر SMS إلى هذا الرقم
                @else
                    سيتم إرسال بيانات الدخول عبر SMS
                @endif
            </div>
        </div>

        {{-- اسم المستخدم (يُخفي عند التوليد التلقائي) --}}
        <div class="col-md-6" id="usernameField" style="{{ $autoGenerateCredentials ? 'display:none' : '' }}">
            <label class="form-label">
                اسم المستخدم 
                <span class="text-danger" id="usernameRequired" style="{{ $autoGenerateCredentials ? 'display:none' : '' }}">*</span>
            </label>
            <input name="username" class="form-control" value="{{ old('username', $user->username ?? '') }}" {{ $autoGenerateCredentials ? '' : 'required' }}>
            <div class="text-danger small mt-1 d-none" data-error-for="username"></div>
            <div class="help small text-muted mt-1" id="usernameHelp" style="{{ $autoGenerateCredentials ? '' : 'display:none' }}">
                سيتم توليده تلقائياً
            </div>
        </div>

        {{-- البريد الإلكتروني --}}
        <div class="col-md-6" id="emailField">
            <label class="form-label">
                البريد الإلكتروني
            </label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}">
            <div class="text-danger small mt-1 d-none" data-error-for="email"></div>
        </div>

        {{-- الدور --}}
        <div class="col-md-6">
            <label class="form-label">الدور <span class="text-danger">*</span></label>
            <select name="role" id="roleSelect" class="form-select" required>
                <option value="">اختر الدور</option>
                @foreach($roles as $r)
                    @php
                        $roleValue = is_array($r) ? ($r['value'] ?? $r['name'] ?? '') : ($r->value ?? $r->name ?? '');
                        $roleLabel = is_array($r) ? ($r['label'] ?? $roleValue) : ($r->label ?? $r->label() ?? $roleValue);
                    @endphp
                    <option value="{{ $roleValue }}" 
                            data-role-id="{{ is_array($r) ? ($r['role_id'] ?? '') : '' }}"
                            {{ (string)$selectedRole === (string)$roleValue ? 'selected' : '' }}>
                        {{ $roleLabel }}
                    </option>
                @endforeach
            </select>
            <div class="text-danger small mt-1 d-none" data-error-for="role"></div>
        </div>

        {{-- المشغل --}}
        @if($authUser->isCompanyOwner())
            {{-- للمشغل: المشغل محدد تلقائياً --}}
            <div class="col-md-6">
                <label class="form-label">المشغل</label>
                <input class="form-control" value="{{ $operatorLocked?->name ?? 'غير مرتبط' }}" disabled>
                <input type="hidden" name="operator_id" value="{{ $operatorLocked?->id ?? '' }}">
                <div class="help small text-muted mt-1">سيتم ربط المستخدم تلقائيًا بمشغلك.</div>
            </div>
        @else
            {{-- للسوبر أدمن وسلطة الطاقة --}}
            <div class="col-md-6" id="operatorField" style="{{ ($isEmpOrTech || $isCompanyOwnerRole) ? '' : 'display:none' }}">
                <label class="form-label">
                    المشغل 
                    <span class="text-danger" id="operatorRequired" style="{{ ($isEmpOrTech || $isCompanyOwnerRole) ? '' : 'display:none' }}">*</span>
                </label>

                {{-- Select2 server-side --}}
                <select name="operator_id" id="operatorSelect" class="form-select js-operator-select">
                    @if($selectedOp)
                        <option value="{{ $selectedOp->id }}" selected>{{ $selectedOp->name }}{{ $selectedOp->unit_number ? ' - '.$selectedOp->unit_number : '' }}</option>
                    @endif
                </select>

                <div class="text-danger small mt-1 d-none" data-error-for="operator_id"></div>
                <div class="help small text-muted mt-1" id="operatorHelp">
                    @if($isCompanyOwnerRole)
                        <span class="text-warning">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            يجب أن يكون رقم المشغل موجود في الأرقام المصرح بها
                        </span>
                    @else
                        للموظف/الفني: لازم يكون تابع لمشغل واحد فقط.
                    @endif
                </div>
            </div>
        @endif

        {{-- كلمة المرور (يُخفي عند التوليد التلقائي) --}}
        <div class="col-md-6" id="passwordField" style="{{ $autoGenerateCredentials ? 'display:none' : '' }}">
            <label class="form-label">
                كلمة المرور 
                <span class="text-danger" id="passwordRequired" style="{{ ($isCreate && !$autoGenerateCredentials) ? '' : 'display:none' }}">*</span>
            </label>
            <input type="password" name="password" class="form-control" {{ ($isCreate && !$autoGenerateCredentials) ? 'required minlength="8"' : '' }}>
            <div class="text-danger small mt-1 d-none" data-error-for="password"></div>
            <div class="help small text-muted mt-1" id="passwordHelp">
                {{ $isCreate ? ($autoGenerateCredentials ? 'سيتم توليدها تلقائياً' : '8 أحرف على الأقل.') : 'اتركها فارغة إذا لا تريد تغييرها.' }}
            </div>
        </div>

        {{-- تأكيد كلمة المرور (يُخفي عند التوليد التلقائي) --}}
        <div class="col-md-6" id="passwordConfirmationField" style="{{ $autoGenerateCredentials ? 'display:none' : '' }}">
            <label class="form-label">
                تأكيد كلمة المرور 
                <span class="text-danger" id="passwordConfirmationRequired" style="{{ ($isCreate && !$autoGenerateCredentials) ? '' : 'display:none' }}">*</span>
            </label>
            <input type="password" name="password_confirmation" class="form-control" {{ ($isCreate && !$autoGenerateCredentials) ? 'required minlength="8"' : '' }}>
        </div>

    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-light-subtle" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary" id="userModalSubmitBtn">
            حفظ
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('roleSelect');
    const authUserIsSuperAdmin = {{ $authUser->isSuperAdmin() ? 'true' : 'false' }};
    const authUserIsAdmin = {{ $authUser->isAdmin() ? 'true' : 'false' }};
    const authUserIsEnergyAuthority = {{ $authUser->isEnergyAuthority() ? 'true' : 'false' }};
    const authUserIsCompanyOwner = {{ $authUser->isCompanyOwner() ? 'true' : 'false' }};
    const isCreate = {{ $isCreate ? 'true' : 'false' }};

    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            const selectedRole = this.value;
            const isEmpOrTech = ['employee', 'technician'].includes(selectedRole);
            const isCompanyOwnerRole = selectedRole === 'company_owner';
            const isMainRole = ['super_admin', 'admin', 'energy_authority'].includes(selectedRole);
            
            // تحديد ما إذا كان username و password يتم توليدهما تلقائياً
            // للمشغل: عند إنشاء مستخدم جديد، username/password يتم توليدهما تلقائياً دائماً (لأنه لا يمكنه إضافة إلا موظف/فني أو أدوار مخصصة)
            // للسوبر أدمن/الآدمن/سلطة الطاقة: عند اختيار أدوار رئيسية أو مشغل، username/password يتم توليدهما تلقائياً
            // عند اختيار أدوار عادية (موظف/فني أو أدوار مخصصة): username/password يجب إدخالهما يدوياً
            const isCustomRole = selectedRole && !['super_admin', 'admin', 'energy_authority', 'company_owner', 'employee', 'technician'].includes(selectedRole);
            const autoGenerateCredentials = isCreate && (
                authUserIsCompanyOwner || // المشغل دائماً يحتاج توليد تلقائي
                ((authUserIsSuperAdmin || authUserIsAdmin || authUserIsEnergyAuthority) && (isMainRole || isCompanyOwnerRole))
            );
            
            // تحديد ما إذا كانت name_en و phone مطلوبة
            // للسوبر أدمن/الآدمن/سلطة الطاقة: عند إنشاء أدوار رئيسية أو مشغل
            const needNameEnAndPhone = isCreate && (authUserIsSuperAdmin || authUserIsAdmin || authUserIsEnergyAuthority) && (isMainRole || isCompanyOwnerRole);
            
            // للمشغل: إظهار حقل phone (اختياري) عند إنشاء مستخدم جديد (موظف/فني أو أدوار مخصصة)
            // عند إضافة أي مستخدم جديد، username/password يتم توليدهما تلقائياً وإرسالهما عبر SMS
            const showPhoneForCompanyOwner = isCreate && authUserIsCompanyOwner;

            // إظهار/إخفاء name_en و phone
            const nameEnField = document.getElementById('nameEnField');
            const phoneField = document.getElementById('phoneField');
            const nameEnRequired = document.getElementById('nameEnRequired');
            const phoneRequired = document.getElementById('phoneRequired');
            const nameEnHelp = document.getElementById('nameEnHelp');
            const phoneHelp = document.getElementById('phoneHelp');
            
            if (nameEnField) {
                nameEnField.style.display = needNameEnAndPhone ? '' : 'none';
                if (nameEnRequired) nameEnRequired.style.display = needNameEnAndPhone ? '' : 'none';
                if (nameEnHelp) nameEnHelp.style.display = needNameEnAndPhone ? '' : 'none';
                if (needNameEnAndPhone) {
                    nameEnField.querySelector('input[name="name_en"]').required = true;
                } else {
                    nameEnField.querySelector('input[name="name_en"]').required = false;
                }
            }
            
            if (phoneField) {
                const shouldShowPhone = needNameEnAndPhone || showPhoneForCompanyOwner;
                phoneField.style.display = shouldShowPhone ? '' : 'none';
                if (phoneRequired) phoneRequired.style.display = needNameEnAndPhone ? '' : 'none';
                if (phoneHelp) {
                    phoneHelp.style.display = shouldShowPhone ? '' : 'none';
                    if (shouldShowPhone) {
                        if (autoGenerateCredentials) {
                            phoneHelp.textContent = 'سيتم توليد اسم المستخدم وكلمة المرور تلقائياً وإرسالهما عبر SMS إلى هذا الرقم';
                        } else {
                            phoneHelp.textContent = 'سيتم إرسال بيانات الدخول عبر SMS';
                        }
                    }
                }
                const phoneInput = phoneField.querySelector('input[name="phone"]');
                if (phoneInput) {
                    phoneInput.required = needNameEnAndPhone;
                }
            }

            // إظهار/إخفاء username
            const usernameField = document.getElementById('usernameField');
            const usernameRequired = document.getElementById('usernameRequired');
            const usernameHelp = document.getElementById('usernameHelp');
            
            if (usernameField) {
                usernameField.style.display = autoGenerateCredentials ? 'none' : '';
                if (usernameRequired) usernameRequired.style.display = autoGenerateCredentials ? 'none' : '';
                if (usernameHelp) usernameHelp.style.display = autoGenerateCredentials ? '' : 'none';
                const usernameInput = usernameField.querySelector('input[name="username"]');
                if (usernameInput) {
                    usernameInput.required = !autoGenerateCredentials;
                }
            }

            // إظهار/إخفاء password
            const passwordField = document.getElementById('passwordField');
            const passwordConfirmationField = document.getElementById('passwordConfirmationField');
            const passwordRequired = document.getElementById('passwordRequired');
            const passwordConfirmationRequired = document.getElementById('passwordConfirmationRequired');
            const passwordHelp = document.getElementById('passwordHelp');
            
            if (passwordField) {
                passwordField.style.display = autoGenerateCredentials ? 'none' : '';
                if (passwordRequired) passwordRequired.style.display = (isCreate && !autoGenerateCredentials) ? '' : 'none';
                if (passwordHelp) {
                    passwordHelp.textContent = isCreate 
                        ? (autoGenerateCredentials ? 'سيتم توليدها تلقائياً' : '8 أحرف على الأقل.')
                        : 'اتركها فارغة إذا لا تريد تغييرها.';
                }
                const passwordInput = passwordField.querySelector('input[name="password"]');
                if (passwordInput) {
                    passwordInput.required = isCreate && !autoGenerateCredentials;
                    passwordInput.minLength = isCreate && !autoGenerateCredentials ? 8 : 0;
                }
            }
            
            if (passwordConfirmationField) {
                passwordConfirmationField.style.display = autoGenerateCredentials ? 'none' : '';
                if (passwordConfirmationRequired) passwordConfirmationRequired.style.display = (isCreate && !autoGenerateCredentials) ? '' : 'none';
                const passwordConfirmationInput = passwordConfirmationField.querySelector('input[name="password_confirmation"]');
                if (passwordConfirmationInput) {
                    passwordConfirmationInput.required = isCreate && !autoGenerateCredentials;
                    passwordConfirmationInput.minLength = isCreate && !autoGenerateCredentials ? 8 : 0;
                }
            }

            // إظهار/إخفاء email (always optional)
            const emailField = document.getElementById('emailField');
            
            if (emailField) {
                const emailInput = emailField.querySelector('input[name="email"]');
                if (emailInput) {
                    emailInput.required = false;
                }
            }

            // إظهار/إخفاء operator field
            const operatorField = document.getElementById('operatorField');
            const operatorRequired = document.getElementById('operatorRequired');
            const operatorHelp = document.getElementById('operatorHelp');
            
            if (operatorField && !authUserIsCompanyOwner) {
                const shouldShow = isEmpOrTech || isCompanyOwnerRole;
                operatorField.style.display = shouldShow ? '' : 'none';
                if (operatorRequired) operatorRequired.style.display = shouldShow ? '' : 'none';
                
                const operatorSelect = operatorField.querySelector('select[name="operator_id"]');
                if (operatorSelect) {
                    operatorSelect.required = shouldShow;
                }
                
                if (operatorHelp) {
                    if (isCompanyOwnerRole) {
                        operatorHelp.innerHTML = '<span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>يجب أن يكون رقم المشغل موجود في الأرقام المصرح بها</span>';
                    } else {
                        operatorHelp.textContent = 'للموظف/الفني: لازم يكون تابع لمشغل واحد فقط.';
                    }
                }
            }
        });
        
        // تشغيل الحدث عند التحميل لتحديث الحقول حسب الدور المحدد مسبقاً أو الحالة الافتراضية
        // للمشغل: يجب إخفاء username/password حتى قبل اختيار الدور
        if (authUserIsCompanyOwner && isCreate) {
            // للمشغل عند إنشاء مستخدم: إخفاء username/password افتراضياً
            const usernameField = document.getElementById('usernameField');
            const passwordField = document.getElementById('passwordField');
            const passwordConfirmationField = document.getElementById('passwordConfirmationField');
            const usernameRequired = document.getElementById('usernameRequired');
            const usernameHelp = document.getElementById('usernameHelp');
            const passwordRequired = document.getElementById('passwordRequired');
            const passwordConfirmationRequired = document.getElementById('passwordConfirmationRequired');
            const passwordHelp = document.getElementById('passwordHelp');
            
            if (usernameField) {
                usernameField.style.display = 'none';
                if (usernameRequired) usernameRequired.style.display = 'none';
                if (usernameHelp) usernameHelp.style.display = '';
                const usernameInput = usernameField.querySelector('input[name="username"]');
                if (usernameInput) {
                    usernameInput.required = false;
                }
            }
            
            if (passwordField) {
                passwordField.style.display = 'none';
                if (passwordRequired) passwordRequired.style.display = 'none';
                if (passwordHelp) {
                    passwordHelp.textContent = 'سيتم توليدها تلقائياً';
                }
                const passwordInput = passwordField.querySelector('input[name="password"]');
                if (passwordInput) {
                    passwordInput.required = false;
                    passwordInput.minLength = 0;
                }
            }
            
            if (passwordConfirmationField) {
                passwordConfirmationField.style.display = 'none';
                if (passwordConfirmationRequired) passwordConfirmationRequired.style.display = 'none';
                const passwordConfirmationInput = passwordConfirmationField.querySelector('input[name="password_confirmation"]');
                if (passwordConfirmationInput) {
                    passwordConfirmationInput.required = false;
                    passwordConfirmationInput.minLength = 0;
                }
            }
            
            // إظهار حقل phone للمشغل
            const phoneField = document.getElementById('phoneField');
            const phoneHelp = document.getElementById('phoneHelp');
            if (phoneField) {
                phoneField.style.display = '';
                if (phoneHelp) {
                    phoneHelp.style.display = '';
                    phoneHelp.textContent = 'سيتم توليد اسم المستخدم وكلمة المرور تلقائياً وإرسالهما عبر SMS إلى هذا الرقم';
                }
                const phoneInput = phoneField.querySelector('input[name="phone"]');
                if (phoneInput) {
                    phoneInput.required = false;
                }
            }
        }
        
        // تشغيل الحدث عند التحميل لتحديث الحقول حسب الدور المحدد مسبقاً
        if (roleSelect.value) {
            roleSelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>
