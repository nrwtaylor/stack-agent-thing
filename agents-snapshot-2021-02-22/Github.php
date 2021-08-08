<?php
/**
 * Github.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Github extends Agent {
    // Not associated with Github.
    // Except the stack-agent-thing package is shared there.
    // But a Thing needs to know what Github is.

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init() {
        $this->retain_for = 24; // Retain for at least 24 hours.
    }

    /**
     *
     * @return unknown
     */
    public function respond() {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        $to = $this->thing->from;
        $from = "github";

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

    }


    /**
     *
     */
    public function readSubject() {
        $this->response = "Provided the stack-agent-thing github location.";

        $this->sms_message = "GITHUB | https://github.com/nrwtaylor/stack-agent-thing | REPLY QUESTION";
        $this->message = "www.github.com";
        $this->keyword = "github";

        $this->thing_report['keyword'] = $this->keyword;
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['email'] = $this->message;
        $this->thing_report['help'] = "Provides the public location of the code for this engine.";

    }

}
