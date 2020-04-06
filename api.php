<?php
require_once 'autoloader.php';
include('setting.php');
require 'vendor/autoload.php';
require_once 'vendor/autoloader.php'; // Replace with your path to guzzle autoload file

use EOV\ApiClient;
use EOV\Purl;
use EOV\Exception\RequestFailureException;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

$file_mimes = array('application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

set_include_path(get_include_path() . PATH_SEPARATOR . './phpseclib0.3.0');
include("Net/SFTP.php");

$sftp = new Net_SFTP("sftp.rds.co.id");

if (!$sftp->login("zurich-eov", "Zur1ch_rD$2020"))
{
    die("Cannot connect");
}

$path = "DATA-FEED/";

$filename = "Datafeed-Export-".date('Ymd').".csv";
$filepath = $path.$filename;
$local_file = "Files/";
$local_path = $local_file.$filename;
if (!$sftp->get($filepath,$local_path))
{
    die("Error downloading file ".$filepath);
}
$csvFile = fopen($local_path, 'r');
fgetcsv($csvFile);
while (($line = fgetcsv($csvFile, 100000, ";")) !== FALSE) {
		$CLIENT_TYPE = $line[17];
		$CYCLE_DATE = $line[0];
		$I_DATE = $line[2];
		$POLICY_HOLDER_NAME = $line[3];
		$POLICY_HOLDER_DATE_OF_BIRTH = $line[5];
		$LIFE_ASSURED = $line[8];
		$POLICY_HOLDER_DATE_OF_BIRTH_LIFE_ASSURED = $line[6];
		$POLICY_NUMBER = $line[7];
		$CURRENCY_1 = $line[9];
		$SUM_ASSURED = $line[10];
		$CURRENCY_2 = $line[9];
		$PREMIUM_AMOUNT = $line[11];
		$PAYMENT_FREQUENCY = $line[12];
		$CODE_PAYMENT_METHOD = $line[13];
		$AGENT_NAME = $line[14];
        $POLICY_HOLDER_PHONE_NUMBER = $line[15];
        $EMAIL_POLICY_HOLDER_NAME = $line[16];
        $COMPONENT_DESCRIPTION =$line[4];
        $STATUS_FLAG='CREATED';

        $sqlInsert = "INSERT INTO tb_data_zurcih (
        CLIENT_TYPE,
        POLICY_HOLDER_NAME,
        LIFE_ASSURED,
        POLICY_HOLDER_DATE_OF_BIRTH,
        POLICY_HOLDER_DATE_OF_BIRTH_LIFE_ASSURED,
        POLICY_NUMBER,
        CURRENCY_1,
        SUM_ASSURED,
        CURRENCY_2,
        PREMIUM_AMOUNT,
        PAYMENT_FREQUENCY,
        CODE_PAYMENT_METHOD,
        AGENT_NAME,
        POLICY_HOLDER_PHONE_NUMBER,
        EMAIL_POLICY_HOLDER_NAME,
        COMPONENT_DESCRIPTION,
        ISSUED_DATE,
        CYCLE_DATE,
        STATUS_FLAG)VALUES(
        '".$CLIENT_TYPE."',
        '".$POLICY_HOLDER_NAME."',
        '".$LIFE_ASSURED."',
        '".$POLICY_HOLDER_DATE_OF_BIRTH."',
        '".$POLICY_HOLDER_DATE_OF_BIRTH_LIFE_ASSURED."',
        '".$POLICY_NUMBER."',
        '".$CURRENCY_1."',
        '".$SUM_ASSURED."',
        '".$CURRENCY_2."',
        '".$PREMIUM_AMOUNT."',
        '".$PAYMENT_FREQUENCY."',
        '".$CODE_PAYMENT_METHOD."',
        '".$AGENT_NAME."',
        '".$POLICY_HOLDER_PHONE_NUMBER."',
        '".$EMAIL_POLICY_HOLDER_NAME."',
        '".$COMPONENT_DESCRIPTION."',
        '".$I_DATE."',
        '".$CYCLE_DATE."',
        '".$STATUS_FLAG."')";
        $result = mysqli_query($koneksi,$sqlInsert);
        if (! empty($result)) {
        	$type = "success";
        	$message = "CSV Data Imported into the Database";
        } else {
                $type = "error";
                $message = "Problem in Importing CSV Data";
            }
}

