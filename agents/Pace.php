<?php
/**
 * Pace.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class Pace extends Agent
{
    // Starting point for a channel pace setting support.

    // Provide start time.
    // And runtime.
    // And a set of indexes.

    // An exercise in augemented virtual collaboration. With water.
    // But a Thing needs to know what minding the gap is.

    public $var = "hello";

    // This will provide a pace - a unit of energy converted into a paced unit of distance.
    // This is useful for PACE because it is the first step in provided the distance travelled down a path.
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

        // Get some stuff from the stack which will be helpful.
        // Until PACE gets its own.
        $this->web_prefix = $this->thing->container["stack"]["web_prefix"];
        $this->mail_postfix = $this->thing->container["stack"]["mail_postfix"];
        $this->word = $this->thing->container["stack"]["word"];
        $this->email = $this->thing->container["stack"]["email"];

        $this->time_travel_unit_name = "s";
        $this->time_unit_name = "seconds";

        $this->thing_report["help"] =
            "Helps set the pace during a block of time.";
    }

    public function run()
    {
        $this->doPace();

        if ($this->agent_input != "pace") {
            $this->set();
        }
    }

    /**
     * Add in code for setting the current distance travelled.
     */
    function set()
    {
        // UK Commonwealth spelling
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["pace", "time_travelled"],
            $this->time_travelled
        );

        $time_string = $this->thing->time();
        $this->thing->json->writeVariable(
            ["pace", "refreshed_at"],
            $time_string
        );

        $pace_timestamp = $this->thing->microtime();
        $this->thing->json->writeVariable(
            ["pace", "timestamp"],
            $pace_timestamp
        );
    }

    /**
     *
     */
    function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "pace",
            "refreshed_at",
        ]);

        $micro_timestamp = $this->thing->json->readVariable([
            "pace",
            "timestamp",
        ]);

        // Keep second level timestamp because I'm not
        // sure Stackr can deal with microtimes (yet).
        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["pace", "refreshed_at"],
                $time_string
            );
        }

        // And in microtime code for Pace.
        if ($micro_timestamp == false) {
            $micro_timestamp = $this->thing->microtime();
            $this->thing->json->writeVariable(
                ["pace", "timestamp"],
                $micro_timestamp
            );
        }

        // If it has already been processed ...
        $this->last_timestamp = $micro_timestamp;

        $this->time_travelled = $this->thing->json->readVariable([
            "pace",
            "time_travelled",
        ]);
        //        $this->response .= "Loaded Pace time travelled and microsecond timestamp."; // Because
    }

    /**
     * -----------------------
     *
     * @return unknown
     */

    public function respond()
    {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        $to = $this->thing->from;
        $from = "pace";

        $this->makeMessage();
        $this->makeSms();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        $this->makeWeb();

        $this->thing_report["sms"] = $this->sms_message;

        return $this->thing_report;
    }

    function isInput($input)
    {
        if ($input === false) {
            return false;
        }
        if (strtolower($input) == strtolower("X")) {
            return false;
        }

        if (is_numeric($input)) {
            return true;
        }
        if ($input == 0) {
            return true;
        }

        return true;
    }

    /**
     *
     */
    private function paceDo()
    {
        // What does pace do. doPace is doing a Pace.

        // Pace is a tool to measure out time in our world.  Welcome.

        // What open source means is that everybody can read and see our code.
        // Because you need to share.  So do we.

        // See Github: nrwtaylor/stack-agent-thing

        // A Pace is a unified step of time measured from you.
        // A Pace is a unit of energy.  It happens to be exactly the same as a metric calorie.
        // Weird. Again.

        // And then Pace figures out how far that energy would have got you along your path.
        // Motivated? With a little energy.

        // Welcome to <Insert Robot Name Here>.  The Robot Pacer.

        // Yeah we just want to run.  But this at this Pace.
        // For Pacers.

        // If you don't want to worry about how long it going take to get there, don't worry about <Insert Robot Name Here>.

        // Code?

        // Sure.

        $this->getEnergy();
    }

    /**
     *
     */
    public function lastPace()
    {
        $this->getTime();

        $findagent_thing = new Findagent($this->thing, "pace");

        $this->thing->log(
            'Agent "Pace" found ' .
                count($findagent_thing->thing_report["things"]) .
                " Pace Agent Things."
        );

        $this->last_created_at =
            $findagent_thing->thing_report["things"][0]["created_at"];
    }

    /**
     *
     */
    public function doPace()
    {
        // What is to do a Pace.

        // This will be the full cycle of a Pace.

        // Pace.

        // For now take unit of energy and convert it into distance travelled.
        $this->energy_number = 1;

        $time_travelled = $this->paceEnergy($this->energy_number);

        $this->getTime();

        $this->max_tick_time = 60 * 4; // Four minutes

        if ($this->tick_time > $this->max_tick_time) {
            $this->flag = new Flag($this->thing, "red");
        }

        // I think a Pace is not the catching a prompt.
    }

    /**
     *
     * @param unknown $energy_text (optional)
     * @return unknown
     */
    private function paceEnergy($energy_text = null)
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

        //        $this->time_travelled += $this->tick_time;
        $this->time_travelled = $this->tick_time;

        // This is a concept of the amount of time travelled
        // from the beginning of the pacing signal.
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
        // When needed.
        $text =
            "Nonsense text string, json string, or other text representation of 1 energy units";

        // Going to be a call from a person for a pace.  So it should be provided.
        // So retrieve the pace energy last posted.
        // Call that a custom Pace function to read and write the energy to a pace variable.
        $this->energy = new Number($this->thing, "energy " . $text); // test uniqueness?
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        if ($this->agent_input == "pace") {
            $this->lastPace();
            return;
        }

        $input = strtolower($this->subject);

        $number_agent = new Number($this->thing, "number");

        $this->min = 1;
        $this->max = 1;
        if (count($number_agent->numbers) == 1) {
            $this->max = $number_agent->numbers[0];
        }

        if (count($number_agent->numbers) == 2) {
            $this->min = min(
                $number_agent->numbers[0],
                $number_agent->numbers[1]
            );
            $this->max = max(
                $number_agent->numbers[0],
                $number_agent->numbers[1]
            );
        }

        $runtime_agent = new Runtime($this->thing, "runtime");

        $response_text = "Set a RUNTIME. ";
        $this->per_item_time = false;
        $this->runtime_minutes = "X";

        if (
            $runtime_agent->minutes != false and
            $runtime_agent->minutes != "X"
        ) {
            $this->runtime_minutes = $runtime_agent->minutes;
            $response_text = "";

            $item_count = $this->max - $this->min + 1;
            $this->per_item_time =
                (float) $runtime_agent->minutes / (float) $item_count;
        }

        $this->response .= $response_text;

        //        $runat_agent = new Runat($this->thing, "runat");
        //        $this->run_at_hour = $runat_agent->hour;
        //        $this->run_at_minute = $runat_agent->minute;

        // Runat is getting changed in the call.
        // devstack runat

        // Build current time
        // Build time entity
        // Now?  Or next?
        $this->getTime();

        $date = explode(" ", $this->current_timestamp)[0];
        $time = explode(" ", $this->current_timestamp)[1];

        $current_date = date_create($date . " " . $time);

        $runat_agent = new Runat($this->thing, "runat");
        //        $this->run_at_hour = $runat_agent->hour;
        //        $this->run_at_minute = $runat_agent->minute;

        $this->run_at_day = "X";
        $this->run_at_hour = "X";
        $this->run_at_minute = "X";

        $response_text = "";

        if ($this->isInput($runat_agent->hour)) {
            $this->run_at_hour = $runat_agent->hour;
            $response_text = "";
        } else {
            $response_text = "Set RUNAT. ";
        }
        //$this->response .= $response_text;

        if ($this->isInput($runat_agent->minute)) {
            $this->run_at_minute = $runat_agent->minute;
            //   $response_text = "";
        } else {
            $response_text = "Set RUNAT. ";
        }
        //$this->response .= $response_text;

        if ($this->isInput($runat_agent->day)) {
            $this->run_at_day = $runat_agent->day;
            $response_text = "";
        } else {
            $response_text = "Set RUNAT. ";
        }

        //if (($runat_agent->hour == "X") and ($runat_agent->minute == "X")) {

        //        $this->run_at_hour = $runat_agent->hour;
        //        $this->run_at_minute = $runat_agent->minute;
        //        $response_text = "Please set a RUNAT time. ";
        //}

        $this->response .= $response_text;

        $runat_date = date_create(
            $date . " " . $this->run_at_hour . ":" . $this->run_at_minute
        );
        if ($runat_date == false) {
            // No runat date set.
            $runat_date = clone $current_date;
        }
        $runend_date = clone $runat_date;

        //$response_text = "Set RUNTIME.";
        if (
            $runtime_agent->minutes != false and
            $runtime_agent->minutes != "X"
        ) {
            $runend_date = date_add(
                $runend_date,
                date_interval_create_from_date_string(
                    $runtime_agent->minutes . " minutes"
                )
            );
            $response_text = "";
        }

        if ($runend_date < $current_date) {
            date_add(
                $runat_date,
                date_interval_create_from_date_string("1 day")
            );
            date_add(
                $runend_date,
                date_interval_create_from_date_string("1 day")
            );
        }

        if ($runend_date != false) {
            $this->runtime =
                $runend_date->getTimestamp() - $runat_date->getTimestamp();
        }
        //$this->response .= $response_text;

        //        $this->runtime = $runend_date->getTimestamp() - $runat_date->getTimestamp();
        $this->elapsed =
            $current_date->getTimestamp() - $runat_date->getTimestamp();
        $this->togo = $this->runtime - $this->elapsed;
        $this->percent = null;
        if ($this->runtime != 0) {
            $this->percent = $this->elapsed / $this->runtime;
        }
        $this->runat_date = $runat_date;
        $this->runend_date = $runend_date;

        $this->items_ghost =
            ($this->max - $this->min) * $this->percent + $this->min;

        $this->message = "https://www.urbandictionary.com/define.php?term=pace";
        $this->keyword = "pace";

        $this->thing_report["keyword"] = $this->keyword;
        //  $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report["email"] = $this->message;

        $this->paceDo();

        return $this->response;
    }

    /**
     *
     */
    public function makeSms()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/pace";

        if (!isset($this->tick_time) or $this->tick_time == 0) {
            $sms = "PACE ";
        } else {
            $sms = "PACE";
        }

        if (isset($this->runtime_minutes)) {
            $sms .= " | runtime " . $this->runtime_minutes;
        }

        if (
            isset($this->run_at_day) or
            isset($this->run_at_hour) or
            isset($this->run_at_minute)
        ) {
            $sms .= " runat";
        }

        if (isset($this->run_at_hour) and isset($this->run_at_minute)) {
            $sms .= " ";
            $hour_text = str_pad($this->run_at_hour, 2, "0", STR_PAD_LEFT);
            $minute_text = str_pad($this->run_at_minute, 2, "0", STR_PAD_LEFT);

            //            if (isset($this->run_at_day)) {$sms .= $hour_text . ":" . $minute_text;}
            $sms .= $hour_text . ":" . $minute_text;
            $sms .= " " . $this->run_at_day;
        }

        //        if ( (isset($this->run_at_day)) and (isset($this->run_at_minute)) ) {
        //            $sms .= " ";
        //            if (isset($this->run_at_day)) {$sms .= $this->run_at_day;}
        //        }

        $link = "";
        if ($this->per_item_time != 0) {
            $this->response .= $this->per_item_time . " minute block";

            $percent = intval($this->percent * 100);

            $progress_text =
                " " .
                $percent .
                "% " .
                "item " .
                intval($this->items_ghost) .
                " of " .
                ($this->max - $this->min + 1) .
                " items.";

            if ($this->min != 1) {
                $progress_text =
                    " " .
                    $percent .
                    "% " .
                    "item " .
                    intval($this->items_ghost);
            }

            if ($this->max == $this->min) {
                $progress_text = " " . $percent . "% complete.";
            }

            if ($this->percent < 0) {
                $progress_text = " coming up.";
            }

            $this->response .= $progress_text;
            $this->response .=
                " " . $this->thing->human_time($this->togo) . " to go.";
        }
        //$sms .= " | https://www.urbandictionary.com/define.php?term=pace";
        //        $sms .= " | " . $link . " | " . $this->response;
        $sms .= " | " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $this->sms_message;
    }

    /**
     *
     */
    public function makeMessage()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/pace";

        if (!isset($this->tick_time) or $this->tick_time == 0) {
            $message = "Pace ";
        } else {
            $message =
                "Paced off for " .
                $this->thing->human_time($this->tick_time) .
                ".";
        }

        $this->message = $message;
        $this->thing_report["message"] = $message;
    }

    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/pace";

        $flag = "";
        if (isset($this->flag->state)) {
            $flag = $this->flag->state;
        }

        $html = "<b>PACE " . $flag;
        $html = "</b>";

        $html .= "<p><b>Pace Variables</b>";
        //$html .= '<br>state ' . $this->state . '';

        $elapsed_time_between_paces = $this->elapsed_clock * 1e3; // To convert to seconds

        // $html .= "<br>Elapsed time between paces " . $this->elapsed_clock * 1e3 . $this->time_unit_name;

        $html .=
            "<br>Pace time " . $this->thing->human_time($this->elapsed_clock);

        $html .=
            "<br>Time travelled " .
            $this->thing->human_time($this->time_travelled); // Commonwealth spelling
        $html .= " (" . number_format(intval($this->time_travelled)) . "s)."; // Commonwealth spelling

        // You can hardcode you Pace page here
        $html .= "<p><b>Pace-Pacer link</b>";
        $html .= "<br>";

        $html .= '<a href="' . $link . '">';
        //        $web .= $this->html_image;
        $html .= $link;

        $html .= "</a>";
        $html .= "<br>";
        $html .= "<br>";
        $html .= 'Pace says, "';
        $html .= $this->message . '"';

        $warranty = new Warranty($this->thing, "warranty");

        $html .=
            "<p><br>" .
            "This is a developmental tool. Sometimes it might not work. If you have resources, we hope you can make it more reliable.";

        $html .=
            "<p><br>" . "Thank you for your recent pace. " . $warranty->message;

        $html .= "<p>";
        $html .= "<br>Last pace time " . $this->last_timestamp;

        //$html .= "<br>Check-in was " . intval($this->time_travelled) . "ms ago."; // Commonwealth spelling

        $this->web_message = $html;
        $this->thing_report["web"] = $this->web_message;
    }
}
