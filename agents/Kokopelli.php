<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Kokopelli extends Agent
{
    public function init()
    {
        $this->kokopelli_response_flag = "off"; // Default is off. Run quietly.
        $this->kokopelli_terse_flag = "on";
        $this->state = "red"; // running

        $this->current_time = $this->thing->time();

        $this->mail_postfix = $this->thing->container["stack"]["mail_postfix"];
        //$this->to_assigned = "kokopelli:#general@kaiju.discord";

        $this->score = 1;
        /*
        REFERENCE
        $datagram = [
            "to" => "null" . $this->mail_postfix,
            "from" => "job",
            "subject" => "s/ job stack",
        ];
*/

        /*
$this->thing->from = 'kokopelli:#general@kaiju.discord';
$this->from = 'kokopelli:#general@kaiju.discord';

        $datagram = [
            "to" => "kokopelli:#general@kaiju.discord",
            "from" => "kokopelli",
            "subject" => "kokopelli",
            "precedence" => "routine",
        ];
        $this->thing->spawn($datagram);
*/

        // Checks in on Kokopelli.
        // What jobs are there?
    }

    public function __destruct()
    {
        // Put another Kokopelli on the queue.
        $this->thing->from = "kokopelli:#general@kaiju.discord";
        $this->from = "kokopelli:#general@kaiju.discord";

        $datagram = [
            "to" => "kokopelli:#general@kaiju.discord",
            "from" => "kokopelli",
            "subject" => "kokopelli",
            "precedence" => "routine",
        ];
        $this->thing->spawn($datagram);
    }

    public function set()
    {
        // Important that everytime this function is called that the count gets updated
        // to reflect the current kokopelli sightings.

        // Why?

        // Otherwise the kokopelli gets repeated. And that people notice.
        $time_string = $this->thing->time();
        $this->thing->Write(["kokopelli", "refreshed_at"], $time_string);

        $this->variables->setVariable("count", $this->count);
        $this->variables->setVariable("refreshed_at", $this->current_time);
    }

    public function run()
    {
    }

    public function makeChoice()
    {
    }

    /**
     *
     * @return unknown
     */
    
    public function respondResponse()
    {
/*
   $n = $this->dieRoll(20);
if ($n != 20) {
        $this->thing->Write(["message", "received_at"], $this->thing->time());
        $this->thing->Write(["message", "agent"], 'kokopelli');
return;
}
*/
$this->thing_report['sms'] = "KOKOPELLI " . $this->count . " " . $this->previous_kokopelli_count;


        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        // test
        $t = $this->agent_input;
        if (is_array($this->agent_input)) {
            $t = "array";
        }
        // Now should interpret kokopelli address.
        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);

            // test

            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }
