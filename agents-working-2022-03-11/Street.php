<?php
/**
 * Street.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Street extends Agent
{
    public $var = "hello";

    /**
     *
     */
    function init()
    {
        $this->node_list = ["address" => ["n-gram", "address"]];
        $this->street_indicators = [
            "road",
            "street",
            "boulevard",
            "lane",
            "avenue",
            "highway",
            "place",
            "terrace",
            "route",
            "square",
            "way",
            "mews",
            "court",
            "boulevard",
            "thoroughfare",
            "alley",
            "rue",
            "artery",
            "cross street",
            "esplanade",
            "boardwalk",
            "row",
            "embankment",
            "close",
            "circle",
            "mall",
            "circus",
            "arcade",
            "via",
            "strasse",
            "drive",
            "roadway",
            "turnpike",
            "expressway",
            "freeway",
            "superhighway",
            "street",
            "motorway",
            "st." . "st",
            "blvd",
            "ave",
            "av",
            "alley",
            "br",
            "ctr",
            "cir",
            "ct",
            "cres",
            "dr",
            "e",
            "expwy",
            "fwy",
            "hwy",
            "hill",
            "isl",
            "jct",
            "ln",
            "lk",
            "lake",
            "loop",
            "lp",
            "mount",
            "moutain",
            "mt",
            "mtn",
        ];
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function isStreet($text)
    {
        foreach ($this->street_indicators as $street_indicator) {
            $variants = [];
            $variants[] = " " . $street_indicator . " ";
            $variants[] = " " . $street_indicator . ".";
            $variants[] = " " . $street_indicator . ",";

            foreach ($variants as $variant) {
                if (stripos($text, $variant) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
