<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Bus extends Agent
{
    public function init()
    {

        $this->state = "red"; // running

        $this->url = $this->web_prefix;
        if (isset($this->thing->container['api']['bus']['url'])) {
            $this->url = $this->thing->container['api']['bus']['url'];
            $this->response .= "Found a url " . $this->url .". ";
        }
        $this->thing_report['help'] = "Helps out if you can't see some things other people see.";
    }


    public function run()
    {
        $this->doBus();
    }

    public function makeChoice() {}

    /**
     *
     * @return unknown
     */
    public function respondResponse() {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        //        $this->thing_report['sms'] = $this->sms_message;

    }

    function set() {
        $this->thing->json->setField("variables");

        $this->thing->json->writeVariable(array("bus",
                "refreshed_at"),  $this->thing->json->time()
        );
    }


    public function get()
    {
    }

    function makeSMS()
    {
        $message = "BUS";

        $message .= " | " . $this->response;

        $this->sms_message = $message;
        $this->thing_report['sms'] = $message;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/bus';

        //$web = '<a href="' . $link . '">';
        // $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/flag.png">';

        //$web .= "</a>";
        //$web .= "<br>";
        $web = "";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';
        $web .= $this->sms_message;

        $this->thing_report['web'] = $web;
    }

    public function readSubject()
    {
$this->response .= "Heard. ";
    }

    function doBus($depth = null) {

        file_get_contents("https://stackr.ca/bus");

        // Get transit context stuff.
        $this->transit_agent = new Transit($this->thing, "transit");
//var_dump($this->transit_agent);

$agency = ucwords($this->transit_agent->agency);
$choices = $this->transit_agent->keywords;
$node_list = $this->transit_agent->node_list;

//$transit_id = $this->transit_agent->transit_id;

//$state = $this->transit_agent->state;

var_dump($agency);
var_dump($choices);
$uuid = $this->getLink();
var_dump($uuid);
$thing = new Thing($uuid);
var_dump($thing->subject);
var_dump($thing->from);

//$agency_agent_word = ucwords($agency);
//var_dump($agency_agent_word);

$agent = $this->getAgent($agency, $thing->subject, $thing);
var_dump($agent->response);
var_dump($agent->message);
var_dump($agent->sms);

//$agency_agent = new {$agency_agent_word}($thing, "transit");

    }


}
