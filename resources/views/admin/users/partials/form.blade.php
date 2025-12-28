@php
    $authUser = auth()->user();
    $isCreate = ($mode ?? 'create') === 'create';

    $roleMeta = [
        \App\Role::SuperAdmin->value   => ['label' => 'مدير النظام',      'badge' => 'danger', 'icon' => 'bi-shield-lock'],
        \App\Role::Admin->value        => ['label' => 'سلطة الطاقة',       'badge' => 'dark',   'icon' => 'bi-lightning-charge'],
        \App\Role::CompanyOwner->value => ['label' => 'مشغل',             'badge' => 'primary','icon' => 'bi-building'],
        \App\Role::Employee->value     => ['label' => 'موظف',             'badge' => 'success','icon' => 'bi-person-badge'],
        \App\Role::Technician->value   => ['label' => 'فني',              'badge' => 'warning','icon' => 'bi-tools'],
    ];

    $selectedRole = old('role');
    if ($selectedRole === null) {
        if (!$isCreate && isset($user)) {
            $selectedRole = $user->role?->value;
        } elseif (!empty($defaultRole)) {
            $selectedRole = $defaultRole;
        }
    }

    $isEmpOrTech = in_array($selectedRole, [\App\Role::Employee->value, \App\Role::Technician->value], true);
@endphp

<div class="mb-4">
    <h6 class="fw-bold mb-3">
        <i class="bi bi-person-vcard text-primary me-2"></i>
        بيانات المستخدم
    </h6>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">الاسم <span class="text-danger">*</span></label>
            <input type="text" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $user->name ?? '') }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">اسم المستخدم <span class="text-danger">*</span></label>
            <input type="text" name="username"
                   class="form-control @error('username') is-invalid @enderror"
                   value="{{ old('username', $user->username ?? '') }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">البريد الإلكتروني <span class="text-danger">*</span></label>
            <input type="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $user->email ?? '') }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">الدور <span class="text-danger">*</span></label>
            <select name="role" id="roleSelect"
                    class="form-select @error('role') is-invalid @enderror" required>
                <option value="">اختر الدور</option>
                @foreach($roles as $r)
                    @php
                        $val = $r->value;
                        $meta = $roleMeta[$val] ?? ['label' => $val, 'badge' => 'secondary', 'icon' => 'bi-person'];
                    @endphp
                    <option value="{{ $val }}" {{ (string)old('role', $selectedRole) === (string)$val ? 'selected' : '' }}>
                        {{ $meta['label'] }}
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted">المشغل يقدر يعمل موظف/فني فقط. السوبر أدمن يقدر يعمل مشغل/سلطة الطاقة/موظف/فني.</small>
        </div>

        {{-- Operator binding --}}
        @if($authUser->isCompanyOwner())
            <div class="col-md-6">
                <label class="form-label fw-semibold">المشغل</label>
                <input type="text" class="form-control" value="{{ $operatorLocked?->name ?? 'غير مرتبط' }}" disabled>
                <small class="form-text text-muted">سيتم ربط المستخدم تلقائيًا بمشغلك.</small>
            </div>
        @else
            <div class="col-md-6" id="operatorField" style="{{ $isEmpOrTech ? '' : 'display:none;' }}">
                <label class="form-label fw-semibold">المشغل <span class="text-danger" id="opReqStar" style="{{ $isEmpOrTech ? '' : 'display:none;' }}">*</span></label>

                {{-- create: operator_id (scalar) / edit: operator_id[] (array) --}}
                @php
                    $opName = $operatorFieldName ?? ($isCreate ? 'operator_id' : 'operator_id[]');
                    $selectedOpId = old('operator_id');
                    if (!$isCreate && isset($userOperators) && !empty($userOperators)) {
                        $selectedOpId = $userOperators[0];
                    }
                @endphp

                <select name="{{ $opName }}" id="operatorSelect"
                        class="form-select @error('operator_id') is-invalid @enderror">
                    <option value="">اختر المشغل</option>
                    @foreach($operators as $op)
                        <option value="{{ $op->id }}" {{ (string)$selectedOpId === (string)$op->id ? 'selected' : '' }}>
                            {{ $op->name }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">للموظف/الفني: لازم يكون تابع لمشغل واحد فقط.</small>
            </div>
        @endif
    </div>
</div>

<hr class="my-4">

<div class="mb-4">
    <h6 class="fw-bold mb-3">
        <i class="bi bi-key text-primary me-2"></i>
        الأمان
    </h6>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">
                كلمة المرور @if($isCreate)<span class="text-danger">*</span>@endif
            </label>
            <input type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   @if($isCreate) required minlength="8" @endif>
            <small class="form-text text-muted">{{ $isCreate ? 'يجب أن تكون 8 أحرف على الأقل.' : 'اتركها فارغة إذا لا تريد تغيير كلمة المرور.' }}</small>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">
                تأكيد كلمة المرور @if($isCreate)<span class="text-danger">*</span>@endif
            </label>
            <input type="password" name="password_confirmation"
                   class="form-control"
                   @if($isCreate) required minlength="8" @endif>
        </div>
    </div>
</div>
