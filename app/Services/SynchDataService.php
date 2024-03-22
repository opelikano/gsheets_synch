<?php

namespace App\Services;

use Illuminate\Support\Facades\Db;
use Exception;

class SynchDataService
{
    protected $googleSheetsService;

    public const ROWS_ALL = 'all';
    public const PAGE_ALL = 'all';

    public const MODE_RANGE = 'range';
    public const MODE_ALL = 'all';
    public const MODE_SINGLE = 'single';
    public const MODE_FROM_POSITION = 'from_position';

    public const BATCH_SIZE = 50;

    public function __construct()
    {
        $this->googleSheetsService = new GoogleSheetsService();
    }

    private function modeDefinition(string $rows): string
    {
        if ($rows == self::ROWS_ALL) return self::MODE_ALL;
        if (!str_contains($rows, '-')) return self::MODE_SINGLE;
//        if (str_contains($rows, '-')) {
        else {
            $arr = explode('-', $rows);
            if ($arr[1] != false) return self::MODE_RANGE;
            else return self::MODE_FROM_POSITION;
        }
    }

    private function rowsDefinition($mode, $rows): array
    {
        if ($mode == self::MODE_RANGE) {
            return explode('-', $rows);
        }
        elseif ($mode == self::PAGE_ALL) {
            return [2, false];
        }
        else return [explode('-', $rows)[0] , false];
    }

    public function synchColumn(string $pageUrl, string $column)
    {
        $sheetConfig = $this->getSheetConfig($pageUrl)[0];
        $header = $this->getHeader($sheetConfig);

        $columnIndex = array_search($column, $header);
        if (!$columnIndex) {
            throw new Exception('Specified incorrect column, please, check. This column must be in google sheet file and in gImport.');
        }

        $literaColumn = $this->googleSheetsService->getColumnLetter($columnIndex);
        $startRow = 2;
        $i = 0;

        while(true) {
            $startBatchRow = $startRow + $i * self::BATCH_SIZE;
            $endBatchRow = $startBatchRow + self::BATCH_SIZE - 1;
            if ($startBatchRow > $endBatchRow) {
                break;
            }

            echo 'Start batch row: ' . $startBatchRow . PHP_EOL;
            echo 'End batch row: ' . $endBatchRow . PHP_EOL;

            $range = $sheetConfig['sheet'] . '!' . $literaColumn . $startBatchRow . ':' . $literaColumn . $endBatchRow;
            $googleData = $this->googleSheetsService->getData($sheetConfig['file_id'], $range);
            $preparedData = $this->prepareData(
                $googleData,
                $sheetConfig,
                [$header[$columnIndex]],
                $startBatchRow,
                $endBatchRow
            );
            $this->saveData($preparedData);
            if (count($googleData) < self::BATCH_SIZE) {
                break;
            }
            sleep(2);
            $i++;
        }
    }

    public function synchData(string $pageUrl, string $rows)
    {
        $sheetsParams = ($pageUrl == self::PAGE_ALL) ? config('gImport') : $this->getSheetConfig($pageUrl);
        $mode = $this->modeDefinition($rows);
        list($startRow, $endRow) = $this->rowsDefinition($mode, $rows);

        foreach ($sheetsParams as $sheetConfig) {
            $header = $this->getHeader($sheetConfig);
            $i = 0;
            $startColumn = 'A';
            $endColumn = $this->googleSheetsService->getColumnLetter(count($header)-1);

            while (true) {
                $startBatchRow = $startRow + $i * self::BATCH_SIZE;
                $endBatchRow_temp = $startBatchRow + self::BATCH_SIZE - 1;
                $endBatchRow = ($endRow && $endBatchRow_temp > $endRow) ? $endRow : $endBatchRow_temp;
                if ($mode == self::MODE_SINGLE) {
                    $endBatchRow = $startBatchRow;
                }
                if ($startBatchRow > $endBatchRow) {
                    break;
                }
                echo 'Start batch row: ' . $startBatchRow . PHP_EOL;
                echo 'End batch row: ' . $endBatchRow . PHP_EOL;

                $range = $sheetConfig['sheet'] . '!' . $startColumn . $startBatchRow . ':' . $endColumn . $endBatchRow;
                $googleData = $this->googleSheetsService->getData($sheetConfig['file_id'], $range);
                if (!$googleData) {
                    break;
                }

                $preparedData = $this->prepareData(
                    $googleData,
                    $sheetConfig,
                    $header,
                    $startBatchRow,
                    $endBatchRow
                );
                $this->saveData($preparedData);
                if (count($googleData) < self::BATCH_SIZE) {
                    break;
                }
                sleep(2);
                $i++;
            }
        }
    }

    private function getSheetConfig(string $pageUrl)
    {
        list($fileId, $sheetId) = $this->googleSheetsService->parsePageParam($pageUrl);
        $sheetName = $this->googleSheetsService->getSheetById($fileId, $sheetId)->title;

        foreach (config('gImport') as $key => $sheet) {
            if ($sheet['file_id'] == $fileId && $sheet['sheet'] == $sheetName) {
                return [config('gImport.' . $key)];
            }
        }
        return false;
    }

    private function getHeader(array $sheet): array
    {
        $range = $sheet['sheet'] . '!A1:AA1';
        return $this->googleSheetsService->getData($sheet['file_id'], $range)[0];
    }

    public function saveData(array $preparedData)
    {
        foreach ($preparedData as $sqlTableName => $columns) {
            $ids = array_column($columns, 'id');
            $existingIds = array_column(DB::table($sqlTableName)
                ->select('id')
                ->whereIn('id', $ids, 'OR')
                ->get()
                ->toArray(),'id');

            foreach ($columns as $strNumber => $column) {
                if (in_array($column['id'], $existingIds)) {
                    DB::table($sqlTableName)->where('id', $column['id'])->update($column);
                    unset($columns[$strNumber]);
                }
            }
            if (!empty($columns)) {
                DB::table($sqlTableName)->insert($columns);
            }
        }
    }

    private function prepareData(
        array $googleData,
        array $sheetConfig,
        array $header,
        int $startRow,
        int $endRow
    )
    {
        $rowNumbers = range($startRow, $endRow);
        $preparedData = [];
        for ($i = 0; $i < count($googleData); $i++) {
            foreach ($googleData[$i] as $k2 => $v) {
                if (!isset($header[$k2]) || !isset($sheetConfig['columns'][$header[$k2]])) continue;
                foreach ($sheetConfig['columns'][$header[$k2]] as $k3 => $tableAndColumn) {
                    list($sqlTableName, $sqlColumnName) = explode(".", $tableAndColumn);
                    $preparedData[$sqlTableName][$rowNumbers[$i]][$sqlColumnName] = $v;
                }
            }

            //if file id column not exists add column id
            foreach ($preparedData as $tableName => $dataForTable) {
                foreach ($dataForTable as $strNumber => $column) {
                    if (!isset($column['id'])) {
                        $preparedData[$tableName][$strNumber]['id'] = $strNumber;
                    }
                }
            }
        }
        return $preparedData;
    }
}
