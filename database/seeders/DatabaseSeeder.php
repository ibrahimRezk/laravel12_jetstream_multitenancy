<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);


        // here is to create main site super admin but check hrms to create it with more features as users has been seperated in user model and another model like employee or admin or else

        $data['email'] =  'admin@gmail.com'  ;
        $data['password'] =  bcrypt('55555sssss') ;
        $data['name'] = 'Super Admin' ;
        $data['main_site_admin'] = true ;    /// <<<<<<<<<<<<<<<<<<<<<<<<<<<
        
        User::factory()->create($data);


        $this->call([
            PlanSeeder::class,
        ]);

    }

}
