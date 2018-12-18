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
                'consumer_key' => 'tn5VnjavB6cJqxOBOEhBQmJES',
                'consumer_secret' => 'Lrm0tTLi5eNqwtSyobo1jmhgOK9037SngaOp6hjI0QxcmoXbgj',
                'access_token' => '1033402875119058946-g4fSADDwlgMOvU9oaFoyFUAvTGE3oa',
                'access_token_secret' => 'zIdouUDzFukCqs6A8OHIeIE00H9bXWSUAUH8HBMW5kcTY'
            ],
            [
                'consumer_key' => 'A24af69hKvM8brBYxGBymEqsw',
                'consumer_secret' => 'QLY0yiPpESRShPfdRhiwrLlf6NldWunBgG4uD5jRLiDoVeDrIA',
                'access_token' => '1033402875119058946-bqdjmUM2Q96wjQMZ5TMNEovko323Lq',
                'access_token_secret' => '9IbB13EZbQqzXXfAkh5oMEi6rt1T6UA4oTFhYSFSWFBcX'
            ],
            [
                'consumer_key' => 'tSlwAyA9EM8lWKXcA3RpDHBqo',
                'consumer_secret' => 'X21Pd12O3FbfqZedqNArqEiMyG7a3hdEapboA01WkXqfuMt2V9',
                'access_token' => '1033402875119058946-RivmyYk7y263C5MRRm3OvJe3OhhyAB',
                'access_token_secret' => 'i2qTtMkJ6b4GB6MKmphcvdNHtLGiRmjeW5P7uHhWh7hPC'
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
