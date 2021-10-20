<?php
declare(strict_types=1);

require_once __DIR__ . '/src/TextToImage.php';

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$inputFileName = __DIR__ . '/data/aeb3bcb1b6148eb24c6917029b604291.xlsx';

$base_url = "http://localhost/simple-event-ticketing";
$error_bag = []; // to store error
$res["type"] = "error";


dump_to_file($_POST);

if(isset($_POST) and !empty($_POST['action']))
{
    $action = $_POST['action'];

    switch($action)
    {
        case "generate": // generate ticket

            // Read excel file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            // get active worksheet
            $workSheet = $spreadsheet->getActiveSheet();

            $studnum = empty($_POST["studnum"])?"":trim("".$_POST["studnum"]);

            if($student = verify_studentnum($studnum, $workSheet))
            {
                $res["type"] = "success";
                $res["value"] = generate_ticket($student, $spreadsheet, $workSheet);
                echo json_encode($res);
                return false;
            }else{
                
                $res["value"] = $error_bag;
                echo json_encode($res);
                return false;
            }

            break;
        case "download": // download ticket
            $ticketname = empty($_POST["ticketname"])?"":$_POST["ticketname"];
            
            if(verify_ticketname($ticketname))
            {
                download_ticket($ticketname);
                return false;
            }else{
                
                $res["value"] = $error_bag;
                echo json_encode($res);
                return false;
            }
            break;
        default:
            $error_bag[] ="unknown request";
            $res["value"] = $error_bag;
            echo json_encode($res);
            break;
    }
}else{

}


//verify ticket name
function verify_ticketname()
{
    
    global $error_bag;
}

// download ticket
function download_ticket($ticketname)
{
    header("Content-Type: image/png");
}


// verify student number, checks if the student number exists in the excell sheet
function verify_studentnum($studnum, $workSheet)
{
    global $error_bag;

    $studnum_len = strlen($studnum);
    if($studnum_len == 0)
    {
        $error_bag[] = "student no. can't be empty";

        return false;
    }

    if($studnum_len < 5 or !ctype_digit($studnum))
    {
        $error_bag[] = "student no. invalid. contact organizer.";

        return false;
    }

    $workSheet_array = $workSheet->toArray();
    $key = array_search($studnum, array_column($workSheet_array, 1)); // search for matching student number

    $student["key"] = $key;

    if($key)
    {
        $student = $workSheet_array[$key];
        array_push($student, $key);
        dump_to_file($student);
        if($student[3] == "issued") //checking if ticket has already been issued
        {
            $error_bag[] = "student no. $studnum has already retrieved a ticket. contact organizer.";

            return false;
        }

        return $student;
    }

    $error_bag[] = "student no. not found, contact organizer";

    return false;
}


// generate ticket
function generate_ticket($student, $spreadsheet, $workSheet)
{
    global $inputFileName;
    global $base_url;
    
    $studnum = md5($student[1].time());
    $filename = "/files/$studnum";

    $brand = '#';
    $cur_date = date("d").date("H").date("i").date("s");
    $invoice = $brand.$cur_date;
    $customer_id = rand(00 , 99);
    $uRefNo = $invoice.'-'.$customer_id;
    
    $text1 = function (TextToImage $handler) use ($uRefNo) { //ticket number

        $handler->add($uRefNo)
                ->position(610, 1005)
                ->font(80, __DIR__ . '/assets/fonts/sweet purple.otf')
                ->color(255, 255, 255)
                ->shadow(1, 2, [0, 0, 0]);
    };
    $text2 = function (TextToImage $handler)  use ($student) { // student number

        $handler->add("".$student[1])
                ->position(610, 1130)
                ->font(80, __DIR__ . '/assets/fonts/sweet purple.otf')
                ->color(255, 255, 255)
                ->shadow(1, 2, [0, 0, 0]);
    };
    $text3 = function (TextToImage $handler) use ($student) { // student name

        $studname = "".$student[0];
        if(strlen($studname)>25)
        {
            $studname = substr($studname, 0,25)."...";
        }

        $handler->add($studname)
                ->position(610, 1255)
                ->font(80, __DIR__ . '/assets/fonts/sweet purple.otf')
                ->color(255, 255, 255)
                ->shadow(1, 2, [0, 0, 0]);
    };

    # Write new image
    TextToImage::setImage(__DIR__ . '/data/ticket.png')->open(
        $text1,
        $text2,
        $text3
    )->close(__DIR__ .$filename);

    # mark ticket issued
    $workSheet->getCell("D".($student[5]+1))->setValue('issued');
    $workSheet->getCell("E".($student[5]+1))->setValue($uRefNo);

    //writing changes directly using loaded spreadsheet data
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save( $inputFileName );

    return "$base_url$filename.png";
}

// debugging purposes
function dump_to_file($data){ 
    file_put_contents("./debug.txt",print_r($data,true),FILE_APPEND);
}