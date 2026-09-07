<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
           [
                'name'      => 'Admin',
                'email'     => 'admin@gmail.com',
                'password'  => Hash::make('password123'),
                'role'      => 'admin',
                'no_hp'     => '081234567890',
                'alamat'    => 'Bandung, West Java',
            ],
            [
                'name'      => 'Michael', //arif muhammad
                'email'     => 'michael@gmail.com',
                'password'  => Hash::make('password123'),
                'role'      => 'petugas',
                'no_hp'     => '081234567891',
                'alamat'    => 'Baleendah, Bandung',
            ],
            [
                'name'      => 'Jordan', //Rian Setiawan
                'email'     => 'jordan@gmail.com',
                'password'  => Hash::make('password123'),
                'role'      => 'peminjam',
                'no_hp'     => '081234567892',
                'alamat'    => 'Ciparay, Bandung',
            ],
            [
                'name'      => 'Kiara', //Siti Aminah
                'email'     => 'kiara@gmail.com',
                'password'  => Hash::make('password'),
                'role'      => 'peminjam',
                'no_hp'     => '081234567893',
                'alamat'    => 'DayeuhKolot, Bandung',
            ],
            [
                'name'      => 'Cantika', //Eka Pratama
                'email'     => 'cantika@gmail.com',
                'password'  => Hash::make('password123'),
                'role'      => 'peminjam',
                'no_hp'     => '081234567894',
                'alamat'    => 'Banjaran, Bandung',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
