
<div class="w-screen h-screen bg-gradient-to-br from-slate-400 to-slate-100 p-16">
    <div class="p-4 text-right sm:fixed sm:top-0 sm:right-0 flex">
      <a href="{{ route('home') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500 pr-4">Home</a>
      <form method="post" action="{{ route('logout') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500">@csrf<button type="submit">Logout</button></form>
    </div>
    <div class="bg-white w-full h-full rounded-2xl grid grid-cols-[1fr_4fr]">
        <div class="rounded-l-2xl border-r-2 bg-gradient-to-b from-white to-slate-100 flex flex-col gap-y-4 p-4 overflow-y-auto">
            <div class="rounded-lg bg-slate-100 w-full h-auto p-4 border flex justify-center font-bold items-center cursor-pointer hover:scale-[1.02] active:shadow-sm active:scale-[0.98] transition">
                <span>+ Add new device</span>
            </div>
            <div class="rounded-lg bg-slate-100 w-full h-auto p-4 border justify-start font-bold items-center cursor-pointer hover:scale-[1.02] active:shadow-sm active:scale-[0.98] transition">
                <h1 class="text-xl">ubuntu-server-01</h1>
                <h1 class="text-sm font-semibold">Available disk space: <span class="font-normal">45%</span></h1>
            </div>
        </div>
        <div class="p-8 flex flex-col h-full bg-white rounded-r-2xl overflow-y-auto" id="mainContent">
            <div class="grid grid-cols-2 border-b-2 pb-6">
                <div class="justify-self-start font-bold text-2xl">
                    <x-fas-computer class="w-14 h-14"/>
                    <h2>ubuntu-server-01</h2>
                </div>
                <div class="justify-self-end flex items-center gap-x-2 self-start">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                      </span>
                    <h2 class="text-md">Online</h2>
                </div>
                <h3 class="text-sm italic">Last update received on: 5/1/2024 10:00 AM</h3>
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
                        <h3 class="font-bold text-sm">Total Space: 100GB</h3>
                        <h3 class="font-bold text-sm">Used Space: 55GB</h3>
                        <h3 class="font-bold text-sm">Available Space: 45GB</h3>
                    </div>
                    <div class="w-full h-full flex justify-center items-center px-8">
                        <div class="relative h-10 w-full bg-slate-200 rounded-xl">
                            <div class="absolute h-full w-[55%] bg-gradient-to-r from-lime-400 to-red-400 rounded-xl flex justify-end items-center font-bold text-md px-4">55%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
  