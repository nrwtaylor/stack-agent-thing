<?php
namespace Nrwtaylor\StackAgentThing;

// Display all errors in production.
// The site must run clean transparent code.
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

// This is written to be understandable.
// Apologies.

// Manage an item.

class Item extends Agent
{
    public $var = 'hello';

    public function init()
    {
        //$this->initItem();

        $this->item = null;
        $this->items = [];

        $this->keywords = ["item"];

        $this->max_price = null;

        if (!isset($this->subject_init)) {
            $this->subject_init = $this->subject;
        }

        // This sets how long the stack will remember a particular search for.
        $this->retain_for = 4; // Retain for at least 4 hours.
        $this->persistence = 24; // And persist 24 hours from the last read.

        // Allow for a new state tree to be introduced here.
        $this->node_list = ["start" => ["helpful", "useful"]];

        $this->thing_report['info'] = 'Item did not add anything useful.';
        $this->thing_report['help'] =
            "An agent which provides insight on items.";

        $this->item_cache = 'on';

        if (isset($this->thing->container['api']['item']['item_cache'])) {
            $this->item_cache =
                $this->thing->container['api']['item']['item_cache'];
        }

        $this->item_horizon = 99;
        if (isset($this->thing->container['api']['item']['item_horizon'])) {
            $this->item_horizon =
                $this->thing->container['api']['item']['item_horizon'];
        }

        $this->default_item = null;
        if (isset($this->thing->container['api']['item']['default_item'])) {
            $this->default_item =
                $this->thing->container['api']['item']['default_item'];
        }

        $this->item_match_minimum = 1;
        if (
            isset($this->thing->container['api']['item']['item_match_minimum'])
        ) {
            $this->item_match_minimum =
                $this->thing->container['api']['item']['item_match_minimum'];
        }

        $this->item_grab_size = 99;
        if (isset($this->thing->container['api']['item']['item_grab_size'])) {
            $this->item_grab_size =
                $this->thing->container['api']['item']['item_grab_size'];
        }

        // No item cache.
        // Select best tiles for each item view. From MySQL tile database.
        $this->run_count = 0;

        $this->item_pointer = 0; // What is being pointed at.
        $this->index = 0; // Where we are working.

        $this->item_id = "X";
    }

    public function itemGet()
    {
        $this->thing->db->setFrom($this->from);

        $this->thing->json->setField("variables");
        $item = $this->thing->json->readVariable(["item", "item"]);
        /*
        if ($this->item != false) {
            $this->item['created_at'] = $this->thing->thing->created_at;
            $this->item['price'] = 1;

            //       }
            //$this->variablesGet();

            $this->response .= "Got item from message0. ";
        }
*/
        if ($item === false) {
            $item = $this->default_item;
        }

        $this->item = $item;
        $this->price_amount = "X";
        if (isset($item['price'])) {
            $this->price_amount = $item['price'];
        }

        return $this->item;
    }

    public function getItems($text = null, $mode = "and")
    {
        //if ($this->tile_cache == "off") {

        //        return array(array(), array(), array());

        //}
        $text = trim($text);

        $items = [];
        if ($text == "item") {
            return;
        }
        if ($text == "") {
            return;
        }

        $this->thing->log("Asked to get items for  " . $text . ".");

        $this->thing->log('start item cache subject search "' . $text . '".');

        $arr = explode("-", $this->getSlug($text));
        $t = "";
        foreach ($arr as $i => $token) {
            if ($mode == "and") {
                $t .= "+" . $token . " ";
            } else {
                $t .= " " . $token . " ";
            }
        }
        $text = trim($t);

        $thing_report = $this->thing->db->subjectSearch(
            $text,
            "item",
            $this->item_grab_size,
            "boolean"
        );

        $this->thing->log('item cache subject search complete.', "DEBUG");

        $things = $thing_report["things"];
        $count = count($things);
        $this->thing->log(
            'found ' . $count . ' items which responded to "' . $text . '".'
        );
        echo "item<br>";
        foreach (array_reverse($things) as $i => $thing) {
            $variables_json = $thing['variables'];
            $variables = $this->thing->json->jsontoArray($variables_json);
            if (!isset($variables['item'])) {
                echo "No item found<br>";
                continue;
            }

            $item = $variables['item'];
            echo "Found " . $item['title'] . ' in the item cache./n';
            $items[] = $item;
        }

        $this->thing->log('made ' . count($this->items) . ' items.', "DEBUG");
        $this->addItems($items, false);

        return;
    }

