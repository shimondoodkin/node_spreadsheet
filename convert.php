<?php

/*
$json =file_get_contents('php://stdin');//rawurldecode($_SERVER['argv'][1]);
$obj=json_decode($json,true);
//echo '\r\ndebug1:\r\n';
//echo "\r\n|".$json."|\r\n";
//var_dump($obj);
if(!$obj)
{
$obj=json_decode(stripslashes($json));
//echo '\r\ndebug2:\r\n';
//var_dump($obj);
}
*/

// php -f convert.php myfile.xlsx myfile.csv

if($_SERVER['argc'] < 3) {
	echo "usage: php -f convert.php input.xlsx output.js\r\n supported formats: .xlsx or .xls or .ods or .csv ";
	exit(2);
}
	error_reporting(E_ALL);

	date_default_timezone_set('Asia/Jerusalem'); // 
	
	ini_set("auto_detect_line_endings", true); // https://github.com/shimondoodkin/node_spreadsheet/issues/2
	
	/** PHPExcel_IOFactory */
	require_once 'phpexcel/Classes/PHPExcel/IOFactory.php';
	require_once 'phpexcel/Classes/PHPExcel/Writer/JSON.php';


	if (!file_exists($_SERVER['argv'][1])) {
		exit($_SERVER['argv'][1] . " not found.\n");
	}

//  echo date('H:i:s') . " Loading file\n";
	$objPHPExcel = PHPExcel_IOFactory::load($_SERVER['argv'][1]);
//  echo date('H:i:s') . " Done\n";

	//$objCSV = new PHPExcel_Writer_CSV($objPHPExcel);
	$objCSV = new PHPExcel_Writer_JSON($objPHPExcel);
	$objCSV->setUseBOM(false);
//	echo date('H:i:s') . " Saveing file\n";
	$objCSV->save($_SERVER['argv'][2]);
	//$objCSV->save('/tmp/json42');
//	echo date('H:i:s') . " Done\n";
// if input is csv then convert charset : iso-8859-8 -> utf-8
?>