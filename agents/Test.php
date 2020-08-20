<?php
namespace Nrwtaylor\StackAgentThing;

// devstack explore
//use PHPUnit\Framework\TestCase;

class Test extends Agent
{
    public $var = 'hello';

    public function set()
    {
        $this->thing->json->writeVariable(
            ["test", "refreshed_at"],
            $this->current_time
        );

        $this->thing->json->writeVariable(
            ["test", "response"],
            $this->response
        );

        if (isset($this->test_text)) {
            $this->thing->json->writeVariable(
                ["test", "text"],
                $this->test_text
            );
        }

        //   $this->response = true;
    }

    function init()
    {
        ob_start();
        $this->thing_report["info"] = "Tests Agent behaviour.";
        $this->thing_report["help"] =
            "This is about testing an agent response.";
    }

    public function test($text = null)
    {
        if ($text == null) {
            return false;
        }

        $this->agentsTest();

        if (isset($this->agents[strtolower($text)])) {
            $agent_name = $this->agents[strtolower($text)];
            $this->test_text = $agent_name;

            $this->response .= "Test text is " . $this->test_text . ". ";

            //     $stub = $this->getMockBuilder($this->test_text)->disableOriginalConstructor()->getMock();
            //      $stub->method("init")->willReturn(11);
            // Calling $stub->doSomething() will now return
            // 'foo'.
            //        $this->assertEquals('foo', $stub->init());
set_time_limit(20);

$thing = new Thing(null);
var_dump($this->from);
var_dump($agent_name);
$thing->Create($this->from, "agent", $agent_name);
$agent = new Agent($thing);

//var_dump($a->thing_report['sms']);
//var_dump($a->thing_report['response']);

//$agent = new \stdClass();
//$agent->response = $a->thing_report['response'];
//$agent->response = $a->thing_report['sms']

//            $agent = $this->getAgent($agent_name); // Push agent response.

            if (isset($agent->thing_report['response'])) {
                $this->response .=
                    "Got " .
                    $agent_name .
                    " response. " .
                    trim($agent->thing_report['response']) .
                    " ";
            } else {
                $this->response .=
                    "Did not get a " . $agent_name . " response. ";
            }
        } else {
            // Either
            //$agent = new Agent($this->thing,"agent");
            $this->test_text = 'agent';
            $agent = $this->getAgent('agent', $text);
            $this->response .=
                "Tested agent response. " . trim($agent->thing_report['response']) . " ";

            // Neither is providing a thing_report.
        }

        if ($agent === true) {
            $this->response .= "Test response: TRUE. ";
            return;
        }

        if ($agent === false) {
            $this->response .= "Test response: FALSE. ";
            return;
        }

        if (!method_exists($agent, 'test')) {
            $this->response .= 'No agent self-test found. ';
        } else {
            try {
                $agent->test();
            } catch (\Throwable $e) {
                $this->response .= 'Threw: ' . $e->getMessage() . ". ";
            }
        }

        $sms = 'No SMS.';
        if (isset($agent->thing_report['sms'])) {
            $sms = "Test SMS message: " . $agent->thing_report['sms'];
        }
        $this->response .= $sms . " / ";

        $response = "No response.";
        if (
            isset($agent->thing_report['response']) and
            $agent->thing_report['response'] != false
        ) {
            $response = "Test response: " . $agent->thing_report['response'];
        }
        $this->response .= $response . " / ";
    }

    public function get()
    {
    }

    public function getTests()
    {
        $things = $this->getThings('test');

        $this->tests = $things;
        //$this->loadTests();
    }

    public function agentsTest()
    {
        // Get list of keywords.

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

            $tokens = explode(".", $file);
            $agent_name = $tokens[0];
            $this->agents[strtolower($agent_name)] = $agent_name;
        }

        // Get tests from text file.
        $this->loadTests();

        // Filter list.
        $not_allowed = ["stop", "forgetall"];
        foreach ($agents as $agent_name => $test_string) {
            if (in_array(strtolower($agent_name), $not_allowed)) {
                unset($agents[$agent_name]);
            }
        }

    }

    function randomTest($input = null)
    {
        $this->agentsTest();
        $files = $this->agents;
        //var_dump($files);
        $file = $files[array_rand($files)];

        $tokens = explode(".", $file);
        $agent_name = $tokens[0];

        $this->response .= "Agent tested: " . $agent_name . ". ";
        $this->test($agent_name);
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
        $text = ob_get_contents();

        if ($text == "") {
            return;
        }

        if (is_string($text)) {
            $this->response .= "Output buffer seen. " . $text . ". ";
        }
    }

    public function loadTests()
    {
        $file = $this->resource_path . 'test/tests.txt';
        $contents = file_get_contents($file);

        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {
            $this->agents[strtolower($line)] = $line;

            // do something with $line
            $line = strtok($separator);
        }
    }

    public function makeWeb()
    {
        if (!isset($this->tests)) {
            $this->getTests();
        }

        $web = "";
        foreach ($this->tests as $uuid => $thing) {
            // devstack.
            $test = $thing->variables['test'];
            //$test = $thing['variables']['test'];

            if (!isset($test['text'])) {
                continue;
            }
            if (!isset($test['response'])) {
                continue;
            }
            if (!isset($test['refreshed_at'])) {
                continue;
            }

            $text =
                $test['refreshed_at'] .
                " " .
                $test['text'] .
                " " .
                $test['response'];
            $web .= $text . "<br>";
        }
        $this->thing_report['web'] = $web;
    }

    function makeSMS()
    {
        $this->node_list = ["test" => ["stay", "go", "game"]];
        $this->sms_message = "TEST | " . $this->response;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
    }

    public function readSubject()
    {
        $input = $this->input;
        if ($input == "test") {
            $this->randomTest();
            $this->response .= "Ran a random test. ";
            return;
        }

        if ($input == "tests") {
            $this->getTests();
            $this->response .= "Got tests. ";
            return;
        }

        $asserted_input = $this->assert($input);
        $this->test($asserted_input);
    }
}
