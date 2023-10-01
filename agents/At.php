<?php
/**
 * At.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class At extends Agent
{
    // devstack backlog
    // recognize runat finishat stopat closeat startat as agents pointing to At.

    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->keywords = ["next", "accept", "clear", "drop", "add", "new"];
        $this->test = "Development code"; // Always iterative.

        $this->tag = "at";
        $this->initAt();
    }

    function initAt()
    {
        // Going to need a little help.
        //$this->year_agent = new Year($this->thing, "year");
        //$this->day_agent = new Day($this->thing, "day");
    }

    /**
     *
     */
    function set()
    {
        if ($this->at == false) {
            return;
        }

        if (!isset($this->day)) {
            $this->day = "X";
        }
        if (!isset($this->hour)) {
            $this->hour = "X";
        }
        if (!isset($this->minute)) {
            $this->minute = "X";
        }

        $datetime = $this->day . " " . $this->hour . ":" . $this->minute;
        $this->datetime = date_parse($datetime);

        $this->at->setVariable("refreshed_at", $this->current_time);
        $this->at->setVariable("day", $this->day);
        $this->at->setVariable("hour", $this->hour);
        $this->at->setVariable("minute", $this->minute);

        $this->thing->log("At set completed.", "DEBUG");

        $this->thing->log(
            $this->agent_prefix .
                " saved " .
                $this->day .
                " " .
                $this->hour .
                " " .
                $this->minute .
                ".",
            "DEBUG"
        );
    }

    /**
     *
     * @param unknown $run_at (optional)
     */
    function get($run_at = null)
    {
        $this->at = new Variables(
            $this->thing,
            "variables " . $this->tag . " " . $this->from
        );

        if ($this->at == false) {
            return;
        }

        $day = $this->at->getVariable("day");
        $hour = $this->at->getVariable("hour");
        $minute = $this->at->getVariable("minute");

        $this->refreshed_at = $this->at->getVariable("refreshed_at");

        if ($this->isInput($day)) {
            $this->day = $day;
        }
        if ($this->isInput($hour)) {
            $this->hour = $hour;
        }
        if ($this->isInput($minute)) {
            $this->minute = $minute;
        }

        $this->thing->log("At get completed.", "DEBUG");
    }

    function getAt()
    {
        if (!isset($this->end_at) and !isset($this->runtime)) {
            if (!isset($this->run_at)) {
                $this->run_at = "X";
            }
            return $this->run_at;
        }

        if (!isset($this->end_at)) {
            $this->getEndat();
        }

        if (!isset($this->runtime)) {
            $this->getRuntime();
        }

        switch (true) {
            case strtoupper($this->end_at) != "X" and
                strtoupper($this->end_at) != "Z":
                $this->run_at = strtotime(
                    $this->end_at . "-" . $this->runtime->minutes . "minutes"
                );
                break;
            default:
                $this->run_at = $this->trainTime();
        }

        return $this->run_at;
    }

    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function isToday($text = null)
    {
        $unixTimestamp = strtotime($this->current_time);
        $day = date("D", $unixTimestamp);

        if (strtoupper($this->day) == strtoupper($day)) {
            return true;
        } else {
            return false;
        }

        // true = yes, false = no
    }

    /**
     *
     * @param unknown $input (optional)
     */

    function numbersAt($input = null)
    {
        if ($input == null) {
            $input = $this->input;
        }

        $this->numbers = [];

        $numbers = $this->extractNumbers($input);

        if (count($numbers) > 0) {
            $this->numbers = $numbers;
        }
    }

    /**
     *
     * @param unknown $input (optional)
     */
    function extractNumber($input = null)
    {
        $this->number = "X";

        if (!isset($this->numbers)) {
            $this->numbersAt($input);
        }
        if (count($this->numbers) == 1) {
            $this->number = $this->numbers[0];
        }
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractAt($input = null)
    {
        // Remove non dates.
        $input = $this->stripUrls($input);

        // Remove access codes (3 4 4)

        $pattern = "/\b\d{3} \d{4} \d{4}\b/i";
        preg_match_all($pattern, $input, $match);
        $t = $match[0];

        foreach ($t as $i => $access_code) {
            $input = str_replace($access_code, " ", $input);
        }

        $input = $this->stripTelephonenumbers($input, " ");
        $this->parsed_date = date_parse($input);

        $month = $this->parsed_date["month"];
        $this->month = $month;

        $minute = $this->parsed_date["minute"];
        $hour = $this->parsed_date["hour"];

        $day_number = $this->parsed_date["day"];
        $this->day_number = $day_number;

        $day_code = $this->extractDay($input);
        $day = "X";
        $allowed_day_codes = [
            "Z",
            "X",
            "MON",
            "TUE",
            "WED",
            "THU",
            "FRI",
            "SAT",
            "SUN",
        ];
        if (in_array(strtoupper($day_code), $allowed_day_codes) === true) {
            $day = strtoupper($day_code);
        }

        // See what numbers are in the input
        if (!isset($this->numbers)) {
            $this->numbersAt($input);
        }
        $this->numbersAt($input);

        // handle June 2000.
        // parse_date will give a day_number of 1 for this string.
        if ($this->day_number == 1) {
            $flag = false;
            foreach ($this->numbers as $i => $number) {
                if ($number == $this->day_number) {
                    $flag = true;
                    break;
                }
            }

            if ($flag == false) {
                $this->day_number = false;
            }
        }

        if (isset($this->numbers) and count($this->numbers) == 0) {
        } elseif (count($this->numbers) == 1) {
            if (strlen($this->numbers[0]) == 4) {
                if ($minute == 0 and $hour == 0) {
                    $minute = substr($this->numbers[0], 2, 2);
                    $hour = substr($this->numbers[0], 0, 2);
                }
            }
        } elseif (count($this->numbers) == 2) {
            if ($minute == 0 and $hour == 0) {
                // Deal with edge case(s)
                if (isset($this->numbers[0]) and ($this->numbers[0] = "0000")) {
                    $this->hour = 0;
                    $this->minute = 0;
                }
                if (
                    ($this->numbers[0] = "00") and
                    (isset($this->numbers[1]) and $this->numbers[1] == "00")
                ) {
                    $this->hour = $hour;
                    $this->minute = $minute;
                }
            } else {
                if ($this->isInput($minute)) {
                    $this->minute = $minute;
                }
                if ($this->isInput($hour)) {
                    $this->hour = $hour % 24;
                }
            }
        } else {
        }

        $this->minute = $minute;
        $this->hour = $hour;

        // TODO Refactor code above and directly below.
        //$clocktime_agent = new Clocktime($this->thing,"clocktime");
        //$t = $clocktime_agent->extractClocktime($input);

        $t = $this->extractClocktime($input);

        if ($t == null) {
            $this->minute = false;
            $this->hour = false;
        } else {
            // TODO
            $this->hour = $t[0];
            $this->minute = $t[1];
        }

        $this->day = false;
        if ($this->isInput($day)) {
            $this->day = $day;
        }

        $this->year = false;

        $year = $this->extractYear($input);
        $year_text = "X";
        if ($year !== false) {
            $year_text = $year["year"]; // Discard era information.
        }
        if ($this->isInput($year_text)) {
            $this->year = $year_text;
        }

        // Resolve the situtation where minutes:hours is resolved as the year.
        if (
            $this->hour . str_pad($this->minute, 2, "0", STR_PAD_LEFT) ==
                $this->year and
            $this->year !== false
        ) {
            // TODO: Dev case "1919 19:19" and similar
            $this->minute = false;
            $this->hour = false;
        }
        $this->timezone = $this->extractTimezone($input);

        $at = [
            "year" => $this->year,
            "month" => $this->month,
            "day" => $this->day,
            "day_number" => $this->day_number,
            "hour" => $this->hour,
            "minute" => $this->minute,
            "timezone" => $this->timezone,
        ];

        // TODO - Gregorian?
        //$this->extractCalendar($input);
        return $at;
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractMeridian($input = null)
    {
        if (!isset($this->number)) {
            $this->extractNumber($input);
        }

        if (count($this->numbers) == 2) {
            if ($this->numbers[0] <= 12 and $this->numbers[0] >= 1) {
                $this->hour = $this->numbers[0];
            }
            if ($this->numbers[1] >= 1 and $this->numbers[1] <= 59) {
                $this->minute = $this->numbers[1];
            }
        }

        if (count($this->numbers) == 1) {
            if ($this->numbers[0] <= 12 and $this->numbers[0] >= 1) {
                $this->hour = $this->numbers[0];
            }
        }

        $pieces = explode(strtolower($input), " ");

        $keywords = ["am", "pm", "morning", "evening", "late", "early"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "stop":
                            if ($key + 1 > count($pieces)) {
                                $this->stop = false;
                                return "Request not understood";
                            } else {
                                $this->stop = $pieces[$key + 1];
                                $this->response .= $this->stopTranslink(
                                    $this->stop
                                );
                                return;
                            }
                            break;

                        case "am":
                            break;

                        case "pm":
                            $this->hour = $this->hour + 12;
                            break;

                        default:
                    }
                }
            }
        }
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractDay($input = null)
    {
        // TODO Refactor as Day class.
        $day = "X";
        $day_evidence = [];
        $days = [
            "MON" => ["monday", "mon", "M"],
            "TUE" => ["tuesday", "tue", "Tu"],
            "WED" => ["wednesday", "wed", "wday", "W"],
            "THU" => ["thursday", "thur", "Thu", "Th"],
            "FRI" => ["friday", "fri", "Fr", "F"],
            "SAT" => ["saturday", "sat", "Sa"],
            "SUN" => ["sunday", "sun", "Su"],
        ];

        foreach ($days as $i => $day_null) {
            $day_evidence[$i] = [];
        }

        foreach ($days as $key => $day_names) {
            if (strpos(strtolower($input), strtolower($key)) !== false) {
                // $day_evidence[] = $key;
                $day = $key;
                $day_evidence[$day][] = $key;
                //break;
            }

            foreach ($day_names as $day_name) {
                if (
                    strpos(strtolower($input), strtolower($day_name)) !== false
                ) {
                    if (
                        strpos(
                            strtolower($input),
                            strtolower($day_name . " ")
                        ) == false
                    ) {
                        continue;
                    }

                    if (
                        strpos(
                            strtolower($input),
                            strtolower(" " . $day_name)
                        ) == false
                    ) {
                        continue;
                    }

                    $day = $key;
                    $day_evidence[$key][] = $day_name;

                }
            }
        }

        $this->parsed_date = date_parse($input);
        if (
            $this->parsed_date["year"] != false and
            $this->parsed_date["month"] != false and
            $this->parsed_date["day"] != false
        ) {
            $date_string =
                $this->parsed_date["year"] .
                "/" .
                $this->parsed_date["month"] .
                "/" .
                $this->parsed_date["day"];

            $unixTimestamp = strtotime($date_string);
            $p_day = strtoupper(date("D", $unixTimestamp));
            if ($day == "X") {
                $day = $p_day;
            }
            $day_evidence[$day][] = $date_string;
        }

        $unixTimestamp = strtotime($input);
        if ($unixTimestamp !== false) {
            $p_day = strtoupper(date("D", $unixTimestamp));
            $day_evidence[$p_day][] = $input;
        }
        $scores = [];
        // Process day evidence
        foreach ($day_evidence as $day => $evidence) {
            $scores[$day] = mb_strlen(implode("", $evidence));
        }

        foreach ($scores as $i => $score) {
            if ($score == 0) {
                unset($scores[$i]);
                continue;
            }

            // Allow one character date recognition if the string is 1 long.
            if ($score == 1 and mb_strlen($input) == 1) {
                continue;
            }

            // Allow two character date recognition if the string is 2 long.
            if ($score == 2 and mb_strlen($input) == 2) {
                continue;
            }

            // Now deal with lots of matching letters in a long string
            // Is there more than one line of evidence?
            if (count($day_evidence[$i]) > 1) {
                continue;
            }

            if ($score > 2) {
                continue;
            }

            // Otherwise ignore
            // TODO: Review
            unset($scores[$i]);
        }

        if (count($scores) == 0) {
            return false;
        }

        if (count($scores) == 1) {
            return array_key_first($scores);
        }

        // Leave it here for now.
        // TODO: Consider three days all with same score
        // TODO: Consider two days wth non-zero scores.
        return false;
    }

    /**
     *
     */
    function makeTXT()
    {
        $txt = $this->sms_message;

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    /**
     *
     */
    public function textAt()
    {
        $day = "X";
        if (isset($this->day)) {
            $day = $this->day;
        }
        if ($day == null) {
            $day = "X";
        }

        $hour = "X";
        if (isset($this->hour)) {
            $hour = $this->hour;
        }

        $minute = "X";
        if (isset($this->minute)) {
            $minute = $this->minute;
        }

        $hour_text = str_pad($hour, 2, "0", STR_PAD_LEFT);
        if ($hour == "X") {
            $hour_text = "XX";
        }

        $minute_text = str_pad($minute, 2, "0", STR_PAD_LEFT);
        if ($minute == "X") {
            $minute_text = "XX";
        }

        $day_text = $day;
        $text = $day_text . " " . $hour_text . ":" . $minute_text;

        return $text;
    }
    public function makeSMS()
    {
        $sms_message = "AT IS " . $this->textAt();

        $sms_message .= $this->response;

        $day = "X";
        if (isset($this->day)) {
            $day = $this->day;
        }
        if ($day == null) {
            $day = "X";
        }

        $hour = "X";
        if (isset($this->hour)) {
            $hour = $this->hour;
        }

        $minute = "X";
        if (isset($this->minute)) {
            $minute = $this->minute;
        }

        if (
            !$this->isInput($day) or
            !$this->isInput($hour) or
            !$this->isInput($minute)
        ) {
            $sms_message .= " | Retrieved time. ";
        }

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    /**
     *
     */
    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);

            $this->thing_report["info"] = $message_thing->thing_report["info"];
        } else {
            $this->thing_report["info"] =
                'Agent input was "' . $this->agent_input . '".';
        }

        $this->thing_report["help"] = "This is a headcode.";
    }

    /**
     *
     * @param unknown $variable
     * @return unknown
     */
    function isData($variable)
    {
        if ($variable !== false and $variable !== true and $variable != null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->num_hits = 0;

        $input = $this->input;
        $input = $this->agent_input;
        if ($this->agent_input == null or $this->agent_input == "") {
            $input = $this->subject;
        }

        if ($input == "at") {
            return;
        }

        $filtered_input = $this->assert($input, "at");
        $keywords = $this->keywords;
        if (strpos($filtered_input, "reset") !== false) {
            $this->hour = "X";
            $this->minute = "X";
            $this->day = "X";
            return;
        }

        if ($this->isAlpha($filtered_input) === true) {
            $this->tag = $filtered_input . "at";
            // reload with new at tag.
            $this->get();
        }

        $this->extractAt($filtered_input);
    }
}
