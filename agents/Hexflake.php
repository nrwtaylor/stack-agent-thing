<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Hexflake extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->test = "Development code";

        $this->node_list = ["hexflake" => ["hexflake", "uuid"]];

        $this->lattice_size = 10;
    }

    public function get()
    {
        $this->snowflake_agent = new Snowflake(
            $this->thing,
            "snowflake hex wall"
        );

        $this->getHexflake();

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "hexflake",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["hexflake", "refreshed_at"],
                $time_string
            );
        }
    }

    public function set()
    {
        $this->setHexflake();
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report["info"] = "This creates a hexflake.";
        $this->thing_report["help"] =
            "This is about hexagons. Alias HEX WALL SNOWFLAKE.";

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "hexflake"
        );

        $choices = $this->thing->choice->makeLinks("hexflake");
        $this->thing_report["choices"] = $choices;
    }

    function makePDF()
    {
        $pdf = $this->snowflake_agent->thing_report["pdf"];
        $this->thing_report["pdf"] = $pdf;
    }

    function makeSMS()
    {
        $cell = $this->lattice[0][0][0];
        $sms = "HEXFLAKE | cell (0,0,0) state " . strtoupper($cell["state"]);

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeMessage()
    {
        $message = "Stackr made a hexflake for you.<br>";

        $uuid = $this->uuid;

        $message .=
            "Keep on stacking.\n\n<p>" .
            $this->web_prefix .
            "thing/$uuid/hexflake.png\n \n\n<br> ";
        $message .=
            '<img src="' .
            $this->web_prefix .
            "thing/" .
            $uuid .
            '/hexflake.png" alt="hexflake" height="92" width="92">';

        $this->thing_report["message"] = $message;
    }

    function setHexflake()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["hexflake", "lattice"],
            $this->lattice
        );
    }

    function getHexflake()
    {
        $n = 2;
        $this->thing->json->setField("variables");
        $this->lattice = $this->thing->json->readVariable([
            "hexflake",
            "lattice",
        ]);

        if ($this->lattice == false) {
            $this->initLattice($n);
        }
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/agent";

        $this->node_list = ["web" => ["hexflake"]];
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "web"
        );
        $choices = $this->thing->choice->makeLinks("web");

        $head = '
<td>
<table border="0" cellpadding="0" cellspacing="0" style="background-color:#FFFFFF; border-bottom:0; border-radius:10px">
<tr>
<td align="center" valign="top">
<div padding: 5px; text-align: center">';

        $foot = "</td></div></td></tr></tbody></table></td></tr>";

        $web = '<a href="' . $link . '">';
        $web .=
            '<img src= "' .
            $this->web_prefix .
            "thing/" .
            $this->uuid .
            '/hexflake.png">';
        $web .= "</a>";
        $web .= "<br>";

        $web .= $this->sms_message;

        $web .= "<br><br>";
        $web .= $head;
        $web .= $choices["button"];
        $web .= $foot;

        $this->thing_report["web"] = $web;
    }

    function makeTXT()
    {
        $txt = "This is a HEXFLAKE";
        $txt .= "\n";
        $txt .= count($this->lattice) . " cells retrieved.";

        $txt .= "\n";
        $txt .= str_pad("COORD (Q,R,S)", 15, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("NAME", 10, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("STATE", 10, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("VALUE", 10, " ", STR_PAD_LEFT);

        $txt .= " " . str_pad("COORD (X,Y)", 6, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";

        // Centre framed on 0,0,0
        $q_array = [-2, -1, 0, 1, 2];
        $r_array = [-2, -1, 0, 1, 2];
        $s_array = [-2, -1, 0, 1, 2];

        // Run the lattice update/display loops
        foreach ($q_array as $q) {
            foreach ($r_array as $r) {
                foreach ($s_array as $s) {
                    //$cell = $this->lattice[$q][$r][$s];
                    $cell = $this->getCell($q, $r, $s);

                    $txt .=
                        " " .
                        str_pad(
                            "(" . $q . "," . $r . "," . $s . ")",
                            15,
                            " ",
                            STR_PAD_LEFT
                        );

                    $txt .= " " . str_pad($cell["name"], 10, " ", STR_PAD_LEFT);
                    $txt .=
                        " " . str_pad($cell["state"], 10, " ", STR_PAD_LEFT);
                    $txt .=
                        " " . str_pad($cell["value"], 10, " ", STR_PAD_RIGHT);
                    $txt .= "\n";
                }
            }
        }

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    public function makePNG()
    {
        $image = $this->snowflake_agent->thing_report["png"];

        $this->PNG = $image;
        $this->thing_report["png"] = $image;
    }

    function initLattice($n)
    {
        $this->lattice_size = $n;

        foreach (range(-$n, $n) as $i) {
            $q_array[$i] = null;
            $r_array[$i] = null;
            $s_array[$i] = null;
        }

        //$value=null;
        $value = ["name" => null, "state" => null, "value" => 0];

        foreach (range(-$n, $n) as $q) {
            foreach (range(-$n, $n) as $r) {
                foreach (range(-$n, $n) as $s) {
                    $this->lattice[$q][$r][$s] = $value;
                }
            }
        }

        $this->lattice[-1][0][0] = [
            "name" => "seed",
            "state" => "on",
            "value" => 0.5,
        ];
        $this->lattice[0][0][0] = [
            "name" => "seed",
            "state" => "on",
            "value" => 0.5,
        ];
        $this->lattice[2][2][2] = [
            "name" => "seed",
            "state" => "on",
            "value" => 0.5,
        ];
    }

    function getCell($q, $r, $s)
    {
        // $cell = true;

        if (
            $q > $this->lattice_size or
            $q < -$this->lattice_size or
            $r > $this->lattice_size or
            $r < -$this->lattice_size or
            $s > $this->lattice_size or
            $s < -$this->lattice_size
        ) {
            $cell = ["name" => "boundary", "state" => "off", "value" => 0]; // red?
        } else {
            if (isset($this->lattice[$q][$r][$s])) {
                $cell = $this->lattice[$q][$r][$s];
            } else {
                // Flag an error;
                $cell = ["name" => "bork", "state" => "off", "value" => true];
            }
        }

        return $cell;
    }

    function updateCell($q, $r, $s)
    {
        // Process the cell;
        // Because CA is 3D spreadsheets.
        //$q_array= array(-1,1);
        //$r_array= array(-1,1);
        //$s_array= array(-1,1);

        //$cell_value = 0;

        // Build a list of the state of the surrounding cells.

        $cell = $this->getCell($q, $r, $s);

        $states = [];
        $i = 0;
        foreach (range(-1, 1, 2) as $q_offset) {
            foreach (range(-1, 1, 2) as $r_offset) {
                foreach (range(-1, 1, 2) as $s_offset) {
                    $neighbour_cell = $this->getCell(
                        $q + $q_offset,
                        $r + $r_offset,
                        $s + $s_offset
                    );

                    if ($neighbour_cell["state"] == "on") {
                        $states[$i] = 1;
                    } else {
                        $states[$i] = 0;
                    }
                    $i += 1;
                }
            }
        }

        // Perform some calculation here on $states,
        // to determine what state the current cell should be in.

        list($n, $p_melt, $p_freeze) = $this->getProb(
            $states[0],
            $states[1],
            $states[2],
            $states[3],
            $states[4],
            $states[5]
        );

        if ($p_melt < $p_freeze) {
            $cell["state"] = "on";
        }
        // Then set lattice value
        $this->lattice[$q][$r][$s] = $cell;
    }

    function readHexflake()
    {
        //$this->get();
    }

    public function readSubject()
    {
    }
}
