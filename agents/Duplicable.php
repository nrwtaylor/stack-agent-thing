<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Duplicable extends Agent
{
    public $var = "hello";

    public function init()
    {
        $this->node_list = ["duplicable" => ["index", "uuid"]];

        $this->haystack =
            $thing->uuid .
            $thing->to .
            $thing->subject .
            $command_line .
            $this->agent_input;

        if (!isset($this->min)) {
            $this->min = 1;
        }
        if (!isset($this->max)) {
            $this->max = 9999;
        }
        if (!isset($this->size)) {
            $this->size = 4;
        }

        $this->initDuplicables();
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "duplicable",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["duplicable", "refreshed_at"],
                $time_string
            );
        }
    }

    public function set()
    {
        $this->setDuplicable();
    }

    // https://www.math.ucdavis.edu/~gravner/RFG/hsud.pdf

    function getUuid()
    {
        $agent = new Uuid($this->thing, "uuid");
        $this->uuid_png = $agent->PNG_embed;
    }

    function getQuickresponse()
    {
        $agent = new Qr($this->thing, "qr");
        $this->quick_response_png = $agent->PNG_embed;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report["info"] = "This creates a duplicable number set.";
        $this->thing_report["help"] = 'Try "DUPLICABLE"';

        $this->thing->log(
            $this->agent_prefix .
                "started message. Timestamp = " .
                number_format($this->thing->elapsed_runtime()) .
                "ms.",
            "OPTIMIZE"
        );

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "duplicable"
        );
        $this->choices = $this->thing->choice->makeLinks("duplicable");

        $this->thing_report["choices"] = $this->choices;
    }

    function makeSMS()
    {
        $sms = "DUPLICABLE | ";
        $sms .= $this->web_prefix . "thing/" . $this->uuid . "/duplicable.pdf";
        $sms .= " | " . "Made a non-duplicable index.";
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeMessage()
    {
        $message = "Made a non-duplicable index for you.<br>";

        $uuid = $this->uuid;

        $message .=
            "Keep on stacking.\n\n<p>" .
            $this->web_prefix .
            "thing/$uuid/duplicable\n \n\n<br> ";

        $this->thing_report["message"] = $message;
    }

    function setDuplicable()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["duplicable", "index"],
            $this->index
        );
        $this->thing->log(
            $this->agent_prefix .
                " saved duplicable index " .
                $this->index .
                ".",
            "INFORMATION"
        );
    }

    function getDuplicable()
    {
        $this->thing->json->setField("variables");
        $this->index = $this->thing->json->readVariable([
            "duplicable",
            "index",
        ]);

        if ($this->index == false) {
            $this->thing->log(
                $this->agent_prefix . " did not find a duplicable index.",
                "INFORMATION"
            );
            // Return.
            return true;
        }

        $this->thing->log(
            $this->agent_prefix .
                " loaded duplicable index " .
                $this->index .
                ".",
            "INFORMATION"
        );
        return;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/agent";
        $this->node_list = ["web" => ["duplicable", "nuuid"]];

        $web = '<a href="' . $link . '">';
        //$web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png">';
        $web .= "</a>";
        $web .= "<br>";

        $web .= "<br><br>";
        $this->thing_report["web"] = $web;
    }

    function makeTXT()
    {
        $txt = "This is an index of NON-TRANSPOSABLE NUMBERS.\n";
        $txt .= "DUPLICABLE NUMBERS omitted.";
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
            if ($this->duplicables_index[$index] == false) {
                continue;
            }

            $this->duplicables_list[$i] = $this->duplicables_index[$index];
            $i += 1;
            $max_i = $i;
        }

        $i = 0;
        $blanks = true;
        if ($blanks) {
            $max_i = $this->max;
        }

        $num_pages = ceil($this->max / ($num_rows * $num_columns));
        while ($i <= $max_i) {
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
                        if (
                            !isset($this->duplicables_index[$i + $local_offset])
                        ) {
                            continue;
                        }
                        $txt .=
                            " " .
                            str_pad(
                                $this->duplicables_index[$i + $local_offset],
                                10,
                                " ",
                                STR_PAD_LEFT
                            );
                    } else {
                        $txt .=
                            " " .
                            str_pad(
                                $this->duplicables_list[$i],
                                10,
                                " ",
                                STR_PAD_LEFT
                            );
                    }
                }
                $txt .= "\n";
            }
            $txt .= "\n";
            $page += 1;
        }

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    function initDuplicables()
    {
        $this->thing->log(
            $this->agent_prefix . "initialized the duplicables index.",
            "INFORMATION"
        );

        $this->duplicables_index = [];
        $this->duplicability_index = [];

        foreach (range($this->min, $this->max) as $i) {
            if (isset($this->duplicables_index[$i])) {
                continue;
            }
            $this->duplicables_index[$i] = $i;
            $arr = $this->getDuplicables($i);

            if ($arr == false) {
                continue;
            }

            foreach ($arr as $key => $value) {
                $v = implode($value);
                //                echo $v . "\n";
                if ($v > $i) {
                    $this->duplicables_index[$v] = false;
                }
            }
        }
    }

    function getDuplicability($n)
    {
        $d = 1;

        foreach (range(-2, 2, 1) as $i) {
            if (!isset($this->duplicables_index)) {
                continue;
            }
            if ($this->duplicables_index[$n + $i] == false) {
                // is duplicable;
                $d += 1;
            }
        }

        return $d / 5;
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

    function getDuplicables($n)
    {
        //$n = "1234";
        $n = ltrim($n, "0");

        //$this->size = 2;
        $elems = str_split($n);

        $num_digits = $this->size;
        $num_digits = count($elems);

        //strlen($n)
        //var_dump(count($elems));
        //echo "<br>";

        //echo $this->size;
        //echo "<br>";

        $i = 0;
        while ($i < $num_digits - count($elems)) {
            //    echo $i . "<br>";
            array_unshift($elems, null);
            $i += 1;
        }

        $v = $this->computeTranspositions($elems);

        return $v;
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
            $result[] = $tmp_array;
        }

        return $result;
    }

    function extractDuplicable($input)
    {
        if (!isset($this->duplicables)) {
            $this->duplicables = [];
        }

        return $this->duplicables;
    }

    public function makePDF()
    {
        if (
            $this->default_pdf_page_template === null or
            !file_exists($this->default_pdf_page_template)
        ) {
            $this->thing_report["pdf"] = false;
            return $this->thing_report["pdf"];
        }

        $txt = $this->thing_report["txt"];

        // initiate FPDI
        $pdf = new Fpdi\Fpdi();

        $pdf->setSourceFile($this->default_pdf_page_template);
        $pdf->SetFont("Helvetica", "", 10);

        $tplidx1 = $pdf->importPage(3, "/MediaBox");

        $s = $pdf->getTemplatesize($tplidx1);

        $pdf->addPage($s["orientation"], $s);

        $pdf->useTemplate($tplidx1);

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

            $txt =
                "INDICES FROM " .
                ($i + 1) .
                " TO " .
                $num_rows * $num_columns * $page .
                "\n";
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
                        if (
                            !isset($this->duplicables_index[$i + $local_offset])
                        ) {
                            continue;
                        }
                        if (
                            $this->duplicables_index[$i + $local_offset] ==
                            false
                        ) {
                            continue;
                        }

                        $txt =
                            " " .
                            str_pad(
                                $this->duplicables_index[$i + $local_offset],
                                10,
                                " ",
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
                                " ",
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
            $pdf->SetFont("Helvetica", "", 12);
            $pdf->SetXY(17, 248);

            $txt = "DUPLICABLE | An index to reduce transposition";
            $pdf->Write(0, $txt);

            $pdf->SetXY(17, 253);

            $txt = "errors. To help prevent duplicate data entry.";
            $pdf->Write(0, $txt);

            $pdf->SetFont("Helvetica", "", 10);

            if ($i > $max_i) {
            } else {
                $pdf->addPage($s["orientation"], $s);
                //        $pdf->useTemplate($tplidx1,0,0,215);
                $pdf->useTemplate($tplidx1);
            }
        }

        $tplidx2 = $pdf->importPage(2, "/MediaBox");
        $pdf->addPage($s["orientation"], $s);
        //        $pdf->useTemplate($tplidx1,0,0,215);
        $pdf->useTemplate($tplidx2);

        // Generate some content for page 2

        $pdf->SetFont("Helvetica", "", 10);

        $this->txt = "" . $this->uuid . ""; // Pure uuid.

        $this->getQuickresponse();
        $pdf->Image($this->quick_response_png, 175, 5, 30, 30, "PNG");

        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(15, 10);
        $t = $this->thing_report["sms"] . "";

        $pdf->Write(0, $t);

        $text = "Pre-printed text and graphics (c) 2018 Stackr Interactive Ltd";
        $pdf->SetXY(15, 20);
        $pdf->Write(0, $text);

        $image = $pdf->Output("", "S");

        $this->thing_report["pdf"] = $image;

        return $this->thing_report["pdf"];
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "duplicable") {
                $this->getDuplicable();

                if (!isset($this->index) or $this->index == null) {
                    $this->index = 1;
                }

                $this->max = 9999;
                $this->size = 4;

                return;
            }
        }

        $keywords = ["duplicable", "transcribe"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "duplicable":
                            $this->getDuplicable();

                            return;

                        case "on":

                        default:
                    }
                }
            }
        }

        $this->getDuplicable();

        if (!isset($this->index) or $this->index == null) {
            $this->index = 1;
        }
    }
}
