<?php
namespace Nrwtaylor\StackAgentThing;

/*

BASELINE. BASELINE. BASELINK.
INTERLINKED.

*/

class Interlink extends Agent
{
    public $var = "hello";

    public function init()
    {
        $this->path = null;
        if (isset($this->thing->container["stack"]["path"])) {
            $this->path = $this->thing->container["stack"]["path"];
        }

        // Name of interlink file to write.
        // dev take filename as input from file_source.

        // Gutenberg reference
        // $this->file_name = $this->resource_path . "interlink/0602911.php";
        $this->file_name = $this->resource_path . "interlink/calendar.php";

        if (file_exists($this->file_name)) {
            include $this->file_name;
            $this->interlinks = $interlinks;
            $this->response .= "Loaded interlink file. ";
        }

        $this->initInterlink();
    }

    public function initInterlink()
    {
        // dev accept file_source as input

        // test on calendar.txt

        $this->file_source =
            "/home/nick/codebase/stackr-resources/calendar/calendar.txt";
        //$this->file_source = $this->resource_path . "book/0602911.txt";

        $this->slug_agent = new Slug($this->thing, "slug");
        $this->ngram_agent = new Ngram($this->thing, "ngram");

        $this->word_agent = new Word($this->thing, "word");
        $this->word_agent->ewolWords();

        $this->brilltagger_agent = new Brilltagger($this->thing, "brilltagger");
    }

    public function run()
    {
    }

    public function test()
    {
    }

