<?php
namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Nominal extends Agent
{
    // This is a Nominal.

    public $var = 'hello';
    function init()
    {
        $this->node_list = [
            "start" => ["stop 1" => ["stop 2", "stop 1"], "stop 3"],
            "stop 3",
        ];
        $this->thing->choice->load('Nominal');

        $this->keywords = ['next', 'accept', 'clear', 'drop', 'add', 'new'];

        $this->default_variable = "X";
        $this->default_state = 'on';
        $this->state = $this->default_state;

        $this->flag = 'green';

        $this->test = "Development code"; // Always iterative.

        $this->initNominal();
    }

    public function initNominal()
    {
        $this->loadNominals();
    }

    function set()
    {
        // A Nominal has some remaining amount of resource and
        // an indication where to start.

        // This makes sure that
        if (!isset($this->nominal_thing)) {
            $this->nominal_thing = $this->thing;
        }

        $this->nominal_thing->Write(
            ["nominal", "state"],
            $this->state
        );

        $this->nominal_thing->Write(
            ["nominal", "refreshed_at"],
            $this->current_time
        );

        $this->nominal_thing->choice->save('nominal', $this->state);
    }

    function get($variable = null)
    {
        //$this->getNominals();
        // Loads current Nominal into $this->Nominal_thing

        $match = false;

        $variable = $this->getVariable('nominal', $variable);
        if ($variable == false) {
            $variable = "X";
        }

        $nominal_things = [];
        // See if a Nominal record exists.
        $findagent_thing = new Findagent($this->thing, 'nominal');

        // This pulls up a list of other Nominal Things.
        // We need the newest Nominal as that is most likely to be relevant to
        // what we are doing.

$things = $findagent_thing->thing_report['things'];


        $this->thing->log(
            'Agent "Nominal" found ' .
                count($findagent_thing->thing_report['things']) .
                " Nominal Things."
        );

        $this->max_index = 0;
        $this->current_variable = null;

        // Set the Nominal thing as the current latest.
        // Not working.
        if (!isset($this->nominal_thing)) {
            $nominal_things = $findagent_thing->thing_report['things'];
            $nominal_thing = $nominal_things[0];
            $thing = new Thing($nominal_thing['uuid']);
            $latest_variable = $thing->Read(["nominal", "state"]);
        }

        if (strtolower($variable) == strtolower($this->default_variable)) {
            // This means we are being asked to pull the default head code
            // which should be the latest if it exists.
            if (isset($latest_variable)) {
                $variable = $latest_variable;
            }
        }


        foreach (
            array_reverse($things)
            as $nominal_thing
        ) {
            $thing = new Thing($nominal_thing['uuid']);

            // Load requird val
            $thing->index = $thing->Read(["nominal", "index"]);
            $thing->state = $thing->Read(["nominal", "state"]);

            $thing->refreshed_at = $thing->Read([
                "Nominal",
                "refreshed_at",
            ]);

            if ($thing->index > $this->max_index) {
                $this->max_index = $thing->index;
            }

            if (!isset($thing->variable)) {
                $thing->variable = $this->default_variable;
            }

            // If the input Nominal matches...
            if (strtolower($variable) == strtolower($thing->variable)) {
                $this->thing->log(
                    'Agent "Nominal" found ' .
                        $thing->variable .
                        ' in existing Nominal #' .
                        $thing->index .
                        '.'
                );
                $match = true;
                break; //Take first matching Nominal.
            } else {
                $this->thing->log(
                    'Nominal #' . $thing->index . ' (' . $thing->variable . ")."
                );
            }
        }

        // If it drops through as Green, then no Nominals matched the current time.
        if ($match == false) {
            // No valid Nominal found, so make a Nominal record in current Thing
            // and set flag to Green ie accepting trains.

            $this->nominal_thing = $this->thing;

            $this->index = 0;

            $this->thing->log(
                'Agent "Nominal" did not find a Nominal for ' . $variable . "."
            );

            // So if a Nominal was not found we need to make one.
            // This will start creating the identities head code space.

            $recurse = true;

            if ($recurse != false) {
                $this->variable = $variable;

                $this->makeNominal();
            }
        } else {
            $this->thing->log("found a matching Nominal.");

            // Red Nominal Thing - There is a current operating Nominal on the stack.
            // Load the Nominal details into this Thing.

            $this->useNominal($thing);
        }
    }

    function dropNominal()
    {
        $this->thing->log("was asked to drop a Nominal.");

        //$this->get(); No need as it ran on start up.

        // If it comes back false we will pick that up with an unset Nominal thing.

        if (isset($this->nominal_thing)) {
            $this->nominal_thing->Forget();
            $this->nominal_thing = null;
        }

        $this->get();
    }

    function useNominal($thing)
    {
        $this->nominal_thing = $thing;

        $this->variable = $thing->variable;

        $this->index = $thing->index;

        return false;
    }

    function makeNominal($variable = null)
    {
        $variable = $this->getVariable('nominal', $variable);

        $this->thing->log('will make a Nominal for ' . $variable . ".");

        $ad_hoc = true;

        if ($ad_hoc != false) {
            // Ad-hoc Nominals allows creation of Nominals on the fly.

            // So we can create this Nominal either from the variables provided to the function,
            // or leave them unchanged.

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            $this->current_variable = $variable;
            $this->variable = $variable;

            $this->nominal_thing = $this->thing;
        }

        $this->set();

        $this->thing->log('found a Nominal and made a Nominal.');
    }

    public function loadNominals()
    {
        $librex_agent = new Librex($this->thing, "nominals/first_names.all");
        $librex_agent->getMatches();

        $matches = [];

        if (!isset($this->nominals_list)) {
            $this->nominals_list = [];
        }

        foreach ($librex_agent->matches as $i => $word) {
            $matches[] = ['nominal' => $word['words']];
            $this->nominals_list[] = $word['words'];
        }

        $librex_agent = new Librex($this->thing, "nominals/last_names.all");
        $librex_agent->getMatches();

        foreach ($librex_agent->matches as $i => $word) {
            $matches[] = ['nominal' => $word['words']];
            $this->nominals_list[] = $word['words'];
        }
        array_unique($this->nominals_list);

        $s = "GREEN";
        $this->librex_matches = $matches;
    }

    public function hasNominal($text = null)
    {
        if ($text == "null") {
            return false;
        }

        $tokens = explode(" ", strtolower($text));

        foreach ($tokens as $i => $token) {
            if (in_array($token, $this->nominals_list)) {
                return true;
            }
        }

        return false;
    }

    function extractNominals($input)
    {
        if (!isset($this->nominals_list)) {
            $this->nominals_list = [];
        }

        $this->nominals = [];

        $tokens = explode(" ", strtolower($input));
        foreach ($tokens as $i => $token) {
            if (in_array($token, $this->nominals_list)) {
                if ($token == "") {
                    continue;
                }
                $this->nominals[] = $token;
            }
        }
        return $this->nominals;
        $pattern = "|\[A-Za-z]|";

        preg_match_all($pattern, $input, $m);
        $this->nominalss = $m[0];

        return $this->nominals;
    }

    function getNominal($input)
    {
        $nominals = $this->extractNominals($input);

        if (count($nominals) == 1) {
            $this->nominal = $nominals[0];
            $this->thing->log(
                'Agent "Nominal" found a Nominal (' .
                    $this->nominal .
                    ') in the text.'
            );
            return $this->nominal;
        }

        if (count($nominals) == 0) {
            return false;
        }

        if (count($nominals) > 1) {
            return true;
        }

        return true;
    }

    function readNominal()
    {
        $this->thing->log("read");
    }

    function addNominal()
    {
        $this->makeNominal();
        $this->get();
    }

    function setState($input)
    {
        switch ($input) {
            case "on":
                if ($this->state == "off" or $this->state == "X") {
                    $this->state = "on";
                }
                break;

            case "off":
                if ($this->state == "on" or $this->state == "X") {
                    $this->state = "off";
                }

                break;
        }
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        $choices = $this->thing->choice->makeLinks($this->state);
        $this->thing_report['choices'] = $choices;

        if (!isset($this->index)) {
            $index = "0";
        } else {
            $index = $this->index;
        }

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);

            $this->thing_report['info'] = $message_thing->thing_report['info'];
        } else {
            $this->thing_report['info'] =
                'Agent input was "' . $this->agent_input . '".';
        }

        $this->thing_report['help'] = 'This is a Nominal.';
    }

    public function makeSMS()
    {
        $s = $this->flag;

        $nominal = "X";
        if (isset($this->nominal)) {
            $nominal = $this->nominal;
        }

        $sms_message = "NOMINAL " . strtoupper($nominal) . " | " . $s;
        $sms_message .= " | ";

        $index = 1;
        if (isset($this->index)) {
            $index = $this->index;
        }

        $sms_message .= " | index " . $index;
        $sms_message .= " | nuuid " . strtoupper($this->nominal_thing->nuuid);

        $sms_message .= " " . $this->response;

        switch ($index) {
            case null:
                $sms_message =
                    "NOMINAL | No active Nominal found. | TEXT Nominal <four digit clock> <1-3 digit runtime>";
                break;

            case '1':
                $sms_message .= " | TEXT NOMINAL <text>";
                break;
            case '2':
                $sms_message .= " | TEXT DROP Nominal";
                break;
            case '3':
                $sms_message .= " | TEXT Nominal";
                break;
            case '4':
                $sms_message .= " | TEXT Nominal";
                break;
            default:
                $sms_message .= " | TEXT ?";
                break;
        }

        $this->thing_report['sms'] = $sms_message;
        $this->thing_report['email'] = $sms_message;
        $this->thing_report['message'] = $sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;
    }

    public function readSubject()
    {
        $this->num_hits = 0;

        $keywords = $this->keywords;

        $input = $this->input;

        $filtered_input = $this->assert($input);
        $nominals = [];
        $new_nominals = $this->extractNominals($filtered_input);

        $nominals = array_merge($new_nominals, $nominals);

        $haystack = strtolower(
            $this->agent_input . " " . $this->from . " " . $this->subject
        );

        $nominals = $this->extractNominals($haystack);

        $nominals = array_merge($new_nominals, $nominals);

        $thing_text = implode((array) $this->thing->thing, " ");
        $nominals = $this->extractNominals($thing_text);

        $nominals = array_merge($new_nominals, $nominals);
        $nominals = array_unique($nominals);

        if (count($nominals) > 0) {
            $this->response .= "Saw at least one nominal. ";
            $this->flag = "red";
        }

        $prior_uuid = null;

        $this->getNominal($input);

        $pieces = explode(" ", strtolower($input));

        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'nominal') {
                $this->readNominal();
                return;
            }

            // Drop through
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'next':
                            $this->thing->log("read subject nextNominal");
                            $this->nextNominal();
                            break;

                        case 'drop':
                            $this->dropNominal();
                            break;

                        case 'add':
                            $this->makeNominal();
                            break;
                        case 'add':

                        default:
                    }
                }
            }
        }

        if ($this->thing->isData($this->variable)) {
            $this->makeNominal($this->variable);
            return;
        }

        $this->readNominal();

        return "Message not understood";
    }
}
