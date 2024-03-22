This project provides data copying from Google Sheets to mySQL. It is used framework Laravel.

## Creating a project and providing access to the API
Creating a GoogleCloud Project:
- Log in to the Google Cloud Console  https://console.cloud.google.com/welcome/
- Create a new project or select an existing one where you'll be working.

Creating a Service Account:
- From the sidebar menu, navigate to "IAM & Admin" -> "Service Accounts".
Click on "Create Service Account".
- Provide a name and description for the service account and click "Create".

Generating Access Key:
- Find your created service account from the list.
- Under the actions for the service account, select "Manage Keys".
- Click on "Add Key" and choose the key type as "JSON".
- A JSON file containing your credentials will be generated and automatically downloaded to your computer.

Grant access to the Google Sheets file to the created user.

Save file credentials.json (e.g., in directory config/) and write in .env
GOOGLE_CREDENTIAL_FILE=config\credentials.json

Also you can view next video:
https://www.youtube.com/watch?v=y-sIJ30Z5CU

## Settings in gImport.php 
Setting up import reconciliation with Google Sheets in mySQL.
It must be described in the config/gImport.php file.
Let's consider an example. (you can see this example in config/gImport_example.php)

- file_id - file name. It is part of url, e.g. for:
			https://docs.google.com/spreadsheets/d/1UGWw4ZI58vEnuovHgqGfTvQ_PGQ0sF6z-t4ghZkVZ4c/edit#gid=1814184294
			file id: 1UGWw4ZI58vEnuovHgqGfTvQ_PGQ0sF6z-t4ghZkVZ4c
- sheet - sheet name (tab in file) 
- columns - where the data comes from and where it goes. For example, data from the "company" column will be written into two tables at once, and "country" - into one.
- column_types - if necessary, you can specify the column type in mySQL. Here, the columns from which data is read from number will be of integer type. But here you need to know for sure that there are only numbers in this column.
```
    'imp_table' => [
        "file_id" => "1UGWw4ZI58vEnuovHgqGfTvQ_PGQ0sF6z-t4ghZkVZ4c",
        "sheet" => "sheet_2",
        "columns" => [
            "name" => ["main_table.name"],
            "user_email" => ["main_table.user_email"],
            "country" => ["main_table.country"],
            "job_title" => ["main_table.job_title"],
            "number" => ["main_table.number"],
            "company" => ["main_table.company", "tab2.company_name"],
            "street" => ["main_table.street", "tab2.company_street"],
            "city" => ["main_table.city", "tab2.company_street"],
            "date" => ["main_table.date"],
        ],
        "column_types" => [
            "number" => "integer"
        ],
    ],
```
## Create/update table structure
```
php artisan app:synch-structure
```
Creating or updating the structure of tables and columns that are described in gImport.
If it becomes necessary to change the structure, You will need to modify the file and execute the command.

## Synchronize data
```
php artisan app:synch-data {pageUrl=all} {rows=all}
```
It is for transferring data from Google Sheets in accordance with gImport.
- pageUrl - link on Google Sheets page or all, default: all (it is mean all files are listed in gImport)
- rows - range of rows when pageUrl is not all, default: all.

You can specify next parameters for rows range:
- all - for import all rows in specified document
- 10-128 - for range of rows
- 10- - for rows beginning from row 10 and to the end of document
- 56 - for single row

## Add/update column
```
php artisan app:synch-column {pageUrl} {column}
```
Updating or adding a single column.
Before that, You need to add the corresponding column in gImport.php and run php artisan app:synch-structure to update the structure of mySQL tables.


## Faker
For testing, you can fill your Google Sheets file with fake data. To do this, it is necessary to describe the name of the columns and therefore their values in a separate file.
You can see an example of such a file in app\Fakers\FakerExample.php. The class must be an implementation of the Google Sheets interface.
Required number of rows - set in method needRowsAmount().

```
php artisan app:run-faker {pageUrl} {fakerClass}
```
- pageUrl - 
page with a table to fill in, e.g. https://docs.google.com/spreadsheets/d/1UGWw4ZI58vEnuovHgqGfTvQ_PGQ0sF6z-t4ghZkVZ4c/edit#gid=1814184294
- fakerClass - class name with faker, e.g. FakerExample.
