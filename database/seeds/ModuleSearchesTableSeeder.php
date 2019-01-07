<?php

use Illuminate\Database\Seeder;

use App\Models\ModuleSearch;

class ModuleSearchesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (config('system.search.modules') as $key => $module)
        {
            ModuleSearch::firstOrCreate(
                [
                    'module_id' => $key
                ],
                [
                    'keyword' => $module['name'],
                    'route' => route($module['route'])
                ]
            );
        }
    }
}
