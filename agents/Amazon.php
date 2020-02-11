<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// https://www.codediesel.com/php/accessing-amazon-product-advertising-api-in-php/

class Amazon extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->test = "Development code"; // Always
        $this->keywords = array('amazon', 'items');

        $this->access_key =
            $this->thing->container['api']['amazon']['access key'];
        $this->secret_key =
            $this->thing->container['api']['amazon']['secret key'];

        $this->associate_tag =
            $this->thing->container['api']['amazon']['associate tag'];

        $this->run_time_max = 360; // 5 hours
        $this->link = "https://www.amazon.com";

        $this->response = "Dev. ";

        $this->thing_report['help'] =
            'This requests products using the Amazon API.';
    }

    function run()
    {
        $this->getItemSearch();

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

}


    public function parseItem($amazon_item = null)
    {
        if ($amazon_item == null) {
            return true;
        }

        if (is_string($amazon_item)) {
            return true;
        }

        $source = "amazon";

        //$url = $amazon_item['DetailPageURL'];
        $url = "";
        if (isset($amazon_item['DetailPageURL'])) {
            $url = $amazon_item['DetailPageURL'];
        }

        //$asin = $amazon_item['ASIN'];

        $asin = "";
        if (isset($amazon_item['ASIN'])) {
            $asin = $amazon_item['ASIN'];
        }

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

        $title = "";
        if (isset($amazon_item['ItemAttributes']['Title'])) {
            $title = $amazon_item['ItemAttributes']['Title'];
        }

$link_thumbnail = null;
if (isset($amazon_item['MediumImage']['URL'])) {

$link_thumbnail = $amazon_item['MediumImage']['URL'];

}

/*
if (($link_thumbnail == null) and (isset($amazon_item['SmallImage']['URL']))) {

$link_thumbnail = $amazon_item['SmallImage']['URL'];

}
*/
if ($link_thumbnail == null) {

$link_thumbnail = $this->web_prefix . "noimage.png";


}



        //$item = $this->parsedItem($amazon_item);
//echo "---<br>";
        $item = array(
            "id" => $asin,
            "title" => $title,
            "thumbnail" => $link_thumbnail,
            "link" => $url,
            "source" => "amazon:" . $asin,
            "amazon" => $amazon_item
        );

/* ebay reference
            "source"=>$source,
            "id" => $item_id,
            "category_name" => $category_name,
            "description" => $description,
            "condition_description" => $condition_description,
            "title" => $title,
            "price" => $price_text,
            "link" => $link,
            "thumbnail" => $link_thumbnail,
            "location" => $location,
            "country" => $country,
            "html_link" => $html_link,
            "picture_urls"=> $picture_urls,
            "ebay"=>$ebay_item
*/

        return $item;
    }

    function getRequest($request_array = null)
    {
        $index = "All";

        $region = "com";
        $method = "GET";
        $host = "webservices.amazon." . $region;
        $uri = "/onca/xml";

        $arr = array(
            "Service" => "AWSECommerceService",
            "AWSAccessKeyId" => $this->access_key,
            "AssociateTag" => $this->associate_tag,
            "Timestamp" => gmdate("Y-m-d\TH:i:s\Z")
        );

        if ($request_array == null) {
            $request_array = array(
                "Operation" => "ItemSearch",
                "Keywords" => $slug,
                "SearchIndex" => $index
            );
        }

        $arr = array_merge($arr, $request_array);

        ksort($arr);

        foreach ($arr as $parameter => $value) {
            $parameter = str_replace("%7E", "~", rawurlencode($parameter));
            $value = str_replace("%7E", "~", rawurlencode($value));
            $canonicalized_query[] = $parameter . "=" . $value;
        }

        $canonicalized_query = implode("&", $canonicalized_query);
        $string_to_sign =
            $method . "\n" . $host . "\n" . $uri . "\n" . $canonicalized_query;

        // Calculate an RFC 2104-compliant HMAC with the SHA256 hash algorithm

        $signature = base64_encode(
            hash_hmac('sha256', $string_to_sign, $this->secret_key, true)
        );
        /* encode the signature for the request */
        //   $signature = str_replace("%7E", "~", rawurlencode($signature));

        $signature = urlencode($signature);

        /* create request */
        $request =
            "http://" .
            $host .
            $uri .
            "?" .
            $canonicalized_query .
            "&Signature=" .
            $signature;

        return $request;
    }

    function getAmazon($text)
    {
        $request = $text;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $xml_response = curl_exec($ch);

        $xml = simplexml_load_string($xml_response);

        if ($xml_response === false) {
            return false;
        } else {
            /* parse XML and return a SimpleXML object, if you would
           rather like raw xml then just return the $xml_response.
         */
            $parsed_xml = @simplexml_load_string($xml_response);
            //        return ($parsed_xml === False) ? False : $parsed_xml;
        }

        if ($parsed_xml === false) {
            $this->logAmazon($text);
            return true;
        }

        $json = json_encode($parsed_xml);
        $amazon_array = json_decode($json, true);

        return $amazon_array;
    }

    function getItemSearch($text = null)
    {
if (($text == null) and ($this->search_words == null)) {return true;}

if ($text == null) {$text = $this->search_words;}

$keywords= $text;
        $keywords = urlencode($keywords);

        $this->response .=
            'Asked Amazon about the word "' . $this->search_words . '". ';
        $this->response .= 'Asked Amazon about the word "' . $keywords . '". ';

        $slug = $keywords;
        $index = "All";

        $request_array = array(
            "Operation" => "ItemSearch",
            "Keywords" => $slug,
            "SearchIndex" => $index
        );

        $request = $this->getRequest($request_array);


        $amazon_array = $this->getAmazon($request);

if (!isset($amazon_array['Items'])) {
if (isset($amazon_array['Error'])) {

$this->logAmazon($amazon_array['Error']['Message']);
return true;
}
}

        $is_valid = $amazon_array['Items']['Request']['IsValid'];
        $total_results = $amazon_array['Items']['TotalResults'];
        $total_pages = $amazon_array['Items']['TotalPages'];
        $more_results_url = $amazon_array['Items']['MoreSearchResultsUrl'];


$items = array();
if ($total_results > 0) {
        $items = $amazon_array['Items']['Item'];
}

$this->more_results_url = $more_results_url;
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
        //https://docs.aws.amazon.com/AWSECommerceService/latest/DG/ItemLookup.html
        // IdType
        //Valid Values: SKU | UPC | EAN | ISBN (US only, when search index is Books). UPC is not valid in the CA locale.

        if ($item_id == null) {
            if (isset($this->item_id)) {
                $item_id = $this->item_id;
            }
        }

        if ($item_id == null) {
            return true;
        }

        $this->response .= 'Asked Amazon about the item "' . $item_id . '". ';
        $this->response .= 'Asked Amazon about the item "' . $item_id . '". ';
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

        $amazon_array = $this->getAmazon($request);

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

        $sms = "AMAZON | " . $s . $this->response;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function makeMessage()
    {
        $message = "Amazon";
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
            if ($input == 'amazon') {
                $this->search_words = null;
                $this->response .= "Asked Amazon about nothing. ";
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
        if (($pos = strpos(strtolower($input), "amazon is")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("amazon is"));
        } elseif (($pos = strpos(strtolower($input), "amazon")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("amazon"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            //        $this->response .= 'Asked Amazon about the word "' . $this->search_words . '". ';
            return false;
        }

        $this->response .= "Message not understood. ";
        return true;
    }
}
