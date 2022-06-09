<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class DOCX extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->text = $this->agent_name;

        $command_line = null;

        $this->node_list = ["docx" => ["docx"]];

        $this->resource_path = $GLOBALS["stack_path"] . "resources/";

        // If it is an agent request for docx only generate docx (for speed)
        // Not yet built.
        if ($this->agent_input == "docx") {
            $this->makeDOCX();
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        // So maybe not choices, but the message needs to be passed.

        $this->thing_report["info"] = "This makes and reads a DOCX.";

        if (!isset($this->thing_report["help"])) {
            $this->thing_report["help"] = "No help available.";
        }

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

    }

    function makeTXT()
    {
        $txt = "";
        if (isset($this->text)) {
           $txt = 'A DOCX which says, "' . $this->text . '".';
        }
        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    function makeChoices()
    {
    }

    function makeWeb()
    {
        if (!isset($this->image_html)) {
            $this->makeDOCX();
        }

        $link = $this->web_prefix . "thing/" . $this->uuid . "/agent";

        $this->node_list = ["web" => ["docx"]];
        // Make buttons
        $this->createChoice($this->agent_name, $this->node_list, "web");
        $this->choices = $this->linksChoice("web");

        $web = '<a href="' . $link . '">' . $this->html_image . "</a>";
        $web .= "<br>";
        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    function makeSMS()
    {
        $sms = "DOCX | " . $this->text;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeMessage()
    {
        if (!isset($this->image_html)) {
            $this->makeDOCX();
        }

        $message = "Made a DOCX for you.<br>";
        $message .= $this->html_image;
        $message .= "<br>";

        $this->thing_report["message"] = $message;
    }

    public function makeDOCX($image = null)
    {
    }

    public function readDOCX($input_file = null)
    {
        if ($input_file == null) {
            return true;
        }
        $kv_strip_texts = '';
        $kv_texts = '';
        if (!$input_file || !file_exists($input_file)) {
            return false;
        }

        $zip_archive = new \ZipArchive();

        $zip = $zip_archive->open($input_file);

        if (!$zip || is_numeric($zip)) {
            return false;
        }
        $contents = false;
        for ($i = 0; $i < $zip_archive->numFiles; $i++) {
            $filename = $zip_archive->getNameIndex($i);
            if ($filename != "word/document.xml") {
                continue;
            }
            $contents = $zip_archive->getFromName($filename);
            break;
        }

        $zip_archive->close();

        if ($contents == false) {
            return true;
        }

        $kv_texts = $contents;
        $kv_texts = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $kv_texts);
        $kv_texts = str_replace('</w:r></w:p>', "\r\n", $kv_texts);
        $kv_texts = strip_tags($kv_texts);

        $this->texts = $kv_texts;

        return $kv_texts;
    }

    public function readSubject()
    {
    }
}
