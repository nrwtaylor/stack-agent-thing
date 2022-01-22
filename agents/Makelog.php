<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);
ini_set("display_errors", 1);

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

// And now the makePhp class, exactly like the makePdf and makePng and makeTxt
// Let's call give it an N-gram to facilitate command 'make php'.
// Also means post-poning tackling what Pdf is actually defined as.
// Which might be exactly how it should be.

// Allowing these core channel processing functions to be handled
// in Composer packages

class Makelog
{
    public $var = "hello";

    function __construct(Thing $thing, $input = null)
    {
        $this->input = $input;
        $this->thing = $thing;

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container["stack"]["web_prefix"];
        $this->mail_postfix = $thing->container["stack"]["mail_postfix"];
        $this->word = $thing->container["stack"]["word"];
        $this->email = $thing->container["stack"]["email"];
        $this->email = $thing->container["stack"]["entity_name"];

        $this->subject = $thing->subject;

        $header = "THING AGENT LOG";
        $header .= "\n";

        $footer = "\n";

        if ($input == null) {
            $footer .= "Agent 'make log' says 'Nothing received'"; //
        } elseif (is_array($input)) {
            $footer .= "Agent 'make log' says array received.";
        } else {
            $footer .= "Agent 'make log' says '" . $input . "' received.";
        }

        $footer .= "\n";
        $footer .= "\n";
        $footer .= $this->web_prefix;

        $file = $header . "\n";

        // routes passes image_name to make png as $input
        $this->agent_thing = new Agent($thing, $input);
        $this->log_handler = new Log($thing, "log");
/*
        $log_text = $this->log_handler->filterLog(
            $this->agent_thing->thing->log,
            null,
            ["make", '"Agent"']
        );
*/
        $log_text = $this->log_handler->filterLog(
            $thing->log,
            null,
            ["make", '"Agent"']
        );


        $text = $log_text;

        $this->text = $log_text;

       $raw_log_text = $this->log_handler->filterLog(
            $thing->log
        );

$this->runtimesLog($thing->log);

        $t = "\nSelf-reported Agent runtimes\n";
        foreach (
            array_reverse($this->agent_run_for)
            as $key => $agent_run_for
        ) {
            $t .=
                $agent_run_for["agent_name"] .
                " " .
                number_format($agent_run_for["run_for"]) .
                "ms\n";
        }

        $file .= $t . "\n";

        $t = "\nSelf-reported Agent chain-of-custody\n";
        if ((isset($this->agent_sequence)) and (is_array($this->agent_sequence))) {
            foreach ($this->agent_sequence as $key => $array) {
                $t .= $array["agent_name"];

                if (!isset($prior_run_for)) {
                    $prior_run_for = 0;
                }
                if ($array["run_for"] != "X") {
                    $t .=
                        " (" .
                        number_format($array["run_for"] - $prior_run_for) .
                        "ms)";
                    $t .= " | ";
                    $prior_run_for = $array["run_for"];
                } else {
                    $t .= " > ";
                }
            }
        }
//echo $t;
//exit();
        $t .= "\n";

        $file .= $t . "\n";
        $file .= "\nSelf-report\n";
        $file .= $text;

        $file .= "\nMessage received\n";
        $file .= $this->subject;

        $file .= "\n";

        $file .= "\nAgent SMS response\n";
        $file .= $this->agent_thing->thing_report["sms"];

        $file .= "\nAgent information\n";

        $file .= "\n";

        $info = "No agent info available.";
        if (isset($this->agent_thing->thing_report["info"])) {
            $info = $this->agent_thing->thing_report["info"];
        }
        $file .= $info;

        $file .= "\n";

        $file .= $footer;

        $this->thing_report = ["thing" => $thing, "log" => $file];

        //$this->makeSnippet();
    }
    /*
    function makeSnippet() {

$this->thing_report['snippet'] = "Merp.";

}
*/
    function runtimesLog($text = null)
    {
        if ($text == null) {
            $text = $this->text;
        }
        $c = 0;
$last_run_for = 0;
        $this->runtimes = [];

        $text = str_replace('<br>','\n', $text);

        $lines = explode('\n', $text);
        $time_stamp = 0;
        $run_time = 0;
        $previous_run_time = 0;
        $this->agent_run_for = [];
        foreach ($lines as $key => $line) {
            preg_match_all("/[a-zA-Z]+/", $line, $matches);
            $words = $matches[0];

            preg_match_all("/[0-9,]+/", $line, $matches);
            $numbers = $matches[0];

            if (!isset($words[1])) {
                continue;
            }

            if (strtolower($words[1]) == "thing") {
                continue;
            }

            $agent_name = $words[2];
/*
            if (strpos($line, "ran for") !== false) {
                $c += 1;
                $this->agent_run_time = [];

                $run_time = (int) str_replace(",", "", $numbers[1]);
                $run_for = $run_time;
                $previous_run_time = $run_time;

                $this->agent_run_for[] = [
                    "agent_name" => $agent_name,
                    "run_for" => $run_for,
                    "text" => $line,
                ];
            }
*/
            if (
                !isset($previous_agent_name) or
                $agent_name != $previous_agent_name
            ) {
                if (!isset($run_for)) {
                    $run_for = "X";
                }
$current_run_for = (int) str_replace(",", "", $numbers[0]);

$run_for = $current_run_for - $last_run_for;

                $this->agent_sequence[] = [
                    "agent_name" => $agent_name,
                    "run_for" => $run_for,
                ];
            }
$last_run_for = $run_for;
            $previous_agent_name = $agent_name;
            $run_for = null;
        }
    }
}
