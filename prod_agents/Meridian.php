<?php
/**
 * Runat.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Meridian extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->keywords = ["next", "accept", "clear", "drop", "add", "new"];
        $this->test = "Development code";
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractMeridian($input = null)
    {
        if (!isset($this->number)) {
            $this->number = $this->extractNumber($input);
        }

        if (count($this->numbers) == 2) {
            if ($this->numbers[0] <= 12 and $this->numbers[0] >= 1) {
                $this->hour = $this->numbers[0];
            }
            if ($this->numbers[1] >= 1 and $this->numbers[1] <= 59) {
                $this->minute = $this->numbers[1];
            }
        }

        if (count($this->numbers) == 1) {
            if ($this->numbers[0] <= 12 and $this->numbers[0] >= 1) {
                $this->hour = $this->numbers[0];
            }
        }

        $pieces = explode(strtolower($input), " ");

        $keywords = ["am", "pm", "morning", "evening", "late", "early"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "stop":
                            if ($key + 1 > count($pieces)) {
                                $this->stop = false;
                                return "Request not understood";
                            } else {
                                $this->stop = $pieces[$key + 1];
                                $this->response .= $this->stopTranslink(
                                    $this->stop
                                );
                                return;
                            }
                            break;

                        case "am":
                            break;

                        case "pm":
                            $this->hour = $this->hour + 12;
                            break;

                        default:
                    }
                }
            }
        }
    }

    /**
     *
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["help"] =
            "Determines am or pm.";
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $input = $this->input;

        if ($input == "meridian") {
            return;
        }

        $filtered_input = $this->assert($input);
        $this->extractMeridian($filtered_input);
    }
}
