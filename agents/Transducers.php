<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Transducers extends Agent
{
    // This gets Forex from an API.

    public $var = "hello";

    public function init()
    {
        $this->test = "Development code"; // Always
        /*
        $this->definitions_count = 0;
        $this->keywords = [
            "oxford",
            "dictionary",
            "dictionaries",
            "english",
            "spanish",
            "german",
        ];
        $this->application_id = $this->settingsAgent([
            "oxford_dictionaries",
            "application_id",
        ]);
        $this->application_key = $this->settingsAgent([
            "oxford_dictionaries",
            "application_key",
        ]);

        $this->run_time_max = 360; // 5 hours
*/
    }

    public function run()
    {
        $this->getTransducers();
    }

    function set()
    {
        //$this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "transducers" . " " . $this->from
        );

        //       $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        //        $this->counter = $this->counter + 1;
    }

    function getTransducers($type = "dictionary")
    {
//$this->getSnapshot();

        if ($type == null) {
            $type = "dictionary";
        }

        $options = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ];

        $context = stream_context_create($options);

        $data_source = "https://192.168.10.10/snapshot.json";

        /*
var_dump($data_source);

$w = stream_get_wrappers();
echo 'openssl: ',  extension_loaded  ('openssl') ? 'yes':'no', "\n";
echo 'http wrapper: ', in_array('http', $w) ? 'yes':'no', "\n";
echo 'https wrapper: ', in_array('https', $w) ? 'yes':'no', "\n";
echo 'wrappers: ', var_export($w);


$url= 'https://example.com';
*/

        $json_data = file_get_contents($data_source, false, $context);
        $data = json_decode($json_data, true);

        $transducers = $data["transducers"];
$this->transducers = $transducers;
        $this->textTransducers($transducers);
        $alerts = $this->alertTransducers($transducers);

if (isset($alerts) and count($alerts) > 0) {

foreach($alerts as $alert) {

$this->response .= " " . $alert['short_text'];

}
}
    }

    function textTransducers($transducers)
    {
        $m = "TRANSDUCERS ";
        foreach ($transducers as $i => $j) {
            //$m .= " " . $i . $j['name'] . " " . $j['amount'];
            $m .= $j["name"] . " " . $j["amount"] . " ";
        }
        $this->response .= $m;
        $this->response .= $this->zuluStamp();

        return $m;
    }

    // Not sure about name yet.
    function operatorText($text)
    {
        $first_char = $text[0];
        $number = floatval(substr($text, 1));

        return ["operator" => $first_char, "number" => $number];
    }

    function alertTransducers($transducers = null)
    {

if ($transducers === null) {

$transducers = $this->transducers;

}

$alerts = [];
        /*
        $json_data = json_decode($data, true);
        $transducers = $json_data['transducers'];
*/
        $universe_alerts = [
            "VLT0" => [
                "<11.0" => ["text" => "LOW BATTERY"],
                ">13.8" => ["text" => "FLOAT BATTERY"],
                "<8.0" => ["text" => "CRITICALLY LOW BATTERY"],
                ">15.0" => ["text" => "HIGH BATTERY"],
            ],
            "GASA" => [">600" => ["text" => "CHECK SENSOR A"]],
            "GASE" => [">600" => ["text" => "CHECK SENSOR E"]],
            "VLT1" => ["<12.0" => ["text" => "LOW CHARGE START BATTERY"]],
            "AMP0" => [
                ">0" => ["text" => "CHARGING"],
                "<0" => ["text" => "DISCHARGING"],
            ],
        ];

        $m = "TRANSDUCERS ";
        foreach ($transducers as $i => $j) {

            $t = $j["name"];

            // Extract rule
            foreach ($universe_alerts[$t] as $rule => $rule_meta) {
                $operator = $this->operatorText($rule)["operator"];
                $number = $this->operatorText($rule)["number"];
$text = null;
                if ($operator === '<') {
                    if ($number > floatval($j["amount"])) {
$text = "ALERT <";                  
                    }
                }

                if ($operator === '>') {
                    if ($number < floatval($j["amount"]) ) {
$text = "ALERT >";
                    }
                }
$text2 = null;
if ($text !== null) {
$text2 = $t . " " . floatval($j['amount']) . " " . $text . " " . $number. " " .  $t. " " . $universe_alerts[$t][$rule]["text"];
$alerts[] = ['text'=>$text2, 'short_text'=>$t ." " . floatval($j['amount']). " " . $universe_alerts[$t][$rule]['text']];
}
            }
        }

return $alerts;
    }

    public function makeSMS()
    {
        $sms = "TRANSDUCERS";

        $sms .= " | " . $this->response;

        // Really need to refactor this double :/
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }
}
