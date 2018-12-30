<?php

use Illuminate\Database\Seeder;

use App\Models\Forum\Category;

class ForumCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [
                'name' => 'Laravel',
                'description' => 'Laravel\'e dair her şey.',
                'sort' => 1
            ],
            [
                'name' => 'PostgreSQL',
                'description' => 'PostgreSQL\'e dair her şey.',
                'sort' => 2
            ],
            [
                'name' => 'Elatsicsearch',
                'description' => 'Elasticsearch\'e dair her şey.',
                'sort' => 3
            ],
            [
                'name' => 'PHP',
                'description' => 'PHP\'ye dair her şey.',
                'sort' => 4
            ],
            [
                'name' => 'Front-end',
                'description' => 'Ön yüz kodlamaya dair her şey.',
                'sort' => 5
            ],
            [
                'name' => 'Linux',
                'description' => 'Linux ve Linux sistemlerine dair her şey.',
                'sort' => 6
            ],
            [
                'name' => 'Sosyal Medya',
                'description' => 'Sosyal medya hakkında her şey.',
                'sort' => 7
            ],
            [
                'name' => 'Off-Topic',
                'description' => 'Forum dışı her şey.',
                'sort' => 8
            ],
        ];

        foreach ($items as $item)
        {
            $query = Category::updateOrCreate(
                [
                    'name' => $item['name']
                ],
                [
                    'slug' => str_slug($item['name']),
                    'description' => $item['description'],
                    'sort' => $item['sort']
                ]
            );
        }
    }
}
