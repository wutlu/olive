<?php

namespace App\Jobs\RealTime;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\RealTime\PinGroup;

use PDF;

//class PdfJob implements ShouldQueue
class PdfJob
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

            if (count($pins))
            {
                $name = implode('.', [
                    $this->id,
                    $pg->organisation_id,
                    date('ymdhis'),
                    str_random(32)
                ]);

                $pdf_path = $pg->pdf_path ? $pg->pdf_path : 'storage/outputs/pdf/'.$name.'.pdf';
                $html_path = $pg->html_path ? $pg->html_path : 'storage/outputs/html/'.$name.'.html';

                $html = '<!DOCTYPE html>'.PHP_EOL;
                $html.= '<html lang="'.app()->getLocale().'">'.PHP_EOL;
                $html.= '<head>'.PHP_EOL;
                $html.= '  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>'.PHP_EOL;
                $html.= '  <style>'.PHP_EOL;
                $html.= '    body { font-family: DejaVu Sans, sans-serif; }'.PHP_EOL;
                $html.= '  </style>'.PHP_EOL;
                $html.= '</head>'.PHP_EOL;
                $html.= '<body>'.PHP_EOL;

                file_put_contents(public_path($html_path), $html, LOCK_EX);

                foreach ($pins as $pin)
                {
                    $document = $pin->document();

                    if ($document->status == 'ok')
                    {
                        $type = $document->data['_type'];
                        $source = $document->data['_source'];

                        $sentiment = @$source['sentiment'];

                        $html = '<div class="'.$type.'">'.PHP_EOL;
                        $html.= '<h3>'.$type.'</h3>'.PHP_EOL;
                        $html.= '<time>'.date('d.m.Y H:i:s', strtotime($source['created_at'])).'</time>'.PHP_EOL;

                        if (@$source['title'])
                        {
                            $html.= '<h6 class="teal-text">'.title_case($source['title']).'</h6>'.PHP_EOL;
                        }

                        if ($type == 'tweet')
                        {
                            $html.= '<a href="https://twitter.com/'.$source['user']['screen_name'].'/status/'.$source['id'].'" target="_blank">https://twitter.com/'.$source['user']['screen_name'].'/status/'.$source['id'].'</a>'.PHP_EOL;
                            $html.= '<p>'.PHP_EOL;
                            $html.= '    <a href="https://twitter.com/intent/user?user_id='.$source['user']['id'].'" target="_blank">@'.$source['user']['screen_name'].'</a>'.PHP_EOL;
                            $html.= '    <span>'.$source['user']['name'].'</span>'.PHP_EOL;
                            $html.= '    <span>('.$source['platform'].')</span>'.PHP_EOL;
                            $html.= '</p>'.PHP_EOL;
                            $html.= '<div>'.nl2br($source['text']).'</div>'.PHP_EOL;

                            if (@$source['external'])
                            {
                                $external_source = $pin->document($source['external']['id']);

                                if ($external_source)
                                {
                                    $html.= '<ul>'.PHP_EOL;
                                    $html.= '  <li>'.PHP_EOL;
                                    $html.= '    <div>Asıl Tweet</div>'.PHP_EOL;
                                    $html.= '    <div>'.PHP_EOL;
                                    $html.= '      <div>'.PHP_EOL;
                                    $html.= '        <a href="https://twitter.com/'.$external_source['_source']['user']['screen_name'].'/status/'.$external_source['_source']['id'].'" target="_blank">https://twitter.com/'.$external_source['_source']['user']['screen_name'].'/status/'.$external_source['_source']['id'].'</a>'.PHP_EOL;
                                    $html.= '        <p>'.PHP_EOL;
                                    $html.= '          <a href="https://twitter.com/intent/user?user_id='.$external_source['_source']['user']['id'].'" target="_blank" class="red-text">@'.$external_source['_source']['user']['screen_name'].'</a>'.PHP_EOL;
                                    $html.= '          <span>'.$external_source['_source']['user']['name'].'</span>'.PHP_EOL;
                                    $html.= '          <span>('.$external_source['_source']['platform'].')</span>'.PHP_EOL;
                                    $html.= '        </p>'.PHP_EOL;
                                    $html.= '        <div>'.nl2br($external_source['_source']['text']).'</div>'.PHP_EOL;
                                    $html.= '      </div>'.PHP_EOL;
                                    $html.= '    </div>'.PHP_EOL;
                                    $html.= '  </li>'.PHP_EOL;
                                    $html.= '</ul>'.PHP_EOL;
                                }
                            }
                        }
                        elseif ($type == 'article')
                        {
                            $html.= '<a href="'.$source['url'].'" target="_blank">'.str_limit($source['url'], 96).'</a>'.PHP_EOL;
                            $html.= '<div>'.nl2br($source['description']).'</div>'.PHP_EOL;
                        }
                        elseif ($type == 'entry')
                        {
                            $html.= '<a href="'.$source['url'].'" target="_blank">'.str_limit($source['url'], 96).'</a>'.PHP_EOL;
                            $html.= '<div>'.nl2br($source['entry']).'</div>'.PHP_EOL;
                        }
                        elseif ($type == 'product')
                        {
                            if (@$source['address'])
                            {
                                $html.= '<ul>'.PHP_EOL;

                                foreach ($source['address'] as $key => $segment)
                                {
                                    $html.= '<li>'.$segment['segment'].'</li>'.PHP_EOL;
                                }

                                $html.= '</ul>'.PHP_EOL;
                            }

                            if ($source['breadcrumb'])
                            {
                                $html.= '<ul>'.PHP_EOL;

                                foreach ($source['breadcrumb'] as $key => $segment)
                                {
                                    $html.= '<li>'.$segment['segment'].'</li>'.PHP_EOL;
                                }

                                $html.= '</ul>'.PHP_EOL;
                            }

                            $html.= '<a href="'.$source['url'].'" target="_blank">'.str_limit($source['url'], 96).'</a>'.PHP_EOL;
                            $html.= '<div>'.PHP_EOL;
                            $html.= '<span>'.title_case($source['seller']['name']).'</span>'.PHP_EOL;

                            if (@$source['seller']['phones'])
                            {
                                $html.= '<ul>'.PHP_EOL;

                                foreach ($source['seller']['phones'] as $key => $phone)
                                {
                                    $html.= '<li>'.$phone['phone'].'</li>'.PHP_EOL;
                                }

                                $html.= '</ul>'.PHP_EOL;
                            }

                            $html.= '</div>'.PHP_EOL;

                            if ($source['description'])
                            {
                                $html.= '<div>'.nl2br($source['description']).'</div>'.PHP_EOL;
                            }

                            $html.= '<p>'.PHP_EOL;
                            $html.= '  <span>'.number_format($source['price']['amount']).'</span>'.PHP_EOL;
                            $html.= '  <span>'.$source['price']['currency'].'</span>'.PHP_EOL;
                            $html.= '</p>'.PHP_EOL;
                        }
                        elseif ($type == 'comment')
                        {
                            $html.= '<a href="https://www.youtube.com/watch?v='.$source['video_id'].'" target="_blank">https:\/\/www.youtube.com/watch?v='.$source['video_id'].'</a>'.PHP_EOL;
                            $html.= '<p>'.PHP_EOL;
                            $html.= '  <a href="https://www.youtube.com/channel/'.$source['channel']['id'].'" target="_blank">@'.$source['channel']['title'].'</a>'.PHP_EOL;
                            $html.= '</p>'.PHP_EOL;
                            $html.= '<div>'.nl2br($source['text']).'</div>'.PHP_EOL;
                        }
                        elseif ($type == 'video')
                        {
                            $html.= '<a href="https://www.youtube.com/watch?v='.$source['id'].'" target="_blank">https://www.youtube.com/watch?v='.$source['id'].'</a>'.PHP_EOL;
                            $html.= '<p>'.PHP_EOL;
                            $html.= '  <a href="https://www.youtube.com/channel/'.$source['channel']['id'].'" target="_blank">@'.$source['channel']['title'].'</a>'.PHP_EOL;
                            $html.= '</p>'.PHP_EOL;

                            if ($source['description'])
                            {
                                $html.= '<div>'.nl2br($source['description']).'</div>'.PHP_EOL;
                            }
                        }

                        $html.= '<div>'.$pin->comment.'</div>'.PHP_EOL;

                        if ($sentiment)
                        {
                            $html.= '<div>'.PHP_EOL;
                            $html.= '  <div style="width: '.($sentiment['pos']*100).'%;"></div>'.PHP_EOL;
                            $html.= '  <div style="width: '.($sentiment['neu']*100).'%;"></div>'.PHP_EOL;
                            $html.= '  <div style="width: '.($sentiment['neg']*100).'%;"></div>'.PHP_EOL;
                            $html.= '</div>'.PHP_EOL;
                        }

                        $html.= '</div>'.PHP_EOL;
                    }
                    else
                    {
                        $html.= '<p>Kaynak Okunamadı.</p>'.PHP_EOL;
                    }

                    file_put_contents(public_path($html_path), $html, FILE_APPEND | LOCK_EX);
                }

                file_put_contents(public_path($html_path), $html, FILE_APPEND | LOCK_EX);

                $pdf = PDF::loadFile(public_path($html_path))->save(public_path($pdf_path));

                $pg->html_path = $html_path;
                $pg->pdf_path = $pdf_path;
            }

            $pg->html_to_pdf = 'success';
            $pg->completed_at = date('Y-m-d H:i:s');

            $pg->save();

            // push bildirim.
        }
    }
}
