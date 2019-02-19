<?php

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
        if (config('app.env') == 'local')
        {
            $this->call([
                CountriesTableSeeder::class,
                StatesTableSeeder::class,
                UserTableSeeder::class,
                PageTableSeeder::class,
                OptionTableSeeder::class,
                ModuleSearchesTableSeeder::class,
            ]);
        }
    }
}
