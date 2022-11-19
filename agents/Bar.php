<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Bar extends Agent
{
    public function init()
    {
        $this->bar_terse_flag = "on";

        $this->state = "red"; // running

        $this->current_time = $this->thing->time();

        $this->mail_postfix = $this->thing->container["stack"]["mail_postfix"];

        $this->max_bar_count = 80;
        if (
            isset(
                $this->thing->container["api"]["bar"]["default_max_bar_count"]
            )
        ) {
            $max_bar_count =
                $this->thing->container["api"]["bar"]["default_max_bar_count"];
        }

        if (isset($max_bar_count) and $max_bar_count != false) {
            $this->max_bar_count = $max_bar_count;
        }

        $this->thing_report["help"] =
            "Counts time to " . $this->max_bar_count . " bars. Text BPM.";

        //        $this->response = "";
    }

    public function set()
    {
        // Important that everytime this function is called that the count gets updated
        // to reflect the current bar.

        // Why?

        // Otherwise the bar count gets repeated. And that people notice.

        $this->variables->setVariable("count", $this->bar_count);
        $this->variables->setVariable("max_bar_count", $this->max_bar_count);

        $this->variables->setVariable("refreshed_at", $this->current_time);
    }

    public function run()
    {
        // devstack develop per user bar counts
        //$this->getBars();
        $this->getTicks();
        $this->ticksBar();

        $this->age =
            strtotime($this->current_time) -
            strtotime($this->last_refreshed_at);
    }

    public function ticksBar()
    {
        // So the question is when was the last bar.
        // without hearing the last bar.

        // Make an assumption. And then check if we miss anything.
        // Eventually.

        $this->bar_time = 60 * 4;

        $this->getTicks();

        // A step to making this faster because then we can end the for loop
        // as soon as we see it.

        $timestamp = $this->last_refreshed_at;
        if ($this->last_refreshed_at === false) {
            $timestamp = $this->current_time;
            $this->response .= "Saw false timestamp. ";
        }

        usort($this->ticks_history, function ($a, $b) {
            $countA = strtotime($a["refreshed_at"]);
            $countB = strtotime($b["refreshed_at"]);

            $diff = $countB - $countA;
            return $diff;
            //return $countA < $countB;
        });

        // How many ticks have there been send the last_refreshed_at time stamp.
        $count = 0;
        foreach ($this->ticks_history as $i => $tick_history) {
            $is_new_tick =
                strtotime($timestamp) - $this->bar_time <
                strtotime($tick_history["refreshed_at"]);

            if ($is_new_tick) {
                $count += 1;
            }
        }

        $this->tick_count = $count;
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
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        // test
        $t = $this->agent_input;
        if (is_array($this->agent_input)) {
            $t = "array";
        }

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);

            // test

            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    public function get()
    {
        $this->variables = new Variables(
            $this->thing,
            "variables bar " . $this->from
        );

        $max_bar_count = $this->variables->getVariable("max_bar_count");

        if (isset($max_bar_count) and $max_bar_count != false) {
            $this->max_bar_count = $max_bar_count;
        }
        $this->last_bar_count = $this->variables->getVariable("count");

        if ($this->last_bar_count === false) {
            $this->last_bar_count = 0;
        }

        $this->last_refreshed_at = $this->variables->getVariable(
            "refreshed_at"
        );
        $this->thing->log(
            $this->agent_prefix . "loaded " . $this->last_bar_count . "."
        );

        $this->getBars();
    }

    function makeSMS()
    {
        $sms = "BAR | ";

        if (!isset($this->bar_count) or $this->bar_count === false) {
            $sms .= "Bar count not set. Text BAR ADVANCE.";
        } else {
            if (
                isset($this->bar_terse_flag) and
                $this->bar_terse_flag == "on"
            ) {
                $sms .=
                    $this->bar_count .
                    " of " .
                    $this->max_bar_count .
                    ". " .
                    $this->tick_count .
                    " ticks. " .
                    $this->last_refreshed_at .
                    ". " .
                    $this->bar_time .
                    " time.";
            } else {
                $sms .=
                    $this->bar_count .
                    " of " .
                    $this->max_bar_count .
                    ". Counted " .
                    $this->tick_count .
                    " ticks since the last bar update " .
                    $this->age .
                    " ago. " .
                    " current time " .
                    $this->current_time .
                    " " .
                    " last refreshed at " .
                    $this->last_refreshed_at .
                    " " .
                    " bar time " .
                    $this->bar_time .
                    " ";
            }
        }

        if (isset($this->bar_terse_flag) and $this->bar_terse_flag == "on") {
        } else {
            $sms .= $this->response;
        }

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
        //   $input = strtolower($this->subject);
        $input = $this->input;

        $this->number_agent = new Number($this->thing, "number bar");
        $this->number_agent->extractNumber($this->input);

        $pieces = explode(" ", strtolower($input));
        $keywords = ["advance", "reset", "maximum", "bar"]; // Order important?
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    $this->Perform($piece);
                    break 2;
                }
            }
        }

        if (!isset($this->bar_count)) {
            $this->bar_count = $this->last_bar_count;
        }

        $this->doBar();
    }

    public function Perform($piece)
    {
        switch ($piece) {
            case "advance":
                $this->advanceBar();
                return;
            case "reset":
                $this->resetBar();
                return;
            case "maximum":
                $this->maxcountBar();
                return;

            case "on":
            default:
                if ($this->number_agent->number != false) {
                    $this->bar_count = $this->number_agent->number;
                    $this->response .=
                        "Number found. Set bar count to " .
                        $this->bar_count .
                        ". ";
                    return null;
                }

                return;
        }
    }

    function nullBar()
    {
    }

    function advanceBar($depth = null)
    {
        if (!isset($this->last_bar_count) or $this->last_bar_count === false) {
            $this->bar_count = 1;
            $this->response .= "Started bar count. ";
            return;
        }

        $this->bar_count = $this->last_bar_count + 1;

        if ($this->bar_count > $this->max_bar_count) {
            $this->bar_count = $this->bar_count % $this->max_bar_count;
            $this->response .= "Wrapped bar count. ";
        }
    }

    function maxcountBar()
    {
        if ($this->number_agent->number != false) {
            $this->max_bar_count = $this->number_agent->number;
            $this->response .=
                "Number found. Set max bar count to " .
                $this->max_bar_count .
                ". ";
            return null;
        }
    }

    function resetBar()
    {
        $this->bar_count = 1;
        $this->response .= "Reset bar count. ";
    }

    function getBars()
    {
        $this->thing->db->setFrom("null" . $this->mail_postfix);

        $t = $this->thing->db->agentSearch("bar", 99);

        $this->ticks_history = [];
        $things = $t["things"];

        if ($things === false) {
            return;
        }

        foreach ($things as $thing_object) {
            $variables_json = $thing_object["variables"];
            $variables = $this->thing->variables->jsontoArray($variables_json);
            if (isset($variables["bar"])) {
                $bar_count = "X";
                $refreshed_at = "X";

                if (isset($variables["bar"]["count"])) {
                    $bar_count = $variables["bar"]["count"];
                }
                if (isset($variables["bar"]["refreshed_at"])) {
                    $refreshed_at = $variables["bar"]["refreshed_at"];
                }

                $this->bars_history[] = [
                    "count" => $bar_count,
                    "refreshed_at" => $refreshed_at,
                ];
            }
        }

        $this->thing->db->setFrom($this->from);
    }

    function getTicks()
    {
        $this->thing->db->setFrom("null" . $this->mail_postfix);

        $t = $this->thing->db->agentSearch("cron", 99);
        $things = $t["things"];

        if ($things === false) {
            return;
        }

        $this->ticks_history = [];
        foreach ($things as $thing_object) {
            $variables_json = $thing_object["variables"];
            $variables = $this->thing->variables->jsontoArray($variables_json);

            if (isset($variables["tick"])) {
                $tick_count = "X";
                $refreshed_at = "X";

                if (isset($variables["tick"]["count"])) {
                    $tick_count = $variables["tick"]["count"];
                }
                if (isset($variables["tick"]["refreshed_at"])) {
                    $refreshed_at = $variables["tick"]["refreshed_at"];
                }

                $this->ticks_history[] = [
                    "count" => $tick_count,
                    "refreshed_at" => $refreshed_at,
                ];
            }
        }

        $this->thing->db->setFrom($this->from);
    }

    function doBar($depth = null)
    {
        if ($this->from != "null" . $this->mail_postfix) {
            $this->response .= "Not from null. ";
            return false;
        }

        // Call stack job with the current bar,
        // to trigger stack related jobs on file.
        $this->stackJob($this->bar_count);
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

        if (!isset($this->bar_count) or $this->bar_count == "X") {
            $this->bar_count = 0;
        }
        $count_notation = $this->bar_count + 1;

        if (file_exists($font)) {
            if (
                $count_notation != 1 or
                $count_notation == $this->max_bar_count
            ) {
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
                $count_notation + 1 == $this->max_bar_count + 1
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
