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

// devstack read the frequency list.

class Frequency extends Agent
{
    /**
     *
     */
    function init()
    {
        $this->agent_name = "FREQUENCY";
        //$this->multiplier = "MHz";

        $this->stack_state = $this->thing->container['stack']['state'];
        $this->short_name = $this->thing->container['stack']['short_name'];

        $this->thing->log(
            'started running on Thing ' . date("Y-m-d H:i:s") . ''
        );

        $this->node_list = ["frequency" => ["frequency", "snowflake"]];

        $this->pattern = '|\b[0-9]{1,3}[" "]?[.]?[0-9]{1,4}\b|';

        $this->aliases = ["learning" => ["good job"]];

        $data_source = $this->resource_path . "ised/frequencies.url";

        $this->thing_report['help'] = "Recognizes frequencies.";
    }

    /**
     *
     */
    function makeTable()
    {
        if (!isset($this->channel['vector'])) {
            $this->getFrequencies();
        }

        if (!isset($this->channel['vector'])) {
            return;
        }

        $data = $this->channel['vector'];

        $t = explode("\n", $data);
        $flag = false;
        $band = [];
        foreach ($t as $i => $line) {
            if (
                strpos(
                    $line,
                    'Canadian Table of Frequency Allocations — kHz'
                ) !== false
            ) {
                $multiplier = "kHz";
            }

            if (
                strpos(
                    $line,
                    'Canadian Table of Frequency Allocations — MHz'
                ) !== false
            ) {
                $multiplier = "MHz";
            }

            if (
                strpos(
                    $line,
                    'Canadian Table of Frequency Allocations — GHz'
                ) !== false
            ) {
                $multiplier = "GHz";
            }

            //if (strpos($line, 'International footnotes') !== false) {
            //    return;
            //}

            // Override pattern to specifically look for the frequency range.
            $pattern =
                '|[0-9]{1,3}[" "]?[.]?[0-9]{1,4} - [0-9]{1,3}[" "]?[.]?[0-9]{1,4}|';

            preg_match_all($pattern, $line, $m);
            if (isset($m[0][0])) {
                // Write the band information. Of the prior set of lines.
                if (isset($frequency_range)) {
                    $band[] = [
                        "range" => $frequency_range,
                        "start" => $start,
                        "end" => $end,
                        "multiplier" => $multiplier,
                        "lines" => $lines,
                    ];
                }
                $count = 0;

                $frequency_range = trim($m[0][0]);
                $lines = [];

                //$flag = true;

                $a = explode("-", $frequency_range);
                $start = (float) str_replace(" ", "", $a[0]);
                $end = (float) $a[1];

                $flag = true;
            }

            if ($flag) {
                if ($count != 0 and trim(strip_tags($line)) != "") {
                    if (trim(strip_tags($line)) == "International footnotes") {
                        $flag = false;
                        break;
                    }

                    $lines[] = trim(strip_tags($line));
                }
                $count += 1;
            }
        }
        $this->band = $band;
    }

    /**
     *
     */
    function printFrequencies()
    {
        $t = "";
        foreach ($this->band as $i => $frequency) {
            $text = implode(" / ", $frequency["lines"]);

            $text = str_replace("end primary service", "", $text);
            $text = str_replace("primary service", "", $text);

            $t .=
                $frequency["range"] .
                " " .
                $frequency["multiplier"] .
                " " .
                $text .
                "\n";
        }
        echo $t;
    }

    /**
     *
     * @param unknown $frequency
     * @return unknown
     */
    function frequencyString($frequency)
    {
        $text = implode(" / ", $frequency["lines"]);
        $text = str_replace("end primary service", "", $text);
        $text = str_replace("primary service", "", $text);

        $text =
            $frequency["range"] . " " . $frequency["multiplier"] . " " . $text;

        return $text;
    }

    /**
     *
     * @param unknown $text
     */
    function findFrequency($text)
    {
        $matches = [];

        if (!isset($this->band)) {
            $this->makeTable();
        }

        // Still not set?
        if (!isset($this->band)) {
            return;
        }

        $search_frequency = (float) $text;

        foreach ($this->band as $i => $band) {
            if (
                isset($this->multiplier) and
                $band['multiplier'] != $this->multiplier
            ) {
                continue;
            }

            if (
                $search_frequency >= $band['start'] and
                $search_frequency <= $band['end']
            ) {
                $matches[] = $band;
            }
        }

        $this->band_matches = $matches;
    }

    /**
     *
     */
    function run()
    {
        $this->getFrequencies();
        $this->makeTable();
        $this->makeResponse();
        $this->makeSMS();
    }

    /**
     *
     * @param unknown $text (optional)
     */
    function doFrequency($text = null)
    {
        $text = trim($text);

        $channel_text = "Not recognized.";
        $this->response = $channel_text;
        $this->message = $this->response;
    }

    /**
     *
     */
    function getFrequencies()
    {
        $data_source = $this->resource_path . "ised/frequencies.txt";

        if (!file_exists($data_source)) {
            return;
        }

        $file_flag = true;

        $data = @file_get_contents($data_source);
        if ($data === false) {
            $file_flag = false;
            $this->thing->log(
                "Data source " . $data_source . " not accessible."
            );

            // Handle quietly.
            if (!isset($this->link)) {
                $this->link = null;
            }
            $data_source = trim($this->link);

            $data = @file_get_contents($data_source);
            if ($data === false) {
                $this->thing->log(
                    "Data source " . $data_source . " not accessible."
                );
                // Handle quietly.
                return;
            }

            $data_target = $this->resource_path . "ised/frequencies.txt";

            try {
                if ($file_flag === false) {
                    @file_put_contents($data_target, $data, LOCK_EX);

                    //                    @file_put_contents($data_target, $data, FILE_APPEND | LOCK_EX);
                    $this->thing->log(
                        "Data source " . $data_source . " created."
                    );
                }
            } catch (Exception $e) {
                // Handle quietly.
            }
        }
        $this->data = $data;
        $this->channel['vector'] = $data;
    }

