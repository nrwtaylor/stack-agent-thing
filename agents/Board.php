<?php
namespace Nrwtaylor\StackAgentThing;

// devstack

class Board extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doBoard();
        $this->readBoard();

// Test
$this->writeBoard();
    }

    public function get()
    {
        $this->getBoard();
    }

    public function set()
    {
        $this->setBoard();
    }


    /**
     *
     */
    public function setBoard()
    {
//        $this->lattice_agent->setLattice();
//        $this->board = $this->lattice_agent->lattice;

$this->decimalBoard();

//        $this->decimal_board += 1;
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["board", "decimal"],
            $this->decimal_board
        );

        $this->thing->log(
            $this->agent_prefix .
                ' saved decimal board ' .
                $this->decimal_board .
                '.',
            "INFORMATION"
        );
    }

    /**
     *
     * @return unknown
     */
    public function decimalBoard()
    {
        $s = "";
        $board_points = [];
        $i = 0;
        foreach (range(-1, 1, 1) as $j) {
            foreach (range(-1, 1, 1) as $k) {
                $board_points[] = $this->board[$i][$j][$k]['state'];
            }
        }

        foreach ($board_points as $point) {
            $s .= $point;
        }
        $this->decimal_board = bindec($s);
        return $this->decimal_board;
    }

    public function doBoard()
    {
        $this->getBoard();
        $this->response .= "Got board. ";
    }

    public function binaryBoard($dec)
    {
        if ($dec == null) {
            $dec = $this->decimal_board;
        }

        $Input = $dec;
        $Output = '';
        if (preg_match("/^\d+$/", $Input)) {
            while ($Input != '0') {
                $Output .= chr(48 + ($Input[strlen($Input) - 1] % 2));
                $Input = bcdiv($Input, '2');
            }
            $Output = strrev($Output);
        }

        $this->binary_board = $Output;
        //      $this->binary_snowflake= decbin($dec);
        return $this->binary_board;
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "board");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    public function readBoard()
    {
        $i = 0;
        $j = 1;
        $k = 1;

$this->response .= "Read board " . $this->board[$i][$j][$k]['state'] . ". ";

        //$lattice_agent->lattice[$i][$j][$k]['name'] = "Mark";
        //$lattice_agent->lattice[$i][$j][$k]['state'] = true;
        //$lattice_agent->lattice[$i][$j][$k]['value'] = 1;
    }

    public function writeBoard()
    {
        $i = 0;
        $j = 1;
        $k = 1;

$random_boolean = rand(0,1) == 1;
$this->response .= "Random boolean " . $random_boolean . ". ";
        $this->board[$i][$j][$k]['state'] = $random_boolean;
$this->setBoard();
$this->response .= "Wrote board " . $this->board[$i][$j][$k]['state'] . ". ";
        //$lattice_agent->lattice[$i][$j][$k]['name'] = "Mark";
        //$lattice_agent->lattice[$i][$j][$k]['state'] = true;
        //$lattice_agent->lattice[$i][$j][$k]['value'] = 1;
    }


    // -----------------------

    public function getBoard()
    {
//        if (!isset($this->board)) {
//            $this->lattice_agent = new Lattice($this->thing, "lattice");
//            $this->lattice_agent->getLattice();
//            $this->board = $this->lattice_agent->lattice;
//        }

        $this->thing->json->setField("variables");
        $this->decimal_board = $this->thing->json->readVariable([
            "board",
            "decimal",
        ]);

        if ($this->decimal_board == false) {
            $this->response .= "Did not find a decimal board. ";

            $this->thing->log(
                $this->agent_prefix . ' did not find a decimal board.',
                "INFORMATION"
            );
            //$this->board = false;
            // No snowflake saved.  Return.
            return true;
        }

        //        $this->max = 12;
        //        $this->size = 3.7;
        //        $this->lattice_size = 15;

        //        $this->binarySnowflake($this->decimal_snowflake);

        //$lattice_agent = new Lattice($this->thing, "lattice");
        //$t = $lattice_agent->lattice;

        //$this->board = $t;

        $this->binaryBoard($this->decimal_board);
$this->response .= "Got ". $this->binary_board . ". ";
$i=0;
foreach(range(-1,1,1) as $j) {
foreach(range(-1,1,1) as $k) {
$count = 0;
$b = substr($this->binary_board, $count, 1);
//var_dump($b);
    //$this->board[$i][$j][$k]['value'] = rand();
    $this->board[$i][$j][$k]['state'] = $b;
$count += 1;


}
}

        $this->thing->log(
            $this->agent_prefix .
                ' loaded decimal board ' .
                $this->decimal_board .
                '.',
            "INFORMATION"
        );
        return;
    }

    public function makeWeb()
    {
        // Make a web html representation of the board.
        // Start with TXT.

        $this->response .= "Board is " . $this->decimal_board;

        $this->makeTXT();
        $web = nl2br($this->thing_report['txt']);

        //var_dump($this->board);

        //}

        $web .= "<p>";
        $web .= $this->response;

        $this->thing_report['web'] = $web;
    }

    public function makeTXT()
    {
        // Make a web html representation of the board.
        // Start with TXT.
        $text = "A BOARD\n";
        //$this->response .= "Board is " . $this->decimal_board;
        //foreach(range(-1,0,1) as $i) {
        $i = 0;
        foreach (range(-1, 1, 1) as $j) {
            foreach (range(-1, 1, 1) as $k) {
                $thing = $this->board[$i][$j][$k];

                $text .=
                    " state " .
                    $thing['state'] .
                    " ";
            }
            $text .= "\n";
        }
        $text .= "\n";

        //}
        //var_dump($this->board);

        //}

        $this->thing_report['txt'] = $text;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a board. You put things on a board.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $link = $this->web_prefix . "/thing/" . $this->uuid . "/board";

        $this->node_list = ["board" => ["board"]];
        $message = "No message provided.";
        if (isset($this->message)) {
            $message = $this->message;
        }
        $sms = "BOARD | " . $message . $this->response;
        $sms .=
            "decimal_board " . $this->textBoard($this->decimal_board) . ". ";
        $sms .= $link;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function textBoard($board = null)
    {
        $text = $board;
        return $text;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "board");
        $choices = $this->thing->choice->makeLinks('board');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        $this->response .= "Ignored subject. ";
        return false;
    }
}
