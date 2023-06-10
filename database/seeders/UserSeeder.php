<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'first_name'=>'aaa',
            'last_name'=>'aaa',
            'responsibility'=>'aaa',
            'address'=>'aaa',
            'phone_number'=>'55555555',
            'password'=>bcrypt(12345678)
        ]);
    }
}
