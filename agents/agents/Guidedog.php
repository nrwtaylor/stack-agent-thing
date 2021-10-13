<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Guidedog extends Agent
{
    public function init()
    {

        $this->state = "red"; // running

        $this->url = $this->web_prefix;
        if (isset($this->thing->container['api']['guidedog']['url'])) {
            $this->url = $this->thing->container['api']['guidedog']['url'];
            $this->response .= "Found a url " . $this->url .". ";
        }
        $this->thing_report['help'] = "Watches out for barks.";
    }


    public function run()
    {
        $this->doGuidedog();
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

        $this->thing->json->writeVariable(array("guidedog",
                "refreshed_at"),  $this->thing->json->time()
        );
    }


    public function get()
    {
    }

    function makeSMS()
    {
        $message = "GUIDEDOG";

        $message .= " | " . $this->response;

        $this->sms_message = $message;
        $this->thing_report['sms'] = $message;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/guidedog';

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

    function doGuidedog($depth = null) {
        file_get_contents("https://stackr.ca/guidedog");
    }


}
