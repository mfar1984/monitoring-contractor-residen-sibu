<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultLanguages = [
            [
                'code' => 'en',
                'name' => 'English',
                'is_default' => true,
                'status' => 'Active'
            ],
            [
                'code' => 'ms',
                'name' => 'Bahasa Melayu',
                'is_default' => true,
                'status' => 'Active'
            ],
            [
                'code' => 'zh',
                'name' => '中文 (Chinese)',
                'is_default' => true,
                'status' => 'Active'
            ]
        ];

        foreach ($defaultLanguages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }
    }
}
