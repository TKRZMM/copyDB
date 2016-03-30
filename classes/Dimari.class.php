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

	// Aktuelle Feldnamen
	private $curFieldnames = array();

	// Erster Feldname für ORDER BY
	private $curFieldnameFirstOrder;










	// Klassen - Konstruktor
	public function __construct($host, $username, $password, $myMySQLHost, $myMySQLUsername, $myMySQLPassword, $myMySQLDBName)
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

		echo "<br>Tabellen und Felder anlegen!<br><br>Zurück zur Index: <a href=\"index.php\">HIER</a><br><br><hr>";


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
		echo "Dimari Tabellen-Namen einlesen (" . $sumTableNames . ")... DONE!<br>";

		echo "<br>";

		// Erstelle Tabellen und Felder...
		$this->createNextMySQLTableLoop();

		return true;

	}










	public function initialGetDimariDBValues()
	{

		flush();
		ob_flush();
		echo "<br>Eine Tabelle importieren!<br><br>Zurück zur Index: <a href=\"index.php\">HIER</a><br><br><hr>";

		echo "<br>Eine Tabelle importieren!<br><br>Nächster Imprt: <a href=\"importTables.php\">HIER</a><br><br><hr>";


		// DB Verbindung zu Dimari herstellen
		echo "DB Verbindung zu Dimari erstellen!<br>";
		$this->createDimariDBConnection();
		echo "DB Verbindung zu Dimari ... DONE!<br>";


		echo "<br>";


		flush();
		ob_flush();
		echo "DB Verbindung zu MySQL erstellen!<br>";
		$this->createMysqliConnect();
		echo "DB Verbindung zu MySQL ... DONE!<br>";


		echo "<br>";


		flush();
		ob_flush();
		// Importieren Inhalte
		$this->createNextMySQLImportLoop();

		echo "===> DONE! <===";

		return true;

	}










	// Selbstaufrufende Methode für das Importieren der Diamri Tabellen
	private function createNextMySQLImportLoop()
	{

		flush();
		ob_flush();
		// Info wie viel wurden schon importiert, wieviel nicht!
		$rest = $this->getImportInfo();
		echo "==> Verbleibend: $rest Tabellen! <==<br><br>";


		flush();
		ob_flush();
		echo "Lese einen Tabellennamen aus MySQL dessen Inhalt noch nicht importiert wurde.<br>";
		if (!$this->getSingleTablenameFormMySQL('enum_import_done')) {
			echo "Lese einen Tabellennamen aus MySQL dessen Inhalt noch nicht importiert wurde. KEINE WEITEREN TABELLEN!!! ... DONE!<br>";

			return true;
		}

		$curTableName = $this->curTableName;
		echo "Lese einen Tabellennamen aus MySQL dessen Inhalt noch nicht importiert wurde. (" . $curTableName . ") ... DONE!<br>";

		echo "<br>";

		flush();
		ob_flush();
		echo "Lese die Feldnamen von " . $curTableName . "<br>";
		$this->getFieldnamesByTableName($curTableName);
		echo "Lese die Feldnamen von " . $curTableName . " (" . count($this->curFieldnames) . " Feldnamen ermittelt)... DONE<br>";


		echo "<br>";


		flush();
		ob_flush();
		echo "Lese und schreibe Daten aus der Dimari: " . $curTableName . "<br>";
		$this->pullDimariDBValue();
		echo "Lese und schreibe Daten aus der Dimari: " . $curTableName . " ... DONE!<br>";

		echo "<br>";

		flush();
		ob_flush();

		return true;

	}










	// Ermittelt die noch zu importierenden Tabellen
	private function getImportInfo()
	{

		// MySQL Obj
		$mysqli = $this->mysqli;

		$queryMySQL = "SELECT COUNT(*) AS REST FROM aa_import_tables WHERE enum_import_done != 'yes'";

		$result = $mysqli->query($queryMySQL);

		$row = $result->fetch_object();

		$rest = $row->REST;

		return $rest;

	}










	// Lese Dimari DB Einträge und schreibe sie in die MySQL - DB
	private function pullDimariDBValue()
	{

		// Daten aus Dimari DB lesen
		$query = "SELECT * FROM " . $this->curTableName . " ORDER BY " . $this->curFieldnameFirstOrder . " ";

		$result = ibase_query($this->dbF, $query);

		$cnt = 0;
		while ($row = ibase_fetch_object($result)) {
			$cnt++;

			// Feldnamen und Value verbinden
			unset($dataSetArray);
			foreach($this->curFieldnames as $index => $curFieldname) {
				$dataSetArray[$curFieldname] = $row->$curFieldname;
			}


			// Einzelnen Datensatz jetzt in die MySQL DB schreiben
			$this->writeToMySQL($dataSetArray);

		}

		// Dimari free result
		ibase_free_result($result);

		// Import für Tabelle als done in dre MySQL DB setzen
		$this->setImportDoneByTablename();

		return true;

	}









	// Update den Import-Falg für die aktuelle Tabelle auf yes
	private function setImportDoneByTablename()
	{

		// MySQL Obj
		$mysqli = $this->mysqli;

		$queryMySQL = "UPDATE `aa_import_tables` SET `enum_import_done` = 'yes' WHERE `aa_import_table_id` = '".$this->curTableID."' LIMIT 1";

		$mysqli->query($queryMySQL);

		return true;

	}










	// Schreibe einzelenen Datensatz in die MySQL DB
	private function writeToMySQL($dataSetArray)
	{

		// MySQL Obj
		$mysqli = $this->mysqli;

		$preQuery = "INSERT INTO `" . $this->curTableName . "` SET ";

		$cnt = 0;
		$midQuery = '';
		foreach($dataSetArray as $fieldname => $value) {

			if ($cnt >= 1)
				$midQuery .= ", ";

			$midQuery .= "`" . $fieldname . "` = '" . $value . "'";

			$cnt++;
		}

		$queryMySQL = $preQuery . $midQuery;

		$mysqli->query($queryMySQL);

		return true;

	}










	// Lese Feldnamen der aktuellen Tabelle ein
	private function getFieldnamesByTableName($curTableName)
	{

		// MySQL Obj
		$mysqli = $this->mysqli;


		// Sicherheits Reset der Inhalte
		unset($this->curFieldnames);
		$this->curFieldnames = array();

		$queryMySQL = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . $this->myMySQLDBName . "' AND TABLE_NAME = '" . $curTableName . "'";

		$result = $mysqli->query($queryMySQL);

		$num_rows = $result->num_rows;

		if ($num_rows >= 1) {
			$cnt = 0;
			while ($row = $result->fetch_object()) {
				$this->curFieldnames[] = $row->COLUMN_NAME;

				// Ersten Feldnamen für order by speichern
				if ($cnt < 1)
					$this->curFieldnameFirstOrder = $row->COLUMN_NAME;

				$cnt++;
			}

			mysqli_free_result($result);

			return true;
		}
		else {

			mysqli_free_result($result);

			return false;
		}

	}










	// Selbstaufrufende Methode für das Erstellen der Diamri Tabellen
	private function createNextMySQLTableLoop()
	{

		flush();
		ob_flush();

		echo "Lese einen Tabellennamen aus MySQL der noch nicht importiert wurde.<br>";
		if (!$this->getSingleTablenameFormMySQL('enum_creation_done')) {
			echo "Lese einen Tabellennamen aus MySQL der noch nicht importiert wurde. KEINE WEITEREN TABELLEN!!! ... DONE!<br>";

			return true;
		}

		flush();
		ob_flush();

		$curTableName = $this->curTableName;
		echo "Lese einen Tabellennamen aus MySQL der noch nicht importiert wurde. (" . $curTableName . ") ... DONE!<br>";

		echo "<br>";


		echo "Tabellen und Felder ggf. anlegen!<br>";

		flush();
		ob_flush();
		$cntFieldnames = $this->createNextMySQLTable();

		flush();
		ob_flush();
		echo "Tabellen und Felder ggf. anlegen (" . $cntFieldnames . " Felder für Tabelle " . $this->curTableName . " angelegt oder bereits vorhanden)... DONE!<br>";

		echo "<br>";

		flush();
		ob_flush();

		$this->createNextMySQLTableLoop();

	}










	private function createNextMySQLTable()
	{

		// Query für die Feldnamen aus Dimari
		$query = 'SELECT *
					FROM rdb$relation_fields f
					JOIN rdb$relations r ON f.rdb$relation_name = r.rdb$relation_name
					 AND r.rdb$view_blr IS NULL
					 AND (r.rdb$system_flag IS NULL OR r.rdb$system_flag = 0)
					 WHERE f.rdb$relation_name = \'' . trim($this->curTableName) . '\'
				ORDER BY f.rdb$field_position';

		$result = ibase_query($this->dbF, $query);

		// Zähler für die erhaltenen Tabellennamen aus Dimari
		$cnt = 0;

		// Obj zu MySQL
		$mysqli = $this->mysqli;

		// Teil-Query zum anlegen der Tabelle und Felder
		$preQuyeryMySQL = 'CREATE TABLE IF NOT EXISTS `' . trim($this->curTableName) . '` (';
		$midQueryMySQL = '';
		while ($row = ibase_fetch_assoc($result)) {
			$cnt++;

			if ($cnt > 1)
				$midQueryMySQL .= ', ';

			// Teil-Query zum anlegen der Tabelle und Felder
			$midQueryMySQL .= '`' . trim($row['RDB$FIELD_NAME']) . '` varchar(100) NOT NULL default \'\'';
		}

		// Dimari free result
		ibase_free_result($result);

		$postQueryMySQL = ')';

		// Erstelle Query zum anlegen der Tabelle und Felder
		$queryMySQL = $preQuyeryMySQL . $midQueryMySQL . $postQueryMySQL;

		// Führe MySQL - Query aus
		$mysqli->query($queryMySQL);

		// Update das Tabellen-Feld... Creation = done
		$queryMySQL = "UPDATE `aa_import_tables` SET enum_creation_done = 'yes' WHERE aa_import_table_id = '" . $this->curTableID . "' LIMIT 1";

		$mysqli->query($queryMySQL);

		return $cnt;

	}










	// Lese den nächsten zu importierenden Tabellennamen ein
	private function getSingleTablenameFormMySQL($getEnaumFieldname)
	{

		$mysqli = $this->mysqli;

		$queryMySQL = "SELECT * FROM aa_import_tables WHERE " . $getEnaumFieldname . " = 'no' ORDER BY aa_import_table_id LIMIT 1";

		$result = $mysqli->query($queryMySQL);

		$num_rows = $result->num_rows;

		if ($num_rows == 1) {
			$row = $result->fetch_object();
			$this->curTableName = trim($row->table_name);
			$this->curTableID = trim($row->aa_import_table_id);

			mysqli_free_result($result);

			return true;
		}
		else {

			mysqli_free_result($result);

			// print ('Alle Tabllen erstellt! ... save exit!');

			return false;
		}

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
			$queryMySQL = "INSERT INTO aa_import_tables SET table_name = '" . $row->RELATION_NAME . "'";

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

		$mysqli = new mysqli('p:' . $this->myMySQLHost, $this->myMySQLUsername, $this->myMySQLPassword, $this->myMySQLDBName);


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

		RETURN true;

	}    // END private function pconnect()










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
