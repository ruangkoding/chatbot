<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        User::create([
            'name'              => 'Testing User',
            'email'             => 'test@mail.com',
            'password'          => \Hash::make('password'),
            'email_verified_at' => now(),
            'remember_token'   => \Str::random(10)
        ]);
    }
}
