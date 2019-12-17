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
    function init()
        //function __construct(Thing $thing, $agent_input = null)
        {
        //  if ($agent_input == null) {$agent_input = '';}
        //  $this->agent_input = $agent_input;
        //        $this->agent_name = "number";


        // Given a "thing".  Instantiate a class to identify and create the
        // most appropriate agent to respond to it.
        //  $this->thing = $thing;

        //        $this->thing_report['thing'] = $this->thing->thing;

        // Get some stuff from the stack which will be helpful.
        //$this->web_prefix = $GLOBALS['web_prefix'];
        //        $this->web_prefix = $thing->container['stack']['web_prefix'];

        //  $this->stack_state = $thing->container['stack']['state'];
        //  $this->short_name = $thing->container['stack']['short_name'];

        // Create some short-cuts.
        //        $this->uuid = $thing->uuid;
        //        $this->to = $thing->to;
        //        $this->from = $thing->from;
        //        $this->subject = $thing->subject;
        //$this->sqlresponse = null;

        //$this->subject = "number ". "please 234,000 1,000,234 find (.3) the -1 20 503 numbers (34) 12.4 in here 12 if you can 12 / 4 = 100000 and some currencies perhaps $6 $23.90 but not $1.000, and in french €5.67";
        //$this->test_count = 15; // I think
        //$this->test_count = null;
        // a french test string "or 5€67 or 66,50 £ or 66,50£ or 20 000 $ 99 999 but more rarely 99.999 or 99.999.999 but how about - 12 432,20";

        //      $this->thing->log('<pre> Agent "Number" started running on Thing ' . date("Y-m-d H:i:s") . '</pre>');
        $this->node_list = array("number"=>
            array("number"));

        //    $this->current_time = $this->thing->time();


        //        $this->variables = new Variables($this->thing, "variables number " . $this->from);

        //$this->get();

        $this->aliases = array("learning"=>array("good job"));

        //  $this->readSubject();

        $this->recognize_french = true; // Flag error


        //        if ($this->agent_input == null) {
        //      $this->respond();
        //        }

        //        $this->set();

        //        $this->thing->log('Agent "Number" found ' . implode(" ",$this->numbers) .".");

        // Way to output test information to web page as a thing call
        // $this->thing->test(date("Y-m-d H:i:s"),'receipt','completed');

        $this->horizon = 99;
    }


    /**
     *
     */
    function get() {
        //echo "get";

        $this->number_agent = new Variables($this->thing, "variables number " . $this->from);

        $this->number = $this->number_agent->getVariable("number");
        $this->refreshed_at = $this->number_agent->getVariable("refreshed_at");

        $this->getCallingagent();
        // Extract calling agent name from class name.
        $agent_class_name = $this->calling_agent;
        $this->calling_agent_name = strtolower((array_reverse(explode('\\', $agent_class_name)))[0]);
    }


    /**
     *
     */
    function set() {
        //echo "set" . $this->number;
        //        $this->variables->setVariable("refreshed_at", $this->refreshed_at);

        //        $this->state = $requested_state;
        //        $this->refreshed_at = $this->current_time;

        //$this->variables = new Variables($this->thing, "variables number " . $this->from);

        $this->number_agent->setVariable("number", $this->number);
        $this->number_agent->setVariable("calling_agent_name", $this->calling_agent_name);



        //$this->nuuid = substr($this->variables_thing->variables_thing->uuid,0,4);
        //$this->variables_thing->setVariable("flag_id", $this->nuuid);

        $this->number_agent->setVariable("refreshed_at", $this->current_time);

        //        $city = new Variables($this->thing, "variables city " . $this->from);

        //        $city->setVariable("city_code", $this->city_code);
        //        $city->setVariable("city_name", $this->city_name);


    }



    /**
     *
     */
    public function makeChart() {

        if (!isset($this->numbers_history)) {$this->historyNumber();}
        $t = "NUMBER CHART\n";
        $points = array();
        $x_min = 1e99;
        $x_max = -1e99;

        $y_min = 1e99;
        $y_max = -1e99;


        foreach ($this->numbers_history as $i=>$number_object) {

            $created_at = $number_object['created_at'];
            $number = $number_object['created_at'];

            $points[$created_at] = $number;
            //$t .= $i . " " . $number['created_at'] . " " . $number['calling_agent'] . " " . $number['number'] . "\n";

            if ($created_at < $x_min) {$x_min = $created_at;}
            if ($created_at > $x_max) {$x_max = $created_at;}

            if ($number < $y_min) {$y_min = $number;}
            if ($number > $y_max) {$y_max = $number;}


        }


        $chart_agent = new Chart($this->thing, "chart");
        $chart_agent->points = $points;

        //var_dump($chart_agent->points);
        //exit();
        $chart_agent->x_min = $x_min;
        $chart_agent->x_max = $x_max;

        $chart_agent->y_min = $y_min;
        $chart_agent->y_max = $y_max;
        $chart_agent->width = 200;
        $chart_agent->height = 100;
        $chart_agent->blankImage();


        // devstack - start here next time
        //$chart_agent->drawGraph();
        //exit();
    }


    /**
     *
     * @return unknown
     */
    function historyNumber() {
        //     if (!isset($this->kaiju_address)) {$this->getAddress($this->thing->from);}
        //     if (!isset($this->kaiju_address)) {return;}

        //var_dump($this->kaiju_address);
        //exit();

        //     $this->kaiju_thing = new Thing(null);
        //     $this->kaiju_thing->Create($this->kaiju_address, "null", "s/ kaiju thing");

        $block_things = array();
        // See if a stack record exists.
        $findagent_thing = new Findagent($this->thing, 'number '. $this->horizon);
        $this->max_index =0;

        $match = 0;

        $link_uuids = array();
        $kaiju_messages = array();
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

            }

            $this->numbers_history[] = array("created_at"=>strtotime($refreshed_at), "calling_agent"=>$calling_agent,
                "number"=>$number);
        }
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
        foreach ($this->numbers_history as $i=>$number) {

            $t .= $i . " " . $number['created_at'] . " " . $number['calling_agent'] . " " . $number['number'] . "\n";

        }
        //if (!isset($this->thing_report['sms'])) {$this->makeSMS();}
        $this->thing_report['txt'] = $t;


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
     */
    /*
    public function respond() {
        // Thing actions

        $this->thing->json->setField("settings");
        $this->thing->json->writeVariable(array("number",
                "received_at"),  $this->thing->json->time()
        );

        $this->thing->flagGreen();

//        $from = $this->from;
//        $to = $this->to;

//        $subject = $this->subject;

        // Now passed by Thing object
//        $uuid = $this->uuid;
//        $sqlresponse = "yes";

        //$message = "Thank you here is a Number.<p>" . $this->web_prefix . "thing/$uuid\n$sqlresponse \n\n<br> ";
        //$message .= '<img src="' . $this->web_prefix . 'thing/'. $uuid.'/receipt.png" alt="thing:'.$uuid.'" height="92" width="92">';

//        $this->makeSMS();

        //$this->thing_report['email'] = array('to'=>$from,
        //   'from'=>'uuid',
        //   'subject'=>$subject,
        //   'message'=>$message,
        //   'choices'=>$choices);

        //$this->makePNG();

//        $this->makeChoices();



        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

//        $this->makeWeb();

//        $this->thing_report['thing'] = $this->thing->thing;

        $this->thing_report['help'] = "This extracts numbers from the datagram.";

  //      return;
    }
*/

    /**
     *
     * @return unknown
     */
    public function readSubject() {
        //var_dump($this->input);
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

        //$input = $this->input;
        //} elseif (explode(" ",$this->agent_input)[0] == "number") {
        //    $input = $this->agent_input;
        //}

        $this->extractNumbers($input);
        $this->extractNumber();

        //var_dump($this->number);

        if ($this->number == false) {

            $this->get();
        }

        //var_dump($input);
        // Then look for messages sent to UUIDS
        //$this->thing->log('Agent "Number" looking for UUID in address.');
        //    $uuid_thing = new Uuid($this->thing, 'uuid');

        //$pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";

        //if (preg_match($pattern, $this->to)) {
        //    $this->thing->log('Agent "Number" found a Number in address.');
        //    return;
        //}

        // Keyword
        //var_dump($input);
        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {

            if ($this->input == 'number') {
                //$this->getNumber();
                $this->response = "Last number retrieved.";
                //echo "meep";
                return;

            }
        }

        if ($this->input == "number chart") {

            $this->getNumbers();
            return;

        }

        $status = true;

        return $status;
    }


    /**
     *
     */
    function makeWeb() {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/uuid';

        $this->node_list = array("number"=>array("number", "thing"));

        $web = '<a href="' . $link . '">';
        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/uuid.png">';
        $web .= "</a>";

        $web .= "<br>";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';
        $web .= $this->subject . "<br>";

        if (!isset($this->numbers[0])) {
            $web .= "No numbers found<br>";
        } else {
            $web .= "First number is ". $this->numbers[0] . "<br>";
            $web .= "Extracted numbers are:<br>";
        }
        foreach ($this->numbers as $key=>$number) {
            $web .= $number . "<br>";
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
        //foreach ($this->numbers as $key=>$number) {
        //    $this->sms_message .= $number . " | ";
        //}
        $sms .= $this->number;
        //$this->sms_message .= 'devstack';

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
    /*
    public function makePNG() {
        $text = "thing:".$this->numbers[0];

        ob_clean();

        ob_start();

        QRcode::png($text, false, QR_ECLEVEL_Q, 4);

        $image = ob_get_contents();

        //header('Content-Type: image/png');
        //echo $image;
        //exit();

        ob_clean();

        $this->thing_report['png'] = $image;

        //echo $this->thing_report['png']; // for testing.  Want function to be silent.

        return $this->thing_report['png'];
    }
*/
    /*
    public function makeImage() {
        $text = "thing:".$this->numbers[0];

        ob_clean();

        ob_start();

        QRcode::png($text, false, QR_ECLEVEL_Q, 4);

        $image = ob_get_contents();

        //header('Content-Type: image/png');
        //echo $image;
        //exit();

        ob_clean();

        $this->image = $image;

    }
*/

}
