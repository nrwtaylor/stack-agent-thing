<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// https://www.etsy.com/developers/documentation/getting_started/requests

class Etsy extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->test = "Development code"; // Always
        $this->keywords = array('amazon', 'items');

$this->items_limit = 100;

$this->access_key = null;
if (isset($this->thing->container['api']['etsy']['keystring'])) {
        $this->access_key =
            $this->thing->container['api']['etsy']['keystring'];
}

$this->secret_key = null;
if (isset($this->thing->container['api']['amazon']['shared secret'])) {
        $this->secret_key =
            $this->thing->container['api']['amazon']['shared secret'];
}
$this->associate_tag = null;
if (isset($this->thing->container['api']['amazon']['associate tag'])) {
        $this->associate_tag =
            $this->thing->container['api']['amazon']['associate tag'];
}

$this->etsy_stack_state = 'off';
if (isset($this->thing->container['api']['etsy']['state'])) {
        $this->etsy_stack_state = $this->thing->container['api']['etsy']['state'];
}
        $this->run_time_max = 360; // 5 hours
        $this->link = "https://www.amazon.com";

        $this->response .= "Dev. ";

        $this->thing_report['help'] =
            'This requests products using the Amazon API.';
    }

    function run()
    {
  //      $this->getItemSearch();

        // Test
        //$this->getItemLookup("B0774T8DC6");
        //$this->getItemLookup("087162143127","UPC");
    }

    function set()
    {
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "amazon" . " " . $this->from
        );

        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->thing->log(
            $this->agent_prefix . 'loaded ' . $this->counter . ".",
            "DEBUG"
        );

        $this->counter = $this->counter + 1;
    }

