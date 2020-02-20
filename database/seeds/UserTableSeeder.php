<?php

use Illuminate\Database\Seeder;

use App\Models\User\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::firstOrCreate(
            [
                'email' => 'alper@8vz.net'
            ],
            [
                'name' => 'qhudabaksh',
                'password' => bcrypt('1234'),
                'verified' => true,
                'root' => true,
                'session_id' => rand(9999, 99999)
            ]
        );

        //factory(App\Models\User\User::class, 50)->create();
    }
}
