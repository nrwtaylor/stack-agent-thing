<?php
namespace Nrwtaylor\StackAgentThing;

class Easter extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doEaster();
    }

    public function doEaster()
    {
        $day_count = $this->computerEaster();

        if ($this->agent_input == null) {
            $array = ['miao', 'miaou', 'hiss', 'prrr', 'grrr'];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "EASTER | " . strtolower($v) . "." . $day_count;

            $this->message = $response; // mewsage?
        } else {
            $this->message = $this->agent_input;
        }
var_dump("merp");
exit();
    }

    function computeEaster()
    {
        /*
Tip: The date of Easter Day is defined as the Sunday after the first full moon which falls on or after the Spring Equinox (21st March).
https://www.w3schools.com/php/func_cal_easter_days.asp
https://www.php.net/manual/en/function.easter-date.php
*/
        $day_count = easter_days(2022);
        var_dump($day_count);
        return $day_count;
    }

    function makeSMS()
    {
        $this->node_list = ["easter" => ["easter"]];
        $this->sms_message = "" . $this->message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "cat");
        $choices = $this->thing->choice->makeLinks('easter');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
