<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@ufo.hu'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'patrik@ufo.hu'],
            [
                'name'     => 'Patrik',
                'password' => Hash::make('password'),
                'role'     => 'user',
            ]
        );

        User::firstOrCreate(
            ['email' => 'odett@ufo.hu'],
            [
                'name'     => 'Odett',
                'password' => Hash::make('password'),
                'role'     => 'user',
            ]
        );

        User::firstOrCreate(
            ['email' => 'kisspeter@ufo.hu'],
            [
                'name'     => 'Kiss Péter',
                'password' => Hash::make('password'),
                'role'     => 'user',
            ]
        );

        User::firstOrCreate(
            ['email' => 'horvatheva@ufo.hu'],
            [
                'name'     => 'Horváth Éva',
                'password' => Hash::make('password'),
                'role'     => 'user',
            ]
        );

        User::firstOrCreate(
            ['email' => 'soselemer@ufo.hu'],
            [
                'name'     => 'Sós Elemér',
                'password' => Hash::make('password'),
                'role'     => 'user',
            ]
        );

        User::firstOrCreate(
            ['email' => 'alimihaly@ufo.hu'],
            [
                'name'     => 'Ali Mihály',
                'password' => Hash::make('password'),
                'role'     => 'user',
            ]
        );
    }
}

/*

1	Admin	admin@ufo.hu
2	Patrik	patrik@ufo.hu
4	Odett	odett@ufo.hu
7	Kiss Péter	kisspeter@ufo.hu
8	Horváth Éva	horvatheva@ufo.hu
9	Sós Elemér	soselemer@ufo.hu
10	Ali Mihály	alimihaly@ufo.hu

mindegyiknek a jelszava: password

*/
