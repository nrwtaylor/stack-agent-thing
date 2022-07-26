<?php
namespace Nrwtaylor\StackAgentThing;

/**
 * Email.php
 *
 * @package default
 */

use ZBateson\MailMimeParser\MailMimeParser;
use ZBateson\MailMimeParser\Message;
use ZBateson\MailMimeParser\Header\HeaderConsts;

// This is the only agent allowed to send emails.
// Need to hunt out legacy code for sendGeneric is other agents.

class Email
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $input (optional)
     */
    function __construct(Thing $thing, $input = null)
    {
        //$this->start_time = microtime(true);
        $this->start_time = $thing->elapsed_runtime();

        /*

        if ($input == null) {
            $this->message = "No message provided";
        } else {
            $this->message = $input;
        }
*/

        $this->makeMessage($input);
        //        $this->input = $input;
        $this->cost = 50;

        $this->test = "Development code";

        $this->thing = $thing;

        //$this->start_time = microtime(true);

        $this->agent_name = "email";
        $this->agent_prefix = 'Agent "Email"';

        $this->thing_report["thing"] = $thing;
        //        $this->thing_report['thing'] = $this->thing->thing;

        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->test = true;
        }

        $this->web_prefix = $this->thing->container["stack"]["web_prefix"];
        $this->mail_prefix = $this->thing->container["stack"]["mail_prefix"];
        $this->mail_postfix = $this->thing->container["stack"]["mail_postfix"];
        $this->mail_regulatory =
            $this->thing->container["stack"]["mail_regulatory"];

        $this->robot_name = $this->thing->container["stack"]["robot_name"];

        $this->short_name = $this->thing->container["stack"]["short_name"];

        $this->word = $thing->container["stack"]["word"];
        $this->email = $thing->container["stack"]["email"];
        $this->stack_email = $this->email;
        $this->resource_path = $GLOBALS["stack_path"] . "resources/";

        if (isset($this->thing->container["api"]["wordpress"]["path_to"])) {
            $this->wordpress_path_to =
                $this->thing->container["api"]["wordpress"]["path_to"];
            require_once $this->wordpress_path_to . "wp-load.php";
        }

        // Sent uo agent.
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

        // This probably isn't needed.
        // But keep it here for if we want to add override type choices.
        $this->node_list = ["email" => ["email"]];

        // Borrow this from iching
        $time_string = $this->thing->Read(["email", "refreshed_at"]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(["email", "refreshed_at"], $time_string);
        }

        $this->email_count = $this->thing->Read(["email", "count"]);

        if ($this->email_count == false) {
            $this->email_count = 0;
        }

        $this->email_per_message_responses = 1;
        $this->email_horizon = 2 * 60; //s

        $this->thing->log(
            'Agent "Email" running on Thing ' . $this->thing->nuuid . ".",
            "INFORMATION"
        );
        $this->thing->log(
            'Agent "Email" received this Thing "' . $this->subject . '".',
            "INFORMATION"
        );

        if ($this->readSubject() == true) {
            // aka 'something terrible happened when reading the to and subject line.
            $this->thing_report = [
                "thing" => $this->thing->thing,
                "choices" => false,
                "info" => "An email address wasn't provided.",
                "help" => "from needs to be a valid email address.",
            ];

            $this->thing->log(
                'Agent "Email" completed without sending an email.',
                "INFORMATION"
            );
            return;
        }

        $this->respond();

        //$this->thing->log ( 'Agent "Email" completed.' );
        $this->thing->log(
            $this->agent_prefix .
                "ran for " .
                number_format($this->thing->elapsed_runtime()) .
                "ms."
        );

        $this->thing_report["etime"] = number_format(
            $this->thing->elapsed_runtime()
        );
        $this->thing_report["log"] = $this->thing->log;

        return;
    }

    public function isEmail($text)
    {
        $meta = $this->metaEmail($text);
        $is_email = true;
        if (!isset($meta["from"])) {
            $is_email = false;
        }

        if (!isset($meta["subject"])) {
            $is_email = false;
        }

        return $is_email;
    }

    public function metaEmail($text)
    {
        // Pull the message in again.
        $parser = new MailMimeParser();

        // parse() returns a Message
        $message = $parser->parse($text);

        $from = $message->getHeaderValue("From");

        $subject = $message->getHeaderValue("Subject");
        $sent = $message->getHeaderValue("Sent");
        $received = $message->getHeaderValue("Received");
        $date = $message->getHeaderValue("Date");

        $meta = [
            "from" => $from,
            "subject" => $subject,
            "sent" => $sent,
            "received" => $received,
            "date" => $date,
        ];

        return $meta;
    }

    public function attachmentsEmail($text)
    {
        // Pull the message in again.
        $parser = new MailMimeParser();

        // parse() returns a Message
        $message = $parser->parse($text);

        $subject = $message->getHeaderValue("Subject");

        $parts = $message->getAllParts();
        foreach ($parts as $i => $part) {
            $content_type = $part->getHeaderValue(HeaderConsts::CONTENT_TYPE); // e.g. "text/plain"
            /*
echo $part->getHeaderParameter(                         // value of "charset" part
    'content-type',
    'charset'
);
*/
            $content = $part->getContent();

            if (!isset($this->parts)) {
                $this->parts = [];
            }
            $this->parts[] = [
                "content_type" => $content_type,
                "content" => $content,
            ];
        }

        return $this->parts;

        // Example from docs. But getParts looks more generic.
        // test

        //        $message = Message::from($text);

        $att = $message->getAttachmentPart(0);
        echo $att->getContentType();
        echo $att->getContent();

        $atts = $message->getAllAttachmentParts();
        foreach ($atts as $ind => $part) {
            $filename = $part->getHeaderParameter(
                "Content-Type",
                "name",
                $part->getHeaderParameter(
                    "Content-Disposition",
                    "filename",
                    "__unknown_file_name_" . $ind
                )
            );

            //    $out = fopen('/path/to/dir/' . $filename, 'w');
            $str = $part->getBinaryContentResourceHandle();

            //    stream_copy_to_stream($str, $out);
            //    fclose($str);
            //    fclose($out);
        }
    }

    public function readEmail($text = null)
    {
        // https://github.com/zbateson/mail-mime-parser

        // test
        //$text = str_replace('Content-Type: multipart/alternative',
        //'Content-Type: multipart/mixed',$text);

        $message = Message::from($text);

        $subject = $message->getHeaderValue("Subject");

        $fromName = null;
        $fromEmail = null;

        $from = $message->getHeader("From");
        if ($from !== null) {
            $fromName = $from->getName();
            $fromEmail = $from->getEmail();
        }

        $toName = null;

        $to = $message->getHeader("To");
        $toEmails = [];
        if ($to !== null) {
            foreach ($to->getAddresses() as $addr) {
                $toName = $to->getName();
                $toEmails[] = $to->getEmail();
            }
        }
        //$to = null;

        //echo $message
        //    ->getHeader(HeaderConsts::CC)                      // also AddressHeader
        //    ->getAddresses()[0]                                // AddressPart
        //    ->getEmail();                                      // user@example.com
        $email_text = $message->getTextContent();
        $email_html = $message->getHtmlContent();

        // Strip tags
        //        $email_html_text = strip_tags($email_html);

        // https://stackoverflow.com/questions/12824899/strip-tags-replace-tags-by-space-rather-than-deleting-them
        $string = $email_html;
        $spaceString = str_replace("<", " <", $string);
        $doubleSpace = strip_tags($spaceString);
        $singleSpace = str_replace("  ", " ", $doubleSpace);
        $email_html_text = $singleSpace;

        //$body = $email_html;
        //if ($email_html === null) {$body = $email_text;}

        $body = $email_text . "\n" . $email_html_text;

        // ZBateson library can sometimes come back null.
        // With multipart.
        // https://github.com/zbateson/mail-mime-parser/issues/29
        // Test for this and use text as body if so.

        if ($email_text == null and $email_html == null) {
            $html_handler = new Html($this->thing, "html");
            //    $body = $html_handler->textHtml($text);
            $body = $this->bodyEmail($text);
        }

        $toEmail = null;
        if (isset($toEmails[0])) {
            $toEmail = $toEmails[0];
        }

        $datagram = [
            "to" => $toEmail,
            "from" => $from,
            "subject" => $subject,
            "text" => $body,
        ];

        $this->attachmentsEmail($text);

        return $datagram;
    }

    function bodyEmail($text)
    {
        [$to, $from, $subject, $message] = $this->parseEmail($text);
        return $message;
    }

    // Basic parser.
    // https://stackoverflow.com/questions/12896/parsing-raw-email-in-php
    function parseEmail($text)
    {
        // handle email
        $lines = explode("\n", $text);

        // empty vars
        $to = "";
        $from = "";
        $subject = "";
        $headers = "";
        $message = "";
        $splittingheaders = true;
        for ($i = 0; $i < count($lines); $i++) {
            if ($splittingheaders) {
                // this is a header
                $headers .= $lines[$i] . "\n";

                // look out for special headers
                if (preg_match("/^Subject: (.*)/", $lines[$i], $matches)) {
                    $subject = $matches[1];
                }
                if (preg_match("/^From: (.*)/", $lines[$i], $matches)) {
                    $from = $matches[1];
                }
                if (preg_match("/^To: (.*)/", $lines[$i], $matches)) {
                    $to = $matches[1];
                }
            } else {
                // not a header, but message
                $message .= $lines[$i] . "\n";
            }

            if (trim($lines[$i]) == "") {
                // empty line, header section has ended
                $splittingheaders = false;
            }
        }

        return [$to, $from, $subject, $message];
    }

    /**
     *
     */
    private function respond()
    {
        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.
        $to = $this->from;
        $from = $this->to . $this->mail_postfix;

        if ($this->message != null) {
            $test_message = $this->message;
        } else {
            $test_message = $this->subject;
        }

        /////

        if ($this->message == false) {
            $this->thing_report["choices"] = false;
            $this->thing_report["info"] = "No message to send.";
            $this->thing_report["help"] = "False message.";

            // No message to send
            return;
        }

        //        $this->email_message = false;
        $this->makeEmail();
        $received_at = time();

        if ($this->thing->thing === true or $this->thing->thing === false) {
        } else {
            $received_at = strtotime($this->thing->thing->created_at);
        }
        $time_ago = time() - $received_at;

        /////

        $this->thing_report["info"] = 'Agent "Email" did not send an email.';

        if (
            isset($this->thing->account) and
            isset($this->thing->account["stack"])
        ) {
            if (
                $this->thing->account["stack"]->balance["amount"] >= $this->cost
            ) {
                //$this->sendSms($to, $test_message);
                //echo $to;
                //echo "/n";
                //echo $from;
                //echo "/n";

                // dev test
                // Use wordpress email if it is available.
                if (isset($this->wordpress_path_to)) {
                    // get the blog administrator's email address
                    //        $to = get_option('admin_email');

                    $name = "User X";

                    //        $to = get_option('admin_email');
                    //        $subject = "Some text in subject...";
                    //        $subject = $input_text;
                    $subject = $this->subject;
                    $message =
                        '"' .
                        $email .
                        '" wants to be updated when this search is updated.';

                    $headers = "From: $name <$email>" . "\r\n";
                    $post_title = "Merp";

                    wp_mail($to, $this->subject, $this->message, $headers);
                } else {
                    $this->sendGeneric(
                        $to,
                        $from,
                        $this->subject,
                        $this->message,
                        null
                    );
                }

                $this->thing->account["stack"]->Debit($this->cost);

                //                $this->sendUSshortcode($to, $test_message);

                //            $this->thing_report['info'] = 'Agent "Email" sent an Email to ' . $this->from . '.';

                //      } else {
                //          echo '<pre> Agent "Sms" did not send a SMS to ' . $this->from . '.  Not enough stack balance.</pre>';
            } else {
                $this->thing_report["info"] =
                    "Email not sent.  Balance of " .
                    $this->thing->account["stack"]->balance["amount"] .
                    " less than " .
                    $this->cost;
            }
        }
        //$this->thing_report = array('thing' => $this->thing->thing, 'choices' => false, 'info' => 'This is a sms sender.','help' => 'Ants.  Lots of ants.');

        //$this->thing_report['choices'] = false;
        $this->thing_report["help"] =
            "This agent is responsible for sending emails.";
    }

    /**
     *
     */
    function makeEmail()
    {
        if (!isset($this->message)) {
            $this->makeMessage();
        }

        if (!isset($this->choices)) {
            $this->makeChoices();
        }
/*
        if (!isset($this->pdf)) {
            $makepdf_agent = new Makepdf($this->thing, $this->thing_report);
            $pdf = $makepdf_agent->pdf;
            //$this->makePdf();
            $this->thing_report["pdf"] = $pdf;
        }
*/
        //new
        $this->thing_report["choices"] = $this->choices;
        $makeemail_agent = new Makeemail($this->thing, $this->thing_report);

        $this->email_message = $makeemail_agent->email_message;

        //old
        //        $from = $this->from .$this->mail_postfix;
        //        $this->email_message = $this->generateMultipart($this->from, $this->message, $this->choice$

        $this->thing_report["email"] = $this->email_message;
    }

    /**
     *
     */
    function makeChoices()
    {
        if (!isset($this->choices)) {
            $this->choices = false;
        }
        $this->thing_report["choices"] = $this->choices;
    }

    /**
     *
     * @param unknown $input (optional)
     */
    function makeMessage($input = null)
    {
        if ($input == null) {
            $this->message = false;
        }

        if (!isset($input["message"])) {
            if (!is_array($input)) {
                $this->message = $input;
            } else {
                $this->message = "No message provided to email agent.";
            }
        } else {
            $this->message = $input["message"];
        }

        if (isset($input["pdf"])) {
            $this->pdf = $input['pdf'];
        } else {
            $this->pdf = null;
        }

        if (!isset($input["choices"])) {
            if (!is_array($input)) {
                $this->choices = false; //"foo";
            } else {
                $this->choices = false; //"No choices provided to email agent.";
            }
        } else {
            $this->choices = $input["choices"];
        }
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        if (
            filter_var($this->to, FILTER_VALIDATE_EMAIL) and
            isset($this->message)
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
    function checkAddress($searchfor)
    {
        // Check address against the beta list
        $limitedbeta_agent = new Limitedbeta($this->thing, "limitedbeta");

        return $limitedbeta_agent->isLimitedbeta($searchfor);
    }

    /**
     *
     * @param unknown $to
     * @param unknown $subject
     * @param unknown $message
     * @param unknown $headers
     * @return unknown
     */
    public function mailer($to, $subject, $message, $headers)
    {
        $donotsend = null;
        //        $donotsend = true; //NRWTaylor 25 Sep 2017

        if ($this->checkAddress($to) != false) {
            $this->thing->log(
                "found the email address in the limited beta list.",
                "INFORMATION"
            );
        } else {
            $this->thing->log(
                "did not find the email address in the limited beta list.",
                "INFORMATION"
            );
            $donotsend = true;
        }
        //        $subject = $this->mail_prefix . " " . $subject;
        if (
            isset($this->mail_prefix) and
            is_string($this->mail_prefix) and
            $this->mail_prefix != ""
        ) {
            $subject = $this->mail_prefix . " " . $subject;
        }

        if (
            strpos(strtolower($subject), strtolower("Stack record: ")) !== false
        ) {
            $donotsend = true;
        }

        if (
            strpos(
                strtolower($subject),
                strtolower("Stack record: Opt-in verification request ")
            ) !== false
        ) {
            $donotsend = true;
        }
        // Do not send an email to stack domain.
        if (
            strpos(strtolower($this->from), strtolower($this->mail_postfix)) !=
            false
        ) {
            $donotsend = true;
        }
        $email_thing = new Thing(null);
        $email_thing->Create($to, "ant", "s/ record email authorization");
        $email_thing->flagGreen();

        $user_state = $email_thing->getState("usermanager");

        if ($donotsend) {
            return true;
        }

        if ($user_state == "opt-out" or $user_state == "deleted") {
            $email_thing = new Thing(null);
            $email_thing->Create($to, "ant", "s/ opt-out or deleted");
            $email_thing->flagGreen();

            return true;
        }

        if (strpos(strtolower($to), "@winlink.org") !== false) {
            $headers = null;
        }

        $response = @mail($to, $subject, $message, $headers);

        if ($response) {
            $this->thing_report["info"] = "Message was accepted for delivery.";
            $this->thing->log("was accepted for delivery.");
        } else {
            $this->thing_report["info"] =
                "Message was not accepted for delivery.";

            $this->thing->log("was not accepted for delivery.");
        }
        $this->thing->log('said "' . $subject . '".');

        $email_thing = new Thing(null);
        $email_thing->Create($to, "email", "s/ email sent");
        $email_thing->flagGreen();

        return "s/success";
    }

    /**
     *
     * @param unknown $raw_message
     * @param unknown $choices     (optional)
     * @return unknown
     */
    public function generateHTML($raw_message, $choices = null)
    {
        $info =
            '<tr>
    <td valign="top" style=" font-size: 16px; text-align: left; border-top: 1px #dddddd solid;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
            <tr>
                <td valign="middle" style="padding:12px 15px 20px 15px;">
                    <div style="color: #999999; font-family: \'Helvetica Neue\', Arial, sans-serif; font-size: 12px; line-height: 17px; text-align: left">
                        Stackr is not yet available for iOS or Android.
                    </div>
                </td>
                <td width="280">
                    <div style="line-height: 17px; padding: 12px 0 20px 0; text-align: right">
                        <a href="' .
            $this->web_prefix .
            '"><img width="92" height="30" src="' .
            $this->web_prefix .
            'Apple_store.png"/></a> <a href="' .
            $this->web_prefix .
            '"><img width="92" height="30" src="' .
            $this->web_prefix .
            'Google_store.png"/></a>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </td>
</tr>';

        $info = '<tr>
    <td valign="top" style=" font-size: 16px; text-align: left; border-top: 1px #dddddd solid;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
            <tr>
                <td valign="middle" style="padding:12px 15px 20px 15px;">
                    <div style="color: #999999; font-family: \'Helvetica Neue\', Arial, sans-serif; font-size: 12px; line-height: 17px; text-align: left">
                        Stackr is not yet available for iOS or Android.
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
</td>
</tr>
';
        // This is the active code. Not makeemail.
        $info = "";

        $html_button_set = $choices["button"];
        if ($choices == null) {
            $html_button_set = "";
        }

        $message =
            '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><META http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>

<div marginwidth="0" marginheight="0" style="background-color:#ffffff;margin:0;padding:0">
<div style="display: none !important;">' .
            $this->to .
            ' just sent a message to you.</div>
<center>
<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="background-color:#ffffff;
height:100%!important;
margin:0;
padding:0;
width:100%!important">
<tr>
<td align="center" valign="top">
<table border="0" cellpadding="0" cellspacing="0"
width="480" style="background-color:#ffffff">
                        <tr>
                            <td colspan="4" height="28">
				</td>
           </tr>
     <tr>
	    <td>
        <table border="0" cellpadding="0" cellspacing="0" width="480" id="templateHeader" style="background-color:#FFFFFF; border-bottom:0;">
            <tr>
                <td colspan="4" height="28"></td>
            </tr>
            <tr>
                <td class="headerContent" width="15" style="padding:0;text-align:right;vertical-align:bottom;"></td>
                <td class="headerContent logo" width="126" height="28" style="padding:0;text-align:left;vertical-align:bottom;">
                    <a href="' .
            $this->web_prefix .
            '"><img style="border:none;" src="' .
            $this->web_prefix .
            'stackr.png" width="79" height="28"/></a>
                </td>
                <td class="headerContent Thing" width="324" align="right" style="padding:0;text-align:right;vertical-align:bottom;">
                    <a href="' .
            $this->web_prefix .
            "web/" .
            $this->uuid .
            "/" .
            $this->to .
            '" style="color:#719e40;font-family:\'Helvetica Neue\', Arial, sans-serif;font-weight:normal;text-decoration:none; font-size: 12px; line-height:15px;">View this Thing in your browser</a>
                </td>
                <td class="headerContent" width="15" style="color:#202020;font-family:\'Helvetica Neue\', Arial, sans-serif;font-size:34px;font-weight:bold;line-height:15px;padding:0;text-align:right;vertical-align:bottom=
;"></td>
            </tr>
            <tr>
                <td colspan="4" height="12"></td>
            </tr>
        </table>
    </td>
</tr>

                        <tr>
<td valign="top" style="border-top: 1px #e0e7f0 solid; background-color: #f5f9fd; font-size: 16px; text-align: left">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
<td valign="top" style="padding: 20px 15px 0 15px;">
<div style="color: #719e40; font-family: \'Helvetica Neue\', Arial, sans-serif; font-size: 16px; line-height: 25px; text-align: left">
Hi,
</div>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td valign="top" style="background-color: #f5f9fd; font-size: 16px; text-align: left">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
<td valign="top" style="padding-left: 15px; padding-right: 15px;">
<div style="color: #575757; font-family: \'Helvetica Neue\', Arial, sans-serif; font-size: 16px; line-height: 18px; text-align: left">

</div>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
                        <tr>
<td valign="top" style="padding:0 15px 20px 15px; background-color: #f5f9fd; font-size: 16px; text-align: left; ">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>

<tr>
<td valign="top">
<div style="color: #575757; font-family: \'Helvetica Neue\', Arial, sans-serif; font-size: 16px; line-height: 25px; text-align: left">
' .
            $raw_message .
            '</div>
</td>
</tr>
</tbody>
</table>
</td>
</tr>



<tr>
    <td valign="top" style=" font-size: 16px; text-align: left; border-top: 1px #dddddd solid;">
' .
            $choices["button"] .
            '
</td>
</tr>' .
            $info .
            '<tr>
<td height="20">
</td>
</tr>
<tr>
<td valign="top" style="font-size: 16px; text-align: left; border-top: 1px #F5F9FD solid;">

<table border="0" align="center" cellpadding="0" cellspacing="0" style="vertical-align: middle;">
<tr>

<td valign="top" style="padding:18px 18px 18px 18px; font-family:\'Helvetica Neue\', Arial, sans-serif; text-align: left;color:#6f6f6f;font-size:10px; line-height:16px; text-decoration:none; background-color:#efefef">

You received this e-mail because of your participation in
Stackr. In order not to receive anymore notifications from Stackr use the following <a href="' .
            $this->web_prefix .
            "thing/" .
            $this->uuid .
            '/unsubscribe">link</a>.
</td>
</tr>
</table>
</td>
</tr>
                        <tr>
    <td valign="top" style=" font-size: 16px; text-align: left;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
            <tr>
                <td valign="top" style="padding:12px 15px 20px 15px;">
                    <div style="color: #999999; font-family: \'Helvetica Neue\', Arial, sans-serif; font-size: 12px; line-height: 17px; text-align: left">
                        ' .
            $this->mail_regulatory .
            '
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </td>
</tr>
                    </table>
                    <br>
                </td>
            </tr>
        </table>
    </center>
</div>
</body>
</html>';

        return $message;
    }

    /**
     *
     * @param unknown $raw_message
     * @return unknown
     */
    function generateText($raw_message)
    {
        $this->unsubscribe = "unsubscribe";

        $message = strip_tags($raw_message);
        $message .= strip_tags($this->mail_regulatory);
        $message .= strip_tags($this->unsubscribe);
        //$message .= $this->pdf;
        return $message;
    }

    /**
     *
     * @param unknown $from
     * @param unknown $raw_message
     * @param unknown $choices     (optional)
     * @return unknown
     */
    function generateMultipart($from, $raw_message, $choices = null)
    {
        //        $from = $this->robot_name . $this->mail_postfix;
        //   $from = $from . $this->mail_postfix;

        // useful in dev - to create the same message received by email.
        $this->generateHTML($raw_message, $choices);

        //create a boundary for the email. This
        $boundary = uniqid("np");

        //headers - specify your from email address and name here
        //and specify the boundary for the email
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "From: " . $from . "\r\n";
        //  $headers .= "Reply-To: ".$from . "\r\n";

        //$headers .= "X-Sender: ".$this->short_name." < admin" . $this->mail_postfix . " >\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        //  $headers .= "To: ".$this->from."\r\n";
        $headers .=
            "Content-Type: multipart/mixed;boundary=\"PHP-mixed-" .
            $boundary .
            "\"" .
            "\r\n";

        $message = "--PHP-mixed-" . $boundary . "\r\n";
        $message .=
            "Content-Type: multipart/alternative;boundary=\"PHP-alt-" .
            $boundary .
            "\"" .
            "\r\n\r\n";

        //here is the content body
        //$message .= "This is a MIME encoded message.";
        $message .= "--PHP-alt-" . $boundary . "\r\n";
        $message .= "Content-type: text/plain;charset=utf-8\r\n";
        $message .= "Content-Transfer-Encoding: quoted-printable\r\n";

        //Plain text body
        $message .= $this->generateText($raw_message) . "\r\n";

        if (strpos($this->from, "@winlink.org") !== false) {
        } else {
            $message .= "--PHP-alt-" . $boundary . "\r\n";
            $message .= "Content-type: text/html;charset=utf-8\r\n";
            $message .= "Content-Transfer-Encoding: quoted-printable\r\n";

            //Html body
            $message .=
                quoted_printable_encode(
                    $this->generateHTML($raw_message, $choices)
                ) . "\r\n";
        }

        if ((isset($this->pdf)) and ($this->pdf !== null)) {
            // fetch pdf
            $pdfLocation =
                "/var/www/html/stackr.ca/resources/snowflake/bubble.pdf"; // file location
            $pdfName = "pdf-file.pdf"; // pdf file name recipient will get
            $filetype = "application/pdf"; // type

            $file = fopen($pdfLocation, "rb");
            $data = fread($file, filesize($pdfLocation));
            fclose($file);
            $pdf = chunk_split(base64_encode($data));

            // attach pdf to email
            $eol = "\r\n";
            $message .=
                "--PHP-alt-" .
                $boundary .
                "\r\n" .
                "Content-Type: $filetype;$eol" .
                " name=\"$pdfName\"$eol" .
                "Content-Disposition: attachment;$eol" .
                " filename=\"$pdfName\"$eol" .
                "Content-Transfer-Encoding: base64$eol$eol" .
                $pdf .
                $eol .
                "--PHP-alt-" .
                $boundary;
        }
*/
        /*
        if ($this->thing_report['pdf'] !== false) {
            $message .= "--PHP-alt-" . $boundary . "\r\n";
            $message .= "Content-type: text/html;charset=utf-8\r\n";
            $message .= "Content-Transfer-Encoding: quoted-printable\r\n";
$c =       quoted_printable_encode(
                    $this->generateHTML($raw_message, $choices)
                );

            //Html body
            $message .= $c . "\r\n";
        }
*/

        //$message .= "--PHP-alt-" . $boundary . "\r\n";
        //$attachment = chunk_split(base64_encode(file_get_contents('attachment.zip')));
        //$attachment = "Meep";
        //$message .= "--PHP-mixed-" . $boundary;
        //$message .= 'Content-Type: application/zip; name="attachment.zip"';
        //$message .= "Content-Transfer-Encoding: base64";
        //$message .= 'Content-Disposition: attachment ';
        //$message .= $attachment;

        $message .= "--PHP-mixed-" . $boundary . "--";

        $m = ["message" => $message, "headers" => $headers];

        return $m;
    }

    /**
     *
     * @param unknown $to
     * @param unknown $from
     * @param unknown $subject
     * @param unknown $raw_message
     * @param unknown $choices     (optional)
     * @return unknown
     */
    public function sendGeneric(
        $to,
        $from,
        $subject,
        $raw_message,
        $choices = null
    ) {
        //    $from = $from .$this->mail_postfix;
        //      $from = $this->robot_name . $this->mail_postfix;
        //     $from = $from . $this->mail_postfix;

        //https://webdesign.tutsplus.com/articles/build-an-html-email-template-from-scratch--webdesign-12770

        $headers =
            "From: " .
            $from .
            "\r\n" .
            "Reply-To: " .
            $from .
            "\r\n" .
            "X-Thing: " .
            $this->uuid .
            "\r\n" .
            "X-Mailer: PHP/" .
            phpversion();
        //$headers .= "CC: susan@example.com\r\n";
        //$headers .= "MIME-Version: 1.0\r\n";
        //$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        // Code block for attempting to stop auto-underling on iPhone (iOS?)
        //  $message = "<!DOCTYPE html><html><head><style>" . ".appleLinksWhite a {color: #ffffff !important; text-decoration: underline;}
        //.appleLinksBlack a {color: #000000 !important; text-decoration: none;}" . "</style></head><body>";
        //  $message .= '<pre>';

        // Process the incoming raw message and generate a multi-part message
        // by default.

        $multipart = $this->generateMultipart($from, $raw_message, $choices);

        return $this->mailer(
            $to,
            $subject,
            $multipart["message"],
            $multipart["headers"]
        );
    }

    /**
     *
     * @param unknown $str
     * @param array   $arr
     * @return unknown
     */
    function contains($str, array $arr)
    {
        foreach ($arr as $a) {
            if (stripos($str, $a) !== false) {
                return true;
            }
        }
        return false;
    }
}