    public function slugsInterlink($text = null)
    {
        if ($text == null) {
            return false;
        }
        if (!isset($this->slug_agent)) {
            $this->slug_agent = new Slug($this->thing, "slug");
        }
        if (!isset($this->ngram_agent)) {
            $this->ngram_agent = new Ngram($this->thing, "ngram");
        }

        $slugs = [];

        $arr = explode("\%20", trim(strtolower($text)));

        $agents = [];
        $onegrams = $this->ngram_agent->getNgrams($text, $n = 1);
        $bigrams = $this->ngram_agent->getNgrams($text, $n = 2);
        $trigrams = $this->ngram_agent->getNgrams($text, $n = 3);

        $arr = array_merge($arr, $onegrams);
        $arr = array_merge($arr, $bigrams);
        $arr = array_merge($arr, $trigrams);

        usort($arr, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        $ngrams = $arr;

        foreach ($ngrams as $i => $ngram) {
            $slug = $this->slug_agent->getSlug($ngram);
            if ($slug === true) {
                continue;
            }
            $slugs[] = $slug;
        }
        return $slugs;
    }

    public function readInterlink()
    {
        if ($this->interlink_make_flag === true or !isset($this->interlink)) {
            $this->interlinks = $this->makeInterlink();
            $this->response .=
                "Built new interlink file " . $this->file_name . ". ";
        }

        $this->txtInterlinks($this->interlinks);

        if ($this->agent_input == null) {
            $response = "Interlinker.";

            $this->interlink_message = $response;
        } else {
            $this->interlink_message = $this->agent_input;
        }
    }

    public function makeWeb()
    {
        $web = "";
        $this->thing_report["web"] = $web;
    }

    public function makeInterlink()
    {
        if (!isset($this->file_source)) {
            $this->response .= "No file source seen. ";
        }

        $filename = $this->file_source;

        $p = new Contents($this->thing, $filename);

        $uuid_agent = new Uuid($this->thing, "uuid");
        $ngram_agent = new Ngram($this->thing, "ngram");
        $slug_agent = new Slug($this->thing, "slug");
        $paragraph_agent = new Paragraph($this->thing, $p->contents);

        $paragraphs = $paragraph_agent->paragraphs;
        $interlinks = [];
        $slug_list = [];
        $index = 0;
        foreach ($paragraphs as $i => $paragraph) {
            // Ignore empty paragraphs.
            $paragraph = trim($paragraph);
            if ($paragraph == "") {
                continue;
            }

            $index += 1;
            $uuid = $this->thing->getUUid();

            $paragraph_slugs = $this->slugsInterlink($paragraph);

            $urls = $this->extractUrls($paragraph);
            $stripped_url_paragraph = $this->stripUrls($paragraph);
            $paragraph_slugs = $this->slugsInterlink($stripped_url_paragraph);

            $interlinks[$uuid] = [
                "text" => $stripped_url_paragraph,
                "index" => $index,
                "urls" => $urls,
            ];
            $slug_list[$uuid] = $paragraph_slugs;
        }
        // Make a list of uuids for each slug.
        // Make an array of slugs

        // Make references to paragraph before and after.
        $prior_uuid = null;
        foreach ($interlinks as $uuid => $interlink) {
            $interlinks[$uuid]["prior_uuid"] = $prior_uuid;
            $prior_uuid = $uuid;
        }

        $posterior_uuid = null;
        foreach (array_reverse($interlinks) as $uuid => $interlink) {
            $interlinks[$uuid]["posterior_uuid"] = $posterior_uuid;
            $posterior_uuid = $uuid;
        }

        $slugs = [];
        foreach ($interlinks as $uuid => $interlink) {
            if ($slug_list[$uuid] == []) {
                continue;
            }

            foreach ($slug_list[$uuid] as $i => $slug) {
                if ($this->isInterlink($slug) === false) {
                    continue;
                }

                if (!isset($slugs[$slug][$uuid]["count"])) {
                    $slugs[$slug][$uuid]["count"] = 0;
                }
                $slugs[$slug][$uuid]["count"] += 1;
            }
        }

        foreach ($interlinks as $uuid => $interlink) {
            if ($slug_list[$uuid] == false) {continue;}
            foreach ($slug_list[$uuid] as $i => $slug) {
                if (!isset($slugs[$slug])) {
                    continue;
                }

                $count = count($slugs[$slug]);

                if ($count <= 0) {
                    continue;
                }

                unset($slugs[$slug][$uuid]);
                $interlinks[$uuid]["slugs"][$slug] = $slugs[$slug];
            }
        }

        foreach ($interlinks as $uuid => $interlink) {
            if (!isset($interlink["slugs"])) {
                continue;
            }
            foreach ($interlink["slugs"] as $slug => $slug_uuids) {
                foreach ($slug_uuids as $i => $slug_uuid) {
                    //echo $uuid ." " . $i . "\n";
                    $distance =
                        $interlinks[$uuid]["index"] - $interlinks[$i]["index"];
                    //echo $distance ."\n";

                    $interlinks[$uuid]["slugs"][$slug][$i][
                        "distance"
                    ] = $distance;
                }
            }
        }

        // Extract time
        $extract_time_flag = true;
        if ($extract_time_flag === true) {
            $dateline_agent = new Dateline($this->thing, "dateline");
            foreach ($interlinks as $uuid => $interlink) {
                $dateline = $dateline_agent->extractDateline(
                    $interlink["text"]
                );
                $interlinks[$uuid]["dateline"] = $dateline;
            }
        }

        $this->saveInterlinks($interlinks);
        return $interlinks;
    }

    public function echoInterlinks($interlinks)
    {
        if (!isset($this->txt)) {
            $this->txtInterlinks($interlinks);
        }
        echo $this->txt;
    }

    public function isInterlink($text)
    {
        if ($text == "") {
            return false;
        }

        if (isset($this->word_agent->ewol_dictionary[$text])) {
            return false;
        }

        $tags = $this->brilltagger_agent->tag($text);

        $tokens = explode("-", $text);
        if (isset($tokens[0])) {
            if ($tokens[0] == "and") {
                return false;
            }
            if (end($tokens) == "and") {
                return false;
            }
            if (mb_strlen($tokens[0]) == 1) {
                return false;
            }

            if ($tokens[0] == "the") {
                return false;
            }
            if (end($tokens) == "the") {
                return false;
            }

            if ($tokens[0] == "of") {
                return false;
            }
            if (end($tokens) == "of") {
                return false;
            }

            if ($tokens[0] == "a") {
                return false;
            }
            if (end($tokens) == "a") {
                return false;
            }
        }

        return true;
    }

    public function txtInterlinks($interlinks)
    {
        $txt = "";
        foreach ($interlinks as $uuid => $interlink) {
            $txt .= $interlink["text"] . "\n";
            //$count = count($interlink['slugs']);
            if (isset($interlink["slugs"])) {
                foreach ($interlink["slugs"] as $slug => $uuids) {
                    if (!is_array($uuids)) {
                        continue;
                    }
                    $count = count($uuids);
                    $txt .= $slug . " " . $count . "\n";
                }
            }
            $txt .= "\n";
        }
        $this->txt = $txt;
    }

    public function makeTXT()
    {
        $this->thing_report["txt"] = $this->txt;
    }

    public function memoryInterlinks($interlinks)
    {
    }

    public function saveInterlinks($interlinks)
    {
        // Save a readable require file.
        $file = $this->file_name;
        file_put_contents(
            $file,
            "<?php\n\$interlinks = " . var_export($interlinks, true) . ";\n?>"
        );
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This reads interlinks between blocks of text (paragraphs).";
        $this->thing_report["help"] = "This is about links between things.";

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;
        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    function makeSMS()
    {
        $this->node_list = ["interlink" => ["interlink"]];
        $sms =
            "INTERLINK | " . $this->interlink_message . " " . $this->response;
        $this->sms_message = "" . $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report["choices"] = $choices;
    }

    public function readSubject()
    {
        $input = $this->input;
        $input = $this->assert($input, "interlink", false);

        $this->interlink_make_flag = false;
        if (stripos($input, "make") !== false) {
            $this->interlink_make_flag = true;
        }

        $this->readInterlink();
    }
}
