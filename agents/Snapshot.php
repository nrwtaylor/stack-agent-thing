<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Snapshot extends Agent
{
    // This gets Forex from an API.

    public $var = 'hello';

    public function init()
    {

$this->default_data_source = "https://192.168.10.10/snapshot.json";

$this->data_source = $this->default_data_source;
if (is_string($this->agent_input)) {
        $this->data_source = $this->agent_input;
}

        $this->test = "Development code"; // Always
    }

    public function run()
    {
       // $this->getApi();
       $this->getSnapshot();
    }

    function set()
    {
//        $this->thing->Write("refreshed_at", $this->current_time);
        //$this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    function get()
    {
        $this->getSnapshot();
    }

    function getSnapshot()
    {
        $data = file_get_contents($this->data_source);
        if ($data == false) {
            return true;
            // Invalid query of some sort.
        }

        $json_data = json_decode($data, true);
        if (!isset($json_data['thingReport'])) {
            return false;
        }
        if (!isset($json_data['thingReport']['snapshot'])) {
            return false;
        }
        $this->snapshot = $json_data['thingReport']['snapshot'];
        return $this->snapshot;
    }


    public function readSubject()
    {
        return false;
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "snapshot" . " " . $this->from
        );

 //       $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

//        $this->counter = $this->counter + 1;
    }





  function getSnapshot($type = "dictionary")
    {
        if ($type == null) {
            $type = "dictionary";
        }

$options=array(
      "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );  








        $context = stream_context_create($options);
        $data_source = "https://192.168.10.10/snapshot.json";


        $data = file_get_contents($this->data_source, false, $context);

        $json_data = json_decode($data, true);
        $transducers = $json_data['transducers'];

        $this->response .= $this->textTransducers($transducers);



//        $json_data = json_decode($data, true);
        if (!isset($json_data['thingReport'])) {
            return false;
        }
        if (!isset($json_data['thingReport']['snapshot'])) {
            return false;
        }
        $this->snapshot = $json_data['thingReport']['snapshot'];
        return $this->snapshot;




}









    function deprecate_getApi($type = "dictionary")
    {
//        if ($this->application_key == null or $this->application_id == null) {
//            return true;
//        }
        if ($type == null) {
            $type = "dictionary";
        }
//
//        $keywords = "";
//        if (isset($this->search_words)) {
//            $keywords = $this->search_words;
//        }

//        $keywords = urlencode($keywords);
/*
        $options = [
            "http" => [
                "ignore_errors" => true,
                "method" => "GET",
                "header" =>
                    "Accept-language: application/json\r\n" .
                    "app_id: " .
                    $this->application_id .
                    "\r\n" . // check function.stream-context-create on php.net
                    "app_key: " .
                    $this->application_key .
                    "\r\n" .
                    "", // i.e. An iPad
            ],
        ];
*/
/*
        $options = [
            "http" => [
                "ignore_errors" => true,
                "method" => "GET",
                "header" =>
                    "Accept-language: application/json\r\n" .
                    "", // i.e. An iPad
            ],
        ];
*/
$options=array(
      "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );  



        $context = stream_context_create($options);

        $data_source = "https://192.168.10.10/snapshot.json";
        //get /entries/{source_lang}/{word_id}/synonyms

/*
var_dump($data_source);

$w = stream_get_wrappers();
echo 'openssl: ',  extension_loaded  ('openssl') ? 'yes':'no', "\n";
echo 'http wrapper: ', in_array('http', $w) ? 'yes':'no', "\n";
echo 'https wrapper: ', in_array('https', $w) ? 'yes':'no', "\n";
echo 'wrappers: ', var_export($w);


$url= 'https://example.com';
*/


        $data = file_get_contents($data_source, false, $context);

  //      var_dump($data['transducers']);
  //      exit();

        $json_data = json_decode($data, true);
//var_dump($json_data);
        $transducers = $json_data['transducers'];
//var_dump($transducers);


                    $m = "TRANSDUCERS ";
                    foreach ($transducers as $i => $j) {
                        //$m .= " " . $i . $j['name'] . " " . $j['amount'];
                        $m .= $j["name"] . " " . $j["amount"] . " ";
                    }
//var_dump($m);

//        exit();
$this->response .= $m;

$this->response .= $this->zuluStamp();


        /*
        $definitions =
            $json_data["results"][0]["lexicalEntries"][0]["entries"][0][
                "senses"
            ];

        $count = 0;
        foreach ($definitions as $id => $definition) {
            if (!isset($definition["definitions"][0])) {
                continue;
            }
            $this->definitions[] = $definition["definitions"][0];
            $count += 1;
        }

        $this->definitions_count = $count;
*/
        return false;
    }

    public function makeSMS()
    {
        $sms = "SNAPSHOT";

        $sms .= " | " . $this->response;

        // Really need to refactor this double :/
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }
}
