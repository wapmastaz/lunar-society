<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(TierSeeder::class);

        // Admin login comes from .env so no credentials live in the repo.
        if ($email = env('ADMIN_EMAIL')) {
            User::updateOrCreate(['email' => $email], [
                'name' => 'Lunar Society Admin',
                'password' => env('ADMIN_PASSWORD'),
            ])->forceFill(['email_verified_at' => now()])->save();
        }
    }
}