//parsing-data
		$table = 'tb_data_zurcih';
        $sql = "DELETE FROM $table where COMPONENT_DESCRIPTION != 'Zurich Proteksi 8'";
        $check = $pdo->prepare($sql);
        $check->execute();
        $sql = "SELECT  *  FROM $table  where STATUS_FLAG='CREATED'";
        $check = $pdo->prepare($sql);
        $check->execute();
        $result=$check->fetchALL();
        $number_of_rows= count($result);
        $mx_add = 20;
        $countupdated=0;
        if($number_of_rows>0){
            foreach($result as $data){  
                if($table=="tb_data_zurcih"){
                    $sql = "UPDATE $table SET
                        SUM_ASSURED= ?,
                        PREMIUM_AMOUNT= ?,
                        CURRENCY_1 = ?,
                        CURRENCY_2 = ?,
                        POLICY_HOLDER_NAME_ROW_2 = ?,
                        LIFE_ASSURED_ROW_2= ?,
                        CODE_FREQUENCY= ?,
                        PAYMENT_METHOD= ?,
                        CODE_COMPONENT_DESCRIPTION= ?,
                        PARSED_AT= ?,
                        STATUS_FLAG='PARSED' WHERE POLICY_NUMBER=?";
                    $stmt= $pdo->prepare($sql);
                    $stmt->execute([
                        convertNominal($data['SUM_ASSURED']),
                        convertNominal($data['PREMIUM_AMOUNT']),
                        converrtCurr($data['CURRENCY_1']),
                        converrtCurr($data['CURRENCY_2']),
                        trim(parsing($data['POLICY_HOLDER_NAME'],$mx_add,2)),
                        trim(parsing($data['LIFE_ASSURED'],$mx_add,2)),
                        convertfreq($data['PAYMENT_FREQUENCY']),
                        convertmetode($data['CODE_PAYMENT_METHOD']),
                        convertcode($data['COMPONENT_DESCRIPTION']),
                        date('Y-m-d H:i:s'),
                        $data['POLICY_NUMBER']]);
                    $countupdated++;
                }
            } 
        }

        $table  = 'tb_data_zurcih';
            $sql    = "SELECT * FROM $table where STATUS_FLAG='PARSED'";
            $query  = $pdo->prepare($sql);
            $query->execute();
            $result = $query->fetchAll();
            $countupdated=0;
            $link_zurich="https://preprod.rtcvid.net/new_zurichpro8/?uid=";
            if(count($result)>0){
                foreach($result as $val){
                    $status = false;
                    $data = array(
                            'CLIENT_TYPE' => $val['CLIENT_TYPE'],
                            'POLICY_HOLDER_NAME' => $val['POLICY_HOLDER_NAME'],
                            'POLICY_HOLDER_NAME_ROW_2' => $val['POLICY_HOLDER_NAME_ROW_2'],
                            'LIFE_ASSURED'=> $val['LIFE_ASSURED'],
                            'LIFE_ASSURED_ROW_2' => $val['LIFE_ASSURED_ROW_2'],
                            'POLICY_HOLDER_DATE_OF_BIRTH' => $val['POLICY_HOLDER_DATE_OF_BIRTH'],
                            'POLICY_HOLDER_DATE_OF_BIRTH_LIFE_ASSURED' => $val['POLICY_HOLDER_DATE_OF_BIRTH_LIFE_ASSURED'],
                            'POLICY_NUMBER' => $val['POLICY_NUMBER'],
                            'CURRENCY_1'=> $val['CURRENCY_1'],
                            'SUM_ASSURED' => $val['SUM_ASSURED'],
                            'CURRENCY_2' => $val['CURRENCY_2'],
                            'PREMIUM_AMOUNT' => $val['PREMIUM_AMOUNT'],
                            'CODE_FREQUENCY' => $val['CODE_FREQUENCY'],
                            'PAYMENT_FREQUENCY' => $val['PAYMENT_FREQUENCY'],
                            'CODE_PAYMENT_METHOD' => $val['CODE_PAYMENT_METHOD'],
                            'PAYMENT_METHOD' => $val['PAYMENT_METHOD'],
                            'AGENT_NAME' =>  $val['AGENT_NAME'],
                            'POLICY_HOLDER_PHONE_NUMBER' => $val['POLICY_HOLDER_PHONE_NUMBER'],
                            'EMAIL_POLICY_HOLDER_NAME' => $val['EMAIL_POLICY_HOLDER_NAME'],
                            'CODE_COMPONENT_DESCRIPTION' => $val['CODE_COMPONENT_DESCRIPTION'],
                            'COMPONENT_DESCRIPTION' => $val['COMPONENT_DESCRIPTION'],
                            'SUGGESTION' => '',
                            'VALIDATE' => '',
                            'CONFRIM_BUKU_POLIS' => '',
                            'CONFRIM_DATA_POLIS' => '',
                            'SURVEY' => '',
                            'LOOPING' => ''
                            );
                    // var_dump($data);
                    $username = 'apizurich';
                    $password = 'Zur|ch@34L!2'; 
                    $api_client = ApiClient::factory('https://preprod.rtcvid.net/', 'new_zurichpro8', $username,$password);
                    $purl = new Purl($api_client);
                    $puid = $purl->create($data);
                    $countupdated++;
                    $id=$val['ID'];
                    $linked=$link_zurich."".$puid;
                    $stmt = $pdo->prepare("UPDATE $table 
                            SET STATUS_FLAG = 'CONVERTED', LANDING_PAGE=:link,UID = :uid,GENERATED_AT=:tgl
                            WHERE ID = :id");
                    $stmt->execute(array(
                        'link'  => $linked,
                        'uid'   => $puid,
                        'id'    => $val['ID'],
                        'tgl'   => date('Y-m-d H:i:s'),
                     ));

                }
            }

    $spreadsheet = new Spreadsheet();
    $Excel_writer = new Csv($spreadsheet);

    $spreadsheet->setActiveSheetIndex(0);
    $activeSheet = $spreadsheet->getActiveSheet();

    $activeSheet->setCellValue('A1', 'UID');
    $activeSheet->setCellValue('B1', 'CLIENT_TYPE');
    $activeSheet->setCellValue('C1', 'POLICY_HOLDER_NAME');
    $activeSheet->setCellValue('D1', 'POLICY_HOLDER_NAME_ROW_2');
    $activeSheet->setCellValue('E1', 'LIFE_ASSURED');
    $activeSheet->setCellValue('F1', 'LIFE_ASSURED_ROW_2');
    $activeSheet->setCellValue('G1', 'POLICY_HOLDER_DATE_OF_BIRTH');
    $activeSheet->setCellValue('H1', 'POLICY_HOLDER_DATE_OF_BIRTH_LIFE_ASSURED');
    $activeSheet->setCellValue('I1', 'POLICY_NUMBER');
    $activeSheet->setCellValue('J1', 'CODE_FREQUENCY');
    $activeSheet->setCellValue('K1', 'PAYMENT_FREQUENCY');
    $activeSheet->setCellValue('L1', 'CODE_PAYMENT_METHOD');
    $activeSheet->setCellValue('M1', 'PAYMENT_METHOD');
    $activeSheet->setCellValue('N1', 'AGENT_NAME');
    $activeSheet->setCellValue('O1', 'POLICY_HOLDER_PHONE_NUMBER');
    $activeSheet->setCellValue('P1', 'EMAIL_POLICY_HOLDER_NAME');
    $activeSheet->setCellValue('Q1', 'CODE_COMPONENT_DESCRIPTION');
    $activeSheet->setCellValue('R1', 'COMPONENT_DESCRIPTION');
    $activeSheet->setCellValue('S1', 'LANDING_PAGE');


        $table = 'tb_data_zurcih';
        $query = $koneksi->query("SELECT  *  FROM $table ORDER BY ID DESC");

    if ($query->num_rows > 0) {
        $i = 2;
            while($row = $query->fetch_assoc()) {
            $activeSheet->setCellValue('A'.$i , $row['UID']);
            $activeSheet->setCellValue('B'.$i , $row['CLIENT_TYPE']);
            $activeSheet->setCellValue('C'.$i , $row['POLICY_HOLDER_NAME']);
            $activeSheet->setCellValue('D'.$i , $row['POLICY_HOLDER_NAME_ROW_2']);
            $activeSheet->setCellValue('E'.$i , $row['LIFE_ASSURED']);
            $activeSheet->setCellValue('F'.$i , $row['LIFE_ASSURED_ROW_2']);
            $activeSheet->setCellValue('G'.$i , $row['POLICY_HOLDER_DATE_OF_BIRTH']);
            $activeSheet->setCellValue('H'.$i , $row['POLICY_HOLDER_DATE_OF_BIRTH_LIFE_ASSURED']);
            $activeSheet->setCellValue('I'.$i , $row['POLICY_NUMBER']);
            $activeSheet->setCellValue('J'.$i , $row['CODE_FREQUENCY']);
            $activeSheet->setCellValue('K'.$i , $row['PAYMENT_FREQUENCY']);
            $activeSheet->setCellValue('L'.$i , $row['CODE_PAYMENT_METHOD']);
            $activeSheet->setCellValue('M'.$i , $row['PAYMENT_METHOD']);
            $activeSheet->setCellValue('N'.$i , $row['AGENT_NAME']);
            $activeSheet->setCellValue('O'.$i , "'".$row['POLICY_HOLDER_PHONE_NUMBER']);
            $activeSheet->setCellValue('P'.$i , $row['EMAIL_POLICY_HOLDER_NAME']);
            $activeSheet->setCellValue('Q'.$i , $row['CODE_COMPONENT_DESCRIPTION']);
            $activeSheet->setCellValue('R'.$i , $row['COMPONENT_DESCRIPTION']);
            $activeSheet->setCellValue('S'.$i , $row['LANDING_PAGE']);
            $i++;
        }
    }
    $filename_out = 'Datafeed-export-eov'.date('Y-m-d H.i.s').'.csv';  
    header('Content-Type: application/text-csv');
    header('Content-Disposition: attachment;filename="'. $filename_out);
    header('Cache-Control: max-age=0');
    $Excel_writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Csv");
    $Excel_writer->save('Files/'.$filename_out);

    $local_directory = 'Files/';
    $remote_directory = 'EOV-ZURICH/';

    /* Add the correct FTP credentials below */
    $sftp = new Net_SFTP('sftp.rds.co.id');
    if (!$sftp->login('zurich-eov', 'Zur1ch_rD$2020')) 
    {
        exit('Login Failed');
    } 

    /* We save all the filenames in the following array */
    // $files_to_upload = array();

    /* Open the local directory form where you want to upload the files */
    if ($handle = opendir($local_directory)) 
    {
        /* This is the correct way to loop over the directory. */
        while (false !== ($file = readdir($handle))) 
        {
            if ($file != "." && $file != "..") 
            {
                $files_to_upload[] = $file;
            }
        }

        closedir($handle);
    }

    if(!empty($files_to_upload))
    {
        /* Now upload all the files to the remote server */
        foreach($files_to_upload as $file)
        {
              /* Upload the local file to the remote server 
                 put('remote file', 'local file');
               */
              $success = $sftp->put($remote_directory . $file, 
                                    $local_directory . $file, 
                                     NET_SFTP_LOCAL_FILE);
        }
    }

    array_map("unlink", glob($local_path));
?>
