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

<div class="row g-3">
    {{-- Basic --}}
    <div class="col-12">
        <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #eef2f7;">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-person-vcard me-2 text-primary"></i>
                <div class="fw-bold">بيانات المستخدم</div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">الاسم <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name ?? '') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">اسم المستخدم <span class="text-danger">*</span></label>
                    <input type="text" name="username"
                           class="form-control @error('username') is-invalid @enderror"
                           value="{{ old('username', $user->username ?? '') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                    <input type="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $user->email ?? '') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">الدور <span class="text-danger">*</span></label>
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
                    <div class="help">المشغل يقدر يعمل موظف/فني فقط. السوبر أدمن يقدر يعمل مشغل/سلطة الطاقة/موظف/فني.</div>
                </div>

                {{-- Operator binding --}}
                @if($authUser->isCompanyOwner())
                    <div class="col-md-6">
                        <label class="form-label">المشغل</label>
                        <input type="text" class="form-control" value="{{ $operatorLocked?->name ?? 'غير مرتبط' }}" disabled>
                        <div class="help">سيتم ربط المستخدم تلقائيًا بمشغلك.</div>
                    </div>
                @else
                    <div class="col-md-6" id="operatorField" style="{{ $isEmpOrTech ? '' : 'display:none;' }}">
                        <label class="form-label">المشغل <span class="text-danger" id="opReqStar" style="{{ $isEmpOrTech ? '' : 'display:none;' }}">*</span></label>

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
                        <div class="help">للموظف/الفني: لازم يكون تابع لمشغل واحد فقط.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Password --}}
    <div class="col-12">
        <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #eef2f7;">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-key me-2 text-primary"></i>
                <div class="fw-bold">الأمان</div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">
                        كلمة المرور @if($isCreate)<span class="text-danger">*</span>@endif
                    </label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           @if($isCreate) required minlength="8" @endif>
                    <div class="help">{{ $isCreate ? 'يجب أن تكون 8 أحرف على الأقل.' : 'اتركها فارغة إذا لا تريد تغيير كلمة المرور.' }}</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        تأكيد كلمة المرور @if($isCreate)<span class="text-danger">*</span>@endif
                    </label>
                    <input type="password" name="password_confirmation"
                           class="form-control"
                           @if($isCreate) required minlength="8" @endif>
                </div>
            </div>
        </div>
    </div>
</div>
