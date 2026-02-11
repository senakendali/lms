<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================
        // ADMIN
        // ==========================
        User::updateOrCreate(
            ['email' => 'admin@flexlabs.co.id'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // ==========================
        // INSTRUCTOR
        // ==========================
        User::updateOrCreate(
            ['email' => 'instructor@flexlabs.co.id'],
            [
                'name' => 'Instructor',
                'password' => Hash::make('password'),
                'role' => 'instructor',
            ]
        );

        // ==========================
        // STUDENT
        // ==========================
        User::updateOrCreate(
            ['email' => 'student@flexlabs.co.id'],
            [
                'name' => 'Student',
                'password' => Hash::make('password'),
                'role' => 'student',
            ]
        );

        $this->command->info('User roles seeded: admin, instructor, student');
    }
}
