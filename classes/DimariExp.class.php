<?php


/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 10.03.2016
 * Time: 15:32
 */
class DimariExp extends Dimari
{

//    public $globalData = array();

	public $globalOut = array();        // Vor Auslieferung










	// Klassen - Konstruktor
	public function __construct($host, $username, $password, $hostRadi, $usernameRadi, $passwordRadi)
	{

		parent::__construct($host, $username, $password, $hostRadi, $usernameRadi, $passwordRadi);

	}   // END public function __construct(...)










	// Initial Methode!!
	public function initialDimariExport()
	{

		//$this->mainToExcel();
		$this->flushByFunctionCall('mainToExcel');


		// globalDataTMP brauche icht mehr... gebe Speicher frei:
		$this->globalData = $this->globalDataTMP;
		$this->globalDataTMP = '';



//        echo "<pre>";
//        print_r($this->globalData);
//        echo "</pre><br>";

		return true;

	}   // END public function initialDimariExport()










	public function writeToExcel()
	{

		$excel = $this->writeToExcelHeadline();

		// Durchlauf 0 ... Headline schreiben
		foreach($this->globalOut as $cntRow => $dataArray) {

			// echo "Zeile: " . $cntRow . "<br>";

			$leadingPipe = false;

			foreach($dataArray as $fieldname => $value) {

				//echo "Feldname: " . $fieldname . " => Value: " . $value . "<br>";

				// Trennzeichen setzen?
				if ($leadingPipe)
					$excel .= ';';

				$excel .= '"' . utf8_encode(trim($value)) . '"';

				// Ab jetzt Trennzeichen setzen!
				$leadingPipe = true;

			}

			$excel .= "\r\n";

		}

//        echo "<br><hr>";
//        echo "<pre>";
//        print_r($excel);
//        echo "</pre><br>";

		// Datei schreiben:
		$curFilename = $this->writeFile($this->setExportType, $excel);

		return true;
	}










	// Schreibt die Export Datei mit Format Version und Datum
	public function writeFile($type, $content, $filename = false)
	{

		if (!$filename)
			$filename = 'DimariDiensteExp_' . $type . '_' . 'V001_' . date('Ymd');

		// '/var/www/html/www/uploads/';
		$fullFilePathAndName = 'uploads/exports/' . $filename . '.csv';


		// Existiert Datei schon? ... wenn ja, Version erhöhen
		if (file_exists($fullFilePathAndName)) {

			// Versionsnummer ermitteln
			preg_match('/(_V(\d+))/', $filename, $matches);
			$fileVersion = $matches[2];

			$nextVersion = $fileVersion + 1;
			$nextVersion = sprintf("%'.03d", $nextVersion);

			$filename = 'DimariDiensteExp_' . $type . '_V' . $nextVersion . '_' . date('Ymd');

			// Für die Info-Ausgabe
			$this->globalLastFilename = $filename;

			// Selbstaufruf ... endet wenn freie Versionsnummer gefunden wurde
			$this->writeFile($type, $content, $filename);
		}
		else {
			// Für die Info-Ausgabe
			$this->globalLastFilename = $filename;

			if ($this->setNoFileCreation != 'yes') {
				$fp = fopen($fullFilePathAndName, 'w');
				fwrite($fp, $content);
				fclose($fp);
			}
		}

		return true;

	}    // END public function writeFile($type, $content, $filename=false)










	public function writeToExcelHeadline()
	{

		$excel = '';

		// Durchlauf 0 ... Headline schreiben
		foreach($this->globalOut as $cntRow => $dataArray) {

			// echo "Zeile: " . $cntRow . "<br>";

			$leadingPipe = false;

			foreach($dataArray as $fieldname => $value) {

				//echo "Feldname: " . $fieldname . " => Value: " . $value . "<br>";

				// Trennzeichen setzen?
				if ($leadingPipe)
					$excel .= ';';

				$excel .= '"' . utf8_encode(trim($fieldname)) . '"';

				// Ab jetzt Trennzeichen setzen!
				$leadingPipe = true;

			}

			$excel .= "\r\n";

			break;
		}

		return $excel;
	}










