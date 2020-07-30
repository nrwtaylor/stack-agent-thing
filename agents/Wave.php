<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Wave extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->keyword = "mordok";

        $this->test = "Development code"; // Always

        $this->node_list = ["off" => ["on" => ["off"]]];
        $this->thing->choice->load('train');

        $this->keywords = [
            'wave',
            'height',
            'hst',
            'tp',
            'direction',
            'link',
            'buoy',
            'bouy',
            'verbosity',
            'mode',
            "period",
            "p",
        ];

        $this->default_run_time = $this->current_time;
        $this->negative_time = true;
    }

    function initWave()
    {
        if ($this->verbosity == false) {
            $this->verbosity = 2;
        }

        if ($this->notch_height == false) {
            $this->notch_height = 1.6;
        }
        if ($this->notch_direction == false) {
            $this->notch_direction = 180;
        }
        if ($this->notch_spread == false) {
            $this->notch_spread = 80;
        }
        if ($this->notch_min_period == false) {
            $this->notch_min_period = 10;
        }
        if ($this->noaa_buoy_id == false) {
            $this->noaa_buoy_id = 44025;
        }
        if ($this->link == false) {
            $this->link =
                "https://magicseaweed.com/Georgica-East-Hampton-Surf-Report/4226/";
        }
    }

    function run()
    {
        $this->getWave();
    }

    function set()
    {
        // This makes sure that
        if (!isset($this->wave_thing)) {
            $this->wave_thing = $this->thing;
        }

        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

        $this->variables_agent->setVariable("state", $requested_state);

        $this->variables_agent->setVariable("verbosity", $this->verbosity);

        $this->variables_agent->setVariable(
            "notch_height",
            $this->notch_height
        );
        $this->variables_agent->setVariable(
            "notch_min_period",
            $this->notch_min_period
        );
        $this->variables_agent->setVariable(
            "notch_direction",
            $this->notch_direction
        );
        $this->variables_agent->setVariable(
            "notch_spread",
            $this->notch_spread
        );
        $this->variables_agent->setVariable("link", $this->link);
        $this->variables_agent->setVariable(
            "noaa_buoy_id",
            $this->noaa_buoy_id
        );

        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );

        $this->thing->choice->save('wave', $this->state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;
    }

    function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "wave" . " " . $this->from
        );

        $this->notch_height = $this->variables_agent->getVariable(
            "notch_height"
        );
        $this->notch_min_period = $this->variables_agent->getVariable(
            "notch_min_period"
        );
        $this->notch_direction = $this->variables_agent->getVariable(
            "notch_direction"
        );
        $this->notch_spread = $this->variables_agent->getVariable(
            "notch_spread"
        );
        $this->link = $this->variables_agent->getVariable("link");

        $this->noaa_buoy_id = $this->variables_agent->getVariable(
            "noaa_buoy_id"
        );
        $this->refreshed_at = $this->variables_agent->getVariables(
            "refreshed_at"
        );

        $this->verbosity = $this->variables_agent->getVariable("verbosity");

        $this->thing->choice->Create(
            $this->keyword,
            $this->node_list,
            $this->previous_state
        );
        $this->thing->choice->Choose($this->requested_state);

        $this->state = $this->thing->choice->current_node;

        $this->state = $this->previous_state;

        $this->initWave();
    }

    function getWave()
    {
        $buoy = $this->noaa_buoy_id;
        //"44025";
        //$buoy = "44025";
        $data_source =
            "http://polar.ncep.noaa.gov/waves/WEB/multi_1.latest_run/plots/multi_1.OPCA02.bull";
        $data_source =
            "https://polar.ncep.noaa.gov/waves/WEB/multi_1.latest_run/plots/multi_1." .
            $buoy .
            ".bull";
        //$data_source = "http://www.ndbc.noaa.gov/data/latest_obs/" . $buoy . ".txt";
        // https://polar.ncep.noaa.gov/waves/WEB/multi_1.latest_run/plots/multi_1.41101.bull

        $data = file_get_contents($data_source);

        if ($data == false) {
            return true;
            // Invalid buoy setting.
        }

        $lines = explode("\n", $data);
        //var_dump($lines[7]);
        //echo "<br>";
        // Seventh line has first line of forecast.  Forecast is updated 4 times daily.
        // So eventually retrieve first six lines and extract one closest
        // to current time.

        // For now pull first line and extract
        // wave parameters.
        //$sections = explode(" | ", $lines[7]);

        $i = 0;
        $forecast = [];
        foreach ($lines as $line) {
            $i += 1;
            if ($i >= 8) {
                //echo $line . "<br>";
                $forecast[] = $line;
                // First explore around the vertical line break
                $fields = explode("|", $line);
                //        echo "---<br>";
                //        var_dump($fields);
                //        echo "<br>";
                // Split time info in fields
                //$time_info = preg_split('/\s+/', $fields[1]);
                $time_info = preg_split('/ +/', $fields[1]);

                //        var_dump($time_info);
                //        echo "<br>";

                $day = intval($time_info[1]);
                $hour = intval($time_info[2]);

                //echo "day and hour " . $day . " " . $hour . "<br>";

                // PULL IN ALL 5 or 6 WAVE FIELDS HERE.  AND NOTCH FILTER FOR
                // THE SAME WAVE GUIDE.
                $field_num = 2;
                $waves = [];
                while ($field_num <= count($fields)) {
                    $field_num += 1;
                    //if ($wave_field_num >= 3) {

                    if (isset($fields[$field_num])) {
                        $wave_info = preg_split('/\s+/', $fields[$field_num]);
                        if (count($wave_info) >= 4) {
                            $height = floatval($wave_info[1]);
                            $period = floatval($wave_info[2]);
                            $direction = intval($wave_info[3]);
                            //      var_dump( $wave_info);
                            //      echo "<br>";
                            //      echo "height, period, direction " . $height . " " . $period . " " . $direction .  "<br>";

                            $waves[] = [
                                "height" => $height,
                                "period" => $period,
                                "direction" => $direction,
                            ];
                        } else {
                            $waves[] = false;
                        }
                    } else {
                        $waves[] = false;
                    }
                    //}
                }
                $wave_spectra[] = [
                    "day" => $day,
                    "hour" => $hour,
                    "waves" => $waves,
                ];
            }
        }

        //exit();

        $utc_time = gmdate('d.m.Y H:i', strtotime($this->current_time));

        $at_hour = intval(date('H', strtotime($utc_time)));
        $at_day = intval(date('j', strtotime($utc_time)));
        $at_minute = intval(date('i', strtotime($utc_time)));

        //echo $at_hour . "<br>";
        //cho $at_day . "<br>";
        //echo $at_minute . "<br>";

        $this->hour = $at_hour;
        $this->day = $at_day;
        //exit();

        //echo "current time " . $this->current_time;
        //echo "current hour" . $at_hour . "current day" . $at_day;

        //echo "<br>";
        $i = 0;
        foreach ($wave_spectra as $key => $spectra) {
            //    echo "hour " . $spectra['hour']. " " .$at_hour . "<br>";
            //    echo "day " . $spectra['day']. " " .$at_day . "<br>";

            $i += 1;
            if ($spectra["hour"] == $at_hour and $spectra["day"] == $at_day) {
                //        echo "meep" . $at_day . " ". $at_hour . "<br>";
                //$this->day = $at_day;
                $waves = $spectra['waves'];
                //exit();
                break;
            }
        }

        $next_wave_set = $wave_spectra[$i + 1]['waves'];
        $wave_set_interpolated = [];
        $i = 0;
        foreach ($waves as $wave) {
            //echo "----" . "<br>";
            //echo "height " . $wave['height'] . " " . $next_wave_set[$i]['height'] . "<br>";
            //echo "direction " . $wave['direction'] . " " . $next_wave_set[$i]['direction'] . "<br>";
            //echo "period ". $wave['period'] . " " . $next_wave_set[$i]['period'] . "<br>";

            //echo "minute " . $at_minute . "<br>";

            if (isset($next_wave_set[$i]['height'])) {
                //echo "<br>";
                $height = round(
                    $wave['height'] +
                        (($next_wave_set[$i]['height'] - $wave['height']) *
                            $at_minute) /
                            60,
                    2
                );
                $direction = intval(
                    $wave['direction'] +
                        ($next_wave_set[$i]['direction'] - $wave['direction']) *
                            ($at_minute / 60)
                );
                $period = round(
                    $wave['period'] +
                        (($next_wave_set[$i]['period'] - $wave['period']) *
                            $at_minute) /
                            60,
                    1
                );
            } else {
                $height = round($wave['height'], 2);
                $direction = intval($wave['direction']);
                $period = round($wave['period'], 1);
            }

            //echo "height " . $height. " direction " . $direction . " period " . $period . "<br>";

            $wave_set_interpolated[] = [
                "height" => $height,
                "direction" => $direction,
                "period" => $period,
            ];

            $i += 1;
        }

        // now we have the index we can get the previous, now, and next wave parameters.
        //        $this->height = $wave_spectra[$i]["height"];
        //        $this->direction = $wave_spectra[$i]["direction"];
        //        $this->period = $wave_spectra[$i]["period"];

        //echo $this->height, $this->direction, $this->period;

        //foreach($waves as $wave) {

        //https://www.surfertoday.com/surfing/9116-the-importance-of-swell-period-in-surfing
        //period > 10.

        // So this looks through all the wave fields to find
        // either the notch passing dominant wave.
        // Or if no waves passing the notch,
        // the dominant wave.
        $spread = 80;
        $this->height = 0;
        $this->dominant_height = 0;
        foreach ($wave_set_interpolated as $wave) {
            $num_matching_wave_fields = 0;

            if ($this->dominant_height < $wave['height']) {
                $this->dominant_height = $wave['height'];
                $this->dominant_direction = $wave['direction'];
                $this->dominant_period = $wave['period'];
            }

            //$this->notch_height = 1.6;
            //$this->notch_direction = 180;
            //$this->notch_spread = 80;
            //$this->notch_min_period = 10;

            if (
                $wave['height'] >= $this->notch_height and
                $wave['direction'] >
                    $this->notch_direction - $this->notch_spread and
                $wave['direction'] <
                    $this->notch_direction + $this->notch_spread and
                $wave['period'] > $this->notch_min_period
            ) {
                if ($this->height < $wave['height']) {
                    // New dominant wave
                    $this->height = $wave['height'];
                    $this->direction = $wave['direction'];
                    $this->period = $wave['period'];
                }
                $num_matching_wave_fields += 1;
            }
        }

        if ($num_matching_wave_fields >= 1) {
            // Use the wave passing the notch.
            $this->setFlag("red");
        } else {
            $this->setFlag("green");
            // Report the dominant wave.
            $this->height = $this->dominant_height;
            $this->direction = $this->dominant_direction;
            $this->period = $this->dominant_period;
        }

        return $this->height;
    }

    function getFlag()
    {
        $this->flag_thing = new Flag($this->variables_agent->thing, 'flag');
        $this->flag = $this->flag_thing->state;

        return $this->flag;
    }

    function setFlag($colour)
    {
        $this->flag_thing = new Flag(
            $this->variables_agent->thing,
            'flag ' . $colour
        );
        $this->flag = $this->flag_thing->state;

        return $this->flag;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();
        // Generate email response.

        $to = $this->thing->from;
        $from = "wave";

        //echo "<br>";

        $choices = $this->thing->choice->makeLinks($this->state);
        $this->thing_report['choices'] = $choices;

        //$interval = date_diff($datetime1, $datetime2);
        //echo $interval->format('%R%a days');
        //$available = $this->thing->human_time($this->available);

        //$s = $this->block_thing->state;
        if (!isset($this->flag)) {
            $this->flag = strtoupper($this->getFlag());
        }
        //$this->link = "https://magicseaweed.com/Hampton-Beach-Surf-Report/2074/";

        if (strtolower($this->flag) == "red") {
            $sms_message = "WAVE = SURF'S UP";
        } else {
            $sms_message = "WAVE";
        }

        if ($this->verbosity >= 2) {
            $sms_message .= " | flag " . strtoupper($this->flag);
            $sms_message .= " | direction " . strtoupper($this->direction) . "";
            $sms_message .= " | height " . strtoupper($this->height) . "m";
            $sms_message .= " | period " . strtoupper($this->period) . "s";
            $sms_message .= " | source NOAA Wavewatch III ";
        }

        if ($this->verbosity >= 9) {
            $sms_message .= " | nowcast " . $this->day . " " . $this->hour;
        }

        if ($this->verbosity >= 5) {
            $sms_message .=
                " | notch " .
                $this->notch_height .
                "m " .
                $this->notch_direction .
                " " .
                $this->notch_spread .
                " " .
                $this->notch_min_period .
                "s";
        }

        if ($this->verbosity >= 2) {
            $sms_message .= " | buoy " . $this->noaa_buoy_id;
        }

        $sms_message .= " | curated link " . $this->link;

        if ($this->verbosity >= 9) {
            $sms_message .=
                " | nuuid " . substr($this->variables_agent->thing->uuid, 0, 4);

            $run_time = microtime(true) - $this->start_time;
            $milliseconds = round($run_time * 1000);

            $sms_message .= " | rtime " . number_format($milliseconds) . 'ms';
        }

        switch ($this->index) {
            case null:
                $sms_message .= " | TEXT WAVE ";

                break;

            case '1':
                $sms_message .= " | TEXT WAVE";
                //$sms_message .=  " | TEXT ADD BLOCK";
                break;
            case '2':
                $sms_message .= " | TEXT WAVE";
                //$sms_message .=  " | TEXT BLOCK";
                break;
            case '3':
                $sms_message .= " | TEXT WAVE";
                break;
            case '4':
                $sms_message .= " | TEXT WAVE";
                break;
            default:
                $sms_message .= " | TEXT ?";
                break;
        }

        $test_message =
            'Last thing heard: "' .
            $this->subject .
            '".  Your next choices are [ ' .
            $choices['link'] .
            '].';
        $test_message .= '<br>Train state: ' . $this->state . '<br>';

        $test_message .= '<br>' . $sms_message;

        $test_message .=
            '<br>Current node: ' . $this->thing->choice->current_node;

        $test_message .= '<br>run_at: ' . $this->run_at;
        $test_message .= '<br>end_at: ' . $this->end_at;

        $this->thing_report['sms'] = $sms_message;
        $this->thing_report['email'] = $sms_message;
        $this->thing_report['message'] = $sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] =
            'This triggers based on specific NOAA buoy data parameters.';
    }

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

    public function readSubject()
    {
        $this->response = null;
        $this->num_hits = 0;
        // Extract uuids into

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

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        //        $this->getWave();

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'wave') {
                //echo "readsubject block";
                //$this->read();
                return;
            }
        }

        // Extract runat signal
        $matches = 0;
        foreach ($pieces as $key => $piece) {
            if (strlen($piece) == 4 and is_numeric($piece)) {
                $run_at = $piece;
                $matches += 1;
            }
        }

        if ($matches == 1) {
            $this->run_at = $run_at;
            $this->num_hits += 1;
            $this->thing->log(
                'found a "run at" time of "' . $this->run_at . '".'
            );
        }

        // Extract runtime signal
        $matches = 0;
        foreach ($pieces as $key => $piece) {
            if ($piece == 'x' or $piece == 'z') {
                $this->quantity = $piece;
                $matches += 1;
                continue;
            }

            if (
                $piece == '5' or
                $piece == '10' or
                $piece == '15' or
                $piece == '20' or
                $piece == '25' or
                $piece == '30' or
                $piece == '45' or
                $piece == '55' or
                $piece == '60' or
                $piece == '75' or
                $piece == '90'
            ) {
                $this->quantity = $piece;
                $matches += 1;
                continue;
            }

            if (strlen($piece) == 3 and is_numeric($piece)) {
                $this->quantity = $piece; //3 digits is a good indicator of a runtime in minutes
                $matches += 1;
                continue;
            }

            if (strlen($piece) == 2 and is_numeric($piece)) {
                $this->quantity = $piece;
                $matches += 1;
                continue;
            }

            if (strlen($piece) == 1 and is_numeric($piece)) {
                $this->quantity = $piece;
                $matches += 1;
                continue;
            }
        }

        //    if ($matches == 1) {
        //        $this->quantity = $piece;
        //        $this->num_hits += 1;
        //$this->thing->log('Agent "Block" found a "run time" of ' . $this->quantity .'.');
        //    }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'buoy':
                        case 'bouy':
                            $number = $this->extractNumber();
                            if (is_numeric($number)) {
                                $this->noaa_buoy_id = $number;
                                $this->set();
                            }
                            return;

                        case 'direction':
                        case 'dir':
                            $number = $this->extractNumber();
                            if (is_numeric($number)) {
                                $this->notch_direction = $number;
                                $this->set();
                            }
                            return;

                        case 'spread':
                            $number = $this->extractNumber();
                            if (is_numeric($number)) {
                                $this->notch_spread = $number;
                                $this->set();
                            }
                            return;

                        case 'period':
                            $number = $this->extractNumber();
                            if (is_numeric($number)) {
                                $this->notch_min_period = $number;
                                $this->set();
                            }
                            return;

                        case 'height':
                            $number = $this->extractNumber();
                            if (is_numeric($number)) {
                                $this->notch_height = $number;
                                $this->set();
                            }
                            return;

                        case 'verbosity':
                        case 'mode':
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

        return false;
    }
}
