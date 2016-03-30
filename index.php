<?php
/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 09.03.2016
 * Time: 08:28
 */


include 'includes/head.php';

// Settings laden
include 'includes/config.inc.php';


// Klasse laden:
include 'includes/classLoader.php';


// KLassen - Objekt erzeugen
$hDimari = new Dimari($host, $username, $password, $myMySQLHost, $myMySQLUsername, $myMySQLPassword, $myMySQLDBName);


// Initial Methode aufrufen:
$hDimari->initialGetDimariDBStructur();



// Debug ausgeben:

//echo "<pre>";
//echo "<hr>globalTarget<br>";
//print_r($hDimari->globalTarget);
//echo "<hr>";
//print_r($hDimari->globalData);
//echo "</pre><br>";




include 'includes/footer.php';

