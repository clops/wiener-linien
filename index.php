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

	#### EXECUTION SETTINGS ####
	#
	ignore_user_abort(true);                // run to the end mon!
	ini_set('max_execution_time','600');    // 10 minutes
	ini_set('memory_limit','64M');


	#### DATA LOCATION AND OTHER SETUP ####
	#
	define('HALTESTELLEN_DATA_LOCATION',    'http://data.wien.gv.at/csv/wienerlinien-ogd-haltestellen.csv');
	define('LINIEN_DATA_LOCATION',          'http://data.wien.gv.at/csv/wienerlinien-ogd-linien.csv');
	define('STEIGE_DATA_LOCATION',          'http://data.wien.gv.at/csv/wienerlinien-ogd-steige.csv');
	define('LOCAL_FILE',                    'cache/current.json');


	#### INIT ####
	//from the console ths script has less output
	$isCLI = ( php_sapi_name() == 'cli' );
	$verboseOutput       = false;
	$forceRegenerateFile = false;

	if($isCLI) {
		$options = getopt( 'vf', array('verbose','force') );

		//debug output marker
		if(isset($options['v']) || isset($options['verbose'])) {
			$verboseOutput = true;
		}

		//force regenerate file marker
		if(isset($options['f']) || isset($options['force'])) {
			$forceRegenerateFile = true;
		}
	}

	define('VERBOSE', $verboseOutput);

	#### METHODS & STUFF ####
	#
	/**
	 * @param $message
	 */
	function _log( $message ) {
		if( VERBOSE ) {
			print $message."\n";
		}
	}


	/***
	 * Reads CSV Data line by line and put it into an assoc array
	 *
	 * @param        $filename
	 * @param string $delimiter
	 * @return array
	 */
	function _readCSVDataFromFile($filename, $delimiter=';') {
		$tempFileName = tempnam('cache', 'remote_');
		_log( 'Fetch remote file "'.$filename.'" to local cache file "'.$tempFileName.'"' );

		$data = file_get_contents($filename);
		file_put_contents($tempFileName, $data);

		_log( 'Attempring to read file "'.$tempFileName.'"' );

		if(!file_exists($tempFileName) || !is_readable($tempFileName)) {
			_log( 'Failed! File does not exist or is not readable' );
			return FALSE;
		}

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

		_log( 'Done!' );
		unlink($tempFileName);
		return $data;
	}


	/**
	 * Creates local JSon file with all the data parsed correctly
	 *
	 * @return bool
	 */
	function createLocalJSONCache() {
		$haltestellen = _readCSVDataFromFile(HALTESTELLEN_DATA_LOCATION);
		$linien       = _readCSVDataFromFile(LINIEN_DATA_LOCATION);
		$steige       = _readCSVDataFromFile(STEIGE_DATA_LOCATION);

		_log( 'Parsing CSV Data' );

		if( !is_array($steige) || !is_array($linien) || !is_array($steige) ) {
			throw new Exception('No data read from Wiener Linien, aborting');
		}

		// Parse all the data into one array that makes sense
		foreach($steige as $steig) {
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

		_log( 'Done!' );
		_log( 'Writing cached file...' );

		//write data to a new json file and do an atomic replace to make sure there is no loss of service
		$newFileName = tempnam('cache', 'preparing_');
		file_put_contents($newFileName, json_encode($haltestellen[214460106]));
		rename($newFileName, LOCAL_FILE);
		chmod(LOCAL_FILE, 0644);
		_log( 'Done!' );

		return true;
	}


	/**
	 * @param string $url
	 * @return int (unix timestamp)
	 */
	function getRemoteFileLastModifiedTimestamp( $url ) {
		$return  = 0;
		$headers = get_headers( $url, 1 );
		if(isset($headers['Last-Modified'])) {
			$return  = strtotime($headers['Last-Modified']);
		}

		return $return;
	}


	/**
	 * Checks if the locally cached file needs recompilation
	 *
	 * @return bool
	 */
	function localJSONCacheOutdated() {
		_log( 'Is local JSON Cache outdated?' );

		$localTimestamp = filemtime( LOCAL_FILE );
		if(
			$localTimestamp < getRemoteFileLastModifiedTimestamp( HALTESTELLEN_DATA_LOCATION ) ||
			$localTimestamp < getRemoteFileLastModifiedTimestamp( LINIEN_DATA_LOCATION ) ||
			$localTimestamp < getRemoteFileLastModifiedTimestamp( STEIGE_DATA_LOCATION )
		){
			_log( 'Yes!' );
			return true;
		}
		_log( 'No' );
		return false;
	}


	#### ACTUAL PROGRAM ####
	#
	/**
	 * The algorithm is very simple:
	 *
	 * Is there a local JSON File?
	 *   NO -->  create it
	 *   YES --> get creation timestamp of json file,
	 *           get last modified timestamps from remote files
	 *           compare, if remote is more than 1 hour older
	 *           then guess what... create the local json file
	 *
	 * In case not called from the console send the local json
	 * file as output
	 */
	try {

		if(!file_exists(LOCAL_FILE) or localJSONCacheOutdated() or $forceRegenerateFile) {
			createLocalJSONCache();
		}

	} catch (Exception $e) {
		print "Unfortunately the JSON File could not be read or created: \r\n";
		print $e->getMessage()." \r\n";
		print $e->getTraceAsString()." \r\n";
		exit;
	}

	//not called from the console? output the json file directly
	if(!$isCLI) {
		header('Content-Type: application/json');
		header('Content-Length: ' . filesize( LOCAL_FILE ));
		header('Last-Modified: '. gmdate('D, d M Y H:i:s', filemtime( LOCAL_FILE )) . ' GMT');
		readfile( LOCAL_FILE );
		exit;
	}
