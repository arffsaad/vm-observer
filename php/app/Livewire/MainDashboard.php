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

        // hash kafkaserver, hostid, user, and password into b64
        $hashData = env("KAFKA_COLLECTOR_SERVER", "localhost:9092") . "|" . $new->id . "|" . env("KAFKA_COLLECTOR_USER", "user") . "|" . env("KAFKA_COLLECTOR_PASSWORD", "password");
        $hashed = base64_encode($hashData);

        $this->onboarding = true;
        $this->onboardID = $new->id;

        $this->installCmd = "curl -s " . env("APP_URL") . "/installer | bash -s $hashed";
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

    public function pollDevice() {
        $this->contentLoading = true;
        $deviceID = $this->currentDevice['id'];
        $this->currentDevice = null;
        $this->currentDevice = Device::find($deviceID)->toArray();
        $this->contentLoading = false;
    }   
}
