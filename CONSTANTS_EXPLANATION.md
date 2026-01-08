# شرح نظام الثوابت (Constants System)

## نظرة عامة

نظام الثوابت مبني على هيكل **Master-Detail** (أساسي-تفصيلي) يسمح بإدارة قوائم القيم الثابتة في النظام بطريقة مرنة وقابلة للتوسع.

---

## هيكل قاعدة البيانات

### 1. جدول `constant_masters` (الثوابت الأساسية)

هذا الجدول يحتوي على **أنواع الثوابت** المختلفة. كل ثابت له رقم فريد.

**الحقول:**
- `id`: المعرف الفريد
- `constant_number`: رقم الثابت (فريد) - مثال: 1, 2, 3, 17, 18...
- `constant_name`: اسم الثابت بالعربية - مثال: "المحافظات", "نوع الصيانة"
- `description`: وصف الثابت
- `is_active`: هل الثابت نشط (true/false)
- `order`: ترتيب العرض

**أمثلة على الثوابت الأساسية:**
```
constant_number: 1  → constant_name: "المحافظات"
constant_number: 12 → constant_name: "نوع الصيانة"
constant_number: 17 → constant_name: "مقارنة كفاءة الوقود"
constant_number: 18 → constant_name: "مقارنة كفاءة الطاقة"
constant_number: 21 → constant_name: "موقع الخزان"
```

---

### 2. جدول `constant_details` (تفاصيل الثوابت)

هذا الجدول يحتوي على **القيم الفعلية** لكل ثابت أساسي. كل تفصيل ينتمي إلى ثابت أساسي واحد.

**الحقول:**
- `id`: المعرف الفريد (هذا الـ ID هو الذي نخزنه في الجداول الأخرى)
- `constant_master_id`: معرف الثابت الأساسي (Foreign Key إلى `constant_masters`)
- `parent_detail_id`: معرف التفصيل الأب (للهيكل الهرمي - اختياري)
- `label`: النص المعروض - مثال: "صيانة دورية", "ضمن المعدل"
- `code`: الترميز - مثال: "PERIODIC", "WITHIN_RANGE"
- `value`: القيمة الرقمية/النصية - مثال: "1201", "1701"
- `notes`: ملاحظات
- `is_active`: هل التفصيل نشط
- `order`: ترتيب العرض

**أمثلة:**

لثابت "نوع الصيانة" (رقم 12):
```
id: 45 | constant_master_id: 12 | label: "صيانة دورية" | code: "PERIODIC" | value: "1201"
id: 46 | constant_master_id: 12 | label: "صيانة طارئة" | code: "EMERGENCY" | value: "1202"
```

لثابت "مقارنة كفاءة الوقود" (رقم 17):
```
id: 50 | constant_master_id: 17 | label: "ضمن المعدل" | code: "WITHIN_RANGE" | value: "1701"
id: 51 | constant_master_id: 17 | label: "اعلى من المعدل" | code: "ABOVE_RANGE" | value: "1702"
id: 52 | constant_master_id: 17 | label: "اقل من المعدل" | code: "BELOW_RANGE" | value: "1703"
```

---

## كيفية التخزين في الجداول الأخرى

### المبدأ الأساسي:
**نحن نخزن `id` من جدول `constant_details` وليس `constant_master_id`**

### مثال من جدول `fuel_efficiencies`:

```php
// في قاعدة البيانات:
fuel_efficiency_comparison_id = 50  // هذا ID من constant_details

// في الكود (Model):
protected $fillable = [
    'fuel_efficiency_comparison_id', // ID من constant_details - ثابت Master رقم 17
];

// العلاقة:
public function fuelEfficiencyComparisonDetail(): BelongsTo
{
    return $this->belongsTo(ConstantDetail::class, 'fuel_efficiency_comparison_id');
}
```

