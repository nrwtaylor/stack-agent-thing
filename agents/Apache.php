<?php
namespace Nrwtaylor\StackAgentThing;

class Apache extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doApache();
    }

public function parseApache($line) {

var_dump($line);
exit()
return $line;
}

public function logApache() {

$contents = file_get_contents("/var/log/apache2/access.log");

        $this->matches = array();
        $separator = "\r\n";
        $line = strtok($contents, $separator);
        $parser_name = 'apache';

        while ($line !== false) {

//            $word = $this->parseMatch($line);
$parse_function = "parse".ucfirst($parser_name);
            $word = $this->$parse_function($line);


            $line = strtok( $separator );
                if ($word == false) {continue;}

            $this->matches[$word['proword']] = $word;
            // do something with $line
//            $line = strtok( $separator );
        }


}

    public function doApache()
    {
$this->logApache();

        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "APACHE | " . strtolower($v) . ".";

            $this->apache_message = $response; // mewsage?
        } else {
            $this->apache_message = $this->agent_input;
        }
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "apache");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is Apache keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = array("apache" => array("apache", "dog"));
        $this->sms_message = "" . $this->apache_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "apache");
        $choices = $this->thing->choice->makeLinks('apache');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
