<?php
namespace Nrwtaylor\StackAgentThing;

class Test extends Agent
{
    public $var = 'hello';

    public function set()
    {
        //   $this->response = true;
    }

    function init()
    {
        $this->thing_report["info"] =
            "Tests Agent behaviour.";
        $this->thing_report["help"] = "This is about testing an agent response.";
    }

    function randomTest($input = null)
    {
        $file =
            $GLOBALS['stack_path'] .
            'vendor/nrwtaylor/stack-agent-thing/agents';

        $files = scandir($file);

        foreach ($files as $i => $file) {
            if (substr($file, -4) != ".php") {
                unset($files[$i]);
                continue;
            }

            if (substr($file, 0, 1) == "_") {
                unset($files[$i]);
                continue;
            }

            if (strtolower(substr($file, 0, 1)) == substr($file, 0, 1)) {
                unset($files[$i]);
                continue;
            }
        }

        $file = $files[array_rand($files)];

        $tokens = explode(".", $file);
        $agent_name = $tokens[0];

        $this->response .= "Agent tested: " . $agent_name . ". ";

        //if (rand(0,1) == 1) {

        $agent = $this->getAgent($agent_name); // Push agent response.
        //} else {
        //$agent = $this->getAgent($agent_name, strtolower($agent_name)); //  Do not push response.
        //}
        if ($agent === true) {
            $this->response .= "Test response: TRUE. ";
            return;
        }

        if ($agent === false) {
            $this->response .= "Test response: FALSE. ";
            return;
        }

$sms = "TEST | No SMS response.";
if (isset($agent->thing_report['sms'])) {
        $sms = $agent->thing_report['sms'];
}

$response = "No response.";
if (isset($agent->thing_report['response'])) {
        $response = $agent->thing_report['response'];
}
        //$this->callAgent($agent_name);
        $this->response .= "Test response: " . $sms . " " . $response . " / ";
    }

    function resultTest()
    {
        if ($this->value == $this->expected_value) {
            echo "Pass  \n";
        } else {
            echo "Fail  \n";
            echo 'returned $value1';
            print_r($this->value);
            echo '\n';
            echo '$expected_response: ';
            print_r($this->expected_value);
            echo '\n';
        }
    }

    function expectedTest($expected_value = null)
    {
        $this->expected_value = $expected_value;
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "test");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];
    }

    function run()
    {
        //$this->test();
    }

    function makeSMS()
    {
        $this->node_list = ["test" => ["stay", "go", "game"]];
        $this->sms_message = "TEST | " . $this->response;
        //if ($this->negative_time < 0) {
        //    $this->sms_message .= " " .$this->thing->human_time($this->negative_time/-1) . ".";
        //}
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        //        $this->thing->choice->Create('channel', $this->node_list, "test");
        //        $choices = $this->thing->choice->makeLinks('test');
        //        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        $input = $this->input;

        if ($input == "test") {
            $this->randomTest();
            $this->response .= "Ran a random test. ";
            return;
        }
    }
}
