<?php

namespace App\Jobs\PDF;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Archive\Archive;

use PDF;
use System;

use App\Utilities\UserActivityUtility as Activity;

class ArchiveJob implements ShouldQueue
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
        $archive = Archive::where('id', $this->id)->first();

        if (@$archive)
        {
            $items = $archive->items;

            if (count($items) >= 1 && count($items) <= 100)
            {
                $name = implode('.', [ $this->id, $archive->organisation_id, date('ymdhis'), str_random(32) ]);

                $html_path = $archive->html_path ? $archive->html_path : 'storage/outputs/html/'.$name.'.html';
                $pdf_path = $archive->pdf_path ? $archive->pdf_path : 'storage/outputs/pdf/'.$name.'.pdf';

                if (file_exists(public_path($pdf_path)))
                {
                    unlink(public_path($pdf_path));
                }

                $view = view('layouts.pdf.archive', compact('archive', 'items'));

                file_put_contents(public_path($html_path), $view, LOCK_EX);

                $archive->html_path = $html_path;
                $archive->pdf_path = $pdf_path;

                try
                {
                    $pdf = PDF::loadFile(public_path($html_path))->save(public_path($pdf_path));

                    $archive->html_to_pdf = 'success';

                    foreach ($archive->organisation->users as $user)
                    {
                        Activity::push(
                            'Arşiv dökümünüz hazırlandı.',
                            [
                                'push' => true,
                                'icon' => 'picture_as_pdf',
                                'user_id' => $user->id,
                                'key' => implode('-', [ $user->id, 'pdf', 'export', $archive->id ]),
                                'button' => [
                                    'type' => 'http',
                                    'method' => 'GET',
                                    'action' => url($archive->pdf_path).'?v='.date('dmyHi'),
                                    'class' => 'btn-flat waves-effect',
                                    'text' => 'İndir'
                                ]
                            ]
                        );
                    }
                }
                catch (\Exception $e)
                {
                    System::log(json_encode([ $e->getMessage() ]), 'App\Jobs\PDF\ArchiveJob::handle('.$this->id.')', 10);

                    foreach ($archive->organisation->users as $user)
                    {
                        Activity::push(
                            'Arşiv dökümü alınırken bir sorun oluştu.',
                            [
                                'markdown' => 'Arşiv dökümünüzü hazırlarken bir problemle karşılaştık. Lütfen tekrar deneyin.',
                                'push' => true,
                                'icon' => 'picture_as_pdf',
                                'user_id' => $user->id,
                                'key' => implode('-', [ $user->id, 'pdf', 'export', $archive->id ])
                            ]
                        );
                    }
                }
            }

            $archive->completed_at = date('Y-m-d H:i:s');
            $archive->save();
        }
    }
}
