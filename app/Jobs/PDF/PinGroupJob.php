<?php

namespace App\Jobs\PDF;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Pin\Group as PinGroup;

use PDF;
use System;

use App\Utilities\UserActivityUtility as Activity;

class PinGroupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pg = PinGroup::where('id', $this->id)->first();

        if (@$pg)
        {
            $pins = $pg->pins;

            if (count($pins) >= 1 && count($pins) <= 100)
            {
                $name = implode('.', [
                    $this->id,
                    $pg->organisation_id,
                    date('ymdhis'),
                    str_random(32)
                ]);

                $html_path = $pg->html_path ? $pg->html_path : 'storage/outputs/html/'.$name.'.html';
                $pdf_path = $pg->pdf_path ? $pg->pdf_path : 'storage/outputs/pdf/'.$name.'.pdf';

                $html = '
                    <!DOCTYPE html>
                    <html lang="'.app()->getLocale().'">
                        <head>
                            <title>'.$pg->name.'</title>
                            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                            <style>
                                body { font-family: DejaVu Sans, sans-serif; }

                                .clearfix:after {
                                    display: block;
                                    clear: both;
                                    content: "";
                                }
                                .clearfix > .left { float: left; }
                                .clearfix > .right { float: right; }

                                time {
                                    color: #999;
                                    font-style: italic;
                                    font-size: 14px;
                                }

                                a {
                                    text-decoration: none;
                                }

                                header {
                                    padding: 1rem;
                                    margin: 0;
                                }
                                header h1 {
                                    text-align: center;
                                    margin: 0;
                                }
                                header img.logo { width: 128px; }

                                .data {
                                    margin: 0 0 12px;
                                    padding: 24px;
                                }
                                .data > h3 {
                                    margin: 0;
                                }
                                .data time {
                                    display: block;
                                }
                                .data > a.url {
                                    font-style: italic;
                                    font-size: 14px;
                                }

                               .data > .pin-comment {
                                    background-color: #fbc02d;
                                    margin: 0 0 12px;
                                    padding: 24px;
                                    font-size: 14px;
                                }

                               .data .text {
                                    background-color: #f6f6f6;
                                    color: #666;
                                    margin: 0 0 12px;
                                    padding: 24px;
                                }

                                .sentiment > .sentiment-item.green {
                                    color: #64dd17;
                                    border-color: #64dd17;
                                }
                                .sentiment > .sentiment-item.red {
                                    color: #D50000;
                                    border-color: #D50000;
                                }
                                .sentiment > .sentiment-item.grey {
                                    color: #9e9e9e;
                                    border-color: #9e9e9e;
                                }

                                .sentiment > .sentiment-item {
                                    border-width: 0 0 4px;
                                    border-style: solid;
                                }

                                .page-break {
                                    page-break-after: always;
                                }
                            </style>
                        </head>
                    <body>
                    <header>
                        <div class="clearfix">
                            <div class="left">
                                <a href="'.config('app.url').'" target="_blank">
                                    <img class="logo" alt="Logo" src="'.asset('img/olive-logo.png').'" />
                                </a>
                            </div>
                            <div class="right">
                                <time>'.date('d.m.Y H:i').'</time>
                            </div>
                        </div>
                        <h1>'.$pg->name.'</h1>
                    </header>
                ';

                file_put_contents(public_path($html_path), $html, LOCK_EX);

                foreach ($pins as $pin)
                {
                    if ($pin->document()->status == 'ok')
                    {
                        $source = $pin->document()->data['_source'];

                        $html = '<div class="data '.$pin->document()->data['_type'].'">';

                        if (@$source['title'])
                        {
                            $html.= '<h3>'.title_case($source['title']).'</h3>';
                        }

                        $html.= '<time>'.date('d.m.Y H:i:s', strtotime($source['created_at'])).'</time>';

                        switch ($pin->document()->data['_type'])
                        {
                            case 'tweet':

                                $html.= '
                                    <a class="url" href="https://twitter.com/'.$source['user']['screen_name'].'/status/'.$source['id'].'" target="_blank">https://twitter.com/'.$source['user']['screen_name'].'/status/'.$source['id'].'</a>
                                    <p>
                                        <a href="https://twitter.com/intent/user?user_id='.$source['user']['id'].'" target="_blank">@'.$source['user']['screen_name'].'</a>
                                        <span>'.$source['user']['name'].'</span>
                                        <span>('.$source['platform'].')</span>
                                    </p>
                                    <div class="text">'.nl2br($source['text']).'</div>
                                ';

                                if (@$source['external'])
                                {
                                    $external_source = $pin->document($source['external']['id']);

                                    if ($external_source)
                                    {
                                        $html.= '
                                            <ul>
                                                <li>
                                                    <span>Asıl Tweet</span>
                                                    <time>'.date('d.m.Y H:i:s', strtotime($external_source['_source']['created_at'])).'</time>
                                                    <a class="url" href="https://twitter.com/'.$external_source['_source']['user']['screen_name'].'/status/'.$external_source['_source']['id'].'" target="_blank">https://twitter.com/'.$external_source['_source']['user']['screen_name'].'/status/'.$external_source['_source']['id'].'</a>
                                                    <p>
                                                        <a href="https://twitter.com/intent/user?user_id='.$external_source['_source']['user']['id'].'" target="_blank" class="red-text">@'.$external_source['_source']['user']['screen_name'].'</a>
                                                        <span>'.$external_source['_source']['user']['name'].'</span>
                                                        <span>('.$external_source['_source']['platform'].')</span>
                                                    </p>
                                                    <div class="text">'.nl2br($external_source['_source']['text']).'</div>
                                                </li>
                                            </ul>
                                        ';
                                    }
                                }

                            break;
                            case 'article':

                                $html.= '<a class="url" href="'.$source['url'].'" target="_blank">'.$source['url'].'</a>';
                                $html.= '<div class="text">'.nl2br($source['description']).'</div>';

                            break;
                            case 'entry':

                                $html.= '<a class="url" href="'.$source['url'].'" target="_blank">'.$source['url'].'</a>';
                                $html.= '<div class="text">'.nl2br($source['entry']).'</div>';

                            break;
                            case 'product':

                                if (@$source['address'])
                                {
                                    $html.= '<ul>';

                                    foreach ($source['address'] as $key => $segment)
                                    {
                                        $html.= '<li>'.$segment['segment'].'</li>';
                                    }

                                    $html.= '</ul>';
                                }

                                if ($source['breadcrumb'])
                                {
                                    $html.= '<ul>';

                                    foreach ($source['breadcrumb'] as $key => $segment)
                                    {
                                        $html.= '<li>'.$segment['segment'].'</li>';
                                    }

                                    $html.= '</ul>';
                                }

                                $html.= '<a href="'.$source['url'].'" target="_blank">'.$source['url'].'</a>';
                                $html.= '<div>';
                                $html.= '<span>'.title_case($source['seller']['name']).'</span>';

                                if (@$source['seller']['phones'])
                                {
                                    $html.= '<ul>';

                                    foreach ($source['seller']['phones'] as $key => $phone)
                                    {
                                        $html.= '<li>'.$phone['phone'].'</li>';
                                    }

                                    $html.= '</ul>';
                                }

                                $html.= '</div>';

                                if ($source['description'])
                                {
                                    $html.= '<div class="text">'.nl2br($source['description']).'</div>';
                                }

                                $html.= '<p>';
                                $html.= '   <span>'.number_format($source['price']['amount']).'</span>';
                                $html.= '   <span>'.$source['price']['currency'].'</span>';
                                $html.= '</p>';

                            break;
                            case 'comment':

                                $html.= '<a class="url" href="https://www.youtube.com/watch?v='.$source['video_id'].'" target="_blank">https://www.youtube.com/watch?v='.$source['video_id'].'</a>';
                                $html.= '<p>';
                                $html.= '   <a href="https://www.youtube.com/channel/'.$source['channel']['id'].'" target="_blank">@'.$source['channel']['title'].'</a>';
                                $html.= '</p>';
                                $html.= '<div class="text">'.nl2br($source['text']).'</div>';

                            break;
                            case 'video':

                                $html.= '<a class="url" href="https://www.youtube.com/watch?v='.$source['id'].'" target="_blank">https://www.youtube.com/watch?v='.$source['id'].'</a>';
                                $html.= '<p>';
                                $html.= '   <a href="https://www.youtube.com/channel/'.$source['channel']['id'].'" target="_blank">@'.$source['channel']['title'].'</a>';
                                $html.= '</p>';

                                if ($source['description'])
                                {
                                    $html.= '<div class="text">'.nl2br($source['description']).'</div>';
                                }

                            break;
                        }

                        $sentiment = @$source['sentiment'];

                        if ($sentiment)
                        {
                            $html.= '<div class="sentiment clearfix">';
                            $html.= '  <div style="width: '.intval($sentiment['pos']*100).'%;" class="sentiment-item green left">';

                            if ($sentiment['pos'] > 0.2)
                            {
                                $html.= '  <span>Pozitif</span>';
                                $html.= '  <span>'.($sentiment['pos']*100).'%</span>';
                            }
                            $html.= '  </div>';

                            $html.= '  <div style="width: '.intval($sentiment['neu']*100).'%;" class="sentiment-item grey left">';

                            if ($sentiment['neu'] > 0.2)
                            {
                                $html.= '  <span>Nötr</span>';
                                $html.= '  <span>'.($sentiment['neu']*100).'%</span>';
                            }

                            $html.= '  </div>';

                            $html.= '  <div style="width: '.intval($sentiment['neg']*100).'%;" class="sentiment-item red left">';

                            if ($sentiment['neg'] > 0.2)
                            {
                                $html.= '  <span>Negatif</span>';
                                $html.= '  <span>'.($sentiment['neg']*100).'%</span>';
                            }

                            $html.= '  </div>';
                            $html.= '</div>';
                        }

                        if ($pin->comment)
                        {
                            $html.= '<div class="pin-comment">'.nl2br($pin->comment).'</div>';
                        }

                        $html.= '</div>';
                    }
                    else
                    {
                        $html = '<div class="data">';
                        $html.= '   <p>Kaynak Okunamadı.</p>';
                        $html.= '</div>';
                    }

                    //$html.= '<div class="page-break"></div>';

                    file_put_contents(public_path($html_path), $html, FILE_APPEND | LOCK_EX);
                }

                $html = '
                    </body>
                    </html>
                ';

                file_put_contents(public_path($html_path), $html, FILE_APPEND | LOCK_EX);

                $pg->html_path = $html_path;
                $pg->pdf_path = $pdf_path;

                try
                {
                    $pdf = PDF::loadFile(public_path($html_path))->save(public_path($pdf_path));

                    $pg->html_to_pdf = 'success';

                    foreach ($pg->organisation->users as $user)
                    {
                        Activity::push(
                            'Pinleme PDF dökümünüz hazırlandı.',
                            [
                                'push' => true,
                                'icon' => 'picture_as_pdf',
                                'user_id' => $user->id,
                                'key' => implode('-', [ $user->id, 'pdf', 'export', $pg->id ]),
                                'button' => [
                                    'type' => 'http',
                                    'method' => 'GET',
                                    'action' => url($pg->pdf_path),
                                    'class' => 'btn cyan waves-effect',
                                    'text' => 'İndir'
                                ]
                            ]
                        );
                    }
                }
                catch (\Exception $e)
                {
                    System::log(json_encode([ $e->getMessage() ]), 'App\Jobs\PDF\PinGroupJob::handle('.$this->id.')', 10);

                    foreach ($pg->organisation->users as $user)
                    {
                        Activity::push(
                            'Pinleme PDF dökümü alınırken bir sorun oluştu.',
                            [
                                'markdown' => 'Pin Grubu PDF dökümünüzü hazırlarken bir problemle karşılaştık. Lütfen tekrar deneyin.',
                                'push' => true,
                                'icon' => 'picture_as_pdf',
                                'user_id' => $user->id,
                                'key' => implode('-', [ $user->id, 'pdf', 'export', $pg->id ])
                            ]
                        );
                    }
                }
            }

            $pg->completed_at = date('Y-m-d H:i:s');
            $pg->save();
        }
    }
}
