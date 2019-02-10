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
                OrganisationTableSeeder::class,
                PageTableSeeder::class,
                MediaCrawlersTableSeeder::class,
                SozlukCrawlersTableSeeder::class,
                ShoppingCrawlersTableSeeder::class,
                TwitterTokensTableSeeder::class,
                OptionTableSeeder::class,
                ModuleSearchesTableSeeder::class,
                CarouselsTableSeeder::class,
                ForumCategoriesTableSeeder::class,
                ForumMessagesTableSeeder::class,
            ]);
        }
    }
}
