<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Device;
use App\Models\User;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

class MainDashboard extends Component
{
    public $listLoading = true;
    public $contentLoading = false;
    public $devices;
    public $currentDevice = null;
    public $onboarding = false;
    public $onboardID;
    public $connected = null;
    public $installCmd = "";

    public function render()
    {
        $this->refreshList();
        return view('livewire.main-dashboard');
    }

    public function createDevice() 
    {   
        // CHECK IF THERE IS AN ORPHAN DEVICE
        $orphan = Device::whereNull('hostname')->first();
        if ($orphan) {
            $new = $orphan;
        } else {
            // if no orphans, create a new device
            $new = new Device;
            $new->user_id = auth()->id();
            $new->save();
        }

        // the server is preconfigured to be allowed to create a new topic for this device. Produce an intro msg to init the topic
        $topic = env("KAFKA_TOPIC_PREFIX", "deviceMetrics_") . $new->id;
        $producer = Kafka::publish('broker')->onTopic($topic);
        $producer->send();

        // hash both kafkaserver and hostid into b64
        $hashData = env("KAFKA_BROKERS", "localhost:9092") . "|" . $new->id;
        $hashed = base64_encode($hashData);

        $this->onboarding = true;
        $this->onboardID = $new->id;

        $this->installCmd = "wget somescript.sh | sh $hashed";
    }

    public function refreshList() {
        $this->listLoading = true;
        $this->devices = User::find(auth()->id())->devices()->get();
        $this->listLoading = false;
    }

    public function waitOnboard() {
        $device = Device::find($this->onboardID);
        if ($device->hostname != null) {
            $this->connected = $device;
            $this->refreshList();
        }
    }

    public function resetOnboarder() {
        $this->onboarding = false;
        $this->onboardID = null;
        $this->connected = null;
        $this->refreshList();
    }

    public function viewDevice($device) {
        $this->resetOnboarder();
        $this->contentLoading = true;
        $this->currentDevice = $device;
        $this->contentLoading = false;
    }

    // public function pollDevice() {

    // }
}
