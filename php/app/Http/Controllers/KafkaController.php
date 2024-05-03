<?php

namespace App\Http\Controllers;

use App\Models\Device;

class KafkaController extends Controller
{
    public function __invoke(\Junges\Kafka\Contracts\ConsumerMessage $message, \Junges\Kafka\Contracts\MessageConsumer $consumer) {
        $hostid = str_replace("deviceMetrics_", "", $message->getTopicName());
        $data = $message->getBody();
        if (isset($data['hostname']))
        {
            $device = Device::find($hostid);
            $device->hostname = $data['hostname'];
            $device->disk_total = $data['disk']['total'];
            $device->disk_free = $data['disk']['free'];
            $device->disk_used = $data['disk']['used'];
            $device->save();
        }
    }
}
