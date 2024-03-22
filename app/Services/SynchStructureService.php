<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;

class SynchStructureService
{
    const DEFAULT_COLUMN_TYPE = 'string';

    public function synchronize()
    {
        $confData = $this->prepareConfigData();

        $needToCreateTables = $this->getTablesToCreate($confData);
        $this->showTablesInfo($needToCreateTables);
        $this->createTables($needToCreateTables);

        $needToCreateColumns = $this->getColumnsToCreate($confData);
        $this->showCreateColumnInfo($needToCreateColumns);
        $this->createColumns($needToCreateColumns);

        $needToRemoveColumns = $this->getColumnsToRemove($confData);
        $this->showRemoveColumnInfo($needToRemoveColumns);
        $this->removeColumns($needToRemoveColumns);
    }

    private function showTablesInfo(array $needToCreateTables): void
    {
        if (empty($needToCreateTables)) {
            echo 'No tables for create.' . PHP_EOL;
            return;
        }
        echo 'Creating next tables: ' . PHP_EOL;
        foreach ($needToCreateTables as $tableName => $data) {
            echo "\t" . $tableName . PHP_EOL;
        }
        echo PHP_EOL;
    }

    private function showCreateColumnInfo(array $needToCreateColumns): void
    {
        if (empty($needToCreateColumns)) {
            echo 'No columns for create.' . PHP_EOL;
            return;
        }
        echo 'Adding next columns to existing tables: ' . PHP_EOL;
        foreach ($needToCreateColumns as $sqlTableName => $columns) {
            if (empty($columns)) {
                continue;
            }
            foreach ($columns as $column => $type) {
                echo "\t" . $sqlTableName . '.' . $column . PHP_EOL;
            }
        }
        echo PHP_EOL;
    }

    private function showRemoveColumnInfo(array $needToRemoveColumns): void
    {
        if (empty($needToRemoveColumns)) {
            echo 'No columns for remove.' . PHP_EOL;
            return;
        }
        echo 'Removing next columns from existing tables: ' . PHP_EOL;
        foreach ($needToRemoveColumns as $sqlTableName => $columns) {
            foreach ($columns as $column) {
                echo "\t" . $sqlTableName . '.' . $column . PHP_EOL;
            }
        }
        echo PHP_EOL;
    }

    private function prepareConfigData(): array
    {
        $tables = [];
        foreach (config('gImport') as $dataBySheet) {
            foreach ($dataBySheet['columns'] as $googleColumnName => $tableColumns) {
                foreach ($tableColumns as $tableAndColumn) {
                    list($sqlTableName, $sqlColumnName) = explode(".", $tableAndColumn);
                    $tables[$sqlTableName][$sqlColumnName] = isset($dataBySheet['column_types'][$googleColumnName])
                        ? $dataBySheet['column_types'][$googleColumnName]
                        : self::DEFAULT_COLUMN_TYPE;
                }
            }
        }
        return $tables;
    }

    private function getTablesToCreate(array $data): array
    {
        foreach ($data as $sqlTableName => $columns) {
            if (Schema::hasTable($sqlTableName)) {
                unset($data[$sqlTableName]);
            }
        }
        return $data;
    }

    private function getColumnsToCreate(array $data): array
    {
        foreach ($data as $sqlTableName => $columns) {
            $existedColumns = Schema::getColumnListing($sqlTableName);
            foreach ($columns as $columnName => $columnType) {
                if (in_array($columnName, $existedColumns)) {
                    unset($data[$sqlTableName][$columnName]);
                    if (count($data[$sqlTableName]) == 0) {
                        unset($data[$sqlTableName]);
                    }
                }
            }
        }
        return $data;
    }

    private function getColumnsToRemove(array $data): array
    {
        $array = [];
        foreach ($data as $sqlTableName => $columns) {
            if (!Schema::hasTable($sqlTableName)) {
                continue;
            }
            $existedColumns = Schema::getColumnListing($sqlTableName);
            foreach ($existedColumns as $existedColumnName) {
                if ($existedColumnName == 'id') {
                    continue;
                }
                if (!in_array($existedColumnName, array_keys($columns))) {
                    $array[$sqlTableName][] = $existedColumnName;
                }
            }
        }
        return $array;
    }

    private function removeColumns(array $needToRemoveColumns): void
    {
        if (!empty($needToRemoveColumns)) {
            foreach ($needToRemoveColumns as $sqlTableName => $needRemoveColumns) {
                Schema::table($sqlTableName, function ($table) use ($needRemoveColumns) {
                    foreach ($needRemoveColumns as $sqlColumn) {
                        $table->dropColumn($sqlColumn);
                    }
                });
            }
        }
    }

    private function createColumns(array $needToCreateColumns): void
    {
        foreach ($needToCreateColumns as $sqlTableName => $columns) {
            if (empty($columns)) {
                continue;
            }
            Schema::table($sqlTableName, function ($table) use ($columns) {
                foreach ($columns as $sqlColumnName => $sqlColumnType) {
                    $table->$sqlColumnType($sqlColumnName)->nullable()->default(null);
                }
            });
        }
    }

    private function createTables(array $needToCreateTables): void
    {
        if (!empty($needToCreateTables)) {
            foreach ($needToCreateTables as $sqlTableName => $columns) {
                Schema::create($sqlTableName, function ($table) use ($columns) {
                    $table->integer('id')->unsigned();
                    $table->unique('id');
                    $table->primary('id');

                    foreach ($columns as $sqlColumn => $sqlType) {
                        if ($sqlColumn == 'id') continue;
                        $table->$sqlType($sqlColumn)->nullable()->default(null);
                    }
                });
            }
        }
    }
}