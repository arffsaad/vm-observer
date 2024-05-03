
<div class="w-screen h-screen bg-gradient-to-br from-slate-400 to-slate-100 p-16">
    <div class="p-4 text-right sm:fixed sm:top-0 sm:right-0 flex">
      <a href="{{ route('home') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500 pr-4">Home</a>
      <form method="post" action="{{ route('logout') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500">@csrf<button type="submit">Logout</button></form>
    </div>
    <div class="bg-white w-full h-full rounded-2xl grid grid-cols-[1fr_4fr]">
        <div class="relative rounded-l-2xl border-r-2 bg-gradient-to-b from-white to-slate-100 flex flex-col gap-y-4 p-4 overflow-y-auto">
            <div wire:click="createDevice" class="rounded-lg bg-slate-100 w-full h-auto p-4 border flex justify-center font-bold items-center cursor-pointer hover:scale-[1.02] active:shadow-sm active:scale-[0.98] transition">
                <span>+ Add new device</span>
            </div>
            @if($devices)
                @foreach($devices as $device)
                    @if($device->hostname != null)
                    <div wire:key="device-{{ $device->id }}" wire:click="viewDevice({{ $device }})" class="rounded-lg bg-slate-100 w-full h-auto p-4 border justify-start font-bold items-center cursor-pointer hover:scale-[1.02] active:shadow-sm active:scale-[0.98] transition">
                        <div class="text-xl italic flex justify-between items-center">
                            <h1>{{ $device->hostname }}</h1>
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 @if(time() - strtotime($device->updated_at) >= 450) bg-red-400 @else bg-green-400 @endif"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 @if(time() - strtotime($device->updated_at) >= 450) bg-red-500 @else bg-green-500 @endif"></span>
                            </span>
                        </div>
                        <h1 class="text-sm font-semibold">Used disk space: <span class="font-normal">{{ round((($device->disk_used / $device->disk_total) * 100), 2) }}%</span></h1>
                    </div>
                    @endif
                @endforeach
            @endif
            @if($listLoading)
            <div class="absolute bg-white w-full h-full inset-0 z-10 opacity-50 flex items-center justify-center">
                <x-fas-spinner class="h-10 w-10 animate-spin"/>
            </div>
            @endif
        </div>
        
        <div class="relative p-8 flex flex-col h-full bg-white rounded-r-2xl overflow-y-auto" id="deviceView">
            <div class="absolute inset-0 w-full h-full flex items-center justify-center bg-white opacity-60 @if(!$contentLoading) hidden @endif">
                <div class="absolute bg-white w-full h-full inset-0 z-10 opacity-50 flex items-center justify-center">
                    <x-fas-spinner class="h-10 w-10 animate-spin"/>
                </div>
            </div>
            @if($onboarding)
            <div class="p-8 flex flex-col gap-y-8 h-full bg-white rounded-r-2xl"
                id="deviceOnboarding">
                <h2 class="font-bold text-2xl">Add new device</h2>
                <div class="flex flex-col gap-y-2">
                    <h3 class="text-md font-semibold underline">1. Install Collector Agent</h3>
                    <code class="block whitespace-pre overflow-x-auto p-4 rounded-lg bg-slate-300">{{ $installCmd ? $installCmd : "" }}</code>
                </div>
                <div class="flex flex-col gap-y-2">
                    <h3 class="text-md font-semibold underline">2. Waiting for connection</h3>
                    <p>When the connection succeeds, this section will update and the device will be registered!</p>
                    <div wire:poll="waitOnboard" class="flex-grow rounded-lg bg-slate-200 border-2 p-4">
                        {{ $connected ? "Device Connected!" : "Waiting for connection..." }}
                        @if($connected)
                        <span>Disk Usage is: {{ round((($connected->disk_used / $connected->disk_total) * 100), 2) }}%</span>
                        @endif
                    </div>
                    @if($connected)
                    <button wire:click="resetOnboarder" class="p-3 rounded-lg bg-sky-500 w-1/7 self-center text-sm text-white font-bold mt-4 hover:bg-sky-700 hover:scale-[1.04] transition">Complete Setup</button>
                    @endif
                </div>
            </div>
            @elseif($currentDevice)
            <div class="grid grid-cols-2 border-b-2 pb-6">
                <div class="justify-self-start font-bold text-2xl">
                    <x-fas-computer class="w-14 h-14"/>
                    <h2>{{$currentDevice['hostname']}}</h2>
                </div>
                <div class="justify-self-end flex items-center gap-x-2 self-start">
                    <span class="relative flex h-3 w-3">
                        @if(!(time() - strtotime($currentDevice['updated_at']) >= 450))
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        @endif
                        <span class="relative inline-flex rounded-full h-3 w-3 @if(time() - strtotime($currentDevice['updated_at']) >= 450) bg-red-500 @else bg-green-500 @endif"></span>
                      </span>
                    <h2 class="text-md">{{ (time() - strtotime($currentDevice['updated_at']) >= 450) ? "Offline" : "Online" }}</h2>
                </div>
                <h3 class="text-sm italic">Last update received on: {{ date("Y-m-d H:i", strtotime($currentDevice['updated_at'])) }}</h3>
            </div>
            <div class="grid grid-cols-[1fr_6fr] h-full w-full">
                <div class="h-full w-full border-r-2 grid grid-cols-1 grid-rows-8">
                    <div class="p-4 bg-slate-200 flex items-center">Disk Usage</div>
                    <div class="p-4 flex items-center hover:bg-slate-200"></div> <!-- in case more metrics are added (cpu usage etc) -->
                    <div class="p-4 flex items-center hover:bg-slate-200"></div>
                    <div class="p-4 flex items-center hover:bg-slate-200"></div>
                    <div class="p-4 flex items-center hover:bg-slate-200"></div>
                    <div class="p-4 flex items-center hover:bg-slate-200"></div>
                    <div class="p-4 flex items-center hover:bg-slate-200"></div>
                    <div class="p-4 flex items-center hover:bg-slate-200"></div>
                </div>
                <div class="h-full w-full p-6">
                    <div class="justify-self-start font-bold text-2xl h-12">
                        <h2>Disk Usage</h2>
                        <h3 class="font-bold text-sm">Total Space: {{ round(($currentDevice['disk_total'] / (1024*1024*1024)), 2)}}GB</h3>
                        <h3 class="font-bold text-sm">Used Space: {{ round(($currentDevice['disk_used'] / (1024*1024*1024)), 2)}}GB</h3>
                        <h3 class="font-bold text-sm">Available Space: {{ round(($currentDevice['disk_free'] / (1024*1024*1024)), 2)}}GB</h3>
                    </div>
                    <div class="w-full h-full flex justify-center items-center px-8">
                        <div class="relative h-10 w-full bg-slate-200 rounded-xl">
                            @if($currentDevice)
                            <div class="absolute h-full w-[5%] bg-gradient-to-r from-green-300 to-green-800 rounded-xl flex justify-end items-center font-bold overflow-x-hidden">
                            </div>
                            <p class="absolute p-2 font-bold text-black">{{ round((($currentDevice['disk_used'] / $currentDevice['disk_total']) * 100), 2) }}%</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="flex justify-center items-center w-full h-full">
                <h2 class="text-lg font-semibold italic">Select a device or <span wire:click="createDevice" class="underline cursor-pointer">Add a device</span></h2>
            </div>
            @endif
        </div>
    </div>
</div>
  