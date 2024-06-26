<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SynchDataService;
use App\Services\GoogleSheetsService;

class SynchColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:synch-column {pageUrl} {column}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It is for update data from GoogleSheets in accordance with gImport.' .
            PHP_EOL . "\t" . ' pageUrl - link on GoogleSheet page or all' .
            PHP_EOL . "\t" . ' column - name of column in Google Sheets file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        (new SynchDataService(new GoogleSheetsService()))->synchColumn(
            $this->argument('pageUrl'),
            $this->argument('column'),
        );
    }
}
