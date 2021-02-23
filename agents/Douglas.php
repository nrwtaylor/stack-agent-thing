<?php
/**
 * Douglas.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Douglas extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {
        $this->test = "Development code";

        $this->thing_report["info"] =
            "DOUGLAS is a tool for managing message generation.";
        $this->thing_report["help"] = "Click on the image for a PDF.";

        $this->node_list = ["douglas" => ["douglas", "uuid"]];

        $this->current_time = $this->thing->json->time();

        $this->initDouglas();
    }

    public function set()
    {
        $this->setDouglas();
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    public function rockyDouglas() {
$thing = new Thing(null);
$thing->Create("merp","merp","rocky");
    $agent = new Rocky($thing, "rocky");

    if (isset($messages[$agent->text])) {
        $this->response .= "Duplicate detected. Skipped. ";
    }
 //   $messages[$agent->text] = true;

    if (!isset($agent->thing_report['pdf'])) {
        $this->response .= "No PDF found. Skipped. ";
 //       continue;
    }
/*
$zip = new \ZipArchive;
var_dump($zip);
*/
/*
$zipContent = null;
$fp = tmpfile();
fwrite($fp, $zipContent);
$stream = stream_get_meta_data($fp);
$filename = $stream['uri'];
*/

$zip = new \ZipArchive;


//$zip->open($filename);



$ZIP_ERROR = [
  \ZipArchive::ER_EXISTS => 'File already exists.',
  \ZipArchive::ER_INCONS => 'Zip archive inconsistent.',
  \ZipArchive::ER_INVAL => 'Invalid argument.',
  \ZipArchive::ER_MEMORY => 'Malloc failure.',
  \ZipArchive::ER_NOENT => 'No such file.',
  \ZipArchive::ER_NOZIP => 'Not a zip archive.',
  \ZipArchive::ER_OPEN => "Can't open file.",
  \ZipArchive::ER_READ => 'Read error.',
  \ZipArchive::ER_SEEK => 'Seek error.',
];
$fp = tmpfile();
$stream = stream_get_meta_data($fp);
$filename = $stream['uri'];

$result_code = $zip->open($filename, \ZipArchive::CREATE);
//ls$result_code = $zip->open('/var/www/stackr.test/resources/douglas/test2.zip', \ZipArchive::CREATE);

if( $result_code !== true ){
   $msg = isset($ZIP_ERROR[$result_code])? $ZIP_ERROR[$result_code] : 'Unknown error.';
$this->response .= $msg ." ";
   return ['error'=>$msg];
}

$this->response .=  "Building zip file. ";

    $zip->addFromString('rocky_asdfsa.pdf', $agent->thing_report['pdf']);

    $zip->close();

header('Content-type: application/zip');
header('Content-Disposition: attachment; filename="download.zip"');
$contents = file_get_contents($filename);

file_put_contents('/var/www/stackr.test/resources/douglas/test5.zip', $contents);

    }

    /**
     *
     */
    public function makeChoices()
    {
        $this->choices = false;
        $this->thing_report["choices"] = $this->choices;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $sms = "DOUGLAS | ";

        $response_text = "No response.";
        if ($this->response != "") {
            $response_text = $this->response;
        }

        $sms .= $response_text;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     */

    /**
     *
     */

    public function setDouglas()
    {
    }

    /**
     *
     * @return unknown
     */
    public function getDouglas()
    {
    }

    /**
     *
     */
    public function initDouglas()
    {
    }

    public function readDouglas($text = null)
    {
    }

    public function run()
    {
    }

    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/douglas.pdf";
        $this->node_list = ["douglas" => ["douglas"]];
        $web = "";

        if (isset($this->html_image)) {
            $web .= '<a href="' . $link . '">';
            $web .= $this->html_image;
            $web .= "</a>";
        }
        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "douglas",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["douglas", "refreshed_at"],
                $time_string
            );
        }
    }

    public function isDouglas($text)
    {
        if (stripos($text, "douglas") !== false) {
            return true;
        }

        // Contains word douglas?
        return false;
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);

        $this->readDouglas($input);

        $pieces = explode(" ", strtolower($input));
$this->rockyDouglas();
        if (count($pieces) == 1) {
            if ($input == "douglas") {
                $this->getDouglas();
                return;
            }
        }

        $this->getDouglas();

        return;
    }
}
