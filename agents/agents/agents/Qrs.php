<?php
namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Qrs extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->test = "Development code";

        $this->node_list = ["qrs" => ["index", "uuid"]];
    }

    public function get() {

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "qrs",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["qrs", "refreshed_at"],
                $time_string
            );
        }


    }


    public function run()
    {
        $this->initQrs();
        $this->initNuuids();

        $this->setNuuids();
    }

    function getQuickresponse($txt = "qr")
    {
        $agent = new Qr($this->thing, $txt);
        $this->quick_response_png = $agent->PNG_embed;
    }

    function initQrs()
    {
        if (!isset($this->min)) {
            $this->min = 1;
        }
        if (!isset($this->max)) {
            $this->max = 221;
        }
        if (!isset($this->size)) {
            $this->size = 4;
        }

        //$this->setProbability();
        // $this->setRules();
    }

    private function responseResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report["info"] = "This creates a duplicable number set.";
        $this->thing_report["help"] = 'Try "DUPLICABLE"';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeChoices()
    {
        $this->thing->log(
            $this->agent_prefix .
                'started makeChoices. Timestamp = ' .
                number_format($this->thing->elapsed_runtime()) .
                'ms.',
            "OPTIMIZE"
        );

        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "nuuids"
        );
        $this->thing->log(
            $this->agent_prefix .
                'completed create choice. Timestamp = ' .
                number_format($this->thing->elapsed_runtime()) .
                'ms.',
            "OPTIMIZE"
        );

        $this->choices = $this->thing->choice->makeLinks('nuuids');
        $this->thing->log(
            $this->agent_prefix .
                'completed makeLinks. Timestamp = ' .
                number_format($this->thing->elapsed_runtime()) .
                'ms.',
            "OPTIMIZE"
        );

        $this->thing_report['choices'] = $this->choices;

        //  $this->thing_report['choices'] = false;
    }

    function makeSMS()
    {
        $sms = "QRS | ";
        $sms .= $this->web_prefix . "thing/" . $this->uuid . "/qrs.pdf";
        $sms .= " | TEXT QR";
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeMessage()
    {
        $message = "Stackr made a non-duplicable index for you.<br>";

        $uuid = $this->uuid;

        $message .=
            "Keep on stacking.\n\n<p>" .
            $this->web_prefix .
            "thing/$uuid/qrs\n \n\n<br> ";

        $this->thing_report['message'] = $message;
    }

    function setNuuids()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(["qrs", "index"], $this->index);
        //$this->thing->log($this->agent_prefix . ' saved duplicable index ' . $this->index . '.', "INFORMATION") ;
    }

    function getNuuids()
    {
        $this->thing->json->setField("variables");
        $this->index = $this->thing->json->readVariable(["qrs", "index"]);

        if ($this->index == false) {
            $this->thing->log(
                $this->agent_prefix . ' did not find a duplicable index.',
                "INFORMATION"
            );
            // Return.
            return true;
        }

        $this->thing->log(
            $this->agent_prefix . ' loaded qrs index ' . $this->index . '.',
            "INFORMATION"
        );
        return;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';
        $this->node_list = ["web" => ["nuuids", "nuuid"]];

        $web = "";

        //$web = '<a href="' . $link . '">';
        //$web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png">';
        //$web .= "</a>";
        $i = 0;
        foreach ($this->index as $key => $value) {
            $web .= $value . "<br>";
            if ($i == 10) {
                break;
            } else {
                $i += 1;
            }
        }

        $web .= "<br>";

        $web .= "<br><br>";
        $this->thing_report['web'] = $web;
    }

    function makeTXT()
    {
        $txt = "This is an index of semi-unique NUUIDS.\n";
        $txt .= 'Duplicate NUUIDs omitted.';
        $txt .= "\n";
        //$txt .= count($this->lattice). ' cells retrieved.';

        $txt .= "\n";
        //$txt .= str_pad("INDEX", 15, ' ', STR_PAD_LEFT);
        //$txt .= " " . str_pad("DUPLICABLE", 10, " ", STR_PAD_LEFT);
        //$txt .= " " . str_pad("STATE", 10, " " , STR_PAD_RIGHT);
        //$txt .= " " . str_pad("VALUE", 10, " ", STR_PAD_LEFT);

        //$txt .= " " . str_pad("COORD (X,Y)", 6, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";

        $num_rows = 40;
        $num_columns = 10;
        $offset = 0;
        $page = 1;
        $i = 1;

        $this->duplicables_list = [];
        foreach (range($this->min, $this->max) as $index) {
            if ($this->index[$index] == false) {
                continue;
            }

            $this->duplicables_list[$i] = $this->index[$index];
            $i += 1;
            $max_i = $i;
        }

        $i = 0;
        $blanks = true;
        if ($blanks) {
            $max_i = $this->max;
        }

        $num_pages = ceil($this->max / ($num_rows * $num_columns));
        while ($i < $max_i) {
            $txt .= "PAGE " . $page . " OF " . $num_pages . "\n";
            $txt .=
                "FROM " .
                ($i + 1) .
                " TO " .
                $num_rows * $num_columns * $page .
                "\n";
            $txt .= "\n";
            foreach (range(1, $num_rows) as $row) {
                foreach (range(1, $num_columns) as $col) {
                    $local_offset = 0;
                    $i =
                        ($page - 1) * $num_rows * $num_columns +
                        ($col - 1) * $num_rows +
                        $row +
                        $offset;

                    if ($blanks) {
                        if (!isset($this->index[$i + $local_offset])) {
                            continue;
                        }
                        $txt .=
                            " " .
                            str_pad(
                                $this->index[$i + $local_offset],
                                10,
                                ' ',
                                STR_PAD_LEFT
                            );
                    } else {
                        $txt .=
                            " " .
                            str_pad(
                                $this->duplicables_list[$i],
                                10,
                                ' ',
                                STR_PAD_LEFT
                            );
                    }
                }
                $txt .= "\n";
            }
            $txt .= "\n";
            $page += 1;
        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    function initNuuids()
    {
        $this->thing->log(
            $this->agent_prefix . 'initialized the duplicables index.',
            "INFORMATION"
        );

        $this->index = [];
        //$v[0] = 1;
        //$v[1] = 2;

        foreach (range(0, 400) as $i) {
            $this->index[$i] = $this->makeNuuid();
            //$this->index[$i] = $this->makeNumber(3);
        }

        foreach (range(0, 400) as $k => $i) {
            foreach (range(0, 400) as $k => $j) {
                if ($i == $j) {
                    continue;
                }
                if ($this->index[$i] == $this->index[$j]) {
                    $this->index[$i] = false;
                    //$this->index[$i]= "*" . $this->index[$i] . "*";
                }
            }
        }

        //var_dump($v);
        return $this->index;
    }

    function echoDuplicables()
    {
        //
        //        $rows = 20;
        //        $columns = 5;

        //        foreach(range(0,$rows) as $row_index) {
        //            foreach(range(0,columns) as $column_index) {
        //            echo $row_index . " " . $column_index . " ".$value. " ";
        //        }
    }

    function makeNuuids($n = null)
    {

        $v = [];

        foreach (range(0, 100) as $i) {
            $v[$i] = rand(0, 9999);
        }
        return $v;
    }

    function makeNuuid()
    {
        $t = new Thing(null);
        return $t->uuid;
    }

    function makeNumber($digits = 4)
    {
        return str_pad(
            rand(0, pow(10, $digits) - 1),
            $digits,
            "0",
            STR_PAD_LEFT
        );
    }

    function readQrs()
    {
        return $this->state;
    }

    function extractNuuid($input)
    {
        if (!isset($this->duplicables)) {
            $this->duplicables = [];
        }

        return $this->duplicables;
    }

    public function makePDF()
    {
        $txt = $this->thing_report['txt'];
        //$txt = explode($txt , "\n");
        // initiate FPDI
        $pdf = new Fpdi\Fpdi();

        $pdf->setSourceFile($this->resource_path . 'snowflake/bubble.pdf');
        $pdf->SetFont('Helvetica', '', 10);

        $tplidx1 = $pdf->importPage(2, '/MediaBox');

        $s = $pdf->getTemplatesize($tplidx1);

        $pdf->addPage($s['orientation'], $s);
        //        $pdf->useTemplate($tplidx1,0,0,215);
        $pdf->useTemplate($tplidx1);

        //$separator = "\r\n";
        //$line = strtok($this->thing_report['txt'], $separator);

        //while ($line !== false) {
        //    # do something with $line
        //    $line = strtok( $separator );
        //echo $line;
        //}
        $pdf->SetTextColor(0, 0, 0);

        $num_rows = 17;
        $num_columns = 13;
        $offset = 0;
        $page = 1;
        $i = 1;

        $row_height = 15.85; //10
        $column_width = 15.85; //10

        $size_x = $column_width - 2;
        $size_y = $row_height - 2;

        $i = 0;
        $blanks = true;
        if ($blanks) {
            $max_i = $this->max;
        }

        $num_pages = ceil($this->max / ($num_rows * $num_columns));

        //$row_height = $size_x; //10
        //$column_width = $size_y; //10

        $left_margin = 6;
        $top_margin = 5;

        while ($i <= $max_i) {
            //        $pdf->SetXY(15, 10);

            //        $txt = "PAGE " . $page . " OF " . $num_pages . "\n";
            //        $pdf->Write(0, $txt);

            //        $pdf->SetXY(15, 15);

            //        $txt = "QR CODES FROM " . ($i+1) . " TO " . (($num_rows * $num_columns)*($page )) ."\n";
            //        $pdf->Write(0, $txt);

            foreach (range(1, $num_rows) as $row) {
                foreach (range(1, $num_columns) as $col) {
                    $local_offset = 0;
                    $i =
                        ($page - 1) * $num_rows * $num_columns +
                        ($col - 1) * $num_rows +
                        $row +
                        $offset;

                    if ($blanks) {
                        if (!isset($this->index[$i + $local_offset])) {
                            continue;
                        }
                        if ($this->index[$i + $local_offset] == false) {
                            continue;
                        }

                        $txt =
                            " " .
                            str_pad(
                                $this->index[$i + $local_offset],
                                10,
                                ' ',
                                STR_PAD_LEFT
                            );

                        //$pdf->SetXY(10 + ($col-1) *19, 30 + $row *5);
                        //$pdf->Write(0, $txt);

                        //        $t = new Thing(null);
                        $this->getQuickresponse($txt);
                        $pdf->Image(
                            $this->quick_response_png,
                            $left_margin + ($col - 1) * $column_width,
                            $top_margin + ($row - 1) * $row_height,
                            $size_x,
                            $size_y,
                            'PNG'
                        );
                    } else {
                        $txt .=
                            " " .
                            str_pad(
                                $this->duplicables_list[$i],
                                10,
                                ' ',
                                STR_PAD_LEFT
                            );

                        //$pdf->SetXY(10 + ($col-1) *19, 30 + $row *5);
                        //$pdf->Write(0, $txt);

                        //        $t = new Thing(null);
                        $this->getQuickresponse($txt);
                        $pdf->Image(
                            $this->quick_response_png,
                            $left_margin + ($col - 1) * 19,
                            $top_margin + $row * 5,
                            30,
                            30,
                            'PNG'
                        );
                    }
                }
                $txt .= "\n";
            }
            $txt .= "\n";
            $page += 1;

            // Bubble
            $pdf->SetFont('Helvetica', '', 12);
            //        $pdf->SetXY(17, 248);

            //        $txt = "NUUIDS | An index of four character";
            //        $pdf->Write(0, $txt);

            //        $pdf->SetXY(17, 253);
            //
            //        $txt = "identifiers.";
            //        $pdf->Write(0, $txt);

            $pdf->SetFont('Helvetica', '', 10);

            if ($i >= $max_i) {
                break;
            } else {
                $pdf->addPage($s['orientation'], $s);
                //        $pdf->useTemplate($tplidx1,0,0,215);
                $pdf->useTemplate($tplidx1);
            }
        }

        $tplidx2 = $pdf->importPage(2, '/MediaBox');
        $pdf->addPage($s['orientation'], $s);

        $pdf->useTemplate($tplidx2);

        // Generate some content for page 2
        $pdf->SetFont('Helvetica', '', 10);

        $txt = $this->web_prefix . "thing/" . $this->uuid . "/nuuids"; // Pure uuid.


        $this->getQuickresponse($txt);
        $pdf->Image($this->quick_response_png, 175, 5, 30, 30, 'PNG');

        $pdf->SetTextColor(0, 0, 0);

        //        $pdf->SetXY(15, 10);
        //        $t = $this->web_prefix . "thing/".$this->uuid;
        //        $t = $this->uuid;

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(15, 10);
        $t = $this->thing_report['sms'] . "";

        $pdf->Write(0, $t);

        //$pdf->SetXY(15, 15);
        //$text = $this->timestampSnowflake();
        //$pdf->Write(0, $text);

        $text = "Pre-printed text and graphics (c) 2018 Stackr Interactive Ltd";
        $pdf->SetXY(15, 20);
        $pdf->Write(0, $text);

        /*
        ob_start();
        $image = $pdf->Output('', 'I');
        $image = ob_get_contents();
        ob_clean();
*/
        $image = $pdf->Output('', 'S');

        $this->thing_report['pdf'] = $image;

        return $this->thing_report['pdf'];
    }

    public function readSubject()
    {
        $input = strtolower($this->input);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'qrs') {
                $this->makeNuuids();

                if (!isset($this->index) or $this->index == null) {
                    $this->index = 1;
                }
                $this->max = 221;
                $this->size = 4;
                //$this->lattice_size = 40;
                return;
            }
        }

        $keywords = ["nuuids"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'qrs':
                            $this->makeNuuids();

                            return;

                        case 'on':
                        //$this->setFlag('green');
                        //break;

                        default:
                    }
                }
            }
        }

        $this->makeNuuids();

        if (!isset($this->index) or $this->index == null) {
            $this->index = 1;
        }
    }
}
