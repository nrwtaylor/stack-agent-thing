<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Telephonenumber extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->thing_report['help'] =
            'Text TELEPHONE NUMBER < A telephone number> to add a number to this list.';
    }

    function run()
    {
    }

    public function set()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["telephonenumber", "refreshed_at"],
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

    function cleanTelephonenumber($text = null)
    {
        if ($text == null) {
            return true;
        }
        return true;
        //return $url;
    }

    function makeWeb()
    {
        $this->getTelephonenumbers();

        $web = "<b>Telephone Number Agent</b><br><p>";
        $web .= "<p>";

        if (isset($this->telephone_numbers) and count($this->telephone_numbers) > 0) {
            $web .= "<b>COLLECTED TELEPHONE NUMBERS</b><br><p>";
            $web .= "<ul>";

            $tempArr = array_unique(array_column($this->telephone_numbers, 'telephone_number'));
            $telephone_numbers = array_intersect_key($this->telephone_numbers, $tempArr);

            foreach ($telephone_numbers as $i => $telephone_number_array) {
                $telephone_number = $telephone_number_array['telephone_number'];

                $telephone_number_link =
                    $this->web_prefix .
                    "thing/" .
                    $telephone_number_array['uuid'] .
                    "/forget";
                $html_link = "[" . '<a href="' . $telephone_number_link . '">forget</a>]';

                if (stripos($telephone_number, "://") !== false) {
                    $link = '<a href="' . $telephone_number . '">' . $telephone_number . '</a>';
                    $web .= "<li>" . $link . " " . $html_link . "<br>";

                    continue;
                } elseif (stripos($telephone_number, ":/") !== false) {
                    $link = '<a href="' . $telephone_number . '">' . $telephone_number . '</a>';
                    $try_link = '[Try ' . str_replace(":/", "://", $link) . ']';
                    $web .= "<li>" . $link . " " . $try_link . "<br>";

                    continue;
                } else {
                    $link =
                        'Try <a href="https://' .
                        $telephone_number .
                        '">' .
                        "https://" .
                        $telephone_number .
                        '</a>';

                    $web .=
                        "<li>" .
                        $telephone_number .
                        ' [' .
                        $link .
                        ']' .
                        $html_link .
                        "<br>";
                    continue;
                }

                $web .= "<li>" . $telephone_number . $html_link . "<br>";
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
        $sms_message = "TELEPHONE NUMBER";
        if (isset($this->response) and $this->response != "") {
            $sms_message .= " | ";
            $sms_message .= $this->response;
        }

        if ($this->verbosity >= 2) {
        }

        if ($this->telephone_number == "X") {
            $telephone_numbers_text = "No telephone numbers found. ";
        } else {
            $telephone_numbers_text = $this->telephone_number;
        }
        $sms_message .= " | " . $telephone_numbers_text;

        $this->thing_report['sms'] = $sms_message;
        $this->sms_message = $sms_message;
    }

    public function getTelephonenumber()
    {
        $this->getTelephonenumbers();

        $this->telephone_number = "X";
        if (isset($this->telephone_numbers[0])) {
            $this->telephone_number = $this->telephone_numbers[0]['telephone_number'];
        }
    }

    public function getTelephonenumbers()
    {
        $telephone_numbers = [];

        $findagent_thing = new Findagent($this->thing, 'telephonenumber');

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

                if (isset($variables['telephone_number'])) {
                    $task_telephone_numbers = $this->extractTelephonenumbers($thing_object['task']);
                    if ($task_telephone_numbers === true) {
                        continue;
                    }
                    if (count($task_telephone_numbers) == 0) {
                        continue;
                    }
                    $task_telephone_numbers = $this->filterUrls($task_telephone_numbers);

                    $telephone_number = [
                        "telephone_number" => implode(" ", $task_telephone_numbers),
                        "uuid" => $thing_object['uuid'],
                    ];

                    $telephone_numbers[] = $telephone_number;
                }
            }
        }

        $telephone_numbers = array_reverse($telephone_numbers);
        $this->telephone_numbers = $telephone_numbers;
    }

    public function filterTelephonenumbers($telephone_numbers = null)
    {
        if (!is_array($telephone_numbers)) {
            return true;
        }

        foreach ($telephone_numbers as $i => $telephone_number) {
            $parts = explode(" ", $telephone_number);
            if (count($parts) == 1) {
                if (stripos($telephone_number, '.') !== false) {
                    $telephone_numbers[$i] = explode(' ', $telephone_number)[0];
                } else {
                    unset($telephone_numbers[$i]);
                }
            }

            if (stripos($telephone_number, "://") !== false) {
                continue;
            }

            if (stripos($telephone_number, ":/") !== false) {
                unset($telephone_numbers[$i]);
            }

            // Did this pick up a decimal.
            $tokens = explode(".", $telephone_number);
            if (count($tokens) == 2) {
                if (!is_numeric($tokens[0])) {
                    continue;
                }
                if (!is_numeric($tokens[1])) {
                    continue;
                }
                unset($telephone_numbers[$i]);
            }
        }
        $telephone_numbers = array_values($telephone_numbers);
        return $telephone_numbers;
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

    public function extractTelephonenumbers($text = null)
    {

        // "is hard"
        // See https://github.com/google/libphonenumber
        // See https://stackoverflow.com/questions/16699007/regular-expression-to-match-standard-10-digit-phone-number

        if ($text == null) {
            return true;
        }
// https://zapier.com/blog/extract-links-email-phone-regex/
// https://www.regextester.com/99519
// $pattern = '/(([\+]{1}[0-9]{1,3}[\ ]{1}[0-9]{1,2}[\ ]{1}[0-9]{4}[\ ]{1}[0-9]{4})|([0]{1}[0-9]{1}[\ ]{1}[0-9]{4}[\ ]{1}[0-9]{4})|([0]{1}[0-9]{1}[\-]{1}[0-9]{4}[\-]{1}[0-9]{4})|([\(]{1}[0]{1}[0-9]{1}[\)]{1}[\ ]{1}[0-9]{4}([\ ]|[\-]){1}[0-9]{4})|([0-9]{4}([\ ]|[\-])?[0-9]{4})|([0]{1}[0-9]{3}[\ ]{1}[0-9]{3}[\ ]{1}[0-9]{3})|([0]{1}[0-9]{9})|([\(]{1}[0-9]{3}[\)]{1}[\ ]{1}[0-9]{3}[\-]{1}[0-9]{4})|([0-9]{3}([\/]|[\-]){1}[0-9]{3}[\-]{1}[0-9]{4})|([1]{1}[\-]?[0-9]{3}([\/]|[\-]){1}[0-9]{3}[\-]{1}[0-9]{4})|([1]{1}[0-9]{9}[0-9]?)|([0-9]{3}[\.]{1}[0-9]{3}[\.]{1}[0-9]{4})|([\(]{1}[0-9]{3}[\)]{1}[0-9]{3}([\.]|[\-]){1}[0-9]{4}(([\ ]?(x|ext|extension)?)([\ ]?[0-9]{3,4}))?)|([1]{1}[\(]{1}[0-9]{3}[\)]{1}[0-9]{3}([\-]){1}[0-9]{4})|([\+]{1}[1]{1}[\ ]{1}[0-9]{3}[\.]{1}[0-9]{3}[\-]{1}[0-9]{4})|([\+]{1}[1]{1}[\ ]?[\(]{1}[0-9]{3}[\)]{1}[0-9]{3}[\-]{1}[0-9]{4}))$/gm';
// $pattern = '/\s*(?:\+?(\d{1,3}))?[-. (]*(\d{3})[-. )]*(\d{3})[-. ]*(\d{4})(?: *x(\d+))?\s*/';
// $pattern = '/((\+\d{1,2}|1)[\s.-]?)?\(?[2-9](?!11)\d{2}\)?[\s.-]?\d{3}[\s.-]?\d{4}$/i';

// Does not work on test file.
// $pattern = '/^(([\+]{1}[0-9]{1,3}[\ ]{1}[0-9]{1,2}[\ ]{1}[0-9]{4}[\ ]{1}[0-9]{4})|([0]{1}[0-9]{1}[\ ]{1}[0-9]{4}[\ ]{1}[0-9]{4})|([0]{1}[0-9]{1}[\-]{1}[0-9]{4}[\-]{1}[0-9]{4})|([\(]{1}[0]{1}[0-9]{1}[\)]{1}[\ ]{1}[0-9]{4}([\ ]|[\-]){1}[0-9]{4})|([0-9]{4}([\ ]|[\-])?[0-9]{4})|([0]{1}[0-9]{3}[\ ]{1}[0-9]{3}[\ ]{1}[0-9]{3})|([0]{1}[0-9]{9})|([\(]{1}[0-9]{3}[\)]{1}[\ ]{1}[0-9]{3}[\-]{1}[0-9]{4})|([0-9]{3}([\/]|[\-]){1}[0-9]{3}[\-]{1}[0-9]{4})|([1]{1}[\-]?[0-9]{3}([\/]|[\-]){1}[0-9]{3}[\-]{1}[0-9]{4})|([1]{1}[0-9]{9}[0-9]?)|([0-9]{3}[\.]{1}[0-9]{3}[\.]{1}[0-9]{4})|([\(]{1}[0-9]{3}[\)]{1}[0-9]{3}([\.]|[\-]){1}[0-9]{4}(([\ ]?(x|ext|extension)?)([\ ]?[0-9]{3,4}))?)|([1]{1}[\(]{1}[0-9]{3}[\)]{1}[0-9]{3}([\-]){1}[0-9]{4})|([\+]{1}[1]{1}[\ ]{1}[0-9]{3}[\.]{1}[0-9]{3}[\-]{1}[0-9]{4})|([\+]{1}[1]{1}[\ ]?[\(]{1}[0-9]{3}[\)]{1}[0-9]{3}[\-]{1}[0-9]{4}))$/';
// $pattern = '/(\d[\+\-]){11}/';

        $pattern = '/(?:(?:\+?([1-9]|[0-9][0-9]|[0-9][0-9][0-9])\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([0-9][1-9]|[0-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?/';

//$pattern = '/^(?=\d[\d ,+-]\d$)(?!.[ ,+-]{2})(?=(?:\D*\d){10}\D*$)/';
        preg_match_all($pattern, $text, $match);

//$pattern = '/(\d\[+- ]){11}/';
//$pattern = '/^(?=\d[\d ,+-]\d$)(?!.[ ,+-]{2})(?=(?:\D*\d){11}\D*$)/';
//        preg_match_all($pattern, $text, $match);



        if (!isset($telephone_numbers)) {
            $telephone_numbers = [];
        }
        $telephone_numbers = array_merge($telephone_numbers, $match[0]);
        $telephone_numbers = array_unique($telephone_numbers);
        // Deal with spaces
        //$telephone_numbers = $this->filterTelephonenumbers($telephone_numbers);

        // TODO: Test.
        foreach ($telephone_numbers as $i=>$telephone_number) {

            if (stripos($telephone_number, "&lt;") !== false) {

                $tokens = explode("&lt;", $telephone_number);
                $telephone_numbers[$i] = rtrim($tokens[1],"&gt");
            }

        }
        return $telephone_numbers;
    }

    public function hasTelphonenumber($text = null)
    {
        $telephone_numbers = $this->extractTelephonenumbers($text);

        // No TELEPHONE NUMBERS found.
        if ($telephone_numbers === true) {
            return false;
        }

        if (count($telephone_numbers) >= 1) {
            return true;
        }

        return false;
    }

    public function extractTelephonenumber($text = null)
    {
        $telephone_numbers = $this->extractTelephonenumbers($text);

        // No TELEPHONE NUMBERS found.
        if ($telephone_numbers === true) {
            return false;
        }
        if (count($telephone_numbers) == 1) {
            return $telephone_numbers[0];
        }

        return false;
    }

    public function stripTelephonenumbers($text = null, $replace_text = null)
    {
        if ($text == null) {
            $text = $this->input;
        }
        if ($replace_text == null) {
            $replace_text = "";
        }

        $telephone_numbers = $this->extractTelephonenumbers($text);

        foreach ($telephone_numbers as $i => $telephone_number) {
            $text = str_replace($telephone_number, $replace_text, $text);
        }
        return $text;
    }

    public function readSubject()
    {
        $this->response = null;
        $this->num_hits = 0;

        $input = $this->input;

        $string = $input;
        $str_pattern = 'telephonenumber';
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
        $quote_agent = new Quote($this->thing, "quote");
        $filtered_input = $quote_agent->stripQuotes($filtered_input);

        $input_input = trim($filtered_input);

        if ($input == '') {
            $this->getTelephonenumber();
            return;
        }

        // Get telephone numbers from string
        $this->telephone_numbers = $this->extractTelephonenumbers($input_input);
        $this->telephone_number = "X";

        if (isset($this->telephone_numbers[0])) {
            $this->telephone_number = $this->telephone_numbers[0];
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'telephonenumber') {
                $this->getTelephonenumber();
                return;
            }

            if ($input == 'read') {
                return;
            }
        }

        return;
    }
}
