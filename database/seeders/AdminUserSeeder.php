<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = config('admin.name');
        $email = config('admin.email');
        $password = config('admin.password');

        if (!$name || !$email) {
            throw new RuntimeException(
                'Konfigurasi nama dan email admin belum diisi.'
            );
        }

        $user = User::firstOrNew([
            'email' => $email,
        ]);

        if (!$user->exists) {
            if (!$password) {
                throw new RuntimeException(
                    'Password admin belum diatur di .env.'
                );
            }

            $user->password = $password;
        }

        $user->name = $name;
        $user->forceFill([
            'role' => 'admin',
        ]);

        $user->save();
    }
}
