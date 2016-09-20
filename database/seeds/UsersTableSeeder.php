<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name'     => 'Zack',
            'email'    => 'hamm.zachary+digits@gmail.com',
            'password' => bcrypt('the-digits'),
            'avatar'   => 'https://www.gravatar.com/avatar/' . md5('hamm.zachary@gmail.com')
        ]);
    }
}
