<?php

namespace Database\Seeders;

use App\Models\SmsTemplate;
use Illuminate\Database\Seeder;

class SmsTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'key' => 'user_credentials',
                'name' => 'رسالة بيانات الدخول للمستخدم',
                'template' => "مرحباً {name}،\nتم تسجيلك على منصة راصد.\nالدور: {role}\nاسم المستخدم: {username}\nكلمة المرور: {password}\nرابط الدخول: {login_url}",
                'max_length' => 160,
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            SmsTemplate::updateOrCreate(
                ['key' => $template['key']],
                $template
            );
        }

        $this->command->info('تم إنشاء/تحديث قوالب SMS بنجاح!');
    }
}
