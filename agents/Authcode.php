<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("allow_url_fopen", 1);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Authcode extends Agent
{
    public $var = 'hello';

    // Cat seemed to be a good place to start on authority.

    public function init()
    {
    }

    public function run()
    {
        $this->doAuthcode();
        //$this->makeAuthcode();
    }

    public function get()
    {
        if (!isset($this->authorities)) {
            $this->authority_agent = new Authority($this->thing, "authority");
            $this->authority_agent->getAuthorities();
            $this->authorities = $this->authority_agent->authorities;
        }
    }

    public function set()
    {
        $this->thing->json->setField("variables");
        $this->authcode['refreshed_at'] = $this->current_time;
        $this->thing->json->writeVariable(["authcode"], $this->authcode);
    }

    public function uuidAuthcode($uuid = null)
    {
        $authcode = $uuid; // devstack
        return $authcode;
    }

    public function isAuthcode($authcode = null)
    {
        if ($authcode == null) {
            return false;
        }

        foreach ($this->authorities['authcode'] as $uuid => $authority) {
            if ($authcode == $this->uuidAuthcode($uuid)) {
                return true;
            }
        }

        return false;
    }

    public function getAuthcodes()
    {
        $this->authcodes = [];
        //        if (!isset($this->authorities)) {
        //            $authority_agent = new Authority($this->thing, "authority");
        $this->authority_agent->getAuthorities();
        $this->authorities = $this->authority_agent->authorities;
        //       }

        foreach ($this->authorities as $name => $authorities) {
            foreach ($authorities as $uuid => $authority) {
                if ($name != 'authcode') {
                    continue;
                }

                $end_at = $this->getEndat($authority);

                $time_remaining =
                    strtotime($end_at) - strtotime($this->current_time);

                if ($time_remaining < 0) {
                    continue; // Ignore authcodes which have expired.
                }

                if (isset($this->authcodes[$uuid])) {
                    $this->response .=
                        "Corrupted authcode table. Text FORGETALL. ";
                    return;
                }

                $this->authcodes[$uuid] = $authority;
            }
        }

        $count = count($this->authcodes);
        $this->response .= "Found " . $count . " valid authcodes. ";
    }

    public function getEndat($authority)
    {
        $endat_agent = new Endat($this->thing, "endat");

        $endat_agent->run_at = $authority['run_at'];
        $endat_agent->runtime = new \stdClass();
        $endat_agent->runtime->minutes = $authority['runtime'] * 60;

        $endat_agent->getEndat();

        return $endat_agent->end_at;
    }

    public function getAuthcode()
    {
        $this->getAuthcodes();

        $this->response .= "Asked for all the authcodes. ";

        if (!isset($this->authcodes)) {
            $this->response .= "No auth codes found. ";
        }
    }

    public function doAuthcode($text = null)
    {
        // From perspective of channel.

        // Check for a token authority.
        // Check for a list authority.

        $t = $this->isAuthcode($text);

        // Calculate the expiry of the authority.

        // Check if the authority has expired. Is in the past.

        if ($this->agent_input == null) {
            $response = "AUTHCODE | ";

            $this->authcode_message = $response; // mewsage?
        } else {
            $this->authcode_message = $this->agent_input;
        }
    }

    function makeAuthcode($text = null)
    {
        $name = "authcode";
        //$run_at = $this->current_time;
        //$runtime = 2; //hours until expiry.

        //       $authority_agent = new Authority($this->thing, "authority");
        $authority = $this->authority_agent->makeAuthority($name);
        $this->authority_agent->set();
        $this->getAuthcodes();
        $this->authcode = $authority;

        $this->response .= "Made a new authcode. ";
    }

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

        if (isset($this->authcodes)) {
            foreach ($this->authcodes as $uuid => $authority) {
                $authorities_list .= $uuid . " / ";
            }
        }

        $sms =
            $this->authcode_message .
            " " .
            $authorities_list .
            " " .
            $this->response;
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
        $input = $this->input;
        $this->keywords = ["authcode", "new"];
        $filtered_input = $this->assert($this->input);

        if ($filtered_input != "") {
            $t = $this->isAuthcode($filtered_input);

            if ($t === true) {
                $this->response .= "Saw an authcode. ";
                return;
            }
            $this->response .= "Did not see an authcode. ";
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'authcode') {
                $this->getAuthcode();
                //$this->response .= "Current authcode retrieved. ".  $this->authcode;
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'new':
                            $this->makeAuthcode();
                            $this->getAuthcode();

                            return;
                    }
                }
            }
        }

        return false;
    }
}
