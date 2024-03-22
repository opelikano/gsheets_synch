<?php

namespace App\Services;

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;

class GoogleSheetsService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName(config('app.name'));
        $this->client->setScopes(Google_Service_Sheets::SPREADSHEETS);
//        $this->client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
        $this->client->setAuthConfig(env('GOOGLE_CREDENTIAL_FILE'));
        $this->client->setAccessType('offline');
        $this->service = new Google_Service_Sheets($this->client);
    }

    public function getSheets(string $spreadsheetId) {
        $response = $this->service->spreadsheets->get($spreadsheetId);
        return $response->getSheets();
    }

    public function getData(string $spreadsheetId, string $range)
    {
        $response = $this->service->spreadsheets_values->get($spreadsheetId, $range);
        return $response->getValues();
    }

    public function parsePageParam(string $pageUrl): array
    {
        preg_match('/\/d\/([^\/]+)/', $pageUrl, $fileId);
        preg_match('/gid=([0-9]+)/', $pageUrl, $sheetId);

        return [$fileId[1], $sheetId[1]];
    }

    public function update($spreadsheetId, $range, $requestBody)
    {
        $response = $this->service->spreadsheets_values->update($spreadsheetId, $range,
            $requestBody, ['valueInputOption' => 'RAW']
        );
        return $response;
    }

    public function append($spreadsheetId, $range, $data)
    {
        $requestBody = new Google_Service_Sheets_ValueRange(['values' => $data]);
        $response = $this->service->spreadsheets_values->append(
            $spreadsheetId,
            $range,
            $requestBody,
            ['valueInputOption' => 'RAW']
        );
        return $response;
    }

    public function getSheetById(string $fileId, string $sheetId)
    {
        $sheets = $this->getSheets($fileId);
        foreach ($sheets as $sheet) {
            if ($sheetId == $sheet->getProperties()->sheetId) {
                return $sheet->getProperties();
            }
        }
        throw new Exception('Incorrect link to document or config for this page does not exist. Please, check link and file gImport.');
    }

    public function getColumnLetter(int $columnIndex): string
    {
        $letter = '';
        if ($columnIndex >= 0) {
            while ($columnIndex >= 0) {
                $remainder = ($columnIndex) % 26;
                $letter = chr(65 + $remainder) . $letter;
                $columnIndex = ($columnIndex - $remainder - 1) / 26;
            }
        }

        return $letter;
    }
}