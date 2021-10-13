<?php
/**
 * Wumpus.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Hypocorism extends Agent
{
    public $var = 'hello';

    /**
     *
     */
    function init()
    {
        $this->agent_name = "hypocorism";
        $this->test = "Development code";

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->node_list = ["start"];
        $info = 'Recognizes diminuitive name forms. ';
    }

    /**
     *
     */
    public function run()
    {
    }

    /**
     *
     */
    public function set()
    {
    }

    /**
     *
     * @param unknown $crow_code (optional)
     * @return unknown
     */
    public function get($crow_code = null)
    {
    }

    /**
     *
     */
    public function loop()
    {
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        //$this->makeSMS();
        //        $this->makeChoices();

        //        $this->thing_report["info"] = "This is a camper in a park with a picnic basket.";
        //        $this->thing_report["help"] = "This is finding picnics. And getting your friends to join you. Text BEAR. Or RANGER.";

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    private function getHypocorisms()
    {
        //if (isset($this->cave_names)) {return;}
        $hypocorisms = [];
        // Makes a one character dictionary

        $file = $this->resource_path . 'hypocorism/hypocorism.txt';
        $contents = file_get_contents($file);

        //'→'

        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {
            // Ignore lines starting with #
            if (substr($line, 0, 1) != "#") {
                $this->injects[] = $line;
            }

            if (stripos($line, '→') !== false) {
                $hypocorism = $this->parseHypocorism($line);

                foreach ($hypocorism['hypocorisms'] as $h => $name) {
                    if (!isset($hypocorisms[strtolower($name)])) {
                        $hypocorisms[strtolower($name)] = [];
                    }
                    $hypocorisms[strtolower($name)] = array_merge(
                        $hypocorisms[strtolower($name)],
                        $hypocorism['hypocorisms']
                    );

                    $hypocorisms[strtolower($name)] = array_unique(
                        $hypocorisms[strtolower($name)]
                    );
                }
                //                foreach ($hypocorism['hypocorisms'] as $h => $name) {
                //                    $hypocorisms[strtolower($name)] =
                //                        $hypocorism['hypocorisms'];
                //                }
            }

            $line = strtok($separator);
        }

        $this->hypocorisms = $hypocorisms;
    }

    public function parseHypocorism($text = null)
    {
        $names = [];
        if ($text == null) {
            return true;
        }

        if (stripos($text, '→') !== false) {
        } else {
            return true;
        }

        $text = trim($text);

        $parts = explode('→', $text);

        $prime_name = $parts[0];

        foreach ($parts as $i => $part) {
            $new_names = explode(",", $part);

            foreach ($new_names as $j => $new_name) {
                $new_names_a = explode(",", $new_name);
                $new_names_b = explode("(", $new_name);
                $new_names_c = explode(" ", $new_name);

                $new_names = array_merge($new_names, $new_names_a);
                $new_names = array_merge($new_names, $new_names_b);
                $new_names = array_merge($new_names, $new_names_c);
            }

            $new_new_names = [];
            foreach ($new_names as $j => $new_name) {
                if (stripos($new_name, ',') !== false) {
                    continue;
                }
                if (stripos($new_name, ' ') !== false) {
                    continue;
                }

                $new_name = str_replace('(', '', $new_name);
                $new_name = str_replace(')', '', $new_name);
                $new_name = str_replace(';', '', $new_name);

                if ($new_name == "") {
                    continue;
                }
                $filtered_new_name = trim($new_name);
                $new_new_names[] = $filtered_new_name;
            }

            $new_new_names = array_unique($new_new_names);

            $names = array_merge($new_new_names, $names);
        }

        $names = array_unique($names);

        $hypocorism = ["name" => $prime_name, "hypocorisms" => $names];
        return $hypocorism;
    }

    /**
     *
     */
    public function makeWeb()
    {
        $test_message = "<b>HYPOCORISM AGENT ";

        $test_message .= "</b><p>";

        $test_message .= $this->response;
        $test_message .= "<p>";

        trim($this->response);

        $this->thing_report['web'] = $test_message;
    }

    /**
     *
     */
    public function makeChoices()
    {
    }

    /**
     *
     */
    public function makeMessage()
    {
        if (isset($this->response)) {
            $m = $this->response;
        } else {
            $m = "No response.";
        }
        $this->message = $m;
        $this->thing_report['message'] = $m;
    }

    public function makeSMS()
    {
        $this->node_list = ["start"];
        $m = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }

    public function readSubject()
    {
        $asserted_text = $this->assert($this->input);

        if ($asserted_text == null) {
            $this->response .=
                "Looks up diminuitive forms of a name ie Davy Dave David. Try HYPOCORISM BOB";
            return;
        }

        // Use the Compression agent which corresponds 1-grams to multi-grams
        // to work backwards and provide a list of all the known bears.
        $t = new Compression($this->thing, "compression hypocorism");
        $phrases = $t->agent->matches['hypocorism'];

        $filtered_text = $asserted_text;

        foreach ($phrases as $i => $phrase) {
            $filtered_text = str_replace($phrase['words'], "", $filtered_text);
        }
        $filtered_text = trim($filtered_text);

        $this->getHypocorisms();

        if (!isset($this->hypocorisms[strtolower($filtered_text)])) {
            $this->response .= "No hypocorisms found. ";
            return;
        }

        $hypocorisms = $this->hypocorisms[strtolower($filtered_text)];
        $this->response .= implode(" ", $hypocorisms);
    }
}
