<?php

namespace App\Console\Commands\Crawlers\Twitter;

use Illuminate\Console\Command;

use App\Models\Twitter\Account;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class AccountControl extends Command
{
    private $endpoint = "https://api.twitter.com/1.1/";

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
    protected $description = 'Müşteri Twitter hesaplarının aktif olup olmadığını kontrol eder.';

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
                        $stack = HandlerStack::create();

                        $oauth = new Oauth1([
                            'consumer_key' => config('services.twitter.client_id'),
                            'consumer_secret' => config('services.twitter.client_secret'),
                            'token' => $account->token,
                            'token_secret' => $account->token_secret
                        ]);

                        $stack->push($oauth);

                        $client = new Client(
                            [
                                'base_uri' => $this->endpoint,
                                'handler' => $stack,
                                'auth' => 'oauth'
                            ]
                        );

                        $response = $client->get('users/show.json', [
                            'query' => [
                                'user_id' => $account->id
                            ],
                            'timeout' => 10,
                            'connect_timeout' => 5,
                            'headers' => [
                                'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))],
                                'Accept' => 'application/json'
                            ]
                        ]);
                        $response = json_decode($response->getBody());

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
}
