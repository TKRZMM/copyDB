<?php
/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 21.03.2016
 * Time: 08:54
 */



$excel = 'hallo markus';
writeFile('FTTC', $excel);


function writeFile($type, $content, $filename = false)
{

	if (!$filename)
		$filename = 'test_' . $type . '_' . 'V001_' . date('Ymd');

	// '/var/www/html/www/uploads/';
	$fullFilePathAndName = 'uploads/' . $filename . '.csv';


	// Existiert Datei schon? ... wenn ja, Version erhöhen
	if (file_exists($fullFilePathAndName)) {

		// Versionsnummer ermitteln
		preg_match('/(_V(\d+))/', $filename, $matches);
		$fileVersion = $matches[2];

		$nextVersion = $fileVersion + 1;
		$nextVersion = sprintf("%'.03d", $nextVersion);

		$filename = 'test_' . $type . '_V' . $nextVersion . '_' . date('Ymd');

		// Selbstaufruf ... endet wenn freie Versionsnummer gefunden wurde
		writeFile($type, $content, $filename);
	} else {
		$fp = fopen($fullFilePathAndName, 'w');
		fwrite($fp, $content);
		fclose($fp);
	}

}






