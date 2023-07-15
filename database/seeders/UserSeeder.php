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
            'first_name'=>'Palamy',
            'last_name'=>'Khounsady',
            'role'=>'OWNER',
            'responsibility'=>'Take care of business',
            'address'=>'Xayxavang, Xaythany, Vientiane',
            'phone_number'=>'55555555',
            'password'=>bcrypt(55555555)
        ]);
    }
}
