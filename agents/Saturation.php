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

class Saturation extends Agent
{
    public $var = "hello";

    /**
     *
     */
    function init()
    {
        $this->node_list = ["saturation" => ["color", "colour", "hue", "luminance", "value", "chroma"]];
        $this->colour_indicators = ["red", "green", "blue", "yellow"];
        // TODO develop file of colour names.
    }


    function get()
    {
        // Take a look at this thing for IChing variables.

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "saturation",
            "refreshed_at",
        ]);

        // And if there is no IChing timestamp create one now.

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["saturation", "refreshed_at"],
                $time_string
            );
        }

        $this->thing->json->setField("variables");
        $this->saturation = $this->thing->json->readVariable([
            "saturation",
            "saturation",
        ]);
    }

    /**
     *
     */
    function set()
    {
        $this->thing->json->writeVariable(
            ["saturation", "saturation"],
            $this->saturation
        );
    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function isSaturation($text)
    {
        // Is it a "degree".
        return true; // Not yet implemeneted.
    }

    // https://stackoverflow.com/questions/2957609/how-can-i-give-a-color-to-imagecolorallocate
    public function rgbtoSaturation($r,$g,$b)
    {

  $r=($r/255); $g=($g/255); $b=($b/255);
  $maxRGB=max($r,$g,$b); $minRGB=min($r,$g,$b); $chroma=$maxRGB-$minRGB;
  if($chroma==0) return array('h'=>0,'s'=>0,'v'=>$maxRGB);
  if($r==$minRGB)$h=3-(($g-$b)/$chroma);
  elseif($b==$minRGB)$h=1-(($r-$g)/$chroma); else $h=5-(($b-$r)/$chroma);

//return array('h'=>60*$h,'s'=>$chroma/$maxRGB,'v'=>$maxRGB);

        return $chroma/$maxRGB;
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

    public function extractSaturation($text) {
        return $this->extractNumber($text);
    }

    public function readSubject()
    {
        $input = $this->input;
        $filtered_input = $this->assert(strtolower($input));
        $saturation = false;

        if ($filtered_input != "") {

            $saturation = $this->extractSaturation($filtered_input);

            if ($saturation !== false) {
                $this->response .= "Saw a Saturation of " . $saturation . ". ";
                $this->saturation = $saturation;
            }
        }

        if ($saturation === false) {
            $this->response .= "Did not hear a saturation. ";
        }
    }
}
