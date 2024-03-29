<?php
/**
 * Day.php
 *
 * @package default
 */

// TODO
// PDF and PNG rendering

// https://nrc.canada.ca/en/research-development/products-services/software-applications/sun-calculator/

namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Day extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {
        $this->dateline = false;
        $this->test = "Development code";

        $this->thing_report["info"] =
            "A DAY is the period from noon to noon in any given place.";
        $this->thing_report["help"] = "Click on the image for a PDF.";

        $this->resource_path = $GLOBALS["stack_path"] . "resources/";

        $command_line = null;

        $this->node_list = ["day" => ["day", "year", "uuid"]];

        //$this->current_time = $this->thing->time();

        $this->projected_time = $this->current_time;

        // Get some stuff from the stack which will be helpful.
        $this->entity_name = $this->thing->container["stack"]["entity_name"];

        $this->default_canvas_size_x = 2000;
        $this->default_canvas_size_y = 2000;

        $agent = new Retention($this->thing, "retention");
        $this->retain_to = $agent->retain_to;

        $agent = new Persistence($this->thing, "persistence");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->draw_center = false;
        $this->draw_outline = false; //Draw hexagon line

        $this->canvas_size_x = $this->default_canvas_size_x;
        $this->canvas_size_y = $this->default_canvas_size_y;

        $this->size = min(
            $this->default_canvas_size_x,
            $this->default_canvas_size_y
        );

        $this->day_indicators = [
            "MON" => ["monday", "mon", "M"],
            "TUE" => ["tuesday", "tue", "Tu"],
            "WED" => ["wednesday", "wed", "wday", "W"],
            "THU" => ["thursday", "thur", "Thu", "Th"],
            "FRI" => ["friday", "fri", "Fr", "F"],
            "SAT" => ["saturday", "sat", "Sa"],
            "SUN" => ["sunday", "sun", "Su"],
        ];

        $this->default_prime_meridian_offset = 0;
        $this->default_julian_correlation["mesoamerican"] = 584283; //GMT

        $this->initDay();
    }

    public function initDay()
    {
        $this->time_agent = new Time($this->thing, "time");
        $this->working_datum = $this->time_agent->datumTime(
            $this->projected_time
        );

        $this->long_count_rounds = [
            "baktun" => 20,
            "katun" => 20,
            "tun" => 20,
            "uinal" => 18,
            "kin" => 20,
        ];

        // Tzolk'in Calendar

        // http://dylansung.tripod.com/sapienti/maya/maya.htm
        // https://mayaarchaeologist.co.uk/2016/12/31/maya-calendar-system/#2
        // https://en.wikipedia.org/wiki/Tzolk%CA%BCin
        // Count of days

        // First item in array is the wikipedia,
        // 'Associated natural phenomena or meaning.

        // Index is the wikipedia Day Name.

        $this->tzolkin_days = [
            "Imix" => [
                "Waterlily",
                "Crocodile",
                "Alligator",
                "Birth",
                "Water",
                "Wine",
                "Sea Dragon",
            ],
            "Ik" => ["Wind", "Breath", "Life force", "Air", "Life"],
            "Akbal" => ["Darkness", "Night", "Early dawn"],
            "Kan" => ["Net", "Sacrifice", "Sky"],
            "Chicchan" => ["Cosmological snake", "Snake"],
            "Cimi" => ["Death"],
            "Manik" => ["Deer"],
            "Lamat" => ["Venus", "Star", "Ripe", "Ripeness", "Maize seeds"],
            "Muluc" => ["Jade", "Water", "Offering", "Moon"],
            "Oc" => ["Dog"],
            "Chuen" => ["Howler monkey", "Ancestor"],
            "Eb" => ["Rain", "Tooth/Jaw"],
            "Ben" => ["Green/young maize", "Seed", "Maize"],
            "Ix" => ["Jaguar"],
            "Men" => ["Eagle"],
            "Cib" => ["Wax", "Candle"],
            "Caban" => ["Earth"],
            "Etznab" => ["Flint", "Obsidian"],
            "Cauac" => ["Rain storm", "Storm"],
            "Ahau" => ["Lord", "Ruler", "Sun"],
        ];

        $this->haab_months = [
            "Pop" => ["mat"],
            "Wo'" => ["black conjunction"],
            "Sip" => ["red conjunction"],
            "Sotz'" => ["bat"],
            "Sek" => ["death"],
            "Xul" => ["dog"],
            "Yaxk'in" => ["new sun"],
            "Mol" => ["water"],
            "Ch'en" => ["black storm"],
            "Yax" => ["green storm"],
            "Sak" => ["white storm"],
            "Keh" => ["red storm"],
            "Mak" => ["enclosed"],
            "K'ank'in" => ["yellow sun"],
            "Muwan" => ["owl"],
            "Pax" => ["planting time"],
            "K'ayab" => ["turtle"],
            "Kumk'u" => ["granary"],
            "Wayeb'" => ["five unlucky days"],
        ];
    }

    public function formatDay($text = null)
    {
        // Placeholder.
        // Make more general.
        $date = date_create_from_format("Y-m-j", $text);
        $d = strtoupper(date_format($date, "Y M d D"));

        //$t = $this->current_time;
        //$d = strtoupper(date('Y M d D H:i', $t))

        return $d;
    }

    public function runDay($text = null)
    {
        /*
        $longitude_agent = new Longitude($this->thing, "longitude");

        // Cannot calculate local time without knowing longitude.
        if ($longitude_agent->longitude === false) {
            $this->response .= "Longitude not known. ";
        }

        $longitude = $longitude_agent->longitude;

        $latitude_agent = new Latitude($this->thing, "latitude");
        $latitude = $latitude_agent->latitude;
*/
        if ($this->latitude === false or strtolower($this->latitude) == "z") {
            $this->response .= "Latitude not known. ";
        }

        if ($this->latitude === false or $this->longitude === false) {
            return true;
        }

        $latitude = (float) $this->latitude;
        $longitude = (float) $this->latitude;

        $timestamp_epoch = (float) $this->timestampEpoch($text);
        $solar_array = date_sun_info($timestamp_epoch, $latitude, $longitude);
        $this->solar_array = $solar_array;
        //$this->timestamp_epoch = $timestamp_epoch;

        $arr = [
            //"night"=> "night",
            "astronomical twilight begin" => "astronomical twilight",
            "nautical twilight begin" => "nautical twilight",
            "civil twilight begin" => "civil twilight",
            "sunrise" => "day",
            "transit" => "day",
            "sunset" => "civil twilight",
            "civil twilight end" => "nautical twilight",
            "nautical twilight end" => "astronomical twilight",
            "astronomical twilight end" => "night",
        ];

        $this->day_solar_milestones = $arr;

        $message = "";
        $count = 0;
        foreach (range(0, 1, 1) as $period_index) {
            foreach ($this->day_solar_milestones as $period => $epoch) {
                // The datum returned is when this event will happen
                // as a DateTime (datum) object.

                $period_timestamp =
                    $this->working_datum->getTimestamp() +
                    $period_index * (60 * 60 * 24);

                $t = $this->projected_time;
                //$t = $this->current_time;
                $e = strtotime($t);

                $datum_projected = new \DateTime();
                $datum_projected->setTimestamp($period_timestamp);
                $datum = $this->twilightDay($period, $datum_projected);
                if ($datum === false) {
                    continue;
                }
                if ($period_timestamp < $e) {
                    continue;
                }

                if ($count == 0) {
                    $message .=
                        $period . " " . $datum->format("Y/m/d G:i:s") . " ";
                } else {
                    $message .=
                        $period . " " . $datum_projected->format("G:i:s") . " ";
                }
                $count += 1;

                $variable_text = str_replace(" ", "_", $period);

                if (
                    $this->solarDay($datum_projected)[$variable_text] <
                    $timestamp_epoch
                ) {
                    //
                    //                if ($this->solar_array[$variable_text] < $timestamp_epoch) {
                    $time_of_day = $period;
                }
                if ($count > 7) {
                    break;
                }
            }
        }
        $day_time = "night";
        if (
            isset($time_of_day) and
            isset($this->day_solar_milestones[$time_of_day])
        ) {
            $day_time = $this->day_solar_milestones[$time_of_day];
        }

        $tz = $datum_projected->getTimezone();
        $message .= $tz->getName();

        if (
            isset($this->day_twilight_flag) and
            $this->day_twilight_flag == "on"
        ) {
            $this->message = strtoupper($day_time) . " " . $message;
        } else {
            $this->message = strtoupper($day_time);
        }

        $this->day_time = $day_time;
    }

    public function timestampEpoch($text = null)
    {
        //$timestamp_epoch = time();
        $timestamp_epoch = $this->projected_time;

        if ($this->dateline !== false) {
            /*
https://www.w3schools.com/php/func_date_strtotime.asp
Note: Be aware of dates in the m/d/y or d-m-y formats;
 if the separator is a slash (/), then the American m/d/y
 is assumed. If the separator is a dash (-) or a dot (.),
 then the European d-m-y format is assumed.
 To avoid potential errors, you should YYYY-MM-DD dates or date_create_from_format() when possible.
*/
            //$timestamp_epoch = strtotime($this->dateline['year']."-".$this->dateline['month']."-".$this->dateline['day_number']);

            //$timestamp_epoch = strtotime("2021-10-24");
        }

        /*
https://www.php.net/manual/en/function.date-sun-info.php
 info at mobger dot de ¶
10 months ago
The relation between timestamp and geoposition is not good defined.
My try of a definition is:

date_sun_info —
Returns an array with information about sunset/sunrise and twilight begin/end
 as Unix-Timestamp for the the geoposition, which must have the same (local) date
 as the timestamp in the parameter-block for the function `date_sun_info`.
*/

        /*
Dev review against
https://nrc.canada.ca/en/research-development/products-services/software-applications/sun-calculator/
Sunrise sunset full year
10 October 2021
Vancouver
Oct 24 2021,5:38,6:16,6:48,11:57,17:04,17:37,18:14,10.27,1.09,11.36,1:59:19

DAY | DAY astronomical twilight begin 2021/10/24 6:01:53 
 nautical twilight begin 6:38:43 civil twilight begin 7:15:53
 sunrise 7:48:31 transit 12:56:36 sunset 18:04:41
 civil twilight end 18:37:19 nautical twilight end 19:14:29
 astronomical twilight begin 6:03:21 America/Los_Angeles 

*/

        //        $timestamp_epoch = time();

        if ($text != null and is_string($text)) {
            $timestamp_epoch = strtotime($text);
        }

        if (is_a($text, "DateTime")) {
            $timestamp_epoch = $text->getTimestamp();
        }

        if ($text == null) {
            $timestamp_epoch = strtotime($this->current_time);
        }

        $this->timestamp_epoch = $timestamp_epoch;
        return $timestamp_epoch;
    }

    public function solarDay($text = null)
    {
        /*
        $timestamp_epoch = time();

        if ($text != null and is_string($text)) {
            $timestamp_epoch = strtotime($text);
        }

        if (is_a($text, "DateTime")) {
            $timestamp_epoch = $text->getTimestamp();
        }
*/
        $timestamp_epoch = (int) $this->timestampEpoch($text);
        $latitude = (float) $this->latitude;
        $longitude = (float) $this->longitude;
        $solar_array = date_sun_info($timestamp_epoch, $latitude, $longitude);

        return $solar_array;
    }

    public function twilightDay($text, $datum = null)
    {
        if ($text == "night") {
            $text = "astronomical twilight begin";
        }

        $variable_text = str_replace(" ", "_", $text);

        // $seconds_to_event =
        //     $this->solar_array[$variable_text] - $this->timestamp_epoch;

        //$time_agent = new Time($this->thing, "time");
        //$working_datum = $time_agent->datumtime($this->current_time);
        $working_datum = $this->working_datum;
        if ($datum != null) {
            $working_datum = $datum;
        }

        $s = $this->solarDay($working_datum)[$variable_text];

        if ($s === true) {
            return false;
        } // No event.

        $seconds_to_event =
            $this->solarDay($working_datum)[$variable_text] -
            $this->timestamp_epoch;

        if ($seconds_to_event < 0) {
            $working_datum->sub(
                new \DateInterval("PT" . -1 * $seconds_to_event . "S")
            );
        } else {
            $working_datum->add(
                new \DateInterval("PT" . $seconds_to_event . "S")
            );
        }

        return $working_datum;
    }

    public function set()
    {
        $this->setDay();
    }

    /**
     *
     * @param unknown $text (optional)
     */
    function getQuickresponse($text = null)
    {
        if ($text == null) {
            $text = $this->web_prefix;
        }
        $agent = new Qr($this->thing, $text);
        $this->quick_response_png = $agent->PNG_embed;
    }

    /**
     *
     */
    public function getNuuid()
    {
        $agent = new Nuuid($this->thing, "nuuid");
        $this->nuuid_png = $agent->PNG_embed;
    }

    /**
     *
     * @param unknown $input
     */
    function getWhatis($input)
    {
        $whatis = "day";
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), $whatis . " is")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen($whatis . " is")
            );
        } elseif (($pos = strpos(strtolower($input), $whatis)) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen($whatis));
        }

        //$filtered_input = ltrim(strtolower($whatIWant), " ");
        $filtered_input = ltrim($whatIWant, " ");

        $this->whatis = $filtered_input;
    }

    // TODO Remove this function. Refactor as a stamp call.
    /**
     *
     * @param unknown $t (optional)
     * @return unknown
     */
    public function timestampDay($t = null)
    {
        //        $s = $this->thing->thing->created_at;

        if (!isset($this->retain_to)) {
            $text = "X";
        } else {
            $t = $this->retain_to;
            $text = "GOOD UNTIL " . strtoupper(date("Y M d D H:i", $t));
            //$text = "CLICK FOR PDF";
        }
        $this->timestamp = $text;
        return $this->timestamp;
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        return $this->thing_report;
    }

    public function authorityDay()
    {
        $a =
            "https://nrc.canada.ca/en/research-development/products-services/software-applications/sun-calculator/";
        $this->authority_day = $a;
        return $a;
    }
    /**
     *
     */
    public function makeChoices()
    {
        $this->choices = false;
        $this->thing_report["choices"] = $this->choices;
    }

    public function mesoamericanDay()
    {
    }

    public function calendarroundDay()
    {
        $tzolkin = $this->tzolkinDay();
        $haab = $this->haabDay();
        $lord_of_the_night = $this->lordofthenightDay();

        return $tzolkin . " " . $haab . " " . $lord_of_the_night;
    }

    public function tzolkinDay()
    {
        $julian_day_number = $this->julianDay();

        // Today, 5 December 2020 (UTC), in the Long Count is 13.0.8.1.6 (using GMT correlation).
        // https://en.wikipedia.org/wiki/Mesoamerican_Long_Count_calendar
        //$gmt_julian_day_number = 584283;

        $gmt_julian_day_number =
            $this->default_julian_correlation["mesoamerican"];

        $days_since_correlation = $julian_day_number - $gmt_julian_day_number;

        // https://sudonull.com/post/158842-Ancient-Mayan-calendar-how-to-calculate-the-date
        $t1 = ($days_since_correlation + 19) % 20;
        $t2 = (($days_since_correlation + 3) % 13) + 1;

        $days = $this->tzolkin_days;

        $numbered_days = array_keys($days);

        $t1_text = $numbered_days[$t1];

        if (
            isset($this->day_translate_flag) and
            $this->day_translate_flag == "on"
        ) {
            $t1_text = ucwords($days[$t1_text][0]);
        }

        return $t1_text . " " . $t2;
    }

    public function lordofthenightDay()
    {
        $julian_day_number = $this->julianDay();

        // Today, 5 December 2020 (UTC), in the Long Count is 13.0.8.1.6 (using GMT correlation).
        // https://en.wikipedia.org/wiki/Mesoamerican_Long_Count_calendar
        //$gmt_julian_day_number = 584283;

        $gmt_julian_day_number =
            $this->default_julian_correlation["mesoamerican"];

        $days_since_correlation = $julian_day_number - $gmt_julian_day_number;

        $g = (($days_since_correlation + 8) % 9) + 1;

        return "G" . $g;
    }

    public function haabDay()
    {
        $julian_day_number = $this->julianDay();

        // Today, 5 December 2020 (UTC), in the Long Count is 13.0.8.1.6 (using GMT correlation).
        // https://en.wikipedia.org/wiki/Mesoamerican_Long_Count_calendar
        //$gmt_julian_day_number = 584283;

        $gmt_julian_day_number =
            $this->default_julian_correlation["mesoamerican"];

        $days_since_correlation = $julian_day_number - $gmt_julian_day_number;

        $months = $this->haab_months;

        // integer, value = divmod(integer, base)
        // H1, H2 = divmod((M + 348) % 365, 20)
        // h1 quotient
        // h2 remainder

        // https://keisan.casio.com/exec/system/1345696485
        $n = ($days_since_correlation + 348) % 365;

        $h1 = $quotient = (int) ($n / 20);
        $h2 = $remainder = $n % 20;

        $numbered_months = array_keys($months);

        $h1_text = $numbered_months[$h1];

        if (
            isset($this->day_translate_flag) and
            $this->day_translate_flag == "on"
        ) {
            $h1_text = ucwords($months[$h1_text][0]);
        }

        $text = $h1_text . " " . $h2;

        return $text;
    }

    public function longcountDay($flagMeridian = false)
    {
        // As best as I can tell.
        // TODO - Include reference. See Calendar.

        $wheels = $this->long_count_rounds;

        $counts = $this->wheelsDay($wheels);

        $text = implode(".", $counts);

        $prime_meridian_offset = $this->default_prime_meridian_offset;

        if ($flagMeridian) {
            if ($prime_meridian_offset == 0) {
                $text .= " at Prime Meridian";
            }
        }

        return $text;
    }

    public function wheelCount($number, $wheels)
    {
        $remainder = $number;
        foreach ($wheels as $wheel_name => $wheel) {
            $wheel_modulo = $wheel;
            if (is_array($wheel)) {
                $a = $this->wheelCount($number, $wheel);
                $wheel_modulo = count($a);
            }

            $seen_wheel = false;
            $factor = 1;
            foreach ($wheels as $inner_wheel_name => $inner_wheel) {
                $inner_wheel_modulo = $inner_wheel;
                if (is_array($inner_wheel)) {
                    $a = $this->wheelCount($number, $inner_wheel);
                    $inner_wheel_modulo = count($a);
                    //$inner_wheel_modulo = count($inner_wheel);
                }

                if ($inner_wheel_name != $wheel_name and $seen_wheel == false) {
                    continue;
                }

                if ($seen_wheel == false) {
                    $seen_wheel = true;
                    continue;
                }

                $factor = $factor * $inner_wheel_modulo;
            }

            $whole_parts = intval($remainder / $factor);

            if ($whole_parts == 0) {
                $long_count[$wheel_name] = 0;
                continue;
            }

            $long_count[$wheel_name] = $whole_parts;

            $remainder = $remainder - $whole_parts * $factor;
        }

        return $long_count;
    }

    public function wheelsDay($wheels = null, $label = false)
    {
        // TODO. Currently out by one kin.
        // Review.

        if ($wheels == null) {
            return true;
        }

        $julian_day_number = $this->julianDay();

        // Today, 5 December 2020 (UTC), in the Long Count is 13.0.8.1.6 (using GMT correlation).
        // https://en.wikipedia.org/wiki/Mesoamerican_Long_Count_calendar
        //$gmt_julian_day_number = 584283;

        $gmt_julian_day_number =
            $this->default_julian_correlation["mesoamerican"];

        $days_since_correlation = $julian_day_number - $gmt_julian_day_number;

        //$gmt_plus_two_julian_day_number = $gmt_julian_day_number + 2;

        $long_count_day_number = $days_since_correlation;

        $remainder = $long_count_day_number;

        $long_count = $this->wheelCount($remainder, $wheels);
        return $long_count;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $sms = "DAY";

        if (isset($this->day_julian_flag) and $this->day_julian_flag == "on") {
            $julian_day_number = $this->julianDay();
            $sms .= " JD " . $julian_day_number;
        }

        if (
            isset($this->day_mesoamerican_flag) and
            $this->day_mesoamerican_flag == "on"
        ) {
            $long_count_day = $this->longcountDay(true);

            $calendar_round_day = $this->calendarroundDay();
            $sms .=
                " MESOAMERICAN " .
                $long_count_day .
                " " .
                $calendar_round_day .
                "";
        }

        $days = [];
        if (isset($this->days)) {
            $days = $this->days;
        }

        /*
        $day_text = "No day found.";
        if (isset($this->day)) {
            $day_text = $this->day;
            $sms .= " | " . $day_text;
        }
*/
        $day_text = "Merp.";
        if (isset($this->day_time)) {
            $day_time_text = $this->day_time;
            $sms .= " | " . $day_time_text;
        }

        $sms .= " | " . $this->message . " " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     */
    public function makeMessage()
    {
        $message = "Made a day for you.<br>";

        $uuid = $this->uuid;

        $message .=
            "Keep on stacking.\n\n<p>" .
            $this->web_prefix .
            "thing/$uuid/day.png\n \n\n<br> ";
        $message .=
            '<img src="' .
            $this->web_prefix .
            "thing/" .
            $uuid .
            '/day.png" alt="day" height="92" width="92">';

        $this->thing_report["message"] = $message;
    }

    /**
     *
     */
    public function setDay()
    {
        return;
        $this->thing->Write(["day", "decimal"], $this->decimal_day);

        $this->thing->log(
            $this->agent_prefix .
                " saved decimal day " .
                $this->decimal_day .
                ".",
            "INFORMATION"
        );
    }

    /**
     *
     * @return unknown
     */
    public function getDay()
    {
        $longitude_agent = new Longitude($this->thing, "longitude");

        // Cannot calculate local time without knowing longitude.
        if ($longitude_agent->longitude === false) {
            $this->response .= "Longitude not known. ";
        }

        $this->longitude = $longitude_agent->longitude;

        $latitude_agent = new Latitude($this->thing, "latitude");
        $this->latitude = $latitude_agent->latitude;
    }

    /**
     *
     */
    public function deprecate_initDay()
    {
        $this->number_agent = new Number($this->thing, "number");
    }

    public function run()
    {
        $this->runDay();
    }

    public function datestringDay($datum)
    {
        $date_string =
            $datum["year"] .
            "-" .
            str_pad($datum["month"], 2, "0", STR_PAD_LEFT) .
            "-" .
            str_pad($datum["day_number"], 2, "0", STR_PAD_LEFT);

        return $date_string;
    }
    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/day.pdf";
        $this->node_list = ["day" => ["day"]];

        $web = "";
        if (
            isset($this->day_mesoamerican_flag) and
            $this->day_mesoamerican_flag == "on"
        ) {
            $long_count_day = $this->longcountDay();

            $calendar_round_day = $this->calendarroundDay();

            $web .= "" . $long_count_day . " " . $calendar_round_day . "";

            $web .= "<br>";
        }

        $web .= $this->formatDay($this->datestringDay($this->dateline));

        $web .= "<br>";
        $latitude_text = $this->formatLatitude($this->latitude);
        $longitude_text = $this->formatLongitude($this->longitude);
        $web .= $latitude_text . " " . $longitude_text;
        $web .= "<br>";

        if ($this->isToday($this->current_time)) {
            $day_text = "X";
            if (isset($this->day_time)) {
                $day_time_text = $this->day_time;
                $web .= strtoupper($day_time_text);
            }
        }
        if (
            isset($this->day_authority_flag) and
            $this->day_authority_flag == "on"
        ) {
            $web .= $this->authorityDay();
            $web .= "<br>";
        }

        $web .= "<p>";

        /*
                $this->itemToken($item_slug);
                $web .= $this->web_token[$item_slug];

*/
        $thing = new Thing(null);
        $thing->Create("token", $this->from, "calendar-page-token");

        $token_handler = new Token($thing, "calendar-page-token");

        if (
            isset($token_handler->itemToken) and
            isset($token_handler->itemToken["calendar-page"])
        ) {
            $token_handler->itemToken["calendar-page"];
            $web .= $token_handler->web_token["calendar-page"];
        }
        $web .= '<a href="' . $link . '">';
        $web .= $this->html_image;
        $web .= "</a>";
        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    /**
     *
     */
    public function makeTXT()
    {
        $txt = "This is a DAY";
        $txt .= "\n";

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    /**
     *
     * @param unknown $r
     * @param unknown $g
     * @param unknown $b
     */
    function rgbcolor($r, $g, $b)
    {
        $this->rgb = imagecolorallocate($this->image, $r, $g, $b);
    }

    public function julianDay($text = null)
    {
        //$time_agent = new Time($this->thing, "time");
        $time_string = $this->time_agent->getTime($text);

        $dateValue = strtotime($time_string);
        // Expect a UTC Zulu time.

        $year = date("Y", $dateValue);
        $month = date("m", $dateValue);
        $day = date("d", $dateValue);

        // See PHP Manual.
        // While there is juliantojd that is for converting julian dates to a day numbet.
        // There is a high-likelyhood we are in the second Gregorian era.

        // TODO - Recognize eras.

        // Code from PHP Manual comment for generating decimal julian day.
        $julianDate = gregoriantojd($month, $day, $year);
        //correct for half-day offset
        $dayfrac = date("G") / 24 - 0.5;
        if ($dayfrac < 0) {
            $dayfrac += 1;
        }

        //now set the fraction of a day
        $frac = $dayfrac + (date("i") + date("s") / 60) / 60 / 24;

        $julianDate = $julianDate + $frac;

        return $julianDate;
    }

    /**
     *
     * @return unknown
     */
    public function makePNG()
    {
        if (isset($this->canvas_size_x)) {
            $canvas_size_x = $this->canvas_size_x;
            $canvas_size_y = $this->canvas_size_x;
        } else {
            $canvas_size_x = 164;
            $canvas_size_y = 164;
        }
        $this->image = imagecreatetruecolor($canvas_size_x, $canvas_size_y);
        //$this->image = imagecreatetruecolor(164, 164);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        // For Vancouver Pride 2018

        // https://en.wikipedia.org/wiki/Rainbow_flag
        // https://en.wikipedia.org/wiki/Rainbow_flag_(LGBT_movement)
        // https://www.schemecolor.com/lgbt-flag-colors.php

        $this->electric_red = imagecolorallocate($this->image, 231, 0, 0);
        $this->dark_orange = imagecolorallocate($this->image, 255, 140, 0);
        $this->canary_yellow = imagecolorallocate($this->image, 255, 239, 0);
        $this->la_salle_green = imagecolorallocate($this->image, 0, 129, 31);
        $this->blue = imagecolorallocate($this->image, 0, 68, 255);
        $this->patriarch = imagecolorallocate($this->image, 118, 0, 137);

        $this->flag_red = imagecolorallocate($this->image, 231, 0, 0);
        $this->flag_orange = imagecolorallocate($this->image, 255, 140, 0);
        $this->flag_yellow = imagecolorallocate($this->image, 255, 239, 0);
        $this->flag_green = imagecolorallocate($this->image, 0, 129, 31);
        $this->flag_blue = imagecolorallocate($this->image, 0, 68, 255);
        // Indigo https://www.rapidtables.com/web/color/purple-color.html
        $this->flag_indigo = imagecolorallocate($this->image, 75, 0, 130);
        $this->flag_violet = imagecolorallocate($this->image, 118, 0, 137);
        $this->flag_grey = $this->grey;

        $this->indigo = imagecolorallocate($this->image, 75, 0, 130);

        $this->ice_green = imagecolorallocate($this->image, 126, 217, 195);
        $this->blue_ice = imagecolorallocate($this->image, 111, 122, 159);
        $this->artic_ice = imagecolorallocate($this->image, 195, 203, 217);
        $this->ice_cold = imagecolorallocate($this->image, 165, 242, 243);
        $this->white_ice = imagecolorallocate($this->image, 225, 231, 228);

        $this->ice_color_palette = [
            $this->ice_green,
            $this->blue_ice,
            $this->artic_ice,
            $this->ice_cold,
            $this->white_ice,
        ];

        // Patriarch as a color name.
        // https://www.schemecolor.com/lgbt-flag-colors.php
        $this->color_palette = [
            $this->electric_red,
            $this->dark_orange,
            $this->canary_yellow,
            $this->la_salle_green,
            $this->blue,
            $this->patriarch,
        ];

        $this->flag_color_palette = [
            $this->flag_red,
            $this->flag_orange,
            $this->flag_yellow,
            $this->flag_green,
            $this->flag_blue,
            $this->flag_indigo,
            $this->flag_violet,
            $this->flag_grey,
        ];

        imagefilledrectangle(
            $this->image,
            0,
            0,
            $canvas_size_x,
            $canvas_size_y,
            $this->white
        );

        $textcolor = imagecolorallocate($this->image, 0, 0, 0);
        //if (isset($this->text_color)) {
        //    $textcolor = $this->text_color;
        //}

        $this->drawDay();

        // Write the string at the top left
        $border = 30;
        $r = 1.165;

        $radius = ($r * ($canvas_size_x - 2 * $border)) / 3;

        // devstack add path
        $font = $this->default_font;
        $text = "A day in slices...";
        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);

        $size = $canvas_size_x - 90;
        $size = 20;
        $angle = 0;
        if (file_exists($font)) {
            $bbox = imagettfbbox($size, $angle, $font, $text);
            $bbox["left"] = 0 - min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $bbox["top"] = 0 - min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
            $bbox["width"] =
                max($bbox[0], $bbox[2], $bbox[4], $bbox[6]) -
                min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $bbox["height"] =
                max($bbox[1], $bbox[3], $bbox[5], $bbox[7]) -
                min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
            extract($bbox, EXTR_PREFIX_ALL, "bb");
            //check width of the image
            $width = imagesx($this->image);
            $height = imagesy($this->image);
            $pad = 0;
        }
        //        imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $this->black, $font, $text);

        //imagestring($this->image, 2, 140, 0, $this->thing->nuuid, $textcolor);

        // https://stackoverflow.com/questions/14549110/failed-to-delete-buffer-no-buffer-to-delete

        if (ob_get_contents()) {
            ob_clean();
        }

        ob_start();
        imagepng($this->image);
        $imagedata = ob_get_contents();

        ob_end_clean();

        $this->thing_report["png"] = $imagedata;

        $response =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="day"/>';

        $this->html_image =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="day"/>';

        $this->PNG_embed = "data:image/png;base64," . base64_encode($imagedata);

        $this->PNG = $imagedata;

        return $response;

        $this->PNG = $image;
        $this->thing_report["png"] = $image;

        return;
    }

    public function drawDay($type = null)
    {
        if ($type == null) {
            $type = $this->type;
        }
        if ($type == "wedge") {
            $this->wedgeDay();
            return;
        }

        $this->sliceDay();
    }

    public function sliceDay()
    {
        $size = null;
        if ($size == null) {
            $size = $this->size;
        }
        $border = 100;
        $size = 1000 - $border;

        if (isset($this->canvas_size_x)) {
            $canvas_size_x = $this->canvas_size_x;
            $canvas_size_y = $this->canvas_size_y;
        } else {
            $canvas_size_x = $this->default_canvas_size_x;
            $canvas_size_y = $this->default_canvas_size_y;
        }

        $width_slice = ($canvas_size_x - 2 * $border) / 24;

        // Draw out the state
        $center_x = $canvas_size_x / 2;
        $center_y = $canvas_size_y / 2;

        // devstack rotation not yet implemented
        if (!isset($this->angle)) {
            $this->angle = 0;
        }

        foreach (range(0, 24, 1) as $i) {
            imageline(
                $this->image,
                $width_slice * $i,
                0,
                $width_slice * $i,
                $canvas_size_y,
                $this->black
            );
        }
    }
    // Not sure about this pattern.
    // But I need a dot to represent a day.
    public function drawDot(
        $text,
        $angle,
        $radius,
        $size,
        $offset = 0,
        $colour = null
    ) {
        if ($colour == null) {
            $colour = $this->black;
        }
        // angle in degrees
        //imagesetthickness($this->image, 5);
        //$init_angle = (-1 * pi()) / 2;
        //$angle = (2 * 3.14159) / 24;
        //$x_pt =  230;
        //$y_pt = 230;

        $angle_radians = ($angle / 180) * pi();

        //foreach (range(0, 24 - 1, 1) as $i) {
        $x_dot = ($radius + $offset) * cos($angle_radians + $this->init_angle);
        $y_dot = ($radius + $offset) * sin($angle_radians + $this->init_angle);


        imagearc(
            $this->image,
            $this->center_x + $x_dot,
            $this->center_y + $y_dot,
            2 * $size,
            2 * $size,
            0,
            360,
            $colour
        );
    }
    // And then to build Day agent
    public function extractHour($text = null)
    {
        if ($text == null) {
            $text = $this->current_time;
        }

        $datum = new \DateTime();
        $datum->setTimestamp(strtotime($this->current_time));

        $hour = $datum->format("H");
        return $hour;
    }

    // And an Minute agent
    public function extractMinute($text = null)
    {
        if ($text == null) {
            $text = $this->current_time;
        }
        $datum = new \DateTime();
        $datum->setTimestamp(strtotime($this->current_time));

        $minute = $datum->format("i");
        return $minute;
    }

    //And a Day Number agent

    // And an Minute agent
    public function extractDaynumber($text = null)
    {
        if ($text == null) {
            $text = $this->current_time;
        }
        $datum = new \DateTime();
        $datum->setTimestamp(strtotime($this->current_time));

        $day_number = $datum->format("N");
        return $day_number;
    }

    // And a new agent today...
    public function isToday($timestamp_current_time = null)
    {
        $datum_current_time = new \DateTime();
        //                $datum_current_time->setTimestamp(strtotime($this->current_time));

        $timestamp_current_time = $this->timestampTime($datum_current_time);

        $day_number = $this->extractDaynumber($timestamp_current_time);
        //$day_number = $this->extractDaynumber($t);
        $minute = $this->extractMinute($timestamp_current_time);
        $hour = $this->extractHour($timestamp_current_time);

        // An hour is 24 hours of 60 minutes.
        $day_percent = ($minute + $hour * 60) / (24 * 60);

        $day_degrees = (float) $day_percent * 360;
        $day_radians = ((float) $day_degrees / 180) * pi();

        $timestamp_working_time = $this->timestampTime($this->working_datum);
        //$t = $timestamp_working_datum;

        $epoch_working = strtotime($timestamp_working_time);
        $epoch_current = strtotime($timestamp_current_time);
        $epoch_start_of_today = $epoch_current - $day_percent * 24 * 60 * 60;
        $epoch_end_of_today =
            $epoch_current + (1 - $day_percent) * 24 * 60 * 60;

        $sunFlag = false;

        if (
            $epoch_working > $epoch_start_of_today and
            $epoch_working < $epoch_end_of_today
        ) {
            $sunFlag = true;
        }
        return $sunFlag;
    }

    public function datumText($text)
    {
        // text with a fully qualified time.

        $datum = new \DateTime();
        $datum->setTimestamp(strtotime($text));

        $t = $this->timestampTime($datum);

        return $t;
    }

    public function wedgeDay()
    {
        imagesetthickness($this->image, 1);

        $size = null;
        if ($size == null) {
            $size = $this->size;
        }
        $border = 100;
        $size = 1000 - $border;

        if (isset($this->canvas_size_x)) {
            $canvas_size_x = $this->canvas_size_x;
            $canvas_size_y = $this->canvas_size_y;
        } else {
            $canvas_size_x = $this->default_canvas_size_x;
            $canvas_size_y = $this->default_canvas_size_y;
        }

        // Draw out the state
        $center_x = $canvas_size_x / 2;
        $center_y = $canvas_size_y / 2;

        $this->center_x = $center_x;
        $this->center_y = $center_y;

        // devstack rotation not yet implemented
        if (!isset($this->angle)) {
            $this->angle = 0;
        }

        $i = (-1 * pi()) / 2;
        $this->init_angle = $i;
        $angle = (2 * 3.14159) / 24;
        //$x_pt =  230;
        //$y_pt = 230;
        /*
Draw the 24 hours.
We mostly agree on that it seems.
*/

        foreach (range(0, 24 - 1, 1) as $i) {
            $this->drawTick(null, $i * (360 / 24), 50, $size - 100, 0);

            //$x_pt = $size * cos($angle * $i + $this->init_angle);
            //$y_pt = $size * sin($angle * $i + $this->init_angle);
            /*
            imageline(
                $this->image,
                $center_x + $x_pt,
                $center_y + $y_pt,
                $center_x + $x_pt,
                $center_y + $y_pt,
                $this->black
            );
*/
            /*
            imageline(
                $this->image,
                $center_x,
                $center_y,
                $center_x + $x_pt,
                $center_y + $y_pt,
                $this->black
            );
*/
        }

        $radius = $size;

        /*
Draw a dot to represent the time.
And the current position of the Sun?
*/

        $datum_current_time = new \DateTime();
        //                $datum_current_time->setTimestamp(strtotime($this->current_time));

        $timestamp_current_time = $this->timestampTime($datum_current_time);

        $day_number = $this->extractDaynumber($timestamp_current_time);
        //$day_number = $this->extractDaynumber($t);
        $minute = $this->extractMinute($timestamp_current_time);
        $hour = $this->extractHour($timestamp_current_time);

        // An hour is 24 hours of 60 minutes.
        $day_percent = ($minute + $hour * 60) / (24 * 60);

        $day_degrees = (float) $day_percent * 360;
        $day_radians = ((float) $day_degrees / 180) * pi();
        //$day_radians = pi();

        /*
Now for projected time
*/

        $sunFlag = $this->isToday();

        if ($sunFlag) {
            $dot_offset = 30;
            $dot_size = 20;

            $this->drawDot(null, $day_degrees, $radius, $dot_size, $dot_offset);
        }

        /*
Now draw the twilight.
*/
        $angle = 20;
        $length = 50;
        //    $radius = $size;
        $text = "tick";

        $arc = [];
        $arc_day = [];
        $period_index = 0;
        $period_timestamp =
            $this->working_datum->getTimestamp() +
            $period_index * (60 * 60 * 24);

        $datum_projected = new \DateTime();
        $datum_projected->setTimestamp($period_timestamp);

        $a = $this->solarDay($datum_projected);
        foreach ($a as $period_name => $period) {
            $period_timestamp = $this->working_datum->getTimestamp();

            $datum_projected = new \DateTime();
            $datum_projected->setTimestamp($period_timestamp);

            $datum = $this->twilightDay($period_name, $datum_projected);
            if ($datum === false) {
                continue;
            }
            $t = $datum->format("G:i:s");
            // dev?

            $parts = explode(":", $t);
            $angle =
                (($parts[0] * 60 * 60 + $parts[1] * 60 + $parts[2]) /
                    (24 * 60 * 60)) *
                360;
            imagesetthickness($this->image, 2);
            //if ($period_name == "sunset" or $period_name == "sunrise") {
            //    imagesetthickness($this->image, 7);
            //}
            if (strpos($period_name, "astronomical") !== false) {
                $arc[] = $angle;
                imagesetthickness($this->image, 7);
            }

            if (strpos($period_name, "sunrise") !== false) {
                $arc_day[] = $angle;
                $colour = $this->blue;
                imagesetthickness($this->image, 7);
            }

            if (strpos($period_name, "sunset") !== false) {
                $arc_day[] = $angle;
                $colour = $this->blue;
                imagesetthickness($this->image, 7);
            }

            $offset = 0;
            $this->drawTick($text, $angle, $radius, $length, $offset, $colour);
            $colour = $this->black;
        }

        imagesetthickness($this->image, 3);

        imagearc(
            $this->image,
            $center_x,
            $center_y,
            2 * $size,
            2 * $size,
            0,
            360,
            $this->black
        );

        imagesetthickness($this->image, 7);

        if (isset($arc[0]) and isset($arc[1])) {
            imagearc(
                $this->image,
                $center_x,
                $center_y,
                2 * $size,
                2 * $size,
                $arc[1] + ($this->init_angle * 180) / pi(),
                $arc[0] + ($this->init_angle * 180) / pi(),
                $this->black
            );
        }
        imagearc(
            $this->image,
            $center_x,
            $center_y,
            2 * $size,
            2 * $size,
            $arc_day[0] + ($this->init_angle * 180) / pi(),
            $arc_day[1] + ($this->init_angle * 180) / pi(),
            $this->blue
        );
    }

    public function get()
    {
        $time_string = $this->thing->Read(["day", "refreshed_at"]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(["day", "refreshed_at"], $time_string);
        }

        $this->getDay();
    }

    public function drawTick(
        $text,
        $angle,
        $radius,
        $length,
        $offset = 0,
        $colour = null
    ) {
        if ($colour == null) {
            $colour = $this->black;
        }
        // angle in degrees
        //imagesetthickness($this->image, 5);
        //$init_angle = (-1 * pi()) / 2;
        //$angle = (2 * 3.14159) / 24;
        //$x_pt =  230;
        //$y_pt = 230;

        $angle_radians = ($angle / 180) * pi();

        //foreach (range(0, 24 - 1, 1) as $i) {
        $x_start =
            ($radius + $offset) * cos($angle_radians + $this->init_angle);
        $y_start =
            ($radius + $offset) * sin($angle_radians + $this->init_angle);

        $x_end =
            ($radius + $offset + $length) *
            cos($angle_radians + $this->init_angle);
        $y_end =
            ($radius + $offset + $length) *
            sin($angle_radians + $this->init_angle);

        imageline(
            $this->image,
            $this->center_x + $x_start,
            $this->center_y + $y_start,
            $this->center_x + $x_end,
            $this->center_y + $y_end,
            $colour
        );
    }
    /**
     *
     * @return unknown
     */
    public function makePDF()
    {
        if (
            $this->default_pdf_page_template === null or
            !file_exists($this->default_pdf_page_template)
        ) {
            $this->thing_report["pdf"] = false;
            return $this->thing_report["pdf"];
        }

        $this->getWhatis($this->subject);
        try {
            // initiate FPDI
            $pdf = new Fpdi\Fpdi();

            $pdf->setSourceFile($this->default_pdf_page_template);
            $pdf->SetFont("Helvetica", "", 10);

            $tplidx1 = $pdf->importPage(1, "/MediaBox");
            $s = $pdf->getTemplatesize($tplidx1);

            $pdf->addPage($s["orientation"], $s);
            $pdf->useTemplate($tplidx1);
            /*
            if (isset($this->hextile_PNG)) {
                $top_x = -6;
                $top_y = 11;

                $pdf->Image(
                    $this->hextile_PNG,
                    $top_x,
                    $top_y,
                    -300,
                    -300,
                    'PNG'
                );
            }
*/
            $this->getNuuid();
            //$pdf->Image($this->nuuid_png, 5, 18, 20, 20, 'PNG');
            $pdf->Image($this->PNG_embed, 7, 30, 200, 200, "PNG");

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(1, 1);

            $pdf->SetFont("Helvetica", "", 26);
            $this->txt = "" . $this->whatis . ""; // Pure uuid.

            $pdf->SetXY(140, 7);
            $text = $this->whatis;
            $line_height = 20;
            $pdf->MultiCell(150, $line_height, $text, 0);

            if (isset($this->hextile_PNG)) {
                $top_x = -6;
                $top_y = 11;

                $pdf->Image(
                    $this->hextile_PNG,
                    $top_x,
                    $top_y,
                    -300,
                    -300,
                    "PNG"
                );
            }

            // Page 2
            $tplidx2 = $pdf->importPage(2);

            $pdf->addPage($s["orientation"], $s);

            $pdf->useTemplate($tplidx2, 0, 0);
            // Generate some content for page 2

            $pdf->SetFont("Helvetica", "", 10);
            $this->txt = "" . $this->uuid . ""; // Pure uuid.

            $link = $this->web_prefix . "thing/" . $this->uuid . "/day";

            $this->getQuickresponse($link);
            $pdf->Image($this->quick_response_png, 175, 5, 30, 30, "PNG");

            //$pdf->Link(175,5,30,30, $link);

            $pdf->SetTextColor(0, 0, 0);

            $pdf->SetXY(15, 7);

            $line_height = 4;

            $t = $this->thing_report["sms"];

            $t = str_replace(" | ", "\n", $t);

            $pdf->MultiCell(150, $line_height, $t, 0);

            //$pdf->Link(15,7,150,10, $link);

            $y = $pdf->GetY() + 0.95;
            $pdf->SetXY(15, $y);
            $text = "v0.0.1";
            $pdf->MultiCell(
                150,
                $line_height,
                $this->agent_name . " " . $text,
                0,
                "L"
            );

            $y = $pdf->GetY() + 0.95;

            $pdf->SetXY(15, $y);
            $text =
                "Pre-printed text and graphics (c) 2020 " . $this->entity_name;
            $pdf->MultiCell(150, $line_height, $text, 0, "L");

            // Good until?
            $text = $this->timestampDay();
            $pdf->SetXY(175, 35);
            $pdf->MultiCell(30, $line_height, $text, 0, "L");

            $image = $pdf->Output("", "S");
            $this->thing_report["pdf"] = $image;
        } catch (Exception $e) {
            $this->thing->console("Caught exception: ", $e->getMessage(), "\n");
        }

        return $this->thing_report["pdf"];
    }

    public function isDay($text)
    {
        $this->parsed_date = date_parse($text);
        $day = $this->parsed_date["day"];

        if ($day !== false) {
            return $day;
        }

        foreach ($this->day_indicators as $day => $day_indicators) {
            if (stripos($text, $day) !== false) {
                return $day;
            }

            foreach ($day_indicators as $i => $day_indicator) {
                if (stripos($text, $day_indicator) !== false) {
                    return $day_indicator;
                }
            }
        }

        return false;
    }

    // TODO
    public function extractDays($text = null)
    {
        $days = [];

        if ($text == null or $text == "") {
            return true;
        }

        if (!isset($this->ngram_agent)) {
            $this->ngram_agent = new Ngram($this->thing, "ngram");
        }

        $tokens = [];
        foreach (range(0, 4, 1) as $i) {
            $new_grams = $this->ngram_agent->extractNgrams($text, $i);
            $tokens = array_merge($tokens, $new_grams);
        }
        foreach ($tokens as $i => $token) {
            if ($token == "") {
                continue;
            }
            $response = $this->isDay($token);
            if ($response === false) {
                continue;
            }

            // TODO refactor
            if (is_string($response)) {
                $response = intval($response);
            }
            if (is_integer($response)) {
                // Check if a day has been mis-categorized as a year.
                $this->parsed_date = date_parse($text);
                $day = $this->parsed_date["day"];
                if ($response == $day) {
                    continue;
                }

                $day_text = strval($response);

                //$era = $this->eraDay($text);
                $day = ["day" => $day_text, "era" => $era];
                $days[] = $day;
            }
        }

        // Remove duplicates.
        // https://stackoverflow.com/questions/307674/how-to-remove-duplicate-values-from-a-multi-dimensional-array-in-php
        $serialized = array_map("serialize", $days);
        $unique = array_unique($serialized);
        $days = array_intersect_key($days, $unique);
        return $days;
    }

    // Extract weekday will be something else.

    function extractDay($input = null)
    {
        $day = "X";
        $day_evidence = [];

        $days = $this->day_indicators;

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

                    //      $day_evidence[] = $day_name;
                    $day = $key;
                    $day_evidence[$key][] = $day_name;

                    //break;
                }
            }
        }

        $this->parsed_date = date_parse($input);

        if (
            $this->parsed_date["year"] != false and
            $this->parsed_date["month"] != false and
            $this->parsed_date["day_number"] != false
        ) {
            $date_string =
                $this->parsed_date["year"] .
                "/" .
                $this->parsed_date["month"] .
                "/" .
                $this->parsed_date["day_number"];

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
            if (!function_exists("array_key_first")) {
                // function array_key_first(array $scores) {
                foreach ($this->day_solar_milestones as $key => $unused) {
                    return $key;
                }
                return null;
                //}
            }

            //            return array_key_first($scores);
        }

        // Leave it here for now.
        // TODO: Consider three days all with same score
        // TODO: Consider two days wth non-zero scores.

        // TODO: Day of week extraction.

        return false;
    }

    /**
     *
     */
    public function readSubject()
    {
        $i = str_replace("-", " ", $this->input);

        $dateline = $this->extractDateline($i);
        if (
            !(
                $dateline["year"] === false and
                $dateline["month"] === false and
                $dateline["day_number"] === false
            )
        ) {
            $date_string =
                $dateline["year"] .
                "-" .
                str_pad($dateline["month"], 2, "0", STR_PAD_LEFT) .
                "-" .
                str_pad($dateline["day_number"], 2, "0", STR_PAD_LEFT);

            $this->projected_time = strtotime($date_string);

            $this->working_datum = $this->time_agent->datumTime($date_string);
            $this->dateline = $dateline;
        } else {
            $timestamp = $this->zuluStamp($this->current_time);

            $dateline = $this->extractDateline($timestamp);

            $this->dateline = $dateline;

            $this->project_time = strtotime($this->current_time);
            $this->working_datum = $this->time_agent->datumTime(
                $this->current_time
            );
        }
        $longitude = $this->extractLongitude($i);
        $latitude = $this->extractLatitude($i);
        if ($longitude !== false) {
            $this->longitude = $longitude;
        }
        if ($latitude !== false) {
            $this->latitude = $latitude;
        }

        $this->type = "wedge";

        $input = $this->agent_input;
        if ($this->agent_input == "" or $this->agent_input == null) {
            $input = $this->subject;
        }

        if ($input == "day") {
            return;
        }

        $this->days = $this->extractDays($input);
        $day = $this->extractDay($input);

        if ($day !== false) {
            $this->day = $day;
            //$this->era = $day['era'];
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "day") {
                $this->getDay();

                if (!isset($this->decimal_day) or $this->decimal_day == null) {
                    $this->decimal_day = rand(1, rand(1, 10) * 1e11);
                }

                $this->binaryDay($this->decimal_day);
                $p = strlen($this->binary_day);

                $this->max = 13;
                $this->size = 4;
                $this->lattice_size = 40;
                $this->response .= "Made a day. ";
                return;
            }
        }

        $indicators = [
            "translate" => ["translate", "english", "anglic"],
            "julian" => ["julian"],
            "mesoamerican" => ["maya"],
            "twilight" => [
                "twilight",
                "dawn",
                "sunset",
                "sunrise",
                "transit",
                "noon",
            ],
        ];
        $dedash_input = str_replace("-", " ", $input);
        $this->flagAgent($indicators, $dedash_input);

        $input_agent = new Input($this->thing, "input");
        $discriminators = ["wedge", "slice"];
        $input_agent->aliases["wedge"] = ["pizza", "wheel", "wedge"];
        $input_agent->aliases["slice"] = ["slice", "column", "columns"];
        $type = $input_agent->discriminateInput($input, $discriminators);
        if ($type != false) {
            $this->type = $type;
        }

        $keywords = ["day"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        default:
                    }
                }
            }
        }

        $this->getDay();
    }
}
