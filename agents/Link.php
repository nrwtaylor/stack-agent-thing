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

class Link {


    /**
     *
     * @param Thing   $thing
     * @return unknown
     */
    function __construct(Thing $thing) {
        //echo "Receipt called";


        $this->thing_report['thing'] = false;

        if ($thing->thing != true) {
            //print "falsey";

            $this->thing->log ( '<pre> Agent "Link" ran on a null Thing ' .  $thing->uuid .  '</pre>');
            $this->thing_report['info'] = 'Tried to run Web on a null Thing.';
            $this->thing_report['help'] = "That isn't going to work";

            return $this->thing_report;


            // exit();

        }




        $this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->agent_name = 'link';
        $this->agent_version = 'redpanda';

        //  $this->thing_report = array('thing' => $this->thing->thing);

        $this->thing_report['thing'] = $this->thing->thing;

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
        // I think.
        // Instead.

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->node_list = array('start a'=>
            array('web default 1'),
            'start b'=>array('web default 2')
        );

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        $this->sqlresponse = null;

        $this->thing->log ( '<pre> Agent "Link" running on Thing ' .  $this->thing->nuuid . '.</pre>' );
        $this->thing->log ( '<pre> Agent "Link" received this Thing "' .  $this->subject .  '".</pre>' );



        $this->getLink();
        // If readSubject is true then it has been responded to.

        $this->readSubject();
        $this->respond(); // Return $this->thing_report;


        $this->thing->log( '<pre> Agent "Link" ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) .'ms.' );

        $this->thing_report['log'] = $this->thing->log;

        return;
    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    public function extractLinks($text = null) {


        preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $text, $match);
        $this->links = $match[0];
        return $this->links;

    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    public function extractLink($text = null) {

        $m = $this->extractLinks($text);
if (!isset($m[0])) {return null;}
        $this->link = $m[0];
        return $m[0];

    }


    /**
     *
     * @return unknown
     */
    function getLink() {

        $block_things = array();
        // See if a block record exists.
        $findagent_thing = new Findagent($this->thing, 'thing');

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

        //$this->thing->log('Agent "Block" found ' . count($findagent_thing->thing_report['things']) ." Block Things.");

        $this->max_index =0;

        $match = 0;

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {

            $this->thing->log($block_thing['task'] . " " . $block_thing['nom_to'] . " " . $block_thing['nom_from']);



            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing['uuid'];
                if ($match == 2) {break;}
            }
        }
        return $this->link_uuid;

    }


    /**
     *
     * @return unknown
     */
    public function respond() {

        // Thing actions
        $this->sms_message = "LINK | " . $this->web_prefix . "thing/" . $this->link_uuid ."/agent";

        $this->thing_report['sms'] = $this->sms_message;


        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("link",
                "received_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
        );

        $this->thing->flagGreen();

        $this->thing->account['thing']->Credit(25);
        $this->thing->account['stack']->Debit(25);

        $choices = false;
        $this->thing_report['choices'] = $choices;

        $this->thing_report['info'] = 'This is the link agent.';
        $this->thing_report['help'] = 'This gets the http address of the last request.';

        $this->thing->log ( '<pre> Agent "Link" credited 25 to the Thing account.  Balance is now ' .  $this->thing->account['thing']->balance['amount'] . '</pre>');



        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;


        return $this->thing_report;
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {

        //$this->defaultButtons();

        $status = true;
        return $status;
    }


    /**
     *
     */
    function defaultButtons() {

        if (rand(0, 5) <= 3) {
            $this->thing->choice->Create('link', $this->node_list, 'start a');
        } else {
            $this->thing->choice->Create('link', $this->node_list, 'start b');
        }

        //$this->thing->choice->Choose("inside nest");
        $this->thing->flagGreen();

        return;
    }

}
