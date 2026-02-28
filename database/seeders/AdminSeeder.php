<?php

namespace Database\Seeders;

use App\Models\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        UserModel::firstOrCreate(
            ['email' => 'admin@ordem.dev'],
            [
                'id'         => Str::uuid()->toString(),
                'name'       => 'Admin',
                'surname'    => 'Sistema',
                'birth_date' => '1990-01-01',
                'password'   => Hash::make((string) env('ADMIN_SEEDER_PASSWORD', 'Admin2026!')),
                'role'       => 'admin',
            ]
        );

        $this->command->info('✔ Admin user criado/verificado: admin@ordem.dev');
    }
}
