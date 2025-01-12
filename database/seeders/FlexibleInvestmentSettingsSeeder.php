<?php

namespace Database\Seeders;

use App\Models\FlexibleInvestmentSettings;
use Illuminate\Database\Seeder;

class FlexibleInvestmentSettingsSeeder extends Seeder
{
    public function run(): void
    {
        FlexibleInvestmentSettings::create([
            'minimum_deposit' => 100.00,
            'maximum_deposit' => 100000.00,
            'daily_profit_percentage' => 0.5, // 0.5% daily
            'is_active' => true
        ]);
    }
}