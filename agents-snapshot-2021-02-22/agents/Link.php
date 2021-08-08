<?php
/**
 * Link.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
// Call regularly from cron
// On call determine best thing to be addressed.

// Start by picking a random thing and seeing what needs to be done.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Link extends Agent
{
    /**
     *
     * @param Thing   $thing
     * @return unknown
     */
    function init()
    {
        if ($this->thing != true) {
            //print "falsey";

            $this->thing->log('ran on a null Thing ' . $this->thing->uuid . '');
            $this->thing_report['info'] = 'Tried to run Web on a null Thing.';
            $this->thing_report['help'] = "That isn't going to work";

            return $this->thing_report;
        }

        $this->start_time = $this->thing->elapsed_runtime();

        $this->agent_version = 'redpanda';

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->node_list = ['link' => ['link']];
    }

public function run() {}

    /** * * @param unknown $text (optional) * @return unknown */
    public function extractLinks($text = null)
    {
        preg_match_all(
            '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#',
            $text,
            $match
        );
        $this->links = $match[0];
        return $this->links;
    }

    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    public function extractLink($text = null)
    {
        $m = $this->extractLinks($text);
        if (!isset($m[0])) {
            return null;
        }
        $this->link = $m[0];
        return $m[0];
    }

    function get()
    {
        $this->getLink();
    }

    function set()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["link", "received_at"],
            gmdate("Y-m-d\TH:i:s\Z", time())
        );
    }

    /**
     *
     * @return unknown
     */
    function getLink($variable = null)
    {
        $block_things = [];
        // See if a block record exists.
        $findagent_thing = new Findagent($this->thing, 'thing');

        $this->max_index = 0;

        $match = 0;

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {
            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing['uuid'];
                if ($match == 2) {
                    break;
                }
            }
        }
        return $this->link_uuid;
    }

    public function textLink($text = null)
    {
        $agent_name = 'agent';
        if ($text != null) {
            $agent_name = $text;
        }

        $link =
            $this->web_prefix . 'thing/' . $this->link_uuid . '/' . $agent_name;
        return $link;
    }

    public function makeSMS()
    {
        // Thing actions
        $sms = "LINK | " . $this->textLink();
        //$sms = "LINK | " . $this->web_prefix . "thing/" . $this->link_uuid ."/agent";
        $this->sms_message = $sms;

        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $this->thing->account['thing']->Credit(25);
        $this->thing->account['stack']->Debit(25);

        $choices = false;
        $this->thing_report['choices'] = $choices;

        $this->thing_report['info'] = 'This is the link agent.';
        $this->thing_report['help'] =
            'This gets the http address of the last request.';

        $this->thing->log(
            'credited 25 to the Thing account.  Balance is now ' .
                $this->thing->account['thing']->balance['amount'] .
                ''
        );
if ($this->agent_input == null) {
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
}
        //        return $this->thing_report;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
$input = strtolower($this->input);
if ($input == "link") {
return;
}

    }
}
