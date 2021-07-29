<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// dev ship model
// Placeholder - not started

class Ship extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->test = "Development code";

        $this->sms_seperator =
            $this->thing->container["stack"]["sms_separator"]; // |
        $this->sms_address = $this->thing->container["stack"]["sms_address"];

        // Get some stuff from the stack which will be helpful.
        $this->word = $this->thing->container["stack"]["word"];
        $this->email = $this->thing->container["stack"]["email"];

        // Load in time quantums
        $this->cron_period = $this->thing->container["stack"]["cron_period"]; // 60s
        $this->thing_resolution =
            $this->thing->container["stack"]["thing_resolution"]; // 1ms

        // Load in a pointer to the stack record.
        $this->stack_uuid = $this->thing->container["stack"]["uuid"];

        // For the Ship
        $this->created_at = $this->thing->created_at;

        //		$this->sqlresponse = null;

        $this->default_state = "not defined";

        // AIS navigation states
        // ? tow
        $this->node_list = [
            "under way using engine"=>["at anchor", "moored", "aground", "engaged in fishing","under way sailing","not under command","restricted manoeuverability","constrained by her draft", "towing astern", "pushing ahead or alongside", "not defined"],
            "at anchor"=>["under way using engine", "not under command", "under way sailing", "aground"],
            "moored"=>["under way using engine", "not under command", "under way sailing"],
            "aground"=>["at anchor", "under way sailing", "not under command","under way using engine"],
            "engaged in fishing"=>["under way sailing", "under way using engine", "aground"],
            "under way sailing"=>["at anchor", "under way using engine", "moored"],
            "not under command"=>["not defined"],
            "towing astern"=>["under way using engine", "aground"],
            "pushing ahead or alongside" => ["under way using engine", "aground"],
            "not defined"=>["at anchor", "moored", "aground", "engaged in fishing","under way sailing","not under command","restricted manoeuverability","constrained by her draft", "towing astern", "pushing ahead or alongside", "not defined"]
        ];

        $this->initShip();

        $info =
            'Provides an agent to manage a ship.';

        // The 90s script
        $ninety_seconds = "Agent code to manage a ship using NMEA strings.";

        $what =
            "And Things they are meant to be shared transparently, but not indiscriminately.";
        $what .= "";

        $why =
            $this->short_name .
            " is intended as a vehicle to leverage Venture Capital investment in individual impact.";


        // Read the subject as passed to this class.
        // No charge to read the subject line.  By machine.
        $this->mmsi = "1234567890";

        $this->state = $this->thing->choice->load($this->mmsi);
        $this->response .= "Ship state is " . $this->state;
        // Err ... making sure the state is saved.
        $this->thing->choice->Choose($this->state);

        $this->state = $this->thing->choice->load($this->mmsi);
if ($this->state == 0) {
$this->state = $this->default_state;
$this->thing->choice->Choose($this->state);
$this->response .= "Set state to default state of " . strtoupper($this->state) . ". ";
}
//        $this->thing->flagRed();
    }

    function run()
    {
        // dev use pheromone quantity to make decisions
        // ?
        $pheromone_agent = new Pheromone($this->thing, "pheromone");
        $this->pheromone_value = $pheromone_agent->value;

    $this->state = $this->thing->choice->load($this->mmsi);

        // Will need to develop this to only only valid state changes.
        switch ($this->state) {
            case "at anchor":
                $this->response .= "State is at anchor. ";
                break;
            case "moored":
                $this->response .= "State is moored. ";
                break;
            case "aground":
                $this->response .= "State is aground. ";
                break;
            case "not under command":
                $this->response .= "State is not under command. ";
                break;
            case "under way using engine":
                $this->response .= "State is under way using engine. ";
                break;
            case "under way sailing":
                $this->response .= "Ship is under way sailing. ";
                break;
            case "not defined":
                $this->response .= "State is not defined. ";

                break;

            default:
                $this->thing->log(
                    $this->agent_prefix .
                        'unknown state provided "' .
                        $this->state .
                        '".'
                );
                $this->response .= "Ship is broken.";

            // this case really shouldn't happen.
            // but it does when a web button lands us here.
        }


    }

    function set()
    {
        $this->thing->json->setField("variables");
/*
        $this->thing->json->writeVariable(
            ["ship", "left_count"],
            $this->left_count
        );
        $this->thing->json->writeVariable(
            ["ship", "right_count"],
            $this->right_count
        );
*/
        $this->thing->json->writeVariable(
            ["ship", "variable"],
            $this->variable
        );

    }

    public function get($ship_code = null)
    {
        $this->current_time = $this->thing->json->time();

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $this->time_string = $this->thing->json->readVariable([
            "ship",
            "refreshed_at",
        ]);

        // This is a request to get the Place from the Thing
        // and if that doesn't work then from the Stack.
        if ($ship_code == null) {
            $ship_code = $this->uuid;
        }

        if ($this->time_string == false) {
            $this->thing->json->setField("variables");
            $this->time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["ship", "refreshed_at"],
                $this->time_string
            );
        }

        $this->refreshed_at = strtotime($this->time_string);

        $this->thing->json->setField("variables");
