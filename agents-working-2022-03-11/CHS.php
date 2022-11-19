<?php
/**
 * CHS.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// Canadian Hydrographic Service
class CHS extends Agent
{
    // Canadian Hydrographic Service

    // https://www.qc.dfo-mpo.gc.ca/tides/en/web-services-offered-canadian-hydrographic-service

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

    public $var = "hello";

    /**
     *
     */
    function init()
    {
        $this->keyword = "environment";

        $this->agent_prefix = 'Agent "Canadian Hydrographic Service" ';

        $this->keywords = [
            "water",
            "level",
            "tide",
            "tides",
            "height",
            "prediction",
            "metocean",
            "tides",
            "nautical",
        ];

        $this->default_station_name = "Vancouver";
        $this->time_zone = "America/Vancouver";
        // Loads in Weather variables.

        if ($this->verbosity == false) {
            $this->verbosity = 2;
        }

        // Rest API server
        $this->url = "https://api-iwls.dfo-mpo.gc.ca";

        // Vancouver 07735
        // wlp-hilo
        // 2021-05-22T00:00:00Z

        // https://api-iwls.dfo-mpo.gc.ca/api/v1/stations?code=07735
        // To get

        // 5cebf1de3d0f4a073c4bb943

        // https://api-iwls.dfo-mpo.gc.ca/api/v1/stations/5cebf1de3d0f4a073c4bb943/data?time-series-code=wlp-hilo&from=2021-05-22T00%3A00%3A00Z&to=2021-05-23T00%3A00%3A00Z

        /*
[
  {
    "eventDate": "2021-05-22T02:58:00Z",
    "qcFlagCode": "2",
    "value": 1.9,
    "timeSeriesId": "5d9dd7b933a9f593161c3e55"
  },
  {
    "eventDate": "2021-05-22T09:52:00Z",
    "qcFlagCode": "2",
    "value": 4.5,
    "timeSeriesId": "5d9dd7b933a9f593161c3e55"
  },
  {
    "eventDate": "2021-05-22T16:46:00Z",
    "qcFlagCode": "2",
    "value": 1.9,
    "timeSeriesId": "5d9dd7b933a9f593161c3e55"
  },
  {
    "eventDate": "2021-05-22T22:30:00Z",
    "qcFlagCode": "2",
    "value": 3.5,
    "timeSeriesId": "5d9dd7b933a9f593161c3e55"
  }
]
*/

        $this->thing->refresh_at = $this->thing->time(time() + 5 * 60); // Refresh after 5 minutes.
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
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "chs" . " " . $this->from
        );

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
    function hiloCHS()
    {
        $this->predictions = [];

        $dt = new \DateTime($this->current_time, new \DateTimeZone("UTC"));

        $now = $dt->format("Y-m-d H:i:s");

        $start_time = date(
            "Y-m-d\TH:i:s\Z",
            strtotime("-12 hour", strtotime($now))
        );
        $end_time = date(
            "Y-m-d\TH:i:s\Z",
            strtotime("+48 hour +30 minutes", strtotime($now))
        );
        if ($this->station == null) {
            $this->response .= "No station provided. ";
            return;
        }

        $station_id = $this->station["id"];
        $time_series_code = "wlp-hilo";

        $url =
            $this->url .
            "/api/v1/stations/" .
            $station_id .
            "/data?time-series-code=" .
            $time_series_code .
            "&from=" .
            $start_time .
            "&to=" .
            $end_time .
            "";
        $data = file_get_contents($url);
        $json_data = json_decode($data, true);

        $units = "m";

        foreach ($json_data as $key => $item) {
            $date = $item["eventDate"];

            $value = $item["value"];
            $name = $this->station["officialName"];
            $id = $this->station["code"];
            $units = "m";
            $prediction = [
                "date" => $date,
                "name" => $name,
                "id" => $id,
                "value" => $value,
                "units" => $units,
            ];

            if (!isset($this->predictions[$name])) {
                $this->predictions[$name] = [];
            }
            $this->predictions[$name][] = $prediction;
        }

        $this->refreshed_at = $this->current_time;
    }

    /**
     *
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        $this->thing_report["help"] = "This reads the Canadian Hydrographic Service Rest-API.";
    }

    /**
     *
     */
    public function makeWeb()
    {
        $web = "<b>CHS Agent</b>";
        $web .= "<p>";
        foreach (
            $this->predictions[$this->station["officialName"]]
            as $i => $prediction
        ) {
            $dt = new \DateTime($prediction["date"], new \DateTimeZone("UTC"));

            $dt->setTimezone(new \DateTimeZone($this->time_zone));

            $prediction_datetime = $dt->format("Y-m-d H:i:s");

            $web .= $prediction_datetime . " ";

            $web .= $prediction["id"] . " ";
            $web .= $prediction["name"] . " ";
            $web .= $prediction["value"] . " " . $prediction["units"];
            $web .= "<br>";
        }

        $web .= "source is CHS" . "<br>";

        $web .= "<p>Timezone " . $this->time_zone . ".";

        $web .= "<br>";

        $ago = $this->thing->human_time(
            time() - strtotime($this->refreshed_at)
        );

        $web .= "CHS feed last queried " . $ago . " ago.<br>";

        $this->thing_report["web"] = $web;
    }

    /**
     *
     */
    public function makeSMS()
    {
        if (!isset($this->forecast_conditions)) {
            $this->forecast_conditions = "";
        }

        $sms_message = "TIDES | " . null;

        if (isset($this->station["officialName"])) {
            $sms_message .= strtoupper($this->station["officialName"]) . " ";
        }

        $prediction_text = "";
        //     if ($this->response == "") {
        $predictions = $this->predictions[$this->station["officialName"]];

        $i = 0;
        foreach ($predictions as $index => $prediction) {
            $dt = new \DateTime($prediction["date"], new \DateTimeZone("UTC"));

            $dt->setTimezone(new \DateTimeZone($this->time_zone));

            $d = $dt->format("H:i");
            $date = $dt->format("Y/m/d");

            if ($i == 0) {
                $d = $dt->format("Y/m/d H:i");

                $old_date = $date;
            }

            if ($old_date != $date) {
                $d = $dt->format("m/d H:i");
            }

            $i += 1;

            $prediction_text .=
                $d . " " . $prediction["value"] . $prediction["units"] . " ";

            $old_date = $date;

            if ($i >= 6) {
                break;
            }
            //       }
        }

        $sms_message .= trim($prediction_text);

        $sms_message .= $this->forecast_conditions . " ";
        $sms_message .= trim($this->response);

        $sms_message .=
            "| Licensed by Canadian Hydrographic Service. Experimental. Not for navigation. Times " .
            $this->time_zone .
            ".";

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    /**
     *
     */
    public function makeMessage()
    {
        $message = "Tides are " . null . ".";
        $message .= " " . "Licensed by Canadian Hydrographic Service.";

        $this->message = $message;
        $this->thing_report["message"] = $message;
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
    public function stationCHS($text = null)
    {
        if ($text == null) {
            $text = $this->default_station_name;
        }

        $chs_stations = $this->getMemory("chs-stations");

        $use_cache = true;

        if (isset($chs_stations["refreshed_at"])) {
            $age =
                strtotime($this->thing->time()) -
                strtotime($chs_stations["refreshed_at"]);

            if ($age < 24 * 60 * 60) {
                $use_cache = false;
            }
        }

        if ($use_cache) {
            //$this->response .= "Called CHS for stations. ";
            $data = file_get_contents(
                "https://api-iwls.dfo-mpo.gc.ca/api/v1/stations"
            );
            $json_data = json_decode($data, true);

            $timestamp = $this->thing->time();

            $chs_stations = [
                "stations" => $json_data,
                "refreshed_at" => $timestamp,
            ];

            $this->setMemory("chs-stations", $chs_stations);
        } else {
            // $this->response .= "Used cached stations. ";
        }

        $this->stations = $chs_stations["stations"];

        foreach ($this->stations as $i => $station) {
            $station_id = $station["id"];
            $station_code = $station["code"];
            $station_name = $station["officialName"];

            if (stripos($text, $station_name) !== false) {
                $stations[] = $station;
            }

            if (stripos($text, $station_code) !== false) {
                $stations[] = $station;
            }
        }

        if (isset($stations[0])) {
            $this->station = $stations[0];
            return;
        }

        // Otherwise try harder to find a match.
        foreach ($this->stations as $i => $station) {
            $station_id = $station["id"];
            $station_code = $station["code"];
            $station_name = $station["officialName"];

            $score = levenshtein($text, $station_name);
            if (!isset($min)) {
                $min = $score;
                $selected_name = $station_name;
                $selected_id = $station_id;
                $selected_station = $station;
            }

            if ($score < $min) {
                $selected_name = $station_name;
                $selected_id = $station_id;
                $selected_station = $station;
                $min = $score;
            }

            $this->station = $selected_station;
        }
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->num_hits = 0;

        $asserted_input = $this->assert($this->input);

        usort($this->keywords, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        foreach ($this->keywords as $keyword) {
            $this->filtered_input = str_replace($keyword, "", $asserted_input);
        }
        $this->filtered_input = str_replace("  ", "", $this->filtered_input);
        $this->filtered_input = trim($this->filtered_input);

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($this->filtered_input));

        if (count($pieces) == 1) {
            if ($this->filtered_input == "weather") {
                $this->response = "Did nothing. ";
                return;
            }

            // Drop through
            // return "Request not understood";
        }

        $this->stationCHS($this->filtered_input);

        $this->hiloCHS();

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "verbosity":
                        case "mode":
                            $number = $this->extractNumber();
                            if (is_numeric($number)) {
                                $this->verbosity = $number;
                                $this->set();
                            }
                            return;

                        default:
                    }
                }
            }
        }
    }
}
