<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Base services
            ['key' => 'pet_taxi',                 'label' => 'Pet Taxi (ida e volta)',              'value' => 4.00],
            ['key' => 'atl',                      'label' => 'Creche (por dia) — Regular',          'value' => 12.00],
            ['key' => 'atl_nao_regular',          'label' => 'Creche (por dia) — Não Regular',      'value' => 14.00],
            ['key' => 'hotel_noite',              'label' => 'Estadia (por noite) — Regular',       'value' => 15.00],
            ['key' => 'hotel_noite_nao_regular',  'label' => 'Estadia (por noite) — Não Regular',  'value' => 17.50],
            ['key' => 'integracao',               'label' => 'Integração (por sessão)',             'value' => 14.00],

            // Treino
            ['key' => 'aula',                     'label' => 'Treino Individual (nas instalações)', 'value' => 17.50],
            ['key' => 'aula_domicilio',           'label' => 'Treino a Domicílio',                 'value' => 25.00],
            ['key' => 'aula_grupo',               'label' => 'Treino em Grupo',                    'value' => 15.00],
            ['key' => 'avaliacao_comportamental', 'label' => 'Avaliação Comportamental',           'value' => 30.00],

            // Outros serviços (sob consulta — manager define o preço)
            ['key' => 'pet_sitting',  'label' => 'Pet Sitting (por dia)',  'value' => 0.00],
            ['key' => 'dog_walking',  'label' => 'Dog Walking (por passeio)', 'value' => 0.00],
            ['key' => 'banho',        'label' => 'Banho e Tosquia',        'value' => 0.00],

            // Packs mensais de creche
            ['key' => 'pack_4',  'label' => 'Pack Creche — 4 sessões',  'value' => 40.00],
            ['key' => 'pack_5',  'label' => 'Pack Creche — 5 sessões',  'value' => 50.00],
            ['key' => 'pack_6',  'label' => 'Pack Creche — 6 sessões',  'value' => 60.00],
            ['key' => 'pack_8',  'label' => 'Pack Creche — 8 sessões',  'value' => 80.00],
            ['key' => 'pack_10', 'label' => 'Pack Creche — 10 sessões', 'value' => 100.00],
            ['key' => 'pack_12', 'label' => 'Pack Creche — 12 sessões', 'value' => 120.00],
            ['key' => 'pack_15', 'label' => 'Pack Creche — 15 sessões', 'value' => 150.00],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
