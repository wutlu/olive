<?php

namespace App\Console\Commands\Alarm;

use Illuminate\Console\Command;

use App\Models\Alarm;
use App\Models\User\User;

use DB;
use Mail;

use App\Mail\NewsletterMail;

class Trigger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alarm:trigger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Zamanı gelmiş alarmların kontrolü.';

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
        $day = implode('_', [ 'day', intval(date('N')) ]);

        $alarms = Alarm::where('hit', '<>', 0)
                       ->where('weekdays', 'LIKE', '%'.$day.'%')
                       ->where('start_time', '<=', date('H:i'))
                       ->where('end_time', '>=', date('H:i'))
                       ->where('sended_at', '<=', DB::raw("NOW() - INTERVAL '1 minutes' * interval"))
                       ->get();

        if (count($alarms))
        {
            foreach ($alarms as $alarm)
            {
                $markdown[] = 'Bu alarm için x dakika sonra 1 ve toplamda x bildirim daha alacaksınız.';
                $markdown[] = '';
                $markdown[] = '**Başlıca İçerikler**';
                $markdown[] = '';
                $markdown[] = 'Tüm içerikler için aşağıdaki bağlantıya gidin.';

                $link = route('search.dashboard', [ 'q' => urlencode('araba || ev'), 's' => '2019-02-22', 'e' => '2019-02-22' ]);

                $alarm->update([
                    //'sended_at' => date('Y-m-d H:i:s'),
                    //'hit' => $alarm->hit-1
                ]);

                Mail::queue(
                    new NewsletterMail(
                        'Alarm 🔔 '.$alarm->name.' 🔔',
                        implode(PHP_EOL, $markdown),
                        $link,
                        User::whereIn('id', $alarm->emails)->get()->pluck('email')
                    )
                );

                $this->info($alarm->name);
            }
        }
    }
}
