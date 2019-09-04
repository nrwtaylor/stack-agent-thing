<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);ini_set('display_errors', 1);

// First off this is called 'make pdf' intentionally to mimic the command structure.
// Touchy much?


// And now the makePng class, exactly like the makePdf
// Let's call give it an N-gram to facilitate command 'make pdf'.
// Also means post-poning tackling what Pdf is actually defined as.
// Which might be exactly how it should be.

// Allowing these core channel processing functions to be handled
// in Composer packages

class makeEmail
{
    public $var = 'hello';

    function __construct(Thing $thing, $input = null)
    {

        $this->input = false;
        $this->choices = false;

        if (isset($input['message'])) {
	        $this->input = $input['message'];
        } else {
            if (!is_array($input)) {
                $this->input = $input;
            }
        }

        if (isset($input['choices'])) {
            $this->choices = $input['choices'];
        }
//var_dump($input);

        // Given a "thing".  Instantiate a class to identify and create the
        // most appropriate agent to respond to it.
        $this->thing = $thing;
        $this->thing_report['thing'] = $thing;

        $this->agent_name = 'makeemail';

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->stack_state = $thing->container['stack']['state'];
        $this->short_name = $thing->container['stack']['short_name'];

        // Create some short-cuts.
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

        $this->web_prefix = $this->thing->container['stack']['web_prefix'];
        $this->mail_prefix = $this->thing->container['stack']['mail_prefix'];
        $this->mail_postfix = $this->thing->container['stack']['mail_postfix'];
        $this->mail_regulatory = $this->thing->container['stack']['mail_regulatory'];

        $this->unsubscribe = "unsub";

        $from =false;

        if (is_array($this->input)) {var_dump($this->input); exit();}

	    $email = $this->generateMultipart($from, $this->input, $this->choices);

        $this->thing_report['email'] = $this->email_message;

	}

	public function generateHTML($raw_message, $choices = null)
    {
		$html_button_set = $choices['button'];
		if ($choices == null) {$html_button_set = "";}



$message = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><META http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>

<div marginwidth="0" marginheight="0" style="background-color:#ffffff;margin:0;padding:0">
<div style="display: none !important;">'. $this->to .' just sent a message to you.</div>
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
                    <a href="' . $this->web_prefix . '"><img style="border:none;" src="https://stackr.ca/stackr.png" width="79" height="28"/></a>
                </td>
                <td class="headerContent Thing" width="324" align="right" style="padding:0;text-align:right;vertical-align:bottom;">
                    <a href="' . $this->web_prefix . 'thing/' . $this->uuid . '/'. $this->to .'" style="color:#719e40;font-family:\'Helvetica Neue\', Arial, sans-serif;font-weight:normal;text-decoration:none; font-size: 12px; line-height:15px;">View this Thing in your browser</a>
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
' . $raw_message . '</div>
</td>
</tr>
</tbody>
</table>
</td>
</tr>



<tr>
    <td valign="top" style=" font-size: 16px; text-align: left; border-top: 1px #dddddd solid;">
' . $choices["button"] . '
</td>
</tr>

                        <tr>
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
                        <a href="https://stackr.ca/"><img width="92" height="30" src="https://stackr.ca/Apple_store.png"/></a> <a href="https://stackr.ca"><img width="92" height="30" src="https://stackr.ca/Google_store.png"/></a>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </td>
</tr>
<tr>
<td height="20">
</td>
</tr>
<tr>
<td valign="top" style="font-size: 16px; text-align: left; border-top: 1px #F5F9FD solid;">

<table border="0" align="center" cellpadding="0" cellspacing="0" style="vertical-align: middle;">
<tr>

<td valign="top" style="padding:18px 18px 18px 18px; font-family:\'Helvetica Neue\', Arial, sans-serif; text-align: left;color:#6f6f6f;font-size:10px; line-height:16px; text-decoration:none; background-color:#efefef">

You received this e-mail because of your participation in
Stackr. In order not to receive anymore notifications from ' . $this->short_name . ' use the following <a href="' . $this->web_prefix . 'thing/' . $this->uuid . '/unsubscribe">link</a>.
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
                        ' . $this->mail_regulatory . '
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

$this->email_message = $message;
	return $message;
	}



