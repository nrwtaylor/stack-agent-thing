<?php
namespace Nrwtaylor\StackAgentThing;

class Notfound
 extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doCat();
    }

    public function doCat()
    {
$this->response .= "404 Not Found.";
    }


    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This manages things that are not found.";
        $this->thing_report["help"] = "This is about some endpoints not existing.";

//        $this->thing_report['message'] = $this->sms_message;
//        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

//        return $this->thing_report;
    }

function makeWeb()
{

//$web = "<b>NOT FOUND</b><p>";
$web = "";
$web .= "That URL was not found.";
$web .= "<p>";
$web .= $this->response;

$this->thing_report['web'] = $web;

}

    function makeSMS()
    {
        $this->node_list = array("404" => array("404", "OK"));
        $sms = "NOT FOUND | " . $this->response;
        $this->thing_report['sms'] = $sms;
    }

    public function readSubject()
    {
    }
}
