<?php
/**
 * PHP generation tool for creating simple opengeodb MySQL dump
 *
 * @author     Tom Bohacek <info@b01.de>
 * @copyright  2017 Tom Bohacek
 * @license    The MIT License (MIT)
 * @link       https://github.com/stell/opengeodb-slim
 */

class Gen {

	public $empty_harray;
	public $seekfile;
	public $countryfile;
	public $countryfile_imploded;

	function __construct() {
		ini_set('max_execution_time', 6000);
		$start = microtime(true);
		$countries = array('D', 'CH', 'FL', 'A', 'B', 'L');

		$this->empty_harray = array(2 => 0, 3 => 0,  4 => 0,  5 => 0,  6 => 0,  7 => 0,  8 => 0,  9 => 0);
		$this->generateLocs($countries);
		$this->generateZips($countries);

		echo number_format(( microtime(true) - $start), 2) . " Seconds\n";
   }

   function getParents($locid, &$ids)   {

   		$pos = strpos($this->countryfile_imploded, "##".$locid."\t")+2;	// search for locid on linestart and get pos of it
   		if($pos == 2) return;	// return if nothing found
   		$ex = explode("##", substr($this->countryfile_imploded, $pos, 2000));	// get a chunk from pos and explode it (2000 should be fine)
   		$data = explode("\t", $ex[0]);	// first element is the right one

 		$ids[$data[13]] = $locid;	// set level index to locid
 		if ($data[13] > 2) {	// continue if hierarchy smaller than land
 			$this->getParents( $data[14], $ids);
 		}
	 	return;

   		/*
   		// older and slower version
   		foreach($this->countryfile as $csvline) {
	        if (stripos($csvline, (string)$locid) === 0)
        	{
        		$data = explode("\t", $csvline);
				$num = count($data);
		 		$ids[$data[$num-3]] = $locid;
		 		if ($data[$num-3] > 2) {
		 			$this->getParents( $data[$num-2], $ids);
		 			//break;
		 		}
			 	return;
        	}
	    }
	    */
	}


	function generateLocs($countries = array(), $limit = 0) {

		foreach ($countries as $country) {

			echo PHP_EOL;
			echo "Generating locs for ". $country."...". PHP_EOL;
			$handle_locations = fopen($country.'_locations.csv', "w");

			$row = 0;
			$row_ignored = 0;

			$content = trim(file_get_contents($country.".tab"));

			$this->countryfile = preg_split('/\n|\r\n?/', $content);
			$totalLines = $limit ? $limit : count($this->countryfile);
			$this->countryfile_imploded = implode("##", $this->countryfile);

		    foreach ($this->countryfile as $csvline) {

		    	$data = explode("\t", $csvline);

		    	// explanation: skip if (level >= 10 || empty line || ignore || first line || no level)
		        if($data[13] >= 10 || count($data) == 0 || @$data[15] == 1 || @$data[0] == '#loc_id' || !$data[13]) {
		        	$row_ignored++;
		        	$totalLines--;
		        	continue;
		        }

		    	$ids = array();

		        $loc_id = $data[0];
		        $level = $data[13];

				$line = array();
				$line[] = $loc_id;
				//$line[] = $country;	// ISO
				//$line[] = $data[11];	// kz
				$line[] = $country;	// kz
				$line[] = $data[3];	// Name
				$line[] = $data[4];	// lat
				$line[] = $data[5];	// lon
				$line[] = $data[13];	// level

				// get hierarchy data
				$ids[$level] =  $loc_id;
				$this->getParents($data[14], $ids);

				$ids = array_reverse($ids, true);
				$new_ids = $this->empty_harray;
				foreach($ids as $key => $val) {
				   $new_ids[$key] = $val;
				}
				$ids = $new_ids;
				$line = array_merge($line, $ids);

				$line[] = $data[9];	// einw

		   		if(count($line) != 15) {
		   			echo "Line broken:". PHP_EOL;
		   			print_r($line);
		   			continue;
		   		}
		        fwrite($handle_locations, implode($line, '#')."\n");

		        echo "\rFinished: ".round((($row*100) / $totalLines), 2). "%\r";
		        $row++;
		       	if($limit && $row == $limit) break;
		    }
		    fclose($handle_locations);

			echo PHP_EOL;
			echo $row . " total". PHP_EOL;
			echo $row_ignored . " ignored". PHP_EOL;
		}
	}


