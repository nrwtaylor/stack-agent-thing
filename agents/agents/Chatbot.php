<?php
/**
 * Chatbot.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Chatbot extends Agent
{

    public $var = 'hello';

    /**
     *
     */
    public function run() {
        $this->getChatbot();
    }


    /**
     *
     */
    public function get() {

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("chatbot", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("chatbot", "refreshed_at"), $time_string );
        }

        $this->refreshed_at = strtotime($time_string);


        $this->thing->json->setField("variables");
        $this->name = strtolower($this->thing->json->readVariable( array("chatbot", "name") ));
/*
        if ( ($this->name == false) ) {

            $this->readSubject();

            $this->thing->json->writeVariable( array("chatbot", "name"), $this->name );


            $this->thing->log($this->agent_prefix . ' completed read.', "OPTIMIZE") ;
        }
*/
    }

    public function set() {

        if ( ($this->name == false) ) {
            $this->thing->json->writeVariable( array("chatbot", "name"), $this->name );
            $this->thing->log($this->agent_prefix . ' completed read.', "OPTIMIZE") ;
        }

    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function extractChatbots($input = null) {

        if (!isset($this->chatbot_names)) {
            $this->chatbot_names = array();
        }

        if (!isset($this->chatbots)) {$this->getChatbots();}



        foreach ($this->chatbots as $index=>$chatbot) {
            $chatbot_name = strtolower($chatbot['name']);

            if (empty($chatbot_name)) {continue;}

            if (strpos($input, $chatbot_name) !== false) {
                $this->chatbot_names[] = $chatbot_name;
            }

        }
        $this->chatbot_names = array_unique($this->chatbot_names);

        return array($this->chatbot_names);
    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function filterChatbots($input = null) {

        if (!isset($this->chatbot_names)) {
            return;
        }

        if (!isset($this->chatbots)) {$this->getChatbots();}

        $this->chatbot_aliases = $this->chatbot_names;

        foreach ($this->chatbot_aliases as $chatbot_alias) {

            $this->chatbot_aliases[] = '@'.$chatbot_alias;
        }

        usort($this->chatbot_aliases, function($a, $b) {
                return strlen($b) <=> strlen($a);
            });

        $this->filtered_input = trim(str_replace($this->chatbot_aliases, '' , $input));

        return $this->filtered_input;
    }


    /**
     *
     * @param unknown $selector (optional)
     * @return unknown
     */
    function getChatbot($selector = null) {
        foreach ($this->chatbots as $index=>$chatbot) {
            // Match the first matching place

            if (($selector == null) or ($selector == "")) {

                if (!isset($this->last_refreshed_at)) {$this->last_refreshed_at = $this->thing->time();}
                if (!isset($this->last_chatbot_name)) {$this->last_chatbot_name = $this->default_chatbot_name;}

                $this->refreshed_at = $this->last_refreshed_at; // This is resetting the count.
                $this->chatbot_name = $this->last_chatbot_name;

                $this->chatbot_variables = new Variables($this->thing, "variables " . $this->chatbot_name . " " . $this->from);
                return array($this->chatbot_name);
            }

            if ($chatbot['name'] == $selector) {
                $this->refreshed_at = $chatbot['refreshed_at'];
                $this->place_name = $chatbot['name'];
                $this->chatbot_name = new Variables($this->thing, "variables " . $this->chatbot_name . " " . $this->from);
                return array($this->chatbot_name);
            }
        }
        return true;
    }


    /**
     *
     */
    function init() {

        $this->default_chatbot_name = "X";
        if (isset($this->thing->container['api']['chatbot']['name'])) {
            $this->default_chatbot_name = $this->thing->container['api']['chatbot']['name'];
        }
        $this->thing_report["info"] = "This recognizes stack chatbot names.";
        $this->thing_report["help"] = 'Try EDNA.';


    }


    /**
     *
     * @return unknown
     */
    public function respond() {

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        return $this->thing_report;
    }


    /**
     *
     */
    public function makeChoices() {
        $this->thing->choice->Create($this->agent_name, $this->node_list, "chatbot");
        $this->choices = $this->thing->choice->makeLinks('chatbot');

        $this->thing_report['choices'] = $this->choices;
    }


    /**
     *
     */
    public function makeSMS() {
        $sms = "CHATBOT\n";

        $sms .= $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }


    /**
     *
     */
    function getChatbots() {
        $this->chatbots = array();
        // Load in the cast. And roles.
        $file = $this->resource_path .'/chatbot/chatbot.txt';

        if (!file_exists($file)) {return true;}

        $contents = @file_get_contents($file);

        if ($contents === false) {$this->response .= "No chatbot list found. "; return true;}

        $handle = fopen($file, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {

                $person_name = $line;
                $arr = explode(",", $line);
                $name= trim($arr[0]);
                if (isset($arr[1])) {$role = trim($arr[1]);} else {$role = "X";}

                // Unique name <> Role mappings. Check?
                $this->name_list[$role] = $name;
                $this->role_list[$name] = $role;

                //$this->placename_list[] = $place_name;
                $this->chatbots[] = array("name"=>$name, "role"=>$role);
            }

            fclose($handle);
        } else {
            // error opening the file.
        }
    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function isChatbot($text = null) {

        $selector = $text;
        foreach ($this->chatbots as $index=>$chatbot) {
            // Match the first matching place

            if ($chatbot['name'] == $selector) {
                return true;
            }
            if ("@".$chatbot['name'] == $selector) {
                return true;
            }

        }
        return false;
    }



    /**
     *
     */
    public function makeWeb() {

        if (!isset($this->chatbots)) {$this->getChatbots();}

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/chatbot';

        $this->node_list = array("chatbot"=>array("chatbot"));

        $web = "<b>Chatbot Agent</b>";

        $web .= "<p>";
/*
        // List recognized chatbots
        foreach ($this->chatbots as $i=>$chatbot) {
            $web .= implode(" " , $chatbot) . '<br>';
        }
*/
        $web .= "OK.";
        $this->thing_report['web'] = $web;

    }


    /**
     *
     */
    public function readSubject() {

        $input = strtolower($this->subject);

        $this->extractChatbots($input);
        $this->filterChatbots($input);


        if ($this->agent_input == "extract") {return;}

        $pieces = explode(" ", strtolower($input));

        $this->getChatbot();

        if (count($pieces) == 1) {

            if ($input == 'chatbot') {
                //$this->getChatbot();
                $this->response .= "OK. ";
                //                if ((!isset($this->index)) or
                //                    ($this->index == null)) {
                //                    $this->index = 1;
                //                }
                return;
            }
        }

        $keywords = array("chatbot");
        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {

                    case 'chatbot':
                        $this->response .= "Saw the word chatbot. ";
                        //$this->getChatbot();

                        return;

                    case 'on':
                        //$this->setFlag('green');
                        //break;


                    default:
                    }
                }
            }
        }

        //$this->getChatbot();
        $this->response .= "OK. ";
    }


}
