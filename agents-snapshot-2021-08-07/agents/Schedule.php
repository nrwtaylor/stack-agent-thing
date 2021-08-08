<?php
/**
 * Wumpus.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

use setasign\Fpdi;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Schedule extends Agent
{
    public $var = 'hello';

    // Lots of work needed here.
    // Currently has persistent coordinate movement (north, east, south, west).
    // State selection is dev.

    // Add a place array. Base it off a 20-node shape.
    // Get path selecting throught the array for Wumpus and Player(s) working.


    /**
     *
     */
    function init() {
        $this->agent_name = "schedule";
        $this->test= "Development code";

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->node_list = array("schedule"=>array("schedule"));
        $info = 'The "Schedule" agent makes sure certain things are done. ';
    }

    /**
     *
     */
    public function run() {
///        $this->getWumpus();
        //        $this->getClocktime();
        //        $this->getBar();
        //$this->getCoordinate();
//        $this->getState();
//$this->getBottomlesspits();

$this->doSchedule();
    }


    /**
     *
     */
    public function set() {
    }


    /**
     *
     * @param unknown $crow_code (optional)
     * @return unknown
     */
    public function get($crow_code = null) {
    }


    /**
     *
     */
    public function loop() {

    }


    /**
     *
     */
    private function getSchedule() {
        //if (isset($this->cave_names)) {return;}

$this->event_agent = new Event($this->thing, "event");
$this->runat_agent = new Runat($this->thing, "event");

        // Makes a one character dictionary

        $file = $this->resource_path . 'schedule/schedule.txt';
        $contents = file_get_contents($file);


        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {
            $items = explode(",", $line);
            $event_day = $items[0];
            $event_modifier = $items[1];
            $event_time = $items[2];
            $event_name = $items[3];
$this->event_agent->makeEvent(null, $event_name);

$this->runat_agent->extractRunat($event_day . " " . $event_modifier . " " . $event_time);

//var_dump($event_name);
//            break;

            // do something with $line
            $line = strtok( $separator );
        }

    }

    public function respond() {
        $this->thing->flagGreen();

//        $to = $this->thing->from;
//        $from = "camper";

      $this->makeSMS();
//        $this->makeChoices();

//        $this->thing_report["info"] = "This is a camper in a park with a picnic basket.";
//        $this->thing_report["help"] = "This is finding picnics. And getting your friends to join you. Text BEAR. Or RANGER.";

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;

        return $this->thing_report;
    }



    private function getInject() {
        //if (isset($this->cave_names)) {return;}

        // Makes a one character dictionary

        $file = $this->resource_path . 'vancouverparksboard/queen_elizabeth_park.txt';
        $contents = file_get_contents($file);


        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {
            //$items = explode(",", $line);
            //$this->injects[] = $line;

if (substr($line,0,1) != "#") {$this->injects[] = $line;}
if ($line == "# places") {break;}

//if (substr($line,0,2) == "//") {continue;}

            //break;

            // do something with $line
            $line = strtok( $separator );

        }

            $k = array_rand($this->injects);
            $v = $this->injects[$k];

$this->inject = $v;

    }

function getLibrex($text) {

$librex_agent = new Librex($this->thing, "vancouverparksboard/queen_elizabeth_park");
//$librex_agent->getMatches($this->input, $text);

// test
//$text = "fountain";


$librex_agent->getMatch($text);

//echo "matching " . $text .".\n";
//var_dump($librex_agent->matches);
//var_dump($librex_agent->response);
//var_dump($librex_agent->best_match);

$this->librex_response = $librex_agent->response;
$this->librex_best_match = $librex_agent->best_match;

//return($librex_agent->best_match);
return $librex_agent->response;
}


   private function getPlace($number = null) {
        //if (isset($this->cave_names)) {return;}

        // Makes a one character dictionary

        $file = $this->resource_path . 'vancouverparksboard/queen_elizabeth_park.txt';
        $contents = file_get_contents($file);


        $separator = "\r\n";
        $line = strtok($contents, $separator);
$place_flag = false;
        while ($line !== false) {
//var_dump($line);
            //$items = explode(",", $line);
            //$this->injects[] = $line;
//if ((substr($line,0,8) != "# places") and ($place_flag == false)) {continue;}
if ($line == "# places") {$place_flag = true;}
if ($place_flag == false) {
    $line = strtok( $separator );
    continue;
}

if (substr($line,0,1) != "#") {
$t = explode(",", $line);
//var_dump($t);
if (!isset($t[2])) {$t[2] = null;}
if (!isset($t[1])) {$t[1] = null;}
if (!isset($t[3])) {$t[3] = null;}

$this->places[$t[0]] = array("place_name"=>trim($t[1]),"link"=>trim($t[3]),"text"=>trim($t[2]));

}
//echo $line;

//if (substr($line,0,2) == "//") {continue;}

            //break;

            // do something with $line
            $line = strtok( $separator );

        }

//            $k = array_rand($this->injects);
//            $v = $this->injects[$k];

$this->place = $this->places[$number];
//var_dump($this->place);
    }


    private function getClocktime() {
        $this->clocktime_agent = new Clocktime($this->thing, "clocktime");
    }


    /*
    private function getCoordinate()
    {
        $this->coordinate = new Coordinate($this->thing, "coordinate");

        $this->x = $this->coordinate->coordinates[0]['coordinate'][0];
        $this->y = $this->coordinate->coordinates[0]['coordinate'][1];

    }
*/

    /**
     *
     */
    private function getBar() {
        $this->thing->bar = new Bar($this->thing, "bar stack");
    }


    /**
     *
     */
    private function getTick() {
        $this->thing->tick = new Tick($this->thing, "tick");
    }


    /**
     *
     */
