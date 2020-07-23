<?php
namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Nonsense extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->test = "Development code";

        $this->node_list = ["nonsense" => ["nonsense", "rocky", "charley"]];

        if (!isset($this->min)) {
            $this->min = 1;
        }
        if (!isset($this->max)) {
            $this->max = 400;
        }
        if (!isset($this->size)) {
            $this->size = 4;
        }

        $this->initNonsense();
    }
    /*
public function set() {

        $this->setNonsense();

}
*/
    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "nonsense",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["nonsense", "refreshed_at"],
                $time_string
            );
        }
    }

    function getUuid()
    {
        $agent = new Uuid($this->thing, "uuid");
        $this->uuid_png = $agent->PNG_embed;
    }

    function getQuickresponse($txt = "qr")
    {
        $agent = new Qr($this->thing, $txt);
        $this->quick_response_png = $agent->PNG_embed;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "nonsense";

        //        $this->makeSMS();

        //        $this->makeMessage();
        //$this->makeTXT();

        $this->makeChoices();

        $this->thing_report["info"] = "This creates a duplicable number set.";
        $this->thing_report["help"] = 'Try "DUPLICABLE"';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
        //        $this->makeWeb();

        //        $this->makeTXT();
        //        $this->makePDF();
        /*
        $this->thing->log(
            $this->agent_prefix .
                'completed message. Timestamp = ' .
                number_format($this->thing->elapsed_runtime()) .
                'ms.',
            "OPTIMIZE"
        );
*/

        return $this->thing_report;
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
            "nonsense"
        );
        $this->thing->log(
            $this->agent_prefix .
                'completed create choice. Timestamp = ' .
                number_format($this->thing->elapsed_runtime()) .
                'ms.',
            "OPTIMIZE"
        );

        $this->choices = $this->thing->choice->makeLinks('nonsense');
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
        $sms = "NONSENSE | ";
        $sms .= $this->nonsense;
        //$sms .= $this->web_prefix . "thing/".$this->uuid."/nonsense.pdf";
        //$sms .= " | TEXT NUUID";

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeMessage()
    {
        $message = "Stackr made a 25 nonsense words for you.<br>";

        $uuid = $this->uuid;

        $message .=
            "<p>" . $this->web_prefix . "thing/$uuid/nonsense\n \n\n<br> ";

        $this->thing_report['message'] = $message;

        return;
    }
    /*
    function setNonsense()
    {
        //        $this->thing->json->setField("variables");
        //        $this->thing->json->writeVariable( array("nonsense", "index"), $this->index );
        //var_dump($this->index);
        //        $this->thing->log($this->agent_prefix . ' saved duplicable index ' . $this->index[0] . '.', "INFORMATION") ;
    }
*/
    /*
    function getNonsense()
    {
        //        $this->thing->json->setField("variables");
        //        $this->index = $this->thing->json->readVariable( array("nonsense", "index") );

        //        if ($this->index == false) {
        //            $this->thing->log($this->agent_prefix . ' did not find a duplicable index.', "INFORMATION") ;
        // Return.
        //            return true;
        //        }

        $this->thing->log(
            $this->agent_prefix .
                ' loaded nonsense index ' .
                $this->index .
                '.',
            "INFORMATION"
        );
        return;
    }
*/
    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';
        $this->node_list = ["web" => ["nonsense", "nuuid"]];

        $web = "<b>Nonsense Agent</b>";
        $web .= "<p>";

        //$web = '<a href="' . $link . '">';
        //$web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png">';
        //$web .= "</a>";
        $i = 0;
        //        foreach ($this->index as $key=>$value) {
        //            $web .= $value . "<br>";
        //            if ($i == 10) {break;} else {$i += 1;}
        //        }

        $web .= $this->nonsense;

        $web .= "<br>";

        $web .= "<br><br>";
        $this->thing_report['web'] = $web;
    }

    function makeTXT()
    {
        $txt = "This is an index of semi-unique NONSENSE.\n";
        $txt .= 'Duplicate NONSENSE omitted.';
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

        //$this->duplicables_list = array();
        //foreach(range($this->min,$this->max) as $index) {
        //    if ($this->index[$index] == false) {continue;}

        //    $this->duplicables_list[$i] = $this->index[$index];
        //    $i +=1;
        //    $max_i = $i;
        //}

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

    function initNonsense()
    {
        return;
        $this->thing->log(
            $this->agent_prefix . 'initialized the duplicables index.',
            "INFORMATION"
        );

        $this->index = [];
        //$v[0] = 1;
        //$v[1] = 2;

        foreach (range(0, 24) as $i) {
            $this->index[$i] = $this->makeNonsense();
            //$this->index[$i] = $this->makeNumber(3);
        }

        foreach (range(0, 24) as $k => $i) {
            foreach (range(0, 24) as $k => $j) {
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

    function makeNonsense($n = 25)
    {
        $v = [];
        //$v[0] = 1;
        //$v[1] = 2;

        foreach (range(0, $n - 1) as $i) {
            $word_agent = new Word($this->thing, "word");
            $word = $word_agent->randomWord();
            $v[$i] = $word;
        }

        $this->nonsense = implode($v, " ");

        return $v;
    }

    function makeNuuid()
    {
        $t = new Thing(null);
        return $t->nuuid;
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

    function computeTranspositions($array)
    {
        if (count($array) == 1) {
            return false;
        }
        $result = [];
        foreach (range(0, count($array) - 2) as $i) {
            $tmp_array = $array;
            $tmp = $tmp_array[$i];
            $tmp_array[$i] = $tmp_array[$i + 1];
            $tmp_array[$i + 1] = $tmp;
            //$this->array_swap($array, $i, $i+1);
            $result[] = $tmp_array;
            //        var_dump($array);
        }

        return $result;
    }

    function read()
    {
        return $this->state;
    }
    /*
    function extractNuuid($input)
    {
        if (!isset($this->duplicables)) {
            $this->duplicables = [];
        }

        return $this->duplicables;
    }
*/
    public function makePDF()
    {
        $txt = $this->thing_report['txt'];
        //$txt = explode($txt , "\n");
        // initiate FPDI
        $pdf = new Fpdi\Fpdi();

        $pdf->setSourceFile($this->resource_path . 'snowflake/bubble.pdf');
        $pdf->SetFont('Helvetica', '', 10);

        $tplidx1 = $pdf->importPage(3, '/MediaBox');

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

        $num_rows = 40;
        $num_columns = 10;
        $offset = 0;
        $page = 1;
        $i = 1;

        $i = 0;
        $blanks = true;
        if ($blanks) {
            $max_i = $this->max;
        }

        $num_pages = ceil($this->max / ($num_rows * $num_columns));

        while ($i <= $max_i) {
            $pdf->SetXY(15, 10);

            $txt = "PAGE " . $page . " OF " . $num_pages . "\n";
            $pdf->Write(0, $txt);

            $pdf->SetXY(15, 15);

            //$txt = "INDICES FROM " . ($i+1) . " TO " . (($num_rows * $num_columns)*($page )) ."\n";
            $txt = "A PAGE OF DIFFERENT NOT UNIQUE NUMBERS";
            $pdf->Write(0, $txt);

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

                        $pdf->SetXY(10 + ($col - 1) * 19, 30 + $row * 5);
                        $pdf->Write(0, $txt);
                    } else {
                        $txt .=
                            " " .
                            str_pad(
                                $this->duplicables_list[$i],
                                10,
                                ' ',
                                STR_PAD_LEFT
                            );

                        $pdf->SetXY(10 + ($col - 1) * 19, 30 + $row * 5);
                        $pdf->Write(0, $txt);
                    }
                }
                $txt .= "\n";
            }
            $txt .= "\n";
            $page += 1;

            // Bubble
            $pdf->SetFont('Helvetica', '', 12);
            $pdf->SetXY(17, 248);

            $txt = "NONSENSE | An index of four character";
            $pdf->Write(0, $txt);

            $pdf->SetXY(17, 253);

            $txt = "identifiers.";
            $pdf->Write(0, $txt);

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

        $txt = $this->web_prefix . "thing/" . $this->uuid . "/nonsense"; // Pure uuid.

        //$this->getUuid();
        //$pdf->Image($this->uuid_png,175,5,30,30,'PNG');

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
            if ($input == 'nonsense') {
                $this->makeNonsense();

                if (!isset($this->index) or $this->index == null) {
                    $this->index = 1;
                }
                $this->max = 400;
                $this->size = 4;
                //$this->lattice_size = 40;
                return;
            }
        }

        $keywords = ["nonsense"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'nonsense':
                            $this->makeNonsense();

                            return;

                        case 'on':
                        //$this->setFlag('green');
                        //break;

                        default:
                    }
                }
            }
        }

        $this->makeNonsense();

        if (!isset($this->index) or $this->index == null) {
            $this->index = 1;
        }

        //$this->max = 9999;
        //$this->size = 4;
        //$this->lattice_size = 40;

        return;
    }
}