	function generateText($raw_message)
    {
        $message = strip_tags($raw_message);
        $message .= strip_tags($this->mail_regulatory);
        $message .= strip_tags($this->unsubscribe);

        return $message;
    }

	function generateMultipart($from, $raw_message, $choices = null)
    {
        // useful in dev - to create the same message received by email.
        $this->generateHTML($raw_message, $choices);

		//create a boundary for the email. This 
		$boundary = uniqid('np');

		//headers - specify your from email address and name here
		//and specify the boundary for the email
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "From: ".$from . "\r\n";
//		$headers .=	"Reply-To: ".$from . "\r\n";
        $headers .= "X-Sender: stackr < admin@stackr.ca >\n";
        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
//		$headers .= "To: ".$this->from."\r\n";
        $headers .= "Content-Type: multipart/mixed;boundary=\"PHP-mixed-" . $boundary . "\"" ."\r\n";

        $message = "--PHP-mixed-" . $boundary . "\r\n";
        $message .= "Content-Type: multipart/alternative;boundary=\"PHP-alt-" . $boundary . "\"" . "\r\n\r\n";

		//here is the content body
		//$message .= "This is a MIME encoded message.";
        $message .= "--PHP-alt-" . $boundary . "\r\n";
        $message .= "Content-type: text/plain;charset=utf-8\r\n";
        $message .= "Content-Transfer-Encoding: quoted-printable\r\n";

		//Plain text body
        $message .= $this->generateText($raw_message) . "\r\n";

		$message .= "--PHP-alt-" . $boundary . "\r\n";
		$message .= "Content-type: text/html;charset=utf-8\r\n";
		$message .= "Content-Transfer-Encoding: quoted-printable\r\n";

		//Html body
		$message .= quoted_printable_encode($this->generateHTML($raw_message, $choices)) . "\r\n";

//		echo $choices['email_html'];

		//$message .= "--PHP-alt-" . $boundary . "\r\n";
		//$attachment = chunk_split(base64_encode(file_get_contents('attachment.zip'))); 
		//$attachment = "Meep";
		//$message .= "--PHP-mixed-" . $boundary; 
		//$message .= 'Content-Type: application/zip; name="attachment.zip"';
		//$message .= "Content-Transfer-Encoding: base64";
		//$message .= 'Content-Disposition: attachment ';
		//$message .= $attachment;

        $message .= "--PHP-mixed-" .$boundary . "--";

        $m = array("message"=>$message,"headers"=>$headers);
        $this->multipart = $m;
        return $m;
    }

	public function sendGeneric($to,$from,$subject,$raw_message, $choices = null)
    {
		$from = $from .$this->mail_postfix;

//https://webdesign.tutsplus.com/articles/build-an-html-email-template-from-scratch--webdesign-12770

		$headers = 'From: '.$from . "\r\n" .
			'Reply-To: '.$from . "\r\n" .
			'X-Thing: '.$this->uuid . "\r\n" .
			 'X-Mailer: PHP/' . phpversion();
		//$headers .= "CC: susan@example.com\r\n";
		//$headers .= "MIME-Version: 1.0\r\n";
		//$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";


		// Code block for attempting to stop auto-underling on iPhone (iOS?)
		//		$message = "<!DOCTYPE html><html><head><style>" . ".appleLinksWhite a {color: #ffffff !important; text-decoration: underline;} 
		//.appleLinksBlack a {color: #000000 !important; text-decoration: none;}" . "</style></head><body>";
		//		$message .= '<pre>';

		// Process the incoming raw message and generate a multi-part message
		// by default.


		$multipart = $this->generateMultipart($from, $raw_message, $choices);

		return $this->mailer($to,$subject,$multipart['message'], $multipart['headers']);
	}




	function contains($str, array $arr)
	{
		foreach($arr as $a) {
		    if (stripos($str,$a) !== false) return true;
		}
		return false;
	}

}

?>