/*
        $this->left_count = $this->thing->json->readVariable([
            "ship",
            "left_count",
        ]);
        $this->right_count = $this->thing->json->readVariable([
            "ship",
            "right_count",
        ]);
*/
        $this->variable = $this->thing->json->readVariable([
            "ship",
            "variable",
        ]);


    }

    public function helpShip() {
        $this->thing_report["help"] =
            'This is the "Ship" Agent. It organizes your Things.';

    }

    public function respondResponse()
    {
        $this->makeChoices();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    public function makeWeb()
    {
        $this->makeChoices();

        $test_message =
            "<b>SHIP " . strtoupper($this->thing->nuuid) . "</b>" . "<br>";
        $test_message .= "<p>";
        $test_message .= "<p><b>Ship State</b>";

        $test_message .=
            '<br>Last thing heard: "' .
            $this->subject .
            '"<br>' .
            "The next Ship choices are [ " .
            $this->choices["link"] .
            "].";
        $test_message .= "<br>State: " . $this->state;
        $test_message .= "<br>left_count is " . $this->left_count;
        $test_message .= "<br>right count is " . $this->right_count;

        $test_message .= "<br>" . $this->ship_behaviour[$this->state] . "<br>";

        $test_message .= "<p>";
        $test_message .= "<p><b>Thing Information</b>";
        $test_message .= "<br>subject: " . $this->subject . "<br>";
        $test_message .= "created_at: " . $this->created_at . "<br>";
        $test_message .= "from: " . $this->from . "<br>";
        $test_message .= "to: " . $this->to . "<br>";
        $test_message .= "<br>" . $this->thing_behaviour[$this->state] . "<br>";

        $test_message .= "<p>";
        $test_message .= "<p><b>Narratives</b>";
        $test_message .= "<br>" . $this->litany[$this->state] . "<br>";
        $test_message .= "<br>" . $this->ship_narrative[$this->state] . "<br>";

        $refreshed_at = max($this->created_at, $this->created_at);
        $test_message .= "<p>";
        $ago = $this->thing->human_time(
            strtotime($this->thing->time()) - strtotime($refreshed_at)
        );
        $test_message .= "<br>Thing happened about " . $ago . " ago.";

        $this->thing_report["web"] = $test_message;
    }

    public function makeChoices()
    {
        $choices = $this->thing->choice->makeLinks($this->state);
        $this->choices = $choices;
        $this->thing_report["choices"] = $choices;
    }

    public function makeMessage()
    {
        if (isset($this->response)) {
            $m = $this->response;
        } else {
            $m = "No response.";
        }
        $this->message = $m;
        $this->thing_report["message"] = $m;
    }

    public function makeSMS()
    {
        $this->makeChoices();
        // Generate SMS response
        $this->litany = [
            "aground" =>
                "TBD.",
            "moored" =>
                "TBD.",
            "under way using engine" =>
                "TBD.",
            "not known" => "not known",
        ];

        $this->thing_behaviour = [
            "aground" => "Roll increasing or decreased.",
            "at anchor" => "Swinging about the anchor.",
            "moored" => "Not moving.",
            "not defined" => "Behaviour not known.",
        ];

        // Behaviour
        $this->ship_behaviour = [
            "aground" => "Not moving.",
            "moored" => "Heading constant. Position constant.",
            "at anchor" => "Position centred on a point.",
            "not defined" => "Could be doing anything."
        ];

        // Narrative
        $this->ship_narrative = [
            "aground" => "Could be a problem. Probably is not.",
            "moored" =>
                "Resupply. Offloading. Loading. Recrew.",
            "at anchor" => "Maintain anchor watch.",
            "not defined" => "Ship's status is not defined."
        ];

        $this->prompt_litany = [
            "aground" => "TEXT SHIP",
            "moored" => "TEXT SHIP",
            "at anchor" => "TEXT SHIP",
            "under way using engine" => "TEXT SHIP",
            "not defined" => "TEXT SHIP <state>", 
        ];

        $sms = "SHIP | " . $this->thing->nuuid;
        $sms .= " | " . $this->thing_behaviour[$this->state];
        $sms .= " | " . $this->ship_behaviour[$this->state];
        $sms .= " " . trim($this->response);
        $sms .= " | " . trim($this->prompt_litany[$this->state]);
        $sms .= " " . $this->response;

$choices = strtoupper(implode(" / ", $this->choices['words']));
$sms .= " " . $choices;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function initShip()
    {
        $this->nmea_handler = new NMEA($this->thing, "nmea");

        $this->longitude_handler = new Longitude($this->thing, "longitude");
        $this->latitude_handler = new Latitude($this->thing, "latitude");

        $this->speed_handler = new Speed($this->thing, "speed");
        $this->course_handler = new Course($this->thing, "course");

        $this->altitude_handler = new Altitude($this->thing, "altitude");

        $this->waypoint_longitude_handler = new Longitude(
            $this->thing,
            "waypoint longitude"
        );
        $this->waypoint_latitude_handler = new Latitude(
            $this->thing,
            "waypoint latitude"
        );

        $this->whatisthis = [
            "at anchor" => "A boat floating at anchor.",
            "moored" => "Up against a dock.",
            "aground" => "Keel touching the bottom.",
            "not under command" => "No master aboard.",
            "restricted manoeuvrability" => "Restricted movements.",
            "constrained by her draft" => "Movement restricted by bathymettry.",
            "engaged in fished" => "Restricted movements. Lines and tackle out.",
            "not defined" => "Not defined."
        ];


    }

    // Load a variable into memory.
    // Refactor to Arr
    public function variableShip($variable_array)
    {
        if (!isset($this->variable)) {
            $this->variable = new \stdClass();
        }

        foreach ($variable_array as $variable_name => $variable_value) {
            $this->variable->{$variable_name} = $variable_value;
        }
    }

    public function readShip($text)
    {
        // Handle a NMEA string
        if ($text === null) {
            return null;
        }
        if ($text == "null") {
            return null;
        }
        if (!$this->nmea_handler->isNMEA($text)) {
            return null;
        }

        $nmea_response = $this->nmea_handler->readNMEA($text);
        if (!($nmea_response === true)) {
        $this->variableShip($nmea_response);

        $nmea_checksum_status = $this->nmea_handler->validateNMEA($text);

        if ($nmea_response["recognized_sentence"] === "N") {
            if (
                !in_array(
                    $nmea_response["sentence_identifier"],
                    $unrecognized_sentences
                )
            ) {
                $unrecognized_sentences[] =
                    $nmea_response["sentence_identifier"];
            }
        }

        if ($nmea_response["sentence_identifier"] == "\$GPGSA") {
            $SV_IDs = $nmea_response["SV_IDs"];
        }

        if ($nmea_response["sentence_identifier"] == "\$GPGSV") {
            $total_number_of_SVs_in_view =
                $nmea_response["total_number_of_SVs_in_view"];
        }

        if ($nmea_response["sentence_identifier"] == "\$GPGGA") {
            $last_fix_time = $nmea_response["time"];

            $fix_quality = $nmea_response["fix_quality"];
            $units_of_the_geoid_seperation =
                $nmea_response["units_of_the_geoid_seperation"];
            $height_of_mean_sea_level_above_WGS84_earth_ellipsoid =
                $nmea_response[
                    "height_of_mean_sea_level_above_WGS84_earth_ellipsoid"
                ];
            $altitude_above_mean_sea_level =
                $nmea_response["altitude_above_mean_sea_level"];
            $altitude_units = $nmea_response["altitude_units"];

            $altitude = $this->extractAltitude($text);
        }

        if ($nmea_response["sentence_identifier"] == "\$GPGLL") {
            $current_latitude = $this->extractLatitude($text);
            $current_longitude = $this->extractLongitude($text);
        }

        if (strtolower($this->nmea_handler->sentenceNMEA($text)) == "xte") {
            $cross_track_error_magnitude =
                $nmea_response["cross_track_error_magnitude"];
            $direction_to_steer = $nmea_response["direction_to_steer"];
            $cross_track_units = $nmea_response["cross_track_units"];
        }

        if (strtolower($this->nmea_handler->sentenceNMEA($text)) == "apb") {
            foreach ($nmea_response as $variable_name => $variable_value) {
                $$variable_name = $variable_value;
            }
        }

        if (strtolower($this->nmea_handler->sentenceNMEA($text)) == "rmc") {
            $current_latitude = $this->extractLatitude($text);
            $current_longitude = $this->extractLongitude($text);

            $variation =
                $nmea_response["variation"] .
                $nmea_response["variation_east_west"];

            $speed_in_knots = $this->extractSpeed($text);
            $true_course = $this->extractCourse($text);

            $time_stamp = $nmea_response["time_stamp"];
            $date_stamp = $nmea_response["date_stamp"];
        }

        if (strtolower($this->nmea_handler->sentenceNMEA($text)) == "rmb") {
            $cross_track_error = $nmea_response["cross_track_error"];
            $direction_to_steer = $nmea_response["direction_to_steer"];
            $to_waypoint_id = $nmea_response["to_waypoint_id"];
            $from_waypoint_id = $nmea_response["from_waypoint_id"];
            $destination_waypoint_latitude =
                $nmea_response["destination_waypoint_latitude"] .
                $nmea_response["destination_waypoint_latitude_north_south"];
            $destination_waypoint_longitude =
                $nmea_response["destination_waypoint_longitude"] .
                $nmea_response["destination_waypoint_longitude_east_west"];

            $range_to_destination_in_nautical_miles =
                $nmea_response["range_to_destination_in_nautical_miles"];
            $bearing_to_destination_in_degrees_true =
                $nmea_response["bearing_to_destination_in_degrees_true"];
            $destination_closing_velocity_in_knots =
                $nmea_response["destination_closing_velocity_in_knots"];
            $arrival_status = $nmea_response["arrival_status"];
        }

        if ($nmea_response["sentence_identifier"] == "\$ECRMC") {
            $current_latitude = $this->extractLatitude($text);
            $current_longitude = $this->extractLongitude($text);
        }
}



// Carry on with process a non-NMEA message.

    }

    public function anchorShip() {
$this->thing->choice->Choose("at anchor");
                    $this->response .= "This Ship is at anchor. ";

    }

    public function moorShip() {
$this->thing->choice->Choose("moored");
                    $this->response .= "This Ship is moored. ";

    }

    public function agroundShip() {
$this->thing->choice->Choose("aground");
                    $this->response .= "This Ship is aground. ";

    }

    public function engagedinfishingShip() {
$this->thing->choice->Choose("engaged in fishing");
                    $this->response .= "This Ship is engaged in fishing. ";

    }

    public function underwaysailingShip() {
$this->thing->choice->Choose("under way sailing");
                    $this->response .= "This Ship is under way sailing. ";

    }

    public function notdefinedShip() {
$this->thing->choice->Choose("not defined");
                    $this->response .= "This Ship is in an undefined state. ";

    }


public function helmShip($text) {

$this->response .= "Helm received - " . $text . ". ";

}


    public function readSubject()
    {
        if ($this->state == null) {
            $this->thing->log(
                $this->agent_prefix .
                    "state is null.  Subject discriminator run."
            );
        }
  
            $filtered_input = $this->assert($this->input);

            switch ($filtered_input) {
                case "at anchor":
                    $this->anchorShip();
                    break;
                case "moored":
                    $this->moorShip();
                    break;
                case "aground":
                    $this->agroundShip();
                    break;
                case "under way sailing":
                    $this->underwaysailingShip();
                    break;

                case "not defined":
                    $this->notdefinedShip();
                    break;

                default:
                    $this->response .= "Unknown. ";
            }
//        }


        $input = strtolower($this->subject);
        // Accept ship commands
        $this->keywords = ["forward", "left", "right", "port", "startboard", "astern"];

        $pieces = explode(" ", strtolower($input));
        /*
        if (count($pieces) == 1) {
            if ($input == 'ship') {
                $this->getPlace();
                $this->response = "Last 'place' retrieved.";
                return;
            }

        }
*/
        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "left":
                        case "port":
                            $this->helmShip("30 degrees port");
                            break;
                        case "starboard":
                        case "right":
                            $this->helmShip("30 degrees starboard");
                            break;

                        case "forward":
                            $this->helmShip("forward easy");
                            break;
                        case "astern":
                            $this->helmShip("astern easy");
                            break;
                    }
                }
            }
        }

        // Update Ship's state tree
        $this->thing->choice->Create($this->mmsi, $this->node_list, $this->state);

        //return false;
    }

}
