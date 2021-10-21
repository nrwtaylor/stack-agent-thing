<?php
/**
 * Input.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class More extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */

    /**
     */
    function init()
    {
        $this->test = "Development code";
        $this->input_agent = null;
    }

    function assertIs($input)
    {
        $this->input_agent = null;
        $agent_name = "input";
        $whatIWant = $input;
        if (
            ($pos = strpos(strtolower($input), $agent_name . " is")) !== false
        ) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen($agent_name . " is")
            );
        } elseif (($pos = strpos(strtolower($input), $agent_name)) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen($agent_name));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $this->input_agent = $filtered_input;
    }

    /**
     *
     * @param unknown $text (optional)
     */
    function doMore($text = null)
    {
        $filtered_text = strtolower($text);
        $ngram_agent = new Ngram($this->thing, $filtered_text);

        foreach ($ngram_agent->ngrams as $index => $ngram) {
            switch ($ngram) {
                case "more":
                    $web = new Web($this->thing, "web");

                    if (strtolower($web->prior_agent) == strtolower("more")) {
                        $this->response .= "More more? ";
                        return;
                    }

                    $this->getMore($web->prior_agent);

                    $this->response .= $this->agent->thing_report["sms"];
                    return;

                default:
            }
        }

        $this->assertIs($this->input);
        $this->response .=
            "Said that input response is expected to the current agent. ";
    }


    /**
     *
     */
    public function get()
    {
        $this->input_agent = new Input($this->thing, "input");
    }

    /**
     *
     * @param unknown $input_flag (optional)
     */
    function set($input_agent = null)
    {
    }

    /**
     *
     */
    function makeSMS()
    {
        $sms = "MORE " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->thing_report["info"] = "This makes an more thing.";
        $this->thing_report["help"] = "This is about asking for more things.";

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->doMore($this->input);

        return false;
    }

    /**
     *
     * @return unknown
     */
    function getMore($agent_class_name)
    {
        try {
            $agent_namespace_name =
                "\\Nrwtaylor\\StackAgentThing\\" . $agent_class_name;

            $this->thing->log(
                'trying Agent "' . $agent_class_name . '".',
                "INFORMATION"
            );
            $agent = new $agent_namespace_name(
                $this->thing,
                strtolower($agent_class_name)
            );

            // If the agent returns true it states it's response is not to be used.
            if (isset($agent->response) and $agent->response === true) {
                throw new Exception("Flagged true.");
            }

            $this->thing_report = $agent->thing_report;

            $this->agent = $agent;
        } catch (\Error $ex) {
            // Error is the base class for all internal PHP error exceptions.
            $this->thing->log(
                'could not load "' . $agent_class_name . '".',
                "WARNING"
            );

            $message = $ex->getMessage();
            $code = $ex->getCode();
            $file = $ex->getFile();
            $line = $ex->getLine();

            $input = $message . "  " . $file . " line:" . $line;
            $this->thing->log($input, "WARNING");

            return false;
        }
        return true;
    }
}
