<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IntegrationSetting;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bahasa Melayu Translations
        $malayTranslations = [
            'overview' => 'Gambaran Keseluruhan',
            'project' => 'Projek',
            'pre_project' => 'Pra Projek',
            'contractor_analysis' => 'Analisis Kontraktor',
            'system_settings' => 'Tetapan Sistem',
            'general' => 'Umum',
            'master_data' => 'Data Induk',
            'group_roles' => 'Kumpulan Peranan',
            'users_id' => 'ID Pengguna',
            'integrations' => 'Integrasi',
            'activity_log' => 'Log Aktiviti',
        ];

        foreach ($malayTranslations as $key => $value) {
            IntegrationSetting::updateOrCreate(
                [
                    'type' => 'translation_ms',
                    'key' => $key
                ],
                [
                    'value' => $value
                ]
            );
        }

        // Chinese Translations
        $chineseTranslations = [
            'overview' => '概览',
            'project' => '项目',
            'pre_project' => '预项目',
            'contractor_analysis' => '承包商分析',
            'system_settings' => '系统设置',
            'general' => '常规',
            'master_data' => '主数据',
            'group_roles' => '组角色',
            'users_id' => '用户ID',
            'integrations' => '集成',
            'activity_log' => '活动日志',
        ];

        foreach ($chineseTranslations as $key => $value) {
            IntegrationSetting::updateOrCreate(
                [
                    'type' => 'translation_zh',
                    'key' => $key
                ],
                [
                    'value' => $value
                ]
            );
        }

        // English Translations (same as original)
        $englishTranslations = [
            'overview' => 'Overview',
            'project' => 'Project',
            'pre_project' => 'Pre Project',
            'contractor_analysis' => 'Contractor Analysis',
            'system_settings' => 'System Settings',
            'general' => 'General',
            'master_data' => 'Master Data',
            'group_roles' => 'Group Roles',
            'users_id' => 'Users ID',
            'integrations' => 'Integrations',
            'activity_log' => 'Activity Log',
        ];

        foreach ($englishTranslations as $key => $value) {
            IntegrationSetting::updateOrCreate(
                [
                    'type' => 'translation_en',
                    'key' => $key
                ],
                [
                    'value' => $value
                ]
            );
        }

        $this->command->info('Translations seeded successfully!');
    }
}
