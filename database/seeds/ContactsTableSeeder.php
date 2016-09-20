<?php

use App\ContactModel;
use App\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class ContactsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create();
        for ($i = 0 ; $i < 500 ; $i++) {
            ContactModel::create([
                'first_name' => $faker->firstName,
                'last_name'  => $faker->lastName,
                'email'      => $faker->unique()->email,
                'phone'      => $faker->phoneNumber,
                'user_id'    => 1,
            ]);
        }
    }
}
