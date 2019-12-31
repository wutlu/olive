<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Report;
use App\Models\User\User;

use App\Notifications\MessageNotification;

class ReportAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Açık unutulmuş raporlar için bildirim gönderimini tetikler.';

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
        $users = User::with('report')->whereNotNull('report_id')->whereHas('Report', function($query) {
            $query->where('updated_at', '<=', date('Y-m-d H:i:s', strtotime('-1 days')));
        })->get();

        if (count($users))
        {
            foreach ($users as $user)
            {
                $this->info($user->email);

                $report = $user->report;
                $report->updated_at = date('Y-m-d H:i:s');
                $report->save();

                if ($user->notification('important'))
                {
                    $this->info('Bildirim gönderildi.');

                    $user->notify(
                        (
                            new MessageNotification(
                                'Küçük Bir Hatırlatma 😇',
                                'Merhaba, '.$user->name,
                                'Tamamlanmamış bir raporunuz var. Unutmadıysanız bu e-postayı dikkate almayın 😅'
                            )
                        )->onQueue('email')
                    );
                }
            }
        }
        else
        {
            $this->info('Hatırlatılacak rapor yok.');
        }
    }
}
