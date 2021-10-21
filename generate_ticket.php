<?php
declare (strict_types = 1);

require_once __DIR__ . '/src/TextToImage.php';

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$inputFileName = getConfig("data_file_name"); //name of file that store data

$base_url = getConfig("base_url"); // url of site

$error_bag = []; // to store error
$res["type"] = "error";

if (stripos($_SERVER["HTTP_USER_AGENT"], "wechat") || stripos($_SERVER["HTTP_USER_AGENT"], "alipay")) {
    $error_bag[] = "open link in browser to get ticket";

    $res["value"] = $error_bag;
    echo json_encode($res);
    return false;
}

if (isset($_POST) and !empty($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case "generate": // generate ticket

            // Read excel file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            // get active worksheet
            $workSheet = $spreadsheet->getActiveSheet();

            $studnum = empty($_POST["studnum"]) ? "" : trim("" . $_POST["studnum"]);

            if ($student = verify_studentnum($studnum, $workSheet)) {
                $res["type"] = "success";
                $res["value"] = generate_ticket($student, $spreadsheet, $workSheet);
                echo json_encode($res);
                return false;
            } else {

                $res["value"] = $error_bag;
                echo json_encode($res);
                return false;
            }

            break;
        case "download": // download ticket
            $ticketname = empty($_POST["ticketname"]) ? "" : $_POST["ticketname"];

            if (verify_ticketname($ticketname)) {
                download_ticket($ticketname);
                return false;
            } else {

                $res["value"] = $error_bag;
                echo json_encode($res);
                return false;
            }
            break;
        case "draw": // randomly get lucky draw winner 
            $pass =  trim($_POST["pass"]);
            if($pass != getConfig("spin_pass"))
            {
                $error_bag[] = "Invalid spin password";
                $res["value"] = $error_bag;
                echo json_encode($res);
                return false;
            }
            // Read excel file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            // get active worksheet
            $workSheet = $spreadsheet->getActiveSheet();

            $workSheet_array = $workSheet->toArray();


            $issued_array = array_filter($workSheet_array, "issued");
            $array_keys = array_keys($issued_array);



            $lucky_num = rand(00, count($issued_array) - 1); // get luck winner
            $lucky_key = $array_keys[$lucky_num];

            $lucky_winner  = strval($issued_array[$lucky_key][4]);
         
            $lucky_winner = str_replace("#","",$lucky_winner);
            $lucky_winner = str_replace("-","",$lucky_winner);

            // set lucky winner
            $workSheet->getCell("F" . ($lucky_key + 1))->setValue('winner');

             //writing changes directly using loaded spreadsheet data
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($inputFileName);

            $res["type"] = "success";
            $res["value"] = str_split($lucky_winner);
            foreach($res["value"] as $key=>$value) // find and replace zero with 10 in the array, needed by slotmachine.js
            {
                if($value == "0")
                {
                    $res["value"][$key] = "10";
                }
            }

            echo json_encode($res); // return luck winner
            return false;

            break;
        default:
            $error_bag[] = "unknown request";
            $res["value"] = $error_bag;
            echo json_encode($res);
            break;
    }
} else {

}

//issued tickets, and hasn't won
function issued($var)
{
    return $var[3] == "issued" and $var[5] !="winner";
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

// verify student number, checks if the student number exists in the spreadsheet
function verify_studentnum($studnum, $workSheet)
{
    global $error_bag;

    $studnum_len = strlen($studnum);
    if ($studnum_len == 0) {
        $error_bag[] = "student no. can't be empty";

        return false;
    }

    if ($studnum_len < 5 or !ctype_digit($studnum)) {
        $error_bag[] = "student no. invalid. contact organizer.";

        return false;
    }

    $workSheet_array = $workSheet->toArray();
    $key = array_search($studnum, array_column($workSheet_array, 1)); // search for matching student number

    $student["key"] = $key;

    if ($key) {
        $student = $workSheet_array[$key];
        array_push($student, $key);

        if ($student[3] == "issued") //checking if ticket has already been issued
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

    $studnum = md5($student[1]);
    $filename = "/files/$studnum";

    $brand = '#';
    $cur_date = date("d") . date("H") . date("i") . date("s");
    $invoice = $brand . $cur_date;
    $customer_id = rand(00, 99);
    $uRefNo = $invoice . '-' . $customer_id;

    $text1 = function (TextToImage $handler) use ($uRefNo) { //ticket number

        $handler->add($uRefNo)
            ->position(610, 1005)
            ->font(80, __DIR__ . '/assets/fonts/sweet purple.otf')
            ->color(255, 255, 255)
            ->shadow(1, 2, [0, 0, 0]);
    };
    $text2 = function (TextToImage $handler) use ($student) { // student number

        $handler->add("" . $student[1])
            ->position(610, 1130)
            ->font(80, __DIR__ . '/assets/fonts/sweet purple.otf')
            ->color(255, 255, 255)
            ->shadow(1, 2, [0, 0, 0]);
    };
    $text3 = function (TextToImage $handler) use ($student) { // student name

        $studname = "" . $student[0];
        if (strlen($studname) > 25) {
            $studname = substr($studname, 0, 25) . "...";
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
    )->close(__DIR__ . $filename);

    # mark ticket issued
    $workSheet->getCell("D" . ($student[5] + 1))->setValue('issued');
    $workSheet->getCell("E" . ($student[5] + 1))->setValue($uRefNo);

    //writing changes directly using loaded spreadsheet data
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save($inputFileName);

    return "$base_url$filename.png";
}

// returns configuration value
function getConfig($key)
{
    $config = include __DIR__ . "/config.php";
    return $config[$key];
}

// debugging purposes
function dump_to_file($data)
{
    file_put_contents("./debug.txt", print_r($data, true)."\n", FILE_APPEND);
}
