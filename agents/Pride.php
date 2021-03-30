<?php
namespace Nrwtaylor\StackAgentThing;
//echo "Watson says hi<br>";

class Pride extends Agent
{
    // Not associated with Vancouver Pride.

    public $var = 'hello';

    function init()
    {
    }


    public function respondResponse()
    {
        $this->thing->flagGreen();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }
    }

    public function readSubject()
    {
        $this->response = "Watson says hello";

        $this->sms_message =
            "PRIDE VANCOUVER | http://www.vancouverpride.ca/ | TEXT PRIDE SNOWFLAKE";
        $this->message = "http://www.vancouverpride.ca/";
        $this->keyword = "pride";

        $this->thing_report['keyword'] = $this->keyword;
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['email'] = $this->message;

        return $this->response;
    }
}
