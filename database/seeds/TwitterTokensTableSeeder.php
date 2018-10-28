<?php

use Illuminate\Database\Seeder;

use App\Models\Twitter\Token;

class TwitterTokensTableSeeder extends Seeder
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
                'consumer_key' => 'TVSeX3HEq9fZV5SvOovYDJb4d',
                'consumer_secret' => 'XJmduCOzg37dtvemc67ASQjtcO8nvIvH8TNsZy8m2E4FTUmckx',
                'access_token' => '1033402875119058946-dkGEoG5g7usP8cndLiBrGD6WnVJcGi',
                'access_token_secret' => '6U9EGsFV5zYFT6sqxTFjGibUkr7lHZgSwQGIYhuJFHEYd'
            ],
            [
                'consumer_key' => 'yV4jfYwP0maAFheS7ldkVIBxO',
                'consumer_secret' => '0TAr4uGvvCb3nMGCtr8rSAroP2XM3ZVuFsAkxw9IcNeQ6jGM0x',
                'access_token' => '1033402875119058946-B7UaSssoT6aDd6YHy0Yz8ryWSP4o4c',
                'access_token_secret' => 'C6bpltnXH8bU9Ty5IjRPu5W3EQdV0WEPpYq6ZnHoQiJyu'
            ]
        ];

        foreach ($items as $item)
        {
           	$query = Token::updateOrCreate(
                [
                    'consumer_key' => $item['consumer_key']
                ],
                [
                    'consumer_key' => $item['consumer_key'],
                    'consumer_secret' => $item['consumer_secret'],
                    'access_token' => $item['access_token'],
                    'access_token_secret' => $item['access_token_secret']
                ]
            );
        }
    }
}
