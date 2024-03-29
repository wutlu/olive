<?php

namespace App;

use Illuminate\Http\Request;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Cookie\CookieJar;

use App\Models\Proxy;

use App\Olive\Gender;

use Sentiment;
use Term;

class Instagram
{
    private $base_uri = 'https://www.instagram.com';
    private $dom;

    public function connect(string $url, bool $cookies = true)
    {
        $client = new Client([
            'base_uri' => $this->base_uri,
            'handler' => HandlerStack::create(),
            'cookies' => $cookies
        ]);

        try
        {
            $arr = [
                'timeout' => 10,
                'connect_timeout' => 5,
                'headers' => [
                    'User-Agent' => config('crawler.user_agents_mobile')[array_rand(config('crawler.user_agents_mobile'))],
                    'Accept-Language' => 'tr-TR;q=0.6,tr;q=0.4'
                ],
                'verify' => false
            ];

            if ($cookies)
            {
                $arr['cookies'] = CookieJar::fromArray([ 'sessionid' => config('services.instagram.session.id') ], '.instagram.com');
            }

            $proxy = Proxy::where('health', '>', 7)->inRandomOrder();

            if ($proxy->exists())
            {
                $arr['proxy'] = $proxy->first()->proxy;
            }

            $client = $client->get($url, $arr);

            $this->dom = $client->getBody();

            return (object) [
                'status' => 'ok',
                'dom' => $this->dom
            ];
        }
        catch (\Exception $e)
        {
            return (object) [
                'status' => 'err',
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }

    public function data(string $method)
    {
        preg_match_all('/(?<=<script type="text\/javascript">window\._sharedData = )(.+)(?=;<\/script>)/', $this->dom, $match);

        $object = [];
        $data = [];

        $json = @$match[0][0];
        $json = json_decode($json);

        if ($json == 'null')
        {
            return [
                'status' => 'err',
                'message' => 'JSON düzgün gelmedi.'
            ];
        }
        else
        {
            try
            {
                $arr = [];

                switch ($method)
                {
                    case 'hashtag':
                        $edges = $json->entry_data->TagPage[0]->graphql->hashtag->edge_hashtag_to_media->edges;
                    break;
                    case 'location':
                        $edges = $json->entry_data->LocationsPage[0]->graphql->location->edge_location_to_media->edges;
                        $arr['place'] = [
                            'name' => $json->entry_data->LocationsPage[0]->graphql->location->name
                        ];
                    break;
                    case 'user':
                        $graphql = $json->entry_data->ProfilePage[0]->graphql;
                        $edges = $graphql->user->edge_owner_to_timeline_media->edges;

                        $gender = new Gender;
                        $gender->loadNames();

                        $user = [
                            'id' => $graphql->user->id,
                            'name' => $graphql->user->full_name,
                            'screen_name' => $graphql->user->username,
                            'gender' => $gender->detector([ $graphql->user->full_name, $graphql->user->username ]),
                            'image' => $graphql->user->profile_pic_url,
                            'counts' => [
                                'follow' => $graphql->user->edge_follow->count,
                                'followed_by' => $graphql->user->edge_followed_by->count,
                                'media' => $graphql->user->edge_owner_to_timeline_media->count
                            ],
                            'called_at' => date('Y-m-d H:i:s')
                        ];

                        if ($graphql->user->external_url)
                        {
                            $user['external_url'] = $graphql->user->external_url;
                        }

                        if ($graphql->user->biography)
                        {
                            $user['description'] = $graphql->user->biography;
                        }

                        if ($graphql->user->is_verified)
                        {
                            $user['verified'] = true;
                        }

                        $return['user'] = $user;
                    break;
                    default:
                        return (object) [
                            'status' => 'err',
                            'message' => 'Girilen metod geçerli değil!'
                        ];
                    break;
                }

                $return['status'] = 'ok';
                $return['data'] = self::map($edges, $method, $arr);

                return (object) $return;
            }
            catch (\Exception $e)
            {
                return (object) [
                    'status' => 'err',
                    'message' => $e->getMessage()
                ];
            }
        }
    }

    private static function map(array $data, string $method = '', array $_arr = [])
    {
        $sentiment = new Sentiment;
        $sentiment->engine('sentiment');

        $consumer = new Sentiment;
        $consumer->engine('consumer');

        $illegal = new Sentiment;
        $illegal->engine('illegal');

        $category = new Sentiment;
        $category->engine('category');

        return array_map(function($item) use ($method, $_arr, $sentiment, $consumer, $illegal, $category) {
            $arr = [
                'id' => $item->node->id,
                'shortcode' => $item->node->shortcode,
                'display_url' => $item->node->display_url,
                'user' => [
                    'id' => $item->node->owner->id
                ],
                'type' => $item->node->is_video ? 'video' : 'photo',
                'created_at' => date('Y-m-d H:i:s', $item->node->taken_at_timestamp),
                'called_at' => date('Y-m-d H:i:s')
            ];

            if (@$item->node->accessibility_caption)
            {
                $caption = explode(':', $item->node->accessibility_caption);

                if (@$caption[1])
                {
                    $arr['caption'] = trim($caption[1]);
                }
            }

            $text = @$item->node->edge_media_to_caption->edges[0]->node->text;

            $arr['sentiment'] = $sentiment->score($text ? $text : '');
            $arr['consumer'] = $consumer->score($text ? $text : '');
            $arr['illegal'] = $illegal->score($text ? $text : '');

            $category_name = $category->net($text ? $text : '', 'category');

            if ($category_name)
            {
                $arr['category'] = $category_name;
            }

            if ($text)
            {
                $arr['text'] = $text;

                preg_match_all('/(?<=#)([A-ZğüşıöçĞÜŞİÖÇa-z0-9\/\.]*)/', $text, $hashtags);

                if (count($hashtags[0]))
                {
                    $_hashtags = [];

                    foreach ($hashtags[0] as $hashtag)
                    {
                        $hashtag = Term::convertAscii($hashtag);

                        if ($hashtag)
                        {
                            if (@$_hashtags[$hashtag])
                            {
                                //
                            }
                            else
                            {
                                $_hashtags[$hashtag] = $hashtag;

                                $arr['entities']['hashtags'][] = [ 'hashtag' => $hashtag ];
                            }
                        }
                    }

                    if (count($_hashtags))
                    {
                        $arr['counts']['hashtag'] = count($_hashtags);
                    }
                }

                preg_match_all('/(?<=@)([A-Za-z0-9_\/\.]*)/', $text, $mentions);

                if (count($mentions[0]))
                {
                    $_mentions = [];

                    foreach ($mentions[0] as $mention)
                    {
                        if ($mention)
                        {
                            if (@$_mentions[$mention])
                            {
                                //
                            }
                            else
                            {
                                $_mentions[$mention] = $mention;

                                $arr['entities']['mentions'][] = [
                                    'mention' => [
                                        'screen_name' => $mention
                                    ]
                                ];
                            }
                        }
                    }

                    if (count($_mentions))
                    {
                        $arr['counts']['mention'] = count($_mentions);
                    }
                }
            }

            if (@$item->node->location->name)
            {
                $arr['place'] = [
                    'name' => $item->node->location->name
                ];
            }
            else if ($method == 'location')
            {
                $arr = array_merge($arr, $_arr);
            }

            return $arr;
        }, $data);
    }
}