/*
    public function respond() {
        // Thing actions
        $this->thing->flagGreen();

        // Generate SMS response

        $to = $this->thing->from;
        $from = "queenelizabethpark";


        //$this->makeChoices();
        $this->choices = false;
        $this->makeMessage();
        $this->makeSMS();

        $this->makeWeb();

        //if ($this->agent_input == null) {
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        //}

        $this->makePDF();

        $this->thing_report['help'] = 'This is the "Park" Agent with information about the park.' ;
    }
*/

    /**
     *
     */
    public function makeWeb() {
        //        return;
        // No web response for now.
        //        $test_message = "<b>WUMPUS " . strtoupper($this->thing->nuuid) . "" . ' NOW ';
        $test_message = "<b>SCHEDULE ";


        $test_message .= "</b><p>";

        //$test_message .= "".  nl2br($this->sms_message);
        $test_message .= "SCHEDULE INFORMATION";
        $test_message .= "<p>";
        $test_message .= $this->response;
        $test_message .= "<br>";



        $test_message .= "PDF ";

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/schedule.pdf';
        $test_message .= '<a href="' . $link . '">schedue.pdf</a>';
        //$web .= " | ";

        $test_message .="<br>";
        $test_message .= "<p>";

        //$this->response = "";
        //$this->getCave();

        trim($this->response);

//        $refreshed_at = max($this->created_at, $this->created_at);
//        $test_message .= "<p>";
        //        $ago = $this->thing->human_time ( strtotime($this->thing->time()) - strtotime($refreshed_at) );
//        $ago = $this->thing->human_time ( strtotime($this->entity_agent->time()) - strtotime($refreshed_at) );

//        $test_message .= "<br>Thing happened about ". $ago . " ago.";

        //$test_message .= '<br>' .$this->whatisthis[$this->state] . '<br>';

        //$this->thing_report['sms'] = $this->message['sms'];
        $this->thing_report['web'] = $test_message;
    }


function doSchedule() {

}

    /**
     *
     */
    public function makeChoices() {
    }


    /**
     *
     */
    public function makeMessage() {
        if (isset($this->response)) {$m = $this->response;} else {$m = "No response.";};
        $this->message = $m;
        $this->thing_report['message'] = $m;
    }


    /**
     *
     */
/*
    public function makeSMS() {

        //$this->makeChoices();

        //$this->choices_text = $this->thing->choice->current_node;
        //     if ($this->choices['words'] != null) {
        //         $this->choices_text = strtoupper(implode(" / " ,$this->choices['words']));
        //     }

//        $sms = "WUMPUS " . strtoupper($this->wumpus_tag) .  "";
//var_dump($this->response);
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/queenelizabethpark.pdf';
        $sms = "PARK | ";

if (stripos($this->response, 'join us') !== false) {
        $sms .= $link . " Made a link to a map. ";
}


        $sms .= $this->response;

        $this->choices_text = "CONTROL VE7RVF";


        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }
*/

    public function makeSMS() {
//var_dump($this->response);
        $this->node_list = array("schedue"=>array("schedule"));
        $m = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }

    /**
     *
     * @return unknown
     */
    public function makePDF() {
        $txt = $this->thing_report['sms'];

        // initiate FPDI
        $pdf = new Fpdi\Fpdi();


        // http://www.percs.bc.ca/wp-content/uploads/2014/06/PERCS_Message_Form_Ver1.4.pdf
        //       $pdf->setSourceFile($this->resource_path . 'percs/PERCS_Message_Form_Ver1.4.pdf');
        $pdf->setSourceFile($this->resource_path . 'schedule/schedule.pdf');

        $pdf->SetFont('Helvetica', '', 10);

        $tplidx1 = $pdf->importPage(1, '/MediaBox');

        $s = $pdf->getTemplatesize($tplidx1);

        $pdf->addPage($s['orientation'], $s);
        // $pdf->useTemplate($tplidx1,0,0,215);
        $pdf->useTemplate($tplidx1);

        $pdf->SetTextColor(0, 0, 0);

        //        $text = "Inject generated at " . $this->thing->thing->created_at. ".";
        //        $pdf->SetXY(130, 10);
        //        $pdf->Write(0, $text);

        $image = $pdf->Output('', 'S');
        //var_dump($image);
        $this->thing_report['pdf'] = $image;

        return $this->thing_report['pdf'];
    }


    /**
     *
     * @return unknown
     */
