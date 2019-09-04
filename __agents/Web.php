<?php
/**
 * Web.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Web extends Agent {


    /**
     *
     * @return unknown
     */
    function init() {


        if ($this->thing->thing != true) {

            $this->thing->log ( 'Agent "Web" ran on a null Thing ' .  $thing->uuid .  '');
            $this->thing_report['info'] = 'Tried to run Web on a null Thing.';
            $this->thing_report['help'] = "That isn't going to work";

            return $this->thing_report;
        }

        $this->agent_version = 'redpanda';

        $this->node_list = array('start a'=>
            array('useful', 'useful?'),
            'start b'=>array('helpful', 'helpful?')
        );

    }


    /**
     *
     */
    public function run() {
        $this->getLink();
    }


    /**
     *
     * @return unknown
     */
    public function respond() {
        // Thing actions

        //        $web_thing = new Thing(null);
        //        $web_thing->Create($this->from, $this->agent_name, 's/ record web view');

        $this->sms_message = "WEB | " . $this->web_prefix . "thing/" . $this->link_uuid . "/" . strtolower($this->prior_agent);

        $this->sms_message .= " | " . $this->response;
        $this->thing_report['sms'] = $this->sms_message;


        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("web",
                "received_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
        );


        $this->thing->flagGreen();

        $choices = false;

        $this->thing_report['message'] = $this->response;

        $this->thing_report['choices'] = $choices;
        $this->thing_report['info'] = 'This is the web agent.';
        $this->thing_report['help'] = 'This agent takes an UUID and runs the Web agent on it.';

        $this->thing->log ( '<pre> Agent "Web" credited 25 to the Thing account.  Balance is now ' .  $this->thing->account['thing']->balance['amount'] . '</pre>');

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->makeWeb();

        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        return $this->thing_report;
    }


    /**
     *
     * @return unknown
     */
    function getLink() {

        $block_things = array();
        // See if a stack record exists.
        $findagent_thing = new Findagent($this->thing, 'thing');

        $this->max_index =0;

        $match = 0;

        $link_uuids = array();

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {

            $this->thing->log($block_thing['task'] . " " . $block_thing['nom_to'] . " " . $block_thing['nom_from']);

            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing['uuid'];
                $link_uuids[] = $block_thing['uuid'];
                // if ($match == 2) {break;}
                // Get upto 10 matches
                if ($match == 10) {break;}


            }
        }
        $this->prior_agent = "web";
        foreach ($link_uuids as $key=>$link_uuid) {
            $previous_thing = new Thing($link_uuid);

            if (isset($previous_thing->json->array_data['message']['agent'])) {

                $this->prior_agent = $previous_thing->json->array_data['message']['agent'];

                if (in_array(strtolower($this->prior_agent), array('web', 'pdf', 'txt', 'log', 'php'))) {
                    continue;
                }

                $this->link_uuid = $link_uuid;
                break;
            }
        }

        $this->web_exists = true;
        // Testing with this removed
        //        $token_thing = new Tokenlimiter($previous_thing, "revoke tokens");
        //        $token_thing->revokeTokens(); // Because
        //        $agent_thing = new Agent($previous_thing,"agent");
        //        if (!isset($agent_thing->thing_report['web'] )) {$this->web_exists = false;}
        $previous_thing->silenceOn();
        $quiet_thing = new Quiet($previous_thing, "on");
        $agent_thing = new Agent($previous_thing);
        if (!isset($agent_thing->thing_report['web'] )) {$this->web_exists = false;}


        return $this->link_uuid;
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->defaultButtons();
        $status = true;
        $this->response = "Made a web link.";
        return $status;
    }


    /**
     *
     */
    function makeWeb() {
        return;
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $this->node_list = array("web"=>array("iching", "roll"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "web");
        $choices = $this->thing->choice->makeLinks('web');

        $web = '<a href="' . $link . '">';
        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->link_uuid . '/receipt.png">';
        $web .= "</a>";

        $web .= "<br>";

        $web .= $this->subject;
        $web .= "<br>";
        $web .= $this->sms_message;

        $web .= "<br><br>";


        $web .= "<br>";

        $this->thing_report['web'] = $web;

    }


    /**
     *
     */
    function defaultButtons() {

        if (rand(1, 6) <= 3) {
            $this->thing->choice->Create('web', $this->node_list, 'start a');
        } else {
            $this->thing->choice->Create('web', $this->node_list, 'start b');
        }

        $this->thing->flagGreen();
    }


}