**ملاحظة مهمة:** 
- `fuel_efficiency_comparison_id` يجب أن يكون `id` من `constant_details` حيث `constant_master_id = 17`
- عند الإدخال، نختار قيمة من القائمة التي تحتوي على تفاصيل الثابت رقم 17 فقط

---

## الهيكل الهرمي (Hierarchical)

بعض الثوابت تدعم الهيكل الهرمي باستخدام `parent_detail_id`.

### مثال: المدن والمحافظات

```
1. constant_masters: constant_number = 1 (المحافظات)
   ├── constant_details: id=1, label="شمال غزة", code="NG"
   ├── constant_details: id=2, label="غزة", code="GZ"
   └── constant_details: id=3, label="الوسطى", code="MD"

2. constant_masters: constant_number = 20 (المدينة)
   ├── constant_details: id=10, parent_detail_id=1, label="بيت حانون" (ينتمي لشمال غزة)
   ├── constant_details: id=11, parent_detail_id=1, label="بيت لاهيا" (ينتمي لشمال غزة)
   └── constant_details: id=20, parent_detail_id=2, label="غزة" (ينتمي لغزة)
```

---

## كيفية الاستخدام في الكود

### 1. استخدام Helper Class:

```php
use App\Helpers\ConstantsHelper;

// الحصول على جميع تفاصيل ثابت معين
$maintenanceTypes = ConstantsHelper::get(12); // نوع الصيانة

// الحصول على تفصيل واحد بواسطة Code
$periodicType = ConstantsHelper::findByCode(12, 'PERIODIC');

// الحصول على تفصيل واحد بواسطة ID
$detail = ConstantsHelper::find(12, 45);
```

### 2. استخدام العلاقات في Models:

```php
// في FuelEfficiency Model
$fuelEfficiency = FuelEfficiency::find(1);

// الحصول على تفصيل مقارنة كفاءة الوقود
$comparison = $fuelEfficiency->fuelEfficiencyComparisonDetail;
echo $comparison->label; // "ضمن المعدل"
echo $comparison->code;  // "WITHIN_RANGE"
echo $comparison->value; // "1701"

// الوصول للثابت الأساسي
$master = $comparison->master;
echo $master->constant_name; // "مقارنة كفاءة الوقود"
```

### 3. في Controllers و Views:

```php
// في Controller
$maintenanceTypes = ConstantsHelper::get(12); // للحصول على قائمة أنواع الصيانة

// في Blade View
<select name="maintenance_type_id">
    @foreach(ConstantsHelper::get(12) as $type)
        <option value="{{ $type->id }}">{{ $type->label }}</option>
    @endforeach
</select>
```

---

## قائمة الثوابت الأساسية في النظام

| رقم الثابت | اسم الثابت | الاستخدام |
|------------|------------|-----------|
| 1 | المحافظات | قائمة المحافظات في فلسطين |
| 2 | جهة التشغيل | نفس المالك / طرف آخر |
| 3 | حالة المولد | فعال / غير فعال |
| 4 | نوع المحرك | Perkins, Volvo, Caterpillar... |
| 5 | نظام الحقن | ميكانيكي / الكتروني / هجين |
| 6 | مؤشر القياس | متوفر / غير متوفر... |
| 7 | الحالة الفنية | ممتازة / جيدة / متوسطة... |
| 8 | نوع لوحة التحكم | Deep Sea, ComAp, Datakom... |
| 9 | حالة لوحة التحكم | تعمل / لا تعمل... |
| 10 | مادة التصنيع | حديد / بلاستيك مقوى / فايبر |
| 11 | الاستخدام | مركزي / احتياطي |
| 12 | نوع الصيانة | صيانة دورية / صيانة طارئة |
| 13 | حالة شهادة السلامة | صالحة / منتهية / غير موجودة |
| 14 | حالة الامتثال البيئي | ملتزم / غير ملتزم... |
| 15 | حالة الوحدة | فعال / غير فعال |
| 16 | إمكانية المزامنة | متوفرة / غير متوفرة |
| **17** | **مقارنة كفاءة الوقود** | **ضمن المعدل / اعلى / اقل** |
| **18** | **مقارنة كفاءة الطاقة** | **ضمن المعدل / اعلى / اقل** |
| 19 | طريقة القياس | سيخ مدرج / ساعة ميكانيكية... |
| 20 | المدينة | قائمة المدن (مرتبطة بالمحافظات) |
| 21 | موقع الخزان | أرضي / علوي / تحت الأرض |

