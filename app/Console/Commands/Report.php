<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\SavedSearch;

use App\Jobs\ReportJob;

class Report extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:trigger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Günlük rapor tetikleyicisi.';

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
        $ss = SavedSearch::where('daily_report', true)->get();

        if (count($ss))
        {
            foreach ($ss as $search)
            {
                $this->info($search->name);

                if ($search->organisation->status)
                {
                    ReportJob::dispatch($search)->onQueue('process');
                }
                else
                {
                    $this->error('Organizasyon süresi bitmiş.');
                }
            }
        }
        else
        {
            $this->error('Kayıtlı arama bulunmuyor.');
        }
    }
}
