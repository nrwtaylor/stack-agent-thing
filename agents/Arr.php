<?php
namespace Nrwtaylor\StackAgentThing;

// dev not tested
// Array is a reserved PHP name

class Arr extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    function run()
    {
        $this->doArr();
    }

    public function doArr()
    {
        if ($this->agent_input == null) {
            $array = ["miao", "miaou", "hiss", "prrr", "grrr"];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "ARRAY | " . strtolower($v) . ".";

            $this->html_message = $response; // mewsage?
        } else {
            $this->html_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This agent handles HTML.";
        $this->thing_report["help"] = "This is about recognizing and processing HTML.";

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeSMS()
    {
        $this->node_list = ["array" => ["array"]];
        $this->sms_message = "" . $this->html_message;
        $this->thing_report["sms"] = $this->sms_message;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report["choices"] = $choices;
    }

    function textArr($arr)
    {
        $flattened_array = $this->flattenArray($arr);
$text = implode(" ", $flattened_array);
        return $text;
    }

// https://stackoverflow.com/questions/262891/is-there-a-way-to-find-out-how-deep-a-php-array-is
public function depthArr($array) {
    $max_indentation = 1;

    $array_str = print_r($array, true);
    $lines = explode("\n", $array_str);

    foreach ($lines as $line) {
        $indentation = (strlen($line) - strlen(ltrim($line))) / 4;

        if ($indentation > $max_indentation) {
            $max_indentation = $indentation;
        }
    }

    return ceil(($max_indentation - 1) / 2) + 1;
}

    /**
     *
     * @param array   $array
     * @return unknown
     */
    function flattenArr(array $array)
    {
        $flat = []; // initialize return array
        $stack = array_values($array); // initialize stack
        while ($stack) {
            // process stack until done
            $value = array_shift($stack);
            if (is_array($value)) {
                // a value to further process
                $stack = array_merge(array_values($value), $stack);
            }
            // a value to take
            else {
                $flat[] = $value;
            }
        }
        return $flat;
    }


    public function readSubject()
    {
        return false;
    }
}
