<?php
/**
 * Txt.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Txt extends Agent {


    /**
     *
     * @return unknown
     */
    function init() {

        if ($this->thing->thing != true) {

            $this->thing->log ( 'Agent "Web" ran on a null Thing ' .  $this->thing->uuid .  '');
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

public function makeSMS() {

        $sms = "TXT | ";
$txt_link = "No link available.";
if ((isset($this->link_uuid)) and ($this->link_uuid != null)) {
        $txt_link = $this->web_prefix . "thing/" . $this->link_uuid . "/" . strtolower($this->prior_agent) . ".txt";
        }
$sms .= $txt_link;

        $sms .= " | " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;


}

    /**
     *
     * @return unknown
     */
    public function respondResponse() {
        // Thing actions

        //        $web_thing = new Thing(null);
        //        $web_thing->Create($this->from, $this->agent_name, 's/ record web view');

//        $this->sms_message = "TXT | " . $this->web_prefix . "thing/" . $this->link_uuid . "/" . strtolower($this->prior_agent) . ".txt";

//        $this->sms_message .= " | " . $this->response;
//        $this->thing_report['sms'] = $this->sms_message;


        $this->thing->Write(array("txt",
                "received_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
        );


        $this->thing->flagGreen();

        $choices = false;

        $this->thing_report['message'] = $this->response;

        $this->thing_report['choices'] = $choices;
        $this->thing_report['info'] = 'This is the web agent.';
        $this->thing_report['help'] = 'This agent takes an UUID and runs the Web agent on it.';

        $this->thing->log ( '<pre> Agent "Txt" credited 25 to the Thing account.  Balance is now ' .  $this->thing->account['thing']->balance['amount'] . '</pre>');

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->makeWeb();

        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        return $this->thing_report;
    }


    /**
     *
     * @return unknown
     */
    public function getLink($text = null) {

        $block_things = array();
        // See if a stack record exists.
        $findagent_thing = new Findagent($this->thing, 'thing');

if($findagent_thing->thing_report['things'] === true) {
        $this->response = "Could not make a link. ";

return null;}

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
        $this->prior_agent = "txt";
        foreach ($link_uuids as $key=>$link_uuid) {
            $previous_thing = new Thing($link_uuid);

            if (isset($previous_thing->variables->array_data['message']['agent'])) {

                $this->prior_agent = $previous_thing->variables->array_data['message']['agent'];

                if (in_array(strtolower($this->prior_agent), array('web', 'pdf', 'txt', 'log', 'php'))) {
                    continue;
                }

                $this->link_uuid = $link_uuid;
                break;
            }
        }

        $this->txt_exists = true;
        // Testing with this removed
        //        $token_thing = new Tokenlimiter($previous_thing, "revoke tokens");
        //        $token_thing->revokeTokens(); // Because
        //        $agent_thing = new Agent($previous_thing,"agent");
        //        if (!isset($agent_thing->thing_report['web'] )) {$this->web_exists = false;}
        $previous_thing->silenceOn();
        $quiet_thing = new Quiet($previous_thing, "on");
        $agent_thing = new Agent($previous_thing);
        if (!isset($agent_thing->thing_report['txt'] )) {$this->txt_exists = false;}

        $this->response = "Made a txt link. ";


        return $this->link_uuid;
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {

        //$this->defaultButtons();
        $status = true;
        //$this->response = "Made a txt link.";
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



}
