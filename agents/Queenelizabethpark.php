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

class Queenelizabethpark extends Agent
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
        $this->test= "Development code";

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';


        $this->node_list = array("start"=>array("inside nest"=>array("nest maintenance"=>array("patrolling"=>"foraging", "foraging")), "midden work"=>"foraging"));

        $info = 'The "Queen Elizabeth Park" agent provides a link to a map. ';

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

$this->doMap();
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
    private function getNews() {
        //if (isset($this->cave_names)) {return;}

        // Makes a one character dictionary

        $file = $this->resource_path . 'wumpus/news.txt';
        $contents = file_get_contents($file);


        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {
            $items = explode(",", $line);
            $this->news = $items[2];
            break;

            // do something with $line
            $line = strtok( $separator );
        }

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


    /**
     *
     */
    public function makeWeb() {
        //        return;
        // No web response for now.
        //        $test_message = "<b>WUMPUS " . strtoupper($this->thing->nuuid) . "" . ' NOW ';
        $test_message = "<b>QUEEN ELIZABETH PARK ";


        $test_message .= "</b><p>";

        //$test_message .= "".  nl2br($this->sms_message);
        $test_message .= "YOUR CHOICES ARE";
        $test_message .= "<p>";


        $test_message .= "PDF ";

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/wumpus.pdf';
        $test_message .= '<a href="' . $link . '">wumpus.pdf</a>';
        //$web .= " | ";


        $test_message .="<br>";
        $test_message .= "<p>";



        $this->response = "";
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


function doMap() {

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
    public function makeSMS() {

        //$this->makeChoices();

        //$this->choices_text = $this->thing->choice->current_node;
        //     if ($this->choices['words'] != null) {
        //         $this->choices_text = strtoupper(implode(" / " ,$this->choices['words']));
        //     }

//        $sms = "WUMPUS " . strtoupper($this->wumpus_tag) .  "";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/queenelizabethpark.pdf';
        $sms = "PARK | " . $link . " Made a link to a map. ";


        $sms .= $this->response;

        $this->choices_text = "CONTROL VE7RVF";


        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
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
        $pdf->setSourceFile($this->resource_path . 'wumpus/wumpus.pdf');

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

        // Accept wumpus commands
        $this->keywords = array("teleport", "look", "news", "forward", "north", "east", "south", "west", "up", "down", "left", "right", "wumpus", "meep", "thing", "start", "meep", "spawn");

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
$park_response = "";
        foreach ($ngram_list as $key=>$piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {

                    switch ($piece) {
                    case 'news':
                        $this->getNews();
                        $park_response = $this->news;
                        //$this->response .= "May 18th is a Wumpus hunt at Queen Elizabeth Park. ";
                        break;

                    case 'look':
                        $this->getCave($this->x);
                        $park_response = "You see " . $this->cave_name . ". ";
                        break;

                    case 'west':
                    case 'south':
                    case 'east':
                    case 'north':
                        $park_response = ucwords($piece) . "? ";
                        break;

                    case 'left':
                        $park_response = "You turned left. ";
                        break;
                    case 'right':
                        $park_response = "You turned right. ";
                        break;

                    case 'forward':
                        $this->left_count += 1;
                        $this->right_count += 1;
                        $park_response = "You bumped into the wall. ";
                        break;

                    case 'lair':
                        $park_response = "Lair. ";
                        break;

                    case 'meep':
                        $park_response = "Merp. ";
                        break;

                    case 'start':
                        $this->start();
                        //$this->thing->choice->Choose($piece);
                        $this->entity_agent->choice->Choose($piece);

                        $park_response = "Heard " . $this->state .". ";
                        break;

                    case 'teleport';
                    case 'spawn':
                        $this->spawn();
                        $this->response .= "Spawn. ";
                        break;
                   }
                }
            }
        }

if ($park_response == "") {$park_response = "Join us Saturday 20 July 1pm to 4.30pm at Queen Elizabeth Park. Contact VE7RVF control.";}

$this->response .= $park_response;
        return false;
    }

    /**
     *
     */
    function start() {
        $this->x = "X";
        $this->getWumpus();
        //$this->thing->choice->Create($this->primary_place, $this->node_list, "start");
        $this->response .= "Welcome player. Wumpus has started.";
        //$this->thing->flagGreen();
        $this->entity_agent->flagGreen();
    }


}