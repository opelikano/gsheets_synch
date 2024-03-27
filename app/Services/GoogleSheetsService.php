<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\SheetProperties;
use Google\Service\Sheets\ValueRange;
use Exception;

class GoogleSheetsService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName(config('app.name'));
        $this->client->setScopes(Sheets::SPREADSHEETS);
//        $this->client->setScopes(Sheets::SPREADSHEETS_READONLY);
        $this->client->setAuthConfig(env('GOOGLE_CREDENTIAL_FILE'));
        $this->client->setAccessType('offline');
        $this->service = new Sheets($this->client);
    }

    public function getSheets(string $spreadsheetId)
    {
        $response = $this->service->spreadsheets->get($spreadsheetId);
        return $response->getSheets();
    }

    public function getData(string $spreadsheetId, string $range): array
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

    public function update(string $spreadsheetId, string $range, $requestBody)
    {
        $response = $this->service->spreadsheets_values->update($spreadsheetId, $range,
            $requestBody, ['valueInputOption' => 'RAW']
        );
        return $response;
    }

    public function append(string $spreadsheetId, string $range, array $data)
    {
        $requestBody = new ValueRange(['values' => $data]);
        return $this->service->spreadsheets_values->append(
            $spreadsheetId,
            $range,
            $requestBody,
            ['valueInputOption' => 'RAW']
        );
    }

    /**
     * @param string $fileId
     * @param string $sheetId
     * @return SheetProperties
     * @throws Exception
     */
    public function getSheetById(string $fileId, string $sheetId): SheetProperties
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
