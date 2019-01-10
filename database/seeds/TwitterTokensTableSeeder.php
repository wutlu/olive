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
        $path = database_path('twitter_tokens.private');

        if (file_exists($path))
        {
            $content = file_get_contents($path);
            $lines = explode(PHP_EOL, $content);
            $tokens = array_map(function($line) {
                $line = explode(' ', $line);

                return [
                    'consumer_key' => $line[0],
                    'consumer_secret' => $line[1],
                    'access_token' => $line[2],
                    'access_token_secret' => $line[3],
                ];
            }, $lines);

            foreach ($tokens as $token)
            {
               	$query = Token::updateOrCreate(
                    [
                        'consumer_key' => $token['consumer_key']
                    ],
                    [
                        'consumer_key' => $token['consumer_key'],
                        'consumer_secret' => $token['consumer_secret'],
                        'access_token' => $token['access_token'],
                        'access_token_secret' => $token['access_token_secret']
                    ]
                );
            }
        }
    }
}
