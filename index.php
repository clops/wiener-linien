<?
	/***
	 *
	 * Simple Renderer of a JSON File from the Wiener Linien CSV Files
	 * The output will be cached and recreated only when any of the
	 * original files is changed.
	 *
	 * Hail procedural programming :D
	 *
	 * @author  Alexey Kulikov <me@clops.at>
	 * @since   05.10.2013
	 *
	 */

	ignore_user_abort(true);                // run to the end mon!
	ini_set('max_execution_time','180');    // 5 minutes
	ini_set('memory_limit','256M');         // 256 MB

	/***
	 * Reads CSV Data line by line and put it into an assoc array
	 *
	 * @param        $filename
	 * @param string $delimiter
	 * @return array
	 */
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

	$haltestellen = readCSVDataFromFile('wienerlinien-ogd-haltestellen.csv');
	$linien       = readCSVDataFromFile('wienerlinien-ogd-linien.csv');
	$steige       = readCSVDataFromFile('wienerlinien-ogd-steige.csv');

	// Parse all the data into one array that makes sense
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

			if(!isset($haltestellen[$steig['FK_HALTESTELLEN_ID']]['LINES'])) {
				$haltestellen[$steig['FK_HALTESTELLEN_ID']]['LINES'] = array();
			}

			//set cache data
			if( isset($linie['BEZEICHNUNG']) and !in_array($linie['BEZEICHNUNG'], $haltestellen[$steig['FK_HALTESTELLEN_ID']]['LINES']) ) {
				$haltestellen[$steig['FK_HALTESTELLEN_ID']]['LINES'][] = $linie['BEZEICHNUNG'];
			}

		}
	}

	//now encode the array to a json string and send it to a file
	file_put_contents('cache/current.json', json_encode($haltestellen));
?>