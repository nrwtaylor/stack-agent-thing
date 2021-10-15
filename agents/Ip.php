<?php
/**
 * Ip.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

//ini_set('memory_limit', '1024M');

ini_set("allow_url_fopen", 1);

class Ip extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->resource_list = $this->settingsAgent(
            ["ip", "resource_list"],
            []
        );
    }

    public function extractIps($text = null)
    {
        if ($text == null) {
            return true;
        }
        // https://stackoverflow.com/questions/24112843/php-regex-to-return-ip-addresses-from-lines-of-text/24112946
        // A starting place.

        // Only accurate so far. Will match 999.999.999.999.

        $pattern = "/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/";

        preg_match_all($pattern, $text, $match);

        if (!isset($urls)) {
            $urls = [];
        }

        $urls = array_merge($urls, $match[0]);
        $urls = array_unique($urls);
        return $urls;
    }

    public function isIp($text)
    {
        return $this->hasIp($text);
    }

    public function hasIp($text = null)
    {
        $tokens = $this->extractIps($text);

        // No URLS found.
        if ($tokens === true) {
            return false;
        }

        if (count($tokens) >= 1) {
            return true;
        }

        return false;
    }

    public function extractIp($text = null)
    {
        $tokens = $this->extractIps($text);

        // No URLS found.
        if ($tokens === true) {
            return false;
        }
        if (count($tokens) == 1) {
            return $tokens[0];
        }

        return false;
    }

    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function findIp($text = null)
    {
        if ($text == null) {
            return null;
        }

        if ($this->isIp($text)) {
            $selector_array = ["ip" => $text];
        } else {
            $selector_array = ["location" => $text];
        }

        $matches = [];

        foreach ($this->resource_list as $i => $resource_list) {
            $m = $this->matchIp($resource_list, $selector_array);

            $matches = array_merge($m, $matches);
        }

        $this->ips_db = $matches;

        $match_array = $this->searchForText(strtolower($text), $this->ips_db);
        return $match_array;
    }

    public function rangeIps($start_ip, $end_ip, $ip)
    {
        return ip2long($ip) >= ip2long($start_ip) and
            ip2long($ip) <= ip2long($end_ip);
    }

    /**
     *
     * @param unknown $file_name
     * @param unknown $selector_array (optional)
     * @return unknown
     */
    function matchIp($file_name, $selector_array = null)
    {
        $matches = [];
        $iterator = $this->nextIp($file_name, $selector_array);

        foreach ($iterator as $iteration) {
            $matches[] = $iteration;
        }

        return $matches;
    }

    /*
     *
     * @param unknown $text
     * @param unknown $array
     * @return unknown
     */
    function searchForText($text, $array)
    {
        //$text = "commercial broadway";
        $text = strtolower($text);
        $pieces = explode(" ", $text);
        $match = false;
        $match_array = [];
        $num_words = count($pieces);
        foreach ($array as $key => $val) {
            $start_ip = $val["start_ip"];
            $end_ip = $val["end_ip"];

            //$stop_id = strtolower($val["stop_id"]);
            //$stop_code = strtolower($val["stop_code"]);
            $place = $val["place"];
            $jurisdiction = $val["jurisdiction"];
            $subjurisdiction = $val["subjurisdiction"];
            $latitude = $val["latitude"];
            $longitude = $val["longitude"];

            $count = 0;

            foreach ($pieces as $piece) {
                if (preg_match("/\b$piece\b/i", $this->textIp($array))) {
                    $count += 1;
                    $match = true;
                } else {
                    $match = false;
                    continue;
                }

                if ($count == $num_words) {
                    break;
                }
            }

            //            if ($count == $num_words) {

            $match_array[] = [
                "start_ip" => $start_ip,
                "end_ip" => $end_ip,
                "place" => $place,
                "jurisdiction" => $jurisdiction,
                "subjurisdiction" => $subjurisdiction,
                "latitude" => $latitude,
                "longitude" => $longitude,
                "score" => $count,
            ];
            //            }
        }
        return $match_array;
    }

    /**
     *
     */
    function makeSMS()
    {
        $sms = "IP | No response.";

        if (isset($this->message)) {
            $sms = "IP | " . $this->message;
        }

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function parseLine($line, $field_names = null)
    {
        if ($field_names == null) {
            $field_names = $this->field_names;
        }

        $field_values = explode(",", $line);
        $i = 0;
        $arr = [];

        foreach ($field_names as $field_name) {
            if (!isset($field_values[$i])) {
                $field_values[$i] = null;
            }
            $arr[$field_name] = $field_values[$i];
            $i += 1;
        }
        return $arr;
    }

    /**
     *
     * @param unknown $file_name
     * @param unknown $selector_array (optional)
     */
    function nextIp($file_name, $selector_array = null)
    {
        $split_time = $this->thing->elapsed_runtime();

        $file = $GLOBALS["stack_path"] . "resources/ip/" . $file_name;
        $handle = fopen($file, "r");
        $line_number = 0;

        $field_names = [
            0 => "start_ip",
            1 => "end_ip",
            2 => "c",
            3 => "jurisdiction",
            4 => "subjurisdiction",
            5 => "place",
            6 => "latitude",
            7 => "longitude",
        ];

        while (!feof($handle)) {
            $line = trim(fgets($handle));
            $line_number += 1;
            // Get headers
            $line1_header = false;
            if ($line_number == 1 and $line1_header == true) {
                $i = 0;
                $field_names = explode(",", $line);
                foreach ($field_names as $field) {
                    $field_names[$i] = preg_replace(
                        '/[\x00-\x1F\x80-\xFF]/',
                        "",
                        $field
                    );
                    $i += 1;
                }
                continue;
            }

            $arr = $this->parseLine($line, $field_names);

            // If there is no selector array, just return it.
            if ($selector_array == null) {
                yield $arr;
                continue;
            }

            if (array_key_exists(0, $selector_array)) {
            } else {
                $selector_array = [$selector_array];
            }

            // Otherwise see if it matches the selector array.
            $match_count = 0;
            $match = true;

            // Look for all items in the selector_array matching
            if ($selector_array == null) {
                continue;
            }

            foreach ($selector_array as $selector) {
                foreach ($selector as $selector_name => $selector_value) {
                    if ($selector_name == "ip") {
                        $ip = $selector_value;
                        $is_in_range = $this->rangeIps(
                            $arr["start_ip"],
                            $arr["end_ip"],
                            $ip
                        );
                        if ($is_in_range === false) {
                            continue;
                        }
                        yield $arr;
                    }
                }

                if ($selector_name == "location") {
                    $location = $selector_array[0]["location"];

                    $haystack = implode(" ", $arr);

                    if (stripos($haystack, $location) !== false) {
                        yield $arr;
                    }
                }
            }
        }

        fclose($handle);

        $this->thing->log(
            "nextIp took " .
                number_format($this->thing->elapsed_runtime() - $split_time) .
                "ms."
        );
    }

    /**
     *
     */
    function infoIp()
    {
        $this->sms_message = "IP";
        //                      if (count($t) > 1) {$this->sms_message .= "ES";}
        $this->sms_message .= " | ";
        $this->sms_message .= "Query best guess location of IP addresses.";
        $this->sms_message .= "TEXT HELP";

        return;
    }

    /**
     *
     */
    function helpIp()
    {
        $this->sms_message = "IP";
        //                      if (count($t) > 1) {$this->sms_message .= "ES";}
        $this->sms_message .= " | ";
        $this->sms_message .= "TEXT <ip address>";
    }

    /**
     *
     */
    function syntaxIp()
    {
        $this->sms_message = "IP";
        //                      if (count($t) > 1) {$this->sms_message .= "ES";}
        $this->sms_message .= " | ";
        $this->sms_message .= 'Syntax: "192.168.1.1". | ';
        $this->sms_message .= "TEXT HELP";
    }

    public function textIp($ip)
    {
        if (isset($ip[0])) {
            $ip = $ip[0];
        }

        $text =
            $ip["jurisdiction"] .
            " " .
            $ip["subjurisdiction"] .
            " " .
            $ip["place"] .
            " " .
            $ip["latitude"] .
            " " .
            $ip["longitude"];
        return $text;
    }

    public function makeTxt()
    {
        $txt = "";
        if (isset($this->matching_ips)) {
            foreach ($this->matching_ips as $i => $matching_ip) {
                $txt .= $this->textIp($matching_ip) . "\n";
            }
        }

        $this->thing_report["txt"] = $txt;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $keywords = ["ip", "internet", "ipv4", "ipv6", "internet protocol"];

        $input = $this->input;
        $this->ips = $this->extractIps($input);

if (count($this->ips) == 1) {

       // $input = $this->assert($input);
        $arr = $this->findIp($this->ips[0]);
        $this->matching_ips = $arr;


        $arr = $arr[0];
        $this->message =
            $input .
            " " .
            $arr["jurisdiction"] .
            " " .
            $arr["subjurisdiction"] .
            " " .
            $arr["place"] .
            " " .
            $arr["latitude"] .
            " " .
            $arr["longitude"];

return;

}

        $input = $this->assert($input);
        $arr = $this->findIp($input);

        $this->matching_ips = $arr;

        $count = 0;
        if (is_array($arr)) {
            $count = count($arr);
        }
        if ($count > 1) {
            $this->message = $count . " matching IP block(s) seen.";
            return;
        }
        $arr = $arr[0];
        $this->message =
            $input .
            " " .
            $arr["jurisdiction"] .
            " " .
            $arr["subjurisdiction"] .
            " " .
            $arr["place"] .
            " " .
            $arr["latitude"] .
            " " .
            $arr["longitude"];
    }
}