//    public function readSubject() {
//        $this->response = null;
//
//        if ($this->state == null) {
//            $this->getWumpus();
//        }

    public function readSubject() {
        $this->response = null;

        $input = strtolower($this->subject);

// Let's see if there is a number between 1 and 28
//var_dump($input);
        $number = new Number($this->thing, "number");
        $number->extractNumbers($input);
        $number->extractNumber();
//var_dump($number->number);

        if ( (isset($number->number)) and ($number->number != 0)) {

            $this->getPlace($number->number);

//$t = implode($this->place," ");

            $this->response .= "Place " . $number->number . " is " . $this->place['place_name'] .". ";
            if ((isset($this->place['link'])) and ($this->place['link'] != null)) {$this->response .= $this->place['link'] . " ";}
            if ((isset($this->place['text'])) and ($this->place['text'] != null)) {$this->response .= $this->place['text'] . " ";}
            return;
        }

        if ($input != "schedule") {
            //var_dump($input);
            $text = $input;

            $t = new Compression($this->thing, "compression queen elizabeth park");

        //$bear_name = "ted";
        //$bear_response = "Quiet.";
//$min_lev = 1e99;
            foreach($t->agent->matches as $type=>$strip_words) {
                foreach($strip_words as $i=>$strip_word){
//if (!isset($strip_word['words'])) {var_dump($strip_word); exit();}

                    $strip_word = $strip_word['words'];

                    $whatIWant = $input;
                    if (($pos = strpos(strtolower($input), $strip_word. " is")) !== FALSE) {
                        $whatIWant = substr(strtolower($input), $pos+strlen($strip_word . " is"));
                    } elseif (($pos = strpos(strtolower($input), $strip_word)) !== FALSE) {
                        $whatIWant = substr(strtolower($input), $pos+strlen($strip_word));
                    }

                    $input = $whatIWant;
                }
            }
            $input = trim($input);
            //var_dump($input);
            $park_response = "";
/*
            if ($input != "") {
                $t = $this->getLibrex($input);
                if ($this->librex_best_match != null) {
                    $this->response = ucwords($this->librex_best_match['words']) . ". " . ucfirst($this->librex_best_match['english']);
                    return;
                }

                $this->getInject();
                $this->response = $this->inject;
                return;

            }
*/
        }
//var_dump($this->response);
//$librex_agent = new Librex($this->thing, "vancouverparksboard/queen_elizabeth_park");
//var_dump($librex_agent->matches);

        // Accept wumpus commands
        $this->keywords = array("schedule");

        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key=>$piece) {
            $ngram_list[] = $piece;
        }

        foreach ($pieces as $key=>$piece) {
            if (isset($last_piece)) {
                $ngram_list[] = $last_piece . " " . $piece;
            }
            $last_piece = $piece;
        }

        foreach ($pieces as $key=>$piece) {
            if ( (isset($last_piece)) and (isset($last_last_piece))) {
                $ngram_list[] = $last_last_piece . " " . $last_piece . " " . $piece;
            }
            $last_last_piece = $last_piece;
            $last_piece = $piece;
        }
        //$this->getCoordinate();
//$park_response = "";
        foreach ($ngram_list as $key=>$piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {

                    switch ($piece) {
                    case 'schedule':
                        $this->getSchedule();
                        $schedule_response = $this->schedule;
                        //$this->response .= "May 18th is a Wumpus hunt at Queen Elizabeth Park. ";

                        break;
                   }
                }
            }
        }

        return false;
    }

    /**
     *
     */
    function start() {
    }


}
