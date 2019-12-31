<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// Etsy: "We believe in code as craft."

// devstack. Develop Item lookup.


class Etsy extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->test = "Development code"; // Always
        $this->keywords = array('etsy', 'items');

        $this->keystring =
            $this->thing->container['api']['etsy']['keystring'];
        $this->shared_secret =
            $this->thing->container['api']['etsy']['shared secret'];

        $this->associate_tag =
            $this->thing->container['api']['etsy']['associate tag'];

        $this->run_time_max = 360; // 5 hours
        $this->link = "https://www.etsy.com";

        $this->response = "Dev. ";

        $this->thing_report['help'] =
            'This requests products using the Etsy API.';
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
            "variables " . "etsy" . " " . $this->from
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

function logEtsy($text) {

if ($text == null) {$text = "MErp";}

//var_dump($text);
//var_dump($text['errorMessage']['error']['message']);


$log_text = "Error message not found.";
if (isset($text['errorMessage']['error']['message'])) {

$log_text = $text['errorMessage']['error']['message'];

}

$request = "No request. ";
if (isset($this->request)) {
$request = $this->request;
}

$thing = new Thing(null);
$thing->Create("meep","etsy", "g/ etsy error " . $request ." - ". $log_text);

        $this->thing->db->setFrom($this->from);

        $this->thing->json->setField("message1");
        $this->thing->json->writeVariable( array("etsy") , $text );

}


    public function parseItem($etsy_item = null)
    {
        if ($etsy_item == null) {
            return true;
        }

        if (is_string($etsy_item)) {
            return true;
        }

        $state = "";
        if (isset($etsy_item['state'])) {
            $url = $etsy_item['state'];
        }

        if ($state != "active") {return false;}

        $source = "etsy";

        $url = "";
        if (isset($etsy_item['url'])) {
            $url = $etsy_item['url'];
        }

        $sku = "";
        if (isset($etsy_item['sku'])) {
            $sku = $etsy_item['sku'];
        }

        $listing_id = "";
        if (isset($etsy_item['listing_id'])) {
            $listing_id = $etsy_item['listing_id'];
        }


/*
        $author = "";
        if (isset($etsy_item['ItemAttributes']['Author'])) {
            $author = $etsy_item['ItemAttributes']['Author'];
        }

        $creator = "";
        if (isset($etsy_item['ItemAttributes']['Creator'])) {
            $creator = $etsy_item['ItemAttributes']['Creator'];
        }

        $manufacturer = "";
        if (isset($etsy_item['ItemAttributes']['Manufacturer'])) {
            $manufacturer = $etsy_item['ItemAttributes']['Manufacturer'];
        }

        $product_group = "";
        if (isset($etsy_item['ItemAttributes']['ProductGroup'])) {
            $product_group = $etsy_item['ItemAttributes']['ProductGroup'];
        }

        $title = "";
        if (isset($etsy_item['title'])) {
            $title = $etsy_item['title'];
        }
*/

$link_thumbnail = null;
if (isset($etsy_item['Images'][0]["url_170x135"])) {

$link_thumbnail = $etsy_item['Images'][0]["url_170x135"];

}

var_dump($link_thumbnail);

/*
if (($link_thumbnail == null) and (isset($etsy_item['SmallImage']['URL']))) {

$link_thumbnail = $etsy_item['SmallImage']['URL'];

}
*/
if ($link_thumbnail == null) {

$link_thumbnail = $this->web_prefix . "noimage.png";


}

//var_dump($etsy_item['Price']);

/*
exit();
if (isset($etsy_item['MediumImage']['URL'])) {

$link_thumbnail = $etsy_item['MediumImage']['URL'];

}
*/


        //$item = $this->parsedItem($etsy_item);
//echo "---<br>";
//var_dump($etsy_item['ItemAttributes']);
//exit();
        $item = array(
            "id" => $listing_id,
            "title" => $title,
            "thumbnail" => $link_thumbnail,
            "link" => $url,
            "source" => "etsy:" . $listing_id,
            "etsy" => $etsy_item
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

//$keywords_text = "keywords=buch";
//$image_text = "includes=Images";

$request_text = "";
foreach($request_array as $key=>$value) {

$request_text .= "&" . $key . "=" . $value;

}
//var_dump($request_text);
//exit();

            //terms = $('#etsy-terms').val();
//            $request = "https://openapi.etsy.com/v2/listings/active.js?keywords=". $terms.
//"&limit=12&includes=Images:1&api_key=". $this->keystring;

//            $request = "https://openapi.etsy.com/v2/listings/active?api_key=". $this->keystring. "&keywords=" ."buch". "&" . $image_text;
//            $request = "https://openapi.etsy.com/v2/listings/active?api_key=". $this->keystring. "&" . $keywords_text . "&" . $image_text;
            $request = "https://openapi.etsy.com/v2/listings/active?api_key=". $this->keystring. $request_text;


return $request;


// -------------- merp ----------------------

        $index = "All";

//        var_dump($slug);
        //exit();
        $region = "com";
        $method = "GET";
        $host = "webservices.amazon." . $region;
        $uri = "/onca/xml";

        $arr = array(
            "Service" => "AWSECommerceService",
            "AWSAccessKeyId" => $this->keystring,
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

    function getEtsy($text)
    {
        $request = $text;
//var_dump($request);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $json_string_response = curl_exec($ch);

/*
        $xml_response = curl_exec($ch);
        $xml = simplexml_load_string($xml_response);

        if ($xml_response === false) {
            return false;
        } else {
         //    parse XML and return a SimpleXML object, if you would
         //  rather like raw xml then just return the $xml_response.
         
            $parsed_xml = @simplexml_load_string($xml_response);
            //        return ($parsed_xml === False) ? False : $parsed_xml;
        }
*/
        if ($json_string_response === false) {
            $this->logEtsy($text);
            return true;
        }

        $json_array = json_decode($json_string_response,true);
//        $etsy_array = json_decode($json, true);

        return $json_array;
    }

    function getItemSearch($text = null)
    {
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
            "keywords" => $slug,
            "includes" => "Images"
        );

        $request = $this->getRequest($request_array);

//        var_dump($request);

        $etsy_array = $this->getEtsy($request);


if (!isset($etsy_array['Items'])) {
if (isset($etsy_array['Error'])) {

$this->logEtsy($etsy_array['Error']['Message']);
return true;
}
}

//        $is_valid = $etsy_array['Items']['Request']['IsValid'];
        $total_results = $etsy_array['count'];
//        $total_pages = $etsy_array['Items']['TotalPages'];
//        $more_results_url = $etsy_array['Items']['MoreSearchResultsUrl'];
//var_dump($total_results);
        $items = $etsy_array['results'];


        foreach ($items as $i => $item) {
            $parsed_item = $this->parseItem($item);
//var_dump($parsed_item);
//exit();
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

//var_dump($this->items);

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

        $etsy_array = $this->getEtsy($request);

        $is_valid = $etsy_array['Items']['Request']['IsValid'];

        $total_results = "";
        if (isset($etsy_array['Items']['TotalResults'])) {
            $total_results = $etsy_array['Items']['TotalResults'];
        }

        $total_pages = "";
        if (isset($etsy_array['Items']['TotalPages'])) {
            $total_pages = $etsy_array['Items']['TotalPages'];
        }

        $more_results_url = "";
        if (isset($etsy_array['Items']['MoreSearchResultsUrl'])) {
            $more_results_url = $etsy_array['Items']['MoreSearchResultsUrl'];
        }

        $items = $etsy_array['Items']['Item'];


        foreach ($items as $i => $item) {
//var_dump($item['MediumImage']);
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
        $html = "<b>ETSY</b>";
        $html .= "<p><b>Etsy definitions</b>";

        $this->html_message = $html;
        $this->thing_report['web'] = $this->html_message;
    }

    public function makeSMS()
    {
        //      $sms = "ETSY | " . $this->response;
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
        //var_dump($input);
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
                        case 'etsy':
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
            //        $this->response .= 'Asked Etsy about the word "' . $this->search_words . '". ';
            return false;
        }

        $this->response .= "Message not understood. ";
        return true;
    }
}
