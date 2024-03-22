<?php

namespace App\Http\Controllers;

use App\Services\SynchStructureService;

class SynchStructureController extends Controller
{
    public function index()
    {
        return (new SynchStructureService())->synchronize();
    }
}