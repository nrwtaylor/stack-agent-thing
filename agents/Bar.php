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
        $this->variables->setVariable("count", $this->bar_count);
        $this->variables->setVariable("max_bar_count", $this->max_bar_count);

        $this->variables->setVariable("refreshed_at", $this->current_time);
    }

    public function run()
    {
        // devstack develop per user bar counts
        $this->getBars();
        $this->getTicks();

        $count = 0;
        foreach ($this->ticks_history as $i => $tick_history) {
            if (
                strtotime($this->last_refreshed_at) >
                strtotime($tick_history["refreshed_at"])
            ) {
                break;
            }
            $count += 1;
        }

        $this->tick_count = $count;
        $this->doBar();
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

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);

            // test
            $this->sendDiscord($this->thing_report["sms"] . " " . $this->input, "Kokopelli");

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
    }

    function makeSMS()
    {
        $message = "BAR";

        if (!isset($this->bar_count) or $this->bar_count === false) {
            $message .= " | Bar count not set. Text BAR ADVANCE.";
        } else {
            $message .=
                " | " .
                $this->bar_count .
                " of " .
                $this->max_bar_count .
                ". Counted " .
                $this->tick_count .
                " ticks since the last bar update. " .
                $this->response;
        }

        $this->sms_message = $message;
        $this->thing_report["sms"] = $message;
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
            $variables = $this->thing->json->jsontoArray($variables_json);
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
            $variables = $this->thing->json->jsontoArray($variables_json);

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
            return false;
        }

        $this->stackJob($this->bar_count);
        return;

        $this->thing->log($this->agent_prefix . "called Tallycounter.");

        $thing = new Thing(null);
        $thing->Create(null, "tallycounter", "s/ tallycounter message");
        $tallycounter = new Tallycounter(
            $thing,
            "tallycounter message tally" . $this->mail_postfix
        );

        $this->response .= "Did a tally count. ";

        $thing = new Thing(null);
        $thing->Create(null, "watchdog", "s/ watchdog");
        $watchdog = new Watchdog($thing, "watchdog");

        $this->response .= "Called the watchdog. ";

        if ($this->bar_count == 0) {
            $thing = new Thing(null);
            $thing->Create(null, "stack", "s/ stack count");
            $stackcount = new Stack($thing, "stack count");

            $this->response .= "Did a stack count. ";
        }

        if ($this->bar_count % 2 == 0) {
            $thing = new Thing(null);
            $thing->Create(null, "latency", "s/ latency check");
            $stackcount = new Latency($thing, "latency check");

            $this->response .= "Checked stack latency. ";
        }

        if ($this->bar_count % 5 == 0) {
            $thing = new Thing(null);
            $thing->Create(null, "manager", "s/ manager");
            $stackcount = new Manager($thing, "manager");

            $this->response .= "Checked manager. ";
        }

        if ($this->bar_count % 7 == 0) {
            $arr = json_encode([
                "to" => "null" . $this->mail_postfix,
                "from" => "damage",
                "subject" => "s/ damage Z",
            ]);

            $client = new \GearmanClient();
            $client->addServer();
            $client->doLowBackground("call_agent", $arr);

            $this->response .= "Damage. ";
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
