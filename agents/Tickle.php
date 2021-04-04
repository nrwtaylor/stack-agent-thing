<?php
namespace Nrwtaylor\StackAgentThing;

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

// devstack
// dev.

class Tickle extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->initTickle();
        $this->test = "Development code";

        $this->node_list = ["tickle" => ["tickle", "tickles", "tickler"]];

        if (!isset($this->min)) {
            $this->min = 1;
        }
        if (!isset($this->max)) {
            $this->max = 400;
        }
        if (!isset($this->size)) {
            $this->size = 4;
        }

        $this->tickle_now = $this->nowTickle();

        $this->initTickle();
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "tickle",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["tickle", "refreshed_at"],
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

        $this->thing_report["info"] =
            "This creates a tickle. Text WIKIPEDIA TICKLER FILE.";
        $this->thing_report["help"] = 'Try "TICKLER"';

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
            "tickle"
        );
        $this->thing->log(
            $this->agent_prefix .
                'completed create choice. Timestamp = ' .
                number_format($this->thing->elapsed_runtime()) .
                'ms.',
            "OPTIMIZE"
        );

        $this->choices = $this->thing->choice->makeLinks('tickle');
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
        $sms = "TICKLE | ";
        if (isset($this->tickle)) {
            $sms .= "tickle " . $this->textTickle($this->tickle) . " ";
        }
        $sms .= "now " . $this->textTickle($this->tickle_now) . " ";

        $sms .= $this->response;
        //$sms .= $this->web_prefix . "thing/".$this->uuid."/tickle.pdf";
        //$sms .= " | TEXT NUUID";

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeMessage()
    {
        $message = "No message.";
        if (isset($this->tickle)) {
            $text = $this->textTickle($this->tickle);
            $message = $text . "<br>";
        }
        $uuid = $this->uuid;

        $message .=
            "<p>" . $this->web_prefix . "thing/$uuid/tickle\n \n\n<br> ";

        $this->thing_report['message'] = $message;
    }

    public function textTickle($tickle = null)
    {
        if ($tickle == null) {
            return false;
        }
        if ($tickle == false) {
            return "";
        }
        // Create a line of text from a tickle object
        // ignore hour and minute for now.

        $text =
            "month " .
            $tickle['month'] .
            " day " .
            $tickle['day'] .
            " number " .
            $tickle['number'];

        return $text;
    }

    public function test()
    {
        //tickle 10 pay gas
    }

    public function nowTickle()
    {
        // devstack
        // use channels timezone.

        $time_string = $this->current_time;
        $tickle = $this->makeTickle($time_string);
        return $tickle;
    }

    public function binTickle($tickle = null)
    {
        $current_bin = "day";

        // available bins. days. months.

        // days, months.
    }

    public function makeTickle($text = null)
    {
        //if ($text == null) {return false;}

        //$at_agent = new At($this->thing, "at");

        $this->at_agent->extractAt($text);

        $month = $this->at_agent->month;

        $day = $this->at_agent->day;
        $hour = $this->at_agent->hour;
        $minute = $this->at_agent->minute;

        $day_number = $this->at_agent->day_number;

        //$number_agent = new Number($this->thing, "number");
        $this->number_agent->extractNumber($text);

        $number = "X";
        if (count($this->number_agent->numbers) == 1) {
            $number = $this->number_agent->number;
        }

if (($number == "X") and (is_numeric($day_number))) {
$number = $day_number;

}

//$text = "test";

        $tickle = [
            "month" => $month,
            "day" => $day,
            "day number" => $day_number,
            "hour" => $hour,
            "minute" => $minute,
            "number" => $number,
            "text" => $text
        ];

        if ($text == null) {
            $this->tickle = $tickle;
        }

        return $tickle;
    }

