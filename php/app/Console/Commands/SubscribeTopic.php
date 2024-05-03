<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use App\Http\Controllers\KafkaController;

class SubscribeTopic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:subscribe-topic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $consumer = Kafka::consumer()
        ->subscribe('^deviceMetrics_.*')
        ->withHandler(new KafkaController)
        ->build();

        $consumer->consume();
    }
}
