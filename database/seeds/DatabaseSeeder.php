<?php

use App\File;
use App\Status;
use App\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $statuses = Status::where('type', 'user')->get();

        // Create my account
        $email = 'mrb2590@gmail.com';

        $user = User::create([
            'first_name' => 'Mike',
            'last_name' => 'Buonomo',
            'email' => $email,
            'slug' => str_slug(explode('@', $email)[0], '-'),
            'password' => bcrypt('apples'),
            'remember_token' => str_random(10),
            'api_token' => str_random(60),
            'status_id' => $statuses[$faker->biasedNumberBetween(
                $statuses->count() - 1, 1, 'sqrt'
            )]->id,
        ]);

        $user->assignRole('super_user');

        // Create 50 random users
        factory(User::class, 50)->create()->each(function($user) use ($faker) {
            $user->assignRole($faker->randomElement([
                'admin', 'member', 'viewer'
            ]));
        });

        // Create 50 random files
        factory(File::class, 50)->create();

    }
}
