<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Usermanager extends Agent
{
    public function init()
    {
        $this->state = "X";
        $this->sms_seperator =
            $this->thing->container["stack"]["sms_separator"]; // |

        if (isset($this->thing->container["api"]["usermanager"])) {
            if (isset($this->thing->container["api"]["usermanager"]["state"])) {
                $state = $this->thing->container["api"]["usermanager"]["state"];
            }
            if ($state == "off") {
                return;
            }
        }

        // Load in time quantums
        $this->cron_period = $this->thing->container["stack"]["cron_period"]; // 60s
        $this->thing_resolution =
            $this->thing->container["stack"]["thing_resolution"]; // 1ms

        // Load in a pointer to the stack record.
        $this->stack_uuid = $this->thing->container["stack"]["uuid"]; // 60s

        $this->verbosity_log = 7;

        $this->node_list = [
            "start" => [
                "new user" => [
                    "opt-in" => ["opt-out" => ["opt-in", "delete"]],
                ],
            ],
        ];
    }

    function readInstruction()
    {
        $existing_state = $this->state;
        $this->thing->log(
            'read instruction "' . $this->agent_input . '".',
            "INFORMATION"
        );

        if ($this->agent_input == "usermanager optin") {
            $this->previous_state = $this->state;
            $this->state = "opt-in";

            if ($this->verbosity_log >= 8) {
                $this->thing->log(
                    "updated the state to " . $this->state . ".",
                    "INFORMATION"
                );
            }
        }

        if ($this->agent_input == "usermanager optout") {
            $this->previous_state = $this->state;
            $this->state = "opt-out";
        }

        if ($this->agent_input == "usermanager delete") {
            $this->previous_state = $this->state;
            $this->state = "delete";
        }

        if ($this->agent_input == "usermanager start") {
            $this->thing->log("set internal state to START.", "INFORMATION");

            $this->previous_state = $this->state;
            $this->state = "start";
        }

        if ($this->agent_input == "usermanager unsubscribe") {
            $this->previous_state = $this->state;
            $this->state = "unsubscribe";
        }

        if ($this->agent_input == "usermanager stop") {
            $this->previous_state = $this->state;
            $this->state = "stop";
        }

        if ($this->agent_input == "usermanager") {
            $this->previous_state = $this->state;
        }

        if ($existing_state === $this->state) {
            $this->response .=
                "Read instruction and did not see state change. ";
        } else {
            $this->response .=
                "Read instruction and saw " . $this->state . ". ";
        }
    }

    function set()
    {
        if ($this->state === $this->previous_state) {
            // Don't change state if there has not been a state change.
            return;
        }

        $this->variables_agent->setVariable("state", $this->state);
        $this->variables_agent->setVariable("counter", $this->counter);

        $this->previous_state = $this->state;
        $this->variables_agent->setVariable(
            "previous_state",
            $this->previous_state
        );

        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables usermanager " . $this->from
        );

        // get gets the state of the Flag the last time
        // it was saved into the stack (serialized).
        $this->state = $this->variables_agent->getVariable("state");
        $this->previous_state = $this->variables_agent->getVariable(
            "previous_state"
        );

        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        if ($this->verbosity_log >= 8) {
            $this->thing->log(
                "loaded " . $this->state . " " . $this->previous_state . ".",
                "DEBUG"
            );
        }

        $this->counter = $this->variables_agent->getVariable("counter");

        if ($this->verbosity_log >= 8) {
            $this->thing->log("loaded " . $this->counter . ".", "DEBUG");
        }

        $this->counter = $this->counter + 1;

        if ($this->state == false) {
            $this->state = "start";
            $this->previous_state = "X";
        }

        if ($this->state == null or $this->state == true) {
            //          $this->state = "start";
            //          $this->previous_state = "start";
        }

        $this->thing->log(
            "retrieved a " . strtoupper($this->state) . " state.",
            "INFORMATION"
        );
    }

    function makeSMS()
    {
        switch ($this->counter) {
            case 0:
            // drop through
            case 1:
                $sms =
                    "USERMANAGER | " .
                    "state " .
                    strtoupper($this->state) .
                    " previous_state " .
                    strtoupper($this->previous_state);
                break;
            default:
                $sms =
                    "USERMANAGER | " .
                    "state " .
                    strtoupper($this->state) .
                    " previous_state " .
                    strtoupper($this->previous_state);
                break;
        }
        $sms .= " counter " . $this->counter . " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function respondResponse()
    {
        // Develop the various messages for each channel.

        $this->thing->flagGreen();
        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["email"] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
        // I think this is the place to report the state.

        $this->thing_report["keyword"] = $this->state;
        $this->thing_report["help"] =
            'Agent "Usermanager" figuring the user of this Thing out';
    }

    public function readSubject()
    {
        if ($this->agent_input != null) {
            $this->readInstruction();
            return;
        }

        // What do we know at this point?
        // We know the nom_from.
        // We have the message.
        // And we know this was directed towards usermanager (or close).

        // So starting with nom_from.
        // Two conditions, we either know the nom_from, or we don't.

        $this->state_change = false;

        // Is this just the word 'usermanager'?

        $input = $this->input;
        $pieces = explode(" ", strtolower($input));

        // Keyword
        if (count($pieces) == 1) {
            if ($input == "usermanager") {
                $this->response .= "Got current user state. ";
                return;
            }
        }

        $input = strtolower($this->to . " " . $this->subject);

        $keywords = [
            "usermanager",
            "optin",
            "opt-in",
            "optout",
            "opt-out",
            "start",
            "delete",
            "new",
        ];
        $pieces = explode(" ", strtolower($input));

        // Keyword

        /*
        if (count($pieces) == 1) {
            if ($input == "usermanager") {
                $this->response .= "Got current user state. ";
                return;
            }
        }
*/
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "optin":
                        case "opt-in":
                            $this->optin();
                            return;

                        case "optout":
                        case "opt-out":
                            $this->optout();
                            return;

                        case "start":
                            $this->start();
                            return;
                        case "new user":
                        case "newuser":
                        case "new":
                            $this->newuser();
                            return;

                        default:
                        // Could not recognize a command.
                        // Drop through
                    }
                }
            }
        }

        // Did not recognize a usermanager command.
        // Try discriminateInput.

        $discriminators = [
            "opt-in" => ["optin", "accept", "okay", "yes", "sure"],
            "opt-out" => ["optout", "leave", "unsubscribe", "no", "quit"],
        ];

        $type = $this->discriminateInput($input, $discriminators);
        if ($type != false) {
            $this->requested_state = $type;
        }

        if (isset($this->requested_state) and $this->requested_state !== null) {
            $this->response .= "Heard " . $this->requested_state . ". ";
            switch ($this->state) {
                case "opt-out":
                    // We are in a state of opt-out.
                    // Only respond to an Opt-in message

                    if ($this->requested_state == "opt-in") {
                        $this->optin();
                        return;
                    }

                    // Otherwise ignore
                    return;

                case "opt-in":
                    // In the opt-in state.
                    if ($this->requested_state == "opt-out") {
                        $this->optout();
                        return;
                    }
                    // Otherwise ignore
                    return;

                case "new user":
                    if ($this->requested_state == "opt-in") {
                        $this->optin();
                        return;
                    }

                    if ($this->requested_state == "opt-out") {
                        $this->optout();
                        return;
                    }
                    return;

                case "start":
                    $this->newuser();
                    break;

                case "delete":
                    //$this->state_change = true;
                    //$this->thing->choice->Choose("new");
                    // Do nothing remain deleted.
                    // Make so must text "start"

                    // Was deleted but now continuing to have conversation
                    // Loop through to start
                    $this->state_change = true;
                    $this->thing->choice->Choose("start");
                    $this->state = "start";
                    return;
                    break;

                default:
            }
        }

        // No instruction found.
        // Don't do anything.
    }

    function newuser()
    {
        $this->thing->log("chose NEWUSER.", "INFORMATION");
        $this->thing->choice->Choose("new user");
        $this->previous_state = $this->state;
        $this->state = "new user";

        $agent = new Newuser($this->thing, "usermanager");
        $this->response .= "Set user state to New User. ";
        return;
        // Make a record of the new user request

        $newuser_thing = new Thing(null);
        $newuser_thing->Create(
            $this->from,
            "usermanager",
            "s/ newuser (usermanager)"
        );

        //		$node_list = array("new user"=>array("opt-in"=>array("opt-out"=>"opt-in")));

        $newuser_thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "new user"
        );

        $newuser_thing->flagRed();
    }

    function start()
    {
        $this->thing->log("chose START.", "INFORMATION");

        $this->thing->choice->Choose("start");

        $this->previous_state = $this->state;
        $this->state = "start";

        $agent = new Start($this->thing, "usermanager");
        $this->response .= "Set user state to Start. ";
    }

    function optout()
    {
        $this->thing->log("chose OPTOUT.", "INFORMATION");

        // Send to the Optin agent to handle response
        $this->thing->choice->Choose("opt-out");
        $this->previous_state = $this->state;
        $this->state = "opt-out";
        $a = $this->traceAgent();
        $b = $this->callingAgent();

        $agent = new Optout($this->thing, "usermanager");
        $this->response .= "Set user state to Opt-out. ";
    }

    function optin()
    {
        $this->thing->log("chose OPTIN.", "INFORMATION");

        // Send to the Optin agent to handle response

        $this->thing->choice->Choose("opt-in");
        $this->previous_state = $this->state;
        $this->state = "opt-in";

        $agent = new Optin($this->thing, "usermanager");
        $this->response .= "Set user state to Opt-in. ";
    }
}
