<?php
/**
 * Number.php
 *
 * @package default
 */


// Uniqueness.  Is valuable.
namespace Nrwtaylor\StackAgentThing;

// Transparency
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Number extends Agent
{


    /**
     *
     */
    function init() {
        $this->node_list = array("number"=>
            array("number"));

        //        $this->variables = new Variables($this->thing, "variables number " . $this->from);

        $this->aliases = array("learning"=>array("good job"));


        $this->recognize_french = true; // Flag error

        $this->thing_report['help'] = "This records numbers. And lets you make a graph. Text NUMBER 4.6. Then text CHART NUMBER.";
        $this->thing_report['info'] = "Text SHUFFLE ALL to reset the numbers. Then text CHART NUMBER.";

        $this->link = $this->web_prefix . 'thing/' . $this->uuid . '/number';

        $this->web_return_max = 10;
        $this->horizon = 99; // 99 items

        $this->event_horizon = 60*60*24;
        $this->y_max_limit = null;
        $this->y_min_limit = null;

    }


    /**
     *
     */
    function get() {

        $this->number_agent = new Variables($this->thing, "variables number " . $this->from);

        $this->number = $this->number_agent->getVariable("number");
        $this->refreshed_at = $this->number_agent->getVariable("refreshed_at");

        // Extract calling agent name from class name.
        $this->getCallingagent();
        $agent_class_name = $this->calling_agent;
        $this->calling_agent_name = strtolower((array_reverse(explode('\\', $agent_class_name)))[0]);

        $event_horizon = $this->number_agent->getVariable("event_horizon");

        if ($event_horizon != false) {$this->event_horizon = $event_horizon;}

        $y_max_limit = $this->number_agent->getVariable("y_max_limit");
        if ($y_max_limit != false) {$this->y_max_limit = $y_max_limit;}

        $y_min_limit = $this->number_agent->getVariable("y_min_limit");
        if ($y_min_limit != false) {$this->y_min_limit = $y_min_limit;}


    }


    /**
     *
     */
    function set() {

        $this->number_agent->setVariable("number", $this->number);
        $this->number_agent->setVariable("calling_agent_name", $this->calling_agent_name);
        $this->number_agent->setVariable("event_horizon", $this->event_horizon);
        $this->number_agent->setVariable("y_max_limit", $this->y_max_limit);
        $this->number_agent->setVariable("y_min_limit", $this->y_min_limit);

        $this->number_agent->setVariable("refreshed_at", $this->current_time);

    }


    /**
     *
     */
    public function makeChart() {

        if (!isset($this->numbers_history)) {$this->historyNumber();}
        $t = "NUMBER CHART\n";
        $points = array();
        //        $x_min = 1e99;
        //        $x_max = -1e99;

        //        $y_min = 1e99;
        //        $y_max = -1e99;

        foreach ($this->numbers_history as $i=>$number_object) {

            $created_at = $number_object['created_at'];
            $number = $number_object['number'];


            $points[$created_at] = $number;

            if (!isset($x_min)) {$x_min = $created_at;}
            if (!isset($x_max)) {$x_max = $created_at;}


            if ($created_at < $x_min) {$x_min = $created_at;}
            if ($created_at > $x_max) {$x_max = $created_at;}

            if (!isset($y_min)) {$y_min = $number;}
            if (!isset($y_max)) {$y_max = $number;}

            if ($number < $y_min) {$y_min = $number;}
            if ($number > $y_max) {$y_max = $number;}

        }

        $this->chart_agent = new Chart($this->thing, "chart number " . $this->from);
        $this->chart_agent->points = $points;

        $this->chart_agent->x_min = $x_min;
        $this->chart_agent->x_max = $x_max;
        $this->chart_agent->x_max = strtotime($this->thing->time);


        if (($this->y_min_limit != false) or ($this->y_min_limit != null)) {
            $y_min = $this->y_min_limit;
        }

        $this->chart_agent->y_min = $y_min;


        if (($this->y_max_limit != false) or ($this->y_max_limit != null)) {
            $y_max = $this->y_max_limit;
        }
        $this->chart_agent->y_max = $y_max;



        $y_spread = 100;
        if (($y_min == false) and ($y_max === false)) {
            //
        } elseif (($y_min == false) and (is_numeric($y_max))) {
            $y_spread = $y_max;
        } elseif (($y_max == false) and (is_numeric($y_min))) {
            // test stack
            $y_spread = abs($y_mix);

        } else {
            $y_spread = $y_max - $y_min;
            //            if ($y_spread == 0) {$y_spread = 100;}
        }
        if ($y_spread == 0) {$y_spread = 100;}

        $this->chart_agent->y_spread = $y_spread;
        $this->chart_agent->drawGraph();

    }


    /**
     *
     */
    public function makeImage() {
        $this->image = $this->chart_agent->image;
    }


    /**
     *
     * @param unknown $text
     * @return unknown
     */
    public function isSingleprecision($text) {

        return $this->isPrecision($text, 1);
    }


    /**
     *
     * @param unknown $text
     * @param unknown $test_precision (optional)
     * @return unknown
     */
    public function isPrecision($text, $test_precision = 1) {

        $precision = $this->getPrecision($text);

        if ($precision == $test_precision) {return true;}



        return false;

    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    public function getPrecision($text = null) {

        if ($text == null) {$text = $this->input;}

        return strlen(substr(strrchr($text, "."), 1));

    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    public function getDigits($text = null) {


        if ($text == null) {$text = $this->input;}

        $this->extractNumber($text);

        $number = $this->number;


        $num_digits = $number !== 0 ? floor(log10($number) + 1) : 1;

        //$left_of_period = strlen(substr(strrchr($text, "."), 0));
        //$num_digits = str_replace(",","",$left_of_period);

        return $num_digits;

    }


    /**
     *
     * @return unknown
     */
    function historyNumber() {

        // See if a stack record exists.
        $findagent_thing = new Findagent($this->thing, 'number '. $this->horizon);

        $this->numbers_history = array();
        foreach ($findagent_thing->thing_report['things'] as $thing_object) {

            $variables_json= $thing_object['variables'];
            $variables = $this->thing->json->jsontoArray($variables_json);

            if (isset($variables['number'])) {

                $number = "X";
                $calling_agent = "X";
                $refreshed_at = "X";


                if (isset($variables['number']['number'])) {$number = $variables['number']['number'];}
                if (isset($variables['number']['calling_agent'])) {$number = $variables['number']['calling_agent'];}
                if (isset($variables['number']['refreshed_at'])) {$refreshed_at = $variables['number']['refreshed_at'];}
                if (isset($variables['number']['refreshed_at'])) {$refreshed_at = $variables['number']['refreshed_at'];}

            }

            $age = strtotime($this->current_time) - strtotime($refreshed_at);
            if ($age > $this->event_horizon) {continue;}

            if (!is_numeric($number) ) {continue;}

            $this->numbers_history[] = array("timestamp"=>$refreshed_at, "created_at"=>strtotime($refreshed_at), "calling_agent"=>$calling_agent,
                "number"=>$number, "uuid"=>$thing_object['uuid']);

        }

        $refreshed_at = array();
        foreach ($this->numbers_history as $key => $row) {
            $refreshed_at[$key] = $row['timestamp'];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->numbers_history);


        /*
            // Sort by length of phrase. Shortest first.
            $traditional = array();
            foreach ($this->numbers_history as $key => $row) {
                $traditional[$key] = $row['timestamp'];
            }
            array_multisort($traditional, SORT_ASC, $this->numbers_history);

$this->numbers_history = $traditional;
*/

    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    public function parseNumber($text = null) {

        if ($text == null) {return true;}
        return $this->extractNumber($text);

    }


    /**
     *
     */
    function makeTXT() {

        if (!isset($this->numbers_history)) {$this->historyNumber();}
        $t = "NUMBER REPORT\n";

        $txt = 'These are NUMBERS. ';
        $txt .= "\n";
        $txt .= "\n";
        $txt .= " " . str_pad("TIMESTAMP", 20, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("AGENT", 20, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("NUMBER", 40, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";

        foreach ($this->numbers_history as $i=>$number) {

            $created_at = $number['timestamp'];
            $calling_agent = $number['calling_agent'];
            $number = $number['number'];

            $txt .= " " . str_pad(strtoupper(trim($created_at)), 20, " ", STR_PAD_RIGHT);
            $txt .= " " . "  " .str_pad(strtoupper(trim($calling_agent)), 20, " ", STR_PAD_LEFT);
            $txt .= " " . "  " .str_pad(strtoupper($number), 40, " ", STR_PAD_LEFT);
            $txt .= "\n";

        }

        $this->thing_report['txt'] = $txt;

    }


    /**
     *
     * @param unknown $text
     * @return unknown
     */
    function hasNumber($text) {


        $this->extractNumbers($text);
        if ((isset($this->numbers)) and (count($this->numbers) > 0)) {return true;}
        return false;

    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractNumbers($input = null) {
        if ($input == null) {
            $input = $this->subject;
        }
        // https://www.regular-expressions.info/floatingpoint.html
        // Life goals regex that does this

        if (!isset($this->numbers)) {
            $this->numbers = array();
        }

        $pieces = explode(" ", $input);

        $i = str_replace(array(',', ':', '-', '/'), ' ', $input);
        $pieces = explode(" ", $i);

        $this->numbers = [];
        foreach ($pieces as $key=>$piece) {

            if (is_numeric($piece)) {
                $this->numbers[] = $piece;
                continue;
            }

            // X - Specify. Z - Available.
            if ((strtoupper($piece) == "X") or (strtoupper($piece == "Z"))) {
                $this->numbers[] = $piece;
                continue;
            }


            // Treat () as accounting format number
            // Rare to see this in use.
            /*
    if (is_numeric(substr($piece,0,-1))) {
            $this->numbers[] = substr($piece,0,-1);
            continue;
    }

    if (is_numeric(substr($piece,-1,1))) {
            $this->numbers[] = substr($piece,-1,1);
            continue;
    }
*/


            if (is_numeric(substr($piece, 1, -1))) {
                if ((substr($piece, 0, 1) == "(") and (substr($piece, -1, 1) == ")")) {
                    $this->numbers[] = -1 * substr($piece, 1, -1);
                    continue;
                }

                $this->numbers[] = substr($piece, 1, -1);
                continue;
            }

            if (is_numeric(str_replace(",", "", $piece))) {
                $this->numbers[] = str_replace(",", "", $piece);
                continue;
            }

            // preg_match_all('!\d+!', $piece, $matches);
            preg_match_all('/([\d]+)/',  $piece, $matches);

            foreach ($matches[0] as $key=>$match) {
                $this->numbers[] = $match;
            }

        }
        return $this->numbers;
    }


    /**
     *
     */
    function extractNumber() {
        $this->number = false; // No numbers.
        if (!isset($this->numbers)) {$this->extractNumbers();}

        if (isset($this->numbers[0])) {
            $this->number = $this->numbers[0];
        }

    }


    /**
     *
     * @return unknown
     */
    public function respondResponse() {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        //        $this->thing_report['sms'] = $this->sms_message;

    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {
        // If the to line is a UUID, then it needs
        // to be sent a receipt.

        if ($this->agent_input == null) {
            $input = $this->subject;
        }

        if ($this->agent_input == "number") {
            $input = $this->subject;
        } else {
            $input = $this->agent_input;
        }

        $this->extractNumbers($input);
        $this->extractNumber();

        $this->extracted_number = $this->number;

        if ($this->number == false) {
            $this->get();
        }

        if ($this->number === false) {
            $this->response .= "No number found. Text NUMBER 5.3. Or NUMBER 6.005.";
            return null;
        }
        // Keyword
        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {

            if ($this->input == 'number') {
                $this->response .= "Last number retrieved.";
                return;

            }
        }


        switch ($this->input) {
        case "number chart":
        case "chart number":
        case "number graph":
        case "graph number":
            $this->response .= "Made a link to a chart " . $this->link;
            return;

        default:
        }

        switch (true) {
        case (stripos($this->input, 'low') !== false) :
        case (stripos($this->input, 'min') !== false) :
        case (stripos($this->input, 'minimum') !== false) :
        case (stripos($this->input, 'lowest') !== false) :
        case (stripos($this->input, 'smallest') !== false) :

            if ($this->extracted_number === false) {

            } else {

                if ( count($this->numbers) == 1) {

                    $this->y_min_limit = $this->number;
                    $this->number = null;
                    $this->response .= "Set minimum range to " . $this->y_min_limit . ". ";

                    return;

                }


            }

        case (stripos($this->input, 'high') !== false) :
        case (stripos($this->input, 'max') !== false) :
        case (stripos($this->input, 'maximum') !== false) :
        case (stripos($this->input, 'dog') !== false) : // Because max translates to dog :|

            if ($this->extracted_number === false) {

            } else {

                if ( count($this->numbers) == 1) {

                    $this->y_max_limit = $this->number;
                    $this->number = null;
                    $this->response .= "Set maximum number to " . $this->y_max_limit . ". ";

                    return;

                }


            }

        case (stripos($this->input, 'limit') !== false) :
        case (stripos($this->input, 'range') !== false) :
        case (stripos($this->input, 'to') !== false) :



            if (stripos($this->input, 'auto') !== false) {

                $this->y_min_limit = null;
                $this->y_max_limit = null;
                $this->number = null;
                $this->response .= "Range set to automatic. ";
                return;

            }


            if ($this->extracted_number === false) {

            } else {

                if ( count($this->numbers) == 2) {

                    $this->y_min_limit = min($this->numbers);
                    $this->y_max_limit = max($this->numbers);
                    $this->number = null;
                    $this->response .= "Range set to  " . $this->y_min_limit . " to " .$this->y_max_limit . ". ";
                    return;

                }

                if ( count($this->numbers) == 1) {

                    $this->y_max_limit = $this->number;
                    $this->number = null;
                    $this->response .= "Set maximum number to " . $this->y_max_limit . ". ";

                    return;

                }


            }

        case (stripos($this->input, 'age') !== false) :
        case (stripos($this->input, 'oldest') !== false) :
        case (stripos($this->input, 'event horizon') !== false) :
            if ($this->extracted_number == false) {

            } else {
                $this->number = null;
                $this->event_horizon = $this->extracted_number;
                $this->response .= "Set event horizon to " . $this->event_horizon . ". ";

                return;
            }


        default:
        }




        $status = true;

        return $status;
    }


    /**
     *
     */
    function makeWeb() {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/number';

        $this->node_list = array("number"=>array("number"));

        $embedded = true;
        if (!$embedded) {
            $web = '<a href="' . $link . '">';
            $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/number.png">';
            $web .= "</a>";
        } else {

            $web = '<a href="' . $link . '">';
            $web .= $this->image_embedded;
            $web .= "</a>";
        }
        $web .= "<br>";

        $web .= "number graph";

        $web .= "<br><br>";



        $web .= "<br>";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';

        $web .= 'Request is "' . $this->subject . '".<br>';

        if (!isset($this->numbers_history[0])) {
            $web .= "No numbers found<br>";
        } else {
            $y_max = "X";
            if ((isset($this->chart_agent->y_max)) and ($this->chart_agent->y_max != false)) {$y_max = $this->chart_agent->y_max;}
            $web .= "Biggest seen number is ". $y_max;

            $web .= "<br>";

            $y_min = "X";

            if ((isset($this->chart_agent->y_min)) and ($this->chart_agent->y_min != false)) {$y_min = $this->chart_agent->y_min;}
            $web .= "Smallest seen number is ". $y_min . "<br>";

            $number = "X";

            if ((isset($this->numbers_history[0]['number'])) and ($this->numbers_history[0]['number'] != false)) {$number = $this->numbers_history[0]['number'];}


            $web .= "Latest number is ". $number . "<br>";
            $web .= "<br>";
            $web .= "Latest " . $this->web_return_max . " extracted numbers are:<br>";
        }
        $i= 0;
        foreach ($this->numbers_history as $key=>$number) {
            $i += 1;
            if ($i >= $this->web_return_max) {break;}
            $web .= $number['timestamp'] . " " .$number['calling_agent'] . " " .$number['number'];
            $link = $this->web_prefix . "thing/". $number['uuid'] . "/forget";

            $web .= " ";
            $web .= '<a href="' . $link . '">Forget</a>';


            $web .= "<br>";
        }

        if ($this->recognize_french == true) {
            //if (count($this->numbers) == $this->test_count) {
            //https://french.kwiziq.com/revision/grammar/how-to-write-decimal-numbers-in-french
            //    $web .= "Found all the numbers.  Excluding the french format.";
            //}
        }

        //   $web .= 'The last agent to run was the ' . ucwords($this->prior_agent) . ' Agent.<br>';


        //$web .= "<a href='"  . $link . "'>" . $link . "</a>";
        //$web .= "<br>";
        //$link = "https://en.wikipedia.org/wiki/Universally_unique_identifier";
        //$web .= "<a href='"  . $link . "'>" . $link . "</a>";

        $web .= "<br>";

        //        $web .= $this->help . "<br>";

        $this->thing_report['web'] = $web;
    }


    /**
     *
     */
    function makeSMS() {

        $sms = "NUMBER | ";

        if ((isset($this->number)) and ($this->number != false)) {       $sms .= $this->number ." | ";}
        $sms .= $this->response;
        $sms .= ' #devstack';

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create("number", $this->node_list, "number");

        $choices = $this->thing->choice->makeLinks("number");
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }


    /**
     *
     * @return unknown
     */
    public function makePNG() {

        if (!isset($this->image)) {return true;}
        $this->chart_agent->makePNG();
        $this->image_embedded = $this->chart_agent->image_embedded;
        $this->thing_report['png'] = $this->chart_agent->thing_report['png'];

    }


}
