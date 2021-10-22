<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


class Memory extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->initMemory();
    }

    public function initMemory()
    {
if (isset($this->agent_input) and $this->agent_input == 'fallback memory') {
return;}
        if (!isset($this->memory)) {
            try {
                $this->memory = new \Memcached(); //point 2.
                $this->memory->addServer("127.0.0.1", 11211);
            } catch (\Throwable $t) {
                //$this->response .= "Could not access memory. ";
                // Failto

//$thing = $this->thing;
//if ($this->thing == null) {
//$thing = new Thing(null);
//}
//                $this->memory = new Memory($this->thing, "fallback memory");


//$this->memory = new Memory($thing, "merp");
                //restore_error_handler();
//                $this->thing->log(
//                    "caught memcached throwable. made memory",
//                    "WARNING"
//                );
                return;
            } catch (\Error $ex) {
//                $this->thing->log("caught memcached error.", "WARNING");
                return true;
            }
        }
    }

    function run()
    {
        $this->doMemory();
    }

    public function doMemory()
    {
        if ($this->agent_input == null) {
            $array = ["miao", "miaou", "hiss", "prrr", "grrr"];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "MEMORY | " . strtolower($v) . ".";

            $this->memory_message = $response; // mewsage?
        } else {
            $this->memory_message = $this->agent_input;
        }
    }

    public function getMemory($text = null)
    {

if (!isset($this->memory)) {return null;}
        // Null?
        // $this->mem_cached = null;
        // Fail to stack php memory code if Memcached is not availble.
        $memory = $this->memory->get($text);
        return $memory;
    }

    public function createMemory($subject, $to) {
        return $this->setMemory(null,['from'=>$to,'to'=>'merp','task'=>'subject']);
    }

    public function setMemory($text = null, $variable = null)
    {
        // Stack rule.
        // You can not create a specific uuid on the stack.
        // If a uuid key is provided, check if it exists.
        if ($this->isUuid($text)) {
            $m = $this->getMemory($text);

            if ($m === false) {
                return true;
            }
        }

        // Create random uuid key if none provided.

        if ($text === null) {
            $text = $this->thing->getUuid();
        }
if (!isset($this->memory)) {
return true;
}

        $memory = $this->memory->set($text, $variable);
        return $memory;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a memory keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = ["memory" => ["memory"]];
        $this->sms_message = "" . $this->memory_message;
        $this->thing_report["sms"] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create("channel", $this->node_list, "memory");
        $choices = $this->thing->choice->makeLinks("memory");
        $this->thing_report["choices"] = $choices;
    }

    public function readSubject()
    {
    }
}
