<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Ebay extends Agent
{

    // This gets items from the Ebay Finding API.

    public $var = 'hello';

    function init()
    {
        $this->test= "Development code"; // Always

//        $this->agent_name = "ebay";
//        $this->agent_prefix = 'Agent "Ebay" ';

        $this->node_list = array("ebay"=>array("ebay"));

        $this->keywords = array('ebay','catalog','catalogue');

        // devstack improve this call as $this->thing->api->ebay->appID.
        $this->application_id = $this->thing->container['api']['ebay']['app ID'];
        $this->application_key = $this->thing->container['api']['ebay']['cert ID']; 

        $this->run_time_max = 360; // 5 hours

        $this->thing_report['help'] = 'This reads the Ebay catalog.';
    }

    function run()
    {
         // Make sure the Snippet code is being run.
         $this->makeSnippet();

         // Do something.
    }

    function set()
    {
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);
    }

    function getLink() {
        $this->link = "www.ebay.com";
    }

    function get()
    {
        // $this->from is set by the calling agent.
        // See thing-wordpress.php / thing-keybase / etc
        $this->variables_agent = new Variables($this->thing, "variables " . "ebay" . " " . $this->from);

        // Count calls to Ebay API. Note call limits.
        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable("refreshed_at");

        $this->thing->log( $this->agent_prefix .  'loaded ' . $this->counter . ".", "DEBUG");

        $this->counter = $this->counter + 1;
    }

    function doApi($type = "dictionary")
    {
        // Lots of things we can do we the Api.
        // First thing let us see what the Finding API does.

        // There is also a Catalog API. And many others.
        // https://developer.ebay.com/docs

        $this->thing->log("Called Ebay Finding API.");
        if ($type == null) {$type = "dictionary";}

        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        // Create + seperated keyword string
        $keywords = str_replace(" ", "+", $keywords);
        $keywords = urlencode($keywords);

        // Not needed.
        $options = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept-language: application/json\r\n" .
                    "app_id: " . $this->application_id . "\r\n" .  // check function.stream-context-create on php.net
                    "app_key: " . $this->application_key . "\r\n" . 
                    "" // i.e. An iPad 
            )
        );
        $options = null;

        $context = stream_context_create($options);

        // https://partnernetwork.ebay.com/epn-blog/2010/05/simple-api-searching-example
        // $data_source = "https://svcs.ebay.com/services/search/FindingService/v1/". $keywords;

        $app_id = $this->application_id;
        $format = "JSON";
        $global_id = "EBAY-US";

        $data_source = "http://svcs.ebay.com/services/search/FindingService/v1?OPERATION-NAME=findItemsAdvanced&SERVICE-VERSION=1.0.1&SECURITY-APPNAME=" . $app_id . "&RESPONSE-DATA-FORMAT=" . $format . "&REST-PAYLOAD=true&GLOBAL-ID=" . $global_id ."&paginationInput.entriesPerPage=5&paginationInput.pageNumber=1&keywords=" . $keywords . "&itemFilter(0).name=ListingType&itemFilter(0).value(0)=FixedPrice";

        $data = file_get_contents($data_source, false, $context);

        if ($data == false) {
            $this->response = "Could not ask Ebay.";
            $this->items_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, TRUE);

        // Extract information.
        $ack = $json_data['findItemsAdvancedResponse'][0]['ack']['0'];

        // Ebay asks developers to check the status response.
        if ($ack != "Success") {
            $this->response = "Could not ask Ebay.";
            $this->items_count = 0;
            return true;
            // Invalid query of some sort.
        }

        // Meta
        // See what other variables are helpful and/or useful.
        $time_stamp = $json_data['findItemsAdvancedResponse'][0]['timestamp']['0'];
        $count = $json_data['findItemsAdvancedResponse'][0]['searchResult']['0']['@count'];

        // Array of ebay items.
        // Use the ebay language here for items.
        // Translation to Thing comes later in code.

