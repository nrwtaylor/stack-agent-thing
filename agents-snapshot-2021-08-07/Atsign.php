<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Atsign extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->thing_report['help'] =
            'Text AT SIGN < A web link> to add a link to this list.';
    }

    function run()
    {
    }

    public function set()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["atsign", "refreshed_at"],
            $this->thing->json->time()
        );
    }

    function get()
    {
    }

    public function makeChoices()
    {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    public function makeTxt()
    {
        $this->thing_report['txt'] = "No text retrieved.";
    }

    function makeWeb()
    {
        $this->getAtsigns();

        $web = "<b>AT SIGN Agent</b><br><p>";
        $web .= "<p>";

        if (isset($this->atsigns) and count($this->atsigns) > 0) {
            $web .= "<b>COLLECTED AT SIGNS</b><br><p>";
            $web .= "<ul>";

            $tempArr = array_unique(array_column($this->atsigns, 'atsign'));
            $atsigns = array_intersect_key($this->atsigns, $tempArr);

            foreach ($atsigns as $i => $atsign_array) {
                $atsign = $atsign_array['atsign'];

                $atsign_link =
                    $this->web_prefix .
                    "thing/" .
                    $atsign_array['uuid'] .
                    "/forget";
                $html_link = "[" . '<a href="' . $atsign_link . '">forget</a>]';

                if (stripos($atsign, "://") !== false) {
                    $link = '<a href="' . $atsign . '">' . $atsign . '</a>';
                    $web .= "<li>" . $link . " " . $html_link . "<br>";

                    continue;
                } elseif (stripos($atsign, ":/") !== false) {
                    $link = '<a href="' . $atsign . '">' . $atsign . '</a>';
                    $try_link = '[Try ' . str_replace(":/", "://", $link) . ']';
                    $web .= "<li>" . $link . " " . $try_link . "<br>";

                    continue;
                } else {
                    $link =
                        'Try <a href="https://' .
                        $atsign .
                        '">' .
                        "https://" .
                        $atsign .
                        '</a>';

                    $web .=
                        "<li>" .
                        $atsign .
                        ' [' .
                        $link .
                        ']' .
                        $html_link .
                        "<br>";
                    continue;
                }

                $web .= "<li>" . $atsign . $html_link . "<br>";
            }

            $web .= "</ul>";

            $web .= "<p>";
        }
        $web .= "<b>HELP</b><br><p>";
        $web .= $this->thing_report['help'];

        $this->thing_report['web'] = $web;
    }

    function makeSMS()
    {
        $sms_message = "AT SIGN";
        if (isset($this->response) and $this->response != "") {
            $sms_message .= " | ";
            $sms_message .= $this->response;
        }

        if ($this->verbosity >= 2) {
        }

        if ($this->atsign == "X") {
            $atsigns_text = "No @ signs found. ";
        } else {
            $atsigns_text = "";

            foreach ($this->atsigns as $i=>$atsign) {
                $atsigns_text .= $atsign . " ";
            }
            $atsigns_text = trim($atsigns_text);
        }

        $sms_message .= " | " . $atsigns_text;

        $this->thing_report['sms'] = $sms_message;
        $this->sms_message = $sms_message;
    }

    public function getAtsign()
    {
        $this->getAtsigns();

        $this->atsign = "X";
        if (isset($this->atsigns[0])) {
            //$this->atsign = $this->atsigns[0]['atsign'];
            $this->atsign = $this->atsigns[0];
        }
    }

    public function getAtsigns()
    {
        $atsigns = [];

        $findagent_thing = new Findagent($this->thing, 'atsign');

        if (!is_array($findagent_thing->thing_report['things'])) {
            return;
        }

        $count = count($findagent_thing->thing_report['things']);

        if ($count > 0) {
            foreach (
                array_reverse($findagent_thing->thing_report['things'])
                as $thing_object
            ) {
                $uuid = $thing_object['uuid'];
                $variables_json = $thing_object['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                $age =
                    strtotime($this->thing->time()) -
                    strtotime($thing_object['created_at']);

                if (isset($variables['atsign'])) {
                    $task_atsigns = $this->extractAtsigns(
                        $thing_object['task']
                    );
                    if ($task_atsigns === true) {
                        continue;
                    }
                    if (count($task_atsigns) == 0) {
                        continue;
                    }
                    $task_atsigns = $this->filterAtsigns($task_atsigns);
                    //$atsign = [
                    //    "atsign" => implode(" ", $task_atsigns),
                    //    "uuid" => $thing_object['uuid'],
                    //];


                    $atsign = implode(" ", $task_atsigns);
                    $atsigns = array_merge($atsigns, $task_atsigns);
                    //$atsigns[] = $atsign;
                }
            }
        }

        $atsigns = array_reverse($atsigns);
        $this->atsigns = array_unique($atsigns);
    }

    public function filterAtsigns($atsigns = null)
    {
        if (!is_array($atsigns)) {
            return true;
        }
        /*
        foreach ($atsigns as $i => $atsign) {
            $parts = explode(" ", $atsign);
            if (count($parts) == 1) {
                if (stripos($atsign, '.') !== false) {
                    $atsigns[$i] = explode(' ', $atsign)[0];
                } else {
                    unset($atsigns[$i]);
                }
            }

            if (stripos($atsign, "://") !== false) {
                continue;
            }

            if (stripos($atsign, ":/") !== false) {
                unset($atsigns[$i]);
            }

            // Did this pick up a decimal.
            $tokens = explode(".", $atsign);
            if (count($tokens) == 2) {
                if (!is_numeric($tokens[0])) {
                    continue;
                }
                if (!is_numeric($tokens[1])) {
                    continue;
                }
                unset($atsigns[$i]);
            }
        }
*/

        return $atsigns;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];
        $this->thing_report['help'] = 'This reads a web resource.';
    }

    public function extractAtsigns($text = null)
    {
        if ($text == null) {
            return true;
        }

        $text = str_replace('atsign is', '', $text);
        $text = str_replace('atsign', '', $text);
        $text = trim($text);

        // https://stackoverflow.com/questions/1812883/preg-match-all-words-start-with-an
        $pattern = "/((?<!\S)@\w+(?!\S))/";
        preg_match_all($pattern, $text, $match);
        if (!isset($atsigns)) {
            $atsigns = [];
        }

        $atsigns = array_merge($atsigns, $match[0]);
        $atsigns = array_unique($atsigns);
        // Deal with spaces
        $atsigns = $this->filterAtsigns($atsigns);

        return $atsigns;
    }

    public function hasAtsign($text = null)
    {
        $atsigns = $this->extractAtsigns($text);

        // No @ signs found.
        if ($atsigns === true) {
            return false;
        }

        if (count($atsigns) >= 1) {
            return true;
        }

        return false;
    }

    public function extractAtsign($text = null)
    {
        $atsigns = $this->extractAtsigns($text);

        // No AT SIGNS found.
        if ($atsigns === true) {
            return false;
        }
        if (count($atsigns) == 1) {
            return $atsigns[0];
        }

        return false;
    }

    public function stripAtsigns($text = null, $replace_text = null)
    {
        if ($text == null) {
            $text = $this->input;
        }
        if ($replace_text == null) {
            $replace_text = "";
        }

        $atsigns = $this->extractAtsigns($text);
        foreach ($atsigns as $i => $atsign) {
            $text = str_replace($atsign, $replace_text, $text);
        }
        return $text;
    }

    public function readSubject()
    {
        $this->response = null;
        $this->num_hits = 0;

        $input = $this->input;
        /*
        //$input = $this->assert($this->input);
        $input = $this->subject;
        if ((isset($this->agent_input)) and ($this->agent_input != "")) {
            $input = $this->agent_input;
        }
*/
        //$input = $this->assert($this->input);

        $string = $input;
        $str_pattern = 'atsign';
        $str_replacement = '';
        $filtered_input = $input;
        if (strpos($string, $str_pattern) !== false) {
            $occurrence = strpos($string, $str_pattern);
            $filtered_input = substr_replace(
                $string,
                $str_replacement,
                strpos($string, $str_pattern),
                strlen($str_pattern)
            );
        }

        $input = trim($filtered_input);

        if ($input == '') {
            $this->getAtsign();
            return;
        }

        $this->atsign = $input;

        // Get as signs from string
        $this->atsigns = $this->extractAtsigns($input);
        $this->atsign = "X";
        if (isset($this->atsigns[0])) {
            $this->atsign = $this->atsigns[0];
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'atsign') {
                $this->getAtsign();
                return;
            }

            if ($input == 'read') {
                return;
            }
        }
        return;
    }
}
