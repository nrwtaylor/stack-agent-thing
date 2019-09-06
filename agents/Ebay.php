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

        $this->agent_prefix = 'Agent "Ebay" ';

        $this->node_list = array("ebay"=>array("ebay"));

        $this->keywords = array('ebay','catalog','catalogue');

//        $this->current_time = $this->thing->json->time();

        $this->application_id = $this->thing->container['api']['ebay']['app ID'];
        $this->application_key = $this->thing->container['api']['ebay']['cert ID']; 

        $this->run_time_max = 360; // 5 hours

	}

    function run()
    {
         // Do something.
    }

    function set()
    {
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);
    }


    function get()
    {
        $this->variables_agent = new Variables($this->thing, "variables " . "ebay" . " " . $this->from);

        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable("refreshed_at");

        $this->thing->log( $this->agent_prefix .  'loaded ' . $this->counter . ".", "DEBUG");

        $this->counter = $this->counter + 1;
    }

    function doApi($type = "dictionary")
    {
        if ($type == null) {$type = "dictionary";}


        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        $keywords = str_replace(" ", "+", $keywords);
        $keywords = urlencode($keywords);

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
            $this->definitions_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, TRUE);

        // Extract information.
        $ack = $json_data['findItemsAdvancedResponse'][0]['ack']['0'];
        $time_stamp = $json_data['findItemsAdvancedResponse'][0]['timestamp']['0'];

        $count = $json_data['findItemsAdvancedResponse'][0]['searchResult']['0']['@count'];

        $items = $json_data['findItemsAdvancedResponse'][0]['searchResult']['0']['item'];

        $this->definitions = $items;

        $this->definitions_count = $count;

        return false;
    }

	public function respondResponse()
    {
		// Thing actions

		$this->thing->flagGreen();

        $choices = false;
		$this->thing_report['choices'] = $choices;

        $this->flag = "green";

        //$this->makeSMS();
        //$this->makeMessage();

        //$this->makeWeb();

        $this->thing_report['email'] = $this->sms_message;
        //$this->thing_report['message'] = $this->sms_message;

        $this->thingreportEbay();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['help'] = 'This reads the Ebay catalog.';

		return;
	}

    public function makeWeb()
    {

        if (isset($this->thing_report['web'])) {return;}
        $web = "<b>EBAY</b>";
        $web .= "<p><b>Ebay definitions</b>";

        if (!isset($this->definitions)) {$web .= "<br>No definitions found on Ebay.";}

        if (isset($this->definitions)) {
            foreach ($this->definitions as $id=>$definition) {

                $title = $definition['title'][0];

                $currency = $definition['sellingStatus'][0]['currentPrice'][0]['@currencyId'];
                $currency_prefix = "";
                $currency_postfix = "USD";
                if ($currency == "USD") {$currency_prefix = "$";$currency_postfix = "";}

                $price = $definition['sellingStatus'][0]['currentPrice'][0]['__value__'];

                $price_text =  $currency_prefix . $price . $currency_postfix;

                $link = $definition["viewItemURL"][0];
                $link_thumbnail =  $definition["galleryURL"][0];
                $location = $definition["location"][0];
                $country = $definition["country"][0];

                $html_link = '<a href="' . $link . '">';
                $html_link .= $title;
                $html_link .= "</a>";
                $web .= "<br>" .  $html_link . " " . $price_text;
            }
        }
        $this->thing_report['web'] = $web;
        $this->web_message = $web;
    }

    public function makeSMS()
    {
        $sms = "EBAY";
        if ((isset($this->search_words)) and ($this->search_words != "")) {
            $sms .= " " . strtoupper($this->search_words);
        }

        $definitions_count = 0;
        if(isset($this->definitions_count)) {$definitions_count = $this->definitions_count;}

        switch ($definitions_count) {
            case 0:
                $sms .= " | No definitions found.";
                break;
            case 1:
                $sms .= " | " .$this->definitions[0];
                break;
            default:

                foreach($this->definitions as $definition) {

                    $currency = $definition['sellingStatus'][0]['currentPrice'][0]['@currencyId'];
                    $currency_prefix = "";
                    $currency_postfix = "USD";
                    if ($currency == "USD") {$currency_prefix = "$";$currency_postfix = "";}

                    $price = $definition['sellingStatus'][0]['currentPrice'][0]['__value__'];

                    $sms .= " / " . $definition['title'][0] . " " . $currency_prefix . $price . $currency_postfix;

                }
        }

        $sms .= " | " . $this->response;

        $this->sms_message = $sms;

    }

    public function makeMessage()
    {
        $message = "Ebay";

        $definitions_count = 0;
        if(isset($this->definitions_count)) {$definitions_count = $this->definitions_count;}

        switch ($definitions_count) {
            case 0:
                $message .= " did not find any definitions.";
                break;
            case 1:
                $message .= ' found, "' .$this->definitions[0] . '"';
                break;
            default:
                foreach($this->definitions as $definition) {
                    $currency = $definition['sellingStatus'][0]['currentPrice'][0]['@currencyId'];
                    $currency_prefix = "";
                    $currency_postfix = "USD";
                    if ($currency == "USD") {$currency_prefix = "$";$currency_postfix = "";}

                    $price = $definition['sellingStatus'][0]['currentPrice'][0]['@currencyId'];

                    $message .= " / " . $definition['title'][0] . " " . $currency_prefix . $price . $currency_postfix;
                }

        }

        $this->message = $message;

    }

    private function thingreportEbay()
    {
        $this->thing_report['sms'] = $this->sms_message;
        //$this->thing_report['web'] = $this->html_message;
        $this->thing_report['message'] = $this->message;
    }

    public function readSubject()
    {
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