    /**
     *
     * @param unknown $text
     * @return unknown
     */
    function hasFrequency($text)
    {
        $this->extractFrequencies($text);
        if (isset($this->frequencies) and count($this->frequencies) > 0) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractFrequencies($input)
    {
        if (!isset($this->frequencies)) {
            $this->frequencies = [];
        }
        //$pattern = '|[0-9]{3}[\.][0-9]{3}|';

        $pattern = $this->pattern;
        preg_match_all($pattern, $input, $m);

        $arr = $m[0];
        //array_pop($arr);
        $this->frequencies = $arr;
        return $arr;
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractFrequency($input)
    {
        $frequencies = $this->extractFrequencies($input);
        if (!is_array($frequencies)) {
            return true;
        }

        if (is_array($frequencies) and count($frequencies) == 1) {
            $this->frequency = $frequencies[0];
            $this->thing->log(
                'found a frequency (' . $this->frequency . ') in the text.'
            );
            return $this->frequency;
        }

        if (is_array($frequencies) and count($frequencies) == 0) {
            return false;
        }
        if (is_array($frequencies) and count($frequencies) > 1) {
            return true;
        }

        return true;
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractMultiplier($input)
    {
        $multipliers = ["kHz", "MHz", "GHz"];

        //        $frequencies = $this->extractFrequencies($input);
        //        if (!(is_array($frequencies))) {return true;}

        $flag = false;
        foreach ($multipliers as $multiplier) {
            if (stripos($input, $multiplier) !== false) {
                $matches[] = $multiplier;
                echo 'true';
            }
        }

        if (isset($matches) and count($matches) == 1) {
            $this->multiplier = $matches[0];
            return;
        }

        return true;
    }

    /**
     * function makeWeb() {
     * $link = $this->web_prefix . 'thing/' . $this->uuid . '/frequency';
     * $this->node_list = array("frequency"=>array("frequency", "snowflake"));
     * // Make buttons
     * $this->thing->choice->Create($this->agent_name, $this->node_list, "frequency");
     * $choices = $this->thing->choice->makeLinks('frequency');
     * $alt_text = "a QR code with a frequency";
     * $web = '<a href="' . $link . '">';
     * //$web_prefix = "http://localhost:8080/";
     * $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/frequency.png" jpg"
     * width="100" height="100"
     * alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/frequency.txt">';
     * $web .= "</a>";
     * $web .= "<br>";
     * //$received_at = strtotime($this->thing->thing->created_at);
     * //$ago = $this->thing->human_time ( $this->created_at );
     * //$web .= "Created about ". $ago . " ago.";
     * //$web.= "<b>UUID Agent</b><br>";
     * //$web.= "uuid is " . $this->uuid. "<br>";
     * $web.= "CREATED AT " . strtoupper(date('Y M d D H:m', $this->created_at)). "<br>";
     * $web .= "<br>";
     * $this->thing_report['web'] = $web;
     * }
     */
    function set()
    {
        //        $this->thing->json->setField("settings");
        //        $this->thing->json->writeVariable(array("frequency",
        //                "received_at"),  $this->thing->json->time()
        //        );

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["frequency", "refreshed_at"],
            $this->thing->json->time()
        );
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->extractFrequency($this->input);

        $this->extractMultiplier($this->input);

        if (isset($this->frequency) and $this->frequency != null) {
            $this->response = "Frequency spotted.";
            $this->findFrequency($this->frequency);

            $t = "No band matches. ";
            if (isset($this->band_matches)) {
                $t = "";
                foreach ($this->band_matches as $i => $band) {
                    $t .= $this->frequencyString($band) . " / ";
                }
            }

            if (strpos(strtolower($t), "amateur")) {
                $ars_thing = new Amateurradioservice($this->thing);
                $this->thing_report = $ars_thing->thing_report;
                $this->agent_name = $ars_thing->agent_name;
                $this->response = $ars_thing->response;

                return;
            }

            $this->response = $t;

            return;
        }

        $input = $this->input;
        $strip_words = ["frequency"];

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

        $this->doFrequency($filtered_input);

        $this->response = "Read frequency. ";
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
        if (isset($this->frequencies) and count($this->frequencies) > 0) {
            $this->response = "";
            foreach ($this->frequencies as $index => $frequency) {
                $this->response .= $frequency . " ";
            }
        }
    }

    /**
     *
     */
    function makeSMS()
    {
        $this->sms_message = strtoupper($this->agent_name) . " | ";
        $this->sms_message .= $this->response;
        $this->sms_message .= ' | TEXT CHANNEL';

        $this->thing_report['sms'] = $this->sms_message;
    }

    /**
     *
     */
    function makeChoices()
    {
        $this->thing->choice->Create(
            "frequency",
            $this->node_list,
            "frequency"
        );

        $choices = $this->thing->choice->makeLinks("frequency");
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }

    /**
     *
     */
    function makeImage()
    {
        $this->image = null;
    }
}
