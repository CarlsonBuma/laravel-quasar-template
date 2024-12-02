<?php

namespace Database\Seeders;

use App\Models\UserCockpit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Access\AccessHandler;

class UserSeeder extends Seeder
{
    public function run()
    {
        $userTable = DB::table('users');

        $userID = $userTable->insertGetId([
            'name' => 'Owner',
            'email' =>'admin@admin.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        UserCockpit::create([
            'user_id' => $userID,
        ]);

        // Grant access
        AccessHandler::addUserAccess(
            $userID,
            null,
            AccessHandler::$tokenAdmin,
            1000,
            '2050-31-12',
            'created.by.seeder'
        );

        AccessHandler::addUserAccess(
            $userID,
            null,
            AccessHandler::$tokenCockpit,
            1000,
            '2050-31-12',
            'created.by.seeder'
        );

        // Normal User
        for($x = 0; $x < 20; $x++) {
            $id = $userTable->insertGetId([
                'name' => 'User' . $x,
                'email' =>'user' . $x .'@user.com',
                'email_verified_at' => now(),
                'password' => Hash::make('test'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            UserCockpit::create([
                'user_id' => $id,
            ]);
        }
    }
}