    public function selectItems($text)
    {
        $slug_agent = new Slug(null, "slug");

        $this->matching_items = null;

        if (!isset($this->items) or $this->items == []) {
            $this->getItems($text);
        }

        $text_words = explode(" ", strtolower($text));

        foreach ($this->items as $i => $item) {
            if (!isset($item['title'])) {
                return;
            }
            $item_title = $item['title'];

            //            $tile_words = explode(" ", strtolower($tile_title));
            $item_words = explode("-", $this->getSlug($item_title['title']));

            //$text_words = explode(" " , strtolower($text));
            $count = 0;
            foreach ($item_words as $j => $item_word) {
                foreach ($text_words as $k => $text_word) {
                    //echo $tile_word . " " . $text_word . "<br>";
                    if (strtolower($item_word) == strtolower($text_word)) {
                        $count += 1;
                    }
                    //echo $tile_word ." " . $text_word . "<br>";
                }
            }

            if ($count >= 3) {
                $this->matching_items[$item['id']] = $item;
            }
        }
    }

    public function cacheItem($item)
    {
        // So this bit is working.
        if (!isset($item['title'])) {
            return true;
        }

        if ($this->gearman_state == "off") {
            return;
        }

        if ($this->item_cache == "off") {
            return;
        }

        $client = new \GearmanClient();
        $client->addServer();
        $arr = json_encode([
            "to" => $this->from,
            "from" => "item",
            "subject" => $item['title'],
            "agent_input" => $item,
        ]);

        $client->doHighbackground("call_agent", $arr);
    }

    public function respondResponse()
    {
        $agent_flag = true;
        if ($this->agent_name == "agent") {
            return;
        }

        if ($agent_flag == true) {
            //        if ($this->agent_input == null) {
            //          $this->respond();
            //      }

            if (!isset($this->thing_report['sms'])) {
                $this->thing_report['sms'] = "ITEM | Standby.";
            }

            $this->thing_report['message'] = $this->thing_report['sms'];

            if ($this->agent_input == null or $this->agent_input == "") {
                //             $message_thing = new Message($this->thing, $this->thing_report);
                //             $this->thing_report['info'] =
                //                 $message_thing->thing_report['info'];
            }
        }
    }

    public function getWords($items = null)
    {
        global $wp;

        $this->words = [];
        foreach ($this->items as $vendor_id => $item) {
            $slug = $wp->slug_agent->extractSlug($item['title']);
            $words = explode("-", $slug);
            $this->words = array_merge($this->words, $words);

            $this->words = array_filter($this->words, function ($arrayEntry) {
                return !is_numeric($arrayEntry);
            });
        }
        $this->words = array_unique($this->words);

        return $this->words;
    }
    /*
    public function getNgrams($items = null)
    {
        global $wp;

        $this->ngram_agent = new Ngram($this->thing, "ngram");

        $this->ngrams = array();
        foreach ($this->items as $vendor_id => $item) {
            $slug = $wp->slug_agent->extractSlug($item['title']);
            //$words = explode("-", $slug);

            $s = str_replace("-", " ", $slug);
            $ngrams = $this->ngram_agent->getNgrams($s, 2);

            $this->ngrams = array_merge($this->ngrams, $ngrams);

            $this->ngrams = array_filter($this->ngrams, function ($arrayEntry) {
                return !is_numeric($arrayEntry);
            });
        }
        $this->ngrams = array_unique($this->ngrams);

        return $this->ngrams;
    }
*/
    public function getAge($item)
    {
        $age = "Fresh";
        if (isset($item['created_at'])) {
            $created_at = strtotime($item['created_at']);
            $age_seconds = time() - $created_at;
            $age = $this->thing->human_time($age_seconds);
        }
        return $age_seconds;
    }

    public function capItems($number = null)
    {
    }

    public function makeSMS()
    {
        $this->thing_report['sms'] =
            "ITEM | " .
            $this->response .
            " | Got " .
            count($this->items) .
            "items.";
        $this->sms_message = "ITEM | " . $this->thing_report['sms'];
    }

    public function run()
    {
        // Call to generate pattern for this class.
        $this->spamTitle();
    }

    public function makeResponse()
    {
        // This is a short simple structured response.
        $this->response .= '<div class="item">DEV ITEM</div>';
        $this->response .= "";
        $this->response .= 'Asked about,"' . $this->subject . '"' . '. ';
        $post_title = $this->getTitle();
        $this->response .= 'The title of this post is "' . $post_title . '". ';
    }

