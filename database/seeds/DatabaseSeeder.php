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
        $this->call(CountriesTableSeeder::class)
        	 ->call(StatesTableSeeder::class)
        	 ->call(UserTableSeeder::class)
             ->call(OrganisationTableSeeder::class)
             ->call(PageTableSeeder::class)
             ->call(MediaCrawlersTableSeeder::class);
    }
}
