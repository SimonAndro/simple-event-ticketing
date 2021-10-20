<?php

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$inputFileName = __DIR__ . '/data/aeb3bcb1b6148eb24c6917029b604291.xlsx';

// Read excel file
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
// get active worksheet
$workSheet = $spreadsheet->getActiveSheet();

$workSheet_array = $workSheet->toArray();


# mark ticket issued
$workSheet->getCell("D5")->setValue('issued');
$workSheet->getCell("E5")->setValue("12345512");

# mark ticket issued
$workSheet->getCell("D6")->setValue('issued');
$workSheet->getCell("E6")->setValue("48552138");

# mark ticket issued
$workSheet->getCell("D7")->setValue('issued');
$workSheet->getCell("E7")->setValue("12810002");

//writing changes directly using loaded spreadsheet data
$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save( $inputFileName );