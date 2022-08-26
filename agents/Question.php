<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Question extends Agent
{
    public $var = "hello";

    function init()
    {
        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->test = true;
        }

        $this->email = $this->thing->container["stack"]["email"];

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $this->thing->container["stack"]["web_prefix"];
        $this->mail_postfix = $this->thing->container["stack"]["mail_postfix"];
        $this->word = $this->thing->container["stack"]["word"];
        $this->email = $this->thing->container["stack"]["email"];
        $this->nominal = $this->thing->container["stack"]["nominal"];
        $this->mail_regulatory =
            $this->thing->container["stack"]["mail_regulatory"];

        $this->entity_name = $this->thing->container["stack"]["entity_name"];

        // Allow for a new state tree to be introduced here.
        $this->node_list = ["start" => ["transit", "opt-in"]];
    }

    public function question()
    {
        $this->sms_message = "QUESTION | Did not understand the question.";
        $this->response = true;
        return;

        $this->sms_message = "QUESTION | forwarded to a human.";

        $this->message = $this->sms_message;

        $message = 'The question is "' . $this->subject . '"';

        $thing = new Thing(null);

        $to = $this->email;

        $thing->Create($to, $thing->uuid, "s/ question");
        $thing->flagGreen();

        $thing_report["thing"] = $thing;
        $thing_report["message"] = $message;
        $thing_report["sms"] = $message;
        $thing_report["email"] = $message;

        $message_thing = new Message($thing, $thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        return $this->message;
    }

    public function questionmark()
    {
        $mail_regulatory = str_replace("\r", "", $this->mail_regulatory);
        $mail_regulatory = str_replace("\n", " ", $mail_regulatory);

        $this->sms_message =
            "QUESTION MARK | " .
            ucwords($this->nominal) .
            " | " .
            $this->email .
            " | " .
            $mail_regulatory .
            " | TEXT <question>";

        $this->message = $this->sms_message;

        $message = "A question mark was received.";

        $thing = new Thing(null);

        $thing->Create($this->email, $thing->uuid, "s/ question mark");
        $thing->flagGreen();

        $thing_report["thing"] = $thing;
        $thing_report["message"] = $message;
        $thing_report["sms"] = $message;
        $thing_report["email"] = $message;

        $message_thing = new Message($thing, $thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        return $this->message;
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->makeChoice();

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["email"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
        $this->thing_report["help"] = "Question manager";
    }

    public function makeSMS()
    {
        if (!isset($this->sms_message)) {
            $this->sms_message = "QUESTION | Question not understood.";
        }
        $this->thing_report["sms"] = $this->sms_message;
    }

    public function makeChoice()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "start"
        );
        $choices = $this->thing->choice->makeLinks("start");
        $this->thing_report["choices"] = $choices;
    }

    private function nextWord($phrase)
    {
    }

    public function hasQuestion($text) {

        $pattern = "/\?/";

        if (preg_match($pattern, $text)) {
            // returns true with ? mark
            $this->thing->log(
                "found a question mark and created a Question agent",
                "INFORMATION"
            );
            return true;
        }
        return false;

    }

    public function readSubject()
    {
        $this->response = null;

        $keywords = ["?"];
        $input = strtolower($this->subject);
        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));
        if (count($pieces) == 1) {
            $input = $this->subject;

            if (is_string($this->subject) and strlen($input) == 1) {
                // Test for single ? mark and call question()
                $this->message = "Single question mark received";
                $this->thing->log(
                    $this->agent_prefix . "got a single question mark."
                );
                $this->questionmark();
                return;
            }

            $this->question();
            return true;
        }

        // If there are more than one piece then look at order.
        $this->thing->log($this->agent_prefix . "now checking pieces.");

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "?":
                            $this->thing->log(
                                $this->agent_prefix . "found a question mark."
                            );

                            if ($key + 1 > count($pieces)) {
                                // "Question mark at end
                                $this->question();
                                return;
                            } else {
                                // Question mark was in the string somewhere.
                                // Not so useful right now.
                                return;
                            }
                            break;

                        default:
                        // default
                    }
                }
            }
        }

        // Okay so we arrive at this point not knowing what the message is.
        // Confirm it ends in a ? mark.

        $test = "?";
        // https://stackoverflow.com/questions/619610/whats-the-most-efficient-test-of-whether-a-php-string-ends-with-another-string
        $endsWith = substr_compare($input, $test, -strlen($test)) === 0;
        if ($endsWith) {
            $this->question();
            return;
        }
        // Message not understood
        return false;
    }
}
