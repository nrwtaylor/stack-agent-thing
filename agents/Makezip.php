<?php
namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);
ini_set("display_errors", 1);

use setasign\Fpdi;

class makeZip
{
    public $var = "hello";

    function __construct(Thing $thing, $input = null)
    {
        $this->input = $input;

        $agent_thing = new Agent($thing, $input);
        $this->thing_report = [
            "thing" => $thing->thing,
            "zip" => $agent_thing->thing_report["zip"],
        ];
    }
}

?>
