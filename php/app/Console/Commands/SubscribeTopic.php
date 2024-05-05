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
        ->subscribe('deviceMetrics')
        ->withHandler(new KafkaController)
        ->withSasl(
            password: env("KAFKA_CONSUMER_PASSWORD", "password"),
            username: env("KAFKA_CONSUMER_USER", "username"),
            mechanisms: env("KAFKA_SECURITY_MECHANISM", "SCRAM-SHA-256"),
            securityProtocol: env("KAFKA_SECURITY_PROTOCOL", "SASL_SSL"),
        )
        ->build();

        $consumer->consume();
    }
}
