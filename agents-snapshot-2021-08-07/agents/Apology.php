<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("allow_url_fopen", 1);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Apology extends Agent
{
    public $var = 'hello';

    // Cat seemed to be a good place to start on authority.

    public function init()
    {
    }

    public function run()
    {
        $this->doApology();
    }

    public function set()
    {
        $this->thing->json->setField("variables");

        $this->apology['refreshed_at'] = $this->current_time;

        $this->thing->json->writeVariable(["apology"], $this->apology);
    }

    public function isApology($apology = null)
    {
        if ($apology == null) {
            return false;
        }

        if (!isset($apology['run_at'])) {
            return false;
        }

        $age = strtotime($this->current_time) - strtotime($apology['run_at']);

        if ($age / (60 * 60) < $apology['runtime']) {
            return true;
        }

        return false;
    }

    public function doApology()
    {
        // From perspective of channel.

        // Check for a token authority.
        // Check for a list authority.
        $this->getApologies();

        // Calculate the expiry of the authority.

        // Check if the authority has expired. Is in the past.

        if ($this->agent_input == null) {
            $array = ['No apology found.'];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "APOLOGY | " . $v;

            $this->message = $response; // mewsage?
        } else {
            $this->message = $this->agent_input;
        }
    }

    function makeApology($text = null)
    {
        $name = "sorry";
        if ($text != null) {
            $name = $text;
        }

        $run_at = $this->current_time;
        $runtime = 8; //hours until expiry.

        $apology = [
            "name" => $name,
            "run_at" => $run_at,
            "runtime" => $runtime,
        ];
        $this->apology = $apology;
        $this->response .= "Made an apology. ";
        return $apology;
    }

    function getApologies()
    {
        $this->apologies = [];
        $things = $this->getThings('apology');
        foreach ($things as $uuid => $thing) {

            // devstack.
            $apology = $thing->variables['apology'];

            if (!isset($apology['name'])) {
                continue;
            }

            $response = $this->isApology($apology);

            //if ($response === true) {continue;}
            if ($response === false) {
                continue;
            }

            $this->apologies[$apology['name']][$uuid] = $apology;
        }
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "apology");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This is an apology.";
        $this->thing_report["help"] = "This is about saying sorry.";

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = ["apology" => ["apologies", "author"]];
        $apologies_list = "";

        foreach ($this->apologies as $name => $apology) {
            $apologies_list .= $name . " / ";
        }
        $sms = $this->message . " " . $apologies_list;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "apology");
        $choices = $this->thing->choice->makeLinks('apology');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
