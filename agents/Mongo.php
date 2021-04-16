<?php
namespace Nrwtaylor\StackAgentThing;

class Memory extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doMemory();
    }

    public function doMemory()
    {
        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "MEMORY | " . strtolower($v) . ".";

            $this->memory_message = $response; // mewsage?
        } else {
            $this->memory_message = $this->agent_input;
        }
    }

    // Plan to deprecate getMemcached terminology.
    public function getMemory($text = null)
    {
        // Null?
        // $this->mem_cached = null;
        // Fail to stack php memory code if Memcached is not availble.
        if (!isset($this->memory)) {
            try {
                $this->memory = new \Memcached(); //point 2.
                $this->memory->addServer("127.0.0.1", 11211);
            } catch (\Throwable $t) {
                // Failto
                $this->memory = new Memory($this->thing, "memory");
                //restore_error_handler();
                $this->thing->log(
                    "caught memcached throwable. made memory",
                    "WARNING"
                );
                return;
            } catch (\Error $ex) {
                $this->thing->log("caught memcached error.", "WARNING");
                return true;
            }
        }

        $memory = $this->memory->get($text);
        return $memory;
    }

    // Plan to deprecate getMemcached terminology.
    public function setMemory($text = null, $variable = null)
    {
        if (!isset($this->memory)) {
            try {
                $this->memory = new \Memcached(); //point 2.
                $this->memory->addServer("127.0.0.1", 11211);
            } catch (\Throwable $t) {
                // Failto
                $this->memory = new Memory($this->thing, "memory");
                //restore_error_handler();
                $this->thing->log(
                    "caught memcached throwable. made memory",
                    "WARNING"
                );
                return;
            } catch (\Error $ex) {
                $this->thing->log("caught memcached error.", "WARNING");
                return true;
            }
        }

        $memory = $this->memory->set($text, $variable);
        return $memory;
    }


    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "memory");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a memory keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = array("memory" => array("memory"));
        $this->sms_message = "" . $this->memory_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "memory");
        $choices = $this->thing->choice->makeLinks('memory');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
