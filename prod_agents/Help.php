<?php
/**
 * Help.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Help extends Agent
{
    /**
     *
     * @param Thing   $thing
     * @return unknown
     */
    function init()
    {
        if ($this->thing != true) {
            $this->thing->log(
                '<pre> Agent "Help" ran on a null Thing ' .
                    $thing->uuid .
                    '</pre>'
            );
            $this->thing_report['info'] = 'Tried to run Help on a null Thing.';
            $this->thing_report['help'] = "That isn't going to work";

            return $this->thing_report;
        }

        //        $this->agent_name = 'help';
        $this->agent_version = 'redpanda';

        $this->node_list = ['help' => ['help', 'info']];

        $this->namespace = "\\Nrwtaylor\\StackAgentThing\\";

	$this->default_agent_name = "agent";
    }

    function run()
    {
        $this->getLink();
        $this->getHelp();
    }

    /**
     *
     */
    public function getHelp()
    {
        if (!isset($this->prior_agent)) {
            $this->help = "Did not get prior agent.";
            return;
        }

        if (strtolower($this->prior_agent) == "help") {
            $this->help = "Asks the previous Agent for help.";
            return;
        }

        try {
            $this->thing->log(
                $this->agent_prefix .
                    'trying Agent "' .
                    $this->prior_agent .
                    '".',
                "INFORMATION"
            );
            $agent_class_name =
                $this->namespace . ucwords(strtolower($this->prior_agent));

            $agent = new $agent_class_name($this->prior_thing);
            if (isset($agent->thing_report['help'])) {
                $this->help = $agent->thing_report['help'];
            }

        } catch (\Error $ex) {
            // Error is the base class for all internal PHP error exceptions.
            $this->thing->log(
                $this->agent_prefix . 'borked on "' . $agent_class_name . '".',
                "WARNING"
            );
            $message = $ex->getMessage();
            //$code = $ex->getCode();
            $file = $ex->getFile();
            $line = $ex->getLine();

            $input = $message . '  ' . $file . ' line:' . $line;

            // This is an error in the Place, so Bork and move onto the next context.
            $bork_agent = new Bork($this->thing, $input);
            //            $thing_report['help'] = $bork_agent->thing_report['help'] . " " . $input;

            $this->help = "Could not retrieve help for that agent.";
            //continue;
        }
    }

    public function makeHelp()
    {
        $help = "No help available.";
        if (isset($this->help)) {
            $help = $this->help;
        }

        if (isset($this->thing_report['help'])) {
            return;
        }

        $this->help = $help;
        $this->thing_report['help'] = $help;
    }

    /**
     *
     */
    public function makeSMS()
    {
        if (!isset($this->help)) {$this->makeHelp();}

	$prior_agent_text = $this->default_agent_name;
	if (isset($this->prior_agent)) {$prior_agent_text = $this->prior_agent;}

        $this->sms_message =
            "HELP | " . ucwords($prior_agent_text) . " | " . $this->help;
        //$this->sms_message .= " | TEXT INFO";
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function set()
    {
        $this->thing->Write(
            ["help", "received_at"],
            gmdate("Y-m-d\TH:i:s\Z", time())
        );
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->makeChoices();

        $this->thing->flagGreen();

        $this->thing_report['info'] = 'This is the help agent.';
        $this->thing_report['help'] =
            'This agent takes a Thing and runs the Help agent on it.';

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    /**
     *
     * @return unknown
     */
    public function getLink($text = null)
    {
        $block_things = [];
        // See if a block record exists.
        $findagent_thing = new Findagent($this->thing, 'thing');

	$things = $findagent_thing->thing_report['things'];

        if ($things === true) {return;}

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

        $this->max_index = 0;

        $match = 0;

        foreach ($things as $block_thing) {
            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing['uuid'];
                if ($match == 2) {
                    break;
                }
            }
        }

        // TODO refactor without new Thing call.

        $previous_thing = new Thing($block_thing['uuid']);
        $this->prior_thing = $previous_thing;
        if (!isset($previous_thing->json->array_data['message']['agent'])) {
            $this->prior_agent = "help";
        } else {
            $this->prior_agent =
                $previous_thing->json->array_data['message']['agent'];
        }

        return $this->link_uuid;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
    }

    /**
     *
     */
    function makeChoices()
    {
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "help"
        );
        $choices = $this->thing->choice->makeLinks('help');

        $this->thing_report['choices'] = $choices;
    }

    /**
     *
     */
    function makeWeb()
    {
        $link = $this->web_prefix . 'web/' . $this->uuid . '/thing';

        $this->node_list = ["web" => ["iching", "roll"]];

        $web = "";

        //        $web = '<a href="' . $link . '">';
        //        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->link_uuid . '/receipt.png">';
        //        $web .= "</a>";

        //        $web .= "<br>";

        $prior_agent_text = $this->default_agent_name;
        if (isset($this->prior_agent)) {$prior_agent_text = $this->prior_agent;}


        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';
        $web .=
            'The last agent to run was the ' .
            ucwords($prior_agent_text) .
            ' Agent.<br>';

        $web .= "<br>";

        $web .= $this->help . "<br>";

        $this->thing_report['web'] = $web;
    }
}
