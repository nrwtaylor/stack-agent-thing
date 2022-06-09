<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Koko extends Agent
{
    public function init()
    {

$test = "KOKOPELLI | Counted 24 kokopellis. [24 2021-09-13T20:11:23Z.] Got 212 previous kokopellis. Read 'Kokopelli job'. input Kokopelli job > Perform job. [weather age 1749 / 3600 [manager age 208 / 120 ] Requested manager. [time age 239 / 300";
$test = "KOKOPELLI | Counted 29 kokopellis. [29 2021-09-13T20:17:58Z.] Got 214 previous kokopellis. Read 'Kokopelli job'. input Kokopelli job > Perform job. [weather age 2144 / 3600 ] [manager age 200 / 120 ] Requested manager. [time age 189 / 300 ]";
$test_shortened = $this->shortenText($test);
var_dump($test_shortened);
$test_shortened = $this->shortenText($test_shortened);
var_dump($test_shortened);
$test_shortened = $this->shortenText($test_shortened);
var_dump($test_shortened);
$test_shortened = $this->shortenText($test_shortened);
var_dump($test_shortened);
$test_shortened = $this->shortenText($test_shortened);
var_dump($test_shortened);
$test_shortened = $this->shortenText($test_shortened);
var_dump($test_shortened);
$test_shortened = $this->shortenText($test_shortened);
var_dump($test_shortened);
$test_shortened = $this->shortenText($test_shortened);
var_dump($test_shortened);


    }

    public function respondResponse()
    {
        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);

            // test

            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    function makeSMS()
    {
        $message = "KOKOPELLI TEST";


        $this->sms_message = $message;
        $this->thing_report["sms"] = $message;
    }

    public function readSubject()
    {
    }

}
