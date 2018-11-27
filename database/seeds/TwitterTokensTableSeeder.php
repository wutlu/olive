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
            ],
            [
                'consumer_key' => 'k6VJe7V43CXCfEMnORY8h0aa2',
                'consumer_secret' => '5F2QAzIalmc6Y8HRCTny8r18zxRgqAQY78UrZOITS8IrmJAU8o',
                'access_token' => '1033402875119058946-0dZxL2GySHE2SnkKv6u1TcwhuyMxcS',
                'access_token_secret' => 'Awpp5lJkbEnm3vjc03D5aNRLRH54XWBn8BIE8qam5mH12'
            ],
            [
                'consumer_key' => 'z2dFPxVVHaj99rwmS9ytu3EUH',
                'consumer_secret' => '2WQwFCS3fCSYcZumiQGxNGmPvU0HqtkfDdku5gl01rA2p5cVE5',
                'access_token' => '1033402875119058946-zpHa3k5124L9rBBfMhcnEy30Ti25Ao',
                'access_token_secret' => 'EeIaHIDa6hq4gse6zgL7ZJlMoTW6aOljrh2Gf7se5w5Lb'
            ],
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
