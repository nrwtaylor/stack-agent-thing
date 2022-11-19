<?php
namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Ajax extends Agent
{
    public $var = 'hello';

    public function run()
    {
        $this->startAjax();
    }

    public function init()
    {
        $this->retain_for = 4; // Retain for at least 4 hours.

        $this->num_hits = 0;

        $this->sqlresponse = null;

        // Allow for a new state tree to be introduced here.
        $this->node_list = ["start" => ["useful", "useful?"]];

        $this->thing_report['info'] = 'Ajax';
        $this->thing_report['help'] =
            "An agent which says, 'Hey'. Type 'Web' on the next line.";
    }

    public function startAjax($type = null)
    {
        $litany = [
            "Meh.",
            "Hhhhhh.",
            "Hi",
            'Received "' . $this->subject . '"',
        ];
        $key = array_rand($litany);
        $value = $litany[$key];

        $this->message = $value;
        $this->sms_message = $value;

        $names = $this->thing->Write(
            ["ajax", "requested_agent"],
            $this->requested_agent
        );

        //if ($time_string == false) {
        $time_string = $this->thing->time();
        $this->thing->Write(
            ["ajax", "refreshed_at"],
            $time_string
        );
        //}

        return $this->message;
    }

    // -----------------------

    public function readAjax($text = null)
    {
        if ($this->agent_input == null) {
            $this->requested_agent = "Ajax";
        } else {
            $this->requested_agent = $text;
        }
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        // Generate email response.

        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "start"
        );
        $choices = $this->thing->choice->makeLinks('start');
        $this->thing_report['choices'] = $choices;

        $this->sms_message = "AJAX | " . $this->sms_message . "";
        $this->thing_report['sms'] = $this->sms_message;

        $this->thing_report['email'] = $this->message;
        $this->thing_report['message'] = $this->message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }
        $this->makeWeb();
    }

    public function makeWeb()
    {
        $this->link = $this->web_prefix . 'thing/' . $this->uuid . "/ajax";
        $ajax_link = 'thing/' . $this->uuid . "/ajax";


        $html = '<script>
function showHint(str) {
    if (str.length == 0) {
        document.getElementById("txtHint").innerHTML = "";
        return;
    } else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHint").innerHTML = this.responseText;
            }
        };
//xmlhttp.open("GET", "gethint.php?q=" + str, true);

xmlhttp.open("GET", $ajax_link . "?q=" + str, true);

        xmlhttp.send();
    }
}
</script>';
        //         xmlhttp.open("GET", "gethint.php?q=" + str, true);

        //         xmlhttp.open("GET", 'thing/' . $this->uuid . '/ajax', true);

        $html .= "<b>AJAX</b>";
        $html .= '<p><b>Start typing a name in the input field below:</b></p>
<form>
First name: <input type="text" onkeyup="showHint(this.value)">
</form>
<p>Suggestions: <span id="txtHint"></span></p>';

        $this->thing_report['web'] = $html;
    }

    public function readSubject()
    {
        $this->readAjax();

        $this->response .= "devstack read ajax";
    }
}
