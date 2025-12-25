@php
    $authUser = auth()->user();
    $isCreate = ($mode ?? 'create') === 'create';

    $action = $isCreate
        ? route('admin.users.store')
        : route('admin.users.update', $user);

    $method = $isCreate ? 'POST' : 'PUT';

    $selectedRole = old('role', $isCreate ? ($defaultRole ?? '') : ($user->role?->value ?? ''));
    $isEmpOrTech = in_array($selectedRole, [\App\Role::Employee->value, \App\Role::Technician->value], true);

    $selectedOp = $selectedOperator ?? null;
@endphp

<form id="userAjaxForm" action="{{ $action }}" method="POST" data-method="{{ $method }}">
    @csrf
    @if(!$isCreate) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">الاسم <span class="text-danger">*</span></label>
            <input name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
            <div class="text-danger small mt-1 d-none" data-error-for="name"></div>
        </div>

        <div class="col-md-6">
            <label class="form-label">اسم المستخدم <span class="text-danger">*</span></label>
            <input name="username" class="form-control" value="{{ old('username', $user->username ?? '') }}" required>
            <div class="text-danger small mt-1 d-none" data-error-for="username"></div>
        </div>

        <div class="col-md-6">
            <label class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
            <div class="text-danger small mt-1 d-none" data-error-for="email"></div>
        </div>

        <div class="col-md-6">
            <label class="form-label">الدور <span class="text-danger">*</span></label>
            <select name="role" id="roleSelect" class="form-select" required>
                <option value="">اختر الدور</option>
                @foreach($roles as $r)
                    <option value="{{ $r->value }}" {{ (string)$selectedRole === (string)$r->value ? 'selected' : '' }}>
                        {{ $r->label() ?? $r->value }}
                    </option>
                @endforeach
            </select>
            <div class="text-danger small mt-1 d-none" data-error-for="role"></div>
        </div>

        {{-- Operator --}}
        @if($authUser->isCompanyOwner())
            <div class="col-md-6">
                <label class="form-label">المشغل</label>
                <input class="form-control" value="{{ $operatorLocked?->name ?? 'غير مرتبط' }}" disabled>
                <div class="help small text-muted mt-1">سيتم ربط المستخدم تلقائيًا بمشغلك.</div>
            </div>
        @else
            <div class="col-md-6" id="operatorField" style="{{ $isEmpOrTech ? '' : 'display:none' }}">
                <label class="form-label">المشغل <span class="text-danger">*</span></label>

                {{-- Select2 server-side --}}
                <select name="operator_id" id="operatorSelect" class="form-select js-operator-select">
                    @if($selectedOp)
                        <option value="{{ $selectedOp->id }}" selected>{{ $selectedOp->name }}{{ $selectedOp->unit_number ? ' - '.$selectedOp->unit_number : '' }}</option>
                    @endif
                </select>

                <div class="text-danger small mt-1 d-none" data-error-for="operator_id"></div>
                <div class="help small text-muted mt-1">للموظف/الفني: لازم يكون تابع لمشغل واحد فقط.</div>
            </div>
        @endif

        {{-- Password --}}
        <div class="col-md-6">
            <label class="form-label">
                كلمة المرور @if($isCreate)<span class="text-danger">*</span>@endif
            </label>
            <input type="password" name="password" class="form-control" @if($isCreate) required minlength="8" @endif>
            <div class="text-danger small mt-1 d-none" data-error-for="password"></div>
            <div class="help small text-muted mt-1">
                {{ $isCreate ? '8 أحرف على الأقل.' : 'اتركها فارغة إذا لا تريد تغييرها.' }}
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label">
                تأكيد كلمة المرور @if($isCreate)<span class="text-danger">*</span>@endif
            </label>
            <input type="password" name="password_confirmation" class="form-control" @if($isCreate) required minlength="8" @endif>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-light-subtle" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary" id="userModalSubmitBtn">
            حفظ
        </button>
    </div>
</form>
