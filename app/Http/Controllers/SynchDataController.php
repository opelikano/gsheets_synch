<?php

namespace App\Http\Controllers;

use App\Services\SynchDataService;
use App\Services\FakerService;

class SynchDataController extends Controller
{
    public function synchColumn(string $pageUrl, string $column)
    {
        (new SynchDataService())->synchColumn($pageUrl, $column);
    }

    public function index(string $pageUrl, string $rows)
    {
        (new SynchDataService())->synchData($pageUrl, $rows);
    }

    public function runFaker(string $pageUrl, string $fakerClass)
    {
        (new FakerService())->runFaker($pageUrl, $fakerClass);
    }
}
