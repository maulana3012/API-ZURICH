<?php
require_once "vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

if(isset($_POST["create-uid"])) {

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
	$activeSheet->setCellValue('J1', 'INSURED_NAME');
	$activeSheet->setCellValue('K1', 'CODE_FREQUENCY');
	$activeSheet->setCellValue('L1', 'PAYMENT_FREQUENCY');
	$activeSheet->setCellValue('M1', 'AGENT_NAME');
	$activeSheet->setCellValue('N1', 'POLICY_HOLDER_PHONE_NUMBER');
	$activeSheet->setCellValue('O1', 'EMAIL_POLICY_HOLDER_NAME');
	$activeSheet->setCellValue('P1', 'CODE_COMPONENT_DESCRIPTION');
	$activeSheet->setCellValue('Q1', 'COMPONENT_DESCRIPTION');
	$activeSheet->setCellValue('R1', 'LANDING_PAGE');


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
	        $activeSheet->setCellValue('J'.$i , $row['INSURED_NAME']);
	        $activeSheet->setCellValue('K'.$i , $row['CODE_FREQUENCY']);
	        $activeSheet->setCellValue('L'.$i , $row['PAYMENT_FREQUENCY']);
	        $activeSheet->setCellValue('M'.$i , $row['AGENT_NAME']);
	        $activeSheet->setCellValue('N'.$i , $row['POLICY_HOLDER_PHONE_NUMBER']);
	        $activeSheet->setCellValue('O'.$i , $row['EMAIL_POLICY_HOLDER_NAME']);
	        $activeSheet->setCellValue('P'.$i , $row['CODE_COMPONENT_DESCRIPTION']);
	        $activeSheet->setCellValue('Q'.$i , $row['COMPONENT_DESCRIPTION']);
	        $activeSheet->setCellValue('R'.$i , $row['LANDING_PAGE']);
	        $i++;
	    }
	}

	$filename = 'Datafeed-export-'.date('Y-m-d H.i.s').'.csv';	
	header('Content-Type: application/text-csv');
	header('Content-Disposition: attachment;filename="'. $filename);
	header('Cache-Control: max-age=0');
	$Excel_writer->save('files/'.$filename);
	header('Location: index.php');

}
?>