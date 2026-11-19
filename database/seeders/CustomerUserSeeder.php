<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class CustomerUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = config('customer.name');
        $email = config('customer.email');
        $password = config('customer.password');

        if (!$name || !$email || !$password) {
            throw new RuntimeException(
                'Konfigurasi customer belum lengkap di file .env.'
            );
        }

        $user = User::firstOrNew([
            'email' => $email,
        ]);

        if ($user->exists && $user->role !== 'customer') {
            throw new RuntimeException(
                'Email tersebut sudah digunakan oleh akun non-customer.'
            );
        }

        if (!$user->exists) {
            $user->password = $password;
        }

        $user->name = $name;

        $user->forceFill([
            'role' => 'customer',
        ]);

        $user->save();
    }
}
