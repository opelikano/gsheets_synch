<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SynchStructureController;

class SynchStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:synch-structure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update database structure in accordance with gImport';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        (new SynchStructureController())->index();
    }
}
