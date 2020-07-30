<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Chooser extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    public function respond()
    {
        //  Intentionally respond.
        // To just record the choice.

        $this->thing->flagGreen();

        // Generate email response.

        $to = $this->thing->from;
        $from = "chooser";

        $this->description = 'scalar';

        //		$old_number = $this->thing->account[$this->description]->balance['amount'];
        //		echo "old number :", $old_number;
    }

    public function makeChoice()
    {
        switch ($this->choice) {
            case 'credit':
                $this->response .= 'Identified a Choice.';
                break;
            case 'create':
                $this->response .=
                    'Identified a Choice creation. ';
                break;
            case 'destroy':
                break;
            default:
            //echo 'default';
        }
    }

    public function makeSMS()
    {
        $this->makeChoice();

        $sms = 'CHOOSER | ' . $this->response;
        $this->thing_report['sms'] = $sms;
    }

    public function scoreChooser($text = null)
    {
        $score = 0;
        if ($text == null) {
            return 0;
        }
        // dev stack

        if (stripos($this->input, $text) !== false) {
            $score += 1;
        }

        return $score;
    }

    public function readSubject()
    {
        // search for a credit debit instruction
        // search for an account creation instruction

        if ($this->scoreChooser('credit') > $this->scoreChooser('create')) {
            // Likely subject is a Credit instruction
            $this->choice = "credit";
        } else {
            $this->choice = "create";
        }

        return false;
    }

    public function choiceSelect()
    {
        $confidence = 0.0;

        return $confidence;
    }

    function choiceCreate()
    {
        return $confidence;
    }
}
