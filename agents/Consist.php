<?php
/**
 * Consist.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Consist extends Agent
{
    // This is a consist.

    // It looks like Xaaabc.
    // Where X is the engine.
    // And a, b and c are rolling stock things.

    public $var = 'hello';

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    //    function __construct(Thing $thing, $agent_input = null) {
    public function init()
    {
        // I'm not sure quite what the node_list means yet
        // in the context of Consists.
        // At the moment it seems to be the Consist routing.
        // Which is leading to me to question whether "is"
        // or "Place" is the next Agent to code up.  I think
        // it will be "Is" because you have to define what
        // a "Place [is]".
        $this->node_list = [
            "consist" => ["add" => ["drop", "add"], "drop"],
            "drop",
        ];
        $this->thing->choice->load('Consist');

        $this->keywords = ['consist', 'clear', 'drop', 'add', 'load', 'is'];

        $this->web_prefix = $this->thing->container['stack']['web_prefix'];

        $this->default_variable = "0Z10";
        $this->default_alias = "Thing";

        $this->current_time = $this->thing->json->time();

        //        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';
        $this->agents_path = $GLOBALS['stack_path'] . 'agents/';
        $this->agents_path =
            $GLOBALS['stack_path'] .
            'vendor/nrwtaylor/stack-agent-thing/agents/';

        $this->test = "Development code"; // Always iterative.

        $this->link = $this->web_prefix . 'thing/' . $this->uuid . '/consist';
    }

    /**
     *
     */
    function set()
    {
        // A Consist has some remaining amount of resource and
        // an indication where to start.

        // This makes sure that
        if (!isset($this->consist_thing)) {
            $this->consist_thing = $this->thing;
        }

        $this->variables_agent->setVariable("consist", $this->consist);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );

        $this->consist_thing->json->setField("variables");
        $this->consist_thing->json->writeVariable(
            ["consist", "consist"],
            $this->consist
        );
        $this->consist_thing->json->writeVariable(
            ["consist", "refreshed_at"],
            $this->current_time
        );
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $this->head_code = $this->thing->json->readVariable([
            "headcode",
            "head_code",
        ]);

        $flag_variable_name = "_" . $this->head_code;

        // Get the current Identities flag
        //        $this->flag = new Variables(
        //            $this->thing,
        //            "variables flag" . $flag_variable_name . " " . $this->from
        //        );

        /*
        $this->variables_agent = new Variables(
            $this->thing,
            "variables alias " . $this->from
        );
*/

        $this->variables_agent = new Variables(
            $this->thing,
            "variables consist" . $flag_variable_name . " " . $this->from
        );

        $this->consist = $this->variables_agent->getVariable("consist");

        //$this->getConsists('consist');
    }

    /**
     *
     * @param unknown $variable (optional)
     * @return unknown
     */
    function getConsists($variable = null)
    {
        // Loads current Consist into $this->Consist_thing

        $match = false;

        $variable = $this->getVariable('consist', $variable);

        $consist_things = [];

        // This pulls up a list of other Consist Things.
        // We need the newest Consist as that is most likely to be relevant to
        // what we are doing.

        $things = $this->getThings('consist');

        $this->thing->log(
            'Agent "Consist" found ' . count($things) . " Consist Things."
        );

        $this->current_variable = null;

        foreach (array_reverse($things) as $thing) {
            $subject = $thing->subject;
            $variables = $thing->variables;
            $created_at = $thing->created_at;

            if (isset($variables['consist'])) {
                $consist = "X";

                if (isset($variables['consist']['consist'])) {
                    $consist = $variables['consist']['consist'];
                }
                if (isset($variables['consist']['refreshed_at'])) {
                    $refreshed_at = $variables['consist']['refreshed_at'];
                }
            }

            if ($refreshed_at == false) {
                // Things is list sorted by date.  So this is the oldest Thing.
                // with a 'keyword' record.
                continue;
            } else {
                $thing_object = new Thing($thing->uuid);

                $thing_object->consist = $consist;
                $this->useConsist($thing_object);
                return false;
            }
        }

        $this->makeConsist();

        return false;
    }

    /**
     *
     */
    function dropConsist()
    {
        $this->thing->log($this->agent_prefix . "was asked to drop a Consist.");

        // If it comes back false we will pick that up with an unset Consist thing.

        if (isset($this->consist_thing)) {
            $this->consist_thing->Forget();
            $this->consist_thing = null;
        }

        $this->get();
    }

    /**
     *
     * @param unknown $thing
     * @return unknown
     */
    function useConsist($thing)
    {
        $this->consist_thing = $thing;

        // Core elements of a Consist
        $this->consist = $thing->consist;

        return false;
    }

    /**
     *
     * @param unknown $variable (optional)
     */
    function makeConsist($variable = null)
    {
        $variable = $this->getVariable('consist', $variable);

        $this->thing->log(
            'Agent "Consist" will make a Consist for ' . $variable . "."
        );

        // Check that the shift is okay for making Consists.

        // Otherwise we needs to make trains to run in the Consist.

        $this->thing->log(
            $this->agent_prefix . "is going to run this for the default engine."
        );

        $this->current_variable = $variable;
        $this->consist = $variable;

        //            $this->consist_thing = $this->thing;

        // Write the variables to the db.
        $this->set();

        //$this->Consist_thing = $this->thing;

        $this->thing->log('Agent "Consist" found Consist and pointed to it.');
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractAlpha($input)
    {
        $words = explode(" ", $input);

        $arr = [];

        foreach ($words as $word) {
            if (ctype_alpha($word)) {
                $arr[] = $word;
            }
        }

        return $arr;
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractConsists($input)
    {
        //        $input = "Train is NbbbX";

        if (!isset($this->consists)) {
            $this->consists = [];
        }

        //        $pattern = "|^[A-Z0-9]{3}(?:List)?$|";

        $pattern = "|^[A-Z]*[A-Z]$|";
        $pattern = "|\w*[A-Z]\w*|";
        //Explanation:
        //^        : Start anchor
        //[A-Z0-9] : Char class to match any one of the uppercase letter or digit
        //{3}      : Quantifier for previous sub-regex
        //(?:List) : A literal 'List' enclosed in non-capturing parenthesis
        //?        : To make the 'List' optional
        //$        : End anchor
        //echo $input;

        preg_match_all($pattern, $input, $m);

        $possible_consists = $m[0];

        if (
            count($possible_consists) >= 1 and
            $possible_consists[0] == "Consist"
        ) {
            array_shift($possible_consists);
        }

        $consists = [];
        // Then tweak selection?
        foreach ($possible_consists as $possible_consist) {
            $consists[] = $possible_consist;
            //       $requested_locomotives =
            //       $requested_rollingstock =
        }

        $this->consists = $consists;

        return $this->consists;

        //        return $arr;
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function readConsist($input)
    {
        $this->headcode_agent = new Headcode($this->thing, $input);
        $headcodes = $this->headcode_agent->getHeadcodes();

        $consist = [];
        foreach ($headcodes as $i => $headcode) {
            if ($headcode['head_code'] == $this->headcode_agent->head_code) {
                $consist[] = $headcode;
            }
        }
        // http://www.greatwestern.org.uk/stockcode.htm
        $this->consist_stock = [
            "engine",
            "carriage",
            "wagon",
            "caboose",
            "break van",
            "brake van",
            "coal wagon",
            "toad",
            "dogfish",
        ];

        $this->consist_string = "";
        $this->consist_array = $consist;
        foreach ($consist as $item) {
            foreach ($this->consist_stock as $j => $stock_name) {
                if (strpos($item[0], $stock_name) !== false) {
                    $this->consist_string .= " " . $item[0];
                }
            }
        }
        return;
        $consists = $this->extractConsists($input);

        if (count($consists) == 1) {
            $this->consist = $consists[0];
            $this->thing->log(
                'Agent "Consist" found a Consist (' .
                    $this->consist .
                    ') in the text.'
            );

            //echo $this->consist;
            //exit();
            return $this->consist;
        }

        if (count($consists) == 0) {
            return false;
        }
        if (count($consists) > 1) {
            return true;
        }

        return true;
    }

    /**
     *
     */
    function addConsist()
    {
        $this->makeConsist();
        $this->get();
        return;
    }

    /**
     *
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report['choices'] = false;

        if (!isset($this->index)) {
            $index = "0";
        } else {
            $index = $this->index;
        }

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);

            $this->thing_report['info'] = $message_thing->thing_report['info'];
        } else {
            $this->thing_report['info'] =
                'Agent input was "' . $this->agent_input . '".';
        }

        $this->thing_report['help'] = 'Try CONSIST IS Rkkk.';
    }

    public function makeSMS()
    {
        if (!isset($this->consist) or $this->consist == null) {
            $consist = "X";
        }

        if (isset($this->consist)) {
            $consist = $this->consist;
        }

        $sms = "CONSIST ";
        $sms .= strtoupper($this->headcode_agent->head_code);
        $sms .= " " . $consist;
        //$sms .= " " . $this->link;

        if (trim($this->consist_string) != "") {
            $sms .= " Consists of " . trim($this->consist_string);
        }

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     * @param unknown $variable
     * @return unknown
     */
    function isData($variable)
    {
        if ($variable !== false and $variable !== true and $variable != null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->response = null;
        $this->num_hits = 0;

        $keywords = $this->keywords;

        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            //$input = strtolower($this->agent_input);
            $input = $this->agent_input;
        } else {
            // $input = strtolower($this->subject);
            $input = $this->subject;
        }

        $prior_uuid = null;

        $this->readConsist($this->subject);

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'consist') {
                //echo "readsubject Consist";
                $this->readConsist($input);
                return;
            }

            // Drop through
        }

        // Extract runat signal
        $matches = 0;

        /*
    if ((isset($this->run_time)) and (isset($this->run_at))) {
        // Good chance with both these set that asking for a new
        // Consist to be created, or to override existing Consist.
        $this->thing->log('Agent "Consist" found a run time.');

        $this->nextConsist();
        return;
    }
*/
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'accept':
                            $this->acceptThing();
                            break;
                        case 'is':
                        case '=':
                            if ($this->isData($this->consist)) {
                                $this->consist = $this->assert(
                                    $input,
                                    "consist",
                                    false
                                );

                                $this->makeConsist($this->consist);
                                return;
                            }

                        case 'clear':
                            $this->clearThing();
                            break;

                        case 'drop':
                            //     //$this->thing->log("read subject nextConsist");
                            $this->dropConsist();
                            break;

                        case 'add':
                            //     //$this->thing->log("read subject nextConsist");
                            $this->makeConsist();
                            break;

                        default:
                        //$this->read();                                                    //echo 'default';
                    }
                }
            }
        }

        if ($this->isData($this->consist)) {
            $this->makeConsist($this->consist);
            return;
        }

        $this->readConsist($input);

        return "Message not understood";

        return false;
    }
}
