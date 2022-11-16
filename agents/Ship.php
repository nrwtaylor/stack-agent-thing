<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Ship extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->default_state = "X";

        $this->keyword = "ship";

        $this->test = "Development code"; // Always

        $this->resource_path = $GLOBALS["stack_path"] . "resources/";

        // Get the current identities uuid.
        $default_ship_id = new Identity($this->thing, "identity");
        $this->default_ship_id = $default_ship_id->uuid;

        // Set up default ship settings
        $this->verbosity = 1;
        $this->requested_state = null;
        //        $this->default_state = "green";
        //        $this->node_list = ["green" => ["red" => ["green"]]];

        $this->show_uuid = "off";

        $this->link = $this->web_prefix . "thing/" . $this->uuid . "/ship";

        $this->show_link = "off";

        $this->refreshed_at = null;

        $this->current_time = $this->thing->time();
        // devstack
        $this->associate_agent = new Associate($this->thing, $this->subject);

        $this->thing_report["help"] =
            "This Ship is either RED, GREEN, YELLOW or DOUBLE YELLOW. Text SHIP DOUBLE YELLOW.";
        $this->thing_report["info"] =
            "DOUBLE YELLOW means keep going. RED means stop.";

        $this->default_state = "not defined";

        // AIS navigation states
        // ? tow
        $this->node_list = [
            "under way using engine" => [
                "at anchor",
                "moored",
                "aground",
                "engaged in fishing",
                "under way sailing",
                "not under command",
                "restricted manoeuverability",
                "constrained by her draft",
                "towing astern",
                "pushing ahead or alongside",
                "not defined",
            ],
            "at anchor" => [
                "under way using engine",
                "not under command",
                "under way sailing",
                "aground",
            ],
            "moored" => [
                "under way using engine",
                "not under command",
                "under way sailing",
            ],
            "aground" => [
                "at anchor",
                "under way sailing",
                "not under command",
                "under way using engine",
            ],
            "engaged in fishing" => [
                "under way sailing",
                "under way using engine",
                "aground",
            ],
            "under way sailing" => [
                "at anchor",
                "under way using engine",
                "moored",
            ],
            "not under command" => ["not defined"],
            "towing astern" => ["under way using engine", "aground"],
            "pushing ahead or alongside" => [
                "under way using engine",
                "aground",
            ],
            "not defined" => [
                "at anchor",
                "moored",
                "aground",
                "engaged in fishing",
                "under way sailing",
                "not under command",
                "restricted manoeuverability",
                "constrained by her draft",
                "towing astern",
                "pushing ahead or alongside",
                "not defined",
            ],
        ];

        $this->initShip();
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
            "engaged in fished" =>
                "Restricted movements. Lines and tackle out.",
            "not defined" => "Not defined.",
        ];
    }

    public function lastShip()
    {
    }

    public function nextShip()
    {
    }

    public function currentShip()
    {
    }

    public function respondResponse()
    {
        $this->makeHelp();
        $this->makeInfo();
        $this->thing->flagGreen();

        $message_thing = new Message($this->thing, $this->thing_report);
        //$thing_report['info'] = $message_thing->thing_report['info'];
    }

    public function set()
    {
        if (!isset($this->ship_thing)) {
            //$this->ship_thing = $this->thing;
            // Nothing to set
            //return true;
        }

        if (isset($this->ship_thing->state)) {
            $this->ship["state"] = $this->ship_thing->state;
        }
        if (isset($this->ship_thing->uuid)) {
            $this->ship["id"] = $this->idShip($this->ship_thing->uuid);
        }

        if (isset($this->ship_thing->uuid)) {
            $this->ship["uuid"] = $this->ship_thing->uuid;
        }
        $this->ship["text"] = "ship check";

        if (isset($this->ship_thing->text)) {
            $this->ship["text"] = $this->ship_thing->text;
        }

        if (isset($this->ship_thing->uuid)) {
            $this->ship_thing->associate($this->ship_thing->uuid);
        }

        if (isset($this->ship_thing->variables->snapshot)) {
            $this->ship["snapshot"] = $this->ship_thing->variables->snapshot;
        } else {
            $this->ship["snapshot"] = true;
        }

        if ($this->channel_name == "web") {
            $this->response .= "Detected web channel. ";
            // Do not effect a state change for web views.
            return;
        }
        $this->setShip();
        //return $this->ship_thing->variables->snapshot;
    }

    function is_positive_integer($str)
    {
        return is_numeric($str) && $str > 0 && $str == round($str);
    }

    public function anchorShip()
    {
        $this->thing->choice->Choose("at anchor");
        $this->response .= "This Ship is at anchor. ";
    }

    public function moorShip()
    {
        $this->thing->choice->Choose("moored");
        $this->response .= "This Ship is moored. ";
    }

    public function agroundShip()
    {
        $this->thing->choice->Choose("aground");
        $this->response .= "This Ship is aground. ";
    }

    public function engagedinfishingShip()
    {
        $this->thing->choice->Choose("engaged in fishing");
        $this->response .= "This Ship is engaged in fishing. ";
    }

    public function underwaysailingShip()
    {
        $this->thing->choice->Choose("under way sailing");
        $this->response .= "This Ship is under way sailing. ";
    }

    public function notdefinedShip()
    {
        $this->thing->choice->Choose("not defined");
        $this->response .= "This Ship is in an undefined state. ";
    }

    public function helmShip($text)
    {
        // https://www.professionalmariner.com/standardized-tractor-tug-commands-for-ship-assist-work/
        $engine_commands = [
            "ahead port full",
            "ahead starboard full",
            "ahead port half",
            "ahead starboard half",
            "ahead port easy",
            "ahead starboard easy",
            "ahead port dead slow",
            "ahead starboard dead slow",
            "ahead full",
            "ahead half",
            "ahead easy",
            "ahead dead slow",
            "astern full",
            "astern half",
            "astern easy",
            "astern dead slow",
            "astern port full",
            "astern port half",
            "astern port easy",
            "astern port dead slow",
            "astern starboard full",
            "astern starboard half",
            "astern starboard easy",
            "astern starboard dead slow",
        ];

        // https://en.wikipedia.org/wiki/Helmsman
        $helm_commands = [
            "midships",
            "meet her",
            "port hard rudder",
            "starboard hard rudder",
            "left hard rudder",
            "right hard rudder",
            "left standard rudder",
            "right standard rudder",
            "shift your rudder",
        ];

        $heading_commands = ["steady as she goes", "steady on a course"];

        $engine_command = null;
        foreach ($engine_commands as $i => $command) {
            if (strpos($text, $command) !== false) {
                $engine_command = $command;
                break;
            }
        }

        $helm_command = null;
        foreach ($helm_commands as $i => $command) {
            if (strpos($text, $command) !== false) {
                $helm_command = $command;
                break;
            }
        }

        $heading_command = null;
        foreach ($helm_commands as $i => $command) {
            if (strpos($text, $command) !== false) {
                $heading_command = $command;
                break;
            }
        }

        if ($engine_command != null) {
            $this->response .=
                "Received engine command - " . $engine_command . ". ";
            return;
        }

        if ($helm_command != null) {
            $this->response .=
                "Received helm command - " . $helm_command . ". ";
            return;
        }

        if ($heading_command != null) {
            $this->response .=
                "Received heading command- " . $heading_command . ". ";
            return;
        }
    }

    function isShip($ship = null)
    {
        // Validates whether the Ship is green or red.
        // Nothing else is allowed.

        if ($ship == null) {
            if (!isset($this->state)) {
                $this->state = "red";
            }

            $ship = $this->state;
        }

        if (
            $ship == "red" or
            $ship == "green" or
            $ship == "yellow" or
            $ship == "double yellow"
        ) {
            return false;
        }

        return true;
    }

    public function get()
    {
        $this->channel = new Channel($this->thing, "channel");
        $this->channel_name = $this->channel->channel_name;

        /*
        if (is_string($this->channel_name)) {
            $this->response .= "Saw channel is " . $this->channel_name . ". ";
        } else {
            $this->response .= "No channel name. ";
        }
*/
        $this->getShip();

        if (!isset($this->ships)) {
            return true;
        }

        foreach ($this->ships as $i => $ship) {
            if ($ship["uuid"] == $this->ship_thing->uuid) {
                $this->ship_thing->state = $ship["state"];
                //$this->ship_thing->longitude = new Longitude($this->ship_thing, "longitude");
                //$this->ship_thing->variables = $ship['variables'];
                return;
            }
        }
    }

    public function run()
    {
        // Get this too. Or put it in the run loop.
        $state = "X";
        if (isset($this->ship_thing->state)) {
            $state = $this->ship_thing->state;
        }

        $this->helpShip($state);
        $this->infoShip($state);
    }

    function helpShip($text = null)
    {
        $a = $this->associationsShip();

        $next_ship_id = "X";
        if (isset($a[0]["id"])) {
            $next_ship_id = $a[0]["id"];
        }
        $next_ship_text = "Next SHIP " . $next_ship_id . ".";

        if (strtolower($text) == "x") {
            $state = "X";
            $help = "No ship found. Text NEW SHIP.";
            if (isset($this->ship_thing)) {
                $help =
                    "Treat this ship as if it is broken. Try SHIP MOORED and see if it changes colour.";
            }
        }

        if (strtolower($text) == "aground") {
            $help = "This Ship is AGROUND.";
        }
        if (strtolower($text) == "under way using engine") {
            $help = "This Ship is UNDER WAY USING ENGINE.";
        }
        if (strtolower($text) == "moored") {
            $help = "This Ship is MOORED.";
        }
        if (strtolower($text) == "engaged in fishing") {
            $help = "This Ship is ENGAGED IN FISHING.";
        }

        if (strtolower($text) == "under way sailing") {
            $help = "This Ship is UNDER WAY SAILING.";
        }

        if (strtolower($text) == "not under command") {
            $help = "This Ship is NOT UNDER COMMAND.";
        }

        if (strtolower($text) == "towing astern") {
            $help = "This Ship is TOWING ASTERN.";
        }

        if (strtolower($text) == "pushing ahead or alongside") {
            $help = "This Ship is PUSHING AHEAD OR ALONGSIDE.";
        }

        if (strtolower($text) == "not defined") {
            $help = "This Ship is NOT DEFINED.";
        }

        if (strtolower($text) == "at anchor") {
            $help = "This Ship is AT ANCHOR.";
        }

        if (strtolower($text) == "restricted manoeuverability") {
            $help = "This Ship has RESTRICTED MANOEUVERABILITY.";
        }

        if (strtolower($text) == "constrained by her draft") {
            $help = "This Ship is CONSTRAINED BY HER DRAFT.";
        }

        $this->thing_report["help"] = $help;

        return $help;
    }

    function infoShip($text = null)
    {
        if (strtolower($text) == "x") {
            $state = "X";
            $info = "Ship is OFF. Or broken.";
        }

        if (strtolower($text) == "aground") {
            $info = "This Ship is AGROUND.";
        }
        if (strtolower($text) == "under way using engine") {
            $info = "This Ship is UNDER WAY USING ENGINE.";
        }
        if (strtolower($text) == "moored") {
            $info = "This Ship is MOORED.";
        }
        if (strtolower($text) == "engaged in fishing") {
            $info = "This Ship is ENGAGED IN FISHING.";
        }

        if (strtolower($text) == "under way sailing") {
            $info = "This Ship is UNDER WAY SAILING.";
        }

        if (strtolower($text) == "not under command") {
            $info = "This Ship is NOT UNDER COMMAND.";
        }

        if (strtolower($text) == "towing astern") {
            $info = "This Ship is TOWING ASTERN.";
        }

        if (strtolower($text) == "pushing ahead or alongside") {
            $info = "This Ship is PUSHING AHEAD OR ALONGSIDE.";
        }

        if (strtolower($text) == "not defined") {
            $info = "This Ship is NOT DEFINED.";
        }

        if (strtolower($text) == "at anchor") {
            $info = "This Ship is AT ANCHOR.";
        }

        if (strtolower($text) == "restricted manoeuverability") {
            $info = "This Ship has RESTRICTED MANOEUVERABILITY.";
        }

        if (strtolower($text) == "constrained by her draft") {
            $info = "This Ship is CONSTRAINED BY HER DRAFT.";
        }

        $this->thing_report["info"] = $info;
        return $info;
    }

    function changeShip($text)
    {
        if (!isset($this->ship_thing)) {
            return true;
        }
        $state = null;

        if (strtolower($text) == "x") {
            $state = "X";
            $type = "ship x";
        }

        if (strtolower($text) == "aground") {
            $state = "aground";
        }
        if (strtolower($text) == "under way using engine") {
            $state = "under way using engine";
        }

        if (strtolower($text) == "moored") {
            $state = "moored";
        }
        if (strtolower($text) == "engaged in fishing") {
            $state = "engaged in fishing";
        }

        if (strtolower($text) == "under way sailing") {
            $state = "under way sailing";
        }

        if (strtolower($text) == "not under command") {
            $state = "not under command";
        }

        if (strtolower($text) == "towing astern") {
            $state = "towing astern";
        }

        if (strtolower($text) == "pushing ahead or alongside") {
            $state = "pushing ahead or alongside";
        }
        if (strtolower($text) == "at anchor") {
            $state = "at anchor";
        }

        if (strtolower($text) == "not defined") {
            $state = "not defined";
        }

        if (strtolower($text) == "restricted manoeuverability") {
            $state = "restricted manoeuverability";
        }

        if (strtolower($text) == "constrained by her draft") {
            $state = "constrained by her draft";
        }

        $this->ship_thing->state = $state;
        $this->ship_thing->text = $state; // Temporary

        if ($state != null) {
            $this->response = "Selected " . $state . " ship.";
        }
    }

    function setShip($text = null)
    {

        if (!isset($this->ship_thing)) {
            return true;
        }

        $this->ship_thing->Write(
            ["ship", "state"],
            $this->ship["state"]
        );

        $this->ship_thing->Write(
            ["ship", "text"],
            $this->ship["text"]
        );

        $this->ship_thing->Write(
            ["ship", "snapshot"],
            $this->ship["snapshot"]
        );

        $this->ship_thing->Write(
            ["ship", "refreshed_at"],
            $this->current_time
        );

        $this->ship_thing->associate($this->ship_thing->uuid);
    }

    function makeShip()
    {
        if (!isset($this->ship_thing)) {
            return true;
        }
    }

    function newShip()
    {
        $this->response .= "Called for a new ship. ";
        $thing = new Thing(null);
        $thing->Create($this->from, "ship", "ship");

        $agent = new Ship($thing, "ship");

        $this->ship_thing = $thing;
        $this->ship_thing->state = "not defined";
        $this->ship_thing->text = "not defined";

        $this->ship_id = $this->idShip($thing->uuid);
    }

    function getShipbyUuid($uuid)
    {
        if ($this->channel_name == "web") {
            $id = $this->idShip($uuid);
            $this->getShipbyId($id);
            return;
        }
        $thing = new Thing($uuid);
        if ($thing->thing == false) {
            $this->ship_thing = false;
            $this->ship_id = null;
            return true;
        }

        $ship = $this->thing->json->jsontoArray($thing->thing->variables)[
            "ship"
        ];

        $this->ship_thing = $thing;
        $this->ship_id = $thing->uuid;

        if (isset($ship["state"])) {
            $this->ship_thing->state = $ship["state"];
        }
    }

    function getShipbyId($id)
    {
        if (!isset($this->ships)) {
            $this->getShips();
        }
        $matched_uuids = [];
        foreach ($this->ships as $i => $ship) {
            if ($ship["id"] == $id) {
                $matched_uuids[] = $ship["uuid"];
                continue;
            }

            if ($this->idShip($ship["uuid"]) == $id) {
                $matched_uuids[] = $ship["uuid"];
                continue;
            }
        }
        if (count($matched_uuids) != 1) {
            return true;
        }

        $uuid = $matched_uuids[0];

        $this->ship_thing = new Thing($uuid);

        $ship = $this->thing->json->jsontoArray(
            $this->ship_thing->thing->variables
        )["ship"];

        $this->ship_id = $this->ship_thing->uuid;

        if (isset($ship["state"])) {
            $this->ship_thing->state = $ship["state"];
        }
    }

    // Take in a uuid and convert it to a ship id (id here).
    function idShip($text = null)
    {
        $ship_id = $text;
        if ($text == null) {
            if (isset($this->ship_thing->uuid)) {
                $ship_id = $this->ship_thing->uuid;
            }
        }

        $t = hash("sha256", $ship_id);
        $t = substr($t, 0, 4);
        return $t;
    }

    public function textShip($ship = null)
    {
        if ($ship == null) {
            $ship = $this->ship;
        }
        $id = "X";
        if (isset($ship["id"])) {
            $id = strtoupper($ship["id"]);
        }

        $uuid = "X";
        if (isset($ship["uuid"])) {
            $uuid = $ship["uuid"];
        }

        $state = "X";
        if (isset($ship["state"])) {
            $state = strtoupper($ship["state"]);
        }

        $text = "X";
        if (isset($ship["text"])) {
            $text = $ship["text"];
        }

        $refreshed_at = "X";
        if (isset($ship["refreshed_at"])) {
            $refreshed_at = $ship["refreshed_at"];
        }

        $text =
            $id .
            " " .
            //            $uuid .
            //            " " .
            " " .
            $state .
            " " .
            $text .
            " " .
            $refreshed_at .
            "\n";
        return $text;
    }

    public function getShip($text = null)
    {
        if ($text != null) {
            $t = $this->getShipbyId($text);
            return;
        }

        if (!isset($this->thing->thing->variables)) {
            $this->response .= "No stack found. ";
            return true;
        }

        if (
            isset(
                $this->thing->json->jsontoArray($this->thing->thing->variables)[
                    "ship"
                ]
            )
        ) {
            // First is there a ship in this thing.
            $ship = $this->thing->json->jsontoArray(
                $this->thing->thing->variables
            )["ship"];

            $ship_id = "X";
            if (isset($this->ship["id"])) {
                $this->ship_id = $ship["id"];
                $this->response .= "Saw " . $this->ship_id . ". ";

                $this->getShipbyId($this->ship_id);
                return;
            }

            $ship_id = "X";
            if (isset($ship["uuid"])) {
                $this->ship_id = $this->idShip($ship["uuid"]);
                $this->response .= "Saw " . $this->ship_id . ". ";

                $this->getShipbyUuid($ship["uuid"]);
                return;
            }

            if (isset($this->ship["refreshed_at"])) {
                $this->ship_id = $this->thing->uuid;

                $this->response .= "Saw a ship in the thing. ";

                $this->getShipbyId($this->ship_id);
                return;
            }

            // Get the most recent ship command.
            //return;
        }
        // Haven't found the ship in the thing.

        if (!isset($this->ships)) {
            $this->getShips();
        }

        foreach ($this->ships as $i => $ship) {
            if (isset($ship["uuid"])) {
                $flag = $this->getShipbyUuid($ship["uuid"]);
                return;
            }

            if (isset($ship["id"])) {
                $flag = $this->getShipbyId($ship["id"]);
                return;
            }
        }

        $this->response .= "Did not find a ship. ";

        // Can't find a ship.
        return false;
    }

    function getShips()
    {
        $this->shipid_list = [];
        $this->ships = [];

        $things = $this->getThings("ship");

        if ($things === null) {
            return;
        }
        if ($things === true) {
            return;
        }
        $count = count($things);
        // See if a headcode record exists.
        //$findagent_thing = new Findagent($this->thing, 'ship');
        //$count = count($findagent_thing->thing_report['things']);
        $this->thing->log('Agent "Ship" found ' . $count . " ship Things.");

        if (!$this->is_positive_integer($count)) {
            // No ships found
        } else {
            foreach (array_reverse($things) as $uuid => $thing) {
                $associations = $thing->associations;

                $ship = [];
                $ship["associations"] = $associations;

                $variables = $thing->variables;
                if (isset($variables["ship"])) {
                    if (isset($variables["ship"]["refreshed_at"])) {
                        $ship["refreshed_at"] =
                            $variables["ship"]["refreshed_at"];
                    }

                    if (isset($variables["ship"]["text"])) {
                        $ship["text"] = $variables["ship"]["text"];
                    }

                    if (isset($variables["ship"]["state"])) {
                        $ship["state"] = $variables["ship"]["state"];
                    }

                    $ship["uuid"] = $uuid;
                    $ship["id"] = $this->idShip($uuid);

                    $this->ships[] = $ship;
                    $this->shipid_list[] = $uuid;
                }
            }
        }

        $refreshed_at = [];
        foreach ($this->ships as $key => $row) {
            $refreshed_at[$key] = $row["refreshed_at"];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->ships);

        return [$this->shipid_list, $this->ships];
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report["choices"] = $choices;
        $this->choices = $choices;
    }

    function makeWeb()
    {
        $web = null;
        if (isset($this->ship_thing)) {
            $web = "";
            $web .= "<b>" . ucwords($this->agent_name) . " Agent</b><br><p>";
            $web .= "<p>";

            $web .= $this->html_image;

            $web .= "<br>";

            $state_text = "X";
            if (isset($this->ship_thing->state)) {
                $state_text = strtoupper($this->ship_thing->state);
            }

            $web .= "SHIP IS " . $state_text;
            $web = "";
            $web .= "<b>" . ucwords($this->agent_name) . " Agent</b><br><p>";
            $web .= "<p>";
            $web .= '<a href="' . $this->link . '">';
            //        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/sig>
            $web .= $this->html_image;

            $web .= "</a>";
            $web .= "<br>";

            $state_text = "X";
            if (isset($this->ship_thing->state)) {
                $state_text = strtoupper($this->ship_thing->state);
            }

            $web .= "SHIP IS " . $state_text . "<br>";

            $id_text = "X";
            if (isset($this->ship_thing->uuid)) {
                $id_text = strtoupper($this->idShip($this->ship_thing->uuid));
            }

            $web .= "SHIP ID " . $id_text . "<br>";

            $web .= "<p>";
        }

        if (!isset($this->ships)) {
            $this->getShips();
        }
        $ship_table = '<div class="Table">
                 <div class="TableRow">
                 <div class="TableHead"><strong>ID</strong></div>
                 <div class="TableHead"><span style="font-weight: bold;">State</span></div>
                 <div class="TableHead"><strong>Text</strong></div></div>';

        if (isset($this->ships) and is_array($this->ships)) {
            $ship_text = "";
            $ships = [];
            foreach ($this->ships as $i => $ship) {
                //  if ($ship['text'] == "ship post") {
                if (isset($ship["uuid"]) and !isset($ship["id"])) {
                    $ship["id"] = $this->idShip($ship["uuid"]);
                }

                $ships[] = $ship;
            }

            $refreshed_at = [];
            foreach ($ships as $key => $row) {
                $refreshed_at[$key] = $row["id"];
            }
            array_multisort($refreshed_at, SORT_DESC, $ships);

            foreach ($ships as $i => $ship) {
                $ship_table .= '<div class="TableRow">';

                $ship_table .=
                    '<div class="TableCell">' .
                    strtoupper($ship["id"]) .
                    "</div>";

                $state = "X";
                if (isset($ship["state"])) {
                    $state = $ship["state"];
                }
                $ship_table .=
                    '<div class="TableCell">' . strtoupper($state) . "</div>";

                $text = "X";
                if (isset($ship["text"])) {
                    $text = $ship["text"];
                }

                $ship_table .=
                    '<div class="TableCell">' . strtoupper($text) . "</div>";
                $ship_table .= "</div>";
            }
            $ship_text = $ship_table . "</div><p>";
            //$web .= "ship_thing uuid" . $this->ship_thing->uuid;

            if (isset($this->ship_thing->variables->ship)) {
                $variables_text = "variables found";
                $ship_variables = $this->ship_thing->variables->ship;
                $ship_variables_html =
                    "" .
                    "state: " .
                    $ship_variables["state"] .
                    "<br>" .
                    "text: " .
                    $ship_variables["text"] .
                    "<br>" .
                    "refreshed at: " .
                    $ship_variables["refreshed_at"] .
                    "<br>" .
                    "";
                $web .= "<b>SHIP VARIABLES</b><br><p>";
                $web .= "ship thing uuid: " . $this->ship_thing->uuid . "<br>";
                $web .= $ship_variables_html . "<br>";
                $web .= "<p>";
            }

            if (isset($this->ship_thing->variables->ship["snapshot"])) {
                $ship_snapshot = $this->ship_thing->variables->ship["snapshot"];

                $ship_snapshot_html =
                    "" .
                    "current latitude: " .
                    $ship_snapshot["current_latitude"] .
                    $ship_snapshot["current_latitude_north_south"] .
                    "<br>" .
                    "current longitude: " .
                    $ship_snapshot["current_longitude"] .
                    $ship_snapshot["current_longitude_east_west"] .
                    "<br>" .
                    "to waypoint id: " .
                    $ship_snapshot["to_waypoint_id"] .
                    "<br>" .
                    "from waypoint id: " .
                    $ship_snapshot["from_waypoint_id"] .
                    "<br>" .
                    "range to waypoint: " .
                    $ship_snapshot["range_to_destination_in_nautical_miles"] .
                    "<br>" .
                    "bearing to waypoint: " .
                    $ship_snapshot["bearing_to_destination_in_degrees_true"] .
                    "<br>" .
                    "GPS timestamp: " .
                    $ship_snapshot["time_stamp"] .
                    "<br>" .
                    "GPS datestamp: " .
                    $ship_snapshot["date_stamp"] .
                    "<br>" .
                    "GPS last fix timestamp: " .
                    $ship_snapshot["fix_time"] .
                    "<br>" .
                    "GPS visible satellites: " .
                    implode(" ", $ship_snapshot["SV_IDs"]) .
                    "<br>" .
                    "";

                $snapshot_text = "merp";
                $web .= "<b>SNAPSHOT</b><br><p>" . $ship_snapshot_html;
                $web .= "<p>";
            }

            $web .= "<b>SHIPS FOUND</b><br><p>" . $ship_text;
            $web .= "<p>";

            $web .= "<b>INFO</b><br><p>";
            $this->makeInfo();
            $web .= $this->info;
        }

        if (isset($this->associations->thing->thing->associations)) {
            $web .= "<p>";
            $association_text = "";

            $associations_array = json_decode(
                $this->associations->thing->thing->associations,
                true
            );
            foreach ($associations_array as $agent_name => $associations) {
                foreach ($associations as $i => $association_uuid) {
                    $association_text .=
                        strtoupper(
                            $this->idShip($this->idShip($association_uuid))
                        ) .
                        " " .
                        $agent_name .
                        "<br>";
                }
            }

            $web .= "<b>ASSOCIATIONS FOUND</b><br><p>" . $association_text;
        }

        $this->thing_report["web"] = $web;
    }

    public function makeInfo()
    {
        $id_text = "X";
        if (isset($this->ship_thing->uuid)) {
            $id_text = strtoupper($this->idShip($this->ship_thing->uuid));
        }

        $web = "";
        if (isset($this->ship_thing->uuid)) {
            if ($this->ship_thing->uuid == $this->uuid) {
                $web .=
                    "The FORGET button will delete this SHIP ID " .
                    $id_text .
                    ". There is no undo.<br>";
            } else {
                $web .=
                    "The FORGET button will not delete this SHIP ID " .
                    $id_text .
                    ". The SHIP will persist in the text CHANNEL. Text SHIP THING LINK for a forgettable link.";
            }
        } else {
            $web .= "There are no ships. Text NEW SHIP to create a ship.";
        }

        $this->info = $web;
        $this->thing_report["info"] = $web;
    }

    function makeLink()
    {
        $id_text = "X";
        if (isset($this->ship_thing->uuid)) {
            $id_text = strtoupper($this->idShip($this->ship_thing->uuid));
        }

        $ship_id = "ship" . "-" . $id_text;

        $link = $this->web_prefix . "thing/" . $this->uuid . "/ship";

        if (isset($this->ship_thing->uuid) and $this->show_uuid == "on") {
            $uuid = $this->ship_thing->uuid;
            $link = $this->web_prefix . "thing/" . $uuid . "/" . $ship_id;
        }

        $this->link = $link;
        $this->thing_report["link"] = $link;
    }

    public function associationsShip()
    {
        $association_array = [];
        if (isset($this->associations->thing->thing->associations)) {
            $associations_array = json_decode(
                $this->associations->thing->thing->associations,
                true
            );
            foreach ($associations_array as $agent_name => $associations) {
                foreach ($associations as $i => $association_uuid) {
                    $association_array[] = [
                        "text" =>
                            strtoupper($this->idShip($association_uuid)) .
                            " " .
                            $agent_name,
                        "id" => $this->idShip($association_uuid),
                        "agent_name" => $agent_name,
                    ];
                }
            }
        }

        return $association_array;
    }

    function makeHelp()
    {
        if (!isset($this->ship_thing->state)) {
            $this->thing_report["help"] = "No ship thing found.";
            return;
        }
        // Get the latest Help ship.
        if (!isset($this->thing_report["help"])) {
            $this->helpShip($this->ship_thing->state);
        }
    }

    function makeTXT()
    {
        $ship_id = "X";
        if (isset($this->ship["id"])) {
            $ship_id = $this->ship["id"];
        }
        $txt = "This is SHIP POLE " . $ship_id . ". ";

        $state = "X";
        if (isset($this->ship_thing->state)) {
            $state = $this->ship_thing->state;
        }

        $txt .= "There is a " . strtoupper($state) . " SHIP. ";
        if ($this->verbosity >= 5) {
            $txt .=
                "It was last refreshed at " . $this->current_time . " (UTC).";
        }
        $txt .= "\n";
        foreach ($this->ships as $i => $ship) {
            $txt .= $this->textShip($ship);
        }

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    function makeSMS()
    {
        $this->makeLink();

        $state = "X";
        if (isset($this->ship_thing->state)) {
            $state = $this->ship_thing->state;
        }

        $ship_id = "X";
        if (isset($this->ship_thing->uuid)) {
            $ship_id = $this->ship_thing->uuid;
            $ship_nuuid = strtoupper(substr($ship_id, 0, 4));
            $ship_id = $this->idShip($ship_id);

            if ($this->show_uuid == "on" and $this->show_link == "off") {
                $ship_id = $this->ship_thing->uuid;
            }
        }

        $state_text = "X";
        if ($state != null) {
            $state_text = strtoupper($state);
        }

        $sms_message = "SHIP " . strtoupper($ship_id) . " IS " . $state_text;

        $sms_message .= " | ";

        if ($this->verbosity > 6) {
            $sms_message .=
                " | previous state " . strtoupper($this->previous_state);
            $sms_message .= " state " . strtoupper($this->state);
            $sms_message .=
                " requested state " . strtoupper($this->requested_state);
            $sms_message .=
                " current node " .
                strtoupper($this->base_thing->choice->current_node);
        }

        if ($this->verbosity > 0) {
            //    $sms_message .= " | ship id " . strtoupper($this->ship['id']);
        }

        if (strtolower($this->input) == "ships") {
            $sms_message .= " Active ships: ";
            foreach ($this->ships as $i => $ship) {
                if (isset($ship["id"])) {
                    $sms_message .= strtoupper($ship["id"]) . " ";
                    $sms_message .= strtoupper($ship["state"]) . " / ";
                } elseif (isset($ship["uuid"])) {
                    $sms_message .= $this->idShip($ship["uuid"]) . " ";
                    $sms_message .= strtoupper($ship["state"]) . " / ";
                }
            }
        }

        if ($this->verbosity > 2) {
            if ($this->state == "red") {
                $sms_message .= " | MESSAGE Green";
            }

            if ($this->state == "green") {
                $sms_message .= " | MESSAGE Red";
            }
        }
        $sms_message .= "" . trim($this->response);

        if ($this->show_link == "on") {
            $sms_message .= " " . $this->link;
        }

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    function makeMessage()
    {
        $state = "X";
        if (isset($this->ship_thing->state)) {
            $state = $this->ship_thing->state;
        }

        $message =
            "This is a SHIP POLE.  The ship is a " .
            trim(strtoupper($state)) .
            " SHIP. ";

        if ($state == "red") {
            $message .= "It is a BAD time at the moment. ";
        }

        if ($state == "green") {
            $message .= "It is a GOOD time now. ";
        }

        $this->message = $message;
        $this->thing_report["message"] = $message;
    }

    public function makeImage()
    {
        $state = "X";
        if (isset($this->ship_thing->state)) {
            $state = $this->ship_thing->state;
        }

        // Create a 55x30 image

        $this->image = imagecreatetruecolor(60, 125);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        $this->light_grey = imagecolorallocate($this->image, 210, 210, 210);
        $this->dark_grey = imagecolorallocate($this->image, 64, 64, 64);

        $this->red = imagecolorallocate($this->image, 231, 0, 0);

        $this->yellow = imagecolorallocate($this->image, 255, 239, 0);
        $this->green = imagecolorallocate($this->image, 0, 129, 31);

        $this->color_palette = [$this->red, $this->yellow, $this->green];

        // Draw a white rectangle
        if (!isset($state) or $state == false) {
            $color = $this->grey;
        } else {
            if (isset($this->{$state})) {
                $color = $this->{$state};
            } elseif (isset($this->{"ship_" . $state})) {
                $color = $this->{"ship_" . $state};
            }
        }
        $ship_id = "X";
        if (isset($this->ship_thing->uuid)) {
            $ship_id = $this->ship_thing->uuid;
        }
        $ship_nuuid = strtoupper(substr($ship_id, 0, 4));
        $ship_id = $this->idShip($ship_id);

        $text = strtoupper($ship_id);

        $width = imagesx($this->image);
        $height = imagesy($this->image);

        $font = $this->default_font;

        $font = null; //

        if (file_exists($font)) {
            // Add some shadow to the text
            //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);

            $size = 8;
            $angle = 90;

            $pad = 0;
            $vertical_text = false;

            if ($vertical_text) {
                $angle = 90;

                imagettftext(
                    $this->image,
                    $size,
                    $angle,
                    ($width * 95) / 100,
                    ($height * 95) / 100,
                    $this->light_grey,
                    $font,
                    $text
                );
            } else {
                $angle = 0;

                imagettftext(
                    $this->image,
                    $size,
                    $angle,
                    ($width * 50) / 100,
                    ($height * 97) / 100,
                    $this->light_grey,
                    $font,
                    $text
                );
            }
        } else {
            /*
            $flag_nuuid = "X";
            if (isset($this->flag->nuuid)) {
                $flag_nuuid = $this->flag_nuuid;
            }

            imagestring(
                $this->image,
                2,
                150,
                100,
                $flag_nuuid,
                $textcolor
            );
*/
            imagestring(
                $this->image,
                2,
                ($width * 6) / 10,
                ($height * 90) / 100,
                $text,
                $this->light_grey
            );
        }

        // Bevel top of ship image

        $points = [0, 0, 6, 0, 0, 6];
        imagefilledpolygon($this->image, $points, 3, $this->white);

        $points = [60, 0, 60 - 6, 0, 60, 6];
        imagefilledpolygon($this->image, $points, 3, $this->white);

        $green_x = 30;
        $green_y = 50;

        $red_x = 30;
        $red_y = 100;

        $yellow_x = 30;
        $yellow_y = 75;

        $double_yellow_x = 30;
        $double_yellow_y = 25;

        imagefilledellipse(
            $this->image,
            $green_x,
            $green_y,
            20,
            20,
            $this->dark_grey
        );

        imagefilledellipse(
            $this->image,
            $red_x,
            $red_y,
            20,
            20,
            $this->dark_grey
        );

        imagefilledellipse(
            $this->image,
            $yellow_x,
            $yellow_y,
            20,
            20,
            $this->dark_grey
        );
        imagefilledellipse(
            $this->image,
            $double_yellow_x,
            $double_yellow_y,
            20,
            20,
            $this->dark_grey
        );

        if ($state == "green") {
            imagefilledellipse(
                $this->image,
                $green_x,
                $green_y,
                20,
                20,
                $this->green
            );
        }

        if ($state == "red") {
            imagefilledellipse(
                $this->image,
                $red_x,
                $red_y,
                20,
                20,
                $this->red
            );
        }

        if ($state == "yellow") {
            imagefilledellipse(
                $this->image,
                $yellow_x,
                $yellow_y,
                20,
                20,
                $this->yellow
            );
        }

        if ($state == "double yellow") {
            imagefilledellipse(
                $this->image,
                $yellow_x,
                $yellow_y,
                20,
                20,
                $this->yellow
            );
            imagefilledellipse(
                $this->image,
                $double_yellow_x,
                $double_yellow_y,
                20,
                20,
                $this->yellow
            );
        }
    }

    public function uuidShip()
    {
        $this->show_uuid = "on";
    }

    public function linkShip()
    {
        $this->show_link = "on";
    }

    public function makePNG()
    {
        if (!isset($this->image)) {
            $this->makeImage();
        }
        $agent = new Png($this->thing, "png");

        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;
        $this->PNG_embed = $agent->PNG_embed;
        $this->thing_report["png"] = $agent->PNG;
    }

    public function makeJPEG()
    {
        if (!isset($this->image)) {
            $this->makeImage();
        }
        $agent = new JPEG($this->thing, "jpeg");

        $agent->makeJPEG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->JPEG = $agent->JPEG;
        $this->JPEG_embed = $agent->JPEG_embed;
        $this->thing_report["jpeg"] = $agent->JPEG;
    }

    // Load a variable into memory.
    // Refactor to Arr
    public function variableShip($variable_array)
    {
        if (!isset($this->ship_thing)) {
            $this->newShip();
        }

        if (!isset($this->ship_thing->variables->snapshot)) {
            $this->ship_thing->variables->snapshot = new \stdClass();
        }

if (isset($variable_array['transducers'])) {
foreach($variable_array['transducers'] as $i=>$transducer) {
        if (!isset($this->ship_thing->variables->snapshot->transducers)) {
            $this->ship_thing->variables->snapshot->transducers = new \stdClass();
        }


$this->ship_thing->variables->snapshot->transducers->{$transducer['sensor_id']} = $transducer;

}
return;
}

// Otherwise normal.

        foreach ($variable_array as $variable_name => $variable_value) {

if ($variable_name == 'SVs') {
//$this->ship_thing->variables->snapshot->{$variable_name} = key($variable_value);
foreach($variable_value as $SV_number => $SV_value) {

if ($SV_number == null or $SV_number == "") {continue;}

$this->ship_thing->variables->snapshot->{$variable_name}[$SV_number] = $SV_value;

}
continue;
}

            $this->ship_thing->variables->snapshot->{$variable_name} = $variable_value;
        }

    }

    public function snapshotShip($text = null)
    {
        if (isset($this->ship_thing->variables->snapshot)) {
            $this->response .= "Saw a snapshot variable. ";
        } else {
            $this->response .= "Did not see a snapshot variable. ";
        }
    }

    public function readShip($text = null)
    {
$unrecognized_sentences = [];
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
            if ($nmea_response["sentence_identifier"] == "\$THXDR") {

//            if (substr($nmea_response["sentence_identifier"],2,3) == "XDR") {
                $transducer_id = substr($nmea_response["sentence_identifier"],1,2);

foreach($nmea_response as $key=>$value) {
if ($this->isUuid($key)) {
//}

//}
//exit();
//                $transducer_id = substr($nmea_response["sentence_identifier"],1,2);
//$transducer_id = $key;
$transducer_id = $this->thing->getUUid();
                if (!isset($transducers)) {$transducers = [];}
                //$transducers[$transducer_id] = $nmea_response["transducers"];
                $transducers[$transducer_id] = $value["transducers"];
}
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

                $last_fix_time = $nmea_response["fix_time"];

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
    }

    public function readSubject($input = null)
    {
        $keywords = [
            "ship",
            "ahead",
            "astern",
            "port",
            "starboard",
            "snapshot",
            "report",
            "under way using engine",
            "at anchor",
            "moored",
            "aground",
            "engaged in fishing",
            "under way sailing",
            "not under command",
            "towing astern",
            "pushing ahead or alongside",
            "restricted manoeuverability",
            "constrained by her draft",
            "not defined",
            "uuid",
            "thing",
            "link",
            "uri",
            "url",
            "web",
            "x",
            "X",
            "list",
            "new",
            "make",
            "last",
            "next",
        ];

        usort($keywords, function ($a, $b) {
            $countA = $this->countNgrams($a);
            $countB = $this->countNgrams($b);

            $diff = $countB - $countA;
            return $diff;
            //return $countA < $countB;
        });
        if ($input == null) {
            $input = $this->input;
        }
        $filtered_input = $this->assert($input);
        $prior_uuid = null;

        //        $pieces = explode(" ", strtolower($input));
        //        $input = strtolower($this->subject);

        $ngram_agent = new Ngram($this->thing, "ngram");
        $pieces = [];
        $arr = $ngram_agent->getNgrams(strtolower($input), 4);
        $pieces = array_merge($pieces, $arr);
        $arr = $ngram_agent->getNgrams(strtolower($input), 3);
        $pieces = array_merge($pieces, $arr);
        $arr = $ngram_agent->getNgrams(strtolower($input), 2);
        $pieces = array_merge($pieces, $arr);
        $arr = $ngram_agent->getNgrams(strtolower($input), 1);
        $pieces = array_merge($pieces, $arr);

        if (count($pieces) == 1) {
            if ($input == $this->keyword) {
                //$this->response .= "Got the current ship. ";
                return;
            }

            if ($input == "ships") {
                $this->response .= "Got active ships. ";
                return;
            }
        }
        /*
        if (count($pieces) == 3) {
            if ($input == "ship double yellow") {
                $this->changeShip('double yellow');
                return;
            }
        }
*/
        $uuid_agent = new Uuid($this->thing, "uuid");
        $t = $uuid_agent->extractUuid($input);
        if (is_string($t)) {
            $this->getShipbyUuid($t);
            return;
        }

        // Okay maybe not a full UUID. Perhaps a NUUID.
        $nuuid_agent = new Nuuid($this->thing, "nuuid");

        $t = $nuuid_agent->extractNuuid($input);

        if (is_string($t)) {
            $response = $this->getShipbyId($t);

            if ($response != true) {
                $this->response .= "Got ship " . $t . ". ";

                return;
            }

            $this->response .= "Match not found. ";
        }

        // Lets think about ships.
        // A ship is connected to another ship.  By radio. Or communications.

        // So look up the ship in associations.

        // ship - returns the uuid and the state of the current ship

        if ($this->channel_name == "web") {
            $this->response .= "Made a web ship panel. ";
            // Do not effect a state change for web views.
            return;
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "uuid":
                        case "thing":
                            $this->uuidShip();
                            break;
                        case "url":
                        case "uri":
                        case "web":
                        case "link":
                            $this->linkShip();
                            break;

                        case "under way using engine":
                            $this->changeShip("under way using engine");
                            return;
                        case "at anchor":
                            $this->changeShip("at anchor");
                            return;

                        case "moored":
                            $this->changeShip("moored");
                            return;

                        case "aground":
                            $this->changeShip("aground");
                            return;

                        case "engaged in fishing":
                            $this->changeShip("engaged in fishing");
                            return;

                        case "under way sailing":
                            $this->changeShip("under way sailing");
                            return;
                        case "not under command":
                            $this->changeShip("not under command");
                            return;
                        case "towing astern":
                            $this->changeShip("towing astern");
                            return;
                        case "pushing ahead or alongside":
                            $this->changeShip("pushing ahead or alongside");
                            return;
                        case "not defined":
                            $this->changeShip("not defined");
                            return;

                        case "restricted manoeuverability":
                            $this->changeShip("restricted manoeuverability");
                            return;

                        case "constrained by her draft":
                            $this->changeShip("constrained by her draft");
                            return;

                        case "x":
                            $this->changeShip("X");
                            return;

                        case "list":
                            $this->getShips();
                            return;
                        case "ahead":
                        case "astern":
                        case "port":
                        case "starboard":
                            $this->helmShip($filtered_input);
                            return;
                        case "make":
                        case "new":
                            $this->newShip();
                            return;
                        case "snapshot":
                        case "report":
                            $this->snapshotShip();
                            return;
                        case "back":

                        case "next":

                        default:
                    }
                }
            }
        }

        //$this->readShip();
        $this->response .= "Did not see a command. ";

        // devstack
        //return "Message not understood";
        //return false;
    }
}
