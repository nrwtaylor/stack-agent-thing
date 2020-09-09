<?php
/**
 * Uuid.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

// Recognizes and handles UUIDS.
// Does not generate them.  That is a Thing function.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Measurement extends Agent
{
    /**
     *
     */
    function init()
    {
        $this->agent_name = "MEASUREMENT";

        $this->stack_state = $this->thing->container['stack']['state'];
        $this->short_name = $this->thing->container['stack']['short_name'];

//        $this->locale = $this->thing->container['stack']['locale'];
        //        $this->created_at =  strtotime($this->thing->thing->created_at);

        // https://english.stackexchange.com/questions/111291/what-is-a-name-for-a-unit-of-measure-and-value
        $this->node_list = array(
            "measurement" => array(
                "measurement",
                "amount",
                "quantity",
                "number"
            )
        );

        //$this->aliases = array("learning"=>array("good job"));

        $this->loadUnits();

        $this->thing_report['help'] =
            "Recognizes text with measurements in it. ";
    }

    public function extractMeasurement($input = null)
    {
        if (!isset($this->measurements)) {
            $this->extractMeasurements($input);
        }

        $this->measurement = "X";
        if (isset($this->measurements[0])) {
            $this->measurement = $this->measurements[0];
        }
        return $this->measurement;
    }

    public function loadUnits()
    {
        // Add in a set of default places
        $file =
            $this->resource_path . 'measurement/units-2.19/definitions.units';
        $contents = file_get_contents($file);

        $handle = fopen($file, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                // process the line read.

                // It's just a list of place names.
                // Common ones.
                $line = trim($line);
                if (substr(trim($line), 0, 1) == "#") {
                    continue;
                }
                if (substr(trim($line), 0, 1) == "!") {
                    continue;
                }
                if (substr(trim($line), 0, 1) == "+") {
                    continue;
                }
                if (substr(trim($line), 0, 5) == "wood_") {
                    continue;
                }
                if (substr(trim($line), 0, 5) == "area_") {
                    continue;
                }

                if (is_numeric(trim($line))) {
                    continue;
                }

                if ($line == "") {
                    continue;
                }
                $tokens = explode(" ", $line);

                $this->units_list[strtolower($tokens[0])] = $tokens[0];

                /*
                // This is where the place index will be called.
                // $place_code = str_pad(RAND(1,99999), 8, " ", STR_PAD_LEFT);
                $place_code = $this->thing->nuuid;

                $this->placecode_list[] = $place_code;
                $this->placename_list[] = $place_name;
                $this->places[] = array("code"=>$place_code, "name"=>$place_name);
*/
            }
            fclose($handle);
        } else {
            // error opening the file.
        }
    }

    function extractMeasurements($input = null)
    {
        $filtered_string = "";
        if (is_array($input)) {
            return true;
        }

        $tokens = explode(' ', $input);
        $measurements = array();
        foreach ($tokens as $i => $token) {
            if ($i == 0) {
                continue;
            }

            $left = trim($tokens[$i - 1]);
            $right = trim($tokens[$i]);

            $measurement = $this->readMeasurement($left);

            if ($measurement != false) {
                $measurements[] = $measurement;
            }

            if (!(is_numeric($left) and ctype_alpha($right))) {
                continue;
            }
            $measurement = $this->readMeasurement($left . " " . $right);

            if ($measurement != false) {
                $measurements[] = $measurement;
            }
        }
if (isset($right)) {
        $measurement = $this->readMeasurement($right);

        if ($measurement != false) {
            $measurements[] = $measurement;
        }
}

        $this->measurements = $measurements;
        return $this->measurements;
    }

    function readMeasurement($text)
    {
        $measurement = null;
        $tokens = explode(" ", $text);
        if (count($tokens) < 1 or count($tokens) > 2) {
            return false;
        }

        if (count($tokens) == 1) {
            $token = trim($text);
            $a = preg_split('/(\d+)/', $token, -1, PREG_SPLIT_DELIM_CAPTURE);

            if (count($a) != 3) {
                return false;
            }

            $left = trim($a[1]);
            $right = trim($a[2]);
        }

        if (count($tokens) == 2) {
            $left = trim($tokens[0]);
            $right = trim($tokens[1]);
        }

        if (isset($this->units_list[strtolower($right)])) {
            $measurement = array("number" => floatval($left), "unit" => $right);
        } elseif (isset($this->units_list[strtolower(substr($right, 1))])) {
            $measurement = array(
                "number" => floatval($left),
                "text" => $right,
                "unit" => substr($right, 1)
            );
        } elseif (isset($this->units_list[strtolower(rtrim($right, 's'))])) {
            $measurement = array(
                "number" => floatval($left),
                "text" => $right,
                "unit" => rtrim($right, 's')
            );
        }

        return $measurement;
    }

    /**
     *
     * @param unknown $text
     * @return unknown
     */
    function hasMeasurement($text)
    {
        $this->extractMeasurements($text);
        if (isset($this->measurements) and count($this->measurements) > 0) {
            return true;
        }
        return false;
    }

    function set()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            array("measurement", "received_at"),
            $this->thing->json->time()
        );
    }