    public function hasItem($items = null)
    {
        if ($items == null) {
            $items = $this->items;
        }
        foreach ($items as $id => $item) {
            if ($this->isItem($item['title'])) {
                $this->thing->log(
                    "Item  " . $item['title'] . "found in the list."
                );

                return true;
                break;
            }
        }
        return false;
    }

    public function matchItem($test_text)
    {
        //$tokens = explode(" " ,$test_text);

        $found_post_title = get_the_title();
        $test_found_post_title = strtolower(
            str_replace("-", " ", $found_post_title)
        );

        $arr = explode("-", $this->getSlug($found_post_title));

        $n = end($arr);
        if (is_numeric($n) and $n > 1000000) {
            $number = $n;
        }

        //$test_found_post_title = implode(" " ,$arr);
        //$test_found_post_title = trim($test_found_post_title);
        $test_found_post_title = strtolower(
            str_replace(" ", "", $test_found_post_title)
        );

        //$alpha_agent = new Alpha($this->thing,"alpha");

        $test_found_post_title = preg_replace(
            "/[^a-zA-Z0-9]+/",
            "",
            $found_post_title
        );

        $test_found_post_title = strtolower($this->stripText(get_the_title()));
        //$test_found_title = str_replace("-"," ",$wp->slug_agent->getSlug(get_the_title()) );

        if (isset($number)) {
            $test_found_post_title = str_replace(
                $number,
                "",
                $test_found_post_title
            );
        }

        $test_text = strtolower($this->stripText($test_text));
        $match_number = similar_text(
            strtolower($test_found_post_title),
            strtolower($test_text),
            $percent
        );

        return $percent;
    }

    public function isItem($test_text)
    {
        $percent = $this->matchItem($test_text);

        // Trialled at 95.
        // Now trialling at 90.
        if ($percent > $this->item_match_minimum) {
            return true;
        }
        return false;
    }

    public function closestItem()
    {
        //        $this->item_id = null;
        //        $this->item = null;

        //        $this->index = 0;
        $lev_min = 1e99;
        $index = -1;
        //        if ($item_id == null) {
        foreach ($this->items as $i => $item) {
            if (!isset($nearest)) {
                $nearest = $item["title"];
            }
            $title = $item['title'];

            $lev_distance = levenshtein(
                strtolower($this->subject),
                strtolower($title)
            );
            if ($lev_distance < $lev_min) {
                $lev_min = $lev_distance;
                $nearest = $title;
                $index = $i;
            }
        }

        if ($index == -1) {
            //$this->item_id = null;
            //$this->item = null;
            return null;
        }
        //        }
        $this->thing->log(
            'picked the closest item ' . $i . '. Which is, "' . $nearest . '".'
        );
        $item = $this->items[$index];
        return $item;
    }

    public function setItem($item = null)
    {
        if ($item == null) {
            $item = $this->item;
        }

        $this->thing->log("set Item.");
        if (!isset($item['created_at'])) {
            $item['created_at'] = $this->thing->time();
        }

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(["item", "item"], $item);
    }

    public function countItems($items = null)
    {
        if ($items == null) {
            $items = $this->items;
        }
        $fresh_count = 0;
        $count = 0;
        foreach ($items as $i => $item) {
            $age = $this->getAge($item); // seconds
            if ($age / (60 * 60) < $this->item_horizon) {
                $fresh_count += 1;
            }
            $count += 1;
        }
        $this->fresh_count = $fresh_count;
        $this->count = $count;
        return $this->fresh_count;
    }

    public function pointerItem($item_id = null)
    {
        if ($item_id == null) {
            $item_id = $this->item_id;
        }

        foreach ($this->items as $i => $item) {
            if ($item['id'] == $this->item_id) {
                $this->item_pointer = $i;
                break;
            }
        }
    }

    public function doItem($text = null)
    {
        //    public function getItems($text = null)
        global $wp;
        //if (count($this->items) > 20) {$this->no_api = true;}

        $this->thing->log('asked to get "' . $text . '".');

        if ($text == null) {
            return true;
        }

        // Get the tiles we are going to display.
        // Add them to the list of items we have.
        global $wp;

        // devstack explore retaining the prior search items.
        // Especially when there are no results which come back.
        if (!isset($this->items)) {
            $this->items = [];
        }

        $post_title = $text;

        $this->search_text = $text;

        //        $post_title = $this->filterItem($text);
        //$this->item_title = $post_title;
        if ($post_title == "") {
            //$this->items = array();
            $this->response = "Empty query. ";
            return;
        }
    }

