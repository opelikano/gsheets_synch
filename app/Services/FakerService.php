<?php

namespace App\Services;

use App\Fakers\GoogleSheetFaker;
use \Exception;

class FakerService
{
    protected $googleSheetsService;
    const BATCH_SIZE = 60;

    public function __construct()
    {
        $this->googleSheetsService = new GoogleSheetsService();
    }

    public function runFaker(string $pageUrl, string $fakerClassName)
    {
        $fakerClass = app()->make('App\\Fakers\\' . $fakerClassName);
        if (!($fakerClass instanceof GoogleSheetFaker)) {
            throw new Exception('Your Faker must be implementation of interface GoogleSheetFaker.');
        }

        $needRowsAmount = $fakerClass->needRowsAmount();

        $data = [];
        $data[] = array_keys($fakerClass->definition()); //header

        list($fileId, $shitId) = $this->googleSheetsService->parsePageParam($pageUrl);
        $sheet = $this->googleSheetsService->getSheetById($fileId, $shitId);

        for ($i = 2; $i <= $needRowsAmount; $i++) {
            $data[] = array_values($fakerClass->definition());
            if ($i % self::BATCH_SIZE == 0 || $i == $needRowsAmount) {
                $this->googleSheetsService->append($fileId, $sheet->title, $data);
                $data = [];
                sleep(1);
            }
        }
    }
}