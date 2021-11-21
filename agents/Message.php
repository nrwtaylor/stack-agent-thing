<?php
/**
 * Message.php
 *
 * @package default
 */
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Message extends Agent
{
    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        // First check that the $agent_input is an array

        // If it null, then it is a non-agent calling the Message function.
        // So address that first

        if ($this->agent_input == null) {
            $this->thing_report = false;
        }

        if (is_array($this->agent_input)) {
            $this->thing_report = $this->agent_input;
            $this->setMessages();
        }

        $this->previous_agent = $this->get_calling_class();
        // Given a "thing".  Instantiate a class to identify and create the
        // most appropriate agent to respond to it.
        //$this->thing = $thing;

        //      $this->thing_report['thing'] = $this->thing->thing;
        $this->thing_report["info"] = "No info available.";

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $this->thing->container["stack"]["web_prefix"];
        $this->stack_state = $this->thing->container["stack"]["state"];
        $this->short_name = $this->thing->container["stack"]["short_name"];
        $this->mail_postfix = $this->thing->container["stack"]["mail_postfix"];
        $this->word = $this->thing->container["stack"]["word"];
        $this->email = $this->thing->container["stack"]["email"];
        $this->stack_email = $this->email;

        $this->default_message_log = null;
        if (
            isset(
                $this->thing->container["api"]["message"]["default_message_log"]
            )
        ) {
            $this->default_message_log =
                $this->thing->container["api"]["message"][
                    "default_message_log"
                ];
        }

        //        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->node_list = ["start" => ["useful", "useful?"]];

        $this->aliases = ["message" => ["communicate"]];

        // Find out what channel this is
        $channel = new Channel($this->thing, "channel");
        $this->channel_name = $channel->channel_name;

        $timestamp = new Timestamp($this->thing, "timestamp");
    }

    /**
     *
     * @return unknown
     */
    function get_calling_class()
    {
        //get the trace
        $trace = debug_backtrace();

        // Get the class that is asking for who awoke it
        if (!isset($trace[1]["class"])) {
            return true;
        }

        // Adjust code to get class name.
        // After update of Message to extend Agent.
        if (isset($trace[3]["class"])) {
            $class_name = $trace[3]["class"];
            return $class_name;
        }

        // Default if can not parse.
        return "help";

        /*
        $class_name = $trace[1]['class'];
        // +1 to i cos we have to account for calling this function
        for ($i = 1; $i < count($trace); $i++) {
            if (isset($trace[$i])) {
                if (
                    isset($trace[$i]['class']) and
                    $class_name != $trace[$i]['class']
                ) {
                    // is it set?
                    // is it a different class
                    return $trace[$i]['class'];
                }
            }
        }
*/
    }

    public function run()
    {
        $this->respondMessage();
    }

    public function make()
    {
    }

    /**
     *
     */
    function get()
    {
        $outcome = $this->thing->Read(["message", "outcome"]);

        if ($outcome != false) {
            $this->do_not_send = true;
        }
    }

    /**
     *
     * @return unknown
     */
    function quotaMessage()
    {
        $this->quota = new Quota($this->thing, "quota");
        $this->quota_flag = $this->quota->flag;

        if ($this->quota_flag == "red") {
            $this->thing_report["info"] =
                'Agent "Message" daily message quota exceeded. ' .
                $this->quota->counter_daily .
                " of " .
                $this->quota->quota_daily .
                " messages sent.";
            return $this->thing_report;
        }
    }

    /**
     *
     */
    function tallyMessage()
    {
        $command = "tally 10000 message tally" . $this->mail_postfix;
        $tally_thing = new Tally($this->thing, $command);

        // Tally message counts up successfully sent messages.
        // So this is a good place to check if the same message has been
        // sent 3 times.
    }

    /**
     *
     */
    function setMessages()
    {
        // 'message' must be set always.  If not fall back to sms_message
        if (!isset($this->thing_report["message"])) {
            $this->message = "Message not set";
            if (isset($this->thing_report["sms"])) {
                $this->message = $this->thing_report["sms"];
                //  $this->message = "test";
            } else {
                $this->message = "Message not set and no sms message available";
            }
        } else {
            if (
                $this->thing_report["message"] == null or
                empty($this->thing_report["message"])
            ) {
                $this->message = "Blank message received";
            } else {
                $this->message = $this->thing_report["message"];
                //$this->message = "Message text received: [" . $this->message . "]";
            }
        }

        if (!isset($this->thing_report["thing"])) {
            $this->from = null;
            $this->to = null;
        } else {
            //   $this->from = $this->thing_report['thing']->nom_from;
            //   $this->to = $this->thing_report['thing']->nom_to;
        }

        // As must 'thing'
        foreach ($this->thing_report as $key => $value) {
            switch ($key) {
                case "keyword":
                    $this->keyword = $this->thing_report["keyword"];
                    continue 2;
                case "sms":
                    $this->sms_message = $this->thing_report["sms"];
                    continue 2;
                case "choices":
                    $this->choices = $this->thing_report["choices"];
                    continue 2;

                /*
                                case 'message':
					if ( !isset($this->thing_report['message']) ) {
                                        	$this->message = $this->sms_message;
					} else {
					        $this->message = $this->thing_report['message'];
					}
*/
                case "email":
                    //     if (isset($this->thing_report['email']) ) {
                    //      $this->message = $this->thing_report['message'];
                    $this->email = $this->thing_report["email"];

                    //     }
                    //break;
                    continue 2;
                case "web":
                    //$this->thing->log('<pre> Agent "Message" started running on Thing ' . date("Y-m-d H:i:s") . '</pre>');

                    //$this->thing->log( "web channel sent to message - no action" );
                    //break;
                    continue 2;
                default:
                    //
                    continue 2;
            }
        }
    }

    /**
     *
     * @return unknown
     */
    function isOpen()
    {
        // See if the channel is open.
        $u = new Usermanager($this->thing, "usermanager");
        if (
            $u->state == "opt-in" or
            $u->state == "start" or
            $u->state == "new user"
        ) {
            $this->messaging = "on";
        } else {
            $this->messaging = "off";
        }

        return $this->messaging;
    }

    /**
     *
     * @param unknown $searchfor
     * @return unknown
     */
    function facebookMessage($searchfor)
    {
        if (!is_numeric($searchfor)) {
            return false;
        }

        // Check address against the beta list
        $file = $this->resource_path . "facebook/fbid.txt";
        $contents = @file_get_contents($file);
        if ($contents == false) {
            return;
        }

        $pattern = "|\b($searchfor)\b|";

        // search, and store all matching occurences in $matches

        if (preg_match_all($pattern, $contents, $matches)) {
            $m = $matches[0][0];
            return $m;
        } else {
            return false;
        }

        return;
    }

    public function discordMessage($text = null)
    {
        // "someone:#general@someplace.discord"
        //return false;
        // https://api.slack.com/changelog/2016-08-11-user-id-format->
        // Don't make assumptions about characters in slack id.
        if ($this->channel_name == "discord") {
            return true;
        }

        $parts = explode("@", $text);
        $place = end($parts);

        $parts = explode(".", $place);
        $service = end($parts);

        if (strtolower($service) == "discord") {
            return true;
        }

        return false; // in dev
    }

    /**
     *
     * @param unknown $searchfor (optional)
     * @return unknown
     */
    function microsoftMessage($searchfor = null)
    {
        //return false;
        // https://api.slack.com/changelog/2016-08-11-user-id-format-changes
        // Don't make assumptions about characters in slack id.
        if ($this->channel_name == "microsoft") {
            return true;
        }
        return false; // in dev
    }

    public function set()
    {
        $this->thing->Write(
            ["message", "outcome"],
            $this->thing_report["info"]
        );

        // Test
        if ($this->default_message_log != null) {
            $file = $this->default_message_log;
            $text = $this->thing_report["info"] . "\n";
            file_put_contents($file, $text, FILE_APPEND | LOCK_EX);
        }
    }

    public function smsMessage($from)
    {
        if (is_numeric($from) and isset($this->sms_message)) {
            return true;
        }
        return false;
    }

    public function emailMessage($from)
    {
        if (
            filter_var($from, FILTER_VALIDATE_EMAIL) and isset($this->message)
        ) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param unknown $searchfor
     * @return unknown
     */
    function slackMessage($searchfor)
    {
        //return false;
        // https://api.slack.com/changelog/2016-08-11-user-id-format-changes
        // Don't make assumptions about characters in slack id.
        if ($this->channel_name == "slack") {
            return true;
        }
        return false; // in dev
        // Check address against the beta list

        $file = $this->resource_path . "slack/id.txt";
        $contents = file_get_contents($file);

        $pattern = "|\b($searchfor)\b|";

        // search, and store all matching occurences in $matches

        if (preg_match_all($pattern, $contents, $matches)) {
            $m = $matches[0][0];
            return $m;
        } else {
            return false;
        }
    }

    public function uuidMessage()
    {
        $token_thing = new Tokenlimiter($this->thing, "uuid");

        $dev_overide = false;
        if (
            $token_thing->thing_report["token"] == "uuid" or
            $dev_overide == true
        ) {
        } else {
            $this->response .= "No uuid token found. ";
            return;
        }

        $uuid_agent = new Uuid($this->thing, "uuid");
        $uuids = $uuid_agent->extractUuids($this->sms_message);

        foreach ($uuids as $i => $uuid) {
            $this->sms_message = str_replace(
                $uuid,
                "private",
                $this->sms_message
            );
        }

        $this->thing_report["sms"] = $this->sms_message;
    }

    public function respond()
    {
    }

    /**
     *
     * @return unknown
     */
    public function respondMessage()
    {
        $this->uuidMessage();

        if (isset($this->do_not_send) and $this->do_not_send == true) {
            $this->thing->log($this->agent_prefix . " do not send.", "WARNING");
            $this->thing_report["info"] =
                'Agent "Message" saw, "Do not send this thing.".';
            return;
        }

        // Thing actions

        $this->thing->Write(["message", "received_at"], $this->thing->time());

        // Process namespace to return agent name
        $previous_agent_path = explode("\\", $this->previous_agent);
        $previous_agent = $previous_agent_path[count($previous_agent_path) - 1];
        $this->thing->Write(["message", "agent"], $previous_agent);

        $this->thing->flagGreen();

        if ($this->thing_report == false) {
            $this->thing_report["info"] =
                'Agent "Message" did not receive a Thing report';
            return $this->thing_report;
        } else {
        }

        if (
            substr($this->subject, 0, 3) === "s/ " and
            strtolower($this->stack_email) != strtolower($this->email)
        ) {
            $this->thing_report["info"] =
                'Agent "Message" did not send a stack message.';
            return $this->thing_report;
        } else {
        }
        $from = $this->from;
        $to = $this->to;

        // Now passed by Thing object
        $uuid = $this->uuid;
        $sqlresponse = "yes";

        $message = "Thank you $from this was MESSAGE";
        // Recognize and then handle Facebook messenger chat.
        if ($this->facebookMessage($to)) {
            // The FB number of Mordok the Magnificent

            $this->channel_name = "facebook";

            // Cost is handled by sms.php
            // So here we should pull in the token limiter and proof
            // it's capacity to token limit outgoing SMS

            $token_thing = new Tokenlimiter($this->thing, "facebook");

            $dev_overide = null;
            if (
                $token_thing->thing_report["token"] == "facebook" or
                $dev_overide == true
            ) {
                $fb_thing = new Facebook($this->thing, $this->sms_message);

                $thing_report["info"] = $fb_thing->thing_report["info"];

                $this->thing_report["channel"] = "facebook"; // one of sms, email, keyword etc
                $this->thing_report["info"] = $fb_thing->thing_report["info"];

                $this->thing->log($this->thing_report["info"], "INFORMATION");

                $this->tallyMessage();
            } else {
                $this->thing_report["channel"] = "facebook"; // one of sms, email, keyword etc
                $this->thing_report["info"] =
                    "You were sent this link through " .
                    $this->thing_report["channel"] .
                    ".";
            }

            $this->thing->log(
                $this->agent_prefix .
                    ' said, "' .
                    $this->thing_report["info"] .
                    '"',
                "WARNING"
            );

            return $this->thing_report;
        }

        // Recognize and then handle Facebook messenger chat.
        if ($this->microsoftMessage($to)) {
            $token_thing = new Tokenlimiter($this->thing, "microsoft");

            $dev_overide = null;
            if (
                $token_thing->thing_report["token"] == "microsoft" or
                $dev_overide == true
            ) {
                //$this->sendMicrosoft($this->from, "testtest");
                /*
                $microsoft_thing = new Microsoft(
                    $this->thing,
                    $this->sms_message
                );

                $microsoft_thing->sendMicrosoft($this->from, $this->sms_message);
*/
                $this->sendMicrosoft($this->from, $this->sms_message);

                $thing_report["info"] = $microsoft_thing->thing_report["info"];

                $this->thing_report["channel"] = "microsoft"; // one of sms, email, keyword etc
                //$this->thing_report["info"] =
                //    'Agent "Message" sent a Microsoft message.';

                $this->thing->log(
                    "<pre> " . $this->thing_report["info"] . "</pre>",
                    "INFORMATION"
                );

                $this->tallyMessage();
            } else {
                $this->thing_report["channel"] = "microsoft"; // one of sms, email, keyword etc
                $this->thing_report["info"] =
                    "You were sent this link through " .
                    $this->thing_report["channel"];
            }

            $this->thing->log(
                $this->agent_prefix .
                    ' said, "' .
                    $this->thing_report["info"] .
                    '"',
                "WARNING"
            );

            return $this->thing_report;
        }

        if ($this->discordMessage($from)) {
            $this->thing->log("responding via Discord.");

            // Cost is handled by sms.php
            // So here we should pull in the token limiter and proof
            // it's capacity to token limit outgoing SMS

            $token_thing = new Tokenlimiter($this->thing, "discord");

            $this->thing->log(
                "received a " . $token_thing->thing_report["token"] . " Token.",
                "INFORMATION"
            );
            $dev_overide = true;
            if (
                $token_thing->thing_report["token"] == "discord" or
                $dev_overide == true
            ) {
                $this->sendDiscord($this->thing_report["sms"], $from);

                $this->thing_report["channel"] = "discord"; // one of sms, email, keyword etc
                $this->thing_report["info"] =
                    'Agent "Message" sent a Discord message';

                $this->thing->log($this->thing_report["info"], "INFORMATION");

                $this->tallyMessage();
            } else {
                $this->thing_report["channel"] = "discord"; // one of sms, email, keyword etc
                $this->thing_report["info"] =
                    'Agent "Message" did not get a Discord token.';
            }

            $this->thing->log($this->thing_report["info"], "WARNING");

            return $this->thing_report;
        }

        if ($this->slackMessage($to)) {
            // The Slack app of Mordok the Magnificent
            $this->thing->log("responding via Slack.");

            // Cost is handled by sms.php
            // So here we should pull in the token limiter and proof
            // it's capacity to token limit outgoing SMS

            $token_thing = new Tokenlimiter($this->thing, "slack");

            $this->thing->log(
                "received a " . $token_thing->thing_report["token"] . " Token.",
                "INFORMATION"
            );
            $dev_overide = null;
            if (
                $token_thing->thing_report["token"] == "slack" or
                $dev_overide == true
            ) {
                $slack_thing = new Slack($this->thing, $this->thing_report);

                // $slack_thing = new Slack($this->thing, $this->sms_message);

                $thing_report["info"] = $slack_thing->thing_report["info"];

                $this->thing_report["channel"] = "slack"; // one of sms, email, keyword etc
                $this->thing_report["info"] =
                    'Agent "Message" sent a Slack message';

                $this->thing->log($this->thing_report["info"], "INFORMATION");

                $this->tallyMessage();
            } else {
                $this->thing_report["channel"] = "slack"; // one of sms, email, keyword etc
                $this->thing_report["info"] =
                    'Agent "Message" did not get a Slack token.';
            }

            $this->thing->log($this->thing_report["info"], "WARNING");

            return $this->thing_report;
        }

        if ($this->smsMessage($from) === true) {
            //        if ( is_numeric($from) and isset($this->sms_message) ) {
            //if ( is_numeric($from) and isset($this->sms_message) and (mb_strlen($from) <= 10)) {
            $this->thing_report["channel"] = "sms"; // one of sms, email, keyword etc

            // Cost is handled by sms.php

            // Check both a thing token and a stack quota.
            $token_thing = new Tokenlimiter($this->thing, "sms");
            $quota = new Quota($this->thing, "quota");

            //$this->thing->log( $this->agent_prefix . " Token is " . $token_thing->thing_report['token'] . ".");

            //$dev_overide = null; //uncomment to stop sms messaging

            switch (true) {
                case $token_thing->thing_report["token"] != "sms":
                    $this->thing->log("no sms token " . $this->uuid);
                    $this->thing_report["info"] =
                        'Agent "Message" did not get SMS token.';
                    break;

                // Need to review this
                case $quota->counter > 5:
                    $this->thing_report["info"] =
                        'Agent "Message" has exceeded the daily message quota.';
                    break;

                //            case (isset($dev_overide)):
                case true:
                    $sms_thing = new Sms($this->thing, $this->sms_message);

                    //                    $this->thing_report["info"] = 'Agent "Message" sent a SMS.';
                    $this->thing_report["info"] =
                        $sms_thing->thing_report["info"];
                    //if ($sms_thing->error != "") {$this->thing_report['info'] = $sms_thing->error;}

                    //                    $this->thing->log(
                    //                        "<pre> " . $this->thing_report["info"] . "</pre>",
                    //                        "INFORMATION"
                    //                    );

                    $this->tallyMessage();
                    $quota = new Quota($this->thing, "quota use");

                    break;

                default:
            }

            $this->thing->log($this->thing_report["info"], "WARNING");

            return $this->thing_report;
        }

        //  $this->thing_report['message'] = null; // one of sms, email, keyword etc

        // Recognize and respond to email messages,
        // IF there is a formatted email message.

        if ($this->emailMessage($from) === true) {
            //if ( filter_var($from, FILTER_VALIDATE_EMAIL) and isset($this->message) ) {
            $this->thing_report["info"] =
                'Agent "Message" did not send an email message.';

            // So here we should pull in the token limiter and proof
            // it's capacity to token limit outgoing email

            $token_thing = new Tokenlimiter($this->thing, "email");
            $quota = new Quota($this->thing, "quota");
            $this->thing->log(
                'Agent "Message" received a ' .
                    $token_thing->thing_report["token"] .
                    " Token.",
                "INFORMATION"
            );
            // $makeemail_agent = new makeEmail($this->thing, $this->thing_report);
            $makeemail_agent = new Makeemail($this->thing, $this->thing_report); // prod fix

            $this->thing_report["email"] = $makeemail_agent->email_message;

            switch (true) {
                case strpos($this->from, $this->mail_postfix) !== false:
                    $this->thing_report["info"] =
                        "did not send an Email to an internal address.";
                    break;

                case $token_thing->thing_report["token"] != "email":
                    $this->thing_report["info"] = "did not get Email token.";
                    break;

                case $quota->flag == "red" and
                    strtolower($this->email) != strtolower($this->stack_email):
                    $this->thing_report["info"] =
                        "Has exceeded the daily message quota.";
                    break;
                //            case (isset($dev_overide)):
                case true:
                    //                if($quota->counter >= $quota->period_limit) {
                    //                    $this->sms_message = "!dailymessagequota";
                    //                }

                    $email_agent = new Email($this->thing, $this->thing_report);

                    $info = "No agent info available.";
                    if (isset($email_agent->thing_report["info"])) {
                        $info = $email_agent->thing_report["info"];
                    }

                    $this->thing_report["info"] = $info;
                    $this->thing->log(
                        $this->thing_report["info"],
                        "INFORMATION"
                    );

                    $this->tallyMessage();
                    $quota = new Quota($this->thing, "quota use");

                    break;

                default:
            }

            $this->thing->log($this->thing_report["info"], "WARNING");

            return $this->thing_report;
        }

        if (!isset($this->thing_report["info"])) {
            $this->thing_report["info"] =
                'Agent "Message" did not accept the message.';
        }

        return $this->thing_report;
    }

    public function read($text = null)
    {
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        //$status = true;
        //return $status;
    }
}
