<?php
/**
 * Cat.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Googlelink extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {
        $this->agent_name = "googlelink";
        $this->test= "Development code";
        $this->thing_report["info"] = "This provides a google url link.";
        $this->thing_report["help"] = "You can help other people share your search.";
    }


    /**
     *
     */
    function makeSMS() {
        $this->node_list = array("google link"=>array("google"));
        $m = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }


    /**
     *
     */
    function makeChoices() {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }


    /**
     *
     * @param unknown $text (optional)
     */
    function doGooglelink($text = null) {

        // If we didn't receive the command NTP ...

        // Steps.

        // Identify parts of speech
        $brilltagger = new Brilltagger($this->thing, $text);

        $this->tokens = $brilltagger->tags;
        $processed_text = "";
        $exclude_parts_of_speech = array("IN", "CC", "VBZ");
        foreach ($this->tokens as $index=>&$token) {
            $token['use_flag'] = 1;
            if (in_array($token["tag"], $exclude_parts_of_speech)) {$token['use_flag'] = 0;}
        }



        foreach ($this->tokens as $index=>&$token) {
            if ($token["use_flag"] == 0) {continue;}

            $processed_text .= $token['token'] . " ";

        }


        $text = $processed_text;
        // Steps.
        $ngram = new Ngram($this->thing, $text);

        $longest_string = "";
        $longest_string_length = 0;
        foreach (array(7, 6, 5, 4, 3, 2, 1) as $key=>$value) {
            $arr = $ngram->extractNgrams($text, $n = $value);
            if ($longest_string_length > 0) {break;}

            foreach ($arr as $key=>$value) {
                // Measuring complexity.
                if ($longest_string_length < strlen($value)) {
                    $longest_string_length = strlen($value);
                    $longest_string = $value;
                }
            }
        }

        if ($longest_string_length == 0) {$longest_string = $text;}

        // Turn it into this.
        // "https://www.google.com/search?q=google+hello"
        $m = mb_ereg_replace(" ", "+", $longest_string);
        $m = mb_strtolower($m);

        // "https://www.google.com/search?q=longest+string"

        $this->response = "https://www.google.com/search?q=" . $m;
        $this->googlelink_message = $this->response;

    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $input = $this->subject;
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "google link")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("google link"));
        } elseif (($pos = strpos(strtolower($input), "googlelink")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("googlelink"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");


        // Note intentional use of subject.
        // Must be passed without pre-processing
        $this->doGooglelink($filtered_input);

        return false;
    }


}
