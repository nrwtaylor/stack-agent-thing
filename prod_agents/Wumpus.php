<?php
/**
 * Wumpus.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

use setasign\Fpdi;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Wumpus extends Agent
{
    public $var = "hello";

    // Lots of work needed here.
    // Currently has persistent coordinate movement (north, east, south, west).
    // State selection is dev.

    // Add a place array. Base it off a 20-node shape.
    // Get path selecting throught the array for Wumpus and Player(s) working.

    /**
     *
     */
    function init()
    {
        $this->test = "Development code";

        // Load in some characterizations.
        $this->short_name = $this->thing->container["stack"]["short_name"];

        $this->sms_seperator =
            $this->thing->container["stack"]["sms_separator"];
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

        $this->primary_place = "lair";

        $this->created_at = $this->thing->created_at;

        $this->sqlresponse = null;

        $this->node_list = [
            "start" => [
                "inside nest" => [
                    "nest maintenance" => [
                        "patrolling" => "foraging",
                        "foraging",
                    ],
                ],
                "midden work" => "foraging",
            ],
        ];

        $this->initWumpus();

        /*
        $this->caves = array("1"=>array("2", "3", "4"),
			 "2"=>array("1", "5", "6"),
			 "3"=>array("1", "7", "8"),
			 "4"=>array("1", "9", "10"),
             "5"=>array("2", "9", "11"),
             "6"=>array("2", "7", "12"),
            "7"=>array("3", "6", "13"),
            "8"=>array("3", "10", "14"),
            "9"=>array("4", "5", "15"),
            "10"=>array("4", "8", "16"),
            "11"=>array("5", "12", "17"),
            "12"=>array("6", "11", "18"),
            "13"=>array("7", "14", "18"),
            "14"=>array("8", "13", "19"),
            "15"=>array("9", "16", "17"),
            "16"=>array("10", "15", "19"),
            "17"=>array("11", "20", "15"),
            "18"=>array("12", "13", "20"),
            "19"=>array("14", "16", "20"),
            "20"=>array("17", "18", "19"));
*/

        /*
        $this->caves = array("1"=>array("8", "20", "12"),
            "2"=>array("5", "9", "13"),
            "3"=>array("9", "11", "15"),
            "4"=>array("5", "7", "14"),
            "5"=>array("2", "4", "10"),
            "6"=>array("14", "16", "19"),
            "7"=>array("4", "10", "14"),
            "8"=>array("1", "12", "17"),
            "9"=>array("2", "3", "11"),
            "10"=>array("5", "7", "20"),
            "11"=>array("3", "9", "20"),
            "12"=>array("1", "8", "17"),
            "13"=>array("2", "15", "16"),
            "14"=>array("4", "6", "7"),
            "15"=>array("3", "13", "18"),
            "16"=>array("6", "13", "18"),
            "17"=>array("8", "12", "19"),
            "18"=>array("15", "16", "19"),
            "19"=>array("6", "17", "18"),
            "20"=>array("1", "10", "11"));
*/
        // devstack load these arrays from a text file

        $this->caves = [
            "1" => ["8", "11", "20"],
            "2" => ["3", "10", "13"],
            "3" => ["2", "8", "9"],
            "4" => ["7", "14", "16"],
            "5" => ["6", "7", "9"],
            "6" => ["5", "12", "14"],
            "7" => ["4", "5", "14"],
            "8" => ["1", "3", "17"],
            "9" => ["3", "5", "11"],
            "10" => ["2", "11", "20"],
            "11" => ["1", "9", "10"],
            "12" => ["6", "17", "20"],
            "13" => ["2", "15", "18"],
            "14" => ["4", "6", "7"],
            "15" => ["13", "16", "18"],
            "16" => ["4", "15", "19"],
            "17" => ["8", "12", "19"],
            "18" => ["13", "15", "19"],
            "19" => ["16", "17", "18"],
            "20" => ["1", "10", "12"],
        ];

        $info =
            'The "Wumpus" agent provides an text driven interface to manage a 3-D coordinate on ' .
            $this->short_name;
        $info .=
            "from the web.  The Management suggests you explore the NEST MAINTENANCE button";

        //        // dev stack
        //        $t = new Input($this->thing, "wumpus");
        //        $this->run_flag = $t->input_agent;
    }

    /**
     *
     */
    public function run()
    {
        $this->getState();
        $this->getBottomlesspits();
        $this->doWumpus();
    }

    /**
     *
     */
    public function set()
    {
        $this->wumpus_tag = $this->entity_agent->nuuid;
        if (!isset($this->refreshed_at)) {
            $this->refreshed_at = $this->thing->time();
        }

        $variables = new Variables(
            $this->thing,
            "variables wumpus " . $this->from
        );

        $variables->setVariable("tag", $this->wumpus_tag);
        $variables->setVariable("refreshed_at", $this->refreshed_at);

        $this->entity_agent->Write(
            ["wumpus", "left_count"],
            $this->left_count
        );
        $this->entity_agent->Write(
            ["wumpus", "right_count"],
            $this->right_count
        );

        // Which cave is the Wumpus in?  And is it a number or a name?
        $this->entity_agent->Write(
            ["wumpus", "cave"],
            strval($this->x)
        );

        $this->entity_agent->choice->Choose($this->state);

        $this->state = $this->entity_agent->choice->load($this->primary_place);
    }

    /**
     *
     * @param unknown $crow_code (optional)
     * @return unknown
     */
    public function get($crow_code = null)
    {
        $this->variables_wumpus = new Variables(
            $this->thing,
            "variables wumpus " . $this->from
        );

        $this->wumpus_tag = $this->variables_wumpus->getVariable("tag");
        $this->refreshed_at = $this->variables_wumpus->getVariable(
            "refreshed_at"
        );

        if ($crow_code == null) {
            $crow_code = $this->wumpus_tag;
        }

        $this->getWumpus($crow_code);

        $this->current_time = $this->entity_agent->time();

        // Borrow this from iching
        $this->time_string = $this->entity_agent->Read([
            "wumpus",
            "refreshed_at",
        ]);

        if ($crow_code == null) {
            $crow_code = $this->uuid;
        }

        if ($this->time_string == false) {
            $this->time_string = $this->entity_agent->time();
            $this->entity_agent->Write(
                ["wumpus", "refreshed_at"],
                $this->time_string
            );
        }

        $this->refreshed_at = strtotime($this->time_string);

        $this->left_count = strtolower(
            $this->entity_agent->Read(["wumpus", "left_count"])
        );
        $this->right_count = $this->entity_agent->Read([
            "wumpus",
            "right_count",
        ]);
        $this->x = $this->entity_agent->Read(["wumpus", "cave"]);

        if ($this->left_count == false or ($this->left_count = "")) {
            $this->left_count = 0;
            $this->right_count = 0;
        }
        if ($this->right_count == false or ($this->right_count = "")) {
            $this->left_count = 0;
            $this->right_count = 0;
        }

        $this->state = $this->entity_agent->choice->load($this->primary_place);

        if ($this->state == false) {
            $this->state = "foraging";
        }

        return [$this->left_count, $this->right_count];
    }

    function getBottomlesspits()
    {
        // Get the place names of the locations of the bottomless pits
        $agent = new Bottomlesspits($this->entity_agent, "bottomless pits");

        $this->bottomless_pits = $agent->bottomless_pits;
    }

    /**
     *
     * @param unknown $requested_nuuid (optional)
     */
    private function getWumpus($requested_nuuid = null)
    {
        $entity_input = "get wumpus";
        if ($requested_nuuid != null) {
            $entity_input = "get wumpus " . $requested_nuuid;
        } else {
            $entity_input = "get wumpus";
        }

        $entity = new Entity($this->thing, $entity_input);
        $this->entity_agent = $entity->thing;

        $this->state = $this->entity_agent->choice->load("lair");
        $this->uuid = $this->entity_agent->uuid;
        $this->nuuid = $this->entity_agent->nuuid;

        $this->caveWumpus();

        $this->choices = $this->entity_agent->choice->makeLinks($this->state);
    }

    /**
     *
     */
    private function cavesWumpus()
    {
        if (isset($this->cave_names)) {
            return;
        }

        // Makes a one character dictionary

        $file = $this->resource_path . "wumpus/wumpus.txt";
        $contents = file_get_contents($file);

        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {
            $items = explode(",", $line);
            $this->cave_names[$items[0]] = $items[1];

            // do something with $line
            $line = strtok($separator);
        }
    }

    /**
     *
     */
    private function newsWumpus()
    {
        //if (isset($this->cave_names)) {return;}

        // Makes a one character dictionary

        $file = $this->resource_path . "wumpus/news.txt";
        $contents = file_get_contents($file);

        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {
            $items = explode(",", $line);
            $this->news = $items[2];
            break;

            // do something with $line
            $line = strtok($separator);
        }
    }

    /**
     *
     * @param unknown $cave_number (optional)
     */
    private function caveWumpus($cave_number = null)
    {
        $this->cavesWumpus();

        $cave_number = "X";

        if ($cave_number == null) {
            $cave_number = $this->x;
        }

        $cave_name = "A dark room";
        if (isset($this->cave_names[strval($cave_number)])) {
            $cave_name = $this->cave_names[strval($cave_number)];
        }
        $this->cave_name = $cave_name;
    }

    /**
     *
     */
    private function getState()
    {
        //        $this->state = $this->thing->choice->load($this->primary_place);
        //        $this->thing->choice->Create($this->primary_place, $this->node_list, $this->state);
        //        $this->thing->choice->Choose($this->state);

        //        $choices = $this->thing->choice->makeLinks($this->state);

        $this->state = $this->entity_agent->choice->load($this->primary_place);
        $this->entity_agent->choice->Create(
            $this->primary_place,
            $this->node_list,
            $this->state
        );
        $this->entity_agent->choice->Choose($this->state);

        $choices = $this->entity_agent->choice->makeLinks($this->state);

        //   $this->state = "AWAKE";
    }

    /**
     *
     */
    private function getClocktime()
    {
        $this->clocktime_agent = new Clocktime($this->thing, "clocktime");
    }

    /*
    private function getCoordinate()
    {
        $this->coordinate = new Coordinate($this->thing, "coordinate");

        $this->x = $this->coordinate->coordinates[0]['coordinate'][0];
        $this->y = $this->coordinate->coordinates[0]['coordinate'][1];

    }
*/

    /**
     *
     */
    private function getBar()
    {
        $this->thing->bar = new Bar($this->thing, "bar stack");
    }

    /**
     *
     */
    private function getTick()
    {
        $this->thing->tick = new Tick($this->thing, "tick");
    }

    /**
     *
     */
    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        // Generate SMS response
        // Generate email response.

        $this->choices = false;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["help"] =
            'This is the "Wumpus" Agent. It stumbles around Things.';
    }

    /**
     *
     */
    public function makeWeb()
    {
        //        return;
        // No web response for now.
        //        $test_message = "<b>WUMPUS " . strtoupper($this->thing->nuuid) . "" . ' NOW ';
        $test_message =
            "<b>WUMPUS " .
            strtoupper($this->entity_agent->nuuid) .
            "" .
            " NOW ";

        $test_message .= "AT ";
        // . strtoupper($this->x) . "" .
        //$test_message .= '<br>';

        if (isset($this->caves[strval($this->x)])) {
            $this->choices_text = "";
            $this->cave_list_text =
                trim(implode(" ", $this->caves[strval($this->x)])) . "";
        }

        $test_message .= strtoupper($this->cave_names[strval($this->x)]);

        //        $test_message .= "<b>WITH ROUTES TO " . strtoupper($this->cave_list_text) . "</b>" . '<br>';

        $test_message .= "</b><p>";

        //$test_message .= "".  nl2br($this->sms_message);
        $test_message .= "YOUR CHOICES ARE";
        $test_message .= "<p>";

        $test_message .= "PDF ";

        $link = $this->web_prefix . "thing/" . $this->uuid . "/wumpus.pdf";
        $test_message .= '<a href="' . $link . '">wumpus.pdf</a>';
        //$web .= " | ";

        $test_message .= "<br>";
        $test_message .= "<p>";

        $this->response = "";

        $current_cave = $this->x;

        trim($this->response);
        $test_message .= "<p>";
        //$this->caves[$current_cave];
        foreach ($this->caves[$current_cave] as $key => $cave) {
            $test_message .=
                "Place " .
                $cave .
                " is the  " .
                strtoupper($this->cave_names[$cave]) .
                "<br>";
        }
        //   $this->response .= "";

        if ($this->state != false) {
            $test_message .= "<p><b>Wumpus State</b>";

            $test_message .=
                '<br>Last thing heard: "' .
                $this->subject .
                '"<br>' .
                "The next Wumpus choices are [ " .
                $this->choices["link"] .
                "].";
            $test_message .= "<br>Lair state: " . $this->state;

            //$test_message .= '<br>left_count is ' . $this->left_count;
            //$test_message .= '<br>right count is ' . $this->right_count;

            $test_message .= "<br>" . $this->behaviour[$this->state] . "<br>";
            $test_message .=
                "<br>" . $this->thing_behaviour[$this->state] . "<br>";
            $test_message .= "<br>" . $this->litany[$this->state] . "<br>";
            $test_message .= "<br>" . $this->narrative[$this->state] . "<br>";
        }

        $refreshed_at = max($this->created_at, $this->created_at);
        $test_message .= "<p>";

        $ago = $this->thing->human_time(
            strtotime($this->entity_agent->time()) - strtotime($refreshed_at)
        );

        $test_message .= "<br>Thing happened about " . $ago . " ago.";

        $this->thing_report["web"] = $test_message;
    }

    function doWumpus()
    {
        if (isset($this->caves[strval($this->x)])) {
            $cave_names = $this->caves[strval($this->x)];
        }

        foreach ($this->bottomless_pits as $index => $bottomless_pit_name) {
            foreach ($cave_names as $index => $cave_name) {
                if (
                    strtolower(strval($this->x)) ==
                    strtolower($bottomless_pit_name)
                ) {
                    $agent = new Input($this->thing, "break");

                    $this->response .=
                        "You fell down a bottomless pit. Text RUN WUMPUS. ";
                    return;
                }

                if (
                    strtolower($cave_name) == strtolower($bottomless_pit_name)
                ) {
                    $this->response .= "You feel a draft. ";
                    break;
                }
            }
        }
    }

    public function makeInput()
    {
        $input_agent = new Input($this->thing, "wumpus");
        $input_agent->addInput("Input?");

        // dev stack
        //        $t = new Input($this->thing, "wumpus");
        //$this->wumpus_input_flag = $input_agent->input_agent;
        $this->wumpus_input_flag = $input_agent->input_state;

        $this->input = $input_agent->input_text;
    }

    /**
     *
     */

    public function makeChoices()
    {
        $this->state = $this->entity_agent->choice->load($this->primary_place);
        $choices = $this->entity_agent->choice->makeLinks($this->state);

        $this->choices = $choices;

        $this->choices_text = "";
        if ($this->choices["words"] != null) {
            $this->choices_text = strtoupper(
                implode(" / ", $this->choices["words"])
            );
        }

        //        $choices = $this->thing->choice->makeLinks();
        $choices = $this->entity_agent->choice->makeLinks();

        $this->choices = $choices;
        $this->thing_report["choices"] = $choices;
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
        $this->thing_report["message"] = $m;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $sms = "WUMPUS " . strtoupper($this->entity_agent->nuuid) . "";

        if (isset($this->state) and $this->state != false) {
            $sms .= " is " . strtoupper($this->state);
        }

        if (in_array($this->x, range(1, 20))) {
            $sms .= " is at ";
            //        $sms .= "(" . $this->x . ", " . $this->y . ")";
            $sms .= "(" . $this->x . ") ";
            $sms .= "" . trim(strtoupper($this->cave_names[$this->x])) . "";
        }

        if ($this->x == 0) {
            $sms .= " IS OUT OF BOUNDS. ";
        }

        $sms .= " \n" . $this->response;
        $sms .= "\n";

        if (strpos($this->web_prefix, "192.168") !== false) {
        } else {
            $sms .= $this->web_prefix . "thing/" . $this->uuid . "/wumpus" . "";

            $sms .= "\n";
        }

        $this->cave_list_text = "";
        $this->choices_text = "SPAWN";

        if (isset($this->caves[strval($this->x)])) {
            $this->choices_text = "";
            $this->cave_list_text =
                trim(implode(" ", $this->caves[strval($this->x)])) . "";
        }

        if ($this->wumpus_input_flag == "anticipate") {
            $sms .=
                "YOUR CHOICES ARE [ " .
                $this->cave_list_text .
                " " .
                $this->choices_text .
                "] ";
        } else {
            $sms .= "TEXT RUN WUMPUS.";
        }

        // TODO Show Wumpus input message.
        $sms .= $this->input;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     * @return unknown
     */
    public function makePDF()
    {
        $file = $this->resource_path . "wumpus/wumpus.pdf";
        if ($file === null or !file_exists($file)) {
            $this->thing_report["pdf"] = false;
            return $this->thing_report["pdf"];
        }

        $txt = $this->thing_report["sms"];

        // initiate FPDI
        $pdf = new Fpdi\Fpdi();

        $pdf->setSourceFile($file);

        $pdf->SetFont("Helvetica", "", 10);

        $tplidx1 = $pdf->importPage(1, "/MediaBox");

        $s = $pdf->getTemplatesize($tplidx1);

        $pdf->addPage($s["orientation"], $s);
        // $pdf->useTemplate($tplidx1,0,0,215);
        $pdf->useTemplate($tplidx1);

        $pdf->SetTextColor(0, 0, 0);

        //        $text = "Inject generated at " . $this->thing->thing->created_at. ".";
        //        $pdf->SetXY(130, 10);
        //        $pdf->Write(0, $text);

        $image = $pdf->Output("", "S");

        $this->thing_report["pdf"] = $image;

        return $this->thing_report["pdf"];
    }

    /**
     *
     * @return unknown
     */
    public function doState()
    {
        switch ($this->state) {
            case "start":
                $this->start();
                $this->response .= "Wumpus started. Welcome player. ";
                break;

            case "spawn":
                $this->spawnWumpus();
                break;
            case "foraging":
                $this->entity_agent->choice->Choose("foraging");

                $this->response .= "Foraging. ";
                break;
            case "inside nest":
                $this->entity_agent->choice->Choose("in nest");

                $this->response .=
                    "Wumpus is inside the " . $this->primary_place . ". ";
                break;
            case "nest maintenance":
                $this->response .= "Wumpus is doing Nest Maintenance. ";
                $this->entity_agent->choice->Choose("nest maintenance");

                break;
            case "patrolling":
                $this->response .= "Wumpus is Patrolling. ";
                $this->entity_agent->choice->Choose("patrolling");

                break;
            case "midden work":
                $this->response .= "Wumpus is taking a look at the midden. ";
                $this->middenwork();

                // Need to figure out how to set flag to red given that respond will then reflag it as green.
                // Can green reflag red?  Think about reset conditions.

                break;

            default:
                $this->thing->log(
                    $this->agent_prefix .
                        'invalid state provided "' .
                        $this->state .
                        '".'
                );
                $this->response .= "You are in a dark cave. ";

            // this case really shouldn't happen.
            // but it does when a web button lands us here.
        }
    }

    public function readSubject()
    {
        $this->response = null;

        if ($this->state == null) {
            $this->getWumpus();
        }

        $this->doState();

        $input = strtolower($this->subject);

        $r = "";
        $this->requested_cave_number = $this->x;
        //        $this->number_agent = new Number($this->thing, $input);
        $this->number_agent = new Number($this->entity_agent, $input);

        $this->number_agent->extractNumber($input);

        if ($this->number_agent->number != false) {
            $this->requested_cave_number = $this->number_agent->number;
        }

        // Check if this is one of the available caves.

        if (!isset($this->caves[strval($this->x)])) {
            $this->spawnWumpus();
        }
        $available_cave_names = $this->caves[strval($this->x)];

        $match = false;
        foreach ($available_cave_names as $key => $cave_name) {
            if ($cave_name == strval($this->requested_cave_number)) {
                $this->x = $this->requested_cave_number;
                $match = true;
                break;
            }
        }

        $cave_text = "";
        if ($this->requested_cave_number == strval($this->x)) {
            $cave_text = "Took a look around the cave. ";
        }

        if ($match != true and $this->number_agent->number != false) {
            $this->response .= "That is not one of the options. ";
        } else {
            $this->response .= "Moved to the next cave. ";
        }
        $this->response .= $cave_text;

        // Accept(able) (comprehended)? wumpus commands
        $this->keywords = [
            "teleport",
            "caves",
            "look",
            "arrow",
            "news",
            "forward",
            "north",
            "east",
            "south",
            "west",
            "up",
            "down",
            "left",
            "right",
            "wumpus",
            "meep",
            "thing",
            "lair",
            "foraging",
            "nest maintenance",
            "patrolling",
            "midden work",
            "nest maintenance",
            "start",
            "meep",
            "spawn",
            "run",
        ];

        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key => $piece) {
            $ngram_list[] = $piece;
        }

        foreach ($pieces as $key => $piece) {
            if (isset($last_piece)) {
                $ngram_list[] = $last_piece . " " . $piece;
            }
            $last_piece = $piece;
        }

        foreach ($pieces as $key => $piece) {
            if (isset($last_piece) and isset($last_last_piece)) {
                $ngram_list[] =
                    $last_last_piece . " " . $last_piece . " " . $piece;
            }
            $last_last_piece = $last_piece;
            $last_piece = $piece;
        }

        $wumpus_response = "";
        foreach ($ngram_list as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "run":
                            $t = new Run($this->thing, "run");
                            break;
                        case "news":
                            $this->newsWumpus();
                            $wumpus_response = $this->news;
                            //$this->response .= "May 18th is a Wumpus hunt at Queen Elizabeth Park. ";
                            break;

                        case "arrow":
                            $this->arrow();
                            $wumpus_response = "Fired a wonky arrow. ";
                            break;

                        case "look":
                            $this->caveWumpus($this->x);
                            $wumpus_response =
                                "You see " . $this->cave_name . ". ";
                            break;

                        case "caves":
                            $this->caves();
                            break;

                        case "west":
                        case "south":
                        case "east":
                        case "north":
                            $wumpus_response = ucwords($piece) . "? ";
                            break;

                        case "left":
                            $wumpus_response = "You turned left. ";
                            break;
                        case "right":
                            $wumpus_response = "You turned right. ";
                            break;

                        case "forward":
                            $this->left_count += 1;
                            $this->right_count += 1;
                            $wumpus_response = "You bumped into the wall. ";
                            break;

                        case "lair":
                            $wumpus_response = "Lair. ";
                            break;

                        case "meep":
                            $wumpus_response = "Merp. ";
                            break;

                        case "start":
                            $this->start();
                            $this->entity_agent->choice->Choose($piece);

                            $wumpus_response = "Heard " . $this->state . ". ";
                            break;

                        case "teleport":
                        case "spawn":
                            $this->spawnWumpus();
                            $this->response .= "Spawn. ";
                            break;

                        case "inside nest":
                            $this->entity_agent->choice->Choose($piece);

                            $this->state =
                                $this->entity_agent->choice->current_node;

                            $wumpus_response = "Heard inside nest.";
                            break;

                        case "foraging":
                            $this->entity_agent->choice->Choose($piece);

                            $this->state =
                                $this->entity_agent->choice->current_node;

                            $wumpus_response = "Now foraging. ";
                            break;

                        case "nest maintenance":
                            $this->entity_agent->choice->Choose($piece);

                            $this->state =
                                $this->entity_agent->choice->current_node;

                            $wumpus_response = "Heard nest maintenance. ";
                            break;

                        case "patrolling":
                            $this->entity_agent->choice->Choose($piece);
                            $this->state =
                                $this->entity_agent->choice->current_node;

                            $wumpus_response .= "Now " . $piece . ". ";
                            break;

                        case "midden work":
                            $this->middenwork();

                            $this->entity_agent->choice->Choose($piece);
                            $this->state =
                                $this->entity_agent->choice->current_node;

                            $wumpus_response .= "Heard midden work. Urgh. ";
                            break;

                        case "break":
                            $this->middenwork();
                            $run = new Run($this->thing, "break");

                            $this->run_flag = $run->agent_text;

                            $t = new Input($this->thing, "wumpus");
                            $this->wumpus_input_flag = $t->input_agent;

                            $wumpus_response .=
                                "Heard break. Stopped program. ";
                            break;
                    }
                }
            }
        }

        if ($wumpus_response == "") {
            $wumpus_response = "Text BREAK to stop running Wumpus.";
        }
        $this->response .= $wumpus_response;
        return false;
    }

    public function initWumpus()
    {
        $this->whatisthis = [
            "inside nest" =>
                "Each time the " .
                $this->short_name .
                ' service is accessed, Stackr creates a uniquely identifable Thing.
                This one is ' .
                $this->uuid .
                '.
                This message from the "Wumpus" ai which was been tasked with mediating web access to this Thing.
                Manage Things on ' .
                $this->short_name .
                ' using the [ NEST MAINTENANCE ] command.
                If Wumpus\'s are bothing you, you can either use the [ FORGET ] command
                to stop receiving notifications for the Thing, or you can turn [ WUMPUS OFF ].
                "Wumpus" is how ' .
                $this->short_name .
                ' manages interactions with your Things by other identities.
                [WUMPUS OFF] will stop any "Wumpus" agent responding.  You can say [ NEST MAINTENANCE ] later if you change your mind.',
            "nest maintenance" =>
                "A Things of yours was displayed again, perhaps by yourself.  This Wumpus is doing some nest maintenance.",
            "patrolling" =>
                "A Thing associated with " .
                "this identity" .
                " was displayed (or requested by) a device.  That's twice now.  This Wumpus is patrolling.",
            "foraging" =>
                "This wumpus is on it's last legs.  It has gone foraging for stack information about you to forget.",
            "midden work" =>
                "One of your records was displayed, perhaps by yourself.  A Wumpus spawned and is doing midden work.",
            "start" =>
                "Start. Not normally means that you displayed a record, let's see if we get any more Wumpus messages.",
        ];

        $this->litany = [
            "inside nest" =>
                "One of your records was displayed, perhaps by yourself.  A Wumpus spawned and is waiting in the nest.",
            "nest maintenance" =>
                "A record of yours was displayed again, perhaps by yourself.  This Wumpus is doing some nest maintenance.",
            "patrolling" =>
                "A record of yours was displayed.  That's twice now.  This Wumpus is patrolling.",
            "foraging" =>
                "This wumpus is on it's last legs.  It has gone foraging for stack information about you to forget.",
            "midden work" =>
                "One of your records was displayed, perhaps by yourself.  A Wumpus spawned and is doing midden work.",
            "start" =>
                "Start.  Not normally means that you displayed a record, let's see if we get any more Wumpus messages.",
        ];

        $this->thing_behaviour = [
            "inside nest" => "A Thing was instantiated.",
            "nest maintenance" => "A Thing was instantiated again.",
            "patrolling" => "A Thing was instantiated twice.",
            "foraging" => "A Thing is searching the stack.",
            "midden work" => "A Thing is doing stack work.",
            "start" => "Start. A Thing started.",
        ];

        // Behaviour
        $this->behaviour = [
            "inside nest" =>
                "Wumpus spawned and is waiting in the lair. For you.",
            "nest maintenance" => "Wumpus is doing some work on the lair.",
            "patrolling" =>
                "That's twice the Wumpus heard you. Now the Wumpus is patrolling.",
            "foraging" => "The Wumpus has gone to look for a snack.",
            "midden work" => "A Wumpus spawned and is tidying up the lair.",
            "start" => "Wumpus egg.",
        ];

        // Narrative
        $this->narrative = [
            "inside nest" => "Everything is dark.",
            "nest maintenance" => "You are hunting for a Wumpus in it's lair.",
            "patrolling" => "Now you are a Wumpus Hunter.",
            "foraging" => "Find the Wumpus.",
            "midden work" => "You are a Midden Worker. Have fun.",
            "start" => "Ant egg.",
        ];

        $this->choices_text = "WUMPUS";
        $this->prompt_litany = [
            "inside nest" => "TEXT WEB / " . $this->choices_text,
            "nest maintenance" => "TEXT WEB / " . $this->choices_text,
            "patrolling" => "TEXT WEB / " . $this->choices_text,
            "foraging" => "TEXT WEB / " . $this->choices_text,
            "midden work" => "TEXT WEB / " . $this->choices_text,
            "start" => "TEXT WEB / " . $this->choices_text,
        ];
    }

    /**
     *
     */
    function middenwork()
    {
        $middenwork = "on";
        if ($middenwork != "on") {
            $this->response .= "No work done. ";
            return;
        }

        //         $this->thing->choice->Create($this->primary_place, $this->node_list, "midden work");

        $this->response .= "Wumpus is fixing up the lair. ";
    }

    /**
     *
     */
    function arrow()
    {
        $input_agent = new Input($this->thing, "wumpus");
        $input_agent->addInput("How far (1-5)?");

        $this->response .= "How far (1-5)?";
        return;
        // devstack
        $current_cave = $this->x;
        $arrow_cave_previous = $current_cave;
        $arrow_cave = $current_cave;
        $this->response .= "Arrow fired through caves";

        foreach (range(1, 5) as $key => $value) {
            $available_caves = $this->caves[$arrow_cave];
            $arrow_cave_previous = $arrow_cave;

            while ($arrow_cave_previous == $arrow_cave) {
                $arrow_cave = $available_caves[array_rand($available_caves)];
            }

            $this->response .= " " . $arrow_cave;
        }

        $this->response .= ". Nothing happened. ";
    }

    /**
     *
     */
    function caves()
    {
        $this->response = "";
        $this->caveWumpus();

        $current_cave = $this->x;

        trim($this->response);

        //$this->caves[$current_cave];
        foreach ($this->caves[$current_cave] as $key => $cave) {
            $this->response .=
                "<" . $cave . ">" . strtoupper($this->cave_names[$cave]) . " ";
        }
        $this->response .= "";
    }

    /**
     *
     */
    function foraging()
    {
        //        $this->thing->choice->Create($this->primary_place, $this->node_list, "foraging");
        $this->entity_agent->choice->Create(
            $this->primary_place,
            $this->node_list,
            "foraging"
        );

        $this->response .= "Wumpus is foraging. ";
    }

    /**
     *
     */
    function patrolling()
    {
        //        $this->thing->choice->Create($this->primary_place, $this->node_list, "patrolling");
        $this->entity_agent->choice->Create(
            $this->primary_place,
            $this->node_list,
            "patrolling"
        );

        $this->response .= "Wumpus is patrolling. ";
    }

    /**
     *
     */
    function spawnWumpus()
    {
        $this->getWumpus();
        //$coordinate = new Coordinate($this->thing, "(0,0)");

        $pheromone["stack"] = 4;

        $this->cave = strval(random_int(1, 20));
        $this->x = $this->cave;

        //        $coordinate = new Coordinate($this->thing, "(".$this->cave.",0)");

        //        $this->thing->choice->Create($this->primary_place, $this->node_list, $this->state);
        $this->entity_agent->choice->Create(
            $this->primary_place,
            $this->node_list,
            $this->state
        );

        //  $this->thing->flagGreen();
        $this->entity_agent->flagGreen();
    }

    /**
     *
     */
    function start()
    {
        $this->x = "X";
        $this->getWumpus();
        //$this->thing->choice->Create($this->primary_place, $this->node_list, "start");
        $this->response .= "Welcome player. Wumpus has started.";
        //$this->thing->flagGreen();
        $this->entity_agent->flagGreen();
    }
}
