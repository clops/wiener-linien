<?
	setlocale(LC_ALL, 'en_US.UTF16');

	function readCSVDataFromFile($filename, $delimiter=';') {
		if(!file_exists($filename) || !is_readable($filename))
			return FALSE;

		//Set the encoding to UTF-8, so when reading files it ignores the BOM
		//mb_internal_encoding('UTF-8');

		$header = NULL;
		$data = array();
		if (($handle = fopen($filename, 'r')) !== FALSE) {
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
				if(!$header) {
					$header = $row;
					if (substr($header[0], 0, 3) == "\xef\xbb\xbf") {
						$header[0] = substr($header[0], 4, -1);
					}
				} else {
					$key        = $row[0];
					$data[$key] = array_combine($header, $row);
				}
			}
			fclose($handle);
		}
		return $data;
	}


	$data = file_get_contents('wienerlinien-ogd-haltestellen.csv');


	$haltestellen = readCSVDataFromFile('wienerlinien-ogd-haltestellen.csv');
	$linien       = readCSVDataFromFile('wienerlinien-ogd-linien.csv');
	$steige       = readCSVDataFromFile('wienerlinien-ogd-steige.csv');

	echo '<pre>';
	foreach($steige as $steig){
		if(
			isset($steig['FK_HALTESTELLEN_ID']) &&
			isset($haltestellen[$steig['FK_HALTESTELLEN_ID']]) &&
			isset($steig['FK_LINIEN_ID']) &&
			isset($linien[$steig['FK_LINIEN_ID']])
		) {
			$linie = $linien[$steig['FK_LINIEN_ID']];

			//set plattforms
			$haltestellen[$steig['FK_HALTESTELLEN_ID']]['PLATFORMS'][] = array(
				'LINIE'             => (isset($linie['BEZEICHNUNG'])?$linie['BEZEICHNUNG']:''),
				'ECHTZEIT'          => (isset($linie['ECHTZEIT'])?$linie['ECHTZEIT']:''),
				'VERKEHRSMITTEL'    => (isset($linie['VERKEHRSMITTEL'])?$linie['VERKEHRSMITTEL']:''),

				'RBL_NUMMER'        => (isset($steig['RBL_NUMMER'])?$steig['RBL_NUMMER']:''),
				'BEREICH'           => (isset($steig['BEREICH'])?$steig['BEREICH']:''),
				'RICHTUNG'          => (isset($steig['RICHTUNG'])?$steig['RICHTUNG']:''),
				'REIHENFOLGE'       => (isset($steig['REIHENFOLGE'])?$steig['REIHENFOLGE']:''),
				'STEIG'             => (isset($steig['STEIG'])?$steig['STEIG']:''),
				'STEIG_WGS84_LAT'   => (isset($steig['STEIG_WGS84_LAT'])?$steig['STEIG_WGS84_LAT']:''),
				'STEIG_WGS84_LON'   => (isset($steig['STEIG_WGS84_LON'])?$steig['STEIG_WGS84_LON']:'')
			);

			//set cache data
			if(isset($linie['BEZEICHNUNG'])) {
				$haltestellen[$steig['FK_HALTESTELLEN_ID']]['LINES'][$linie['BEZEICHNUNG']] = $linie['BEZEICHNUNG'];
			}

		}
	}

	echo '<pre>';
	print_r($haltestellen);
?>