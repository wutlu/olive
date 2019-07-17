<?php

namespace App;

use Illuminate\Http\Request;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Models\Proxy;

class Instagram
{
    private $base_uri = 'https://www.instagram.com';
    private $dom;

    public function connect(string $url)
    {
        $client = new Client([
            'base_uri' => $this->base_uri,
            'handler' => HandlerStack::create()
        ]);

        try
        {
            $arr = [
                'timeout' => 10,
                'connect_timeout' => 5,
                'headers' => [
                    'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))],
                    'Accept-Language' => 'tr-TR;q=0.6,tr;q=0.4'
                ],
                'verify' => false
            ];

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
                'code' => $e->getCode()
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
                        $arr['location'] = $json->entry_data->LocationsPage[0]->graphql->location->name;
                    break;
                    case 'user':
                        $graphql = $json->entry_data->ProfilePage[0]->graphql;
                        $edges = $graphql->user->edge_owner_to_timeline_media->edges;

                        $return['user'] = [
                            'id' => $graphql->user->id,
                            'name' => $graphql->user->full_name,
                            'screen_name' => $graphql->user->username,
                            'profile_pic_url' => $graphql->user->profile_pic_url,
                            'external_url' => $graphql->user->external_url,
                            'biography' => $graphql->user->biography,
                            'counts' => [
                                'follow' => $graphql->user->edge_follow->count,
                                'followed_by' => $graphql->user->edge_followed_by->count,
                                'media' => $graphql->user->edge_owner_to_timeline_media->count
                            ]
                        ];
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
        return array_map(function($item) use ($method, $_arr) {
            $arr = [
                'id' => $item->node->id,
                'shortcode' => $item->node->shortcode,
                'display_url' => $item->node->display_url,
                'owner_id' => $item->node->owner->id,
                'type' => $item->node->is_video ? 'video' : 'photo',
                'created_at' => date('Y-m-d H:i:s', $item->node->taken_at_timestamp)
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

            if ($text)
            {
                $arr['text'] = $text;

                preg_match_all('/(?<=#)([A-ZğüşıöçĞÜŞİÖÇa-z0-9\/\.]*)/', $text, $hashtags);

                if (count($hashtags[0]))
                {
                    foreach ($hashtags[0] as $hashtag)
                    {
                        if ($hashtag)
                        {
                            $arr['hashtags'][] = $hashtag;
                        }
                    }
                }

                preg_match_all('/(?<=@)([A-Za-z0-9_\/\.]*)/', $text, $mentions);

                if (count($mentions[0]))
                {
                    foreach ($mentions[0] as $mention)
                    {
                        if ($mention)
                        {
                            $arr['mentions'][] = $mention;
                        }
                    }
                }
            }

            if (@$item->node->location->name)
            {
                $arr['location'] = $item->node->location->name;
            }
            else if ($method == 'location')
            {
                $arr = array_merge($arr, $_arr);
            }

            return $arr;
        }, $data);
    }
}
