<?php

namespace App;

enum Governorate: int
{
    case Gaza = 10;
    case Middle = 20;
    case KhanYunis = 30;
    case Rafah = 40;

    /**
     * الحصول على اسم المحافظة بالعربية
     */
    public function label(): string
    {
        return match ($this) {
            self::Gaza => 'غزة',
            self::Middle => 'الوسطى',
            self::KhanYunis => 'خانيونس',
            self::Rafah => 'رفح',
        };
    }

    /**
     * الحصول على ترميز المحافظة
     */
    public function code(): string
    {
        return match ($this) {
            self::Gaza => 'GAZ',
            self::Middle => 'MID',
            self::KhanYunis => 'KHU',
            self::Rafah => 'RAF',
        };
    }

    /**
     * الحصول على جميع المحافظات
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * الحصول على المحافظة من الرقم
     */
    public static function fromValue(int $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * الحصول على تفاصيل المحافظة
     */
    public function details(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'code' => $this->code(),
        ];
    }
}
