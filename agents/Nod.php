<?php
/**
 * Nod.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class Nod extends Agent
{
    // An exercise in augemented virtual collaboration. With water.
    // But a Thing needs to know what minding the gap is.

    public $var = 'hello';

    // This will provide a nod - a unit of energy converted into a nodded unit of distance.
    // This is useful for NOD because it is the first step in provided the distance travelled down a path.
    // When provided with a random time interval series of energy inputs.

    // See getEnergy() for dev

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */

    function init()
    {
        $this->retain_for = 24; // Retain for at least 24 hours.

        $this->state = "dev";

        $this->time_travel_unit_name = "s";
        $this->time_unit_name = "seconds";

    }

    public function run()
    {
        $this->doNod();
    }

    /**
     * Add in code for setting the current distance travelled.
     */
    function set()
    {
        if ($this->agent_input == "nod") {
            return;
        }

        // UK Commonwealth spelling
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["nod", "time_travelled"],
            $this->time_travelled
        );

        $time_string = $this->thing->time();
        $this->thing->json->writeVariable(
            ["nod", "refreshed_at"],
            $time_string
        );

        $nod_timestamp = $this->thing->microtime();
        $this->thing->json->writeVariable(["nod", "timestamp"], $nod_timestamp);
    }

    /**
     *
     */
    function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "nod",
            "refreshed_at",
        ]);

        $micro_timestamp = $this->thing->json->readVariable([
            "nod",
            "timestamp",
        ]);

        // Keep second level timestamp because I'm not
        // sure Stackr can deal with microtimes (yet).
        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["nod", "refreshed_at"],
                $time_string
            );
        }

        // And in microtime code for Nod.
        if ($micro_timestamp == false) {
            $micro_timestamp = $this->thing->microtime();
            $this->thing->json->writeVariable(
                ["nod", "timestamp"],
                $micro_timestamp
            );
        }

        // If it has already been processed ...
        $this->last_timestamp = $micro_timestamp;

        $this->time_travelled = $this->thing->json->readVariable([
            "nod",
            "time_travelled",
        ]);
        $this->response =
            "Loaded Nod time travelled and microsecond timestamp."; // Because
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

    }

    /**
     *
     */
    private function nodDo()
    {
        // What does nod do. doNod is doing a Nod.

        // Nod is a tool to connect to our world.  Welcome.

        // What open source means is that everybody can read and see our code.
        // Because you need to share.  So do we.

        // Nod is a project of Stackr Interactive Ltd based in Burnaby, BC.  No.  Not EA.

        // And our code is what NOD does.  Hello Robot.

        // See Github: nrwtaylor/stack-agent-thing

        // A Nod is a unified step of work measured from you.
        // A Nod is a unit of energy.  It happens to be exactly the same as a metric calorie.
        // Weird.

        // And then Nod figures out how for that energy would have got you along your path.

        // Welcome to <Insert Robot Name Here>.  The Robot Coxswain.

        // Yeah we want to steer the path too.  But this is Nod.
        // For Nodders.

        // If you don't want to worry about where you are going, don't worry about <Insert Robot Name Here>.

        // Code?

        // Sure.

        $this->getEnergy();
    }

    /**
     *
     */
    public function lastNod()
    {
        $this->getTime();

        // $findagent_thing = new Findagent($this->thing, 'nod');

        $things = $this->getThings('nod');
        $count = 0;

        if (!is_array($things)) {
            return false;
        }

        $count = count($things);
        $this->thing->log('Agent "Nod" found ' . $count . " Nod Agent Things.");
        $this->last_created_at = $things[0]['created_at'];
    }

    /**
     *
     */
    public function doNod()
    {
        // What is to do a Nod.

        // This will be the full cycle of a Nod.

        // Nod.

        // For now take unit of energy and convert it into distance travelled.
        $this->energy_number = 1;

        $time_travelled = $this->nodEnergy($this->energy_number);

        $this->getTime();

        $this->max_tick_time = 60 * 4; // Four minutes

        if ($this->tick_time > $this->max_tick_time) {
            $this->flag = new Flag($this->thing, "red");
        }

        // I think a Nod is not the catching a prompt.
    }

    /**
     *
     * @param unknown $energy_text (optional)
     * @return unknown
     */
    private function nodEnergy($energy_text = null)
    {
        if ($energy_text == null) {
            $energy_text = $this->energy->number;
        }

        $scalar = 0;
        $this->velocity = 0;
        $this->velocity = -1; // m/s test current
        $this->acceleration = 0;

        // Realworld elapsed time
        //$tick_time = 534 / 1000; // ms > s

        $this->tick_time = $this->getTime(); //s but test

        $this->time_travelled += $this->tick_time;

        return $this->time_travelled;
    }

    /**
     *
     * @return unknown
     */
    public function getTime()
    {
        if (isset($this->elapsed_clock)) {
            return $this->elapsed_clock;
        }
        // Only do this once.
        // Can't have calculations based on different timestamps.
        if (!isset($this->current_timestamp)) {
            $this->current_timestamp = $this->thing->microtime();
        }

        //$this->current_timestamp = $this->thing->microtime();
        $this->elapsed_clock = $this->microtime_diff(
            $this->last_timestamp,
            $this->current_timestamp
        );
        return $this->elapsed_clock;
    }

    /**
     * https://gist.github.com/hadl/5721816
     *
     * @param unknown $start
     * @param unknown $end
     * @return unknown
     */
    function microtime_diff($start, $end)
    {
        // Lots of testing needed on this :/
        // Date maths.  Urgh.

        list($start_date, $start_clock, $start_usec) = explode(" ", $start);
        list($end_date, $end_clock, $end_usec) = explode(" ", $end);

        $diff_date = strtotime($end_date) - strtotime($start_date);

        $diff_clock = strtotime($end_clock) - strtotime($start_clock);

        $diff_usec = floatval($end_usec) - floatval($start_usec);

        return floatval($diff_date) + floatval($diff_clock) + $diff_usec;
    }

    /**
     *
     */
    private function getEnergy()
    {
        // Insert code to talk to Concept2.
        $text =
            "Nonsense text string, json string, or other text representation of 1 energy units";

        // Going to be a call from a peeple for a nod.  So it should be provided.
        // So retrieve the noddy energy last posted.
        // Call that a custom Nod function to read and write the energy to a nod variable.
        $this->energy = new Number($this->thing, "energy " . $text); // test uniqueness?

        // Which means we now have
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->thing_report['help'] =
            "Helps folk check if you are okay. TEXT WEB.";

        if ($this->agent_input == "nod") {
            $this->lastNod();
            return;
        }

        $input = strtolower($this->subject);

        $this->response = "The robot needs the word.";
        if (strpos($input, 'amalgamate') !== false) {
            $this->response = "Thanks for the word.";
            return;
        }

        $this->response = "Nodded.";
        //$this->sms_message = "SPLOSH | https://dictionary.cambridge.org/dictionary/english/splosh";
        $this->message = "https://www.urbandictionary.com/define.php?term=nod";
        $this->keyword = "nod";

        $this->thing_report['keyword'] = $this->keyword;
        //  $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['email'] = $this->message;

        $this->nodDo();

        return $this->response;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/nod';

        if (!isset($this->tick_time) or $this->tick_time == 0) {
            $sms = "NOD ";
        } else {
            //$sms = "NODDED " . $this->thing->human_time($this->tick_time);
            $sms = "NOD";
        }

        //$sms .= " | https://www.urbandictionary.com/define.php?term=nod";
        $sms .= " | " . $link . " | " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $this->sms_message;
    }

    /**
     *
     */
    public function makeMessage()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/nod';

        if (!isset($this->tick_time) or $this->tick_time == 0) {
            $message = "Nod ";
        } else {
            $message =
                "Nodded off for " .
                $this->thing->human_time($this->tick_time) .
                ".";
        }

        //$sms .= " | https://www.urbandictionary.com/define.php?term=nod";
        //$sms .= " | " . $link . " | " . $this->response;

        $this->message = $message;
        $this->thing_report['message'] = $message;
    }

    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/nod';

        $flag = "";
        if (isset($this->flag->state)) {
            $flag = $this->flag->state;
        }

        $html = "<b>NOD " . $flag;
        $html = "</b>";

        $html .= "<p><b>Nod Variables</b>";
        //$html .= '<br>state ' . $this->state . '';

        //$html .= "<br>Distance just sploshed is " . $this->distance_travelled . $this->distance_unit_name;
        //$html .= "<br>Total distance sploshed " . $this->distance . $this->distance_unit_name;

        $elapsed_time_between_nods = $this->elapsed_clock * 1e3; // To convert to seconds

        // $html .= "<br>Elapsed time between nods " . $this->elapsed_clock * 1e3 . $this->time_unit_name;

        $html .=
            "<br>Nod time " . $this->thing->human_time($this->elapsed_clock);

        $html .=
            "<br>Time travelled " .
            $this->thing->human_time($this->time_travelled); // Commonwealth spelling
        $html .= " (" . number_format(intval($this->time_travelled)) . "s)."; // Commonwealth spelling

        // You can hardcode you Splosh page here
        $html .= "<p><b>Nod-Nodder link</b>";
        $html .= "<br>";

        $html .= '<a href="' . $link . '">';
        //        $web .= $this->html_image;
        $html .= $link;

        $html .= "</a>";
        $html .= "<br>";
        $html .= "<br>";
        $html .= 'Nod says, "';
        $html .= $this->message . '"';

        $warranty = new Warranty($this->thing, "warranty");

        $html .=
            "<p><br>" .
            "This is a developmental tool. Sometimes it might not work. If you have resources, we hope you can make it more reliable.";

        $html .=
            "<p><br>" . "Thank you for your recent nod. " . $warranty->message;

        //exit();

        $html .= "<p>";
        $html .= "<br>Last nod time " . $this->last_timestamp;

        //$html .= "<br>Check-in was " . intval($this->time_travelled) . "ms ago."; // Commonwealth spelling

        $this->web_message = $html;
        $this->thing_report['web'] = $this->web_message;
    }
}
