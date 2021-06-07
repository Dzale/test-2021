<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $admins = [
            [
                "user" => [
                    "email" => "admin@larakicks.com",
                    "password" => bcrypt("Admin123!"),

                ],
                "profile" => [
                    "name" => "Larakicks",

                ]

            ]
        ];
        foreach ($admins as $admin) {
            $user = new User;
            $user->fill($admin["user"])->save();
            $user->markEmailAsVerified();

            $user->assignRole('admin');
            $user->assignRole('user');

            $profile = new \App\Models\Profile;
            $profile->fill($admin['profile'])
                ->user()->associate($user)
                ->save();
        }
    }
}
