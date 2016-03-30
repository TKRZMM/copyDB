<?php


/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 15.03.2016
 * Time: 08:33
 */
class Dimari
{

	// Initial Variable:

	// Globaler HAUPT - Handler für Datenverarbeitung
	public $globalData = array();

	// Datenbank Variable ... werden durch den Construktor gesetzt
	private $myHost;
	private $myUsername;
	private $myPassword;
	private $myMySQLHost;
	private $myMySQLUsername;
	private $myMySQLPassword;
	private $myMySQLDBName;

	// Datenbank Object
	private $dbF;
	private $mysqli;


	// Aktuelle Tabelle die erstellt oder gefüllt wird
	private $curTableName;
	private $curTableID;



	// Klassen - Konstruktor
	public function __construct($host, $username, $password, $myMySQLHost, $myMySQLUsername, $myMySQLPassword , $myMySQLDBName)
	{

		$this->myHost = $host;
		$this->myUsername = $username;
		$this->myPassword = $password;


		$this->myMySQLHost = $myMySQLHost;
		$this->myMySQLUsername = $myMySQLUsername;
		$this->myMySQLPassword = $myMySQLPassword;
		$this->myMySQLDBName = $myMySQLDBName;

	}   // END public function __construct(...)










	// Default Name - Methode
	public function myName($out = false)
	{

		if ($out)
			print (__CLASS__);

		return __CLASS__;
	}
	// END public function myName(...)






	public function initialGetDimariDBStructur()
	{

		// DB Verbindung zu Dimari herstellen
		echo "DB Verbindung zu Dimari erstellen!<br>";
		$this->createDimariDBConnection();
		echo "DB Verbindung zu Dimari ... DONE!<br>";


		echo "<br>";


		echo "DB Verbindung zu MySQL erstellen!<br>";
		$this->createMysqliConnect();
		echo "DB Verbindung zu MySQL ... DONE!<br>";


		echo "<br>";


		echo "Lese Dimari Tabellen-Namen ein.<br>";
		$sumTableNames = $this->getDBTableNamesFromDimariDB();
		echo " Dimari Tabellen-Namen einlesen (".$sumTableNames.")... DONE!<br>";


		echo "<br>";

		echo "Lese einen Tabellennamen aus MySQL der noch nicht importiert wurde.<br>";
		$curTableName = $this->getSingleTablenameFormMySQL();
		echo "Lese einen Tabellennamen aus MySQL der noch nicht importiert wurde. (".$curTableName.") ... DONE!<br>";


		$this->test();




		// IDEBUG pre - tag
//		echo "<pre><hr>";
//		print_r($this);
//		echo "<hr></pre><br>";


		return true;

	}










	private function test()
	{
		$query = 'SELECT *
					FROM rdb$relation_fields f
					JOIN rdb$relations r ON f.rdb$relation_name = r.rdb$relation_name
					 AND r.rdb$view_blr IS NULL
					 AND (r.rdb$system_flag IS NULL OR r.rdb$system_flag = 0)
					 WHERE f.rdb$relation_name = \''.$this->curTableName.'\'
				ORDER BY f.rdb$field_position';

		$result = ibase_query($this->dbF, $query);

		// Zähler für die erhaltenen Tabellennamen aus Dimari
		$cnt = 0;

		// Obj zu MySQL
		$mysqli = $this->mysqli;

//		while ($row = ibase_fetch_object($result)) {
		while ($row = ibase_fetch_assoc($result)) {
			$cnt++;

			// IDEBUG pre - tag
			echo "<pre><hr>";
			print_r($row);
			echo "<hr></pre><br>";
			// Erzeuge Tabelle und Felder
			$queryMySQL = "ALTER TABLE `".$this->curTableName."` ADD ".$row['RDB$FIELD_NAME']." VARCHAR(100)";

			echo "$queryMySQL<br>";

			// MySQL - Query für den Inser ausführen
			//$mysqli->query($queryMySQL);

		}

		// Dimari free result
		ibase_free_result($result);

		return $cnt;

	}


















	// Lese den nächsten zu importierenden Tabellennamen ein
	private function getSingleTablenameFormMySQL()
	{
		$mysqli = $this->mysqli;

		$queryMySQL = "SELECT * FROM mm_import_tables WHERE enum_import_done = 'no' ORDER BY mm_import_table_id LIMIT 1";

		$result = $mysqli->query($queryMySQL);

		$row = $result->fetch_object();
		$this->curTableName = $row->table_name;
		$this->curTableID = $row->mm_import_table_id;

		mysqli_free_result($result);

		return $row->table_name;

	}




















	// Lese die Tabellen-Namen aus Dimari und speichere sie in der MySQL - DB
	private function getDBTableNamesFromDimariDB()
	{

		// Query um aus Dimari die Tabellennamen zu lesen
		$query = 'SELECT rdb$relation_name as relation_name
					FROM rdb$relations
					WHERE rdb$view_blr IS NULL
					  AND (rdb$system_flag IS NULL OR rdb$system_flag = 0)
				 ORDER BY rdb$relation_name';

		$result = ibase_query($this->dbF, $query);

		// Zähler für die erhaltenen Tabellennamen aus Dimari
		$cnt = 0;

		// Obj zu MySQL
		$mysqli = $this->mysqli;

		while ($row = ibase_fetch_object($result)) {
			$cnt++;

			// Query um den Tabellennamen in MySQL zu speichern
			// table_name ist unique ... so das ich keine Daten überschreibe
			$queryMySQL = "INSERT INTO mm_import_tables SET table_name = '".$row->RELATION_NAME."'";

			// MySQL - Query für den Inser ausführen
			$mysqli->query($queryMySQL);
		}

		// Dimari free result
		ibase_free_result($result);

		return $cnt;

	}






















	////////////////////////// START DIVERSE BLOCK ///////////////////////////////////


	// Dimari Datenbankverbindung herstellen
	private function createDimariDBConnection()
	{

		$host = $this->myHost;
		$username = $this->myUsername;
		$password = $this->myPassword;

		if (!($dbF = ibase_pconnect($host, $username, $password, 'ISO8859_1', 0, 3)))
			die('Could not connect: ' . ibase_errmsg());

		$this->dbF = $dbF;

		// Status
		//$this->outNow('DB Verbindung!', 'OK', 'Info');

		return true;

	}   // END private function createDimariDBConnection()









	// Erzeugt MySQL permanente Verbindung
	private function createMysqliConnect()
	{

		$mysqli = new mysqli('p:'.$this->myMySQLHost, $this->myMySQLUsername, $this->myMySQLPassword, $this->myMySQLDBName);


		// DB Verbindung fehlgeschlagen?
		if ($mysqli->connect_errno) {

			print ("<pre>");
			$message = "FEHLER -KRITISCH FÜHRT ZU EXIT-<br>";
			$message .= "Versuch Aufbau Datenbankverbindung fehlgeschlagen!<br>";
			$message .= "MySQL-Fehlermeldung: <br>";
			$message .= "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			print($message);
			print ("</pre>");
			exit;
		}

		// Speichere Verbindungs-Objekt
		$this->mysqli = $mysqli;

		RETURN TRUE;

	}	// END private function pconnect()










	// Ibase num_rows
	public function ibase_num_rows($result)
	{

		$myResult = $result;

		$cnt = 0;

		while ($row = @ibase_fetch_row($myResult))
			$cnt++;

		return $cnt;

	}   // END private function ibase_num_rows(...)








}   // END class Dimari
