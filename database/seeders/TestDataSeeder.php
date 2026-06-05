<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Dog;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        // ─── Owners & Dogs ─────────────────────────────────────────────────────
        $data = [
            [
                'user'  => ['name' => 'Ana Ferreira',    'email' => 'ana@testecaudafeliz.com'],
                'owner' => ['phone' => '912 111 001'],
                'dogs'  => [
                    ['name' => 'Loki',   'breed' => 'Golden Retriever', 'birthdate' => '2020-03-15'],
                    ['name' => 'Mia',    'breed' => 'Beagle',            'birthdate' => '2021-07-22'],
                ],
            ],
            [
                'user'  => ['name' => 'Bruno Costa',     'email' => 'bruno@testecaudafeliz.com'],
                'owner' => ['phone' => '913 222 002'],
                'dogs'  => [
                    ['name' => 'Rex',    'breed' => 'Pastor Alemão',     'birthdate' => '2019-11-05'],
                ],
            ],
            [
                'user'  => ['name' => 'Carla Mendes',    'email' => 'carla@testecaudafeliz.com'],
                'owner' => ['phone' => '914 333 003'],
                'dogs'  => [
                    ['name' => 'Bella',  'breed' => 'Labrador',          'birthdate' => '2022-01-10'],
                    ['name' => 'Simba',  'breed' => 'Shih Tzu',          'birthdate' => '2020-09-30'],
                ],
            ],
            [
                'user'  => ['name' => 'Diogo Oliveira',  'email' => 'diogo@testecaudafeliz.com'],
                'owner' => ['phone' => '915 444 004'],
                'dogs'  => [
                    ['name' => 'Bolt',   'breed' => 'Dálmata',           'birthdate' => '2021-04-18'],
                ],
            ],
            [
                'user'  => ['name' => 'Eva Santos',      'email' => 'eva@testecaudafeliz.com'],
                'owner' => ['phone' => '916 555 005'],
                'dogs'  => [
                    ['name' => 'Luna',   'breed' => 'Husky Siberiano',   'birthdate' => '2023-02-14'],
                    ['name' => 'Thor',   'breed' => 'Rottweiler',        'birthdate' => '2019-08-20'],
                ],
            ],
            [
                'user'  => ['name' => 'Filipe Rodrigues', 'email' => 'filipe@testecaudafeliz.com'],
                'owner' => ['phone' => '917 666 006'],
                'dogs'  => [
                    ['name' => 'Max',    'breed' => 'Border Collie',     'birthdate' => '2022-06-01'],
                ],
            ],
            [
                'user'  => ['name' => 'Gisela Pereira',  'email' => 'gisela@testecaudafeliz.com'],
                'owner' => ['phone' => '918 777 007'],
                'dogs'  => [
                    ['name' => 'Buddy',  'breed' => 'Poodle',            'birthdate' => '2021-12-25'],
                    ['name' => 'Kira',   'breed' => 'Boxer',             'birthdate' => '2020-05-11'],
                ],
            ],
        ];

        $owners = [];
        foreach ($data as $d) {
            $user = User::firstOrCreate(
                ['email' => $d['user']['email']],
                ['name' => $d['user']['name'], 'password' => $password, 'role' => 'owner']
            );

            $owner = Owner::firstOrCreate(
                ['user_id' => $user->id],
                ['name' => $d['user']['name'], 'email' => $d['user']['email'], 'phone' => $d['owner']['phone']]
            );

            $dogs = [];
            foreach ($d['dogs'] as $dogData) {
                $dogs[] = Dog::firstOrCreate(
                    ['owner_id' => $owner->id, 'name' => $dogData['name']],
                    ['breed' => $dogData['breed'], 'birthdate' => $dogData['birthdate']]
                );
            }

            $owners[] = ['owner' => $owner, 'dogs' => $dogs];
        }

        // ─── Bookings ──────────────────────────────────────────────────────────
        // Helper
        $book = function (Owner $owner, Dog $dog, array $attrs) {
            Booking::firstOrCreate(
                ['owner_id' => $owner->id, 'dog_id' => $dog->id, 'type' => $attrs['type'], 'start_date' => $attrs['start_date']],
                array_merge([
                    'status'     => 'aprovado',
                    'is_regular' => true,
                    'pet_taxi'   => false,
                    'subtype'    => null,
                    'frequency'  => null,
                    'end_date'   => null,
                    'notes'      => null,
                ], $attrs)
            );
        };

        // Ana — Loki (ATL semanal + pet taxi)
        $book($owners[0]['owner'], $owners[0]['dogs'][0], [
            'type'       => 'atl',
            'start_date' => now()->startOfWeek()->toDateString(),
            'frequency'  => 'semanal',
            'pet_taxi'   => true,
            'is_regular' => true,
        ]);

        // Ana — Mia (ATL semanal sem taxi)
        $book($owners[0]['owner'], $owners[0]['dogs'][1], [
            'type'       => 'atl',
            'start_date' => now()->startOfWeek()->addDay()->toDateString(),
            'frequency'  => 'semanal',
            'pet_taxi'   => false,
            'is_regular' => true,
        ]);

        // Bruno — Rex (ATL semanal + pet taxi, não regular)
        $book($owners[1]['owner'], $owners[1]['dogs'][0], [
            'type'       => 'atl',
            'start_date' => now()->startOfWeek()->toDateString(),
            'frequency'  => 'semanal',
            'pet_taxi'   => true,
            'is_regular' => false,
        ]);

        // Carla — Bella (ATL quinzenal, sem taxi)
        $book($owners[2]['owner'], $owners[2]['dogs'][0], [
            'type'       => 'atl',
            'start_date' => now()->startOfWeek()->addDays(2)->toDateString(),
            'frequency'  => 'quinzenal',
            'pet_taxi'   => false,
            'is_regular' => true,
        ]);

        // Carla — Simba (ATL semanal, pet taxi)
        $book($owners[2]['owner'], $owners[2]['dogs'][1], [
            'type'       => 'atl',
            'start_date' => now()->startOfWeek()->addDays(3)->toDateString(),
            'frequency'  => 'semanal',
            'pet_taxi'   => true,
            'is_regular' => true,
        ]);

        // Diogo — Bolt (Hotel esta semana)
        $book($owners[3]['owner'], $owners[3]['dogs'][0], [
            'type'       => 'hotel',
            'start_date' => now()->startOfWeek()->addDays(1)->toDateString(),
            'end_date'   => now()->startOfWeek()->addDays(4)->toDateString(),
            'is_regular' => true,
        ]);

        // Eva — Luna (Hotel: só esta semana, entrada hoje saída 3 dias depois)
        $book($owners[4]['owner'], $owners[4]['dogs'][0], [
            'type'       => 'hotel',
            'start_date' => now()->startOfWeek()->addDays(3)->toDateString(),
            'end_date'   => now()->startOfWeek()->addDays(6)->toDateString(),
            'is_regular' => false,
        ]);

        // Eva — Thor (Treino individual semanal)
        $book($owners[4]['owner'], $owners[4]['dogs'][1], [
            'type'       => 'aula',
            'subtype'    => 'individual',
            'start_date' => now()->startOfWeek()->addDays(1)->toDateString(),
            'frequency'  => 'semanal',
        ]);

        // Filipe — Max (Treino domicílio quinzenal)
        $book($owners[5]['owner'], $owners[5]['dogs'][0], [
            'type'       => 'aula',
            'subtype'    => 'domicilio',
            'start_date' => now()->startOfWeek()->addDays(3)->toDateString(),
            'frequency'  => 'quinzenal',
        ]);

        // Filipe — Max (Integração semanal)
        $book($owners[5]['owner'], $owners[5]['dogs'][0], [
            'type'       => 'integracao',
            'start_date' => now()->startOfWeek()->addDays(5)->toDateString(),
            'frequency'  => 'semanal',
        ]);

        // Gisela — Buddy (Aula grupo semanal)
        $book($owners[6]['owner'], $owners[6]['dogs'][0], [
            'type'       => 'aula',
            'subtype'    => 'grupo',
            'start_date' => now()->startOfWeek()->addDays(2)->toDateString(),
            'frequency'  => 'semanal',
        ]);

        // Gisela — Kira (Pack Creche 10 sessões, esta semana)
        $book($owners[6]['owner'], $owners[6]['dogs'][1], [
            'type'       => 'pack_creche',
            'subtype'    => '10',
            'start_date' => now()->startOfWeek()->addDays(1)->toDateString(),
        ]);

        // Carla — Bella (Dog Walking esta semana)
        $book($owners[2]['owner'], $owners[2]['dogs'][0], [
            'type'       => 'dog_walking',
            'start_date' => now()->startOfWeek()->addDays(2)->toDateString(),
        ]);

        // Ana — Loki (Banho esta semana)
        $book($owners[0]['owner'], $owners[0]['dogs'][0], [
            'type'       => 'banho',
            'start_date' => now()->startOfWeek()->addDays(4)->toDateString(),
        ]);

        // Eva — Luna (Pet Sitting esta semana)
        $book($owners[4]['owner'], $owners[4]['dogs'][0], [
            'type'       => 'pet_sitting',
            'start_date' => now()->startOfWeek()->addDays(5)->toDateString(),
        ]);

        // Avaliação comportamental
        $book($owners[1]['owner'], $owners[1]['dogs'][0], [
            'type'       => 'aula',
            'subtype'    => 'avaliacao_comportamental',
            'start_date' => now()->startOfWeek()->addDays(4)->toDateString(),
            'frequency'  => 'mensal',
        ]);

        // ─── Clientes antigos (não regulares — último serviço há +3 meses) ────────
        $oldClients = [
            [
                'user'  => ['name' => 'Hugo Martins',    'email' => 'hugo@testecaudafeliz.com'],
                'owner' => ['phone' => '919 888 008'],
                'dogs'  => [['name' => 'Pipas', 'breed' => 'Bulldog Francês', 'birthdate' => '2019-05-10']],
            ],
            [
                'user'  => ['name' => 'Inês Sousa',      'email' => 'ines@testecaudafeliz.com'],
                'owner' => ['phone' => '910 999 009'],
                'dogs'  => [
                    ['name' => 'Duque', 'breed' => 'Cocker Spaniel', 'birthdate' => '2018-11-20'],
                    ['name' => 'Nina',  'breed' => 'Yorkshire',      'birthdate' => '2020-03-08'],
                ],
            ],
            [
                'user'  => ['name' => 'Jorge Fonseca',   'email' => 'jorge@testecaudafeliz.com'],
                'owner' => ['phone' => '911 000 010'],
                'dogs'  => [['name' => 'Tito', 'breed' => 'Dachshund', 'birthdate' => '2021-07-15']],
            ],
        ];

        $oldOwners = [];
        foreach ($oldClients as $d) {
            $user = User::firstOrCreate(
                ['email' => $d['user']['email']],
                ['name' => $d['user']['name'], 'password' => $password, 'role' => 'owner']
            );
            $owner = Owner::firstOrCreate(
                ['user_id' => $user->id],
                ['name' => $d['user']['name'], 'email' => $d['user']['email'], 'phone' => $d['owner']['phone']]
            );
            $dogs = [];
            foreach ($d['dogs'] as $dogData) {
                $dogs[] = Dog::firstOrCreate(
                    ['owner_id' => $owner->id, 'name' => $dogData['name']],
                    ['breed' => $dogData['breed'], 'birthdate' => $dogData['birthdate']]
                );
            }
            $oldOwners[] = ['owner' => $owner, 'dogs' => $dogs];
        }

        // Old bookings (5+ months ago) → estes cães ficam não regulares
        $bookOld = function (Owner $owner, Dog $dog, array $attrs) {
            Booking::firstOrCreate(
                ['owner_id' => $owner->id, 'dog_id' => $dog->id, 'type' => $attrs['type'], 'start_date' => $attrs['start_date']],
                array_merge([
                    'status'     => 'aprovado',
                    'is_regular' => false,
                    'pet_taxi'   => false,
                    'subtype'    => null,
                    'frequency'  => null,
                    'end_date'   => null,
                    'notes'      => null,
                ], $attrs)
            );
        };

        // Hugo — Pipas (ATL, parou há 5 meses)
        $bookOld($oldOwners[0]['owner'], $oldOwners[0]['dogs'][0], [
            'type'       => 'atl',
            'start_date' => now()->subMonths(5)->toDateString(),
            'frequency'  => 'semanal',
            'is_regular' => false,
        ]);

        // Inês — Duque (Hotel, há 4 meses)
        $bookOld($oldOwners[1]['owner'], $oldOwners[1]['dogs'][0], [
            'type'       => 'hotel',
            'start_date' => now()->subMonths(4)->toDateString(),
            'end_date'   => now()->subMonths(4)->addDays(3)->toDateString(),
            'is_regular' => false,
        ]);

        // Inês — Nina (Treino, há 6 meses)
        $bookOld($oldOwners[1]['owner'], $oldOwners[1]['dogs'][1], [
            'type'       => 'aula',
            'subtype'    => 'individual',
            'start_date' => now()->subMonths(6)->toDateString(),
            'frequency'  => 'quinzenal',
            'is_regular' => false,
        ]);

        // Jorge — Tito (ATL + pet taxi, parou há 4 meses e meio)
        $bookOld($oldOwners[2]['owner'], $oldOwners[2]['dogs'][0], [
            'type'       => 'atl',
            'start_date' => now()->subWeeks(19)->toDateString(),
            'frequency'  => 'semanal',
            'pet_taxi'   => true,
            'is_regular' => false,
        ]);

        $this->command->info('Test data seeded: 10 owners, 16 dogs, 19 bookings (incl. 4 non-regular).');
    }
}
