<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

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

        $this->run_time_max = 360; // 5 hours
        $this->link = "https://www.amazon.com";

        $this->response = "Dev. ";

        $this->thing_report['help'] =
            'This requests products using the Amazon API.';

    }

    function run()
    {
        $this->getApi("dictionary");
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

    function getApi($type = "dictionary")
    {
        if ($type == null) {
            $type = "dictionary";
        }

        $keywords = "";
        if (isset($this->search_words)) {
            $keywords = $this->search_words;
        }

        $keywords = urlencode($keywords);

        $this->response .=
            'Asked Amazon about the word "' . $this->search_words . '". ';
        $this->response .= 'Asked Amazon about the word "' . $keywords . '". ';

        /*
        $options = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept-language: application/json\r\n" .
                    "app_id: " . $this->application_id . "\r\n" .  // check function.stream-context-create on php.net
                    "app_key: " . $this->application_key . "\r\n" . 
                    "" // i.e. An iPad 
            )
        );


        $context = stream_context_create($options);
*/

        //

        $data_source = "http://webservices.amazon.com/onca/xml?
Service=AWSECommerceService&
AWSAccessKeyId=[AWS Access Key ID]&
AssociateTag=[Associate ID]&  
Operation=ItemSearch&
Keywords=the%20hunger%20games&
SearchIndex=Books
&Timestamp=[YYYY-MM-DDThh:mm:ssZ]
&Signature=[Request Signature]";

        $data_source =
            "http://webservices.amazon.com/onca/xml?
Service=AWSECommerceService&
AWSAccessKeyId=" .
            $this->access_key .
            "&
Operation=ItemSearch&
Keywords=the%20hunger%20games&
SearchIndex=Books";

        $data = @file_get_contents($data_source, false, $context);

        if ($data == false) {
            $this->response .= "No response from Amazon. ";
            return true;
            // Invalid query of some sort.
        }

        var_dump($data);
        echo "Amazon.php getApi()";
        exit();

        $json_data = json_decode($data, true);

        $definitions =
            $json_data['results'][0]['lexicalEntries'][0]['entries'][0][
                'senses'
            ];

        $count = 0;
        foreach ($definitions as $id => $definition) {
            if (!isset($definition['definitions'][0])) {
                continue;
            }
            $this->definitions[] = $definition['definitions'][0];
            $count += 1;
        }

        $this->definitions_count = $count;

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
        $sms = "AMAZON | " . $this->response;
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
                //$this->search_words = null;
                $this->response .= "Asked Amazon about nothing. ";
                return;
            }
        }

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
