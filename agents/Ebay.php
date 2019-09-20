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
        $this->test = "Development code"; // Always

        $this->node_list = array("ebay" => array("ebay"));
        $this->keywords = array('ebay', 'catalog', 'catalogue');

        $this->environment = "production"; // production

        $word = strtolower($this->word) . "_" . $this->environment;

        $this->thing->log(
            $this->agent_prefix . 'using ebay keys for  ' . $word . ".",
            "DEBUG"
        );


        $this->application_id =
            $this->thing->container['api']['ebay'][$word]['app ID'];
        $this->application_key =
            $this->thing->container['api']['ebay'][$word]['cert ID'];
        $this->devID = $this->thing->container['api']['ebay'][$word]['dev ID'];

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
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    function getTime()
    {
        // devstack
        // Ebay test

        // Are you there ebay

        // Without using this useful tool.
        //use \DTS\eBaySDK\Shopping\Services;
        //use \DTS\eBaySDK\Shopping\Types;

        // Create the service object.
        $service = new Services\ShoppingService();

        // Create the request object.
        $request = new Types\GeteBayTimeRequestType();

        // Send the request to the service operation.
        $response = $service->geteBayTime($request);

        // Output the result of calling the service operation.
        printf(
            "The official eBay time is: %s\n",
            $response->Timestamp->format('H:i (\G\M\T) \o\n l jS Y')
        );
    }

    function getLink()
    {
        $this->link = "www.ebay.com";
    }

    function get()
    {
        // $this->from is set by the calling agent.
        // See thing-wordpress.php / thing-keybase / etc
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "ebay" . " " . $this->from
        );

        // Count calls to Ebay API. Note call limits.
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

    function doItem($text)
    {
        $this->item = $this->eBayGetSingle($text);
    }

    function eBayGetSingle($ItemID)
    {
        $this->thing->log("get single item id " . $ItemID . ".");

        $this->response .= "Requested an eBay item. ";
        $URL = 'http://open.api.ebay.com/shopping';

        //change these two lines
        $compatabilityLevel = 967;
        $appID = $this->application_id;

//https://developer.ebay.com/devzone/shopping/docs/callref/getsingleitem.html#detailControls
        //you can also play with these selectors
        $includeSelector =
            "Details,Description,TextDescription,ShippingCosts,ItemSpecifics,Variations,Compatibility,PrimaryCategoryName";

        // Construct the GetSingleItem REST call
        $apicall =
            "$URL?callname=GetSingleItem&version=$compatabilityLevel" .
            "&appid=$appID&ItemID=$ItemID" .
            "&responseencoding=XML" .
            "&IncludeSelector=$includeSelector";
        $xml = simplexml_load_file($apicall);
        if ($xml) {
            $json = json_encode($xml);
            $array = json_decode($json, true);
            return $array;
        }
        return false;
    }

    function doApi($text = null)
    {
        // Each of these calls has a cost.
        // If we do all three we get the widest net.

        // Collates all the items to $this->items

        $this->ebayApi($text); // no return blue tablecloth with giraffes

        // Could also do.

        //        $this->wideApi($text); // Lots of returns
        //        $this->ngramApi($text);

        $this->thing->log("search for " . $text . ".");
    }

    function wideApi($text = null)
    {
        $keywords = "(" . str_replace(" ", ",", $text) . ")";
        $this->findingApi($keywords);
        $this->thing->log("wide search for " . $keywords . ".");
    }

    function ebayApi($text = null)
    {
        $keywords = $text;
        $this->findingApi($keywords);
        $this->thing->log("did a Finding API search for " . $keywords . ".");
    }

    function ngramApi($text = null)
    {
        $keywords = '@1 ' . $text;
        $this->findingApi($keywords);

        $this->thing->log("ngram search for " . $keywords . ".");
    }

    function findingApi($text = null)
    {

        // , + - all have specific meanings to eBay.
        $this->response = "Searched eBay query " . $text . ". ";
        // Lots of things we can do we the Api.
        // First thing let us see what the Finding API does.

        // There is also a Catalog API. And many others.
        // https://developer.ebay.com/docs

        //  $this->thing->log("Called Ebay Finding API.");
        //        if ($type == null) {$type = "dictionary";}

        $keywords = "";
        if (isset($this->search_words)) {
            $keywords = $this->search_words;
        }
        //echo "looking for " . implode(" ",$this->keywords) . "<br>";
        // Create + seperated keyword string
        //$keywords = $this->thing->subject;

        if ($text != null) {
            $keywords = $text;
        }

        $this->thing->log('Call Ebay Finding API ask about, "' . $keywords . '".');

        $keywords = urlencode($keywords);
        // Not needed.
        $options = array(
            'http' => array(
                'method' => "GET",
                // check function.stream-context-create on php.net
                'header' =>
                    "Accept-language: application/json\r\n" .
                    "app_id: " .
                    $this->application_id .
                    "\r\n" .
                    "app_key: " .
                    $this->application_key .
                    "\r\n" .
                    "" // i.e. An iPad
            )
        );
        $options = null;

        $context = stream_context_create($options);

// https://thisinterestsme.com/file_get_contents-timeout/
$context = stream_context_create(
    array('http'=>
        array(
            'timeout' => 5,  //120 seconds
        )
    )
);

        // https://partnernetwork.ebay.com/epn-blog/2010/05/simple-api-searching-example
        // $data_source = "https://svcs.ebay.com/services/search/FindingService/v1/". $keywords;

        $app_id = $this->application_id;
        $format = "JSON";
        $global_id = "EBAY-US";

        if ($this->environment == "production") {
            $end_point =
                "https://svcs.ebay.com/services/search/FindingService/v1"; // production
        } else {
            $end_point =
                "https://svcs.sandbox.ebay.com/services/search/FindingService/v1"; // sandbox
        }

        $entries = 50;
        $data_source =
            $end_point .
            "?OPERATION-NAME=findItemsAdvanced&SERVICE-VERSION=1.0.1&SECURITY-APPNAME=" .
            $app_id .
            "&RESPONSE-DATA-FORMAT=" .
            $format .
            "&REST-PAYLOAD=true&GLOBAL-ID=" .
            $global_id .
            "&paginationInput.entriesPerPage=" .
            $entries .
            "&paginationInput.pageNumber=1&keywords=" .
            $keywords;

        // From the example.
        // . "&itemFilter(0).name=ListingType&itemFilter(0).value(0)=FixedPrice";

        // devstack timeout call

        // Set a 5 second timeout.
//        $default_socket_timeout = ini_get('default_socket_timeout');
//        ini_set('default_socket_timeout', 5);

        $this->call = $data_source;



        $data = @file_get_contents($data_source, false, $context);

//        ini_set('default_socket_timeout', $default_socket_timeout);

        if ($data == false) {
            $this->thing->log("Finding API call failed.");

            $this->response .= "Could not ask Ebay. ";
            $this->items_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, true);

        // Extract information.
        $ack = $json_data['findItemsAdvancedResponse'][0]['ack']['0'];

        // Ebay asks developers to check the status response.
        if ($ack != "Success") {
            $this->thing->log("Finding API not successful.");

            $this->response .= "Query not successful. ";
            $this->items_count = 0;
            return true;
            // Invalid query of some sort.
        }

        // Meta
        // See what other variables are helpful and/or useful.
        $time_stamp =
            $json_data['findItemsAdvancedResponse'][0]['timestamp']['0'];
        $count =
            $json_data['findItemsAdvancedResponse'][0]['searchResult']['0'][
                '@count'
            ];

        // Array of ebay items.
        // Use the ebay language here for items.
        // Translation to Thing comes later in code.

        $items = null;
        if (
            isset(
                $json_data['findItemsAdvancedResponse'][0]['searchResult']['0'][
                    'item'
                ]
            )
        ) {
            $items =
                $json_data['findItemsAdvancedResponse'][0]['searchResult']['0'][
                    'item'
                ];
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

        $this->items_count = $count;

        $this->thing->log(
            "got " . $this->items_count . " items."
        );

        return false;
    }

    function getItems($text = null)
    {
        $this->items = array();
    }

    function getItem()
    {
        if (!isset($this->items)) {
            $this->getItems();
        }
        $this->item = $this - items[0];
        $this->thing->log("set item to first of items.");
    }

    function getUsertoken()
    {
        //use \DTS\eBaySDK\OAuth\Services;
        //use \DTS\eBaySDK\OAuth\Types;

        /**
         * Create the service object.
         */
        $service = new Services\OAuthService([
            'credentials' => $config['sandbox']['credentials'],
            'ruName' => $config['sandbox']['ruName'],
            'sandbox' => true
        ]);
        /**
         * Create the request object.
         */
        $request = new Types\GetUserTokenRestRequest();
        $request->code = '<AUTHORIZATION CODE>';
        /**
         * Send the request.
         */
        $response = $service->getUserToken($request);
        /**
         * Output the result of calling the service operation.
         */
        printf("\nStatus Code: %s\n\n", $response->getStatusCode());
        if ($response->getStatusCode() !== 200) {
            printf(
                "%s: %s\n\n",
                $response->error,
                $response->error_description
            );
        } else {
            printf(
                "%s\n%s\n%s\n%s\n\n",
                $response->access_token,
                $response->token_type,
                $response->expires_in,
                $response->refresh_token
            );
        }
        $this->thing->log("got user token.");
    }

    function getToken()
    {
        //use \DTS\eBaySDK\OAuth\Services;
        //use \DTS\eBaySDK\OAuth\Types;

        /**
         * Create the service object.
         */
        $service = new Services\OAuthService([
            'credentials' => $config['sandbox']['credentials'],
            'ruName' => $config['sandbox']['ruName'],
            'sandbox' => true
        ]);
        /**
         * Send the request.
         */
        $response = $service->getAppToken();
        /**
         * Output the result of calling the service operation.
         */
        printf("\nStatus Code: %s\n\n", $response->getStatusCode());
        if ($response->getStatusCode() !== 200) {
            printf(
                "%s: %s\n\n",
                $response->error,
                $response->error_description
            );
        } else {
            printf(
                "%s\n%s\n%s\n%s\n\n",
                $response->access_token,
                $response->token_type,
                $response->expires_in,
                $response->refresh_token
            );
        }
    }

    function doCatalog()
    {
        $service = new Services\CatalogService([
            'authorization' => $config['production']['oauthUserToken']
            //,'httpOptions' => ['debug' => true]
        ]);

        /**
         * Create the request object.
         */
        $request = new Types\SearchRestRequest();
        $request->q = 'iphone';
        $request->limit = '3';
        /**
         * Send the request.
         */
        $response = $service->search($request);
        /**
         * Output the result of calling the service operation.
         */
        printf("\nStatus Code: %s\n\n", $response->getStatusCode());
        if (isset($response->errors)) {
            foreach ($response->errors as $error) {
                printf(
                    "%s: %s\n%s\n\n",
                    $error->errorId,
                    $error->message,
                    $error->longMessage
                );
            }
        }
        if ($response->getStatusCode() === 200) {
            foreach ($response->productSummaries as $productSummary) {
                printf(
                    "%s\n%s\n",
                    $productSummary->title,
                    $productSummary->brand
                );
            }
        }
    }

    public function makeSnippet()
    {
        if (isset($this->thing_report['snippet'])) {
            return;
        }

        $web = "";

        $web_items = "";
        if (!isset($this->items) or count($this->items) == 0) {
            return;
        }
        foreach ($this->items as $id => $item) {
            $parsed_item = $this->parseItem($item);
            $web_items .=
                "<br>" .
                $parsed_item['html_link'] .
                " " .
                $parsed_item['price'];
        }

        $snippet_prefix = '<span class = "' . $this->agent_name . '">';
        $snippet_postfix = '</span>';
        $web .= $web_items;
        $web = $snippet_prefix . $web . $snippet_postfix;
        $this->thing_report['snippet'] = $web;
        $this->thing->log("made snippet.");
    }

    public function makeTXT()
    {
        $this->thing->log("Called Ebay makeTxt.");
        if (isset($this->thing_report['web'])) {
            return;
        }

        $txt = "EBAY\n";
        $txt .= "Ebay items\n";

        if (!isset($this->items) or count($this->items) == 0) {
            return;
        }

        $txt_items = "";
        foreach ($this->items as $id => $item) {
            $parsed_item = $this->parseItem($item);
            $txt_items .=
                "\n" . $parsed_item['title'] . " " . $parsed_item['price'];
        }

        $txt .= $txt_items;
        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
        $this->thing->log("made text.");
    }

    public function parseItem($item = null)
    {
        if ($item == null) {
            return;
        }

        $title = $item['title'][0];

        $currency = $item['sellingStatus'][0]['currentPrice'][0]['@currencyId'];
        $currency_prefix = "";
        $currency_postfix = "USD";
        if ($currency == "USD") {
            setlocale(LC_MONETARY, 'en_US.UTF-8');
            $currency_prefix = "$";
            $currency_postfix = "";
        }

        $price = $item['sellingStatus'][0]['currentPrice'][0]['__value__'];

        $price_text = $currency_prefix . $price . $currency_postfix;

        // https://www.php.net/manual/en/function.money-format.php
        $price_text = money_format('%.2n', $price);

        $link = $item["viewItemURL"][0];

        $link_thumbnail = null;
        if (isset($item['galleryURL'][0])) {
            $link_thumbnail = $item["galleryURL"][0];
        }

        $location = $item["location"][0];
        $country = $item["country"][0];

        $html_link = '<a href="' . $link . '">';
        $html_link .= $title;
        $html_link .= "</a>";

        $item_id = $item["itemId"][0];
//var_dump($item["primaryCategory"][0]['categoryName'][0]);
//exit();
$category_name = "X";
if (isset($item["primaryCategory"][0]["categoryName"][0])) {$category_name = $item["primaryCategory"][0]["categoryName"][0];}

        $parsed_item = array(
            "id" => $item_id,
            "category_name"=> $category_name,
            "title" => $title,
            "price" => $price_text,
            "link" => $link,
            "thumbnail" => $link_thumbnail,
            "location" => $location,
            "country" => $country,
            "html_link" => $html_link
        );
        return $parsed_item;
    }

    public function makeSMS()
    {
        $sms = "EBAY";
        if (isset($this->search_words) and $this->search_words != "") {
            $sms .= " " . strtoupper($this->search_words);
        }

        $items_count = 0;
        if (isset($this->items_count)) {
            $items_count = $this->items_count;
        }

        switch ($items_count) {
            case 0:
                $sms .= " | No items found.";
                break;
            case 1:
                $item = $this->items[0];
                $parsed_item = $this->parseItem($item);
                $sms .=
                    "" . $parsed_item['title'] . " " . $parsed_item['price'];
                break;
            default:
                foreach ($this->items as $item) {
                    $parsed_item = $this->parseItem($item);
                    $sms .=
                        " / " .
                        $parsed_item['title'] .
                        " " .
                        $parsed_item['price'];
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
        if (isset($this->items_count)) {
            $items_count = $this->items_count;
        }

        switch ($items_count) {
            case 0:
                $message .= " did not find any items.";
                break;

            case 1:
                $message .=
                    ' found one item, "' .
                    $this->parseItem($this->items[0])['title'] .
                    '"';
                //$message .= ' found one item.';

                break;
            default:
                foreach ($this->items as $item) {
                    $parsed_item = $this->parseItem($item);
                    $message .=
                        " / " .
                        $parsed_item['title'] .
                        " " .
                        $parsed_item['price'];
                }
        }

        $this->message = $message;
        $this->thing_report['message'] = $message;
    }

    function isItem($text)
    {
        // devstack
        // This recognizes that the item number is a number.
        if (is_numeric($text)) {
            return true;
        }
        return false;

        if ($this->getItem($text) != null) {
            return true;
        }
        return false;
    }

    function extractItem($text)
    {
        // devstack not tested

        if (preg_match("/(?<=QQitemZ).*?\z/", $text, $match)) {
            return $match;
        }
        //echo "No match found";
        else {
            return null;
        }
    }

    public function readSubject()
    {
        $this->thing->log('Ebay read input, "' . $this->input . '".');
        $this->response = null;

        $keywords = $this->keywords;

        $input = $this->input;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'ebay') {
                $this->response .= "Did not ask Ebay about nothing. ";
                return;
            }
        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "ebay is")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("ebay is"));
        } elseif (($pos = strpos(strtolower($input), "ebay")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("ebay"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($this->isItem($filtered_input)) {
            // Likely given an item code.
            $this->doItem($filtered_input);
            $this->response .=
                "Asked Ebay about an item #" . $filtered_input . ". ";
            $this->thing->log("asked about " . $filtered_input . ".");

            return;
        }

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->doApi($this->search_words);

            $this->response .=
                "Asked Ebay about the word " . $this->search_words . ". ";
            $this->thing->log("asked about " . $this->search_words . ".");

            return false;
        }

        $this->thing->log("did not understand subject.");

        $this->response .= "Message not understood. ";
        return true;
    }
}
