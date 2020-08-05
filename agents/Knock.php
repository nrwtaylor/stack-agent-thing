<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Knock extends Agent
{
    // This is a door knocker.

    public $var = 'hello';

    function init()
    {
        $this->keyword = "knock";

        $this->test = "Development code"; // Always

        $this->keywords = ['knock'];
    }

    function set()
    {
        $this->thing->json->writeVariable(
            ["knock", "refreshed_at"],
            $this->current_time
        );

        $this->refreshed_at = $this->current_time;
    }

    function getContext()
    {
        $this->context_agent = new Context($this->thing, "context");
        $this->context = $this->context_agent->context;
        $this->context_id = $this->context_agent->context_id;
        return $this->context;
    }

    function makeSMS()
    {
        if (isset($this->sms_message)) {
            $this->thing_report['sms'] = $this->sms_message;
            return $this->sms_message;
        }

        $this->sms_message = "KNOCK ";
        $this->sms_message .= " | context " . ucwords($this->context);

        $this->thing_report['sms'] = $this->sms_message;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();
        // Generate email response.

        $this->doEvacsim();
        $this->makeSMS();

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['info'] = 'Provides a simulated door knock.';
        $this->thing_report['help'] = 'Activate KNOCK with EVACSIM ON.';
    }

    function getEvacsim()
    {
        $this->evacsim_agent = new Evacsim($this->thing, "evacsim");
        $this->evacsim = $this->evacsim_agent->state;

        if ($this->evacsim == "on") {
            $this->context_agent = new Context($this->thing, "train");
            $this->context = $this->context_agent->context;
            $this->context_id = $this->context_agent->context_id;
        }

        return $this->evacsim;
    }

    public function readSubject()
    {
        $this->getContext();
        $this->getEvacsim();

        return false;
    }

    function isEvacsim()
    {
        if ($this->evacsim == "on") {
            return true;
        } else {
            return false;
        }
    }

    public function doEvacsim()
    {
        if (!isset($this->evacsim)) {
            $this->getEvacsim();
        }

        if ($this->isEvacsim()) {
            $evacsim_agent = new Evacsim($this->thing, "knock");
            $this->sms_message = $evacsim_agent->sms_message;
        }
    }
}
