<?php
/**
 * Orac.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Litany extends Agent
{
    public $var = 'hello';

    /**
     *
     */
    function init()
    {
        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }

        $this->num_hits = 0;

        // Allow for a new state tree to be introduced here.
        $this->node_list = ["start" => ["useful", "useful?"]];
    }

    /**
     *
     */
    function run()
    {
        $this->text = "";
        $this->getLitany($this->librex_name);
        $this->doLitany();
    }

    /**
     *
     * @param unknown $librex
     * @param unknown $searchfor
     * @return unknown
     */
    public function getLitany($librex_name)
    {
        // Look up the meaning in the dictionary.
        if ($librex_name == "" or $librex_name == " " or $librex_name == null) {
            return false;
        }

        switch ($librex) {
            default:
                $file =
                    $this->resource_path .
                    $librex_name .
                    '/' .
                    $librex_name .
                    '.txt';
        }

        $contents = @file_get_contents($file);
        // devstack add \b to Word

        if (!isset($this->resource)) {$this->resource = "";}
        if ($contents === false) {$this->resource .= "Resource " . $librex_name . " not available. ";
            $this->litany = null;
            return $this->litany;
        }

        $this->litany = explode('\r\n', $contents);
        return $this->litany;
    }

    /**
     *
     * @param unknown $type (optional)
     * @return unknown
     */
    public function doLitany($type = null)
    {
        if (!isset($this->litany)) {
            $this->response .= "No litany retrieved. ";
            return true;
        }
        //$this->findOrac("orac", "orac");

        $key = array_rand($this->litany);
        $value = $this->litany[$key];

        $this->message = $value;
        $this->sms_message = $value;

        $time_string = $this->thing->time();
        $this->thing->Write(
            ["litany", "refreshed_at"],
            $time_string
        );

        return $this->message;
    }

    public function makeMessage()
    {
        if (!isset($this->message)) {
            $this->message = "No message found.";
        }
    }
    // -----------------------

    /**
     *
     * @return unknown
     */
    public function respond()
    {
        // Thing actions
        $this->thing->flagGreen();

        // Generate email response.
        $to = $this->thing->from;
        $from = "orac";

        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "start"
        );
        $choices = $this->thing->choice->makeLinks('start');
        $this->thing_report['choices'] = $choices;

        $this->sms_message = "LITANY | " . $this->sms_message . " | REPLY HELP";
        $this->thing_report['sms'] = $this->sms_message;

        $this->thing_report['email'] = $this->message;
        $this->thing_report['message'] = $this->message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        $this->thing_report['help'] = "This is Blake 7's robot.";

        return $this->thing_report;
    }

    /**
     *
     * @return unknown
     */
    public function test()
    {
        $this->test = false; // good
        return "green";
    }

    /**
     *
     */
    public function readSubject()
    {
        $text = $this->assert($this->input);
        $this->librex_name = $text;
        $this->response = null;
        return;
    }
}
