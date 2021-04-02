<?php
/**
 * Measurement.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Measurement extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {
        $this->keywords = ["measurement"];
    }

    public function isMeasurement($text = null)
    {
        $measurement = $this->extractMeasurement($text);
        if ($measurement === false) {return false;}
        return true;
    }

    function decomposeMeasurement($input = null)
    {
        $tokens = explode(" ", $input);
        if (count($tokens) !== 1) {
            return true;
        }

        $m = preg_split(
            "/([A-Za-z]+)/",
            $input,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

if (!isset($m[1])) {return true;}

        $amount = $m[0];
        $units = $m[1];
        if (!ctype_alpha($m[1])) {
            return true;
        }

        return [$amount, $units];
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */

    function extractMeasurements($input = null)
    {
        $measurements = [];
        $ngrams = $this->extractNgrams($input, 2);

        foreach ($ngrams as $ngram) {
            if ($this->countTokens($ngram) == 1) {
                $measurement = $this->decomposeMeasurement($ngram);
                if ($measurement === true) {continue;}
                list($amount, $units) = $measurement;
            }
            if ($this->countTokens($ngram) == 2) {
                $tokens = explode(" ", $ngram);
                $amount = $tokens[0];
                $units = $tokens[1];
            }
            $measurement = ["amount" => $amount, "units" => $units];
            $measurements[] = $measurement;
        }
        return $measurements;
    }

    function extractMeasurement($input = null)
    {
        $measurements = $this->extractMeasurements($input);

        if (count($measurements) != 1) {
            return false;
        }

        return $measurements[0];
    }

    public function selectMeasurement($measurement = null)
    {
        if ($measurement == null) {
            $this->amount = "X";
            $this->units = "X";
            $this->response .= "Did not get a measurement. ";
            return;
        }

        $this->measurement = $measurement;
        $this->amount = $measurement["amount"];
        $this->units = $measurement["units"];
        $this->response .= "Got a measurement. ";
    }

    /**
     *
     */
    function makeTXT()
    {
        $txt = $this->sms_message;

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $sms_message = "MEASUREMENT | ";
        if (isset($this->measurement)) {
            $sms_message .=  "" . $this->amount . " " . $this->units . " ";
        }
        $sms_message .= $this->response;

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    /**
     *
     */
    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["help"] = "This is the measurements manager.";
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $filtered_input = $this->assert($this->input, "measurement");

        $measurement = $this->extractMeasurement($filtered_input);

        if ($measurement !== false) {
            $this->selectMeasurement($measurement);
        }
    }
}
