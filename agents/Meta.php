<?php
namespace Nrwtaylor\StackAgentThing;

class Meta extends Agent
{
    function init()
    {
        $this->uuid = $this->thing->uuid;

        if (!isset($this->thing->to)) {
            $this->to = null;
        } else {
            $this->to = $this->thing->to;
        }
        if (!isset($this->thing->from)) {
            $this->from = null;
        } else {
            $this->from = $this->thing->from;
        }
        if (!isset($this->thing->subject)) {
            $this->subject = $this->agent_input;
        } else {
            $this->subject = $this->thing->subject;
        }

        $this->keywords = [];

        $this->reading = null;
    }

    function set()
    {
    }

    function get()
    {
        $time_string = $this->thing->Read([
            "meta",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["meta", "refreshed_at"],
                $time_string
            );
        }
        /*
        // If it has already been processed ...
        $this->reading = $this->thing->Read(array(
            "meta",
            "reading"
        ));
*/
    }

    function getMeta($thing = null)
    {
        if ($thing == null) {
            if (!isset($this->thing)) {
                $thing->to = null;
                $thing->from = null;
                $thing->subject = null;
            } else {
                $thing = $this->thing;
            }
        }
/*
        if (!isset($thing->to)) {
            $this->to = null;
        } else {
            $this->to = $thing->to;
        }
        if (!isset($thing->from)) {
            $this->from = null;
        } else {
            $this->from = $thing->from;
        }
        if (!isset($thing->subject)) {
            $this->subject = null;
        } else {
            $this->subject = $thing->subject;
        }
*/

        // Non-nominal
        $this->uuid = $thing->uuid;

        if (isset($thing->to)) {
            $this->to = $thing->to;
        }

        // Potentially nominal
        if (isset($thing->subject)) {
            $this->subject = $thing->subject;
        }

        // Treat as nomina
        if (isset($thing->from)) {
            $this->from = $thing->from;
        }
        // Treat as nomina
        if (isset($thing->created_at)) {
            $this->created_at = $thing->created_at;
        }

        if (isset($this->thing->thing->created_at)) {
            $this->created_at = strtotime($this->thing->thing->created_at);
        }
        if (!isset($this->to)) {
            $this->to = "null";
        }
        if (!isset($this->from)) {
            $this->from = "null";
        }
        if (!isset($this->subject)) {
            $this->subject = "null";
        }
        //if (!isset($this->created_at)) {$this->created_at = date('Y-m-d H:i:s');}
        if (!isset($this->created_at)) {
            $this->created_at = time();
        }

        $data_gram = [
            "from" => $this->from,
            "to" => $this->to,
            "message" => $this->subject,
        ];

        $this->meta = $data_gram;
        $this->meta_string = implode(" ", $data_gram);
    }

    function extractMeta($input = null)
    {
        if ($input == null) {
            if ($this->agent_input == null) {
                $input = $this->agent_input;
            } else {
                $input = $this->subject;
            }
        }

        if ($input == "") {
            $data_gram = ["from" => null, "to" => null, "message" => null];

            $this->meta = $data_gram;
            return;
        }
        /*
        if (!isset($this->words)) {
            $this->getWords($input);
        }
*/
        $sections = ["from", "to", "message"];

        $parse_section = null;
        $message = "";
        $to = "";
        $from = "";

        $this->tokens = explode(" ", $input);

        foreach ($this->tokens as $temp => $word) {
            foreach ($sections as $temp => $section) {
                if ($word == $section) {
                    $parse_section = $word;
                }
            }

            switch ($parse_section) {
                case "message":
                    if (!isset($message_count)) {
                        $message_count = 1;
                    } else {
                        $message_count += 1;
                        $message .= " " . $word;
                    }
                    break;
                //              continue;
                case "to":
                    if (!isset($to_count)) {
                        $to_count = 1;
                    } else {
                        $to_count += 1;
                        $to .= " " . $word;
                    }
                    break;
                //              continue;
                case "from":
                    if (!isset($from_count)) {
                        $from_count = 1;
                    } else {
                        $from_count += 1;
                        $from .= " " . $word;
                    }
                    break;
                //              continue;
            }
        }

        $this->subject = ltrim($message);
        $this->to = ltrim($to);
        $this->from = ltrim($from);
    }

    function getWords($message = null)
    {
        if ($message == null) {
            $message = $this->subject;
        }

        $agent = new Word($this->thing, $message);
        $this->words = $agent->words;
    }

    public function respondResponse()
    {
        if ($this->agent_input != null) {
            return;
        }

        $this->cost = 100;

        // Thing stuff
        $this->thing->flagGreen();

        // Make SMS
        //$this->makeSMS();
        //$this->thing_report['sms'] = $this->sms_message;

        // Make message
        $this->thing_report['message'] = $this->sms_message;

        // Make email
        //$this->makeEmail();

        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        if (isset($this->meta)) {
            $this->thing->Write(
                [$this->agent_name, "meta"],
                $this->meta
            );
        }

        return $this->thing_report;
    }

    function makeSMS()
    {
        if (isset($this->meta)) {
            switch ($this->meta) {
                case true:
                    $this->sms_message = "META | no thing metadata found";
                    break;
                case false:
                    $this->sms_message = "META | no thing metadata";
                    break;
                case null:
                    $this->sms_message = "META | no thing metadata";
                    break;
                default:
                    $this->sms_message = "META | " . $this->meta_string;
                    break;
            }
        } else {
            $this->sms_message = "META | no metadata set";
        }
        return;
    }

    function makeEmail()
    {
        $this->email_message = $this->sms_message;
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);

        $keywords = ['meta', 'metadata'];
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'metadata':
                        case 'meta':
                            $prefix = $piece;
                            if (!isset($prefix)) {
                                $prefix = 'meta';
                            }
                            $words = preg_replace(
                                '/^' . preg_quote($prefix, '/') . '/',
                                '',
                                $input
                            );
                            $words = ltrim($words);

                            $this->extractMeta($words);

                            return;

                        default:
                    }
                }
            }
        }

        $this->extractMeta();
        $status = true;
    }

    function contextWord()
    {
        $this->word_context = '';
        return $this->word_context;
    }
}