/*
    public function respond()
    {
        if ($this->kokopelli_response_flag == "on") {
            $this->respondResponse();
        }
    }
*/
    public function get()
    {
        //$contents = $this->load("kokopelli/kokopelli-jobs.txt");
        $this->kokopelli_jobs = require $this->resource_path .
            "kokopelli/kokopelli_jobs.php";

        $this->variables = new Variables(
            $this->thing,
            "variables kokopelli " . $this->from
        );

        $this->last_count = $this->variables->getVariable("count");

        if ($this->last_count === false) {
            $this->last_count = 0;
        }

        $this->last_refreshed_at = $this->variables->getVariable(
            "refreshed_at"
        );
        $this->thing->log(
            $this->agent_prefix . "loaded " . $this->last_count . "."
        );

//        $this->response .=
//            "[" . $this->last_count . " " . $this->last_refreshed_at . ".] ";
        $this->getKokopellis();
    }

    function makeSMS()
    {
        $sms = "KOKOPELLI";
        $sms .= " | ";
        if (!isset($this->count) or $this->count === false) {
            $sms .= "Kokopelli count not set. Text KOKOPELLI ADVANCE.";
        } else {
            if (
                isset($this->kokopelli_terse_flag) and
                $this->kokopelli_terse_flag == "on"
            ) {
                $sms .= "" . "Counted " . $this->count . " kokopellis. ";
                $sms .= "Got " . $this->previous_kokopelli_count . ". ";
            } else {
                $sms .= " | " . "Counted " . $this->count . " kokopellis. ";
            }
        }

        if (
            isset($this->kokopelli_response_flag) and
            $this->kokopelli_response_flag == "on"
        ) {
            $sms .= $this->response;
        } else {
            //$sms .= $this->response;
        }

        //$message = $this->shortenText($message);

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/agent";

        $web = '<a href="' . $link . '">';
        // $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/flag.png">';
        $web .= $this->html_image;

        $web .= "</a>";
        $web .= "<br>";
        $web .= "<b>" . ucwords($this->agent_name) . " Agent</b><br>";
        $web .= $this->sms_message;

        $this->thing_report["web"] = $web;
    }

    public function readSubject()
    {
        $this->response .= "Heard '" . $this->input . "'. ";
        //   $input = strtolower($this->subject);
        $input = $this->input;

        // strip roll
        $dice = $this->extractRolls($this->input);

        $die_N_text = "d20";
        if (count($dice) == 1) {
            $die_N_text = $dice[0];
        }
        // if (substr($job["bar_count"], 0, 1) == "d") {
        $die_N = intval(ltrim($die_N_text));

        $i = $input;
        if ($die_N != 0) {
            $roll = $this->dieRoll($die_N);
            $this->response .= "d " . $die_N . " ";

            $this->response .= "roll " . $roll . " ";

            if ($roll == 1) {
                $this->response .= "Critical hit. ";
            }
            // }

            $input = $this->input;
            $tokens = explode(" ", $input);
            $i = "";

            foreach ($tokens as $token) {
                $match = false;
                foreach ($dice as $dice_token) {
                    if (strtolower($token) == strtolower($dice_token)) {
                        $match = true;
                    }
                }
                if ($match == false) {
                    $i .= $token . " ";
                }
            }
            //        $input = trim($i);
        }
        $input = trim($i);

        $this->number_agent = new Number($this->thing, "number kokopelli");
        $this->number_agent->extractNumber($input);

        //$this->response .= "input " . $input . " ";

        $pieces = explode(" ", strtolower($input));

        if (stripos(strtolower($input), "kokopelli") !== false) {
            $this->score = $this->score * 10;
        }

        $action_flag = false;
        $keywords = ["advance", "reset", "job"]; // Order important?
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    $this->Perform($piece);
                    $action_flag = true;
                    break 2;
                }
            }
        }

        // Only response SMS if did something.
        if ($action_flag === true) {
            $this->kokopelli_response_flag = "on";
        }
