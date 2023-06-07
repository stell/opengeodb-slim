<?php

/**
 * PHP generation tool for creating simple opengeodb MySQL dump
 *
 * @author     Tom Bohacek <info@b01.de>
 * @copyright  2017 Tom Bohacek
 * @license    The MIT License (MIT)
 * @link       https://github.com/stell/opengeodb-slim
 */

namespace B01\Gen;

class Gen
{
    public $empty_harray;
    public $nonger;
    public $seekfile;
    public $countryfile;
    public $countryfile_imploded;

    public function __construct() {
        ini_set('max_execution_time', 6000);
        $start = microtime(true);
        $this->nonger = true;
        $countries = array('D', 'CH', 'FL', 'A', 'B', 'L');
        //	$countries = array('B', 'CH');

        $this->empty_harray = array(2 => 0, 3 => 0,  4 => 0,  5 => 0,  6 => 0,  7 => 0,  8 => 0,  9 => 0);
        $this->generateLocs($countries);
        $this->generateZips($countries);

        echo number_format((microtime(true) - $start), 2) . " Seconds\n";
    }

    private function getParents($locid, &$ids) {
        // search for locid on linestart and get pos of it
        $pos = strpos($this->countryfile_imploded, "##" . $locid . "\t") + 2;
        // return if nothing found
        if ($pos == 2) {
            return;
        }
        // get a chunk from pos and explode it (2000 should be fine)
        $ex = explode("##", substr($this->countryfile_imploded, $pos, 2000));
        $data = explode("\t", $ex[0]);  // first element is the right one

        $ids[$data[13]] = $locid;   // set level index to locid
        if ($data[13] > 2) {    // continue if hierarchy smaller than land
            $this->getParents($data[14], $ids);
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

    private function generateLocs($countries = array(), $limit = 0)
    {
        echo PHP_EOL;
        echo "Generating locs ..." . PHP_EOL;
        $handle_locations = fopen('locations.csv', "w");

        foreach ($countries as $country) {
            echo "Generating locs for " . $country . "..." . PHP_EOL;
            $row = 0;
            $row_ignored = 0;
            $content = trim(file_get_contents($country . ".tab"));

            $this->countryfile = preg_split('/\n|\r\n?/', $content);
            $totalLines = $limit ? $limit : \count($this->countryfile);
            $this->countryfile_imploded = implode("##", $this->countryfile);

            foreach ($this->countryfile as $csvline) {
                $data = explode("\t", $csvline);
                // explanation: skip if (level >= 10 || empty line || ignore || first line || no level)
                if ($data[13] >= 10 || \count($data) == 0 || @$data[15] == 1 || @$data[0] == '#loc_id' || !$data[13]) {
                    $row_ignored++;
                    $totalLines--;
                    continue;
                }

                $ids = array();
                $loc_id = $data[0];
                $level = $data[13];

                $line = array();
                $line[] = $loc_id;
                //$line[] = $country;   // ISO
                //$line[] = $data[11];  // kz
                $line[] = $country; // kz
                $line[] = $data[3]; // Name
                $line[] = $data[4]; // lat
                $line[] = $data[5]; // lon
                $line[] = $data[13];    // level

                // get hierarchy data
                $ids[$level] =  $loc_id;
                $this->getParents($data[14], $ids);

                $ids = array_reverse($ids, true);
                $new_ids = $this->empty_harray;
                foreach ($ids as $key => $val) {
                    $new_ids[$key] = $val;
                }
                $ids = $new_ids;
                $line = array_merge($line, $ids);
                $line[] = $data[9]; // einw

                if (\count($line) != 15) {
                    echo "Line broken:" . PHP_EOL;
                    print_r($line);
                    continue;
                }
                fwrite($handle_locations, implode('#', $line) . "\n");

                echo "\rFinished: " . round((($row * 100) / $totalLines), 2) . "%\r";
                $row++;
                if ($limit && $row == $limit) {
                    break;
                }
            }
            echo PHP_EOL;
            echo $row . " total" . PHP_EOL;
            echo $row_ignored . " ignored" . PHP_EOL;
        }

        if ($this->nonger) {
            $lines = array();
            $lines[] = array(500001, 'AFG', 'Afghanistan', '', '', 2, 500001, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500002, 'AG', 'Antigua und Barbuda', '', '', 2, 500002, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500003, 'AL', 'Albanien', '', '', 2, 500003, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500004, 'AND', 'Andorra', '', '', 2, 500004, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500005, 'ANG', 'Angola', '', '', 2, 500005, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500006, 'AM', 'Armenien', '', '', 2, 500006, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500007, 'ARU', 'Aruba', '', '', 2, 500007, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500008, 'AUS', 'Australien', '', '', 2, 500008, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500009, 'AUT', 'Palästinensische Autonomiegebiete/Gazastreifen', '', '', 2, 500009, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500010, 'AX', 'Åland', '', '', 2, 500010, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500011, 'AXA', 'Anguilla', '', '', 2, 500011, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500012, 'AZ', 'Aserbaidschan', '', '', 2, 500012, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500014, 'BD', 'Bangladesch', '', '', 2, 500014, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500015, 'BDS', 'Barbados', '', '', 2, 500015, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500016, 'BF', 'Burkina Faso', '', '', 2, 500016, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500017, 'BG', 'Bulgarien', '', '', 2, 500017, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500018, 'BHT', 'Bhutan', '', '', 2, 500018, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500019, 'BIH', 'Bosnien und Herzegowina', '', '', 2, 500019, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500020, 'BJ', 'Benin', '', '', 2, 500020, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500021, 'BOL', 'Bolivien', '', '', 2, 500021, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500022, 'BR', 'Brasilien', '', '', 2, 500022, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500023, 'BRN', 'Bahrain', '', '', 2, 500023, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500024, 'BRU', 'Brunei', '', '', 2, 500024, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500025, 'BS', 'Bahamas', '', '', 2, 500025, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500026, 'BW', 'Botswana', '', '', 2, 500026, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500027, 'BY', 'Weißrussland', '', '', 2, 500027, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500028, 'BZ', 'Belize', '', '', 2, 500028, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500029, 'C', 'Kuba', '', '', 2, 500029, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500030, 'CAM', 'Kamerun', '', '', 2, 500030, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500031, 'CDN', 'Kanada', '', '', 2, 500031, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500032, 'CGO', 'Demokratische Republik Kongo', '', '', 2, 500032, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500034, 'CHN', 'China (Volksrepublik)', '', '', 2, 500034, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500035, 'CI', 'Elfenbeinküste', '', '', 2, 500035, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500036, 'CL', 'Sri Lanka', '', '', 2, 500036, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500037, 'CO', 'Kolumbien', '', '', 2, 500037, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500038, 'COM', 'Komoren', '', '', 2, 500038, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500039, 'CR', 'Costa Rica', '', '', 2, 500039, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500040, 'CV', 'Kap Verde', '', '', 2, 500040, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500041, 'CY', 'Zypern', '', '', 2, 500041, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500042, 'CZ', 'Tschechien', '', '', 2, 500042, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500044, 'DJI', 'Dschibuti', '', '', 2, 500044, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500045, 'DK', 'Dänemark', '', '', 2, 500045, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500046, 'DOM', 'Dominikanische Republik', '', '', 2, 500046, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500047, 'DZ', 'Algerien', '', '', 2, 500047, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500048, 'E', 'Spanien', '', '', 2, 500048, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500049, 'EAK', 'Kenia', '', '', 2, 500049, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500050, 'EAT', 'Tansania', '', '', 2, 500050, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500051, 'EAU', 'Uganda', '', '', 2, 500051, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500052, 'EC', 'Ecuador', '', '', 2, 500052, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500053, 'ER', 'Eritrea', '', '', 2, 500053, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500054, 'ES', 'El Salvador', '', '', 2, 500054, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500055, 'EST', 'Estland', '', '', 2, 500055, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500056, 'ET', 'Ägypten', '', '', 2, 500056, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500057, 'ETH', 'Äthiopien', '', '', 2, 500057, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500058, 'F', 'Frankreich', '', '', 2, 500058, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500059, 'FIN', 'Finnland', '', '', 2, 500059, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500060, 'FJI', 'Fidschi', '', '', 2, 500060, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500062, 'FO', 'Färöer', '', '', 2, 500062, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500063, 'FSM', 'Mikronesien', '', '', 2, 500063, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500064, 'G', 'Gabun', '', '', 2, 500064, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500065, 'GB', 'Vereinigtes Königreich', '', '', 2, 500065, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500066, 'GBA', 'Alderney', '', '', 2, 500066, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500067, 'GBG', 'Guernsey', '', '', 2, 500067, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500068, 'GBJ', 'Jersey', '', '', 2, 500068, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500069, 'GBM', 'Isle of Man', '', '', 2, 500069, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500070, 'GBZ', 'Gibraltar', '', '', 2, 500070, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500071, 'GCA', 'Guatemala', '', '', 2, 500071, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500072, 'GE', 'Georgien', '', '', 2, 500072, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500073, 'GH', 'Ghana', '', '', 2, 500073, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500074, 'GQ', 'Äquatorialguinea', '', '', 2, 500074, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500075, 'GR', 'Griechenland', '', '', 2, 500075, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500076, 'GUB', 'Guinea-Bissau', '', '', 2, 500076, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500077, 'GUI', 'Guinea', '', '', 2, 500077, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500078, 'GUY', 'Guyana', '', '', 2, 500078, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500079, 'H', 'Ungarn', '', '', 2, 500079, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500080, 'HK', 'Hongkong', '', '', 2, 500080, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500081, 'HN', 'Honduras', '', '', 2, 500081, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500082, 'HR', 'Kroatien', '', '', 2, 500082, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500083, 'I', 'Italien', '', '', 2, 500083, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500084, 'IL', 'Israel', '', '', 2, 500084, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500085, 'IND', 'Indien', '', '', 2, 500085, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500086, 'IR', 'Iran', '', '', 2, 500086, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500087, 'IRL', 'Irland', '', '', 2, 500087, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500088, 'IRQ', 'Irak', '', '', 2, 500088, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500089, 'IS', 'Island', '', '', 2, 500089, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500090, 'J', 'Japan', '', '', 2, 500090, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500091, 'JA', 'Jamaika', '', '', 2, 500091, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500092, 'JOR', 'Jordanien', '', '', 2, 500092, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500093, 'K', 'Kambodscha', '', '', 2, 500093, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500094, 'KAN', 'St. Kitts und Nevis', '', '', 2, 500094, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500095, 'KG', 'Kirgisistan', '', '', 2, 500095, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500096, 'KIR', 'Kiribati', '', '', 2, 500096, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500097, 'KN', 'Grönland', '', '', 2, 500097, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500098, 'KP', 'Nordkorea', '', '', 2, 500098, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500099, 'KSA', 'Saudi-Arabien', '', '', 2, 500099, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500100, 'KWT', 'Kuwait', '', '', 2, 500100, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500101, 'KZ', 'Kasachstan', '', '', 2, 500101, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500103, 'LAO', 'Laos', '', '', 2, 500103, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500104, 'LAR', 'Libyen', '', '', 2, 500104, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500105, 'LB', 'Liberia', '', '', 2, 500105, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500106, 'LS', 'Lesotho', '', '', 2, 500106, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500107, 'LT', 'Litauen', '', '', 2, 500107, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500108, 'LV', 'Lettland', '', '', 2, 500108, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500109, 'M', 'Malta', '', '', 2, 500109, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500110, 'MA', 'Marokko', '', '', 2, 500110, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500111, 'MAL', 'Malaysia', '', '', 2, 500111, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500112, 'MC', 'Monaco', '', '', 2, 500112, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500113, 'MD', 'Moldawien', '', '', 2, 500113, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500114, 'MEX', 'Mexiko', '', '', 2, 500114, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500115, 'MGL', 'Mongolei', '', '', 2, 500115, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500116, 'MH', 'Marshallinseln', '', '', 2, 500116, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500117, 'MK', 'Mazedonien', '', '', 2, 500117, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500118, 'MNE', 'Montenegro', '', '', 2, 500118, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500119, 'MOC', 'Mosambik', '', '', 2, 500119, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500120, 'MS', 'Mauritius', '', '', 2, 500120, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500121, 'MV', 'Malediven', '', '', 2, 500121, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500122, 'MW', 'Malawi', '', '', 2, 500122, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500123, 'MYA', 'Myanmar', '', '', 2, 500123, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500124, 'N', 'Norwegen', '', '', 2, 500124, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500125, 'NA', 'Niederländische Antillen', '', '', 2, 500125, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500126, 'NAM', 'Namibia', '', '', 2, 500126, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500127, 'NAU', 'Nauru', '', '', 2, 500127, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500128, 'NCL', 'Neukaledonien', '', '', 2, 500128, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500129, 'NEP', 'Nepal', '', '', 2, 500129, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500130, 'NGR', 'Nigeria', '', '', 2, 500130, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500131, 'NI', 'Nordirland', '', '', 2, 500131, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500132, 'NIC', 'Nicaragua', '', '', 2, 500132, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500133, 'NL', 'Niederlande', '', '', 2, 500133, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500134, 'NZ', 'Neuseeland', '', '', 2, 500134, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500135, 'OM', 'Oman', '', '', 2, 500135, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500136, 'P', 'Portugal', '', '', 2, 500136, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500137, 'PA', 'Panama', '', '', 2, 500137, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500138, 'PAL', 'Palau', '', '', 2, 500138, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500139, 'PE', 'Peru', '', '', 2, 500139, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500140, 'PK', 'Pakistan', '', '', 2, 500140, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500141, 'PL', 'Polen', '', '', 2, 500141, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500142, 'PMR', 'Transnistrien', '', '', 2, 500142, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500143, 'PNG', 'Papua-Neuguinea', '', '', 2, 500143, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500144, 'PRI', 'Puerto Rico', '', '', 2, 500144, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500145, 'PY', 'Paraguay', '', '', 2, 500145, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500146, 'Q', 'Katar', '', '', 2, 500146, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500147, 'RA', 'Argentinien', '', '', 2, 500147, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500148, 'RB', 'Botswana', '', '', 2, 500148, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500149, 'RC', 'Republik China (Taiwan)', '', '', 2, 500149, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500150, 'RCA', 'Zentralafrikanische Republik', '', '', 2, 500150, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500151, 'RCB', 'Republik Kongo', '', '', 2, 500151, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500152, 'RCH', 'Chile', '', '', 2, 500152, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500153, 'RG', 'Guinea', '', '', 2, 500153, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500154, 'RH', 'Haiti', '', '', 2, 500154, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500155, 'RI', 'Indonesien', '', '', 2, 500155, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500156, 'RIM', 'Mauretanien', '', '', 2, 500156, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500157, 'RKS', 'Kosovo', '', '', 2, 500157, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500158, 'RL', 'Libanon', '', '', 2, 500158, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500159, 'RM', 'Madagaskar', '', '', 2, 500159, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500160, 'RMM', 'Mali', '', '', 2, 500160, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500161, 'RN', 'Niger', '', '', 2, 500161, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500162, 'RO', 'Rumänien', '', '', 2, 500162, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500163, 'ROK', 'Südkorea', '', '', 2, 500163, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500164, 'RUM', 'Rumänien', '', '', 2, 500164, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500165, 'ROU', 'Uruguay', '', '', 2, 500165, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500166, 'RP', 'Philippinen', '', '', 2, 500166, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500167, 'RSM', 'San Marino', '', '', 2, 500167, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500168, 'RT', 'Togo', '', '', 2, 500168, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500169, 'RU', 'Burundi', '', '', 2, 500169, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500170, 'RUS', 'Russland', '', '', 2, 500170, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500171, 'RWA', 'Ruanda', '', '', 2, 500171, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500172, 'S', 'Schweden', '', '', 2, 500172, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500173, 'SD', 'Swasiland', '', '', 2, 500173, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500174, 'SGP', 'Singapur', '', '', 2, 500174, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500175, 'SK', 'Slowakei', '', '', 2, 500175, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500176, 'SLE', 'Sierra Leone', '', '', 2, 500176, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500177, 'SLO', 'Slowenien', '', '', 2, 500177, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500178, 'SME', 'Suriname', '', '', 2, 500178, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500180, 'SN', 'Senegal', '', '', 2, 500180, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500181, 'SO', 'Somalia', '', '', 2, 500181, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500182, 'SOL', 'Salomonen', '', '', 2, 500182, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500183, 'SRB', 'Serbien', '', '', 2, 500183, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500184, 'SSD', 'Südsudan', '', '', 2, 500184, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500185, 'STP', 'São Tomé und Príncipe', '', '', 2, 500185, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500186, 'SUD', 'Sudan', '', '', 2, 500186, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500187, 'SY', 'Seychellen', '', '', 2, 500187, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500188, 'SYR', 'Syrien', '', '', 2, 500188, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500189, 'T', 'Thailand', '', '', 2, 500189, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500190, 'TD', 'Tschad', '', '', 2, 500190, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500191, 'TG', 'Togo', '', '', 2, 500191, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500192, 'TJ', 'Tadschikistan', '', '', 2, 500192, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500193, 'TL', 'Osttimor', '', '', 2, 500193, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500194, 'TM', 'Turkmenistan', '', '', 2, 500194, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500195, 'TN', 'Tunesien', '', '', 2, 500195, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500196, 'TON', 'Tonga', '', '', 2, 500196, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500197, 'TR', 'Türkei', '', '', 2, 500197, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500198, 'TT', 'Trinidad und Tobago', '', '', 2, 500198, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500199, 'TUV', 'Tuvalu', '', '', 2, 500199, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500200, 'UA', 'Ukraine', '', '', 2, 500200, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500201, 'UAE', 'Vereinigte Arabische Emirate', '', '', 2, 500201, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500202, 'USA', 'Vereinigte Staaten von Amerika', '', '', 2, 500202, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500203, 'UZ', 'Usbekistan', '', '', 2, 500203, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500204, 'V', 'Vatikanstaat', '', '', 2, 500204, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500205, 'VAN', 'Vanuatu', '', '', 2, 500205, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500206, 'VG', 'Britische Jungferninseln', '', '', 2, 500206, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500207, 'VN', 'Vietnam', '', '', 2, 500207, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500208, 'WAG', 'Gambia', '', '', 2, 500208, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500209, 'WAL', 'Sierra Leone', '', '', 2, 500209, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500210, 'WB', 'Westjordanland', '', '', 2, 500210, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500211, 'WD', 'Dominica', '', '', 2, 500211, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500212, 'WG', 'Grenada', '', '', 2, 500212, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500213, 'WL', 'St. Lucia', '', '', 2, 500213, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500214, 'WS', 'Samoa', '', '', 2, 500214, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500215, 'WSA', 'Demokratische Arabische Republik Sahara', '', '', 2, 500215, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500216, 'WV', 'St. Vincent und die Grenadinen', '', '', 2, 500216, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500217, 'YEM', 'Jemen', '', '', 2, 500217, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500218, 'YV', 'Venezuela', '', '', 2, 500218, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500219, 'Z', 'Sambia', '', '', 2, 500219, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500220, 'ZA', 'Südafrika', '', '', 2, 500220, 0, 0, 0, 0, 0, 0, 0, 0);
            $lines[] = array(500221, 'ZW', 'Simbabwe', '', '', 2, 500221, 0, 0, 0, 0, 0, 0, 0, 0);

            foreach ($lines as $line) {
                fwrite($handle_locations, implode('#', $line) . "\n");
            }
            echo PHP_EOL;
            echo "Created locs for worldwide" . PHP_EOL;
        }
        fclose($handle_locations);
    }

    private function generateZips($countries = array(), $limit = 0)
    {
        echo PHP_EOL;
        echo "Generating zips ..." . PHP_EOL;
        $handle_zips = fopen('zips.csv', "w");

        foreach ($countries as $country) {
            echo "Generating zips for " . $country . "..." . PHP_EOL;
            $row = 0;
            $row_ignored = 0;
            $row_nozip = 0;

            $content = trim(file_get_contents($country . ".tab"));
            $this->countryfile = preg_split('/\n|\r\n?/', $content);
            $totalLines = $limit ? $limit : \count($this->countryfile);
            $this->countryfile_imploded = implode("##", $this->countryfile);

            foreach ($this->countryfile as $csvline) {
                $data = explode("\t", $csvline);
                // explanation: skip if (level >= 10 || empty line || ignore || first line || no level)
                if ($data[13] >= 10 || \count($data) == 0 || @$data[15] == 1 || @$data[0] == '#loc_id' || !$data[13]) {
                    $row_ignored++;
                    $totalLines--;
                    continue;
                }
                if ($data[7] == "") {
                    $row_nozip++;
                    $totalLines--;
                    continue;
                }
                $ids = array();
                $zips = explode(",", $data[7]);
                $zipcount = \count($zips);

                $loc_id = $data[0];
                $level = $data[13];

                $line = array();
                $line[] = $loc_id;
                //$line[] = $country;   // ISO
                //$line[] = $data[11];  // kz
                $line[] = $country; // kz
                //$line[] = sprintf('%05d', $zips[0]) ; // PLZ
                $line[] = $zips[0] ;    // PLZ
                $line[] = $data[3]; // Name
                $line[] = $data[4]; // lat
                $line[] = $data[5]; // lon
                $line[] = $data[13];    // level

                $ids[$level] =  $loc_id;

                $this->getParents($data[14], $ids);

                $ids = array_reverse($ids, true);
                $new_ids = $this->empty_harray;
                foreach ($ids as $key => $val) {
                    $new_ids[$key] = $val;
                }
                $ids = $new_ids;
                $line = array_merge($line, $ids);

                if (\count($line) != 15) {
                    echo "Line broken:" . PHP_EOL;
                    print_r($line);
                    continue;
                }
                fwrite($handle_zips, implode('#', $line) . "\n");
                $row++;
                echo "\rFinished: " . round((($row * 100) / $totalLines), 2) . "%\r";
                if ($zipcount > 1) {
                    array_shift($zips); // remove first
                    foreach ($zips as $zip) {
                        // $line[2] =  sprintf('%05d', $zip) ;
                        $line[2] = $zip ;
                        fwrite($handle_zips, implode('#', $line) . "\n");
                        $row++;
                        $totalLines++;
                    }
                }
                if ($limit && $row == $limit) {
                    break;
                }
            }
            echo PHP_EOL;
            echo $row . " total" . PHP_EOL;
            echo $row_ignored . " ignored" . PHP_EOL;
            echo $row_nozip . " no zip" . PHP_EOL;
        }
        fclose($handle_zips);
    }
}
new Gen();

/*

// Landkreise in D
SELECT * FROM `q3m1f_prime_geo_locations` WHERE `level` = 5 AND loc_kfz = 'D'
oder
SELECT * FROM `q3m1f_prime_geo_locations` WHERE `level` = 5 AND hier2 = 105



// alle PLZ in Berlin
SELECT loc_plz  FROM `q3m1f_prime_geo_zips` WHERE `loc_name` LIKE 'Berlin'
oder
SELECT loc_plz  FROM `q3m1f_prime_geo_zips` WHERE `loc_id` = 14356



// alle PLZ rund um Berlin 50 km
SELECT loc_plz FROM `q3m1f_prime_geo_zips` WHERE (
ACOS(SIN(PI() * 52.520008 / 180.0) * SIN(PI() * loc_lat / 180.0)
+ COS(PI() * 52.520008/180.0) * COS(PI() * loc_lat / 180.0)
* COS(PI() * loc_lon / 180.0 - PI() * 13.404954 / 180.0)) * 6371 )
< 50;



// alle Orte (level 2) rund um Passau (48.566736, 13.431947) Radius 20 km
SELECT loc_name FROM `q3m1f_prime_geo_locations` WHERE (
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

FROM `q3m1f_prime_geo_locations` AS l7
LEFT JOIN q3m1f_prime_geo_locations AS l6 ON l7.hier6=l6.loc_id
LEFT JOIN q3m1f_prime_geo_locations AS l5 ON l6.hier5=l5.loc_id
LEFT JOIN q3m1f_prime_geo_locations AS l4 ON l5.hier4=l4.loc_id
LEFT JOIN q3m1f_prime_geo_locations AS l3 ON l4.hier3=l3.loc_id
LEFT JOIN q3m1f_prime_geo_locations AS l2 ON l3.hier2=l2.loc_id
WHERE l7.loc_id = 132446

*/
