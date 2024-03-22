<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SynchDataController;
use App\Services\SynchDataService;

class SynchData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:synch-data {pageUrl=all} {rows=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It is for transferring data from GoogleSheets in accordance with gImport.' .
            PHP_EOL . "\t" . ' pageUrl - link on GoogleSheet page or all, default: all' .
            PHP_EOL . "\t" . ' rows - range of rows when pageUrl is not all, default: all';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rows = $this->argument('rows');
        if (!preg_match('/^(all|\d+(?:-\d*)?)$/', $rows)) {
            $err = 'You can specify next parameters for rows range:.' . PHP_EOL;
            $err .= "\tall - for import all rows in specified document" . PHP_EOL;
            $err .= "\t10-128 - for range of rows" . PHP_EOL;
            $err .= "\t10- - for rows beginning from row 10 and to the end of document" . PHP_EOL;
            $err .= "\t56 - for single row" . PHP_EOL;
            $this->error($err);
            return;
        }

        (new SynchDataController(new SynchDataService()))
            ->index($this->argument('pageUrl'), $rows);
    }
}