$items = null;
if (isset( $json_data['findItemsAdvancedResponse'][0]['searchResult']['0']['item'])) {

        $items = $json_data['findItemsAdvancedResponse'][0]['searchResult']['0']['item'];
}
        $this->items = $items;

        $this->items_count = $count;

        return false;
    }

    public function makeSnippet()
    {
$this->thing->log("Called Ebay makeSnippet.");
        if (isset($this->thing_report['snippet'])) {return;}

        $web = "";

//        if (!isset($this->items)) {$web .= "<br>No items found on Ebay.";}

//        if (isset($this->items)) {

            $web_items = "";
if ((!isset($this->items)) or (count($this->items) == 0)) {return;}
            foreach ($this->items as $id=>$item) {
                $parsed_item = $this->parseItem($item);
                $web_items .= "<br>" .  $parsed_item['html_link'] . " " . $parsed_item['price'];
            }
 //       }
$snippet_prefix = '<span class = "' . $this->agent_name . '">';
$snippet_postfix = '</span>';
       $web .= $web_items;
$web = $snippet_prefix . $web . $snippet_postfix;
        $this->thing_report['snippet'] = $web;
    }

    public function makeTXT()
    {
$this->thing->log("Called Ebay makeTxt.");
        if (isset($this->thing_report['web'])) {return;}

        $txt = "EBAY\n";
        $txt .= "Ebay items\n";

//        if (!isset($this->items)) {$web .= "<br>No items found on Ebay.";}

        if ((!isset($this->items)) or (count($this->items) == 0)) { return;}


//        if (isset($this->items)) {
            $txt_items = "";
            foreach ($this->items as $id=>$item) {
                $parsed_item = $this->parseItem($item);
                $txt_items .= "\n" .  $parsed_item['title'] . " " . $parsed_item['price'];
            }
 //       }

       $txt .= $txt_items;
        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;

    }

    public function parseItem($item = null) {

        if ($item == null) {return;}

                $title = $item['title'][0];

                $currency = $item['sellingStatus'][0]['currentPrice'][0]['@currencyId'];
                $currency_prefix = "";
                $currency_postfix = "USD";
                if ($currency == "USD") {$currency_prefix = "$";$currency_postfix = "";}

                $price = $item['sellingStatus'][0]['currentPrice'][0]['__value__'];

                $price_text =  $currency_prefix . $price . $currency_postfix;

                $link = $item["viewItemURL"][0];
                $link_thumbnail =  $item["galleryURL"][0];
                $location = $item["location"][0];
                $country = $item["country"][0];

                $html_link = '<a href="' . $link . '">';
                $html_link .= $title;
                $html_link .= "</a>";

        $parsed_item = array("title"=>$title,"price"=>$price_text, "link"=>$link, "thumbnail" => $link_thumbnail, "location"=>$location, "country"=>$country, "html_link"=>$html_link); 
return $parsed_item;
    }

    public function makeSMS()
    {
        $sms = "EBAY";
        if ((isset($this->search_words)) and ($this->search_words != "")) {
            $sms .= " " . strtoupper($this->search_words);
        }

        $items_count = 0;
        if(isset($this->items_count)) {$items_count = $this->items_count;}

        switch ($items_count) {
            case 0:
                $sms .= " | No items found.";
                break;
            case 1:
                $sms .= " | " .$this->items[0];
                break;
            default:

                foreach($this->items as $item) {
                    $parsed_item = $this->parseItem($item);
                    $sms .= " / " . $parsed_item['title'] . " " . $parsed_item['price'];
                }
        }

        $sms .= " | " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function makeMessage()
    {
        $message = "Ebay";

        $items_count = 0;
        if(isset($this->items_count)) {$items_count = $this->items_count;}

        switch ($items_count) {
            case 0:
                $message .= " did not find any items.";
                break;
            case 1:
                $message .= ' found, "' .$this->items[0] . '"';
                break;
            default:
                foreach($this->items as $item) {
//                    $currency = $definition['sellingStatus'][0]['currentPrice'][0]['@currencyId'];
//                    $currency_prefix = "";
 //                   $currency_postfix = "USD";
 //                   if ($currency == "USD") {$currency_prefix = "$";$currency_postfix = "";}

   //                 $price = $definition['sellingStatus'][0]['currentPrice'][0]['@currencyId'];

                  //  $parsed_item = $this->parseItem($definition);

                  //  $message .= " / " . $parsed_item['title'] . " " . $parsed_item['price'];
                }

        }

        $this->message = $message;
        $this->thing_report['message'] = $message;
    }

    private function thingreportEbay()
    {
//        $this->thing_report['sms'] = $this->sms_message;
//       $this->thing_report['web'] = $this->html_message;
//        $this->thing_report['message'] = $this->message;
    }

    public function readSubject()
    {
$this->thing->log("Ebay readSubject");
        $this->response = null;

        $keywords = $this->keywords;


        $input = $this->input;

        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'ebay') {
                //$this->search_words = null;
                $this->response = "Did not ask Ebay about nothing.";
                return;
            }

        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "ebay is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("ebay is")); 
        } elseif (($pos = strpos(strtolower($input), "ebay")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("ebay")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->doApi("dictionary");
            $this->response = "Asked Ebay about the word " . $this->search_words . ".";
            return false;
        }

        $this->response = "Message not understood";
		return true;
	}

}
