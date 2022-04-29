<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("allow_url_fopen", 1);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Authority extends Agent
{
    public $var = 'hello';

    // Cat seemed to be a good place to start on authority.

    public function init()
    {
    }

    public function run()
    {
        $this->doAuthority();
        //$this->makeAuthority();
    }

    public function set()
    {
        $this->authority['refreshed_at'] = $this->current_time;

        $this->thing->Write(["authority"], $this->authority);
    }

    public function isAuthority($authority = null)
    {
        if ($authority == null) {
            return false;
        }

        if (!isset($authority['run_at'])) {
            return false;
        }

        $age = strtotime($this->current_time) - strtotime($authority['run_at']);

        if ($age / (60 * 60) < $authority['runtime']) {
            return true;
        }

        return false;
    }

    public function doAuthority()
    {
        // From perspective of channel.

        // Check for a token authority.
        // Check for a list authority.
        $this->getAuthorities();

        // Calculate the expiry of the authority.

        // Check if the authority has expired. Is in the past.

        if ($this->agent_input == null) {
            $array = ['No authority found.'];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "AUTHORITY | " . $v;

            $this->message = $response; // mewsage?
        } else {
            $this->message = $this->agent_input;
        }
    }

    function makeAuthority($text = null)
    {
$name = "stack token";
if ($text != null) {
$name = $text;
}

        $run_at = $this->current_time;
        $runtime = 8; //hours until expiry.

        $authority = [
            "name" => $name,
            "run_at" => $run_at,
            "runtime" => $runtime,
        ];
        $this->authority = $authority;
$this->response .= "Made an authority. ";
return $authority;
    }

    function getAuthorities()
    {
        $this->authorities = [];

        $things = $this->getThings('authority');

        if ($things == false) {return;}

        foreach ($things as $uuid => $thing) {
            // devstack.
            $authority = $thing->variables['authority'];

            if (!isset($authority['name'])) {
                continue;
            }

            $response = $this->isAuthority($authority);

            //if ($response === true) {continue;}
            if ($response === false) {
                continue;
            }

            $this->authorities[$authority['name']][$uuid] = $authority;
        }
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "authority");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is an authority keeping an eye on what is allowed.";
        $this->thing_report["help"] = "This is about being transparent.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = ["authority" => ["authorities", "author"]];
        $authorities_list = "";

        foreach ($this->authorities as $name => $authority) {
            $authorities_list .= $name . " / ";
        }
        $sms = $this->message . " " . $authorities_list;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "authority");
        $choices = $this->thing->choice->makeLinks('authority');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