function logAmazon($text) {

if ($text == null) {$text = "MErp";}



$log_text = "Error message not found.";
if (isset($text['errorMessage']['error']['message'])) {

$log_text = $text['errorMessage']['error']['message'];

}

$request = "No request. ";
if (isset($this->request)) {
$request = $this->request;
}

$thing = new Thing(null);
$thing->Create("meep","amazon", "g/ amazon error " . $request ." - ". $log_text);

        $this->thing->db->setFrom($this->from);

        $this->thing->json->setField("message1");
        $this->thing->json->writeVariable( array("ebay") , $text );

$this->response .= $request . " - " . $log_text ." " ;

}


    public function parseItem($amazon_item = null)
    {

        if ($amazon_item == null) {
            return true;
        }

        if (is_string($amazon_item)) {
            return true;
        }
        $source = "etsy";

        //$url = $amazon_item['DetailPageURL'];
        $etsy_id = "";
        if (isset($amazon_item['listing_id'])) {
            $etsy_id = $amazon_item['listing_id'];
        }
        //$url = $amazon_item['DetailPageURL'];
        $url = "";
        if (isset($amazon_item['url'])) {
            $url = $amazon_item['url'];
        }

        //$asin = $amazon_item['ASIN'];
/*
        $asin = "";
        if (isset($amazon_item['ASIN'])) {
            $asin = $amazon_item['ASIN'];
        }
*/
/*
        $author = "";
        if (isset($amazon_item['ItemAttributes']['Author'])) {
            $author = $amazon_item['ItemAttributes']['Author'];
        }

        $creator = "";
        if (isset($amazon_item['ItemAttributes']['Creator'])) {
            $creator = $amazon_item['ItemAttributes']['Creator'];
        }

        $manufacturer = "";
        if (isset($amazon_item['ItemAttributes']['Manufacturer'])) {
            $manufacturer = $amazon_item['ItemAttributes']['Manufacturer'];
        }

        $product_group = "";
        if (isset($amazon_item['ItemAttributes']['ProductGroup'])) {
            $product_group = $amazon_item['ItemAttributes']['ProductGroup'];
        }
*/
        $title = "";
        if (isset($amazon_item['title'])) {
            $title = $amazon_item['title'];
        }

        $description = "";
        if (isset($amazon_item['description'])) {
            $description = $amazon_item['description'];
        }

        $price = "";
        if (isset($amazon_item['price'])) {
            $price = $amazon_item['price'];
        }


$link_thumbnail = null;
if (isset($amazon_item['Images'][0]['url_75x75'])) {

$link_thumbnail = $amazon_item['Images'][0]['url_75x75'];

}

/*
if (($link_thumbnail == null) and (isset($amazon_item['SmallImage']['URL']))) {

$link_thumbnail = $amazon_item['SmallImage']['URL'];

}
*/
if ($link_thumbnail == null) {

$link_thumbnail = $this->web_prefix . "noimage.png";


}

$picture_urls = array();
if (isset($amazon_item->Images)) {
foreach($amazon_item->Images as $i=>$image_array) {

if (isset($image_array->url_570xN)) {

$picture_urls[] = $image_array->url_570xN;

}

}
}


        //$item = $this->parsedItem($amazon_item);
//echo "---<br>";
        $item = array(
            "id" => $etsy_id,
            "title" => $title,
            "description" => $description,
            "thumbnail" => $link_thumbnail,
            "link" => $url,
            "source" => "etsy:" . $etsy_id,
            "vendor" => $amazon_item,
            "picture_urls" => $picture_urls
        );

        return $item;
    }

    function getRequest($request_array = null)
    {

if (($request_array == null) or (!isset($request_array['keywords']))) {

return true;

}

//$request_array['keywords'] = "bananagrams";


$keywords = $request_array['keywords'];
//$keywords = "bananagrams";
$request = "https://openapi.etsy.com/v2/listings/active?keywords=".$keywords."&limit=".$this->items_limit. "&includes=Images:1&api_key=".$this->access_key;
//$request = 'https://openapi.etsy.com/v2/listings/active?api_key='.$this->access_key;
return $request;

    }

    function getEtsy($text)
    {
if ($this->etsy_stack_state == 'off') {
$this->response .= "Agent is off. ";
return true;
}
        $request = $text;

        $ch = curl_init($request);
        //curl_setopt($ch, CURLOPT_URL, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response_body = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$response = json_decode($response_body,true);
return $response;

    }

    function getItemSearch($text = null)
    {
if ($this->etsy_stack_state == 'off') {
$this->response .= "Agent is off. ";
return true;
}

if (($text == null) and ($this->search_words == null)) {return true;}

if ($text == null) {$text = $this->search_words;}

$keywords= $text;
        $keywords = urlencode($keywords);

        $this->response .=
            'Asked Etsy about the word "' . $this->search_words . '". ';
        $this->response .= 'Asked Etsy about the word "' . $keywords . '". ';

        $slug = $keywords;
        $index = "All";

        $request_array = array(
            "Operation" => "ItemSearch",
            "Keywords" => $slug,
            "SearchIndex" => $index
        );

//$slug_agent = new Slug($this->thing, "slug");
//$keywords = $slug_agent->getSlug($this->search_words);
//var_dump($keywords);
//exit();
$request_array = array("keywords"=>$keywords);

        $request = $this->getRequest($request_array);

        $amazon_array = $this->getEtsy($request);
        $total_results = $amazon_array['count'];

$items = array();
if ($total_results > 0) {
        $items = $amazon_array['results'];
}

//$this->more_results_url = $more_results_url;
if ($items == array()) {$this->items = array(); return;}

        foreach ($items as $i => $item) {
            $parsed_item = $this->parseItem($item);

            $this->items[] = $parsed_item;
        }



        // $this->items = $items;
        if (!isset($this->items)) {
            $this->getItems();
        }
        if ($items != null) {
            $this->items = array_merge($this->items, $items);
        }

        // This is where we choose the best return from eBay.
        $this->item = null;
        if (isset($this->items[0])) {
            $this->item = $this->items[0];
        }


        $this->items_count = count($this->items);

        $this->thing->log("got " . $this->items_count . " items.");

        return false;
    }

    function getItems($text = null)
    {
        $this->items = array();
    }




    function getItemLookup($item_id = null, $item_id_type = "ASIN")
    {

if ($this->etsy_stack_state == 'off') {
$this->response .= "Agent is off. ";
return true;
}



        if ($item_id == null) {
            if (isset($this->item_id)) {
                $item_id = $this->item_id;
            }
        }

        if ($item_id == null) {
            return true;
        }

        $this->response .= 'Asked Etsy about the item "' . $item_id . '". ';
        $this->response .= 'Asked Etsy about the item "' . $item_id . '". ';
/*
        $request_array = array(
            "Operation" => "ItemLookup",
            "ResponseGroup" => "Images,Small",
            "ItemId" => $item_id
        );
*/
        $request_array = array(
            "Operation" => "ItemLookup",
            "ResponseGroup" => "OfferFull",
            "ItemId" => $item_id
        );

        $item_id_type_array = array("SKU", "UPC");

        $index = "All";

        if (in_array($item_id_type, $item_id_type_array)) {
            $id_type_array = array(
                "IdType" => $item_id_type,
                "SearchIndex" => $index
            );

            $request_array = array_merge($request_array, $id_type_array);

            // merge
        }

        $request = $this->getRequest($request_array);

        $amazon_array = $this->getEtsy($request);

        $is_valid = "";
        if (isset($amazon_array['Items']['Request']['IsValid'])) {
            $is_valid = $amazon_array['Items']['Request']['IsValid'];
        }

        $total_results = "";
        if (isset($amazon_array['Items']['TotalResults'])) {
            $total_results = $amazon_array['Items']['TotalResults'];
        }

        $total_pages = "";
        if (isset($amazon_array['Items']['TotalPages'])) {
            $total_pages = $amazon_array['Items']['TotalPages'];
        }

        $more_results_url = "";
        if (isset($amazon_array['Items']['MoreSearchResultsUrl'])) {
            $more_results_url = $amazon_array['Items']['MoreSearchResultsUrl'];
        }

        $items = array();
        if (isset($amazon_array['Items']['Item'])) {
            $items = $amazon_array['Items']['Item'];
        }

        foreach ($items as $i => $item) {

            $parsed_item = $this->parseItem($item);
            $this->items[] = $parsed_item;
        }
        return false;
    }

    public function make()
    {
        $this->makeSMS();
        $this->makeMessage();
        $this->makeWeb();
    }

    public function respond()
    {
        // Thing actions

        $this->thing->flagGreen();
        // Generate email response.

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }
    }

    public function makeWeb()
    {
        $html = "<b>AMAZON</b>";
        $html .= "<p><b>Amazon definitions</b>";

        $this->html_message = $html;
        $this->thing_report['web'] = $this->html_message;
    }

    public function makeSMS()
    {
        //      $sms = "AMAZON | " . $this->response;
        $s = "";

if (isset($this->items)) {
        foreach ($this->items as $i => $item) {
if (isset($item['title'])) {
            $s .= $item['title'] . " / ";
}
        }
}

        $sms = "ETSY | " . $s . $this->response;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function makeMessage()
    {
        $message = "Etsy";
        $this->message = $message;
        $this->thing_report['message'] = $this->message;
    }

    public function readSubject()
    {
        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->subject);
        }

        $this->input = $input;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'etsy') {
                $this->search_words = null;
                $this->response .= "Asked Etsy about nothing. ";
                return;
            }
        }
        /*
        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'amazon':
                            break;

                        default:
                    }
                }
            }
        }
*/
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "etsy is")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("etsy is"));
        } elseif (($pos = strpos(strtolower($input), "etsy")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("etsy"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->response .= 'Asked Etsy about the word "' . $this->search_words . '". ';
$this->getItemSearch();
            return false;
        }

        $this->response .= "Message not understood. ";
        return true;
    }
}