	// Daten für Exort bzw. zum Dateischreiben aufbereiten
	public function preWriteToFiel()
	{

		$cntRowEntry = 0;

		$exp = array();

		// Haupt - Durchlauf
		foreach($this->globalData as $preCurCustomerID => $mainCustomerArray) {


			// Durchlauf Customer
			foreach($mainCustomerArray['CUSTOMER_ID'] as $curCustomerID => $customerArray) {
				if (isset($customerArray['KUNDEN_NR'])) {


					// DATEN EBENE

					$cntRowEntry++;

					// FORMAT: $exp[$cntRowEntry]['KUNDEN_NR'] = $customerArray['KUNDEN_NR'];
					$this->addExp($customerArray, 'KUNDEN_NR', $exp, $cntRowEntry);                 // 1
					$this->addExp($customerArray, 'DIENST_ART', $exp, $cntRowEntry);                // 2
					$this->addExp($customerArray, 'DIENST_BEZEICHNUNG', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'DIENST_BEMERKUNG', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'DATEN_USERNAME', $exp, $cntRowEntry);            // 5
					$this->addExp($customerArray, 'DATEN_USERPASSWORT', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'NAT_BETREIBEREBENE', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'CLIENT_ID', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'USERINFO_ID', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'ROUTER_MODELL', $exp, $cntRowEntry);             // 10
					$this->addExp($customerArray, 'ROUTER_SERIEN_NR', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'ACS_ID', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'EXT_PRODUKT_ID', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'OPTION_1', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'OPTION_2', $exp, $cntRowEntry);                  // 15
					$this->addExp($customerArray, 'OPTION_3', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'GUELTIG_VON', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'GUELTIG_BIS', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'ERFASST_AM', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'UNTERZEICHNET_AM', $exp, $cntRowEntry);              // 20
					$this->addExp($customerArray, 'WIDERRUFEN_AM', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'GEKUENDIGT_AM', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'STANDORT', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'INSTALLATIONSTERMIN', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'HAUPTVERTEILER', $exp, $cntRowEntry);                // 25
					$this->addExp($customerArray, 'KABELVERZWEIGER', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'DOPPELADER_1', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'DOPPELADER_2', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'VOIP_DIENST_BEZEICHNUNG', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'VOIP_DIENST_BEMERKUNG', $exp, $cntRowEntry);         // 30
					$this->addExp($customerArray, 'VOIP_EXT_PRODUKT_ID', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'SPERRE_0900', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'UEBERMITTLUNG_RUFNR', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'PURTEL_KUNDENNUMMER', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'PURTEL_HAUPTANSCHLUSS', $exp, $cntRowEntry);         // 35
					$this->addExp($customerArray, 'VOIP_SPERRE_AKTIV', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'VOIP_PORTIERUNG', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'VOIP_PORT_TERMIN', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'VOIP_PORT_ABG_CARRIER', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'VOIP_PORT_REST_MSN_KUENDIGEN', $exp, $cntRowEntry);  // 40

					for($i = 1; $i <= 3; $i++) {
						$this->addExp($customerArray, 'VOIP_ACCOUNT_' . $i, $exp, $cntRowEntry);
						$this->addExp($customerArray, 'VOIP_ACCOUNT_PASSWORT_' . $i, $exp, $cntRowEntry);
						$this->addExp($customerArray, 'VOIP_NATIONAL_VORWAHL_' . $i, $exp, $cntRowEntry);
						$this->addExp($customerArray, 'VOIP_KOPFNUMMER_' . $i, $exp, $cntRowEntry);
						$this->addExp($customerArray, 'VOIP_TRANSACTION_NO_' . $i, $exp, $cntRowEntry);
					}

					$this->addExp($customerArray, 'EGN_VERFREMDUNG', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'TELEFONBUCHEINTRAG', $exp, $cntRowEntry, 'N');
					$this->addExp($customerArray, 'TELEBUCH_NACHNAME', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'TELEBUCH_VORNAME', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'TELEBUCH_STRASSE', $exp, $cntRowEntry);    // 60
					$this->addExp($customerArray, 'TELEBUCH_PLZ', $exp, $cntRowEntry);

					$this->addExp($customerArray, 'TELEBUCH_ORT', $exp, $cntRowEntry);  // 64 (!)
					$this->addExp($customerArray, 'TELEBUCH_TEL', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'TELEBUCH_FAX', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'TELEBUCH_SPERRE_INVERS', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'TELEBUCH_EINTRAG_ELEKT', $exp, $cntRowEntry);

					for($i = 4; $i <= 10; $i++) {
						$this->addExp($customerArray, 'VOIP_ACCOUNT_' . $i, $exp, $cntRowEntry);
						$this->addExp($customerArray, 'VOIP_ACCOUNT_PASSWORT_' . $i, $exp, $cntRowEntry);
						$this->addExp($customerArray, 'VOIP_NATIONAL_VORWAHL_' . $i, $exp, $cntRowEntry);
						$this->addExp($customerArray, 'VOIP_KOPFNUMMER_' . $i, $exp, $cntRowEntry);
						$this->addExp($customerArray, 'VOIP_TRANSACTION_NO_' . $i, $exp, $cntRowEntry);
					}

					$this->addExp($customerArray, 'VOIP_ABG_PORT_TERMIN', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'VOIP_ABG_PORT_AUF_CARRIER', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'DSLAM_PORT', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'TELEFONBUCH_UMFANG', $exp, $cntRowEntry);


					$this->addExp($customerArray, 'TV_DIENSTE', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'ROUTER_MAC_ADR', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'FTTH_CUS_ID', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'DOCSIS', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'BRIDGE_MODE', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'ELVIS_HAUPT_ACCOUNT', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'CPE_VOIP_ACCOUNT_2', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'BANDBREITE', $exp, $cntRowEntry);
					// $this->addExp($customerArray, 'COS_ID', $exp, $cntRowEntry);
					$this->addExp($customerArray, 'CUST_ID', $exp, $cntRowEntry);


				}
			}   // END // Durchlauf Customer



		} // END // Haupt - Durchlauf


//        echo "<pre>";
//        print_r($this->globalData);
//        print_r($exp);
//        echo "</pre><br>";

		$this->globalOut = '';
		$this->globalOut = $exp;

		return true;

	}   // END public function preWriteToFiel()










