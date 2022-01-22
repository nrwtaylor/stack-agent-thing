<?php
namespace Nrwtaylor\StackAgentThing;

class Googledocs extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->googledocs_horizon = 60;

        if (!isset($this->thing->at_agent)) {
            $this->thing->at_agent = new At($this->thing, "at");
        }
        $this->test_url = null;
        if (isset($this->thing->container['api']['googledocs']['test_url'])) {
            $this->test_url =
                $this->thing->container['api']['googledocs']['test_url'];
        }

$this->test_url = 'https://docs.google.com/document/d/1H-PTthPJqzGeEw9erFtVy22eOu1TF6RsYm5BQfhBJ0o/edit?usp=sharing';
$this->test_url = 'https://docs.google.com/document/d/1H-PTthPJqzGeEw9erFtVy22eOu1TF6RsYm5BQfhBJ0o';

    }

    function run()
    {
        $this->doGoogledocs();
    }

    public function get()
    {
    }

    public function test()
    {
        $this->thing->console("Googledocs test start.\n");

        if (!is_string($this->test_url)) {
            $this->thing->console("Test URL not found.\n");
            return false;
        }

        $url = $this->test_url;

        $contents = $this->urlGoogledocs($url);
        $this->contents = $contents;

        $paragraphs = $this->paragraphsGoogledocs($contents);
        $this->thing->console("Retrieved test contents.\n");

        $arr = ['year', 'month', 'day', 'day_number', 'hour', 'minute'];

        foreach ($paragraphs as $i => $paragraph) {
echo ".";
            if (trim($paragraph) == "") {
                continue;
            }
            $googledocs = $this->extractGoogledocs($paragraph);
            if ($googledocs == false) {
                continue;
            }
            //$this->thing->log($googledocs['googledocs'] . "\n" . $googledocs['line']);
            $this->thing->console($googledocs['googledocs'] . "\n");
            $this->thing->console($googledocs['line'] . "\n");
            $this->thing->console($this->timestampGoogledocs($googledocs) . "\n");
            $this->thing->console("\n");
//echo $googledocs['line'] . "n";
        }

        $this->response .= "Read " . $i . " paragraphs. ";

        $this->thing->console("Googledocs test completed.\n");
    }

    public function urlGoogledocs($url)
    {
        $this->thing->console("urlGoogledocs read start.\n");

        $start_time = time();

        $read_agent = new Read($this->thing, $url);
        $contents = $read_agent->contents;

        // dev refactor
        //       $contents = $this->urlRead($url);

        $run_time = time() - $start_time;

        $this->response .=
            "Googledocs source took " . $run_time . " seconds to get. ";

        $this->thing->console("urlGoogledocs read complete.\n");

        return $contents;
    }

    public function paragraphsGoogledocs($contents)
    {
        $paragraphs = $this->extractParagraphs($contents);
        return $paragraphs;
    }

    public function getGoogledocs($text = null)
    {
        if ($text == null) {
            return true;
        }
        //        if (!is_string($this->test_url)) {
        //            return false;
        //        }

        // Read the specificed url. And get the first googledocs.
        // Googledocs being  timestamp + text.
        // Time this part/

        if (!isset($this->paragraphs)) {
            $this->paragraphs = $this->paragraphsGoogledocs($text);
        }
        $start_time = time();

        $arr = ['year', 'month', 'day', 'day_number', 'hour', 'minute'];

        foreach ($this->paragraphs as $i => $paragraph) {
echo "line " . $i . "\n";
            $googledocs = $this->extractGoogledocs($paragraph);
            if ($this->isGoogledocs($googledocs) === false) {
                continue;
            }

            $this->thing->log(
                $googledocs['googledocs'] . "\n" . $googledocs['line'] . "\n"
            );
            break;
        }
        $run_time = time() - $start_time;
        $this->thing->log(" getGoogledocs " . $run_time);
        $this->response .= "Got a googledocs [" . $run_time . " seconds]. ";

        $googledocs['retrieved_at'] = $this->current_time;

        return $googledocs;
    }

    public function isGoogledocs($googledocs = null)
    {
        if ($googledocs === false) {
            return false;
        }

        if (!isset($googledocs['line'])) {
            return false;
        }

        $text = $googledocs['line'];
        if (ctype_space($text) === true) {
            return false;
        }
        //        $run_time = time() - $start_time;

        // Because that is not a 'googledocs'.
        // A googledocs should have UTC in.
        // At least on this stack.
        // At least for now.
        $tokens = explode("UTC", $text);

        if (!isset($tokens[1])) {
            return false;
        }

        if (count($tokens) == 1 and $tokens[0] == "") {
            return false;
        }

        if (ctype_space($tokens[1]) === true) {
            return false;
        }
        if ($tokens[1] === "") {
            return false;
        }

        $time_tokens = explode(" ", $tokens[0]);
        if (strtolower($time_tokens[0]) == "timestamp") {
            return true;
        }

        return false;
    }

    public function extractGoogledocs($text = null)
    {
        if ($text === false) {
            return false;
        }
        if ($text === "") {
            return false;
        }
        if ($text === " ") {
            return false;
        }
        if ($text === true) {
            return false;
        }
        if ($text === null) {
            return false;
        }

        // Urls and Telephone numbers are not dates.
        // Remove them to make the date extractors work easier.

        // refactor this to At.
        //$text = $this->stripUrls($text);
        //$text = $this->stripTelephonenumbers($text, " ");

        $paragraph = $text;

        // Todo extract calendar.

        $arr = [
            'year',
            'month',
            'day',
            'day_number',
            'hour',
            'minute',
            'timezone',
        ];

        if ($paragraph == "") {
            return false;
        }
        $t = $this->thing->at_agent->extractAt($paragraph);
        $flag = false;
        $date = [];

        foreach ($arr as $component) {
            $this->{$component} = $this->thing->at_agent->{$component};

            if ($this->{$component} !== false) {
                $flag = true;
            }
            $date[$component] = $this->{$component};
        }
        if ($flag === false) {
            // No components seen
            return false;
        }

        $googledocs = $this->textGoogledocs($date);
        $date['line'] = $paragraph;
        $date['googledocs'] = $googledocs;
        return $date;
    }

    public function timestampGoogledocs($googledocs)
    {
        if ($googledocs == null) {
            return true;
        }
        if ($googledocs == false) {
            return true;
        }
        $arr = [
            'year' => 'XXXX',
            'month' => 'XX',
            'day_number' => 'XX',
            'hour' => 'XX',
            'minute' => 'XX',
            'second' => 'XX',
            'timezone' => 'X'
        ];
        foreach ($arr as $component => $default_text) {
            ${$component} = $default_text;

            if (!isset($googledocs[$component])) {
                $googledocs[$component] = $arr[$component];
            }

            if ($googledocs[$component] === null) {
                continue;
            }
            if ($googledocs[$component] === false) {
                continue;
            }
            if ($googledocs[$component] === true) {
                continue;
            }
            if (strtolower($googledocs[$component]) === 'x') {
                continue;
            }
            if (strtolower($googledocs[$component]) === 'z') {
                continue;
            }
            if ($googledocs[$component] === '?') {
                continue;
            }

            // is_int does not recognizing '2020' as an integer.
            // So use this.
            // https://www.php.net/manual/en/function.is-int.php
            if (ctype_digit(strval($googledocs[$component]))) {
                ${$component} = str_pad(
                    $googledocs[$component],
                    mb_strlen($default_text),
                    "0",
                    STR_PAD_LEFT
                );
            }
        }

        $timezone = "X";
        if (strtolower($googledocs['timezone']) == 'utc') {
            $timezone = "Z";
        }

        $text =
            $year .
            "-" .
            $month .
            "-" .
            $day_number .
            'T' .
            $hour .
            ":" .
            $minute .
            ":" .
            $second .
            $timezone;

        return $text;
    }

    public function textGoogledocs($googledocs)
    {
        $text = "";
        foreach ($googledocs as $key => $value) {
            if ($value === false) {
                continue;
            }
            $text .= $key . " " . $value . " ";
        }

        return $text;
    }

    public function doGoogledocs()
    {
        if ($this->agent_input == null) {
            $array = ['where are you?'];
            $k = array_rand($array);
            $v = $array[$k];

            if (isset($this->googledocs['googledocs'])) {
                $v = $this->googledocs['googledocs'];
            }

            //$response = "GOOGLEDOCS | " . strtolower($v) . ".";

            $this->googledocs_message = $v; // mewsage?
        } else {
            $this->googledocs_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a googledocs keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        // return $this->thing_report;
    }

    public function makeWeb() {

//var_dump($this->googledocs);

}

    function makeSMS()
    {
        $this->node_list = ["googledocs" => ["googledocs", "dog"]];

        $sms = "GOOGLEDOCS ";
        // . $this->googledocs_message;

        if (isset($this->googledocs)) {
            $googledocs_timestamp = $this->timestampGoogledocs($this->googledocs);

            $timestamp_text = "undated";
            if (is_string($googledocs_timestamp)) {
                $timestamp_text = $googledocs_timestamp;
            }

            $sms .= $timestamp_text . " ";

            // See if there is a googledocs with a UTC timestamp.
            if (
                $this->googledocs !== false and
                stripos($this->googledocs['line'], " utc ") !== false
            ) {
                $tokens = explode(" UTC ", $this->googledocs['line']);

                $text_token = $tokens[1];
                $time_tokens = explode(" ", $tokens[0]);

                if (strtolower($time_tokens[0]) === 'timestamp') {
                    $sms .= "| " . $text_token . " ";
                }
            }
        }
        $sms .= $this->response;

        $this->sms_message = "" . $sms;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "googledocs");
        $choices = $this->thing->choice->makeLinks('googledocs');
        $this->thing_report['choices'] = $choices;
    }

    public function questionGoogledocs($text = null)
    {
        $this->thing->log("questionGoogledocs start");
        $googledocs_horizon = $this->googledocs_horizon;

        $agent_class_name = "Googledocs";

        // Start of ? code
        // This is a generalised piece of code.
        // It creates a unique key from the hashed from address and the agent name.
        // Then it gets either a cached version of the agent variable.
        // Or if 'too' old, calls for a more recent agent variable.

        // TODO Refactor to Agent.

        $agent_name = strtolower($agent_class_name);

        $this->thing->log("questionGoogledocs instantiate slug Thing");

        $slug = $this->getSlug($agent_name . "-" . "test");

        $this->thing->log("questionGoogledocs call getMemory");
        $memory = $this->getMemory($slug);
        $age = 1e9;

        $this->thing->log("questionGoogledocs got memory");

        if ($memory != false and $memory != true) {
            $this->thing->log("questionGoogledocs found a memory");

            $age =
                strtotime($this->current_time) -
                strtotime($memory['retrieved_at']);

            // How old can the googledocs be before needing another check?
            if ($age <= $googledocs_horizon and $this->isGoogledocs($memory)) {
                // If younger than 60 seconds use response in memory.
                $this->response .=
                    "Saw an " .
                    $agent_name .
                    " channel memory from " .
                    $this->thing->human_time($age) .
                    " ago. ";

                $this->googledocs = $memory;
                $this->thing->log("questionGoogledocs return memory");

                return $memory;
            }

            if (!$this->isGoogledocs($memory)) {
                $this->thing->log("did not see a memory");

                $memory = $this->getGoogledocs($text);
                $this->setMemory($slug, $memory);
            }
        }
        if ($age > $googledocs_horizon) {
            $this->thing->log("questionGoogledocs saw the memory was too old");

            $datagram = [
                "to" => $this->from,
                "from" => "googledocs",
                "subject" => "googledocs update",
                //                    "subject" => "s/ " . "googledocs",
                "agent_input" => "googledocs",
            ];

            $response = $this->thing->spawn($datagram);
            if ($response === true) {
                $this->response .= "Request for googledocs update unsuccessful. ";
                $this->thing->log("Spawn request failed.");
            } else {
                $this->response .= "Requested a googledocs update. ";
                $this->thing->log("Requested thing spawn.");
            }
            //            $age = 0;
        }

        $googledocs = $memory;

        $this->googledocs = $googledocs;
        $this->thing->log("questionGoogledocs complete");
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);
        if ($this->agent_input != null) {
            $input = $this->agent_input;
        }
        if ($input == "googledocs") {
            $this->questionGoogledocs();
            return;
        }

        if ($input == "googledocs update") {
            $slug = "googledocs-test";
            $memory = $this->getGoogledocs($input);

            $this->setMemory($slug, $memory);
            $this->googledocs = $memory;
            return;
        }

        if ($input == "googledocs test") {
            $this->test();
            return;
        }

        $this->googledocs = $this->extractGoogledocs($input);
    }
}
