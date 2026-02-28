<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        UserModel::firstOrCreate(
            ['email' => 'customer@ordem.dev'],
            [
                'id'         => Str::uuid()->toString(),
                'name'       => 'Cliente',
                'surname'    => 'Teste',
                'birth_date' => '1995-06-15',
                'password'   => Hash::make('Customer2026'),
                'role'       => 'customer',
            ]
        );

        $this->command->info('✔ Customer user criado/verificado: customer@ordem.dev');
    }
}