    public function vendorItems($vendor_items, $cache_flag = true)
    {
        if ($vendor_items == false) {
            $this->thing->log("Amazon items received false.");
            $this->response .= "Nothing back from amazon. ";
            return true;
        }

        if (!isset($vendor_items)) {
            return true;
        }

        $count = 0;
        if ($vendor_items != null) {
            $count = count($vendor_items);
        }
        $this->addItems($vendor_items);

        if ($cache_flag) {
            $this->makeCache($vendor_items);
        }
        //                $this->makeCache($ebay_items);
    }

    public function makeCounts()
    {
        $this->count = count($this->items);

        $fresh_count = 0;
        foreach ($this->items as $i => $item) {
            $age = $this->getAge($item);
            if ($age / (60 * 60) < $this->item_horizon) {
                $fresh_count += 1;
            }
        }

        $this->fresh_count = $fresh_count;
    }

    public function extractItem($text = null)
    {
        //$this->tile_title = $arr['subject'];
        if (isset($text['agent_input'])) {
            $item = $text['agent_input'];

            $this->item = $item;
            $this->source = "null";
            return;
        }

        //$tokens = explode(" ", $this->post_title);
        $tokens = $this->getTokens($this->post_title);
        $last_title_token = end($tokens);

        $title_numbers = [];
        foreach ($tokens as $i => $token) {
            if (is_numeric($token)) {
                $title_numbers[] = $token;
            }
        }
        //$title_numbers = $this->title_numbers;

        $vendor_id = null;
        if (
            end($title_numbers) == $last_title_token and
            mb_strlen($last_title_token) >= mb_strlen("113726988485") - 1
        ) {
            $vendor_id = $last_title_token;
            if (!isset($this->item_id) or $this->item_id == "X") {
                $this->item_id = $vendor_id;
            }
        }

        return $this->item_id;
    }

    public function echoItem($item = null)
    {
        if ($item == null) {
            $item = $this->item;
        }
        $t = "";
        foreach ($item as $key => $value) {
            if ($key == "ebay") {
                continue;
            }
            $t .= $key . " " . $value . "<br>";
        }
        echo $t;
    }

    public function get()
    {
        $this->itemGet();
    }

    public function set()
    {
        if (isset($this->item)) {
            $this->setItem($this->item);
        }

        // And note the time.
        $this->thing->db->setFrom($this->from);
        $this->thing->json->setField("variables");

        $time_string = $this->thing->json->time();
        $this->thing->json->writeVariable(
            ["item", "refreshed_at"],
            $time_string
        );

        //$count = count($this->items);

        //$this->thing->json->writeVariable(array("item", "item"), $this->item);

        $this->thing->log("Set item.");
    }

    public function parseItem($item)
    {
        // Should be able to handle any kind of item.
        $item = $this->thing->ebay_agent->parseItem($ebay_item);
        return $item;
    }

    public function schemaItem()
    {
    }

    public function makeWeb()
    {
        if (is_array($this->items)) {
            if (count($this->items) == 0) {
                $response = "";
                if (isset($this->response)) {
                    $response = $this->response;
                }

                $html = '<div class="empty-return">';
                $html .=
                    'Sorry we could not find what you are looking for. Try searching again. ' .
                    $response;
                $html .= '</div>';
                $this->thing_report['web'] = $html;
                return;
            }
        }
    }

    public function readSubject()
    {
        // An array. Probably Gearman sending a tile to be cached.
        if (is_array($this->agent_input) and $this->agent_input != []) {
            $this->thing->log("Found an array. Extract. Set.");
            echo "found an array. extracting item.";
            $this->extractItem($this->agent_input);
            $this->setItem($this->item);
            //            $this->getItems($this->agent_input);

            $this->response .=
                'Extracted tile from "' . $this->agent_input . '".';

            return;
        }

        if (is_string($this->input)) {
            $this->getItems($this->agent_input);
            $this->selectItems($this->input);

            $this->response .= 'Extracted tile from "' . $this->input . '".';
            return;
        }

        $this->getItems($this->agent_input);
        $this->response .= 'Get items.';

        //$this->selectTiles($this->input);
        $this->thing->log("Read subject.", "DEBUG");
    }
}
