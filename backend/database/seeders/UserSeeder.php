<?php

namespace Database\Seeders;

use App\Models\UserAvatar;
use App\Models\Entity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $userTable = DB::table('users');

        // Admin
        $userID = $userTable->insertGetId([
            'name' => 'Owner',
            'email' =>'admin',
            'email_verified_at' => now(),
            'password' => Hash::make('admin'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        UserAvatar::create([
            'user_id' => $userID,
        ]);

        Entity::create([
            'user_id' => $userID,
        ]);
        
        DB::table('admins')->insert([
            'user_id' => $userID,
            'role' =>'Owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Normal Users
        for($x = 0; $x < 20; $x++) {
            $id = $userTable->insertGetId([
                'name' => 'User' . $x,
                'email' =>'user' . $x,
                'email_verified_at' => now(),
                'password' => Hash::make('test'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            UserAvatar::create([
                'user_id' => $id,
            ]);

            Entity::create([
                'user_id' => $id,
            ]);
        }
    }
}
