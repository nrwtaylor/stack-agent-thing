<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Bible extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->api_key = $this->thing->container['api']['biblesearch'];
        $this->retain_for = 1; // Retain for at least 1 hour.
        // Allow for a new state tree to be introduced here.
        $this->node_list = ["start" => ["useful", "useful?"]];
    }

    public function getBible($words = null)
    {
        if (!isset($this->keywords)) {
            $this->keywords = $words;
        }

        $words = str_replace(' ', '+', $words);

        $xml = $this->getText($words);

        $verses = $xml->search->result->verses->verse;

        if ($verses == null) {
            $sms_message = "BIBLE";
            $sms_message .= " | No matching verse found for " . $words . ".";
            $sms_message .= " | MESSAGE 'BIBLE words'";
            $this->sms_message = $sms_message;
            return;
        }
        $arr[] = [];
        $sms_messages = [];
        foreach ($verses as $key => $verse) {
            $id = (string) $verse->id;
            $id = strip_tags($id);

            $text = (string) $verse->text;
            $text = strip_tags($text);

            $copyright = (string) $verse->copyright;

            $text = preg_replace('#^\d+#', '', $text);

            $text = preg_replace('/^[a-zA-Z]+$/', '', $text);

            // Remove line breaks
            $text = preg_replace("/\r|\n/", " ", $text);

            $message = $id . " | " . $text;

            $sms_message = "BIBLE";
            $sms_message .= " | " . $message;
            $sms_message .= " | MESSAGE 'BIBLE words'";

            $arr[] = ["id" => $id, "verse" => $text, "message" => $message];
            $sms_messages[] = $sms_message;
        }

        $k = array_rand($sms_messages);
        $this->sms_message = $sms_messages[$k];
        //        $this->sms_message = "testtest";

        $k = array_rand($arr);

        $this->sms_message = "BIBLE";
        $this->sms_message .= " | " . $arr[$k]['message'];

        $this->sms_message .= " | google " . $this->getLink($arr[$k]['id']);

        $this->sms_message .= " | text source bibles.org datafeed";
    }
    /*
    public function Parse($url)
    {
        $fileContents = file_get_contents($url);

        $fileContents = str_replace(["\n", "\r", "\t"], '', $fileContents);

        $fileContents = trim(str_replace('"', "'", $fileContents));

        $simpleXml = simplexml_load_string($fileContents);

        $json = json_encode($simpleXml);

        return $json;
    }
*/
    public function nullAction()
    {
        $names = $this->thing->Write(
            ["character", "action"],
            'null'
        );

        $this->message = "BIBLE | Request not understood. | TEXT SYNTAX";
        $this->sms_message = "BIBLE | Request not understood. | TEXT SYNTAX";
        $this->response = true;
        return $this->message;
    }

    function bibleInfo()
    {
        $this->sms_message = "BIBLE";

        $this->sms_message .= " | ";

        $this->sms_message .=
            'Live data feed provided through the bibles.org API. | https://developer.translink.ca/ | ';

        $this->sms_message .= "TEXT HELP";

        return;
    }

    function bibleHelp()
    {
        $this->sms_message = "BIBLE";

        $this->sms_message .= " | ";

        $this->sms_message .=
            'Text one or more words. | For example, "Bible peace". | ';

        $this->sms_message .= "TEXT BIBLE <word(s)>";

        return;
    }

    function bibleSyntax()
    {
        $this->sms_message = "BIBLE";

        $this->sms_message .= " | ";

        $this->sms_message .= 'Syntax: "<keyword>". | ';

        $this->sms_message .= "TEXT HELP";

        return;
    }

    public function getVerse()
    {
        //$token = '#{API Token}';
        $token = $this->api_key;
        $url = 'https://bibles.org/v2/verses/eng-GNTD:Acts.8.34.xml';

        // Set up cURL
        $ch = curl_init();
        // Set the URL
        curl_setopt($ch, CURLOPT_URL, $url);
        // don't verify SSL certificate
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // Return the contents of the response as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // Follow redirects
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        // Set up authentication
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$token:X");

        // Do the request
        $response = curl_exec($ch);
        curl_close($ch);

        //print($response);

        $test = simplexml_load_string($response);
        //print_r($test);
    }

    public function getText($keywords = null)
    {
        if ($keywords == null) {
            $options = [
                'peace',
                'love',
                'help',
                'protect',
                'care',
                'support',
                'aid',
            ];

            $k = array_rand($options);
            $keywords = $options[$k];
        }

        //$url = 'https://bibles.org/v2/verses.xml?keyword=' . $keywords;

        // devstack. endpoint has changed.
        //$url = 'https://labs.bible.org/api/?passage=John+3:16-17';
        $url = 'https://labs.bible.org/api/?keyword=samaritan';

        $xml = $this->getXML($url);

        return $xml;
    }

    public function getXML($url)
    {
        $token = $this->api_key;

        // Set up cURL
        $ch = curl_init();
        // Set the URL
        curl_setopt($ch, CURLOPT_URL, $url);
        // don't verify SSL certificate
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // Return the contents of the response as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // Follow redirects
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        // Set up authentication
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$token:X");

        // Do the request
        $response = curl_exec($ch);

        curl_close($ch);
        //$xml = simplexml_load_string($response);
        $xml = new SimpleXMLElement($response);

        return $xml;
    }

    public function getLink($ref = null)
    {
        $this->link = "https://www.google.ca/search?q=" . $ref;
        return $this->link;
    }

    public function findText($input)
    {
        $url = 'https://bibles.org/v2/search.xml?query=' . $input;
    }

    // -----------------------

    public function respondResponse()
    {
        //$this->thing_report = array('thing' => $this->thing->thing);

        // Thing actions
        $this->thing->flagGreen();

        //$this->readSubject();

        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['choices'] = false;
        $this->thing_report['info'] = 'SMS sent';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] = 'Connector to bibles.org API.';

        return $this->thing_report;
    }

    private function nextWord($phrase)
    {
    }

    public function readSubject()
    {
        $emoji_thing = new Emoji($this->thing, "emoji");
        $thing_report = $emoji_thing->thing_report;

        if (isset($emoji_thing->emojis)) {
            $input = ltrim(strtolower($emoji_thing->translated_input));
        }

        //        $this->response = null;

        $keywords = ['bible'];

        //$input = strtolower($this->subject);

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            $input = $this->subject;

            if (strtolower($input) == 'bible') {
                $this->getBible();
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'bible':
                            $prefix = 'bible';
                            $words = preg_replace(
                                '/^' . preg_quote($prefix, '/') . '/',
                                '',
                                $input
                            );
                            $words = ltrim($words);
                            $this->getBible($words);
                            return;

                        default:

                    }
                }
            }
        }

        $this->nullAction();
        return "Message not understood";
    }
}
