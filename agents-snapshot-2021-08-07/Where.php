<?php
/**
 * Where.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Where extends Agent
{
    public $var = 'hello';


    /**
     *
     */
    function init() {

        if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

        $this->email = $this->thing->container['stack']['email'];
        $this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid . '.');

        $this->current_time = $this->thing->time();
        $this->verbosity = 9;

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->thing_report['help'] = 'Text WHERE.';
        $this->thing_report['info'] = 'An agent to address where related questions.';

        $this->sqlresponse = null;

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $this->thing->container['stack']['web_prefix'];
        $this->mail_postfix = $this->thing->container['stack']['mail_postfix'];
        $this->word = $this->thing->container['stack']['word'];
        $this->email = $this->thing->container['stack']['email'];
        $this->nominal = $this->thing->container['stack']['nominal'];
        $this->mail_regulatory = $this->thing->container['stack']['mail_regulatory'];

        $this->entity_name = $this->thing->container['stack']['entity_name'];

        // Allow for a new state tree to be introduced here.
        $this->node_list = array("where"=>array("privacy", "weather"));

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );
        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());

        $this->thing_report['log'] = $this->thing->log;
        $this->thing_report['response'] = $this->response;

    }


    /**
     *
     */
    public function where() {
    }


    /**
     *
     */
    function get() {
        $this->getWhere();
    }


    /**
     *
     */
    function getWhere() {
        $file = $this->resource_path .'/where/where.txt';
        $contents = file_get_contents($file);

        $handle = fopen($file, "r");

        $channels = array("sms", "email", "snippet", "han", "word", "slug");
        $channel = "null";
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $text = trim(str_replace(array('#', '[', ']'), '', $line));
                if (in_array($text, $channels)) {
                    $channel = $text;
                    continue;
                }

                if (!isset($this->thing_report[$channel])) {$this->thing_report[$channel] = "";}
                $this->thing_report[$channel] .= $line;

            }

            fclose($handle);
        } else {
            // error opening the file.
        }
    }



    /**
     *
     * @return unknown
     */
    public function respond() {


        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $thing_report['info'] = $message_thing->thing_report['info'] ;
        }


    }


    /**
     *
     */
    public function makeHan() {
        $this->han = '哪里';
        $this->thing_report['han'] = $this->han;
    }


    /**
     *
     */
    public function makeSnippet() {

        $snippet = "where";
        if (isset($this->thing_report['snippet'])) {
            $snippet = $this->thing_report['snippet'];
        }
        $this->snippet = '<div class="thing snippet">' . $snippet .  '</div>';
        $this->thing_report['snippet'] = $snippet;
    }


    /**
     *
     */
    public function makeMessage() {


    }


    /**
     *
     */
    public function makeSMS() {
        if (!isset($this->sms_message)) {
            $text = "No response.";
            if (isset($this->thing_report['sms'])) {
                $text = $this->thing_report['sms'];
            }
            $this->sms_message = "WHERE | " . $text;

        }
        $this->thing_report['sms'] = $this->sms_message;
    }


    /**
     *
     */
    public function makeChoice() {
        $this->thing->choice->Create($this->agent_name, $this->node_list, "where");
        $choices = $this->thing->choice->makeLinks('where');
        $this->thing_report['choices'] = $choices;
    }


    /**
     *
     * @param unknown $phrase
     */
    private function nextWord($phrase) {


    }


    /**
     *
     */
    public function readSubject() {

        $this->response = null;

        $keywords = array('?');
        $input = strtolower($this->subject);
        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {

            $input = $this->subject;
            if ($input == "where") {
                $this->response = "Single word where received";
                $this->thing->log('got a single keyword.');
                $this->where();
                return;
            }

            $this->where();
            $this->response = "Provided where details. ";
            return;
        }
        $this->response = true;
    }


}
