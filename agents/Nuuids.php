<?php
namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Nuuids extends Agent
{
    public $var = "hello";

    public function init()
    {
$this->choice_handler = new Choice($this->thing, "choice");
        $this->node_list = ["nuuids" => ["index", "uuid"]];

        if (!isset($this->min)) {
            $this->min = 1;
        }
        if (!isset($this->max)) {
            $this->max = 400;
        }
        if (!isset($this->size)) {
            $this->size = 4;
        }

        $this->initNuuids();

        $this->setNuuids();
    }

    public function get()
    {
        $time_string = $this->thing->Read([
            "nuuids",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["nuuids", "refreshed_at"],
                $time_string
            );
        }
    }

    public function set()
    {
        $this->setNuuids();
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
        $this->makeChoices();

        $this->thing_report["info"] = "This creates a set of not-unique ids.";
        $this->thing_report["help"] = 'Try "NUUID"';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeChoices()
    {
        $this->thing->log(
            $this->agent_prefix .
                "started makeChoices. Timestamp = " .
                number_format($this->thing->elapsed_runtime()) .
                "ms.",
            "OPTIMIZE"
        );
/*
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "nuuids"
        );
*/
//$this->choice_handler = new Choice($this->thing, "choice");
        $this->choice_handler->Create(
            $this->agent_name,
            $this->node_list,
            "nuuids"
        );


        $this->thing->log(
            $this->agent_prefix .
                "completed create choice. Timestamp = " .
                number_format($this->thing->elapsed_runtime()) .
                "ms.",
            "OPTIMIZE"
        );

        $this->choices = $this->choice_handler->makeLinks("nuuids");

//        $this->choices = $this->thing->choice->makeLinks("nuuids");
        $this->thing->log(
            $this->agent_prefix .
                "completed makeLinks. Timestamp = " .
                number_format($this->thing->elapsed_runtime()) .
                "ms.",
            "OPTIMIZE"
        );

        $this->thing_report["choices"] = $this->choices;
    }

    function makeSMS()
    {
        $sms = "NUUIDS | ";
        $sms .= $this->web_prefix . "thing/" . $this->uuid . "/nuuids.pdf";
        $sms .= " | TEXT NUUID";

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeMessage()
    {
        $message = "Stackr made a non-duplicable index for you.<br>";

        $uuid = $this->uuid;

        $message .=
            "Keep on stacking.\n\n<p>" .
            $this->web_prefix .
            "thing/$uuid/nuuids\n \n\n<br> ";

        $this->thing_report["message"] = $message;
    }

    function setNuuids()
    {
        $this->thing->Write(["nuuids", "index"], $this->index);

        $this->thing->log(
            $this->agent_prefix .
                " saved duplicable index " .
                $this->index[0] .
                ".",
            "INFORMATION"
        );
    }

    function getNuuids()
    {
        $this->index = $this->thing->Read(["nuuids", "index"]);

        if ($this->index == false) {
            $this->thing->log(
                $this->agent_prefix . " did not find a duplicable index.",
                "INFORMATION"
            );
            // Return.
            return true;
        }

        $this->thing->log(
            $this->agent_prefix . " loaded nuuids index " . $this->index . ".",
            "INFORMATION"
        );
    }

    function makeSnippet()
    {
        $web = "";
        $i = 0;
        foreach ($this->index as $key => $value) {
            $web .= $value . "<br>";
            if ($i == 10) {
                break;
            } else {
                $i += 1;
            }
        }
        $web .= "<p>";
        $this->snippet = $web;
        $this->thing_report["snippet"] = $web;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/agent";
        $this->node_list = ["web" => ["nuuids", "nuuid"]];

        $web = "";
        $web .= $this->snippet;
        $this->web = $web;
        $this->thing_report["web"] = $web;
    }

    function makeTXT()
    {
        $txt = "This is an index of semi-unique NUUIDS.\n";
        $txt .= "Duplicate NUUIDs omitted.";
        $txt .= "\n";

        $txt .= "\n";
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

    function initNuuids()
    {
        $this->thing->log("initialized the nuuids index.", "INFORMATION");

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

        return $this->index;
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
            $result[] = $tmp_array;
        }

        return $result;
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
        if ($this->default_pdf_page_template === null) {
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

            $txt = "NUUIDS | An index of four character";
            $pdf->Write(0, $txt);

            $pdf->SetXY(17, 253);

            $txt = "identifiers.";
            $pdf->Write(0, $txt);

            $pdf->SetFont("Helvetica", "", 10);

            if ($i >= $max_i) {
                break;
            } else {
                $pdf->addPage($s["orientation"], $s);
                $pdf->useTemplate($tplidx1);
            }
        }

        $tplidx2 = $pdf->importPage(2, "/MediaBox");
        $pdf->addPage($s["orientation"], $s);

        $pdf->useTemplate($tplidx2);

        // Generate some content for page 2
        $pdf->SetFont("Helvetica", "", 10);

        $txt = $this->web_prefix . "thing/" . $this->uuid . "/nuuids"; // Pure uuid.

        $this->getQuickresponse($txt);
        $pdf->Image($this->quick_response_png, 175, 5, 30, 30, "PNG");

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
            if ($input == "nuuids") {
                $this->makeNuuids();

                if (!isset($this->index) or $this->index == null) {
                    $this->index = 1;
                }
                $this->max = 400;
                $this->size = 4;
                return;
            }
        }

        $keywords = ["nuuids"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "nuuids":
                            $this->makeNuuids();

                            return;

                        case "on":

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
