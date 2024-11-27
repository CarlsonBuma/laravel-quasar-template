<?php

namespace Database\Seeders;

use App\Models\UserAccess;
use App\Models\UserEntity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Middleware\AppAccessAdmin;
use App\Http\Controllers\Access\AccessHandler;
use App\Http\Middleware\AppAccessCockpit;

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

        UserEntity::create([
            'user_id' => $userID,
        ]);

        // Grant access
        AccessHandler::addUserAccess(
            $userID,
            null,
            AppAccessAdmin::getAccessToken(),
            1000,
            '2050-31-12',
            'created.by.seeder'
        );

        AccessHandler::addUserAccess(
            $userID,
            null,
            AppAccessCockpit::getAccessToken(),
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

            UserEntity::create([
                'user_id' => $id,
            ]);
        }
    }
}
