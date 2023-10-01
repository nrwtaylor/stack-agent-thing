<?php
namespace Nrwtaylor\StackAgentThing;
/*
Reads a folder of furniture text resources.
*/
class Furniture extends Agent
{
    public $var = "hello";

    function init()
    {
        // Load words
        // See if there is time to look for more words.
    }

    function get()
    {
        $this->getFurniture();
        $this->new_words = [];
    }

    function run()
    {
        $this->doFurniture();
    }

    function set()
    {
        $this->setFurniture();
    }

    public function getFurniture()
    {
        //echo "foo";
        $this->thing->console("Start getting uris");

        $this->uriFurniture(); // Get URIs
        $this->thing->console("Got uris");

        // test resource
        //$this->uris= ['/var/www/stackr.test/resources/furniture/itemlist.txt'];

        $contents = file_get_contents(
            $this->resource_path . "furniture/words.txt"
        );

        $lines = explode("\n", $contents);
        $this->lines = $lines;
        if (!isset($this->words)) {
            $this->words = [];
        }

        $this->words = array_merge($this->words, $this->lines);
        $this->words = array_unique($this->words);
        $this->words = array_map("trim", $this->words);
    }

    public function setFurniture()
    {
        $data = "";
        foreach ($this->new_words as $word => $value) {
            $data .= $word . "\r\n";
        }
        $this->thing->console("New words" . "\n");
        $this->thing->console(str_replace("\r\n", " ", $data) . "\n");

        file_put_contents(
            $this->resource_path . "furniture/words.txt",
            $data,
            FILE_APPEND
        );
    }

    public function doFurniture()
    {
        if ($this->agent_input == null) {
            $array = ["miao", "miaou", "hiss", "prrr", "grrr"];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "FURNITURE | " . strtolower($v) . ".";

            $this->furniture_message = $response; // mewsage?
        } else {
            $this->furniture_message = $this->agent_input;
        }

        $contents = $this->randomFurniture();
        $words = $this->findFurniture($contents); // Set this->new_words;
    }

    public function randomFurniture()
    {
        $random_uri = $this->uris[array_rand($this->uris)];
        return $random_uri;
        //$this->wordsFurniture($random_uri);
    }

    public function scanFurniture()
    {
        foreach ($this->uris as $i => $uri) {
            $words = $this->wordsFurniture($uri);
        }
    }

    public function wordsFurniture($text)
    {
        $contents = @file_get_contents($text);

        if ($contents === false) {
            return true;
        }

        $words = $this->extractWords($contents);
        $words = array_unique($words);

        //        foreach ($words as $i => $word) {
        //            $this->words[$word] = [
        //                "foundAt" => $this->current_time,
        //            ];
        //        }
        return $words;
    }

    public function findFurniture($text)
    {
        $words = $this->wordsFurniture($text);
        if ($words === true) {
            return true;
        }
        foreach ($words as $i => $word) {
            if (isset($this->words[$word])) {
                $value = $this->words[$word];
            }

            $value["foundAt"] = $this->current_time;

            $this->new_words[$word] = $value;
            $this->words[$word] = $value;
        }
        return $words;
    }

    public function uriFurniture()
    {
        if (!isset($this->uris)) {
            $this->uris = []; // Or load them in?
        }

        if (!isset($this->words)) {
            $this->words = []; // Or load them in?
        }

        $uris = $this->uris;
        $words = $this->words;

        $yourStartingPath = "/var/www/stackr.test/resources/furniture";
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($yourStartingPath),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $filenames = scandir($file->getRealpath());
                foreach ($filenames as $i => $filename) {
                    $uri = $file->getRealpath() . "/" . $filename;

                    if (is_dir($uri)) {
                        continue;
                    }
$this->thing->console($uri . "\n");
                    try {
                        $this->uris[] = $uri;
                    } catch (\Error $ex) {
                    } catch (Throwable $e) {
                    }
                }
            }
        }
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is about things that are furniture.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeSMS()
    {
        $this->node_list = ["furniture" => ["not cat", "not dog"]];
        $this->sms_message = "" . $this->furniture_message;
        $this->thing_report["sms"] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create("channel", $this->node_list, "furniture");
        $choices = $this->thing->choice->makeLinks("furniture");
        $this->thing_report["choices"] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