	function generateZips($countries = array(), $limit = 0) {
		foreach ($countries as $country) {

			echo PHP_EOL;
			echo "Generating zips for ". $country."...". PHP_EOL;
			$handle_zips = fopen($country.'_zips.csv', "w");

			$row = 0;
			$row_ignored = 0;
			$row_nozip = 0;

			$content = trim(file_get_contents($country.".tab"));
			$this->countryfile = preg_split('/\n|\r\n?/', $content);
			$totalLines = $limit ? $limit : count($this->countryfile);
			$this->countryfile_imploded = implode("##", $this->countryfile);

			foreach ($this->countryfile as $csvline) {
			 	$data = explode("\t", $csvline);
		    	// explanation: skip if (level >= 10 || empty line || ignore || first line || no level)
		        if($data[13] >= 10 || count($data) == 0 || @$data[15] == 1 || @$data[0] == '#loc_id' || !$data[13]) {
		        	$row_ignored++;
		        	$totalLines--;
		        	continue;
		        }
		        if($data[7] == "") {
		        	$row_nozip++;
		        	$totalLines--;
		        	continue;
		        }

		        $ids = array();

		        $zips = explode(",", $data[7]);
		        $zipcount = count($zips);

		        $loc_id = $data[0];
		        $level = $data[13];

				$line = array();
				$line[] = $loc_id;
				//$line[] = $country;	// ISO
				//$line[] = $data[11];	// kz
				$line[] = $country;	// kz
				$line[] = sprintf('%05d', $zips[0]) ;	// PLZ
				$line[] = $data[3];	// Name
				$line[] = $data[4];	// lat
				$line[] = $data[5];	// lon
				$line[] = $data[13];	// level

				$ids[$level] =  $loc_id;

				$this->getParents($data[14], $ids);

				$ids = array_reverse($ids, true);
				$new_ids = $this->empty_harray;
				foreach($ids as $key => $val) {
				   $new_ids[$key] = $val;
				}
				$ids = $new_ids;
				$line = array_merge($line, $ids);

		   		if(count($line) != 15) {
		   			echo "Line broken:". PHP_EOL;
		   			print_r($line);
		   			continue;
		   		}
		        fwrite($handle_zips, implode($line, '#')."\n");
		        $row++;

				echo "\rFinished: ".round((($row*100) / $totalLines), 2). "%\r";

		        if($zipcount > 1) {
		        	array_shift($zips);	// remove first
			        foreach ($zips as $zip) {
			        	$line[2] =  sprintf('%05d', $zip) ;
			        	fwrite($handle_zips, implode($line, '#')."\n");
			        	 $row++;
			        	 $totalLines++;
			        }
		        }
		        if($limit && $row == $limit) break;
		    }
		    fclose($handle_zips);

			echo PHP_EOL;
			echo $row . " total". PHP_EOL;
			echo $row_ignored . " ignored". PHP_EOL;
			echo $row_nozip . " no zip". PHP_EOL;
		}
	}
}
new Gen();


/*

// Landkreise in D
SELECT * FROM `geo_locations` WHERE `level` = 5 AND loc_kfz = 'D'
oder
SELECT * FROM `geo_locations` WHERE `level` = 5 AND hier2 = 105



// alle PLZ in Berlin
SELECT loc_plz  FROM `geo_zips` WHERE `loc_name` LIKE 'Berlin'
oder
SELECT loc_plz  FROM `geo_zips` WHERE `loc_id` = 14356



// alle PLZ rund um Berlin 50 km
SELECT loc_plz FROM `geo_zips` WHERE (
ACOS(SIN(PI() * 52.520008 / 180.0) * SIN(PI() * loc_lat / 180.0)
+ COS(PI() * 52.520008/180.0) * COS(PI() * loc_lat / 180.0)
* COS(PI() * loc_lon / 180.0 - PI() * 13.404954 / 180.0)) * 6371 )
< 50;



// alle Orte (level 2) rund um Passau (48.566736, 13.431947) Radius 20 km
SELECT loc_name FROM `geo_locations` WHERE (
ACOS(SIN(PI() * 48.566736 / 180.0) * SIN(PI() * loc_lat / 180.0)
+ COS(PI() * 48.566736/180.0) * COS(PI() * loc_lat / 180.0)
* COS(PI() * loc_lon / 180.0 - PI() * 13.431947 / 180.0)) * 6371 )
< 20 AND level = 7;



// Geo-Hierarchien von "Sinzendorf" (locid = 132446)
SELECT l2.loc_name AS land,
l3.loc_name as bundesland,
l4.loc_name as bezirk,
l5.loc_name as landkreis,
l6.loc_name as gemeinde,
l7.loc_name as ortschaft

FROM `geo_locations` AS l7
LEFT JOIN geo_locations AS l6 ON l7.hier6=l6.loc_id
LEFT JOIN geo_locations AS l5 ON l6.hier5=l5.loc_id
LEFT JOIN geo_locations AS l4 ON l5.hier4=l4.loc_id
LEFT JOIN geo_locations AS l3 ON l4.hier3=l3.loc_id
LEFT JOIN geo_locations AS l2 ON l3.hier2=l2.loc_id
WHERE l7.loc_id = 132446

*/