	// Fügt Daten an das Ausgabe Array an
	private function addExp($customerArray, $fieldname, & $exp, $cntRowEntry, $default = '')
	{

		if (isset($customerArray[$fieldname]))
			$exp[$cntRowEntry][$fieldname] = $customerArray[$fieldname];
		else
			$exp[$cntRowEntry][$fieldname] = $default;

		return true;

	} // END private function addExp()










	// Haupt - Methode für den Excelexport
	public function mainToExcel()
	{

		// Cnt
		$cntCustomerOnStart = 0;
		$cntContractOnStart = 0;
		$cntProductOnStart = 0;
		$cntCovIDOnStart = 0;
		$cntSubIDOnStart = 0;


		// Durchlauf Customer
		foreach($this->globalData['CUSTOMER_ID_Array'] as $curCustomerID => $customerArray) {

			$cntSubOnSingleCustomer = 0;


			$cntCustomerOnStart++;
			// echo "$curCustomerID<br>";


			// Neue Datenzeile erzeugen
			$curDataSet = array();
			unset ($curDataSet);


			// Setze Kundennummer
			$curDataSet['CUSTOMER_ID'][$curCustomerID]['KUNDEN_NR'] = $curCustomerID;


			// Setze Dienstart (FTTC oder FTTH)
			$curDataSet['CUSTOMER_ID'][$curCustomerID]['DIENST_ART'] = $this->setExportType;


			// Setze STANDORT
			// HARDCODE
			$curDataSet['CUSTOMER_ID'][$curCustomerID]['STANDORT'] = 'KD';


			// TELEFONBUCH_UMFANG
			if (isset($customerArray['TELEFONBUCH_UMFANG']))
				$curDataSet['CUSTOMER_ID'][$curCustomerID]['TELEFONBUCH_UMFANG'] = $customerArray['TELEFONBUCH_UMFANG'];
			else
				$curDataSet['CUSTOMER_ID'][$curCustomerID]['TELEFONBUCH_UMFANG'] = '';


			// Durchlauf Contract
			foreach($customerArray['CONTRACT_ID'] as $curContractID => $contractArray) {

				$cntContractOnStart++;
				// echo "$curContractID<br>";


				// DATEN EBENE CONTRACT

				// Setze CUST_ID ... CONTRACT_ID
				$curDataSet['CUSTOMER_ID'][$curCustomerID]['CUST_ID'] = $contractArray['CONTRACT_ID'];


				// Setze GUELTIG_VON
				if (isset($contractArray['GUELTIG_VON']))
					$curDataSet['CUSTOMER_ID'][$curCustomerID]['GUELTIG_VON'] = $contractArray['GUELTIG_VON'];
				else
					$curDataSet['CUSTOMER_ID'][$curCustomerID]['GUELTIG_VON'] = '';



				// Setze GUELTIG_BIS
				if (isset($contractArray['GUELTIG_BIS']))
					$curDataSet['CUSTOMER_ID'][$curCustomerID]['GUELTIG_BIS'] = $contractArray['GUELTIG_BIS'];
				else
					$curDataSet['CUSTOMER_ID'][$curCustomerID]['GUELTIG_BIS'] = '';



				// Setze ERFASST_AM
				if (isset($contractArray['ERFASST_AM']))
					$curDataSet['CUSTOMER_ID'][$curCustomerID]['ERFASST_AM'] = $contractArray['ERFASST_AM'];
				else
					$curDataSet['CUSTOMER_ID'][$curCustomerID]['ERFASST_AM'] = '';



				// Setze UNTERZEICHNET_AM
				if (isset($contractArray['UNTERZEICHNET_AM']))
					$curDataSet['CUSTOMER_ID'][$curCustomerID]['UNTERZEICHNET_AM'] = $contractArray['UNTERZEICHNET_AM'];
				else
					$curDataSet['CUSTOMER_ID'][$curCustomerID]['UNTERZEICHNET_AM'] = '';



				// Durchlauf Products
				if ((isset($contractArray['PRODUCT_ID'])) && (count($contractArray['PRODUCT_ID']) > 0)) {
					foreach($contractArray['PRODUCT_ID'] as $curProductID => $productArray) {

						$cntProductOnStart++;
						// echo "$curProductID<br>";


						// DATEN EBENE PRODUCT


						// VDSL Produkt?
						if (in_array($curProductID, $this->setProductIDForInternet[$this->setExportType])) {

							// Setze einige Werte für den Export die vornehmlich VDSL bestimmt sind
							$this->preExcelVDSL($curDataSet, $curCustomerID, $curProductID, $productArray);

						}



						// Setze ClientID ... laut S. Reimann ist die gleich der Kundennummer
						$curDataSet['CUSTOMER_ID'][$curCustomerID]['CLIENT_ID'] = $curCustomerID;


						// Setze EGN_VERFREMDUNG
						if ((isset($productArray['EGN_VERFREMDUNG'])) && ($productArray['EGN_VERFREMDUNG'] == '1'))
							$curDataSet['CUSTOMER_ID'][$curCustomerID]['EGN_VERFREMDUNG'] = 'J';
						else
							$curDataSet['CUSTOMER_ID'][$curCustomerID]['EGN_VERFREMDUNG'] = 'N';



						// Durchlauf COV_ID
						if ((isset($productArray['COV_ID'])) && (count($productArray['COV_ID']) > 0)) {
							foreach($productArray['COV_ID'] as $curCOV_ID => $covIDArray) {

								$cntCovIDOnStart++;
								// echo "$curCOV_ID<br>";


								// SETZE VOIP_DIENST_BEZEICHNUNG
								// HARDCODE TKRZ Telefonie
								if (in_array($curProductID, $this->setProductIDForVOIP[$this->setExportType]))
									$curName = 'TKRZ Telefonie';
								else
									$curName = $productArray['PRODUCT_Name'];

								$curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_DIENST_BEZEICHNUNG'] = $curName;
								$curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_DIENST_BEMERKUNG'] = '';


								// Setze Ext_Produkt_ID
								$curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_EXT_PRODUKT_ID'] = $this->setExtProdServiceID['setProductIDForVOIP'];


								// Setze COS_ID
								$curDataSet['CUSTOMER_ID'][$curCustomerID]['COS_ID'] = $productArray['COS_ID'];


								// Durchlauf SUBS_ID
								if ((isset($covIDArray['SUBS_ID'])) && (count($covIDArray['SUBS_ID']) > 0)) {
									foreach($covIDArray['SUBS_ID'] as $curSUBS_ID => $subsIDArray) {

										$cntSubIDOnStart++;
										// echo "$curSUBS_ID<br>";


										// DATEN EBENE SUBS


										// VOIP_PORT_TERMIN
										if (!isset($curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_TERMIN'])) {

											if (isset($subsIDArray['VOIP_PORT_TERMIN']))
												$curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_TERMIN'] = $subsIDArray['VOIP_PORT_TERMIN'];
											else
												$curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_TERMIN'] = '';
										}
										else {
											if ((strlen($curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_TERMIN']) < 1) && (isset($subsIDArray['VOIP_PORT_TERMIN'])))
												$curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_TERMIN'] = $subsIDArray['VOIP_PORT_TERMIN'];
										}



										// VOIP_PORT_ABG_CARRIER
										if (!isset($curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_ABG_CARRIER'])) {

											if (isset($subsIDArray['CARRIER_CODE']))
												$curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_ABG_CARRIER'] = $subsIDArray['CARRIER_CODE'];
											else
												$curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_ABG_CARRIER'] = '';

										}
										else {
											if ((strlen($curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_ABG_CARRIER']) < 1) && (isset($subsIDArray['CARRIER_CODE'])))
												$curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_ABG_CARRIER'] = $subsIDArray['CARRIER_CODE'];
										}



										// VOIP Rufnummer verarbeiten
										$cntSubOnSingleCustomer++;

										// VOIP_ACCOUNT_X
										$curVOIP_ACCOUNT = 'VOIP_ACCOUNT_' . $cntSubOnSingleCustomer;
										$this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, $curVOIP_ACCOUNT);


										// VOIP_ACCOUNT_PASSWORT_X
										$curVOIP_ACCOUNT_PASSWORT = 'VOIP_ACCOUNT_PASSWORT_' . $cntSubOnSingleCustomer;
										$this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, $curVOIP_ACCOUNT_PASSWORT);


										// VOIP_NATIONAL_VORWAHL__X
										$curVOIP_NATIONAL_VORWAHL = 'VOIP_NATIONAL_VORWAHL_' . $cntSubOnSingleCustomer;
										$this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, $curVOIP_NATIONAL_VORWAHL);


										// VOIP_KOPFNUMMER__X
										$curVOIP_KOPFNUMMERL = 'VOIP_KOPFNUMMER_' . $cntSubOnSingleCustomer;
										$this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, $curVOIP_KOPFNUMMERL);


										// Telefonbucheintrag?
										if ((isset($subsIDArray['TELEFONBUCHEINTRAG'])) && ($subsIDArray['TELEFONBUCHEINTRAG'] == 'J')) {

											$curDataSet['CUSTOMER_ID'][$curCustomerID]['TELEFONBUCHEINTRAG'] = 'J';

											// Wenn ich noch keine Daten habe... übernehme ich diese
											if (!isset($curDataSet['CUSTOMER_ID'][$curCustomerID]['TELEFONBUCH_NACHNAME'])) {


												$this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_NACHNAME');
												$this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_VORNAME');
												$this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_STRASSE');
												$this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_PLZ');
												$this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_ORT');


												// TELEBUCH_TEL
												$myNum = $curDataSet['CUSTOMER_ID'][$curCustomerID][$curVOIP_NATIONAL_VORWAHL] . '/' . $curDataSet['CUSTOMER_ID'][$curCustomerID][$curVOIP_KOPFNUMMERL];
												$curDataSet['CUSTOMER_ID'][$curCustomerID]['TELEBUCH_TEL'] = $myNum;


												$this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_FAX');
												$this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_SPERRE_INVERS');
												$this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_EINTRAG_ELEKT');

											}
										}
										else {
											if (!isset($curDataSet['CUSTOMER_ID'][$curCustomerID]['TELEFONBUCHEINTRAG']))
												$curDataSet['CUSTOMER_ID'][$curCustomerID]['TELEFONBUCHEINTRAG'] = 'N';
										}


									}
								}   // END // Durchlauf SUBS_ID



							}
						}   // END // Durchlauf COV_ID


						// Setze alles was wir nicht haben... noch unbekannt... oder leer bleiben kann
						$this->preExcelAllUnknown($curDataSet, $curCustomerID, $curProductID, $productArray);


					}
				}   // END // Durchlauf Products



			}   // END // Durchlauf Contract



			// DATEN EBENE CUSTOMER

			// Setze INSTALLATIONSTERMIN Doppelt abgefangen
			// Hab noch keinen Installationstermin gesetz, weiche aus auf ... GUELTIG_VON... VOIP_PORT_TERMIN... wenn möglich
			if ((!isset($curDataSet['CUSTOMER_ID'][$curCustomerID]['INSTALLATIONSTERMIN'])) || (strlen($curDataSet['CUSTOMER_ID'][$curCustomerID]['INSTALLATIONSTERMIN']) < 1)) {


				// GUELTIG_VON eventuell gesetzt?
				if ((isset($curDataSet['CUSTOMER_ID'][$curCustomerID]['GUELTIG_VON'])) && (strlen($curDataSet['CUSTOMER_ID'][$curCustomerID]['GUELTIG_VON']) > 0)) {
					$curDataSet['CUSTOMER_ID'][$curCustomerID]['INSTALLATIONSTERMIN'] = $curDataSet['CUSTOMER_ID'][$curCustomerID]['GUELTIG_VON'];
				}
				else {
					if (isset($curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_TERMIN']))
						$curDataSet['CUSTOMER_ID'][$curCustomerID]['INSTALLATIONSTERMIN'] = $curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_TERMIN'];
					else
						$curDataSet['CUSTOMER_ID'][$curCustomerID]['INSTALLATIONSTERMIN'] = '';
				}
			}



			// Setzte VOIP_PORTIERUNG ... ggf. später noch mit der Excelliste von Sasch überschreibbar
			// Wenn keine SUBS_ID && Excel Feld "O" == ja ... Portierung N
			// Wenn SUBS_ID ... Portierung J
			// Default wird hier gesetzt auf N
			if ($cntSubOnSingleCustomer > 0)
				$curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORTIERUNG'] = 'J';
			else
				$curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORTIERUNG'] = 'N';


			// In globalen Array speichern
			$this->globalDataTMP[$curCustomerID] = $curDataSet;

		}   // END // Durchlauf Customer


		return true;

	}   // END  private function mainToExcel()










	// Fügt Daten dem Datensatz hinzu
	private function addDataToDataSetBySubsIDArray(& $curDataSet, $curCustomerID, $subsIDArray, $fiedlname)
	{

		// Setze Wert
		if (isset($subsIDArray[$fiedlname]))
			$curDataSet['CUSTOMER_ID'][$curCustomerID][$fiedlname] = $subsIDArray[$fiedlname];
		else
			$curDataSet['CUSTOMER_ID'][$curCustomerID][$fiedlname] = '';

		return true;

	} // END private function addDataToDataSet(& $curDataSet, $curCustomerID, $subsIDArray)



	// Setze alles was wir nicht haben... noch unbekannt... oder leer bleiben kann
	// HARDCODE Diverses
	private function preExcelAllUnknown(& $curDataSet, $curCustomerID, $curProductID, $productArray)
	{

		// ROUTER_MODELL
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['ROUTER_MODELL'] = '';

		// ROUTER_SERIEN_NR
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['ROUTER_SERIEN_NR'] = '';

		// ACS_ID
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['ACS_ID'] = '';


		// WIDERRUFEN_AM
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['WIDERRUFEN_AM'] = '';

		// GEKUENDIGT_AM
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['GEKUENDIGT_AM'] = '';


		// SPERRE_0900 ... laut L. Koschin immer sperren
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['SPERRE_0900'] = 'J';

		// UEBERMITTLUNG_RUFNR ... laut L. Koschin immer übermitteln
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['UEBERMITTLUNG_RUFNR'] = 'J';

		// VOIP_SPERRE_AKTIV ... laut L. Koschin nie
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_SPERRE_AKTIV'] = 'N';

		// VOIP_PORT_REST_MSN_KUENDIGEN ... laut S. Reimann Default N belassen
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_REST_MSN_KUENDIGEN'] = 'N';

		// TODO hier ist manuelles nacharbeiten nötig
		// VOIP_ABG_PORT_TERMIN ... laut S. Reimann später und von Hand blocken
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_ABG_PORT_TERMIN'] = '';

		// TODO hier ist manuelles nacharbeiten nötig
		// VOIP_ABG_PORT_TERMIN ... laut S. Reimann später und von Hand blocken
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_ABG_PORT_AUF_CARRIER'] = '';

		return true;

	}   // END private function preExcelAllUnknown(...)










	// Setze einige VDSL Werte
	private function preExcelVDSL(& $curDataSet, $curCustomerID, $curProductID, $productArray)
	{

		// Setze Optionen:
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['OPTION_1'] = '';
		// HARDCODE OPTION_2 auf 7
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['OPTION_2'] = '7';
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['OPTION_3'] = '';


		// Setze Dienst-Bezeichnung
		// HARDCODE VDSL4me
		if ($curProductID == '10059') {

			$curName = 'VDSL4me';

			// Energiekunde! ... Trage das unter Optionen ein
			$curDataSet['CUSTOMER_ID'][$curCustomerID]['OPTION_1'] = '21';
		}
		elseif ($curProductID == '10070')
			$curName = 'VDSL4me';
		else
			$curName = $productArray['PRODUCT_Name'];

		$curDataSet['CUSTOMER_ID'][$curCustomerID]['DIENST_BEZEICHNUNG'] = $curName;
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['DIENST_BEMERKUNG'] = '';


		// Setze NAT_BETREIBEREBENE
		// HARDCODE
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['NAT_BETREIBEREBENE'] = 'N';


		// Setze Ext_Produkt_ID
		$curDataSet['CUSTOMER_ID'][$curCustomerID]['EXT_PRODUKT_ID'] = $this->setExtProdServiceID['setProductIDForInternet'];

		return true;

	}   // END private function preExcelVDSL(...)










	private function getCurIndex()
	{

		$curNum = count($this->globalOut);

		$newIndex = $curNum + 1;

		$newIndex = $newIndex - 1;

		return $newIndex;

	}   // END private function getCurIndex()


}   // END class DimariExp

