<?php

namespace App\Console\Commands;

use App\Jobs\FetchAqiJob;
use App\Models\City;
use Illuminate\Console\Command;

class FetchAqiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch';

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
            $cities = City::select('name', 'state')->get();
    
            foreach ($cities as $city) {
                dispatch(new FetchAqiJob($city->name, $city->state));
            }
    
            $this->info('AQI jobs dispatched successfully.');
        }
}
