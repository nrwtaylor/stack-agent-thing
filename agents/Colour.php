<?php
/**
 * Colour.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Colour extends Agent
{
    public $var = "hello";

    /**
     *
     */
    function init()
    {
        $this->node_list = ["address" => ["n-gram", "address"]];
        $this->colour_indicators = ["red", "green", "blue", "yellow"];
        // TODO develop file of colour names.
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function isColour($text)
    {
        foreach ($this->colour_indicators as $indicator) {
            $variants = [];
            $variants[] = " " . $indicator . " ";
            $variants[] = " " . $indicator . ".";
            $variants[] = " " . $indicator . ",";
            $variants[] = "," . $indicator . ",";
            $variants[] = "(" . $indicator . ")";
            $variants[] = "{" . $indicator . "}";
            $variants[] = "[" . $indicator . "]";

            foreach ($variants as $variant) {
                if (stripos($text, $variant) !== false) {
                    return true;
                }
            }
        }

        $colours = $this->extractColours($text);
        if (count($colours) > 0) {
            return true;
        }

        return false;
    }

    // https://stackoverflow.com/questions/2957609/how-can-i-give-a-color-to-imagecolorallocate
    function allocatehexColor($im, $hex)
    {
        $hex = ltrim($hex, "#");
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return imagecolorallocate($im, $r, $g, $b);
    }

    public function hextorgbColour($hex)
    {
        $hex = ltrim($hex, "#");
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return [$r, $g, $b];
    }

    public function makeLink()
    {
        $this->link = false;

        if (isset($this->colour) and isset($this->colour["hex"])) {
            $colour = $this->colour["hex"];
            $hex = ltrim($colour, "#");

            $this->link = "https://htmlcolors.com/hex/" . $hex;
        }
        $this->thing_report["link"] = $this->link;
    }

    public function closesthexColour($hex)
    {
        $hex = ltrim($hex, "#");
        $target_r = hexdec(substr($hex, 0, 2));
        $target_g = hexdec(substr($hex, 2, 2));
        $target_b = hexdec(substr($hex, 4, 2));

        if (!isset($this->colour_names)) {
            $this->colour_names = $this->loadColours();
        }

        $closest_distance = 1e99;

        foreach ($this->colour_names as $slug => $colour_array) {
            list($r, $g, $b) = $this->hextorgbColour($colour_array["hex"]);

            $distance = pow(
                pow($target_r - $r, 2) +
                    pow($target_g - $g, 2) +
                    pow($target_b - $b, 2),
                0.5
            );
            if ($distance < $closest_distance) {
                $closest_colour = $colour_array;
                $closest_distance = $distance;
            }
        }

        return $closest_colour;
    }

    public function hexColour($text)
    {
        $pattern = "/\#[A-Za-z0-9]{6}/i";

        preg_match_all($pattern, $text, $match);
        if (!isset($colours)) {
            $colours = [];
        }

        $colours = array_merge($colours, $match[0]);
        $colours = array_unique($colours);

        if (count($colours) === 1) {
            return $colours[0];
        }
        if (count($colours) > 1) {
            return true;
        }
        return false;
    }

    public function textColour($text)
    {
        if (!isset($this->colour_names)) {
            $this->colour_names = $this->loadColours();
        }

        foreach ($this->colour_names as $slug => $colour_array) {
            if (strtolower($text) === strtolower($colour_array["name"])) {
                return $colour_array;
            }
        }
        return false;
    }

    public function closesttextColour($text)
    {
        if (!isset($this->colour_names)) {
            $this->colour_names = $this->loadColours();
        }

        $closest_distance = 1e99;

        foreach ($this->colour_names as $slug => $colour_array) {
            $distance = levenshtein(
                $this->getSlug($colour_array["name"]),
                $this->getSlug($text)
            );
            if ($distance < $closest_distance) {
                $closest_colour = $colour_array;
                $closest_distance = $distance;
            }
        }
        return $closest_colour;
    }

    public function texthexColour($text)
    {
        $hex = $this->textColour($text);
        if (isset($hex["hex"])) {
            return $hex["hex"];
        }
        return false;
    }

    public function respondResponse()
    {
        //        $this->makeHelp();
        //        $this->makeInfo();
        $this->thing->flagGreen();

        $message_thing = new Message($this->thing, $this->thing_report);
        //$thing_report['info'] = $message_thing->thing_report['info'];
    }

    public function makeSMS()
    {
        $sms_message = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    public function extractColour($text)
    {
        $found_colour = false;

        // This extracts hex colors.
        $colours = $this->extractColours($text);
        if (count($colours) !== 0) {
            $this->response .= "Saw " . $colours[0] . " is a colour. ";
            $x = $this->closesthexColour($colours[0]);

            if (strtolower($x["hex"]) === strtolower($colours[0])) {
                $this->response .= "A colour called " . $x["name"] . ". ";
            } else {
                $this->response .=
                    "A colour close to " .
                    $x["name"] .
                    " (" .
                    $x["hex"] .
                    "). ";
            }

            $found_colour = $x;
        }

        $ngrams = $this->extractNgrams($text, 3);

        $colours = [];
        foreach ($ngrams as $i => $ngram) {
            $temp_colour = $this->textColour(strtolower($ngram));

            if ($temp_colour === false) {
                continue;
            }
            $colours[
                $this->getSlug(strtolower($temp_colour["name"]))
            ] = $temp_colour;
        }

        // Remove 'subcolors' ie blue if royal blue.

        foreach ($colours as $slug_a => $colour_array_a) {
            foreach ($colours as $slug_b => $colour_array_b) {
                if ($slug_a === $slug_b) {
                    continue;
                }
                if (
                    strpos($colour_array_a["name"], $colour_array_b["name"]) !==
                    false
                ) {
                    unset($colours[$slug_b]);
                }
            }
        }

        $colour = false;
        if (count($colours) === 1) {
            $colour = reset($colours);
        }

        // Found exact if colour is not false.
        if ($colour !== false) {
            $this->response .=
                "Saw " .
                $colour["name"] .
                " is a colour (" .
                $colour["hex"] .
                "). ";
            $found_colour = $colour;
        }

        return $found_colour;
    }

    public function readSubject()
    {
        $input = $this->input;
        $filtered_input = $this->assert(strtolower($input));
        if ($filtered_input != "") {
            $colour = $this->extractColour($this->input);

            $colours = $this->extractColours($this->input);

            if (count($colours) !== 0) {
                $this->response .= "Saw " . $colours[0] . " is a colour. ";
                $x = $this->closesthexColour($colours[0]);

                if ($x["hex"] === $colours[0]) {
                    $this->response .= "A colour called " . $x["name"] . ". ";
                } else {
                    $this->response .=
                        "A colour close to " .
                        $x["name"] .
                        " (" .
                        $x["hex"] .
                        "). ";
                }

                $this->colour = $x;
            }

            $colour = $this->textColour($filtered_input);
            // Found exact.
            if ($colour !== false) {
                $this->response .=
                    "Saw " .
                    $filtered_input .
                    " is a colour (" .
                    $colour["hex"] .
                    "). ";
                $this->colour = $colour;
            } else {
                // See if there is a valid response

                // dev explore cascading structural response.
                // Before generalizing in Agent.

                // Avoid looping
                // Refactor this into Agent

                if (
                    !isset($this->thing->{$this->agent_name . "_input_history"})
                ) {
                    $this->thing->{$this->agent_name . "_input_history"} = [];
                }
                $this->thing->{$this->agent_name .
                    "_input_history"}[] = $filtered_input;

                $prior_colour_input =
                    $this->thing->{$this->agent_name . "_input_history"}[
                        count(
                            $this->thing->{$this->agent_name . "_input_history"}
                        ) - 1
                    ];
                $current_colour_input = $filtered_input;

                if (
                    count(
                        $this->thing->{$this->agent_name . "_input_history"}
                    ) >
                        1 and
                    $prior_colour_input === $current_colour_input
                ) {
                    return false;
                }

                $agent = new Agent($this->thing, $filtered_input);
                $sms = $agent->thing_report["sms"];
                $message = $agent->thing_report["message"];
                $colour = $this->extractColour($message);

                // If no color is found but there is bit of text provided.
                // Try to find it.
                // Look for a close levenshtein match.
                // If the text isn't an agent.
                if ($colour === false and !$this->isAgent($filtered_input)) {
                    $colour = $this->closesttextColour($filtered_input);
                    $this->response .=
                        "Saw " .
                        $colour["name"] .
                        " might be a close match colour (" .
                        $colour["hex"] .
                        "). ";
                }
                $this->colour = $colour;
            }
        }

        if (!isset($this->colour)) {
            $this->response .= "Did not hear a colour. ";
        }
    }
}
