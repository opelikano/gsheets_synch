<?php

return [
    'imp_table' => [
        "file_id" => "1UGWw4ZI58vEnuovHgqGfTvQ_PGQ0sF6z-t4ghZkVZ4c",
        "sheet" => "sheet_2",
        "columns" => [
            "name" => ["main_table.name"],
            "user_email" => ["main_table.user_email"],
            "country" => ["main_table.country"],
            "job_title" => ["main_table.job_title"],
            "number" => ["main_table.number", "tab2.number"],
            "company" => ["main_table.company", "tab2.company_name"],
            "street" => ["main_table.street", "tab2.company_street"],
            "city" => ["main_table.city", "tab2.company_street"],
            "date" => ["main_table.date"],
        ],
        "column_types" => [
            "number" => "integer"
        ],
    ],
    'second' => [
        "file_id" => "1UGWw4ZI58vEnuovHgqGfTvQ_PGQ0sF6z-t4ghZkVZ4c",
        "sheet" => "sheet_1",
        "columns" => [
            "column" => ["second.column"],
            "name" => ["second.name"],
            "title" => ["second.title"],
            "author" => ["second.author"],
            "date" => ["second.date"],
            "new_column" => ["second.new_column"],
        ],
        "column_types" => [
        ],
    ],
];
