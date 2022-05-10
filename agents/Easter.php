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
        $easter_date_text = "Could not compute.";

        if ($this->year >= 1970 and $this->year <= 2037) {
            $easter_date_text = $this->computeEaster($this->year);
        }

        if ($this->agent_input == null) {
            $response = "EASTER | " . $easter_date_text . " [" . $this->year . "].";

            $this->message = $response; // mewsage?
        } else {
            $this->message = $this->agent_input;
        }
    }

    function computeEaster($year = null)
    {
        /*
Tip: The date of Easter Day is defined as the Sunday after the first full moon which falls on or after the Spring Equinox (21st March).
https://www.w3schools.com/php/func_cal_easter_days.asp
https://www.php.net/manual/en/function.easter-date.php
*/
        $day_count = easter_date($year);

        $datum = new \DateTime();
        $datum->setTimestamp($day_count);

        $t = $this->timestampTime($datum);

        $text = $datum->format('j F Y');

        return $text;
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
        $input = $this->input;
        $year_array = $this->extractYear($input);

        $year = $year_array['year'];

        if ($year == null) {
            $year = $this->currentYear();
        }

        $this->year = $year;

        return false;
    }
}
