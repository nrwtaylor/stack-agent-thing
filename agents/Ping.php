<?php
/**
 * Ping.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class Ping extends Agent
{
    public $var = "hello";

    /**
     *
     */
    public function init()
    {
        // So I could call
        $this->test = false;
        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->node_list = ["ping" => ["pong"]];
    }

    /**
     *
     */
    public function run()
    {
        $this->getPing();
    }

    function test()
    {
        $this->test_result = "Not OK";
        if ($this->ping_time <= 5) {
            $this->test_result = "OK";
        }
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["email"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["keyword"] = "pingback";
        $this->thing_report["help"] = "Checks if the stack is there.";
    }

    /**
     *
     */
    public function makeSMS()
    {
        $this->sms_message = "PING | A message from this Identity pinged us.";
        $this->sms_message .=
            " | Received " .
            $this->thing->human_time($this->ping_text) .
            " ago.";

        $this->thing_report["sms"] = $this->sms_message;
    }

    /**
     *
     */
    public function getPing()
    {
        $received_at = $this->created_at;
        $this->ping_time = time() - $received_at;

        if ($this->ping_time < 1) {
            $this->ping_text = "<1 second";
        } else {
            $this->ping_text = $this->ping_time;
        }
    }

    /**
     *
     */
    public function makeMessage()
    {
        $message = "A message from this Identity pinged us.";
        $message .=
            " Received " . $this->thing->human_time($this->ping_text) . " ago.";

        $this->sms_message = $message;
        $this->thing_report["message"] = $message;
    }

    /**
     *
     */
    public function readSubject()
    {
        $this->response .= "Responded to a ping. ";
    }
}