// else {
//            $this->kokopelli_response_flag = "off";
//        }
        if (!isset($this->count)) {
            $this->count = $this->last_count;
        }
    }

    public function Perform($piece)
    {
        $this->response .= "Perform " . $piece . ". ";
        switch ($piece) {
            case "advance":
                $this->score = $this->score * 10;
                $this->advanceKokopelli();
                return;
            case "reset":
                $this->score = $this->score * 10;
                $this->resetKokopelli();
                return;
            case "job":
                $this->score = $this->score * 10;
                $this->jobKokopelli();
                return;

            case "on":
            default:
                if ($this->number_agent->number != false) {
                    $this->count = $this->number_agent->number;
                    $this->response .=
                        "Number found. Set count to " . $this->count . ". ";
                    return null;
                }

                return;
        }
    }

    function nullKokopelli()
    {
    }

    function advanceKokopelli($depth = null)
    {
        if (!isset($this->last_count) or $this->last_count === false) {
            $this->count = 1;
            $this->response .= "Started kokopelli count. ";
            return;
        }

        $this->count = $this->last_count + 1;
        $this->response .= "Advanced kokopelli count. ";
    }

    function resetKokopelli()
    {
        $this->count = 1;
        $this->response .= "Reset count. ";
    }

    public function timerKokopelli($job)
    {
        //      if ($text == null) {
        //          return true;
        //      }

        $to = $job["to"];
        $from = $job["from"];
        $text = $job["subject"];
        $period = $job["tick_count"];

        //$variable_name = str_replace(" ", "_", $text);
        $parts = explode(" ", $text);
        $t = $parts[0];
        $variable_name = str_replace(" ", "_", $t);

        // When was the last weather report?
        $match = false;

        foreach (array_reverse($this->kokopellis) as $i => $kokopelli) {
            if (stripos($kokopelli["subject"], $text) !== false) {

                if (
                    isset(
                        $kokopelli["variables"][$variable_name]["refreshed_at"]
                    )
                ) {
                    $last_update =
                        $kokopelli["variables"][$variable_name]["refreshed_at"];
                }

                //                $last_update =
                //                    $kokopelli->variables["kokopelli"]["refreshed_at"];
                $match = true;
                break;
            }
        }

        if ($match !== true) {
            foreach ($this->kokopellis as $i => $kokopelli) {
                $agents = array_keys($kokopelli["variables"]);
                //var_dump($agents);
                foreach ($agents as $agent) {
                    // Not quite right.

                    if (stripos($variable_name, $agent) !== false) {
                        $last_update =
                            $kokopelli["variables"]["kokopelli"][
                                "refreshed_at"
                            ];
                        $match = true;
                        break;
                    }
                }
            }
        }

        //        if (count($this->kokopellis) != 0) {
        $age = 0;
        if ($match) {
            $age = strtotime($this->current_time) - strtotime($last_update);
        }
        /*
    KOKOPELLI | Counted 64 kokopellis. [64 2021-09-12T23:57:38Z]
    Got 885 previous kokopellis. Read 'Kokopelli job'. Critical hit.
    input Kokopelli job Perform job. last weather timestamp last weather update age1631491058 Requested weatherlast time timestamp2021-09-12T22:09:20Z last time update age6498
    Requested timeGot last count of 64 kokopellis.
*/
        $this->response .= "[";
        $this->response .= $text . " ";
        //        $this->response .=
        //            "updated at " . ($last_update ? $last_update : "X") . " ";
        $this->response .= "age " . $age . " / " . $period . " ";

        if ($match === false or $age > $period) {
            //                $to = "agent"; // to
            $subject = $text;
            //                $from = $this->from; //from

            $job = [
                "to" => "agent", //y$to,
                "from" => $from,
                "subject" => $subject,
                "agent_input" => "gearman",
            ];
            $thing_report = $this->runJob($job);
        }
        $this->response .= "] ";

        if ($match === false or $age > $period) {
            $this->response .= "Requested " . $text . ". ";
        }
        //      } else {
        //        $this->response .= "No " . $text . "  Kokopellis visible. ";
        //  }
    }

    function jobKokopelli()
    {
        // So the stack scheduler can be used for non-logic based jobs.
        // This should be reserved for things the stack scheduler can
        // not (yet) parse.

        foreach ($this->kokopelli_jobs as $nuuid => $job) {
            //var_dump($job);
            //            $this->timerKokopelli($job["subject"], $job["tick_count"]);
            $this->timerKokopelli($job);
        }

        // When was the last weather report?
        //        $this->timerKokopelli("environmentcanadamarineweather", 3600);
        //        $this->timerKokopelli("weather", 3600);
        //        $this->timerKokopelli("manager", 60 * 30);
        //        $this->timerKokopelli("time", 60 * 1);
    }

    function getKokopellis()
    {
        $things_agent = [];
        // Lots of connections.
        // I think this might be because I am holding the 100+ things open.
        // So test processing this to de-thing the thing into a kokopelli.
        $from = "kokopelli:#general@kaiju.discord";

        //  $things = $this->getThings("kokopelli");

        //foreach ($this->kokopelli_jobs as $nuuid => $job) {
        //    $got_things = $this->getThings($job["to"]);
        //
        //          $things = array_merge($things, $got_things);
        //    }
        $query_list = [
            "manager",
            "kokopelli",
            "time",
            "weather",
            "environmentcanadamarineweather",
            "kplex",
        ];

        foreach ($query_list as $i => $query) {
            $things = $this->getThings($query);
            //var_dump(count($things));
            $things_agent[$query] = $things;
        }
        /*
        $things_agent['kokopelli'] = $this->getThings("kokopelli");
        $things_agent['time'] = $this->getThings("time");
        $things_agent['weather'] = $this->getThings("weather");
        $things_agent['environmentcanadamarineweather'] = $this->getThings(
            "environmentcanadamarineweather"
        );
        $things['kplex'] = $this->getThings("kplex");
*/

        /*
        $things = array_merge(
            $things_kokopelli,
            $things_time,
            $things_weather,
            $things_environmentcanadamarineweather
        );
*/
        $things = [];
        foreach ($things_agent as $agent => $_things) {
            if ($_things == null) {
                $_things = [];
            }
            $things = array_merge($things, $_things);
        }
        /*
        $things = array_merge(
            $things_kokopelli,
            $things_time,
            $things_weather,
            $things_environmentcanadamarineweather,
            $things_kplex
        );
*/
        $kokopellis = [];
        foreach ($things as $i => $thing) {
            $kokopelli = [
                "uuid" => $thing->uuid,
                "variables" => $thing->variables,
                "created_at" => $thing->created_at,
                "subject" => $thing->subject,
            ];
            $kokopellis[$thing->uuid] = $kokopelli;
        }

        if ($things === null) {
            return;
        }
        if ($things === true) {
            return;
        }

        if ($things === false) {
            $this->response .= "Saw false return from kokopelli count. ";

            return;
        }

        // The WORK is Here.

        $count = count($kokopellis);
        $this->previous_kokopelli_count = $count;
        //$this->response .= "Got " . count($things) . " previous kokopellis. ";

        usort($kokopellis, function ($first, $second) {
            return strtotime($first["created_at"]) -
                strtotime($second["created_at"]);
        });

        //array_unique($things);

        $this->kokopellis = $kokopellis;

        $this->kokopelli_history = [];

        if ($kokopellis === false) {
            $this->response .= "Saw false return from kokopelli count. ";
            return;
        }

        foreach ($kokopellis as $kokopelli) {
            $variables = $kokopelli["variables"];
            if (isset($variables["kokopelli"])) {
                $count = "X";
                $refreshed_at = "X";

                if (isset($variables["kokopelli"]["count"])) {
                    $count = $variables["kokopelli"]["count"];
                }

                if (isset($variables["kokopelli"]["refreshed_at"])) {
                    $refreshed_at = $variables["kokopelli"]["refreshed_at"];
                }

                $this->kokopelli_history[] = [
                    "count" => $count,
                    "refreshed_at" => $refreshed_at,
                ];
            }
        }
    }

    public function makeImage()
    {
        // Create a x_width x y_width image
        $x_width = 200;
        $y_width = 125;

        $this->image = imagecreatetruecolor(200, 125);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        $this->red = imagecolorallocate($this->image, 231, 0, 0);

        $this->yellow = imagecolorallocate($this->image, 255, 239, 0);
        $this->green = imagecolorallocate($this->image, 0, 129, 31);

        $this->color_palette = [$this->red, $this->yellow, $this->green];

        imagefilledrectangle($this->image, 0, 0, 200, 125, $this->white);

        $border = 25;

        $lines = ["e", "g", "b", "d", "f"];
        $i = 0;
        foreach ($lines as $key => $line) {
            $x1 = 0;
            $x2 = $x_width;
            $y1 = $i * 15 + 25;
            $y2 = $y1;
            imageline(
                $this->image,
                $x1 + $border,
                $y1,
                $x2 - $border,
                $y2,
                $this->black
            );
            $i = $i + 1;
        }

        imageline(
            $this->image,
            0 + $border,
            25,
            0 + $border,
            4 * 15 + 25,
            $this->black
        );
        imageline(
            $this->image,
            200 - $border,
            25,
            200 - $border,
            4 * 15 + 25,
            $this->black
        );

        $textcolor = $this->black;

        $font = $this->default_font;

        $size = 10;
        $angle = 0;

        if (!isset($this->count) or $this->count == "X") {
            $this->count = 0;
        }
        $count_notation = $this->count + 1;

        if (file_exists($font)) {
            if ($count_notation != 1) {
                //            if ($count_notation != 1 or $count_notation == $this->max_count) {
                imagettftext(
                    $this->image,
                    $size,
                    $angle,
                    0 + 10,
                    110,
                    $this->black,
                    $font,
                    $count_notation
                );
            }

            if (
                $count_notation + 1 != 1 or
                $count_notation + 1 == $this->max_count + 1
            ) {
                imagettftext(
                    $this->image,
                    $size,
                    $angle,
                    200 - 25,
                    110,
                    $this->black,
                    $font,
                    $count_notation + 1
                );
            }
        }
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
    }
}
