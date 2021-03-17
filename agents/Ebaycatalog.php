<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Ebaycatalog extends Agent
{
    // This gets items from the Ebay Finding API.

    public $var = 'hello';

    function init()
    {
        $this->test = "Development code"; // Always

        $this->node_list = ["ebay catalog" => ["ebay catalog"]];

        $this->keywords = ['ebay', 'catalog', 'catalogue'];

        $word = strtolower($this->word) . "_production";

        //        $this->appID = $this->thing->container['api']['ebay'][$word]['app ID'];

        $this->appID = $this->settingsAgent(['api', 'ebay', $word, 'app ID']);
        $this->certID = $this->settingsAgent(['api', 'ebay', $word, 'cert ID']);
        $this->devID = $this->settingsAgent(['api', 'ebay', $word, 'dev ID']);

        //        $this->certID =
        //            $this->thing->container['api']['ebay'][$word]['cert ID'];
        //        $this->devID = $this->thing->container['api']['ebay'][$word]['dev ID'];

        $this->clientID = $this->appID;

        //       $this->authToken ="";
        //        $this->refreshToken ="";
        //        $this->ruName= "";

        $this->serverUrl = 'https://api.ebay.com/ws/api.dll'; // server URL different for prod and sandbox

        //        $this->code_oauth =
        //            $this->thing->container['api']['ebay'][$word]['production'][
        //                'auth code'
        //            ];
        $this->code_oauth = $this->settingsAgent([
            'api',
            'ebay',
            $word,
            'production',
            'auth code',
        ]);

        $this->authToken = $this->code_oauth;
        $this->ruName = $this->settingsAgent([
            'api',
            'ebay',
            $word,
            'production',
            'ruName',
        ]);

        //        $this->ruName =
        //            $this->thing->container['api']['ebay'][$word]['production'][
        //                'ruName'
        //            ];

        //$this->paypalEmailAddress = $this->thing->container['api']['ebay']['email address'];

        //$this->firstAuthAppToken();

        //$this->authorizationToken();
        // Having gotten it it is valid for 18 months.

        $this->definitions = [];

        $this->run_time_max = 360; // 5 hours

        $this->thing_report['help'] = 'This reads the Ebay catalog.';
    }

    function run()
    {
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

    function getLink($ref = null)
    {
        $this->link = "https://ebay.com";
    }

    function get()
    {
        // Use the common Ebay variable.
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
    /*
    function doApi($type = "dictionary")
    {
// https://developer.ebay.com/api-docs/commerce/catalog/resources/product_summary/methods/search
// Note: Only the sell.inventory scope is required for selling applications, and only the commerce.catalog.readonly scope is required for buying applications.

// https://francescopantisano.it/ebay-oauth-2-generate-token-refresh-php/
// https://stackoverflow.com/questions/42549023/using-ebay-oauth

        $this->thing->log("Called Ebay Catalog API.");
        if ($type == null) {$type = "dictionary";}


        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        // Create + seperated keyword string
        $keywords = str_replace(" ", "+", $keywords);
        $keywords = urlencode($keywords);

$arr = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic '.$this->code_oauth
        );

        // Not needed.
        $options = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept-language: application/json\r\n" .
                    "app_id: " . $this->appID . "\r\n" .  // check function.stream-context-create on php.net
                    "app_key: " . $this->certID . "\r\n" . 
                    "" // i.e. An iPad 
            )
        );
        $options = null;
        $options = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>$arr
            )
        );


        $context = stream_context_create($options);

        // https://partnernetwork.ebay.com/epn-blog/2010/05/simple-api-searching-example

        // $data_source = "https://svcs.ebay.com/services/search/FindingService/v1/". $keywords;

        $app_id = $this->appID;
        $format = "JSON";
        $global_id = "EBAY-US";

        $data_source = "http://svcs.ebay.com/services/search/FindingService/v1?OPERATION-NAME=findItemsAdvanced&SERVICE-VERSION=1.0.1&SECURITY-APPNAME=" . $app_id . "&RESPONSE-DATA-FORMAT=" . $format . "&REST-PAYLOAD=true&GLOBAL-ID=" . $global_id ."&paginationInput.entriesPerPage=5&paginationInput.pageNumber=1&keywords=" . $keywords . "&itemFilter(0).name=ListingType&itemFilter(0).value(0)=FixedPrice";
$data_source = "https://api.ebay.com/commerce/catalog/v1_beta/product_summary/search?q=drone&limit=3";
//$data_source = "https://api.ebay.com/commerce/taxonomy/v1_beta/get_default_category_tree_id?marketplace_id=EBAY_AT";

        $data = file_get_contents($data_source, false, $context);
var_dump($data);
exit();
        if ($data == false) {
            $this->response = "Could not ask Ebay Catalog.";
            $this->definitions_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, TRUE);
var_dump($json_data);
        // Extract information.
        $ack = $json_data['findItemsAdvancedResponse'][0]['ack']['0'];

        if ($ack != "Success") {
            $this->response = "Could not ask Ebay Catalog.";
            $this->definitions_count = 0;
            return true;
            // Invalid query of some sort.
        }


        $time_stamp = $json_data['findItemsAdvancedResponse'][0]['timestamp']['0'];
        $count = $json_data['findItemsAdvancedResponse'][0]['searchResult']['0']['@count'];

        // Array of ebay items.
        $items = $json_data['findItemsAdvancedResponse'][0]['searchResult']['0']['item'];
        $this->definitions = $items;

        $this->definitions_count = $count;

        return false;
    }
*/

    function doEbaycatalog($text = null)
    {
        //$link = "https://api.ebay.com/identity/v1/oauth2/token";
        $link =
            "https://api.ebay.com/commerce/catalog/v1_beta/product_summary/search?q=drone&limit=3";
        //var_dump($this->clientID.':'.$this->certID);
        $codeAuth = base64_encode($this->clientID . ':' . $this->certID);

        $this->doApi($text);

        return;
        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . $codeAuth,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_GET, 1);
        //        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=authorization_code&amp;code=".$this->authCode."&amp;redirect_uri=".$this->ruName);
        //        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=authorization_code&amp;code=".$this->authCode."&amp;redirect_uri=".$thi);

        $response = curl_exec($ch);
        $json = json_decode($response, true);
        $info = curl_getinfo($ch);
        curl_close($ch);
        //var_dump($json);
        if ($json != null) {
            $this->authToken = $json["access_token"];
            $this->refreshToken = $json["refresh_token"];
        }
    }

    public function doApi($post_data)
    {
        //echo "Do API" . "\n";

        // Your ID and token
        $authToken = $this->authToken;
        //var_dump($authToken);
        // The data to send to the API
        $post_data = json_encode([
            "legacyOrderId" => "110181400870-27973775001",
        ]);
        $url =
            'https://api.sandbox.ebay.com/post-order/v2/cancellation/check_eligibility';

        $post_data = null;
        $url =
            "https://api.ebay.com/commerce/catalog/v1_beta/product_summary/search?q=drone&limit=3";

        $url =
            "https://sandbox.ebay.com/commerce/catalog/v1_beta/product_summary/search?q=drone&limit=3";

        //Setup cURL
        //         $header = array(
        //                        'Accept: application/json',
        //                        'Authorization: TOKEN '.$authToken,
        //                        'Content-Type: application/json',
        //                        'X-EBAY-C-MARKETPLACE-ID: EBAY-UK'
        //                         );

        //$post_data = json_encode(array("legacyOrderId"=>"110181400870-27973775001"));

        $header = [
            'Accept: application/json',
            'Authorization: Bearer ' . $authToken,
            'Content-Type: application/json',
            'X-EBAY-C-MARKETPLACE-ID: EBAY-US',
        ];
        //var_dump($header);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //        curl_setopt($ch, CURLOPT_POST, 1);
        //        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo "ERROR:" . curl_error($ch);
        }
        curl_close($ch);
        //echo "JSON Response." . "\n";
        //var_dump($response);
        //        echo json_decode($response,true);
    }

    function doEligibility()
    {
        $post_data = json_encode([
            "legacyOrderId" => "110181400870-27973775001",
        ]);
        $url =
            'https://api.sandbox.ebay.com/post-order/v2/cancellation/check_eligibility';
        $this->doApi($post_data, $url);
    }

    //}
    public function firstAuthAppToken()
    {
        $url =
            "https://auth.ebay.com/oauth2/authorize?client_id=" .
            $this->clientID .
            "&amp;response_type=code&amp;redirect_uri=" .
            $this->ruName .
            "&amp;scope=https://api.ebay.com/oauth/api_scope https://api.ebay.com/oauth/api_scope/sell.marketing.readonly https://api.ebay.com/oauth/api_scope/sell.marketing https://api.ebay.com/oauth/api_scope/sell.inventory.readonly https://api.ebay.com/oauth/api_scope/sell.inventory https://api.ebay.com/oauth/api_scope/sell.account.readonly https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly https://api.ebay.com/oauth/api_scope/sell.fulfillment https://api.ebay.com/oauth/api_scope/sell.analytics.readonly";

        // Run this manually.
        // Devstack do the API call and retrience the code.
    }

    public function authorizationToken()
    {
        echo "Requested authorization token." . "\n";
        $link = "https://api.ebay.com/identity/v1/oauth2/token";
        $codeAuth = base64_encode($this->clientID . ':' . $this->certID);
        //var_dump($codeAuth);
        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . $codeAuth,
        ]);

        $t =
            'grant_type=authorization_code&
      code=' .
            $codeAuth .
            '&redirect_uri=' .
            $this->ruName;

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $t);
        $response = curl_exec($ch);
        $json = json_decode($response, true);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($json != null) {
            //echo "JSON dump" ."\n";
            //var_dump($json);

            if (!isset($json['access_token'])) {
                echo "Did not retrieve access token.";
            } else {
                $this->authToken = $json["access_token"];
            }
            //var_dump($this->authToken);

            $this->refreshToken = $json["refresh_token"];
            //var_dump($this->refreshToken);
        }
    }

    public function refreshToken()
    {
        $link = "https://api.ebay.com/identity/v1/oauth2/token";
        $codeAuth = base64_encode($this->clientID . ':' . $this->certID);
        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . $codeAuth,
        ]);
        echo $this->refreshToken;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            "grant_type=refresh_token&amp;refresh_token=" .
                $this->refreshToken .
                "&amp;scope=https://api.ebay.com/oauth/api_scope https://api.ebay.com/oauth/api_scope/sell.marketing.readonly https://api.ebay.com/oauth/api_scope/sell.marketing https://api.ebay.com/oauth/api_scope/sell.inventory.readonly https://api.ebay.com/oauth/api_scope/sell.inventory https://api.ebay.com/oauth/api_scope/sell.account.readonly https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly https://api.ebay.com/oauth/api_scope/sell.fulfillment https://api.ebay.com/oauth/api_scope/sell.analytics.readonly"
        );
        $response = curl_exec($ch);
        $json = json_decode($response, true);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($json != null) {
            $this->authToken = $json["access_token"];
        }
    }

    public function makeSnippet()
    {
        $this->thing->log("Called Ebay makeSnippet.");
        if (isset($this->thing_report['snippet'])) {
            return;
        }

        $web = "";

        $web_definitions = "";
        foreach ($this->definitions as $id => $definition) {
            $parsed_item = $this->parseItem($definition);
            $web_definitions .=
                "<br>" .
                $parsed_item['html_link'] .
                " " .
                $parsed_item['price'];
        }

        $snippet_prefix = '<span class = "' . $this->agent_name . '">';
        $snippet_postfix = '</span>';
        $web .= $web_definitions;
        $web = $snippet_prefix . $web . $snippet_postfix;
        $this->thing_report['snippet'] = $web;
    }

    public function makeTXT()
    {
        $this->thing->log("Called Ebay makeTxt.");
        if (isset($this->thing_report['web'])) {
            return;
        }

        $txt = "EBAY\n";
        $txt .= "Ebay items\n";

        //        if (!isset($this->definitions)) {$web .= "<br>No definitions found on Ebay.";}

        //        if (isset($this->definitions)) {
        $txt_items = "";
        foreach ($this->definitions as $id => $definition) {
            $parsed_item = $this->parseItem($definition);
            $txt_items .=
                "\n" . $parsed_item['title'] . " " . $parsed_item['price'];
        }
        //       }

        $txt .= $txt_items;
        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
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
            $currency_prefix = "$";
            $currency_postfix = "";
        }

        $price = $item['sellingStatus'][0]['currentPrice'][0]['__value__'];

        $price_text = $currency_prefix . $price . $currency_postfix;

        $link = $item["viewItemURL"][0];
        $link_thumbnail = $item["galleryURL"][0];
        $location = $item["location"][0];
        $country = $item["country"][0];

        $html_link = '<a href="' . $link . '">';
        $html_link .= $title;
        $html_link .= "</a>";

        $parsed_item = [
            "title" => $title,
            "price" => $price_text,
            "link" => $link,
            "thumbnail" => $link_thumbnail,
            "location" => $location,
            "country" => $country,
            "html_link" => $html_link,
        ];
        return $parsed_item;
    }

    public function makeSMS()
    {
        $sms = "EBAY CATALOG";
        if (isset($this->search_words) and $this->search_words != "") {
            $sms .= " " . strtoupper($this->search_words);
        }

        $definitions_count = 0;
        if (isset($this->definitions_count)) {
            $definitions_count = $this->definitions_count;
        }

        switch ($definitions_count) {
            case 0:
                $sms .= " | No definitions found.";
                break;
            case 1:
                $sms .= " | " . $this->definitions[0];
                break;
            default:
                foreach ($this->definitions as $definition) {
                    $parsed_item = $this->parseItem($definition);
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
        $message = "Ebay Catalog";

        $definitions_count = 0;
        if (isset($this->definitions_count)) {
            $definitions_count = $this->definitions_count;
        }

        switch ($definitions_count) {
            case 0:
                $message .= " did not find any definitions.";
                break;
            case 1:
                $message .= ' found, "' . $this->definitions[0] . '"';
                break;
            default:
                foreach ($this->definitions as $definition) {
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
        $this->thing->log("Ebay Catalog readSubject");
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

            if ($input == 'ebaycatalog') {
                //$this->search_words = null;
                $this->response = "Did not ask Ebay about nothing.";
                return;
            }
        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "ebay catalog is")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("ebay catlalog is")
            );
        } elseif (
            ($pos = strpos(strtolower($input), "ebay catalog")) !== false
        ) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("ebay catalog")
            );
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->doEbaycatalog();
            $this->response =
                "Asked Ebay Catalog about the word " .
                $this->search_words .
                ".";
            return false;
        }

        $this->response = "Message not understood";
        return true;
    }
}
