@extends('layouts.admin')

@section('title', 'الدليل الإرشادي')

@push('styles')
<style>
.guide-section {
    margin-bottom: 1.5rem;
}

.guide-section:last-child {
    margin-bottom: 0;
}

.role-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: 600;
}

.steps-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.steps-list .step-item {
    padding: 1rem 0;
    border-bottom: 1px solid var(--tblr-border-color);
    position: relative;
    padding-right: 2rem;
}

.steps-list .step-item:last-child {
    border-bottom: none;
}

.steps-list .step-item::before {
    content: counter(step-counter);
    counter-increment: step-counter;
    position: absolute;
    right: 0;
    top: 1rem;
    width: 1.75rem;
    height: 1.75rem;
    background: var(--tblr-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}

.steps-list {
    counter-reset: step-counter;
}

.step-item h4 {
    color: var(--tblr-primary);
    margin-bottom: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
}

.accordion-button {
    font-weight: 600;
}

.accordion-button:not(.collapsed) {
    background-color: var(--tblr-primary-bg-subtle);
    color: var(--tblr-primary);
}

.info-box {
    background: var(--tblr-primary-bg-subtle);
    border: 1px solid var(--tblr-primary-border-subtle);
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1rem;
}

.info-box h4 {
    color: var(--tblr-primary);
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.info-box ul {
    margin-bottom: 0;
    padding-right: 1.25rem;
}

.info-box ul li {
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.info-box ul li:last-child {
    margin-bottom: 0;
}

.table-responsive {
    border-radius: 0.5rem;
    overflow: hidden;
}

.table thead {
    background: var(--tblr-bg-surface-secondary);
}

.table thead th {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--tblr-body-color);
    border-bottom: 2px solid var(--tblr-border-color);
}

.table tbody td {
    font-size: 0.875rem;
    vertical-align: middle;
}

.table tbody td code {
    background: var(--tblr-bg-surface-secondary);
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    font-size: 0.8125rem;
}

.links-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.links-section ul li {
    margin-bottom: 0.5rem;
}

.links-section ul li:last-child {
    margin-bottom: 0;
}

.links-section ul li a {
    color: var(--tblr-primary);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: color 0.2s;
}

.links-section ul li a:hover {
    color: var(--tblr-primary-hover);
    text-decoration: underline;
}
</style>
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-info-circle me-2"></i>
                            الدليل الإرشادي
                        </h5>
                        <div class="general-subtitle">
                            دليل استخدام منصة راصد - شرح شامل للنظام وآلية عمل كل دور
                        </div>
                    </div>
                </div>

                <div class="card-body pb-4">
                    {{-- نظرة عامة على النظام --}}
                    <div class="guide-section">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-house-door me-2 text-primary"></i>
                                    نظرة عامة على النظام
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">
                                    <strong>منصة راصد</strong> هي منصة إلكترونية متكاملة لإدارة ومراقبة المولدات الكهربائية في فلسطين.
                                    تهدف المنصة إلى تسهيل عملية إدارة المولدات ومراقبة أدائها وضمان الامتثال للقوانين واللوائح.
                                </p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-semibold mb-2">المميزات الرئيسية:</h6>
                                        <ul class="mb-0">
                                            <li>إدارة المشغلين والمولدات</li>
                                            <li>تسجيل سجلات التشغيل والصيانة</li>
                                            <li>مراقبة كفاءة الوقود</li>
                                            <li>إدارة الامتثال والسلامة</li>
                                            <li>تتبع أسعار التعرفة الكهربائية</li>
                                            <li>نظام إشعارات ورسائل داخلي</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-semibold mb-2">الأهداف:</h6>
                                        <ul class="mb-0">
                                            <li>ضمان الشفافية في إدارة المولدات</li>
                                            <li>تحسين كفاءة التشغيل</li>
                                            <li>تسهيل التواصل بين الأطراف</li>
                                            <li>توفير تقارير دقيقة وموثوقة</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- الأدوار في النظام --}}
                    <div class="guide-section">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-people me-2 text-primary"></i>
                                    الأدوار في النظام
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="accordion" id="rolesAccordion">
                                    {{-- السوبر أدمن --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#superAdmin">
                                                <span class="badge bg-danger me-2">مدير النظام</span>
                                                السوبر أدمن (SuperAdmin)
                                            </button>
                                        </h2>
                                        <div id="superAdmin" class="accordion-collapse collapse show" data-bs-parent="#rolesAccordion">
                                            <div class="accordion-body">
                                                <div class="info-box">
                                                    <h4>الصلاحيات:</h4>
                                                    <ul>
                                                        <li>✅ إدارة جميع المستخدمين (إنشاء، تعديل، حذف)</li>
                                                        <li>✅ إدارة الأدوار والصلاحيات</li>
                                                        <li>✅ إدارة المشغلين والمولدات</li>
                                                        <li>✅ إدارة إعدادات الموقع</li>
                                                        <li>✅ إدارة الثوابت</li>
                                                        <li>✅ عرض سجل الأخطاء</li>
                                                        <li>✅ إدارة الأرقام المصرح بها</li>
                                                    </ul>
                                                </div>
                                                <div class="info-box">
                                                    <h4>آلية العمل:</h4>
                                                    <ol class="mb-0">
                                                        <li>يمكنه إنشاء مستخدمين من جميع الأدوار</li>
                                                        <li>عند إنشاء SuperAdmin/Admin/EnergyAuthority/CompanyOwner: يُدخل الاسم، الاسم بالإنجليزي، رقم الجوال، البريد، والدور</li>
                                                        <li>يتم توليد username و password تلقائياً</li>
                                                        <li>يتم إرسال SMS بالبيانات تلقائياً</li>
                                                        <li>يمكنه إدارة جميع المشغلين والمولدات</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- سلطة الطاقة --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#energyAuthority">
                                                <span class="badge bg-info me-2">سلطة الطاقة</span>
                                                EnergyAuthority
                                            </button>
                                        </h2>
                                        <div id="energyAuthority" class="accordion-collapse collapse" data-bs-parent="#rolesAccordion">
                                            <div class="accordion-body">
                                                <div class="info-box">
                                                    <h4>الصلاحيات:</h4>
                                                    <ul>
                                                        <li>✅ إدارة المستخدمين (Admin, EnergyAuthority, CompanyOwner, Employee, Technician)</li>
                                                        <li>✅ إدارة الأدوار والصلاحيات</li>
                                                        <li>✅ إدارة المشغلين والمولدات</li>
                                                        <li>✅ إدارة الأرقام المصرح بها</li>
                                                        <li>✅ إرسال الرسائل</li>
                                                        <li>✅ عرض سجل الأخطاء</li>
                                                        <li>❌ لا يمكنه إدارة إعدادات الموقع</li>
                                                        <li>❌ لا يمكنه إنشاء SuperAdmin</li>
                                                    </ul>
                                                </div>
                                                <div class="info-box">
                                                    <h4>آلية العمل:</h4>
                                                    <ol class="mb-0">
                                                        <li>يمكنه إنشاء مستخدمين تحت سلطته</li>
                                                        <li>عند إنشاء Admin/EnergyAuthority/CompanyOwner: نفس آلية السوبر أدمن</li>
                                                        <li>يمكنه إضافة مشغل من خلال الأرقام المصرح بها</li>
                                                        <li>يمكنه تعريف صلاحيات الأدوار</li>
                                                        <li>يمكنه إدارة جميع المشغلين والمولدات</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- المشغل --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#companyOwner">
                                                <span class="badge bg-primary me-2">مشغل</span>
                                                CompanyOwner (المشغل)
                                            </button>
                                        </h2>
                                        <div id="companyOwner" class="accordion-collapse collapse" data-bs-parent="#rolesAccordion">
                                            <div class="accordion-body">
                                                <div class="info-box">
                                                    <h4>الصلاحيات:</h4>
                                                    <ul>
                                                        <li>✅ إدارة ملف المشغل</li>
                                                        <li>✅ إدارة وحدات التوليد التابعة له</li>
                                                        <li>✅ إدارة المولدات التابعة له</li>
                                                        <li>✅ إدارة الموظفين والفنيين التابعين له</li>
                                                        <li>✅ إضافة موظف/فني جديد</li>
                                                        <li>✅ إدارة أسعار التعرفة</li>
                                                        <li>✅ تسجيل سجلات التشغيل والصيانة</li>
                                                        <li>✅ إدارة كفاءة الوقود</li>
                                                        <li>✅ إدارة الامتثال والسلامة</li>
                                                        <li>✅ إدارة الأدوار المخصصة (للموظفين والفنيين)</li>
                                                        <li>❌ لا يمكنه إدارة مشغلين آخرين</li>
                                                        <li>❌ لا يمكنه إدارة إعدادات النظام</li>
                                                    </ul>
                                                </div>
                                                <div class="info-box">
                                                    <h4>آلية العمل:</h4>
                                                    <ol class="mb-0">
                                                        <li><strong>إعداد ملف المشغل:</strong> يجب إكمال بيانات المشغل أولاً</li>
                                                        <li><strong>إضافة وحدات التوليد:</strong> يمكنه إضافة وحدات التوليد التابعة له</li>
                                                        <li><strong>إضافة المولدات:</strong> يمكنه إضافة المولدات لكل وحدة توليد</li>
                                                        <li><strong>إضافة الموظفين:</strong> يمكنه إضافة موظفين وفنيين فقط
                                                            <ul>
                                                                <li>يُدخل الاسم فقط</li>
                                                                <li>يتم توليد username و password تلقائياً</li>
                                                                <li>يتم ربط الموظف تلقائياً بمشغله</li>
                                                            </ul>
                                                        </li>
                                                        <li><strong>تسجيل السجلات:</strong> يمكنه تسجيل سجلات التشغيل والصيانة والامتثال</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- المدير --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#admin">
                                                <span class="badge bg-secondary me-2">مدير</span>
                                                Admin (المدير)
                                            </button>
                                        </h2>
                                        <div id="admin" class="accordion-collapse collapse" data-bs-parent="#rolesAccordion">
                                            <div class="accordion-body">
                                                <div class="info-box">
                                                    <h4>الصلاحيات:</h4>
                                                    <ul>
                                                        <li>✅ عرض المشغلين والمولدات</li>
                                                        <li>✅ عرض وحدات التوليد</li>
                                                        <li>✅ عرض سجلات التشغيل والصيانة</li>
                                                        <li>✅ عرض كفاءة الوقود</li>
                                                        <li>✅ عرض الامتثال والسلامة</li>
                                                        <li>✅ عرض أسعار التعرفة</li>
                                                        <li>✅ عرض المستخدمين</li>
                                                        <li>✅ عرض الصلاحيات</li>
                                                        <li>❌ لا يمكنه إنشاء أو تعديل أي شيء (عرض فقط)</li>
                                                    </ul>
                                                </div>
                                                <div class="info-box">
                                                    <h4>آلية العمل:</h4>
                                                    <p class="text-muted mb-0">
                                                        دور Admin هو دور <strong>عرض فقط</strong> - يمكنه الاطلاع على جميع البيانات ولكن لا يمكنه إجراء أي تعديلات أو إضافات.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- الموظف --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#employee">
                                                <span class="badge bg-success me-2">موظف</span>
                                                Employee (الموظف)
                                            </button>
                                        </h2>
                                        <div id="employee" class="accordion-collapse collapse" data-bs-parent="#rolesAccordion">
                                            <div class="accordion-body">
                                                <div class="info-box">
                                                    <h4>الصلاحيات:</h4>
                                                    <ul>
                                                        <li>✅ عرض بيانات المشغل التابع له</li>
                                                        <li>✅ عرض وحدات التوليد والمولدات</li>
                                                        <li>✅ تسجيل سجلات التشغيل (حسب الصلاحيات الممنوحة)</li>
                                                        <li>✅ تسجيل سجلات الصيانة (حسب الصلاحيات الممنوحة)</li>
                                                        <li>✅ تسجيل كفاءة الوقود (حسب الصلاحيات الممنوحة)</li>
                                                        <li>✅ تسجيل الامتثال والسلامة (حسب الصلاحيات الممنوحة)</li>
                                                        <li>❌ لا يمكنه إدارة المستخدمين</li>
                                                        <li>❌ لا يمكنه إدارة إعدادات المشغل</li>
                                                    </ul>
                                                </div>
                                                <div class="info-box">
                                                    <h4>آلية العمل:</h4>
                                                    <p class="text-muted mb-0">
                                                        الموظف يعمل تحت إشراف المشغل. يمكنه تسجيل السجلات حسب الصلاحيات الممنوحة له من قبل المشغل.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- الفني --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#technician">
                                                <span class="badge bg-warning me-2">فني</span>
                                                Technician (الفني)
                                            </button>
                                        </h2>
                                        <div id="technician" class="accordion-collapse collapse" data-bs-parent="#rolesAccordion">
                                            <div class="accordion-body">
                                                <div class="info-box">
                                                    <h4>الصلاحيات:</h4>
                                                    <ul>
                                                        <li>✅ عرض بيانات المشغل التابع له</li>
                                                        <li>✅ عرض وحدات التوليد والمولدات</li>
                                                        <li>✅ تسجيل سجلات الصيانة (حسب الصلاحيات الممنوحة)</li>
                                                        <li>✅ تسجيل الامتثال والسلامة (حسب الصلاحيات الممنوحة)</li>
                                                        <li>✅ تسجيل كفاءة الوقود (حسب الصلاحيات الممنوحة)</li>
                                                        <li>❌ لا يمكنه إدارة المستخدمين</li>
                                                        <li>❌ لا يمكنه إدارة إعدادات المشغل</li>
                                                    </ul>
                                                </div>
                                                <div class="info-box">
                                                    <h4>آلية العمل:</h4>
                                                    <p class="text-muted mb-0">
                                                        الفني يعمل تحت إشراف المشغل. يركز على تسجيل سجلات الصيانة والامتثال حسب الصلاحيات الممنوحة له.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- آلية إنشاء المستخدمين --}}
                    <div class="guide-section">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-person-plus me-2 text-primary"></i>
                                    آلية إنشاء المستخدمين
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>الدور</th>
                                                <th>يمكنه إنشاء</th>
                                                <th>البيانات المطلوبة</th>
                                                <th>توليد Username</th>
                                                <th>إرسال SMS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><span class="badge bg-danger">SuperAdmin</span></td>
                                                <td>جميع الأدوار</td>
                                                <td>
                                                    <strong>لـ SuperAdmin/Admin/EnergyAuthority/CompanyOwner:</strong><br>
                                                    الاسم، الاسم بالإنجليزي، رقم الجوال، البريد، الدور<br>
                                                    <strong>لـ Employee/Technician:</strong><br>
                                                    الاسم، المشغل
                                                </td>
                                                <td>
                                                    <code>sp_</code> للـ SuperAdmin<br>
                                                    <code>ad_</code> للـ Admin<br>
                                                    <code>ea_</code> للـ EnergyAuthority<br>
                                                    <code>operator_username_employee_name</code> للموظف
                                                </td>
                                                <td>✅ تلقائي</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-info">EnergyAuthority</span></td>
                                                <td>Admin, EnergyAuthority, CompanyOwner, Employee, Technician</td>
                                                <td>نفس SuperAdmin</td>
                                                <td>نفس SuperAdmin</td>
                                                <td>✅ تلقائي</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-primary">CompanyOwner</span></td>
                                                <td>Employee, Technician فقط</td>
                                                <td>الاسم فقط</td>
                                                <td><code>operator_username_employee_name</code></td>
                                                <td>⚠️ إذا كان هناك رقم جوال</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-secondary">Admin</span></td>
                                                <td>❌ لا شيء</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>-</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="alert alert-info mt-3 mb-0">
                                    <h6 class="alert-heading mb-2">
                                        <i class="bi bi-info-circle me-2"></i>
                                        ملاحظات مهمة:
                                    </h6>
                                    <ul class="mb-0">
                                        <li>يتم توليد <code>username</code> و <code>password</code> تلقائياً عند إنشاء مستخدم جديد</li>
                                        <li>يتم إرسال SMS تلقائياً يحتوي على بيانات الدخول والرابط</li>
                                        <li>يتم ربط الموظف/الفني تلقائياً بالمشغل</li>
                                        <li>يتم توليد email تلقائياً إذا لم يُدخل: <code>username@rased.local</code></li>
                                        <li>عند إنشاء CompanyOwner: يجب أن يكون رقم المشغل موجود في الأرقام المصرح بها</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- خطوات العمل للمشغل --}}
                    <div class="guide-section">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-list-check me-2 text-primary"></i>
                                    خطوات العمل للمشغل
                                </h5>
                            </div>
                            <div class="card-body">
                                <ol class="steps-list">
                                    <div class="step-item">
                                        <h4>الخطوة 1: إعداد ملف المشغل</h4>
                                        <p class="text-muted mb-0">اذهب إلى <strong>المشغل → ملف المشغل</strong> وأكمل جميع البيانات المطلوبة</p>
                                    </div>
                                    <div class="step-item">
                                        <h4>الخطوة 2: إضافة وحدات التوليد</h4>
                                        <p class="text-muted mb-0">اذهب إلى <strong>وحدات التوليد</strong> وأضف وحدات التوليد التابعة لك</p>
                                    </div>
                                    <div class="step-item">
                                        <h4>الخطوة 3: إضافة المولدات</h4>
                                        <p class="text-muted mb-0">اذهب إلى <strong>المولدات</strong> وأضف المولدات لكل وحدة توليد</p>
                                    </div>
                                    <div class="step-item">
                                        <h4>الخطوة 4: إضافة الموظفين والفنيين</h4>
                                        <p class="text-muted mb-0">اذهب إلى <strong>المشغل → إضافة موظف/فني</strong> وأضف موظفيك وفنييك</p>
                                    </div>
                                    <div class="step-item">
                                        <h4>الخطوة 5: تسجيل السجلات</h4>
                                        <p class="text-muted mb-0">ابدأ بتسجيل سجلات التشغيل والصيانة والامتثال</p>
                                    </div>
                                </ol>
                            </div>
                        </div>
                    </div>

                    {{-- روابط مفيدة --}}
                    <div class="guide-section">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-link-45deg me-2 text-primary"></i>
                                    روابط مفيدة
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 links-section">
                                        <h6 class="fw-semibold mb-3">للمشغل:</h6>
                                        <ul>
                                            <li><a href="{{ route('admin.operators.profile') }}"><i class="bi bi-arrow-left"></i> ملف المشغل</a></li>
                                            <li><a href="{{ route('admin.generation-units.index') }}"><i class="bi bi-arrow-left"></i> وحدات التوليد</a></li>
                                            <li><a href="{{ route('admin.generators.index') }}"><i class="bi bi-arrow-left"></i> المولدات</a></li>
                                            <li><a href="{{ route('admin.users.create') }}"><i class="bi bi-arrow-left"></i> إضافة موظف/فني</a></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6 links-section">
                                        <h6 class="fw-semibold mb-3">للسوبر أدمن وسلطة الطاقة:</h6>
                                        <ul>
                                            <li><a href="{{ route('admin.users.index') }}"><i class="bi bi-arrow-left"></i> المستخدمون</a></li>
                                            <li><a href="{{ route('admin.operators.index') }}"><i class="bi bi-arrow-left"></i> المشغلون</a></li>
                                            <li><a href="{{ route('admin.roles.index') }}"><i class="bi bi-arrow-left"></i> الأدوار</a></li>
                                            <li><a href="{{ route('admin.permissions.index') }}"><i class="bi bi-arrow-left"></i> الصلاحيات</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
