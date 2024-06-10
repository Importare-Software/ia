<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Alexis',
                'email' => 'alexis@importare.mx',
                'password' => Hash::make('secret'),
            ],
            [
                'name' => 'jorge',
                'email' => 'jorge@importare.mx',
                'password' => Hash::make('secret'),
            ],
        ]);
    }
}