function tableTickle($tickles = null) {

        $tickle_now = $this->nowTickle();


if ($tickles == null) {return false;}

            // factor into seperate agent.
            $html_table = '<div class="Table">
                 <div class="TableRow">
                 <div class="TableHead"><strong>ID</strong></div>';

foreach($this->tickle_now as $i=>$j) {

$html_table .=
                 '<div class="TableHead"><span style="font-weight: bold;">' . ucwords($i) . '</span></div>';

}

$html_table .= '</div>';

            foreach ($tickles as $uuid => $tickle) {
                $html_table .= '<div class="TableRow">';

                    $html_table .=
                        '<div class="TableCell">' . substr($uuid, 0, 4) . '</div>';

                $t = "";
                foreach ($tickle as $bin_name => $q) {
                    $cell_content = $q;
                    if (strtolower($q) == strtolower($tickle_now[$bin_name])) {
                        $t .= "Matched " . $bin_name . " ";
                        $cell_content = "<b>" . $q ."</b>";
                    }

                    $html_table .=
                        '<div class="TableCell">' . $cell_content . '</div>';
                }


                $html_table .= '</div>';
            }
            $html_table = $html_table . '</div><p>';
//            $web .= $html_table;
//            $web .= "<br>";

return $html_table;


}

    function makeWeb()
    {
        $tickle_now = $this->nowTickle();

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';
        $this->node_list = ["web" => ["tickle", "tickler", "tickles"]];

        $web = "<b>Tickle Agent</b>";
        $web .= "<p>";

        //$web = '<a href="' . $link . '">';
        //$web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png">';
        //$web .= "</a>";
        $i = 0;
        //        foreach ($this->index as $key=>$value) {
        //            $web .= $value . "<br>";
        //            if ($i == 10) {break;} else {$i += 1;}
        //        }
        if (isset($this->tickle)) {
            $web .= $this->textTickle($this->tickle);
            $web .= "<br>";
        }
        if (!isset($this->tickles)) {
            $this->getTickles();
        }

//        $html_table = $this->tableTickle($this->tickles);

        if (isset($this->tickles)) {

            $ref_tickles = array($tickle_now);
            $html_table = $this->tableTickle($ref_tickles);

            $web .= $html_table;
            $web .= "<br>";
            $web .= "<p>";

            $html_table = $this->tableTickle($this->tickles);

            $web .= $html_table;
            $web .= "<br>";

        }

        $web .= "<br><br>";
        $this->thing_report['web'] = $web;
    }

    function makeTXT()
    {
        $txt = "This is an index of semi-unique TICKLE.\n";
        $txt .= 'Duplicate TICKLE omitted.';
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

    public function initTickle()
    {
        $this->at_agent = new At($this->thing, "at");
        $this->number_agent = new Number($this->thing, "number");
    }

    public function readTickle($text = null)
    {
    }

    public function makePDF()
    {
        if ($this->default_pdf_page_template === null) {
            $this->thing_report['pdf'] = false;
            return $this->thing_report['pdf']; 
        }

        $txt = $this->thing_report['txt'];
        //$txt = explode($txt , "\n");
        // initiate FPDI
        $pdf = new Fpdi\Fpdi();

        $pdf->setSourceFile($this->default_pdf_page_template);
        $pdf->SetFont('Helvetica', '', 10);

        $tplidx1 = $pdf->importPage(3, '/MediaBox');

        $s = $pdf->getTemplatesize($tplidx1);

        $pdf->addPage($s['orientation'], $s);
        //        $pdf->useTemplate($tplidx1,0,0,215);
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
            $txt = "A TICKLE";
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

            $txt = "TICKLE | A reminder for today.";
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

        $txt = $this->web_prefix . "thing/" . $this->uuid . "/tickle"; // Pure uuid.

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

        $text = "Pre-printed text and graphics (c) 2020 Stackr Interactive Ltd";
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

    public function getTickles()
    {
        $this->response .= "Got tickles. ";

        $tickles = [];
        $agent_name = 'job';
        $things = $this->getThings('tickle');

        if ($things == []) {
            return true;
        }

        $agents = ["tickle", "job", "snowflake"];

        foreach ($agents as $i => $agent_name) {
            foreach (array_reverse($things) as $thing) {
                $uuid = $thing->uuid;
                $subject = $thing->subject;
                $variables = $thing->variables;
                $created_at = $thing->created_at;

                if (isset($variables[$agent_name])) {
 //                   $tickle = [
 //                       "subject" => $subject,
 //                       "name" => $agent_name,
 //                       "variables" => $variables[$agent_name],
 //                       "created_at" => $created_at,
 //                   ];

                    if (!isset($tickles[$uuid])) {
                        $tickles[$uuid] = [];
                    }

$tickle = $this->makeTickle($subject);


                    $tickles[$uuid] = array_merge($tickles[$uuid], $tickle);

                    //$this->tickles[] = $tickle;
                }
            }
        }

        if (!isset($this->tickles)) {
            $this->tickles = [];
        }

        $this->tickles = array_merge($this->tickles, $tickles);
    }

    public function readSubject()
    {
        $input = strtolower($this->input);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            $i = trim(strtolower($input));
            switch ($i) {
                case 'tickler':
                case 'tickles':
                    $this->getTickles();
                    //                            $this->makeTickle();

                    return;

                case 'tickle':
                    $this->makeTickle();

                    if (!isset($this->index) or $this->index == null) {
                        $this->index = 1;
                    }
                    $this->max = 400;
                    $this->size = 4;
                    //$this->lattice_size = 40;
                    break;
            }
        }

        $keywords = ["tickle"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'tickle':
                            $this->makeTickle();

                            return;

                        case 'on':
                        //$this->setFlag('green');
                        //break;

                        default:
                    }
                }
            }
        }

        $this->makeTickle();

        if (!isset($this->index) or $this->index == null) {
            $this->index = 1;
        }
    }
}
