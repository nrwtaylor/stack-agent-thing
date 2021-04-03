<?php
/**
 * Alpha.php
 *
 * @package default
 */

// 4 letters.  Is handy to have.
namespace Nrwtaylor\StackAgentThing;

// Transparency
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Alpha extends Agent
{
    public function isAlpha($token)
    {
        if (ctype_alpha($token)) {
            return true;
        }
        return false;
    }

    /**
     *
     */
    function init()
    {
        $this->node_list = ["alpha" => ["number"]];

        $this->aliases = ["learning" => ["good job"]];
        $this->recognize_french = true; // Flag error
    }

    /**
     *
     */
    function get()
    {
    }

    /**
     *
     */
    function set()
    {
        $time_string = $this->thing->json->time();
        $this->thing->json->writeVariable(
            ["alpha", "refreshed_at"],
            $time_string
        );
/*
        $this->thing->json->setField("settings");
        $this->thing->json->writeVariable(
            ["alpha", "received_at"],
            $this->thing->json->time()
        );
*/
    }

    public function trimAlpha($text)
    {
        $letters = [];
        $new_text = "";
        $flag = false;
        foreach (range(0, mb_strlen($text)) as $i) {
            $letter = substr($text, $i, 1);

            if (ctype_alnum($letter)) {
                $flag = true;
            }

            if (!ctype_alnum($letter) and $flag == false) {
                $letter = "";
            }

            $letters[] = $letter;
        }

        $new_text = "";
        $flag = false;
        foreach (array_reverse($letters) as $i => $letter) {
            if (ctype_alnum($letter)) {
                $flag = true;
            }

            if (!ctype_alnum($letter) and $flag == false) {
                $letter = "";
            }

            $n = count($letters) - $i - 1;

            $letters[$n] = $letter;
        }
        $new_text = implode("", $letters);

        return $new_text;
    }

    function countAlpha($text)
    {
        $characters = str_split($text);
        $count = 0;
        $max_count = 0;
        foreach ($characters as $i => $character) {
            if (!ctype_alpha($character)) {
                if ($count > $max_count) {
                    $max_count = $count;
                }
                $count = 0;
            }
            if (ctype_alpha($character)) {
                $count += 1;

                if ($count > $max_count) {
                    $max_count = $count;
                }
            }
        }

        return $max_count;
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractAlphas($input = null)
    {
        if ($input == null) {
            $input = $this->subject;
        }
        // https://www.regular-expressions.info/floatingpoint.html
        // Life goals regex that does this

        if (!isset($this->alphas)) {
            $this->alphas = [];
        }

        $pieces = explode(" ", $input);
        $this->alphas = [];
        foreach ($pieces as $key => $piece) {
            if (ctype_alpha($piece)) {
                $this->alphas[] = $piece;
                continue;
            }

            if (ctype_alpha(str_replace(",", "", $piece))) {
                $this->alphas[] = str_replace(",", "", $piece);
                continue;
            }

            if (ctype_alpha($piece)) {
                $this->alphas[] = $piece;
            }
        }

        return $this->alphas;
    }

    /**
     *
     */
    function extractAlpha()
    {
        $this->alpha = false; // No numbers.
        if (!isset($this->alphas)) {
            $this->extractAlphas();
        }

        if (isset($this->alphas[0])) {
            $this->alpha = $this->alphas[0];
        }
    }

    /**
     *
     */
    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report["info"] = $message_thing->thing_report["info"];
        $this->thing_report["help"] = "This extracts alphas from the datagram.";
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        // If the to line is a UUID, then it needs
        // to be sent a receipt.
        if ($this->agent_input == null) {
            $input = $this->subject;
        }

        if ($this->agent_input == "alpha") {
            $input = $this->subject;
        } else {
            $input = $this->agent_input;
        }

        $this->extractAlphas($input);
        $this->extractAlpha();

        if ($this->alpha == false) {
            $this->get();
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "alpha") {
                $this->getAlpha();
                $this->response = "Last alpha retrieved.";
                return;
            }
        }

        $status = true;

        return $status;
    }

    /**
     *
     */
    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/uuid";

        $this->node_list = ["number" => ["number", "thing"]];

        $web = '<a href="' . $link . '">';
        $web .=
            '<img src= "' .
            $this->web_prefix .
            "thing/" .
            $this->uuid .
            '/uuid.png">';
        $web .= "</a>";

        $web .= "<br>";
        $web .= "<b>" . ucwords($this->agent_name) . " Agent</b><br>";
        $web .= $this->subject . "<br>";

        if (!isset($this->numbers[0])) {
            $web .= "No numbers found<br>";
        } else {
            $web .= "First number is " . $this->alphas[0] . "<br>";
            $web .= "Extracted numbers are:<br>";
        }
        foreach ($this->alphas as $key => $alpha) {
            $web .= $alpha . "<br>";
        }

        if ($this->recognize_french == true) {
            // devstack
        }

        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    /**
     *
     */
    function makeSMS()
    {
        $sms = "ALPHA | ";
        //foreach ($this->numbers as $key=>$number) {
        //    $this->sms_message .= $number . " | ";
        //}
        $sms .= $this->alpha;
        //$this->sms_message .= 'devstack';

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     */
    function makeChoices()
    {
        $this->thing->choice->Create("number", $this->node_list, "number");

        $choices = $this->thing->choice->makeLinks("number");
        $this->thing_report["choices"] = $choices;
        $this->choices = $choices;
    }

    /**
     *
     * @return unknown
     */
    /*
    public function makePNG() {
        $text = "thing:".$this->alphas[0];

        ob_clean();

        ob_start();

        QRcode::png($text, false, QR_ECLEVEL_Q, 4);

        $image = ob_get_contents();
        ob_clean();

        $this->thing_report['png'] = $image;
        return $this->thing_report['png'];
    }
*/
}
