<?php
declare(strict_types=1);

require_once __DIR__ . '/src/TextToImage.php';


$student = array(
 "PAUL ERNEST KACHULE ABCDE...",
 "2017020501001",
 "MALAWI",
 "issued",
 "2",
);

generate_ticket($student);


// generate ticket
function generate_ticket($student, $spreadsheet=0, $workSheet=0)
{

    $studnum = $student[1];
    $filename = "/files/$studnum";

    $brand = '#';
    $cur_date = substr("".time(),5);
    $invoice = $brand.$cur_date;
    $customer_id = rand(00000 , 99999);
    $uRefNo = $invoice.'-'.$customer_id;
    
    $text1 = function (TextToImage $handler) use ($uRefNo) {

        $handler->add($uRefNo)
                ->position(610, 1005)
                ->font(80, __DIR__ . '/data/sweet purple.otf')
                ->color(255, 255, 255)
                ->shadow(1, 2, [0, 0, 0]);
    };
    $text2 = function (TextToImage $handler)  use ($student) {

        $handler->add("".$student[1])
                ->position(610, 1130)
                ->font(80, __DIR__ . '/data/sweet purple.otf')
                ->color(255, 255, 255)
                ->shadow(1, 2, [0, 0, 0]);
    };
    $text3 = function (TextToImage $handler) use ($student) {

        $handler->add("".$student[0])
                ->position(610, 1255)
                ->font(80, __DIR__ . '/data/sweet purple.otf')
                ->color(255, 255, 255)
                ->shadow(1, 2, [0, 0, 0]);
    };

    # Write new image
    TextToImage::setImage(__DIR__ . '/data/ticket.png')->open(
        $text1,
        $text2,
        $text3
    )->close(__DIR__ .$filename);

    return $filename;
}