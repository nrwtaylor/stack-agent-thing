<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Carpool extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->keyword = "carpool";
        $this->test = "Development code"; // Always

        $this->node_list = ["carpool" => ["on" => ["off"]]];

        $this->web_prefix = $this->thing->container["stack"]["web_prefix"];

        $this->link = $this->web_prefix . "thing/" . $this->uuid . "/carpool";

        $this->current_time = $this->thing->json->time();
        $this->thing_report["help"] =
            "This is your Carpool. Try CARPOOL ON. CARPOOL OFF. PLACE IS GILMORE. FLAG IS RAINBOW.";
        $this->thing_report["info"] =
            "Provides a web link to share with the status of the car pool.";
    }

    function run()
    {
    }

    public function set($requested_state = null)
    {
        if ($requested_state == null) {
            return true;
        }

        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

        $this->variables_thing->setVariable("state", $requested_state);
        $this->variables_thing->setVariable(
            "refreshed_at",
            $this->current_time
        );

        $this->thing->choice->Choose($requested_state);

        $this->thing->choice->save($this->keyword, $requested_state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;
    }

    public function get()
    {
        $this->variables_thing = new Variables(
            $this->thing,
            "variables carpool " . $this->from
        );

        if (!isset($this->requested_state)) {
            if (!isset($this->state)) {
                $this->requested_state = "X";
            } else {
                $this->requested_state = $this->state;
            }
        }

        $this->previous_state = $this->variables_thing->getVariable("state");
        $this->refreshed_at = $this->variables_thing->getVariables(
            "refreshed_at"
        );

        $this->thing->choice->Create(
            $this->keyword,
            $this->node_list,
            $this->previous_state
        );
        $this->thing->choice->Choose($this->requested_state);

        $this->state = $this->thing->choice->current_node;

        $this->state = $this->previous_state;

        // Bring in stuff
        $this->getPlace();
        $this->getFlag();
        $this->getHeadcode();

        $this->getQuantity();

        $this->destination = new Destination($this->thing, "destination");

        $this->getIdentity();

        $this->clocktime = new Clocktime($this->thing, "clocktime");
        $this->channel = new Channel($this->thing, "channel");
        $this->context = new Context($this->thing, "group");
    }

    function selectChoice($choice = null)
    {
        if ($choice == null) {
            return $this->state;
        }

        $this->thing->log(
            'chose "' . $choice . '".'
        );
        $this->set($choice);

        return $this->state;
    }

    public function respondResponse()
    {
        $this->makeCarpool();

        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report["email"] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    function makeChoices()
    {
        $this->node_list = ["carpool" => ["privacy", "warranty", "github"]];

        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "carpool"
        );

        $this->choices = $this->thing->choice->makeLinks("carpool");

        $this->thing_report["choices"] = $this->choices;
    }

    function makeMessage()
    {
        switch ($this->state) {
            case "off":
                $m = "The carpool is not running";
                break;
            case "on":
                if (!isset($this->place->place_name)) {
                    $place = "NOT SET";
                } else {
                    $place = strtoupper($this->place->place_name);
                }

                $m =
                    "The carpool is at " . strtoupper($this->place->place_name);
                $m .= " showing a " . strtoupper($this->flag->state) . " flag.";
                $m .=
                    " Carpool " .
                    strtoupper($this->headcode->head_code) .
                    " is running.";
                break;
            default:
                $m = "The carpool is not running.";
        }

        $this->message = $m;
        $this->thing_report["message"] = $m;
    }

    function getPlace()
    {
        $this->place = new Place($this->thing, "place");

        if (
            !isset($this->place->place_name) or
            $this->place->place_name == false
        ) {
            $this->place->place_name = "X";
        }
    }

    function getQuantity()
    {
        $this->quantity = new Quantity($this->thing, "quantity");
    }

    function getHeadcode()
    {
        $this->headcode = new Headcode($this->thing, "headcode");
        if (!isset($this->headcode->head_code)) {
            $this->headcode->head_code = "X";
        }
    }

    function getFlag()
    {
        $this->flag = new Flag($this->thing, "flag");

        if (!isset($this->flag->state) or $this->flag->state == false) {
            $this->flag->state = "X";
        }

        $this->thing->log(
            $this->agent_prefix . " got a flag " . $this->flag->state . "."
        );
    }

    function getIdentity()
    {
        $this->identity = new Identity($this->thing, "identity");
        if (!isset($this->identity->state) or $this->identity->state == false) {
            $this->identity->state = "off";
        }

        $this->thing->log(
            $this->agent_prefix .
                " got identity state " .
                $this->identity->state .
                "."
        );
    }

    function makeSMS()
    {
        if ($this->state == false) {
            $text = "X";
        } else {
            $text = $this->state;
        }
        $sms_message = "CARPOOL IS " . strtoupper($text);

        switch ($this->state) {
            case "off":
                $sms_message .= " | The carpool is not running.";
                break;
            case "on":
                if ($this->flag->state == false) {
                    $flag_state = "X";
                } else {
                    $flag_state = $this->flag->state;
                }
                $sms_message .= " | flag " . strtoupper($flag_state);
                $sms_message .=
                    " | headcode " . strtoupper($this->headcode->head_code);

                if ($this->place->place_name == false) {
                    $place_name = "X";
                } else {
                    $place_name = $this->place->place_name;
                }
                $sms_message .= " | place " . strtoupper($place_name);

                $sms_message .= " | link " . $this->link;

                break;
            default:
                $sms_message .= " | The carpool is not running.";
        }

        $sms_message .=
            " | nuuid " .
            substr($this->variables_thing->variables_thing->uuid, 0, 4);

        $sms_message .= " | TEXT HELP";

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    function makeWeb()
    {
        $head = '<p class="description">';
        $foot = "</p>";

        if ($this->state == "on") {
            $link = $this->web_prefix . "thing/" . $this->uuid . "/agent";

            $link_txt =
                $this->web_prefix . "thing/" . $this->uuid . "/carpool.txt";

            $web =
                "This is an experimental carpool tool hosted by Stackr Interactive - a small AI and Games start-up in Burnaby, British Columbia. ";

            $web .= "<br><br>";

            if ($this->identity->state == "on") {
                $web .=
                    "You can talk directly to the carpool with " .
                    $this->channel->channel_name .
                    " " .
                    $this->from .
                    ".<br><br>";
            } else {
                $web .= "The carpool has not shared contact details.<br><br>";
            }

            $web .= $this->place->html_image;
            $web .= "<br>";

            $web .= "<br>";

            $web .=
                "The carpool is going to be arriving at " .
                strtoupper($this->place->place_name) .
                " ";

            $web .=
                " at " .
                str_pad($this->clocktime->hour, 2, "0", STR_PAD_LEFT) .
                ":" .
                str_pad($this->clocktime->minute, 2, "0", STR_PAD_LEFT) .
                ".<br>";

            $quantity = "X";
            if (isset($this->quantity->quantity)) {
                $quantity = $this->quantity->quantity;
            }

            $web .=
                "The driver says there are " .
                $quantity .
                " seats available.<br>";

            $web .=
                "The driver is showing a " .
                $this->flag->state .
                " flag.<br><br>";

            $destination_name = "NOT SET";
            if (isset($this->destination->destination_name)) {
                $destination_name = $this->destination->destination_name;
            }

            $web .= "The common destination is " . $destination_name . ".";
            $web .= "<br>";

            $flag_html_image = "NOT SET";
            if (isset($this->flag->html_image)) {
                $flag_html_image = $this->flag->html_image;
            }

            $web .= $flag_html_image;

            $web .= "<br><br>";

            $web .=
                "The link you have been shared tells you about this carpool. ";

            $web .= "Shared with you by the car pool driver and/or guests. ";

            $web .=
                "Users of this service are guests of the individual carpool driver.";

            $web .= "<br><br>";

            $web .= "<b>" . ucwords($this->agent_name) . " Agent</b><br>";

            $refreshed_at = max(
                strtotime($this->flag->refreshed_at),
                strtotime($this->place->refreshed_at)
            );

            $refreshed_at = max(
                strtotime($this->flag->refreshed_at),
                strtotime($this->place->refreshed_at),
                strtotime($this->quantity->refreshed_at)
            );

            $clock_time_refreshed_at = null;
            if (isset($this->clocktime->refreshed_at)) {
                $clock_time_refreshed_at = strtotime(
                    $this->clocktime->refreshed_at
                );
            }

            $refreshed_at = max(
                strtotime($this->flag->refreshed_at),
                strtotime($this->place->refreshed_at),
                strtotime($this->quantity->refreshed_at),
                $clock_time_refreshed_at
            );

            $ago = $this->thing->human_time(
                strtotime($this->thing->time()) - $refreshed_at
            );

            $web .= "Last heard from this carpool about " . $ago . " ago.";

            $web .= "<br><br>";

            $web .= "<b>" . ucwords($this->agent_name) . " Information</b><br>";

            $web .=
                "The link you have been shared gives you the carpool's state. ";

            $web .= "Shared with you by the car pool driver. ";

            $web .=
                "Users of this service are guests of the individual carpool driver.";

            $web .= "<br><br>";

            $web .=
                "The Province of British Columbia says that traveling together in a single vehicle can save you both money, and is also better for the environment.";
            $web .=
                '<a href="https://www2.gov.bc.ca/gov/content/family-social-supports/seniors/transportation/carpooling-and-car-sharing">';

            $web .=
                "https://www2.gov.bc.ca/gov/content/family-social-supports/seniors/transportation/carpooling-and-car-sharing";

            $web .= "</a>";

            $web .= "<br><br>";

            $web .=
                "A car pool vehicle is exempt from the provisions of the Passenger Transportation Act. ";
            $web .=
                "Section 2 (below) of the Transportation Act describes a car pool vehicle.<br> ";

            $web .=
                '<a href="http://www.bclaws.ca/Recon/document/ID/freeside/266_2004">';

            $web .= "http://www.bclaws.ca/Recon/document/ID/freeside/266_2004";

            $web .= "</a>";

            $web .= "<br><br>";

            $web .= "
(2) A motor vehicle that can accommodate a driver and not more than 11 passengers is a car pool vehicle on any day if<br>
(a) on that day, the motor vehicle is used for no purpose other than to transport passengers on one return trip between<br>
(i) the residences of any or all of the driver and the passengers, and<br>
(ii) the respective places of employment of the driver and passengers, or a common destination, and<br>
(b) neither the driver nor the operator receives any compensation for that transportation other than contributions for
operating costs, which contributions do not, in the aggregate, exceed the operating costs that are attributable to the return trip referred to in paragraph (a).";
        }

        if ($this->state != "on") {
            $web = "<b>" . ucwords($this->agent_name) . " Agent</b><br>";
            $web .= $this->message;
            $web .= "<br>";
        }
        $this->thing_report["web"] = $head . $web . $foot;
        return;

        $web .= $this->sms_message;
        $web .= "<br>";

        $web .= "<br>";

        $web .= "<br>";

        $ago = $this->thing->human_time(
            strtotime($this->thing->time()) - strtotime($this->refreshed_at)
        );
        $web .= "Last asserted about " . $ago . " ago.";

        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    function makeCarpool()
    {
        $this->flag->makePNG();
        $this->headcode->makePNG();
        $this->place->makePNG();
    }

    function assertCarpool($input)
    {
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "carpool is")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("carpool is")
            );
        } elseif (($pos = strpos(strtolower($input), "carpool")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("carpool"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $place = $this->getPlace($filtered_input);
        if ($place) {
            //true so make a place
            $this->makePlace(null, $filtered_input);
        }
    }

    public function readSubject()
    {
        $this->response = null;

        $keywords = ["off", "on"];

        $input = $this->input;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == $this->keyword) {
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "off":
                            $this->thing->log("switch off");
                            $this->selectChoice("off");
                            return;
                        case "on":
                            $this->selectChoice("on");
                            return;
                        case "next":
                        default:
                    }
                }
            }
        }

        if (isset($this->carpool_code) and $this->carpool_code != null) {
            $this->getPlace($this->place->place_code);
            $this->thing->log(
                $this->agent_prefix .
                    "using extracted place_code " .
                    $this->place->place_code .
                    ".",
                "INFORMATION"
            );
            $this->response =
                $this->place->place_code . " used to retrieve a Place.";

            return;
        }

        if (isset($this->carpool_name) and $this->carpool_name != null) {
            $this->getCarpool($this->carpool_name);

            $this->thing->log(
                $this->agent_prefix .
                    "using extracted place_name " .
                    $this->place->place_name .
                    ".",
                "INFORMATION"
            );
            $this->response =
                strtoupper($this->place->place_name) . " retrieved.";
            $this->assertCarpool($this->place->place_name);
            return;
        }

        if (isset($this->last_place_code) and $this->last_place_code != null) {
            $this->getPlace($this->last_place_code);
            $this->thing->log(
                $this->agent_prefix .
                    "using extracted last_place_code " .
                    $this->last_place_code .
                    ".",
                "INFORMATION"
            );
            $this->response =
                "Last place " .
                $this->last_place_code .
                " used to retrieve a Place.";

            return;
        }

        // so we get here and this is null placename, null place_id.
        // so perhaps try just loading the place by name

        $place = strtolower($this->subject);

        if (!$this->getPlace(strtolower($place))) {
            // Place was found
            // And loaded
            $this->response = $place . " used to retrieve a Place.";

            return;
        }

        $this->makePlace(null, $place);
        $this->thing->log(
            $this->agent_prefix .
                "using default_place_code " .
                $this->default_place_code .
                ".",
            "INFORMATION"
        );

        $this->response = "Made a Place called " . $place . ".";
        return;
    }
}
