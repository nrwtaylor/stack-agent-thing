<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Flag extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->keyword = "flag";

        $this->test = "Development code"; // Always

        // Set up default flag settings
        $this->verbosity = 1;
        $this->requested_state = null;
        $this->default_state = "green";
        $this->node_list = ["green" => ["red" => ["green"]]];

        // Get some stuff from the stack which will be helpful.

        $this->link = $this->web_prefix . "thing/" . $this->uuid . "/flag";

        $this->refreshed_at = null;

        $this->current_time = $this->thing->time();
        $this->nauticalFlags();
        $this->colourFlags();
    }

    function set($requested_state = null)
    {
        if ($requested_state == null) {
            if (!isset($this->requested_state)) {
                $this->requested_state = "green"; // If not sure, show green.

                if (isset($this->state)) {
                    $this->requested_state = $this->state;
                }
            }

            $requested_state = $this->requested_state;
        }

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

        $this->flag->setVariable("state", $this->state);
        $this->flag->setVariable("refreshed_at", $this->current_time);
    }

    public function lexiconFlags($lexicon_name = null)
    {
        $haystack = function ($variable) {
            $text = "";
            if (is_string($variable)) {
                return $variable;
            }
            foreach ($variable as $parameter => $arr) {
                if (is_string($arr)) {
                    $text .= $arr . " ";
                    continue;
                }

                if (is_int($arr)) {
                    $text .= $arr . " ";
                    continue;
                }

                if ($arr == null) {
                    continue;
                }

                foreach ($arr as $i => $w) {
                    $text .= $w . " ";
                }
            }
            return trim($text);
        };

        $flags_lexicon = [];
        $this->flags_resource[$lexicon_name] =
            "flag/" . $lexicon_name . "-flags.php";
        $flags = require $this->resource_path .
            $this->flags_resource[$lexicon_name];

        foreach ($flags as $group_name => $group) {
            foreach ($group["set"] as $flag_name => $flag_descriptor) {
                // dev improve
                if (is_numeric($flag_name)) {
                    continue;
                }

                //if ((!isset($flag_descriptor)) or ($flag_descriptor == [])) {var_dump("no descriptor",$flag_name);}

                $flag_slug = $this->getSlug($flag_name);

                if (!isset($flag_descriptor["word"])) {
                    $flag_descriptor["word"] = $flag_name;
                }

                if (!isset($flags_lexicon[$flag_slug])) {
                    $flags_lexicon[$flag_slug] = $flag_descriptor;
                    $flags_lexicon[$flag_slug]["haystack"] = $haystack(
                        $flag_descriptor
                    );

                    $flags_lexicon[$flag_slug]["slug"] = $flag_slug;
                }
                /*
                if (!isset($flag_descriptor["word"])) {
                    $flag_slug_word = $flag_slug;

if(is_string($flag_descriptor)) {$flag_descriptor = ['word'=>$flag_descriptor];}

                    $flags_lexicon[$flag_slug] = $flag_descriptor;

                    $flags_lexicon[$flag_slug]["haystack"] = $haystack(
                        $flag_descriptor
                    );
                    $flags_lexicon[$flag_slug]["slug"] = $flag_slug;
                    continue;
                }
*/

                foreach ($flag_descriptor["word"] as $parameter => $flag_word) {
                    $flag_slug_word = $this->getSlug($flag_word);

                    //                    if (!isset($flags_lexicon[$flag_slug_word])) {
                    //                        $flags_lexicon[$flag_slug_word] = [];
                    //                    }
                    $flags_lexicon[$flag_slug_word] = $flag_descriptor;

                    $flags_lexicon[$flag_slug_word]["haystack"] = $haystack(
                        $flag_descriptor
                    );

                    $flags_lexicon[$flag_slug_word]["slug"] = $flag_slug;
                }
            }
        }
        //var_dump($flags_lexicon);
        $this->flags_lexicon[$lexicon_name] = $flags_lexicon;
    }

    public function nauticalFlags($text = null)
    {
        $this->lexiconFlags("nautical");
    }

    public function colourFlags($text = null)
    {
        $this->lexiconFlags("colour");
    }

    public function extractFlags($text = null)
    {
        if ($text == null) {
            return true;
        }

        $tokens = explode(" ", $text);
        $lexicons = ["nautical", "colour"];
        $matches = [];
        foreach ($tokens as $i => $token) {
            foreach ($lexicons as $i => $lexicon_name) {
                foreach (
                    $this->flags_lexicon[$lexicon_name]
                    as $flag_slug => $flag_descriptor
                ) {
                    if (!is_string($flag_slug)) {
                        $flag_slug = "" . $flag_slug;
                    }

                    if ($this->getSlug($token) == $flag_slug) {
                        //if (!isset($flag_descriptor['slug'])) {
                        //    $matches[] = $flag_descriptor['slug'];
                        //}
                        if (!isset($flag_descriptor["slug"])) {
                        }
                        $matches[] = $flag_descriptor["slug"];
                    }
                }
            }
        }
        return $matches;
    }

    public function extractFlag($text = null)
    {
        if ($text == null) {
            return true;
        }

        $flag_slugs = $this->extractFlags($text);

        if (count($flag_slugs) == 1) {
            return $flag_slugs[0];
        }
        return false;
    }

    function isFlag($flag = null)
    {
        // Validates whether the Flag is green or red.
        // Nothing else is allowed.

        if ($flag == null) {
            if (!isset($this->state)) {
                $this->state = "red";
            }

            $flag = $this->state;
        }
        $matches = [];
        foreach (
            $this->flags_lexicon["nautical"]
            as $flag_slug => $flag_descriptor
        ) {
            if (!is_string($flag_slug)) {
                $flag_slug = chr($flag_slug);
            }
            if (strpos($this->getSlug($flag), $flag_slug) !== false) {
                $matches[] = $flag_slug;
            }
        }

        if (count($matches) == 1) {
            return true;
        }

        if (
            $flag == "red" or
            $flag == "green" or
            $flag == "rainbow" or
            $flag == "yellow" or
            $flag == "blue" or
            $flag == "indigo" or
            $flag == "violet" or
            $flag == "orange" or
            $flag == "grey"
        ) {
            return false;
        }

        return true;
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $this->head_code = $this->thing->json->readVariable([
            "headcode",
            "head_code",
        ]);

        $flag_variable_name = "_" . $this->head_code;

        // Get the current Identities flag
        $this->flag = new Variables(
            $this->thing,
            "variables flag" . $flag_variable_name . " " . $this->from
        );

        // get gets the state of the Flag the last time
        // it was saved into the stack (serialized).
        $this->previous_state = $this->flag->getVariable("state");
        $this->refreshed_at = $this->flag->getVariable("refreshed_at");

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isFlag($this->previous_state)) {
            $this->state = $this->previous_state;
        } else {
            $this->state = $this->default_state;
        }
    }

    function selectChoice($choice = null)
    {
        if ($choice == null) {
            if (!isset($this->state)) {
                $this->response .= "Did not find an existing flag. ";
                $this->state = $this->default_state;
            }
            $choice = $this->state;
        }

        if (!isset($this->state)) {
            $this->state = "X";
        }
        $this->previous_state = $this->state;
        $this->state = $choice;

        $this->response .= "Selected a " . $this->state . " flag. ";

        $this->thing->log(
            'Agent "' .
                ucwords($this->keyword) .
                '" chose "' .
                $this->state .
                '".',
            "INFORMATION"
        );

        return $this->state;
    }

    function makeChoices()
    {
        $this->thing->choice->Create(
            $this->keyword,
            $this->node_list,
            $this->state
        );

        $choices = $this->flag->thing->choice->makeLinks($this->state);
        $this->thing_report["choices"] = $choices;
        $this->choices = $choices;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/agent";

        $web = "<b>" . ucwords($this->agent_name) . " Agent</b><br><p>";
        $web .= "<p>";
        $web .= '<a href="' . $link . '">';

        $web .= $this->html_image;

        $web .= "</a>";
        $web .= "<br>";
        $web .= $this->sms_message;
        $this->thing_report["web"] = $web;
    }

    public function readFlag()
    {
        $state_text = "X";
        if (isset($this->state) and $this->state !== false) {
            $state_text = $this->state;
        }

        $this->response .= "Saw a " . $state_text . " Flag. ";
    }

    public function respondResponse()
    {
        // At this point state is set
        //        $this->set($this->state);

        // Thing actions

        $this->thing->flagGreen();

        if ($this->state == "inside nest") {
            $t = "NOT SET";
        } else {
            $t = $this->state;
        }

        $this->thing_report["email"] = $this->message;

        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeHelp()
    {
        if ($this->state == "green") {
            $this->thing_report["help"] =
                "This Flag is either RED or GREEN. GREEN means available.";
        }

        if ($this->state == "red") {
            $this->thing_report["help"] =
                "This Flag is either RED or GREEN. RED means busy.";
        }
    }

    function makeTXT()
    {
        $txt = "This is FLAG POLE " . $this->flag->nuuid . ". ";
        $txt .= "There is a " . strtoupper($this->state) . " FLAG. ";
        if ($this->verbosity >= 5) {
            $txt .=
                "It was last refreshed at " . $this->current_time . " (UTC).";
        }

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    function makeSMS()
    {
        $flag_state = "X";
        if ($this->state != false) {
            $flag_state = $this->state;
        }

        $headcode_text = strtoupper($this->head_code);
        //$headcode_text = "XXXX";

        $sms_message =
            "FLAG " . $headcode_text . " IS " . strtoupper($flag_state);
        if ($this->verbosity > 6) {
            $sms_message .=
                " | previous state " . strtoupper($this->previous_state);
            $sms_message .= " state " . strtoupper($this->state);
            $sms_message .=
                " requested state " . strtoupper($this->requested_state);
            $sms_message .=
                " current node " .
                strtoupper($this->base_thing->choice->current_node);
        }
        if ($this->verbosity > 2) {
            $sms_message .= " | nuuid " . strtoupper($this->thing->nuuid);
        }
        if ($this->verbosity >= 9) {
            $sms_message .=
                " | base nuuid " . strtoupper($this->flag->thing->nuuid);
        }

        if ($this->verbosity > 0) {
            $sms_message .= " | nuuid " . $this->flag->nuuid;
        }

        if ($this->verbosity > 2) {
            if ($this->state == "red") {
                $sms_message .= " | MESSAGE Green";
            }

            if ($this->state == "green") {
                $sms_message .= " | MESSAGE Red";
            }
        }

        $sms_message .= " " . $this->response;

        $sms_message = $this->filterAn($sms_message);

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    function makeMessage()
    {
        $message =
            "This is a FLAG POLE.  The flag is a " .
            trim(strtoupper($this->state)) .
            " FLAG. ";

        if ($this->state == "red") {
            $message .= "It is a BAD time at the moment. ";
        }

        if ($this->state == "green") {
            $message .= "It is a GOOD time now. ";
        }

        //$test_message .= 'And the flag is ' . strtoupper($this->state) . ".";

        $this->message = $message;
        $this->thing_report["message"] = $message; // NRWTaylor. Slack won't take hmtl raw. $test_message;
    }

    public function makeImage()
    {
        $this->image = imagecreatetruecolor(200, 125);
        //$red = imagecolorallocate($this->image, 255, 0, 0);
        //$green = imagecolorallocate($this->image, 0, 255, 0);
        //$grey = imagecolorallocate($this->image, 100, 100, 100);

        //$this->image = imagecreatetruecolor($canvas_size_x, $canvas_size_y);
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

        // https://www.bustle.com/p/9-pride-flags-whose-symbolism-everyone-should-know-9276529

        $this->blue = imagecolorallocate($this->image, 0, 68, 255);

        $this->pride_red = imagecolorallocate($this->image, 231, 0, 0);
        $this->pride_orange = imagecolorallocate($this->image, 255, 140, 0);
        $this->pride_yellow = imagecolorallocate($this->image, 255, 239, 0);
        $this->pride_green = imagecolorallocate($this->image, 0, 129, 31);
        $this->pride_blue = imagecolorallocate($this->image, 0, 68, 255);
        $this->pride_violet = imagecolorallocate($this->image, 118, 0, 137);

        $this->flag_red = imagecolorallocate($this->image, 231, 0, 0);
        $this->flag_orange = imagecolorallocate($this->image, 255, 140, 0);
        $this->flag_yellow = imagecolorallocate($this->image, 255, 239, 0);
        $this->flag_green = imagecolorallocate($this->image, 0, 129, 31);
        $this->flag_blue = imagecolorallocate($this->image, 0, 68, 255);
        // Indigo https://www.rapidtables.com/web/color/purple-color.html
        $this->flag_indigo = imagecolorallocate($this->image, 75, 0, 130);
        $this->flag_violet = imagecolorallocate($this->image, 118, 0, 137);

        $this->indigo = imagecolorallocate($this->image, 75, 0, 130);

        $this->color_palette = [
            $this->pride_red,
            $this->pride_orange,
            $this->pride_yellow,
            $this->pride_green,
            $this->pride_blue,
            $this->pride_violet,
        ];

        // Draw a white rectangle
        if (!isset($this->state) or $this->state == false) {
            $color = $this->grey;
        } else {
            if (isset($this->{$this->state})) {
                $color = $this->{$this->state};
            } elseif (isset($this->{"flag_" . $this->state})) {
                $color = $this->{"flag_" . $this->state};
            }
        }

        if ($this->state == "rainbow") {
            //    $color = $this->grey;
            foreach (range(0, 5) as $n) {
                $a = $n * (200 / 6);
                $b = $n * (200 / 6) + 200 / 6;
                $color = $this->color_palette[$n];

                $a = $n * (125 / 6);
                $b = $n * (125 / 6) + 200 / 6;

                imagefilledrectangle($this->image, 0, $a, 200, $b, $color);
            }
        } else {
            if (!isset($color)) {
                $color = $this->grey;
            }
            imagefilledrectangle($this->image, 0, 0, 200, 125, $color);
        }

        $light_text_list = ["red", "rainbow", "indigo", "violet", "blue"];
        if (in_array($this->state, $light_text_list)) {
            $textcolor = imagecolorallocate($this->image, 255, 255, 255);
        } else {
            $textcolor = imagecolorallocate($this->image, 0, 0, 0);
        }

        // Write the string at the top left
        imagestring($this->image, 2, 150, 100, $this->flag->nuuid, $textcolor);
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

    public function readSubject()
    {
        //$this->response = null;

        $keywords = [
            "flag",
            "red",
            "green",
            "rainbow",
            "blue",
            "indigo",
            "orange",
            "yellow",
            "violet",
            "gray",
            "grey",
            "gris",
            "cinzento",
        ];

        //        if (isset($this->agent_input)) {
        //            $input = $this->agent_input;
        //        } else {
        //            $input = strtolower($this->subject);
        //        }
        $input = $this->input;
        $filtered_input = $this->assert($input);

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == $this->keyword) {
                //$this->get();
                $this->response .= "Got the flag. ";
                return;
            }
        }

        $flag_slug = $this->extractFlag($input);

        if ($flag_slug !== false) {
            $this->selectChoice($flag_slug);
            return;
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "red":
                            $this->thing->log(
                                $this->agent_prefix .
                                    "received request for RED FLAG.",
                                "INFORMATION"
                            );
                            $this->selectChoice("red");
                            return;
                        case "green":
                            $this->selectChoice("green");
                            return;
                        case "rainbow":
                        case "blue":
                        case "indigo":
                        case "orange":
                        case "yellow":
                        case "violet":
                            $this->selectChoice($piece);
                            return;

                        case "gray":
                        case "grey":
                        case "gris":
                        case "cinzento":
                            $this->selectChoice("grey");
                            return;

                        case "next":

                        default:
                    }
                }
            }
        }

        $this->readFlag();
    }
}
