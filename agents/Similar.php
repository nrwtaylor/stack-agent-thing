<?php

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// TODO - Do not update set on read.

class Similar extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->keyword = "similar";

        $this->verbosity = 1;

        if (isset($this->settings['verbosity'])) {
            $this->verbosity = $this->settings['verbosity'];
        }

        if ($this->verbosity >= 2) {
            $this->thing->log(
                $this->agent_prefix .
                    'running on Thing ' .
                    $this->thing->nuuid .
                    "."
            );
        }

        $this->start_time = $this->thing->elapsed_runtime();

        $this->test = "Development code"; // Always

        if ($this->verbosity >= 2) {
            $this->thing->log(
                $this->agent_prefix .
                    'received this Thing, "' .
                    $this->subject .
                    '".'
            );
        }

        // Set up default flag settings
        //        $this->verbosity = 1;
        $this->requested_flag = null;
        $this->default_flag = "green";

        $this->requested_thing_name = 'thing';
        $this->horizon = 15; // Sounds about right.
        // Allows for manual overloading of input.  Gets boring.
        // Allows a 15 day horizong (14+1) which is a good default
        // for repeating daily scheduling patterns.

        $this->node_list = ["green" => ["red" => ["green"]]];

        $this->current_time = $this->thing->json->time();

        if ($this->verbosity >= 2) {
            $this->thing->log(
                $this->agent_prefix .
                    ' got flag variables. Timestamp ' .
                    number_format($this->thing->elapsed_runtime()) .
                    'ms.'
            );
        }
    }

    public function read($text = null)
    {
        $this->readInstruction();
        $this->readSubject();
    }

    public function run()
    {
        $this->getSimilar();
    }

    public function makeWeb()
    {
        $web = "";

        $embedded = true;
        if (!$embedded) {
            //  $web = '<a href="' . $link . '">';
            $web .=
                '<img src= "' .
                $this->web_prefix .
                'thing/' .
                $this->uuid .
                '/number.png">';
            //  $web .= "</a>";
        } else {
            //  $web = '<a href="' . $link . '">';
            if (isset($this->image_embedded['similarity'])) {
                $web .= $this->image_embedded['similarity'];
                //   $web .= "</a>";
                $web .= "Similarity Chart";
                $web .= "<p>";
            }
            //   $web .= '<a href="' . $link . '">';
            if (isset($this->image_embedded['similarness'])) {
                $web .= $this->image_embedded['similarness'];
                //   $web .= "</a>";
                $web .= "Similarness Chart";
                $web .= "<p>";
            }
        }

        $this->web = $web;
        $this->thing_report['web'] = $web;
    }

    function set($requested_flag = null)
    {
        if ($requested_flag == null) {
            if (!isset($this->requested_flag)) {
                // Set default behaviour.
                // $this->requested_state = "green";
                // $this->requested_state = "red";
                $this->requested_flag = "green"; // If not sure, show green.
            }
            $requested_flag = $this->requested_flag;
        }

        $this->flag = $requested_flag;
        $this->refreshed_at = $this->current_time;

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(["similar", "flag"], $this->flag);
        $this->thing->json->writeVariable(
            ["similar", "refreshed_at"],
            $this->current_time
        );
        $this->thing->json->writeVariable(
            ["similar", "similarness"],
            $this->similarness
        );
        $this->thing->json->writeVariable(
            ["similar", "similarity"],
            $this->similarity
        );

        if ($this->verbosity >= 2) {
            $this->thing->log(
                $this->agent_prefix . 'set Flag to ' . $this->flag
            );
        }
    }

    function isFlag($flag = null)
    {
        // Validates whether the Flag is green or red.
        // Nothing else is allowed.

        if ($flag == null) {
            if (!isset($this->flag)) {
                $this->flag = "X";
            }
            $flag = $this->flag;
        }

        if ($flag == "red" or $flag == "green") {
            return false;
        }

        return true;
    }

    function getSimilar()
    {
        $train_things = [];

        // Get recent train tags.
        // This will include simple 'train'
        // requests too.
        // Think about that.

        $findagent_thing = new Findagent(
            $this->thing,
            $this->requested_thing_name . ' ' . $this->horizon
        );

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

        $t = $findagent_thing->thing_report;

        $count = 0;
        if (isset($t['things']) and is_array($t['things'])) {
            $count = count($t['things']);
        }

        if ($this->verbosity >= 2) {
            $this->thing->log(
                $this->agent_prefix .
                    'found ' .
                    $count .
                    " " .
                    ucwords($this->requested_thing_name) .
                    " Agent Things."
            );
        }

        $this->max_index = 0;
        $this->previous_trains = [];
        $this->similarness = 0;
        $this->similarity = 0;
        $this->flag = "green";

        $this->matches = [];

        if (
            isset($findagent_thing->thing_report['things']) and
            count($findagent_thing->thing_report['things']) > 1
        ) {
            foreach ($findagent_thing->thing_report['things'] as $thing) {
                foreach ($findagent_thing->thing_report['things'] as $thing2) {
                    if ($thing['uuid'] == $thing2['uuid']) {
                        continue;
                    }

                    $subject = mb_substr($thing['task'],0,255);
                    $subject2 = mb_substr($thing2['task'],0,255);
                    //$l = levenshtein($subject, $this->subject);
                    $l = levenshtein($subject, $subject2);

                    if ($l == 0) {
                        $this->similarity += 1;
                        $this->matches[] = $subject;
                    }

                    $this->similarness += $l;

                    if ($this->verbosity == 9) {
                        $this->thing->log(
                            $this->agent_prefix .
                                ' ' .
                                $subject .
                                ' ' .
                                $l .
                                '.',
                            "DEBUG"
                        );
                    }
                }
            }
        }

        if ($this->similarity >= 2) {
            $this->flag = "red";
        }

        if ($this->verbosity >= 2) {
            $this->thing->log(
                $this->agent_prefix .
                    'calculated similarness =  ' .
                    $this->similarness .
                    '.',
                "DEBUG"
            );
            $this->thing->log(
                $this->agent_prefix .
                    'calculated similarity =  ' .
                    $this->similarity .
                    '.',
                "DEBUG"
            );
        }
    }

    function get()
    {
        // get gets the state of the Flag the last time
        // it was saved into the stack (serialized).
        $this->previous_flag = $this->thing->getVariable("similar", "flag");
        $this->refreshed_at = $this->thing->getVariable(
            "similar",
            "refreshed_at"
        );

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isFlag($this->previous_flag)) {
            $this->flag = $this->previous_flag;
        } else {
            $this->flag = $this->default_flag;
        }

        if ($this->verbosity >= 2) {
            $this->thing->log(
                $this->agent_prefix .
                    'got a ' .
                    strtoupper($this->flag) .
                    ' FLAG.'
            );
        }
    }

    function selectChoice($choice = null)
    {
        if ($choice == null) {
            if (!isset($this->flag)) {
                $this->flag = $this->default_flag;
            }
            $choice = $this->flag;
        }

        if (!isset($this->flag)) {
            $this->previous_flag = false;
        } else {
            $this->previous_flag = $this->flag;
        }

        $this->flag = $choice;

        $this->thing->log(
            'Agent "' .
                ucwords($this->keyword) .
                '" chose "' .
                $this->flag .
                '".'
        );

        return $this->flag;
    }

    function makeChoices()
    {
        $this->thing->choice->Create(
            $this->keyword,
            $this->node_list,
            $this->flag
        );

        $choices = $this->flag->thing->choice->makeLinks($this->flag);
        $this->thing_report['choices'] = $choices;
    }

    public function respond()
    {
        if ($this->agent_input != null) {
            return true;
        }

        // At this point state is set
        $this->set($this->flag);

        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        $this->thing_report['email'] = $this->message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        $this->makeHelp();
        $this->makeTxt();
    }

    function makeHelp()
    {
        if ($this->flag == "green") {
            $this->thing_report['help'] =
                'FLAG GREEN. No recent similarness has been seen.';
        }

        if ($this->flag == "red") {
            $this->thing_report['help'] =
                'FLAG RED. Recent similarness has been seen.';
        }
    }

    function makeTXT()
    {
        $txt = 'This is SIMILAR /n';
        //$txt .= 'There is a '. strtoupper($this->flag) . " FLAG. ";
        if ($this->verbosity >= 5) {
            $txt .=
                'It was last refreshed at ' . $this->current_time . ' (UTC).';
        }

        $txt .= '/r';

        foreach ($this->matches as $t) {
            $txt .= $t;
        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    function makeSMS()
    {
        $sms_message = "SIMILAR ";

        if ($this->verbosity >= 7) {
            $sms_message .= " | similarity " . strtoupper($this->similarity);
        }

        if ($this->verbosity >= 7) {
            $sms_message .= " | similarness " . strtoupper($this->similarness);
        }

        if ($this->verbosity >= 6) {
            $sms_message .=
                " | previous flag " . strtoupper($this->previous_flag);
        }

        if ($this->verbosity >= 1) {
            $sms_message .= " | flag " . strtoupper($this->flag);
        }

        if ($this->verbosity >= 5) {
            $sms_message .= " | nuuid " . strtoupper($this->thing->nuuid);
        }

        if ($this->verbosity >= 2) {
            if ($this->flag == "red") {
                $sms_message .= " | MESSAGE Latency";
            }

            if ($this->flag == "green") {
                $sms_message .= ' | MESSAGE Input';
            }
        }

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

    function makeMessage()
    {
        $message = 'This is a SIMILARITY detector. ';

        if ($this->flag == 'red') {
            $message .=
                'MORDOK has seen similarity with the previous ' .
                $this->horizon .
                ' messages. ';
        }

        if ($this->flag == 'green') {
            $message .= 'MORDOK is likely operating with low LATENCY. ';
        }

        $message .= 'The flag is a  ' . strtoupper($this->flag) . " FLAG. ";

        $this->message = $message;
        $this->thing_report['message'] = $message; // NRWTaylor. Slack won't take hmtl raw. $test_message;
    }

    public function readInstruction()
    {
        if ($this->agent_input == null) {
            //$this->defaultCommand();
            return;
        }

        $pieces = explode(" ", strtolower($this->agent_input));

        if (isset($pieces[0])) {
            $this->requested_thing_name = $pieces[0];
        }
        if (isset($pieces[1])) {
            $this->horizon = $pieces[1];
        }
    }

    public function readSubject()
    {
        $this->response = null;

        if ($this->agent_input == "similar") {
            return null;
        }

        $this->historySimilar();
        $this->chartSimilarity();
        $this->chartSimilarness();

        if ($this->agent_input != null) {
            $this->requested_thing_name = $this->agent_input;
        }

        $keywords = ['flag', 'red', 'green'];

        $input = strtolower($this->subject);

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == $this->keyword) {
                $this->get();
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'red':
                            $this->thing->log(
                                $this->agent_prefix .
                                    'received request for RED FLAG.'
                            );
                            $this->selectChoice('red');
                            return;
                        case 'green':
                            $this->selectChoice('green');
                            return;
                        case 'next':

                        default:
                    }
                }
            }
        }

        // If all else fails try the discriminator.

        $input_agent = new Input($this->thing, "input");
        $discriminators = ['red', 'green'];
        $input_agent->aliases['red'] = ['r', 'red', 'on'];
        $input_agent->aliases['green'] = [
            'g',
            'grn',
            'gren',
            'green',
            'gem',
            'off',
        ];

        $this->requested_flag = $input_agent->discriminateInput(
            $haystack,
            $discriminators
        );

        switch ($this->requested_flag) {
            case 'green':
                $this->selectChoice('green');
                return;
            case 'red':
                $this->selectChoice('red');
                return;
        }

        $this->get();

        return "Message not understood";

        return false;
    }

    function historySimilar()
    {
        $variable_set = [
            "similar" => ["similarness", "similarity", "refreshed_at"],
        ];
        $history_agent = new History($this->thing, "history");
        $history_agent->variablesHistory($variable_set);
        $this->similars_history = $history_agent->variables_history;
    }

    public function chartSimilarness()
    {
        if (!isset($this->similars_history)) {
            $this->historySimilar();
        }
        $chart_agent = new Chart($this->thing, "chart");

        $variable_set = ['similarness' => null];
        $image_embedded = $chart_agent->historyChart(
            $this->similars_history,
            $variable_set
        );

        $this->image_embedded['similarness'] = $image_embedded;
    }

    public function chartSimilarity()
    {
        if (!isset($this->similars_history)) {
            $this->historySimilar();
        }

        $chart_agent = new Chart($this->thing, "chart");
        $variable_set = ['similarity' => null];
        $image_embedded = $chart_agent->historyChart(
            $this->similars_history,
            $variable_set
        );
        $this->image_embedded['similarity'] = $image_embedded;
    }
}
