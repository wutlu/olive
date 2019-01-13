<?php

namespace App\Console\Commands\Crawlers\Twitter;

use Illuminate\Console\Command;

use App\Models\Twitter\Account;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class AccountControl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:account_control';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Twitter kullanıcı hesaplarının aktif olup olmadığını kontrol et.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $accounts = Account::where('status', true)->get();

        if (count($accounts))
        {
            foreach ($accounts->chunk(10) as $chunk)
            {
                foreach ($chunk as $account)
                {
                    $this->info($account->name);

                    $reasons = [];
                    $status = true;

                    try
                    {
                        $response = self::getUser([ 'id' => $account->id ]);

                        $account->name = $response->name;
                        $account->screen_name = $response->screen_name;
                        $account->avatar = $response->profile_image_url;
                        $account->description = $response->description;
                        $account->suspended = false;

                        if (!$response->description)
                        {
                            $reasons[] = 'Twitter hesabınızın biyografi alanını doldurun.';
                            $status = false;
                        }

                        if ($response->profile_image_url == 'http://abs.twimg.com/sticky/default_profile_images/default_profile.png')
                        {
                            $reasons[] = 'Twitter hesabınıza bir profil resmi ekleyin.';
                            $status = false;
                        }

                        if ($response->suspended)
                        {
                            $reasons[] = 'Twitter hesabınız askıya alınmış. Lütfen farklı bir hesap ile tekrar giriş yapın.';
                            $status = false;

                            $account->suspended = true;
                        }

                        $this->info('Active');
                    }
                    catch (\Exception $e)
                    {
                        if ($e->getCode() == 401)
                        {
                            $reasons[] = 'Twitter hesabınıza erişilemiyor.';
                            $status = false;
                        }
                        else
                        {
                            $reasons[] = 'Twitter hesabınızda tespit edemediğimiz bir problem var.';
                            $status = false;
                        }

                        $this->error('Disabled');
                    }

                    $account->status = $status;
                    $account->reasons = $status ? null : implode(PHP_EOL, $reasons);
                    $account->save();
                }
            }
        }
    }

    /**
     * Twitter Profili
     *
     * - Twitter profil bilgilerini getir.
     *
     * @return json
     */
    public static function getUser(array $user, string $token = null, string $token_secret = null)
    {
        $stack = HandlerStack::create();

        $oauth = new Oauth1([
            'consumer_key' => config('services.twitter.client_id'),
            'consumer_secret' => config('services.twitter.client_secret'),
            'token' => $token ? $token : config('services.twitter.access_token'),
            'token_secret' => $token_secret ? $token_secret : config('services.twitter.access_token_secret')
        ]);

        $stack->push($oauth);

        $client = new Client(
            [
                'base_uri' => 'https://api.twitter.com/1.1/',
                'handler' => $stack,
                'auth' => 'oauth'
            ]
        );

        if (@$user['id'])
        {
            $key = 'id';
            $val = $user['id'];
        }
        else if (@$user['screen_name'])
        {
            $key = 'screen_name';
            $val = $user['screen_name'];
        }

        $response = $client->get('users/show.json', [
            'query' => [
                $key => $val
            ],
            'timeout' => 10,
            'connect_timeout' => 5,
            'headers' => [
                'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))],
                'Accept' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody());
    }
}
