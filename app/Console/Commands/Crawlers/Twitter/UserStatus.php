<?php

namespace App\Console\Commands\Crawlers\Twitter;

use Illuminate\Console\Command;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Models\Proxy;
use App\Models\Twitter\StreamingUsers;

use App\Wrawler;

class UserStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:user_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Twitter için takip edilen kullanıcıların hesap durumlarını kontrol et.';

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
        $tusers = StreamingUsers::orderBy('updated_at', 'ASC')->limit(10)->get();

        if (count($tusers))
        {
            foreach ($tusers as $tuser)
            {
                $this->info($tuser->user_id);

                $client = new Client([
                    'base_uri' => 'https://twitter.com',
                    'handler' => HandlerStack::create()
                ]);

                try
                {
                    $tuser->reason = null;

                    $arr = [
                        'timeout' => 10,
                        'connect_timeout' => 5,
                        'headers' => [
                            'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))],
                            'Accept-Language' => 'tr-TR;q=0.6,tr;q=0.4'
                        ],
                        'verify' => false,
                        'query' => [
                            'user_id' => $tuser->user_id
                        ]
                    ];

                    $p = Proxy::where('health', '>', 7)->inRandomOrder()->first();

                    if (@$p)
                    {
                        $arr['proxy'] = $p->proxy;
                    }

                    $dom = $client->get('intent/user', $arr)->getBody();

                    $saw = new Wrawler($dom);

                    $title = $saw->get('title')->toText();
                    $verified = $saw->get('.verified')->toText();
                    $screen_name = $saw->get('.nickname')->toText();

                    if (strpos($title, 'Hesap Askıya Alındı'))
                    {
                        $tuser->reason = 'Hesap Askıya Alınmış';
                    }

                    if ($screen_name)
                    {
                        $tuser->screen_name = str_replace('@', '', $screen_name);
                    }

                    $tuser->verified = $tuser->verified ? true : false;
                }
                catch (\Exception $e)
                {
                    $tuser->reason = 'Hesap Silinmiş';
                }

                $tuser->status = false;
                $tuser->updated_at = date('Y-m-d H:i:s');
                $tuser->update();

                if ($tuser->reason)
                {
                    $this->error($tuser->reason);
                }
                else
                {
                    $this->info('ok');
                }

                sleep(rand(1, 5));
            }
        }
        else
        {
            $this->error('İncelenecek kullanıcı bulunamadı!');
        }
    }
}
