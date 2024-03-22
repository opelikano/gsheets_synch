<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SynchDataController;

class RunFaker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-faker {pageUrl} {fakerClass}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It is for set fake data to GoogleSheets. Before this command create file with tools in dir /Fakers.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        (new SynchDataController())
            ->runFaker($this->argument('pageUrl'), $fakerClass = $this->argument('fakerClass'));
    }
}
