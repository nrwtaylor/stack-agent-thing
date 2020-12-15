<?php
/**
 * CHS.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// Canadian Hydrographic Service
class CHS extends Agent
{
    // SOAP needs enabling in PHP.ini

    // https://www.waterlevels.gc.ca/docs/Specifications%20-%20Spine%20observation%20and%20predictions%202.0.3(en).pdf

    // https://www.waterlevels.gc.ca/eng/info/Webservices
    // Canadian Hydrographic Service

    // License required from Canadian Hydrographic Service to re-publish.
    // https://www.waterlevels.gc.ca/eng/info/Licence

    //  “This product has been produced by or for
    // [insert User's corporate name] and includes data and
    // services provided by the Canadian Hydrographic Service
    // of the Department of Fisheries and Oceans. The
    // incorporation of data sourced from the Canadian
    //  Hydrographic Service of the Department of Fisheries
    // and Oceans within this product does NOT constitute an
    // endorsement by the Canadian Hydrographic Service or
    // the Department of Fisheries and Oceans of this product.”

    public $var = 'hello';

    /**
     *
     */
    function init()
    {
        $this->keyword = "environment";

        $this->agent_prefix = 'Agent "Weather" ';

        $this->keywords = [
            'water',
            'level',
            'tide',
            'tides',
            'height',
            'prediction',
            'metocean',
            'tides',
            'nautical',
        ];

        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "weather" . " " . $this->from
        );

        $this->default_station_name = "Vancouver";

        // Loads in Weather variables.

        if ($this->verbosity == false) {
            $this->verbosity = 2;
        }
        // Create the SoapClient instance
        $url = "https://ws-shc.qc.dfo-mpo.gc.ca/predictions" . "?wsdl";

        if (!extension_loaded('soap')) {
            $this->response .= "Tide connector not available. ";
            // Do things
        }

        try {
            $this->client = new \SoapClient($url);
            // $this->client     = new \SoapClient($url, array("trace" => 1, "exception" => 0));
        } catch (SoapFault $sf) {
            $this->response .= "Could not get tides. ";
        } catch (Throwable $t) {
            $this->response .= "Could not get tides. ";
        } catch (Exception $e) {
            $this->response .= "Could not get tides. ";
        }
        $this->getWeather();
    }

    /**
     *
     */
    function run()
    {
        $this->doCHS($this->input);
    }

    function getTimezone()
    {
        // Eventually call the timezone agent.
        $this->time_zone = "America/Vancouver";
    }

    /**
     *
     * @param unknown $text
     */
    function doCHS($text)
    {
        // No state awareness.
        //        if (!isset($this->state)) {$this->state = $this->default_state;}
        //        $this->getState();

        $filtered_text = strtolower($text);
        $ngram_agent = new Ngram($this->thing, $filtered_text);

        foreach ($ngram_agent->ngrams as $index => $ngram) {
            switch ($ngram) {
                case "list":
                    $t = "Available stations are ";
                    foreach ($this->stations as $name => $prediction) {
                        $t .= $name . " / ";
                    }
                    $this->response .= $t;
                    break;

                default:
            }
        }
    }

    /**
     *
     */
    function set()
    {
        $this->variables_agent->setVariable("state", $this->state);

        $this->variables_agent->setVariable("verbosity", $this->verbosity);

        if (!isset($this->current_conditions)) {
            $this->current_conditions = null;
        }
        if (!isset($this->forecast_conditions)) {
            $this->forecast_conditions = null;
        }

        $this->variables_agent->setVariable(
            "current_conditions",
            $this->current_conditions
        );
        $this->variables_agent->setVariable(
            "forecast_conditions",
            $this->forecast_conditions
        );

        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );

        $this->refreshed_at = $this->current_time;
    }

    /**
     *
     */
    function get()
    {
        $this->state = $this->variables_agent->getVariable("state");

        $this->last_current_conditions = $this->variables_agent->getVariable(
            "current_conditions"
        );
        $this->last_forecast_conditions = $this->variables_agent->getVariable(
            "forecast_conditions"
        );

        $this->last_refreshed_at = $this->variables_agent->getVariables(
            "refreshed_at"
        );

        $this->verbosity = $this->variables_agent->getVariable("verbosity");
    }

    /**
     *
     */
    function getWeather()
    {
        $this->getTimezone();
        $this->predictions = [];
        $this->stations = [];

        $dt = new \DateTime($this->current_time, new \DateTimeZone("UTC"));

        $now = $dt->format('Y-m-d H:i:s');

        $start_time = date(
            'Y-m-d H:i:s',
            strtotime('-12 hour', strtotime($now))
        );
        $end_time = date(
            'Y-m-d H:i:s',
            strtotime('+48 hour +30 minutes', strtotime($now))
        );

        // Area around Vancouver.
        $lat = 49.3;
        $long = -122.86;

        $units = "m";

        //        $size = 0.5;
        $size = 1;

        // example from CHS
        // $m = $client->search("hilo", 47.5, 47.7, -61.6, -61.4, 0.0, 0.0, $date . " ". "00:00:00", $date . " " . "23:59:59", 1, 100, true, "", "asc");

        //        $m = $this->client->search("hilo", $lat - $size, $lat + $size, $long - $size, $long + $size, 0.0, 0.0, $date . " ". "00:00:00", $date . " " . "23:59:59", 1, 100, true, "", "asc");

        $m = $this->client->search(
            "hilo",
            $lat - $size,
            $lat + $size,
            $long - $size,
            $long + $size,
            0.0,
            0.0,
            $start_time,
            $end_time,
            1,
            500,
            true,
            "",
            "asc"
        );
        foreach ($m->data as $key => $item) {
            //echo "station" . $value->metadata[0]->value . " ";
            //echo $value->metadata[1]->value . "";

            $date_min = $item->boundaryDate->min;
            $date_max = $item->boundaryDate->max;

            if ($date_min == $date_max) {
                // expected
                $date = $date_min;
            } else {
                $date = true;
            }

            $name = $item->metadata[1]->value;
            $name = str_replace("* ", " ", $name);
            $name = str_replace(" *", " ", $name);
            $name = trim($name);

            $id = $item->metadata[0]->value;
            $value = $item->value;

            $prediction = [
                "date" => $date,
                "name" => $name,
                "id" => $id,
                "value" => $value,
                "units" => $units,
                "item" => $item,
            ];
            $this->predictions[] = $prediction;

            $this->stations[$name][] = $prediction;
        }

        $this->refreshed_at = $this->current_time;
    }

    /**
     *
     */
    function getTemperature()
    {
        // devstack not finished
        if (!isset($this->conditions)) {
            $this->getWeather();
        }
        $this->current_temperature = -1;
    }

    /**
     *
     * @param unknown $needles
     * @param unknown $haystack
     * @return unknown
     */
    function match_all($needles, $haystack)
    {
        if (empty($needles)) {
            return false;
        }

        foreach ($needles as $needle) {
            if (strpos($haystack, $needle) == false) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     */
    public function respond()
    {
        // Thing actions
        $this->thing->flagGreen();
        // Generate email response.

        $to = $this->thing->from;
        $from = "chs";

        $choices = false;
        $this->thing_report['choices'] = $choices;

        $this->makeSms();
        $this->makeMessage();

        $this->thing_report['email'] = $this->sms_message;
        //$this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;
        $this->thing_report['txt'] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        $this->makeWeb();

        $this->thing_report['help'] = 'This reads a web resource.';
    }

    /**
     *
     */
    public function makeWeb()
    {
        $this->getTimezone();
        $web = "<b>CHS Agent</b>";
        $web .= "<p>";

        foreach ($this->predictions as $key => $prediction) {
            $dt = new \DateTime($prediction['date'], new \DateTimeZone("UTC"));

            $dt->setTimezone(new \DateTimeZone($this->time_zone));

            //                $d = date('H:i', strtotime($prediction["date"]));
            //                $d = $dt->format('H:i');
            //                $date = $dt->format('Y/m/d');

            //        $now = date("Y-m-d H:i:s");
            $prediction_datetime = $dt->format('Y-m-d H:i:s');

            //            $web .= $prediction['date'] . " ";
            $web .= $prediction_datetime . " ";

            $web .= $prediction['id'] . " ";
            $web .= $prediction['name'] . " ";
            $web .= $prediction['value'] . " " . $prediction['units'];
            $web .= "<br>";
        }

        $web .= "<p>";
        //        $web .= "current conditions are " . $this->current_conditions . "<br>";
        $web .=
            "forecast conditions becoming " .
            $this->forecast_conditions .
            "<br>";

        //        $web .= "data from " . $this->link . "<br>";
        $web .= "source is CHS" . "<br>";

        $web .= "<p>Timezone " . $this->time_zone . ".";

        $web .= "<br>";

        $ago = $this->thing->human_time(
            time() - strtotime($this->refreshed_at)
        );

        $web .= "CHS feed last queried " . $ago . " ago.<br>";

        //$this->sms_message = $sms_message;
        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    public function makeSms()
    {
        $this->getTimezone();

        if (!isset($this->forecast_conditions)) {
            $this->forecast_conditions = "";
        }

        $sms_message = "TIDES | " . null;

        if (isset($this->station_name)) {
            $sms_message .= strtoupper($this->station_name) . " ";
        }

        $prediction_text = "";
        if ($this->response == "") {
            $predictions = $this->stations[$this->station_name];

            $i = 0;
            foreach ($predictions as $index => $prediction) {
                //$prediction = $predictions[0];

                //                $tz = $this->time_zone;

                //                $dt = new \DateTime($tmestamp, new \DateTimeZone("UTC"));
                $dt = new \DateTime(
                    $prediction["date"],
                    new \DateTimeZone("UTC")
                );

                $dt->setTimezone(new \DateTimeZone($this->time_zone));

                //                $d = date('H:i', strtotime($prediction["date"]));
                $d = $dt->format('H:i');
                $date = $dt->format('Y/m/d');

                if ($i == 0) {
                    $d = $dt->format('Y/m/d H:i');

                    $old_date = $date;
                }

                if ($old_date != $date) {
                    //   $d = date('m/d H:i',strtotime($prediction["date"]));
                    $d = $dt->format('m/d H:i');
                }

                $i += 1;

                $prediction_text .=
                    $d .
                    " " .
                    $prediction["value"] .
                    $prediction["units"] .
                    " ";

                $old_date = $date;

                if ($i >= 6) {
                    break;
                }
            }
        }

        $sms_message .= trim($prediction_text);

        $sms_message .= $this->forecast_conditions . " ";
        $sms_message .= trim($this->response);
        //        $sms_message .= " | link " . $this->link;
        $sms_message .=
            "| Licensed by Canadian Hydrographic Service. Experimental. Not for navigation. Times " .
            $this->time_zone .
            ".";

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

    /**
     *
     */
    public function makeMessage()
    {
        $message = "Tides are " . null . ".";
        $message .= " " . "Licensed by Canadian Hydrographic Service.";

        $this->message = $message;
        $this->thing_report['message'] = $message;
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function extractNumber($input = null)
    {
        if ($input == null) {
            $input = $this->subject;
        }

        $pieces = explode(" ", strtolower($input));

        // Extract number
        $matches = 0;
        foreach ($pieces as $key => $piece) {
            if (is_numeric($piece)) {
                $number = $piece;
                $matches += 1;
            }
        }

        if ($matches == 1) {
            if (is_integer($number)) {
                $this->number = intval($number);
            } else {
                $this->number = floatval($number);
            }
        } else {
            $this->number = true;
        }
        return $this->number;
    }

    /**
     *
     */
    public function getStation()
    {
        //$ngrams = new Ngram($this->thing,"ngram");
        //$ngrams->extractNgrams($this->input);
        //var_dump($ngrams->ngrams);

        $this->station_name = $this->default_station_name;

        foreach ($this->stations as $station_name => $prediction) {
            if (
                strpos(strtolower($this->input), strtolower($station_name)) !=
                false
            ) {
                $station_names[] = $station_name;
            }
        }

        if (isset($station_names[0])) {
            $this->station_name = $station_names[0];
            return;
        }

        // Otherwise try harder to find a match.
        foreach ($this->stations as $name => $prediction) {
            $score = levenshtein($this->filtered_input, $name);
            if (!isset($min)) {
                $min = $score;
                $selected_name = $name;
            }

            if ($score < $min) {
                $selected_name = $name;
                $min = $score;
            }

            $this->station_name = $selected_name;
        }
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->response = null;
        $this->num_hits = 0;

        //$this->number = extractNumber();
        $keywords = $this->keywords;

        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->subject);
        }

        $this->input = $input;
        $keywords = $this->keywords;
        usort($keywords, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        $this->filter_input = $this->input;
        foreach ($keywords as $keyword) {
            $this->filtered_input = str_replace($keyword, "", $this->input);
            //$this->filtered_input = str_replace("chs", "", $this->filtered_input);
        }
        $this->filtered_input = str_replace("  ", "", $this->filtered_input);
        $this->filtered_input = trim($this->filtered_input);

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        //  $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'weather') {
                //echo "readsubject block";
                //$this->read();
                $this->response = "Did nothing.";
                return;
            }

            // Drop through
            // return "Request not understood";
        }

        $this->getStation();

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'verbosity':
                        case 'mode':
                            $number = $this->extractNumber();
                            if (is_numeric($number)) {
                                $this->verbosity = $number;
                                $this->set();
                            }
                            return;

                        default:
                        //$this->read();
                        //echo 'default';
                    }
                }
            }
        }

        return "Message not understood";
        return false;
    }
}
