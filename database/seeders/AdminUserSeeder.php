<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@fsl.com'],
            [
                'name'     => 'Admin FSL',
                'email'    => 'admin@fsl.com',
                'password' => \Illuminate\Support\Facades\Hash::make('fsl@admin2024'),
            ]
        );
    }
}
