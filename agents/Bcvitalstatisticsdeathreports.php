<?php
namespace Nrwtaylor\StackAgentThing;

use Smalot\PdfParser;

class Bcvitalstatisticsdeathreports extends Agent
{
    public $var = "hello";

    public function init()
    {
        //$this->email = $this->thing->container["stack"]["email"];
        $this->link =
            "https://www2.gov.bc.ca/assets/gov/birth-adoption-death-marriage-and-divorce/statistics-reports/death-reports/deaths-by-lha-2020.pdf";

        $this->bcvitalstatistics_read_flag = false; // False do not read.
    }

    function set()
    {
        $time_string = $this->thing->time();
        $this->thing->Write(
            ["bcvitalstatistics", "refreshed_at"],
            $time_string
        );
    }

    function get()
    {
        $time_string = $this->thing->Read([
            "bcvitalstatistics",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->Write(
                ["bcvitalstatistics", "refreshed_at"],
                $time_string
            );
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    public function run()
    {
        $file =
            "https-www2-gov-bc-ca-assets-gov-birth-adoption-death-marriage-and-divorce-statistics-reports-death-reports-deaths-by-lha-2020-pdf";
        $filepath = "/var/www/stackr.test/resources/read/" . $file;

        if (!file_exists($filepath)) {
            return true;
        }

        $last_modified = filemtime($filepath);
        $timezone = date_default_timezone_get();
        $datum = new \DateTime("@$last_modified", new \DateTimeZone($timezone));

        $this->doc_timestamp = $this->timestampTime($datum, $timezone);

        $t = file_get_contents("/var/www/stackr.test/resources/read/" . $file);

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($filepath);

        $details = $pdf->getDetails();

        /*
Creator => Oracle11gR1 AS Reports Services
CreationDate => 2021-05-10T02:02:30+00:00
ModDate => 2021-05-10T02:02:30+00:00
Producer => Oracle PDF driver
Title => deaths-by-lha-2020.pdf
Author => Oracle Reports
Pages => 2
*/
        $break_tokens = [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
        ];
        // Loop over each property to extract values (string or array).
        foreach ($details as $property => $value) {
            if (is_array($value)) {
                $value = implode(", ", $value);
            }
            $this->details[$property] = $value;
        }
        $pages = $pdf->getPages();
        $tokens = [];
        // Loop over each page to extract text.
        foreach ($pages as $page_no => $page) {

            $text = $page->getText();
            $toks = $this->processThing($text);
            $tokens = array_merge($tokens, $toks);
$page_tokens[$page_no] = $toks;
        }
        // dev
        // TODO process tokens.
      //  var_dump($tokens);
foreach($page_tokens as $tokens) {
        $lines = [];
        $i = 0;
        $i_start = null;
$month = null;
$data_section = false;
        //foreach($tokens as $i=>$token) {
        while ($i < count($tokens)) {
            $token = $tokens[$i];

if ($data_section === true) {
$i += 1;
if (in_array($token, $break_tokens)) { $month_row_count = 0;$month = $token; continue;}
if ($month == null) {continue;}
echo $month_row_count . " " . $token . "\n";

$lines[$lha_id_index[$month_row_count]]['months'][$month] = $token;

$month_row_count += 1;
//$i += 1;
continue;

}

 
           if ($token === "Page" and $i_start === null) {
                $i_start = $i + 3;
$lha_row_count = 0;
$lha_id_index[$lha_row_count] = true;
            }
            if ($i >= $i_start) {
                if (is_numeric($token)) {
                    $health_authority_id = $token;
                    $health_authority = null;
                }
if (!isset($health_authority_id)) {$i += 1;continue;}
                if (ctype_alpha($token)) {
                    if (ctype_alpha($tokens[$i + 1])) {
                        $health_authority = $token . " " . $tokens[$i + 1];
			$i += 2;
                    } else {
                        $health_authority = $token;
                        $i += 1;
                    }

if ($health_authority === 'Health Area') {$data_section = true;}


if (!isset($lines[$health_authority_id])) {$lines[$health_authority_id] = [];}
                   $lha_id_index[$lha_row_count] = $health_authority_id;
$lha_row_count += 1;   
                        $lines[$health_authority_id]['lha'] = $health_authority;
                        $lines[$health_authority_id]['lha_id'] = $health_authority_id;
continue;
                }
            }
$i += 1;
        }
//var_dump($lha_id_index);
var_dump($lines);
exit();
}
        //}

        //}
    }

    public function processThing($text = null)
    {
        $tokens = explode(" ", $text);
        $t = [];
        foreach ($tokens as $token) {
            if ($token === "") {
                continue;
            }

            $matches = preg_split(
                "/(,?\s+)|((?<=[a-z])(?=\d))|((?<=\d)(?=[a-z]))/i",
                $token
            );

            if (count($matches) == 1) {
                $matches = [$token];
            }

            $t = array_merge($t, $matches);
        }

        return $t;
    }

    public function readSubject()
    {
        $this->response .= "Heard a request to read BC Vital Statistics. ";
    }

    public function makeMessage()
    {
        $message = "";
        $this->message = $message; //. ".";
        $this->thing_report["message"] = $message;
    }

    public function makeSMS()
    {
        $link =
            $this->web_prefix . "thing/" . $this->uuid . "/bcvitalstatistics";

        $sms = "BC VITAL STATISTICS ";
        $sms .= " | " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $this->sms_message;
    }

    public function makeWeb()
    {
        $link =
            $this->web_prefix . "thing/" . $this->uuid . "/bcvitalstatistics";

        $html = "<b>BC VITAL STATISTICS WATCHER</b>";

        $html .= '<br>BC Vital Statistics watcher says , "';

        $html .= "<p>";

        $html .= $this->details["CreationDate"];
        $html .= "<br>";
        $html .= $this->details["Title"];
        $html .= "<br>";
        $html .= $this->doc_timestamp;
        $html .= "<p>";
        $html .= $this->sms_message . '"';
        $this->web_message = $html;
        $this->thing_report["web"] = $html;
    }
}
