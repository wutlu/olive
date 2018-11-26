<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Sentiment;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $keys = [ 'neg' => 'Negatif', 'pos' => 'Pozitif', 'neu' => 'Nötr' ];
        $strings = [
            'Weather today is rubbish',
            'This cake looks amazing',
            'His skills are mediocre',
            'He is very talented',
            'She is seemingly very agressive',
            'Marie was enthusiastic about the upcoming trip. Her brother was also passionate about her leaving - he would finally have the house for himself.',
            'To be or not to be?',
        ];

        $sentiment = new Sentiment();

        foreach ($strings as $string)
        {
            $scores = $sentiment->score($string);
            $class = $sentiment->categorise($string);

            $this->line('');

            $this->info($string);

            switch ($class)
            {
                case 'neg': $this->error($keys[$class]); break;
                case 'neu': $this->line($keys[$class]); break;
                case 'pos': $this->info($keys[$class]); break;
            }

            print_r($scores);

            $this->line('');
        }
    }
}
