<?php

namespace Database\Seeders;

use App\Models\Entities;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $userTable = DB::table('users');

        // Admins
        $userID = $userTable->insertGetId([
            'name' => 'Owner',
            'email' =>'admin@admin.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Entities::create([
            'user_id' => $userID,
        ]);
        
        DB::table('admins')->insert([
            'user_id' => $userID,
            'role' =>'Owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Normal User
        for($x = 0; $x < 20; $x++) {
            $id = $userTable->insertGetId([
                'name' => 'User' . $x,
                'email' =>'user' . $x .'@admin.com',
                'email_verified_at' => now(),
                'password' => Hash::make('test'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Entities::create([
                'user_id' => $id,
            ]);
        }
    }
}
