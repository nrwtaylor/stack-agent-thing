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

class Hue extends Agent
{
    public $var = "hello";

    /**
     *
     */
    function init()
    {
        $this->node_list = ["hue" => ["color", "colour", "saturation", "luminance", "value", "chroma"]];
        $this->colour_indicators = ["red", "green", "blue", "yellow"];
        // TODO develop file of colour names.
    }


    function get()
    {
        // Take a look at this thing for IChing variables.

        $time_string = $this->thing->Read([
            "hue",
            "refreshed_at",
        ]);

        // And if there is no IChing timestamp create one now.

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["hue", "refreshed_at"],
                $time_string
            );
        }

        $this->hue = $this->thing->Read([
            "hue",
            "hue",
        ]);
    }

    /**
     *
     */
    function set()
    {
        $this->thing->Write(
            ["hue", "hue"],
            $this->hue
        );
    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function isHue($text)
    {
        // Is it a "degree".
        return true; // Not yet implemeneted.
    }

    // https://stackoverflow.com/questions/2957609/how-can-i-give-a-color-to-imagecolorallocate
    public function rgbtoHue($r,$g,$b)
    {

  $r=($r/255); $g=($g/255); $b=($b/255);
  $maxRGB=max($r,$g,$b); $minRGB=min($r,$g,$b); $chroma=$maxRGB-$minRGB;
  if($chroma==0) return array('h'=>0,'s'=>0,'v'=>$maxRGB);
  if($r==$minRGB)$h=3-(($g-$b)/$chroma);
  elseif($b==$minRGB)$h=1-(($r-$g)/$chroma); else $h=5-(($b-$r)/$chroma);

//return array('h'=>60*$h,'s'=>$chroma/$maxRGB,'v'=>$maxRGB);

        return 60 * $h;
    }

/*
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
*/

    public function respondResponse()
    {
        $this->thing->flagGreen();
        $message_thing = new Message($this->thing, $this->thing_report);
    }

    public function makeSMS()
    {
        $sms_message = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    public function extractHue($text) {
return $this->extractDegree($text);

    }

    public function readSubject()
    {
        $input = $this->input;
        $filtered_input = $this->assert(strtolower($input));
        $hue = false;

        if ($filtered_input != "") {
//            $hue = false;
            $hue = $this->extractHue($filtered_input);

            if ($hue !== false) {
                $this->response .= "Saw a Hue of " . $hue . "°. ";
                $this->hue = $hue;
            }
        }

        if ($hue === false) {
            $this->response .= "Did not hear a hue. ";
        }
    }
}
