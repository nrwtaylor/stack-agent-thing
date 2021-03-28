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
        //$this->thing_report["help"] =
        //    "Click on the ZIP link for a package of PDFs.";

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
        $this->makeZip();
        $this->makeHelp();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    public function rockyDouglas($uuid = null)
    {
        $thing = new Thing($uuid);
        if ($uuid === null) {
            $thing->Create("merp", "merp", "rocky");
        }
        $agent = new Rocky($thing, "rocky");

        if (!isset($agent->thing_report["pdf"])) {
            return true;
        }

        if ($uuid === null) {
            $this->thing->associate($thing->uuid);
        }
        return [$thing->nuuid, $agent->thing_report["pdf"]];
    }

    public function zipDouglas()
    {
        if (class_exists("\ZipArchive") == false) {
            $this->zip_error = true;
            $this->response .= "ZIP archive not available. ";
            return true;
        }

        $zip = new \ZipArchive();

        $ZIP_ERROR = [
            \ZipArchive::ER_EXISTS => "File already exists.",
            \ZipArchive::ER_INCONS => "Zip archive inconsistent.",
            \ZipArchive::ER_INVAL => "Invalid argument.",
            \ZipArchive::ER_MEMORY => "Malloc failure.",
            \ZipArchive::ER_NOENT => "No such file.",
            \ZipArchive::ER_NOZIP => "Not a zip archive.",
            \ZipArchive::ER_OPEN => "Can't open file.",
            \ZipArchive::ER_READ => "Read error.",
            \ZipArchive::ER_SEEK => "Seek error.",
        ];
        $fp = tmpfile();
        $stream = stream_get_meta_data($fp);
        $filename = $stream["uri"];

        $result_code = $zip->open($filename, \ZipArchive::CREATE);

        if ($result_code !== true) {
            $msg = isset($ZIP_ERROR[$result_code])
                ? $ZIP_ERROR[$result_code]
                : "Unknown error.";
            $this->response .= $msg . " ";
            return ["error" => $msg];
        }

        if (
            isset($this->association_uuids) and
            count($this->association_uuids) > 0
        ) {
            foreach ($this->association_uuids as $i => $association_uuid) {
                [$nuuid, $pdf_string] = $this->rockyDouglas($association_uuid);
                $zip->addFromString("rocky_" . $nuuid . ".pdf", $pdf_string);
            }
        } else {
            $sheet_count = 1;
            if (isset($this->sheet_count)) {
                $sheet_count = $this->sheet_count;
            }

            $this->response .= "Made " . $sheet_count . " sheets. ";
            foreach (range(1, $sheet_count) as $i) {
                [$nuuid, $pdf_string] = $this->rockyDouglas();
                $zip->addFromString("rocky_" . $nuuid . ".pdf", $pdf_string);
            }
        }

        $zip->close();

        $this->response .= "Built ZIP file. ";

        //        header("Content-type: application/zip");
        //        header('Content-Disposition: attachment; filename="download.zip"');

        $contents = @file_get_contents($filename);
        $this->contents_zip = $contents;
    }

    public function makeZip()
    {
        $this->thing_report["zip"] = $this->contents_zip;
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
        $sheet_count = null;
        if (isset($this->sheet_count)) {
            $sheet_count = $sheet_count;
        }
        $sheet_count = $this->thing->json->writeVariable(
            ["douglas", "sheet_count"],
            $sheet_count
        );
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
        if ($text === null) {
            return;
        }
        $number = $this->extractNumber($text);

        if (is_int($number) and $number >= 1) {
            $this->sheet_count = $number;
        }
    }

    public function run()
    {
    }

    public function makeHelp()
    {
        $help = "ZIP files are not available on this stack.";
        if (!isset($this->zip_error) or $this->zip_error !== true) {
            $help = "Click on the ZIP link for a package of PDFs.";
        }
        $this->help = $help;
        $this->thing_report["help"] = $help;
    }

    /**
     *
     */
    public function makeWeb()
    {
        $web = "";
        if (!isset($this->zip_error) or $this->zip_error !== true) {
            $link = $this->web_prefix . "thing/" . $this->uuid . "/douglas.zip";
            $this->node_list = ["douglas" => ["douglas"]];
            $web .=
                "ZIP file with " .
                $this->sheet_count .
                " randomly generated sheets.<br>";

            $web .= '<a href="' . $link . '">';
            $web .= $link;
            $web .= "</a>";
            $web .= "<br>";

            $web .= "<p>";
            $web .= "Individual radiograms.<br>";
        }
        if (isset($this->association_uuids)) {
            foreach ($this->association_uuids as $i => $uuid) {
                $rocky_link =
                    $this->web_prefix . "thing/" . $uuid . "/rocky.pdf";

                $web .= '<a href="' . $rocky_link . '">';
                $web .= $rocky_link;
                $web .= "</a><br>";
            }
        }

        $this->thing_report["web"] = $web;
    }

    public function get()
    {
        //echo "associations" . $this->thing->associations ."\n";

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

        $sheet_count = $this->thing->json->readVariable([
            "douglas",
            "sheet_count",
        ]);
        $this->sheet_count = $sheet_count;
        $associations = null;
        if (isset($this->thing->thing->assocations)) {
            $associations = json_decode(
                $this->thing->thing->associations,
                true
            );
        }
        if ($associations === null) {
            return;
        }
        $association_uuids = $associations["agent"];
        $this->association_uuids = $association_uuids;
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
        //        $input = strtolower($this->subject);
        $input = $this->assert($this->input, "douglas", false);
        //        $this->readDouglas($input);

        $number = $this->extractNumber($input);
        $number = intval($number);
        if (is_int($number) and $number >= 1) {
            $this->sheet_count = $number;
        }

        $l = $this->web_prefix . "thing/" . $this->thing->uuid . "/douglas.zip";

        //        $input = strtolower($this->subject);

        //        $this->readDouglas($input);

        $pieces = explode(" ", strtolower($input));
        $this->zipDouglas();
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