public function filterMeasurements($text) {
$this->extractMeasurement($text);
$filtered_text = $text;

foreach($this->measurements as $i=>$measurement) {

//var_dump($measurement);

$unit_text = $measurement['unit'];
if (isset($measurement['text'])) {$unit_text = $measurement['text'];}

$tokens[0] = $measurement['number'] ." ".$unit_text;
$tokens[1] = $measurement['number'] ."".$unit_text;

foreach($tokens as $j=>$token) {

$filtered_text = str_replace($tokens[$j], " ", $filtered_text);

}


}

$filtered_text = trim(preg_replace('!\s+!', ' ', $filtered_text));


return $filtered_text;
}

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
if ($this->agent_input == "measurement") {return;}

        // Test
        // $this->input = "amount 21.65 54.2 sdfdsaf $21.32 -$543.345345";
        $this->extractMeasurement($this->input);
        if (isset($this->measurement) and $this->measurement != null) {
            $this->response = "Measurement spotted.";
            return;
        }

        $input = $this->input;
        $strip_words = array("measurement");

        foreach ($strip_words as $i => $strip_word) {
            $whatIWant = $input;
            if (
                ($pos = strpos(strtolower($input), $strip_word . " is")) !==
                false
            ) {
                $whatIWant = substr(
                    strtolower($input),
                    $pos + strlen($strip_word . " is")
                );
            } elseif (
                ($pos = strpos(strtolower($input), $strip_word)) !== false
            ) {
                $whatIWant = substr(
                    strtolower($input),
                    $pos + strlen($strip_word)
                );
            }

            $input = $whatIWant;
        }

        $filtered_input = ltrim(strtolower($input), " ");

        return false;
    }

    /**
     *
     */
    function makeResponse()
    {
        if (isset($this->response)) {
            return;
        }
        $this->response = "X";
        if (isset($this->measurements) and count($this->measurements) > 0) {
            $this->response = "";
            foreach ($this->measurements as $index => $measurement) {
                $this->response .= $measurement . " ";
            }
        }
    }

    /**
     *
     */
    function makeSMS()
    {
        $this->sms_message = strtoupper($this->agent_name) . " | ";

        $t = "";
if (isset($this->measurements)) {
        foreach ($this->measurements as $i => $measurement) {
            $unit_text = $measurement['unit'];
            if (isset($measurement['text'])) {
                $unit_text = $measurement['text'];
            }

            $t .= $measurement['number'] . " " . $unit_text . " / ";
        }
        $t = trim($t);
        $this->sms_message .= $t . " ";
}
        $this->sms_message .= $this->response;
        // $this->sms_message .= ' | TEXT CHANNEL';

        $this->thing_report['sms'] = $this->sms_message;
    }

    /**
     *
     */
    function makeChoices()
    {
    }

    /**
     *
     */
    function makeImage()
    {
        $this->image = null;
    }
}
