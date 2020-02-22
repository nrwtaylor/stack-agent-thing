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

    function is_positive_integer($str)
    {
        return is_numeric($str) && $str > 0 && $str == round($str);
    }

    function init()
    {
        $this->email = $this->thing->container['stack']['email'];
        $this->stack_email = $this->email;

        $this->response = "Init eBay. ";
        $this->flag = "green";
        $this->ebay_daily_call_count = 0;
        $this->test = "Development code"; // Always

        $this->node_list = array("ebay" => array("ebay"));
        $this->keywords = array('ebay', 'catalog', 'catalogue');

        $this->environment = "production"; // production

        $word = strtolower($this->word) . "_" . $this->environment;
        $this->thing->log(
            $this->agent_prefix . 'using ebay keys for  ' . $word . ".",
            "DEBUG"
        );

        if (!isset($this->thing->container['api']['ebay'])) {
            $this->response .= "Settings not available. ";
            return true;
        }

        $this->credential_set =
            $this->thing->container['api']['ebay']['credential_set'];

$word = $this->credential_set;

        $this->application_id =
            $this->thing->container['api']['ebay'][$word]['app ID'];
        $this->application_key =
            $this->thing->container['api']['ebay'][$word]['cert ID'];
        $this->devID = $this->thing->container['api']['ebay'][$word]['dev ID'];

        $this->desired_state = $this->thing->container['api']['ebay']['state'];

        $this->auth_token = base64_encode(
            $this->application_id . ":" . $this->application_key
        );

        $this->run_time_max = 360; // 5 hours

        $this->thing_report['help'] = 'This reads the Ebay catalog.';

        //$this->eBayGetItemSnapshotFeed(4);
    }

    public function errorEbay()
    {
        $this->sms_message = 'EBAY | There is a problem with the eBay API.';
        $this->message = $this->word . ' turned off the eBay API. ' . $this->response;

        $message = 'The stack saw errors back from the eBay API. The eBay API is currently '. strtoupper($this->state) .".";

        $thing = new Thing(null);

        $to = $this->stack_email;
        $thing->Create($to, "human", 's/ ebay error message to ' . $this->from);
        $thing->flagGreen();

        $thing_report['thing'] = $thing;
        $thing_report['message'] = $message;
        $thing_report['sms'] = $message;
        $thing_report['email'] = $message;



        $message_thing = new Message($thing, $thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->response .= $this->thing_report['info'] . " ";

        return $this->message;
    }

    function run()
    {
        // Make sure the Snippet code is being run.
        $this->makeSnippet();

        // Do something.
    }

    function set()
    {
        if (!isset($this->state) or $this->state == false) {
            $this->state = "off";
        }

        $this->variables_agent->setVariable("state", $this->state);

        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable(
            "daily_call_count",
            $this->ebay_daily_call_count
        );
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );

        //     if ($this->last_state == false) {
        //            $this->readSubject();

$this->runtime = $this->thing->elapsed_runtime() - $this->start_time;

        $this->thing->json->writeVariable(
            array("ebay", "runtime"),
            $this->runtime
        );


        $this->thing->json->writeVariable(array("ebay", "state"), $this->state);
        $this->thing->json->writeVariable(
            array("ebay", "refreshed_at"),
            $this->current_time
        );

        $this->thing->log($this->agent_prefix . ' completed read.', "OPTIMIZE");
        //    }
    }

    function getTime()
    {
        if ($this->state == "off") {
            return true;
        }
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
        //$from = $this->from;
        // Because this is a per key allowance.
        $from = "stack";

        // $this->from is set by the calling agent.
        // See thing-wordpress.php / thing-keybase / etc
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "ebay" . " " . $from
        );

        $this->last_state = $this->variables_agent->getVariable("state");

        // Count calls to Ebay API. Note call limits.
        $this->counter = $this->variables_agent->getVariable("counter");

        $this->ebay_daily_call_count = $this->variables_agent->getVariable(
            "daily_call_count"
        );

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

    function logEbay($text, $type = "ERROR")
    {
        if ($text == null) {
            $text = "MErp";
        }

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

$calling_function = debug_backtrace()[1]['function'];

        $thing = new Thing(null);
        $thing->Create(
            "meep",
            "ebay",
            "g/ ebay " . $type . " " . $calling_function . " - " . $request . " - " . $log_text
        );

        //$this->state = $this->last_state;

        $this->thing->db->setFrom($this->from);

        $this->thing->json->setField("message1");
        $this->thing->json->writeVariable(array("ebay"), $text);

        $this->flag = "red";
        $this->response .= "Logging " . $request . " " . $log_text . ". ";


if ($type == "WARNING") {return true;}

        // Okay at this point we have one error...
        // Have we had other errors recently?

        $findagent_thing = new Findagent($this->thing, 'ebay');

        $count = count($findagent_thing->thing_report['things']);
        $this->thing->log(
            'found ' .
                count($findagent_thing->thing_report['things']) .
                " place Things."
        );

        if ($findagent_thing->thing_report['things'] == true) {
        }

        if (!$this->is_positive_integer($count)) {
            // Do nothing
        } else {
            $now = strtotime($this->thing->time());

            $count = 0;
            foreach (
                $findagent_thing->thing_report['things']
                as $thing_object
            ) {
                $time_string = $thing_object['created_at'];
                $created_at = strtotime($time_string);

                $age = $now - $created_at;

                if ($age < 60 * 5) {
                    $this->response .= "Saw error  " . $age . "s ago. ";
                    $count += 1;
                }

            }
        }

        if ($count > 2) {
            $this->response .= "Turned eBay off. ";
            $this->state = "off";

            // Send a message. Handle the error.
            $this->errorEbay();
        }

        // Log to the created error Thing.
        $thing->json->writeVariable(array("ebay", "state"), $this->state);
        $thing->json->writeVariable(
            array("ebay", "refreshed_at"),
            $this->current_time
        );
    }

    function eBayGetSingle($ItemID)
    {
        if ($this->state == "off") {
            return true;
        }
        $this->request = $ItemID;
        $this->thing->log("get single item id " . $ItemID . ".");

        $this->response .= "Requested a single eBay item " . $ItemID . ". ";
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
        $this->ebay_daily_call_count += 1;

        if ($xml) {
            $json = json_encode($xml);
            $array = json_decode($json, true);
            $this->flag = "green";
            if ($array["Ack"] == "Failure") {
                $this->logEbay($array);
            }

            return $array;
        }
        $this->flag = "green";
        return false;
    }

    function eBayGetItemSnapshotFeed($ItemID)
    {
        if ($this->state == "off") {
            return true;
        }

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

        $this->request = $ItemID;
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

        // Construct the GetSingleItem REST call
        //        $apicall =
        //            "$URL?callname=GetSingleItem&version=$compatabilityLevel" .
        //            "&appid=$appID&ItemID=$ItemID" .
        //            "&responseencoding=XML" .
        //            "&IncludeSelector=$includeSelector";

        // GET https://api.ebay.com/buy/browse/v1/item/v1|201533667898|0
        $apicall =
            'https://api.ebay.com/buy/feed/v1_beta/item?feed_scope=NEWLY_LISTED&category_id=15032&date=20170924';

        $xml = simplexml_load_file($apicall);

        $this->ebay_daily_call_count += 1;

        if ($xml) {
            $json = json_encode($xml);
            $array = json_decode($json, true);
            if ($array["Ack"] == "Failure") {
                $this->logEbay($array);
            }

            return $array;
        }
        $this->flag = "green";
        return false;
    }

    // devstack
    function eBayGetCategory($category_id)
    {
        if ($this->state == "off") {
            return true;
        }
        $this->request = $category_id;
        $this->thing->log(
            "get multiple items by cateogory " . $category_id . "."
        );

        $this->response .= "Requested multiple items. ";
        $URL = 'http://open.api.ebay.com/shopping';
        //change these two lines
        $compatabilityLevel = 967;
        $appID = $this->application_id;
        //you can also play with these selectors
        $includeSelector =
            "Details,Description,TextDescription,ShippingCosts,ItemSpecifics,Variations,Compatibility,PrimaryCategoryName";

        // Construct the GetSingleItem REST call
        $apicall =
            "https://svcs.ebay.com/services/search/FindingService/v1?" .
            "OPERATION-NAME=findItemsAdvanced&" .
            "SERVICE-VERSION=1.0.0&" .
            "SECURITY-APPNAME=$appID&" .
            "RESPONSE-DATA-FORMAT=XML&" .
            "REST-PAYLOAD&" .
            "outputSelector=AspectHistogram&" .
            "paginationInput.entriesPerPage=10&" .
            "categoryId=" .
            $category_id;

        $xml = @simplexml_load_file($apicall);

        $this->ebay_daily_call_count += 1;

        if ($xml) {
            $json = json_encode($xml);
            $array = json_decode($json, true);
            if (isset($array["Ack"]) and $array["Ack"] == "Failure") {
                $this->logEbay($array);
            }
            if (isset($array["ack"]) and $array["ack"] == "Failure") {
                $this->logEbay($array);
            }
            return $array;
        }
        $this->flag = "green";
        return false;
    }

    /*
http://open.api.ebay.com/shopping?
   callname=GetMultipleItems&
   responseencoding=XML&
   appid=YourAppIDHere&
   siteid=0&
   version=967&
   ItemID=190000456297,280000052600,9600579283
*/

    // devstack
    function eBayGetMultiple($items = array())
    {
        if ($this->state == "off") {
            return true;
        }

        if ($items == array()) {
            return;
        }
        $items_string = implode(",", $items);
        //var_dump($items_string);
        $this->request = $items_string;
        //exit();

        $includeSelector =
            "Details,Description,TextDescription,ShippingCosts,ItemSpecifics,Variations,Compatibility,PrimaryCategoryName";

        // Construct the GetSingleItem REST call
        //        $apicall =
        //            "$URL?callname=GetSingleItem&version=$compatabilityLevel" .
        //            "&appid=$appID&ItemID=$ItemID" .
        //            "&responseencoding=XML" .
        //            "&IncludeSelector=$includeSelector";

        $this->thing->log("get multiple items by item id.");

        $this->response .= "Requested multiple items. ";
        $URL = 'http://open.api.ebay.com/shopping';
        //change these two lines
        $compatabilityLevel = 967;
        $appID = $this->application_id;
        //you can also play with these selectors
        $includeSelector =
            "Details,Description,TextDescription,ShippingCosts,ItemSpecifics,Variations,Compatibility,PrimaryCategoryName";

        // Construct the Multiple Item REST call
        $apicall =
            'http://open.api.ebay.com/shopping?' .
            'callname=GetMultipleItems&' .
            'responseencoding=XML&' .
            'appid=' .
            $appID .
            '&' .
            'siteid=0&' .
            'version=967&' .
            'ItemID=' .
            $items_string .
            '';

        $xml = @simplexml_load_file($apicall);

        $this->ebay_daily_call_count += 1;

        //var_dump($xml);
        if ($xml) {
            $json = json_encode($xml);
            $array = json_decode($json, true);

            if ($array["Ack"] == "Failure") {
                $this->logEbay($array);
            }

            return $array;
        }
        $this->flag = "green";
        return false;
    }

    // devstack
    function eBayFastKeywords($keywords, $n = 10)
    {
        if ($this->state == "off") {
            return true;
        }

        if ($keywords == null) {return true;}
        if ($keywords == "") {return true;}
        if ($keywords == " ") {return true;}
        if ($keywords === false) {return true;}
        if ($keywords === true) {return true;}


        $this->request = $keywords;
        $this->thing->log("fastest ebay search for " . $keywords . ".");

        $this->response .= "Requested multiple items for " . $keywords . ". ";
        $URL = 'http://open.api.ebay.com/shopping';
        //change these two lines
        $compatabilityLevel = 967;
        $appID = $this->application_id;
        //you can also play with these selectors
        //        $includeSelector =
        //            "Details,Description,TextDescription,ShippingCosts,ItemSpecifics,Variations,Compatibility,PrimaryCategoryName,PictureURL";

        $includeSelector =
            "Details,Description,TextDescription,ShippingCosts,ItemSpecifics,Variations,Compatibility,PrimaryCategoryName,PictureURL";

        //        $includeSelector =
        //            "Description, TextDescription";

        // Construct the GetSingleItem REST call

        $apicall =
            "https://svcs.ebay.com/services/search/FindingService/v1?" .
            "OPERATION-NAME=findItemsByKeywords&" .
            "SERVICE-VERSION=1.0.0&" .
            "SECURITY-APPNAME=$appID&" .
            "RESPONSE-DATA-FORMAT=XML&" .
            "REST-PAYLOAD&" .
            "keywords=" .
            urlencode($keywords) .
            "&" .
            "outputSelector(0)=location&" .
            "outputSelector(1)=condition&" .
            "itemFilter(0).name=HideDuplicateItems&" .
            "itemFilter(0).value=true&" .
            "paginationInput.entriesPerPage=" .
            $n;

        $xml = @simplexml_load_file($apicall);

        $this->ebay_daily_call_count += 1;

        if ($xml) {
            $json = json_encode($xml);
            $array = json_decode($json, true);

            if (isset($array["ack"]) and $array["ack"] == "Failure") {
                $this->logEbay($array);
            }
            if (isset($array["Ack"]) and $array["Ack"] == "Failure") {
                $this->logEbay($array);
            }

            return $array;
        }
        //$this->state = "off";
        $this->flag = "green";
        return false;
    }

    // devstack
    function eBayGetKeywords($keywords)
    {
        if ($this->state == "off") {
            return true;
        }
        $this->request = $keywords;
        $this->thing->log("get multiple items by keyword " . $keywords . ".");

        $this->response .= "Requested multiple items. ";
        $URL = 'http://open.api.ebay.com/shopping';
        //change these two lines
        $compatabilityLevel = 967;
        $appID = $this->application_id;
        //you can also play with these selectors
        $includeSelector =
            "Details,Description,TextDescription,ShippingCosts,ItemSpecifics,Variations,Compatibility,PrimaryCategoryName";

        // Construct the GetSingleItem REST call

        $apicall =
            "https://svcs.ebay.com/services/search/FindingService/v1?" .
            "OPERATION-NAME=findItemsByKeywords&" .
            "SERVICE-VERSION=1.0.0&" .
            "SECURITY-APPNAME=$appID&" .
            "RESPONSE-DATA-FORMAT=XML&" .
            "REST-PAYLOAD&" .
            "keywords=" .
            urlencode($keywords) .
            "&" .
            "itemFilter(0).name=HideDuplicateItems&" .
            "itemFilter(0).value=true&" .
            "paginationInput.entriesPerPage=10";

        $xml = @simplexml_load_file($apicall);

        $this->ebay_daily_call_count += 1;

        if ($xml) {
            $json = json_encode($xml);
            $array = json_decode($json, true);

            //if ($array["Ack"] == "Failure") {$this->logEbay($array);}
            if (isset($array["ack"]) and $array["ack"] == "Failure") {
                $this->logEbay($array);
            }
            if (isset($array["Ack"]) and $array["Ack"] == "Failure") {
                $this->logEbay($array);
            }

            return $array;
        }
        $this->flag = "green";
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
        if ($this->state == "off") {
            return true;
        }
        $keywords = "(" . str_replace(" ", ",", $text) . ")";
        $this->findingApi($keywords);
        $this->thing->log("wide search for " . $keywords . ".");
    }

    function ebayApi($text = null)
    {
        if ($this->state == "off") {
            return true;
        }
        $keywords = $text;
        $this->findingApi($keywords);
        $this->thing->log("did a Finding API search for " . $keywords . ".");
    }

    function ngramApi($text = null)
    {
        if ($this->state == "off") {
            return true;
        }
        $keywords = '@1 ' . $text;
        $this->findingApi($keywords);

        $this->thing->log("ngram search for " . $keywords . ".");
    }

    function findingApi($text = null)
    {
        if ($this->state == "off") {
            return true;
        }
        if (!isset($this->application_key)) {
            return true;
        }
        if (!isset($this->devID)) {
            return true;
        }

        // , + - all have specific meanings to eBay.
        $this->response .= "Searched eBay query " . $text . ". ";
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

        $this->thing->log(
            'Call Ebay Finding API ask about, "' . $keywords . '".'
        );

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
        $context = stream_context_create(array(
            'http' => array(
                'timeout' => 5 //120 seconds
            )
        ));

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
            "itemFilter(0).name=HideDuplicateItems&" .
            "itemFilter(0).value=true&" .
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

        $this->ebay_daily_call_count += 1;

        //        ini_set('default_socket_timeout', $default_socket_timeout);

        // Extract information
        $ack = "";
        if (isset($json_data['findItemsAdvancedResponse'][0]['ack']['0'])) {
            $ack = $json_data['findItemsAdvancedResponse'][0]['ack']['0'];
        }
        if ($ack == "Failure") {
            $this->logEbay($data);
        }
        if ($data == false) {
            $this->logEbay("Data is false.", "WARNING");
        }

        if ($data == false) {
            $this->thing->log("Finding API call failed.");

            $this->response .= "Could not ask Ebay. ";
            //   $this->state = "off";
            $this->items_count = 0;
            $this->flag = "red";
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, true);

        // Extract information.
        $ack = $json_data['findItemsAdvancedResponse'][0]['ack']['0'];

        //var_dump($ack);
        //if ($ack == "Failure") {$this->logEbay($array);}

        // Ebay asks developers to check the status response.
        if ($ack != "Success") {
            $this->flag = "red";
            $this->thing->log("Finding API not successful.");

            $this->response .= "Query not successful. ";
            $this->items_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $this->flag = "green";
        //if ($ack == "Failure") {$this->logEbay($array);}

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

        $this->thing->log("got " . $this->items_count . " items.");

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
        if ($this->state == "off") {
            return true;
        }
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

    public function parseItem($ebay_item = null)
    {
        //if (isset($ebay_item['Description'])) {var_dump($ebay_item);exit();}
        if ($ebay_item == null) {
            return true;
        }

        if (is_string($ebay_item)) {
            return true;
        }

        if (isset($ebay_item['Item'])) {
            $ebay_item = $ebay_item['Item'];
        }

        $source = "ebay";

        $title = "X";

        if (isset($ebay_item['title'])) {
            $title = $ebay_item['title'];
            if (is_array($ebay_item['title'])) {
                $title = $ebay_item['title'][0];
            }
        }

        $currency = "X";
        $currency_prefix = "?";
        $currency_postfix = "?";

        if (
            isset(
                $ebay_item['sellingStatus'][0]['currentPrice'][0]['@currencyId']
            )
        ) {
            $currency =
                $ebay_item['sellingStatus'][0]['currentPrice'][0][
                    '@currencyId'
                ];
            $currency_prefix = "";
            $currency_postfix = "USD";
            if ($currency == "USD") {
                setlocale(LC_MONETARY, 'en_US.UTF-8');
                $currency_prefix = "$";
                $currency_postfix = "";
            }
        }

        if (isset($ebay_item['sellingStatus']['currentPrice'])) {
            $currency = "USD";
            $currency_prefix = "";
            $currency_postfix = "USD";
            if ($currency == "USD") {
                setlocale(LC_MONETARY, 'en_US.UTF-8');
                $currency_prefix = "$";
                $currency_postfix = "";
            }
        }

        $price = "X";
        $price_text = "X";
        if (
            isset(
                $ebay_item['sellingStatus'][0]['currentPrice'][0]['__value__']
            )
        ) {
            $price =
                $ebay_item['sellingStatus'][0]['currentPrice'][0]['__value__'];

            $price_text = $currency_prefix . $price . $currency_postfix;

            // https://www.php.net/manual/en/function.money-format.php
            $price_text = money_format('%.2n', $price);
        }

        if (isset($ebay_item['sellingStatus']['currentPrice'])) {
            $price = $ebay_item['sellingStatus']['currentPrice'];

            $price_text = $currency_prefix . $price . $currency_postfix;

            // https://www.php.net/manual/en/function.money-format.php
            $price_text = money_format('%.2n', $price);
        }

        //if ($price == "X") {var_dump($ebay_item);exit();}

        $picture_urls = "X";
        if (isset($ebay_item["Item"]["PictureURL"])) {
            $picture_urls = $ebay_item["Item"]["PictureURL"];
        }

        if (isset($ebay_item["PictureURL"])) {
            $picture_urls = $ebay_item["PictureURL"];
        }

        $description = "X";
        if (isset($ebay_item["Description"])) {
            $description = $ebay_item["Description"];
        }

        $condition_description = "X";
        if (isset($ebay_item["ConditionDescription"])) {
            $condition_description = $ebay_item["ConditionDescription"];
        }

        $link = null;
        if (isset($ebay_item["viewItemURL"])) {
            $link = $ebay_item["viewItemURL"];
            if (is_array($ebay_item["viewItemURL"])) {
                $link = $ebay_item["viewItemURL"][0];
            }
        }

        if (isset($ebay_item["ViewItemURLForNaturalSearch"])) {
            $link = $ebay_item["ViewItemURLForNaturalSearch"];
            //echo $link;
        }

        //$link = "X";
        if (isset($ebay_item['link'])) {
            $link = $ebay_item['link'];
        }

        //if (!isset($link)) {var_dump($ebay_item); exit();}

        //var_dump($ebay_item);
        //if ($picture_urls == "X") {var_dump($ebay_item);exit();}
        $link_thumbnail = "X";
        //         if (isset($ebay_item["galleryURL"])) {$link_thumbnail = $ebay_item["galleryURL"];}

        if (isset($ebay_item["galleryURL"])) {
            $link_thumbnail = $ebay_item["galleryURL"];
            if (is_array($ebay_item['galleryURL'])) {
                $link_thumbnail = $ebay_item["galleryURL"][0];
            }
        }

        if (isset($ebay_item["GalleryURL"])) {
            $link_thumbnail = $ebay_item["GalleryURL"];
            if (is_array($ebay_item['GalleryURL'])) {
                $link_thumbnail = $ebay_item["GalleryURL"][0];
            }
        }
        //if ($link_thumbnail == "X") {
        //var_dump($ebay_item);
        //}
        //$link_thumbnail = "X";
        if (isset($ebay_item['thumbnail'])) {
            $link_thumbnail = $ebay_item['thumbnail'];
        }

        if (!isset($link_thumbnail)) {
            throw "No thumbnail";
            return;
            //var_dump($ebay_item); exit();
        }

        $location = "X";
        if (isset($ebay_item["location"])) {
            $location = $ebay_item["location"];
            if (is_array($ebay_item["location"])) {
                $location = $ebay_item["location"][0];
            }
        }

        $country = "X";
        if (isset($ebay_item["country"])) {
            $country = $ebay_item["country"][0];
            if (is_array($ebay_item["country"])) {
                $country = $ebay_item["country"][0];
            }
        }

        //if(!isset($link)) {var_dump($ebay_item);echo "<br><br>";}

        if (!isset($link)) {
            if (isset($bay_item["viewItemURL"])) {
                $link = $ebay_item["viewItemURL"];
            }
            if (isset($bay_item["link"])) {
                $link = $ebay_item["link"];
            }
        }

        $html_link = "";
        if (isset($link)) {
            $html_link = '<a href="' . $link . '">';
            $html_link .= $title;
            $html_link .= "</a>";
        }

        $item_id = "X";
        if (isset($ebay_item['itemId'])) {
            $item_id = $ebay_item["itemId"];
            if (is_array($ebay_item["itemId"])) {
                $item_id = $ebay_item["itemId"][0];
            }
        }
        if (isset($ebay_item['ItemID'])) {
            $item_id = $ebay_item["ItemID"];
            if (is_array($ebay_item["ItemID"])) {
                $item_id = $ebay_item["ItemID"][0];
            }
        }

        //if(!isset($item_id)) {var_dump($ebay_item["itemId"][0]);exit();}

        if (isset($ebay_item["id"])) {
            $item_id = $ebay_item["id"];
        }
        //if (!isset($link)) {var_dump($ebay_item); exit();}

        //var_dump($item["primaryCategory"][0]['categoryName'][0]);
        $category_name = "X";
        if (isset($ebay_item["primaryCategory"][0]["categoryName"][0])) {
            $category_name = $ebay_item["primaryCategory"][0]["categoryName"];

            if (is_array($category_name)) {
                $category_name =
                    $ebay_item["primaryCategory"][0]["categoryName"][0];
            }
        }
        $item = array(
            "source" => $source,
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
            "picture_urls" => $picture_urls,
            "ebay" => $ebay_item
        );
        return $item;
    }

    public function makeSMS()
    {
        $sms = "EBAY";
        $sms .= " | " . $this->state . "";
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
        $sms .= " daily call count " . $this->ebay_daily_call_count;
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
        //$this->response .= null;

        if (strtolower($this->input) == "ebay on") {
            $this->state = "on";
            return;
        }
        if (strtolower($this->input) == "ebay off") {
            $this->state = "off";
            return;
        }

        $this->state = $this->last_state;

        if ($this->last_state == "off") {
            $this->response .= "eBay is in an OFF condition. ";
            return;
        }

        //$this->state = $this->last_state;

        if (strtolower($this->input) == "ebay") {
            $this->response .= "Checked eBay state. ";
            return;
        }

        $keywords = $this->keywords;

        $input = $this->input;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword

        if ($this->agent_input == "ebay") {
            $this->response .= "Set up a connector to the Ebay API(s). ";
            return;
        }

        if (count($pieces) == 1) {
            if ($input == 'ebay') {
                $this->response .= "Did not ask Ebay about nothing. ";
                return;
            }
        }

        // Don't pull anything. Just set up the connector.
        //return;

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
