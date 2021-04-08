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

    }

    function makeSMS()
    {
        $sms = "NONSENSE | ";
        $sms .= $this->nonsense;

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
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';
        $this->node_list = ["web" => ["nonsense", "nuuid"]];

        $web = "<b>Nonsense Agent</b>";
        $web .= "<p>";

        $i = 0;

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

        $txt .= "\n";

        $txt .= "\n";
        $txt .= "\n";

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
                }
            }
        }

        return $this->index;

    }

    function makeNonsense($n = 25)
    {
        $v = [];

        foreach (range(0, $n - 1) as $i) {
            $word_agent = new Word($this->thing, "word");
            $word = $word_agent->randomWord();
            $v[$i] = $word;
        }

        $this->nonsense = implode(" ", $v);

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

    function readNonsense($text = null)
    {
    }

    public function makePDF()
    {
        if (($this->default_pdf_page_template === null) or (!file_exists($this->default_pdf_page_template))) {
            $this->thing_report['pdf'] = false;
            return $this->thing_report['pdf'];
        }


        $txt = $this->thing_report['txt'];
        // initiate FPDI
        $pdf = new Fpdi\Fpdi();

        $pdf->setSourceFile($this->default_pdf_page_template);
        $pdf->SetFont('Helvetica', '', 10);

        $tplidx1 = $pdf->importPage(3, '/MediaBox');

        $s = $pdf->getTemplatesize($tplidx1);

        $pdf->addPage($s['orientation'], $s);

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
                $pdf->useTemplate($tplidx1);
            }
        }

        $tplidx2 = $pdf->importPage(2, '/MediaBox');
        $pdf->addPage($s['orientation'], $s);

        $pdf->useTemplate($tplidx2);

        // Generate some content for page 2
        $pdf->SetFont('Helvetica', '', 10);

        $txt = $this->web_prefix . "thing/" . $this->uuid . "/nonsense"; // Pure uuid.

        $this->getQuickresponse($txt);
        $pdf->Image($this->quick_response_png, 175, 5, 30, 30, 'PNG');

        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(15, 10);
        $t = $this->thing_report['sms'] . "";

        $pdf->Write(0, $t);

        $text = "Pre-printed text and graphics (c) 2018 Stackr Interactive Ltd";
        $pdf->SetXY(15, 20);
        $pdf->Write(0, $text);

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

                        default:
                    }
                }
            }
        }

        $this->makeNonsense();

        if (!isset($this->index) or $this->index == null) {
            $this->index = 1;
        }

    }
}
