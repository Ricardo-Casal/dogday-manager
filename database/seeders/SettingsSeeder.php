<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'pet_taxi',    'label' => 'Pet Taxi (recolha)',  'value' => 5.00],
            ['key' => 'hotel_noite', 'label' => 'Hotel (por noite)',   'value' => 12.00],
            ['key' => 'atl',         'label' => 'ATL (por dia)',       'value' => 8.00],
            ['key' => 'aula',        'label' => 'Aula (por sessão)',   'value' => 15.00],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