---

## مثال شامل: جدول FuelEfficiency

### في Migration:
```php
// تخزن ID من constant_details
$table->foreignId('fuel_efficiency_comparison_id')->nullable()
    ->constrained('constant_details')->nullOnDelete()
    ->comment('ID من constant_details - ثابت Master رقم 17');
```

### في Model:
```php
protected $fillable = [
    'fuel_efficiency_comparison_id', // ID من constant_details - ثابت Master رقم 17
];

public function fuelEfficiencyComparisonDetail(): BelongsTo
{
    return $this->belongsTo(ConstantDetail::class, 'fuel_efficiency_comparison_id');
}
```

### في Controller (عند الإدخال):
```php
// الحصول على القائمة للعرض في الفورم
$fuelEfficiencyOptions = ConstantsHelper::get(17);

// التحقق من صحة الإدخال
$validated = $request->validate([
    'fuel_efficiency_comparison_id' => 'required|exists:constant_details,id',
]);

// التأكد من أن الـ ID ينتمي للثابت الصحيح
$detail = ConstantDetail::find($request->fuel_efficiency_comparison_id);
if ($detail && $detail->constant_master_id != 17) {
    // خطأ: هذا ID لا ينتمي لثابت مقارنة كفاءة الوقود
}
```

### في View (عند العرض):
```php
{{ $fuelEfficiency->fuelEfficiencyComparisonDetail->label }} 
// يطبع: "ضمن المعدل" أو "اعلى من المعدل" أو "اقل من المعدل"

// مع badge ملون
<span class="badge badge-{{ $fuelEfficiency->fuelEfficiencyComparisonDetail->getBadgeColor() }}">
    {{ $fuelEfficiency->fuelEfficiencyComparisonDetail->label }}
</span>
```

---

## Cache System

النظام يستخدم Cache لتحسين الأداء:

```php
// الكاش يتم مسحه تلقائياً عند تحديث الثوابت
ConstantsHelper::clearCache(17); // مسح كاش ثابت معين
ConstantsHelper::clearCache();   // مسح كل الكاش
```

---

## ملخص سريع

1. **ConstantMaster**: يحتوي على أنواع الثوابت (المحافظات، نوع الصيانة، إلخ)
2. **ConstantDetail**: يحتوي على القيم الفعلية لكل ثابت (صيانة دورية، صيانة طارئة)
3. **التخزين**: نخزن `id` من `constant_details` في الجداول الأخرى
4. **الاستخدام**: نستخدم Helper أو Relations للوصول للقيم
5. **التحقق**: يجب التأكد من أن الـ ID ينتمي للثابت الصحيح

---

## مثال عملي كامل

```php
// 1. إنشاء سجل جديد في fuel_efficiencies
$fuelEfficiency = new FuelEfficiency();
$fuelEfficiency->generator_id = 5;
$fuelEfficiency->consumption_date = '2025-01-15';

// الحصول على تفصيل "ضمن المعدل" من ثابت 17
$withinRange = ConstantsHelper::findByCode(17, 'WITHIN_RANGE');
$fuelEfficiency->fuel_efficiency_comparison_id = $withinRange->id; // تخزين ID
$fuelEfficiency->save();

// 2. قراءة السجل لاحقاً
$record = FuelEfficiency::find(1);
$comparison = $record->fuelEfficiencyComparisonDetail;
echo $comparison->label; // "ضمن المعدل"
echo $comparison->master->constant_name; // "مقارنة كفاءة الوقود"
```

