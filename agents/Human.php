<?php
/**
 * Limitedbeta.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Human extends Agent
{
    public $var = 'hello';

    /**
     *
     */
    function init()
    {
        $this->thing->log(
            $this->agent_prefix .
                'running on Thing ' .
                $this->thing->nuuid .
                '.'
        );

        $this->node_list = ["human" => ["human", "agent"]];
    }

public function set() {

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("human", "refreshed_at"), $this->current_time );


}

    /**
     *
     * @return unknown
     */
    public function human()
    {
        if ($this->address === false) {
            return;
        }
        if ($this->address === true) {
            return;
        }

        $this->sms_message =
            'HUMAN | The datagram was forwarded to a mapped address.';
        $this->message = $this->word . ' forwarded to a mapped address.';

        $message = 'The stack received a human addressed message.';

        $thing = new Thing(null);

        $to = $this->address;

        $thing->Create($to, $thing->uuid, 'human ' . $this->from);
        $thing->flagGreen();

        $thing_report['thing'] = $thing;
        $thing_report['message'] = $message;
        $thing_report['sms'] = $message;
        $thing_report['email'] = $message;

        $message_thing = new Message($thing, $thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    /**
     *
     */
    public function readSubject()
    {
        $input = $this->assert($this->input);

        $address_agent = new Address($this->thing, 'address');

        $address = $address_agent->isAddress($this->to, "human");

        // Is there a reference to a known human?
        if ($address === false or $address === true) {
            $address = $address_agent->isAddress($input, "human");
        }

        $this->address = $address;

        $this->human();
    }
}
