<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Alias extends Agent
{
    // This is the alias manager.  It assigns an alias coding to
    // N-grams which are the same idea gram.

    // It needs to return the latest alias record for the current context.
    // So first find the context.
    // Then find the latest alias record in that context.

    public $var = 'hello';
    function init()
    {
        $this->keyword = "alias";

        $this->start_time = $this->thing->elapsed_runtime();
        $this->thing_report['thing'] = $this->thing->thing;

        $this->test = "Development code"; // Always

        //$this->thing->choice->load('alias');
        $this->node_list = ["off" => ["on" => ["off"]]];

        // This isn't going to help because we don't know if this
        // is the base.
        //        $this->state = "off";
        //        $this->thing->choice->load($this->keyword);

        $this->current_time = $this->thing->json->time();

        $this->variables_agent = new Variables(
            $this->thing,
            "variables alias " . $this->from
        );

        $this->alias_thing = $this->variables_agent->thing;
        $this->keywords = ['alias', 'is'];

        $this->context = null;
        $this->context_id = null;
        $this->alias = null;
        $this->alias_id = null;

        $this->current_time = $this->thing->json->time();

        $default_alias_name = "alias";
    }

    function getState()
    {
        if (!isset($this->state)) {
            $this->state = "X";
        }

        return $this->state;
    }

    function set()
    {
        // A block has some remaining amount of resource and
        // an indication where to start.

        // This makes sure that
        if (!isset($this->alias_thing)) {
            $this->alias_thing = $this->thing;
        }

        $this->variables_agent->setVariable("alias", $this->alias);

        $this->variables_agent->setVariable("context", $this->context);
        $this->variables_agent->setVariable("alias_id", $this->alias_id); // exactly same as context id

        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );

        $this->thing->json->writeVariable(["alias", "alias"], $this->alias);
        $this->thing->json->writeVariable(["alias", "context"], $this->context);
        $this->thing->json->writeVariable(
            ["alias", "alias_id"],
            $this->alias_id
        ); // exactly same as context_id

        $this->thing->json->writeVariable(
            ["alias", "refreshed_at"],
            $this->current_time
        );

        $this->thing->log(
            $this->agent_prefix .
                ' thought ' .
                $this->alias .
                " " .
                $this->context .
                " " .
                $this->alias_id .
                "."
        );

        $this->refreshed_at = $this->current_time;
    }

    function extractContext($input = null)
    {
        $this->context_agent = new Context($this->thing, "context " . $input);

        $this->context = $this->context_agent->context;
        $this->context_id = $this->context_agent->context_id;

        $this->thing->log(
            $this->agent_prefix .
                ' got context ' .
                $this->context .
                " " .
                $this->context_id .
                ". ",
            "DEBUG"
        );

        return $this->context;
    }

    function getAliases()
    {
        $this->aliases_list = [];

        $findagent_thing = new Findagent($this->thing, 'alias');

        $this->thing->log(
            'Agent "Alias" found ' .
                count($findagent_thing->thing_report['things']) .
                " Alias Agent Things."
        );
        $this->thing->log(
            'Agent "Alias". Timestamp ' .
                number_format($this->thing->elapsed_runtime()) .
                'ms.'
        );

        foreach ($findagent_thing->thing_report['things'] as $thing_object) {
            // While timing is an issue of concern

            $uuid = $thing_object['uuid'];

            //echo $thing_object['task'];

            if ($thing_object['nom_to'] != "usermanager") {

                $variables_json = $thing_object['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                if (
                    isset($variables['alias']) and
                    isset($variables['alias']['alias'])
                ) {
                    // prod

                    //     (isset($variables['alias'])) {
                    $alias = $variables['alias']['alias'];

                    $variables['alias'][] = $thing_object['task'];
                    $this->aliases_list[] = $variables['alias'];
                }
            }
        }

        return $this->aliases_list;
    }

    function extractAliases($input = null)
    {
        // Get the list of aliases
        if (!isset($this->aliases_list)) {
            $this->getAliases();
        }
        //$search_array = array_combine(array_map('strtolower', $this->aliases), $this->aliases);

        $search_array = null;
        if ($input == null) {
            $input = strtolower($this->subject);
        }

        $this->aliases = [];

        $pieces = explode(" ", strtolower($input));
        foreach ($pieces as $key => $piece) {
            foreach ($this->aliases_list as $key => $alias_arr) {
                //        $search_array = array_combine(array_map('strtolower', $this->aliases), $this->aliases);

                $alias = $alias_arr['alias'];

                if (isset($search_array[strtolower($piece)])) {
                } else {
                    $alphanum_alias = preg_replace("/[^A-Z]+/", "", $alias);
                    $this->aliases[] = $alphanum_alias;
                    $search_array = array_combine(
                        array_map('strtolower', $this->aliases),
                        $this->aliases
                    );
                }
            }
        }
        return $this->aliases;
    }

    function get($train_time = null)
    {
        // Loads current alias into $this->alias_thing

        $this->get_start_time = $this->thing->elapsed_runtime();

        $this->thing->log(
            'Timestamp ' .
                number_format($this->thing->elapsed_runtime()) .
                'ms.'
        );

        $this->variables_agent = new Variables(
            $this->thing,
            "variables alias " . $this->from
        );
        $this->variables_agent->getVariables();

        $this->thing->log(
            'Timestamp ' .
                $this->thing->elapsed_runtime() .
                'ms.'
        );

        // So if no alias records are returned, then this is the first
        // record to be set. A null call to set() will start things off.

        // if ($this->variables_agent->alias != null) {
        // Otherwise, we know we have at least a handful of
        // existing aliases to check.

        // Filter by context_id
        $this->getAliases();
        $aliases = [];
        foreach ($this->aliases_list as $key => $alias) {
            //            echo "alias " .$alias['alias'] . " alias_id " . $alias['alias_id'] . " context " . $alias['context'] . " is " . $alias['context'];
            //            echo "<br>";
            if ($alias['alias_id'] == $this->context_id) {
                $aliases[] = $alias;
            }
        }

        if (count($aliases) == 0) {
            $this->response .= "Got zero aliases. ";
            $this->alias = null;
        } else {
            $this->alias = $aliases[0]['alias'];
            $this->alias_id = $aliases[0]['alias_id'];

            $this->alias = null;
            $this->alias_id = null;
        }
    }

    function dropAlias()
    {
        $this->thing->log($this->agent_prefix . "was asked to drop an alias.");

        if (isset($this->alias_thing)) {
            $this->alias_thing->Forget();
            $this->alias_thing = null;
        }

        $this->get();
    }

    function runAlias()
    {
        $this->makeAlias($this->alias);

        $this->state = "running";
    }

    function makeAlias($alias = null)
    {
        $this->thing->log(
            $this->agent_prefix .
                'will make an Alias with ' .
                $this->alias .
                "."
        );

        $allow_create_alias = true;

        if ($allow_create_alias) {

            $this->thing->log(
                    'found an alias ' .
                    $this->alias .
                    'and made a Alias entry' .
                    $this->alias_id .
                    '.'
            );

        } else {
            $this->thing->log(
                $this->agent_prefix . 'was not allowed to make a Alias entry.'
            );

        }

        $this->thing->log(
            $this->agent_prefix . 'found an alias and made a Alias entry.'
        );
        $this->response .= 'Asked to make a Alias. But did not. ';
    }

    function extractAlias($input = null)
    {
        // Extract everything to the right
        // of the first is or =
        $pieces = explode(" ", strtolower($input));

        if ($input == null) {
            $alias = "X";
            return $alias;
        } else {
            $input = strtolower($this->subject);

            $keywords = ['is'];
            $pieces = explode(" is ", strtolower($input));

            //        $this->max_ngram = 10;
            if (count($pieces) == 2) {
                // A left and a right pairing and nothing else.
                // So we can substitute the word and pass it to Alias.

                $this->left_grams = $pieces[0];
                $this->right_grams = $pieces[1];

                $left_num_words = count(explode(" ", $this->left_grams));
                $right_num_words = count(explode(" ", $this->right_grams));

                if ($left_num_words < $right_num_words) {
                    $this->alias_id = $this->left_grams;
                    $this->alias = $this->right_grams;
                } else {
                    $this->alias_id = $this->right_grams;
                    $this->alias = $this->left_grams;
                }

                //            if ($left_num_words <= $this->max_ngram) {

                // Could call this as a Gearman worker.
                // Pass it to Alias which handles is/alias as the same word.
                //$instruction = $left_grams . " alias " . $right_grams;

                $this->response .= "Got alias " . $this->alias . ". ";

                if ($this->alias == "place") {
                    // Okay straight to Place
                    $place_agent = new Place($this->thing);
                    return;
                }

                return;
            }
        }
        $alias = "X";
        return $alias;
    }

    function readAlias($text = null)
    {
        if ($text == null) {
            $text = $this->input;
        }

        $this->thing->log("read");
    }

    function addAlias()
    {
        $this->makeAlias();
        $this->get();
    }

    function makeTXT()
    {
        if (!isset($this->aliases_list)) {
            $this->getAliases();
        }

        $txt =
            'These are ALIASES for RAILWAY ' .
            $this->variables_agent->nuuid .
            '. ';
        $txt .= "\n";
        $txt .= count($this->aliases_list) . ' Aliases retrieved.';

        $txt .= "\n";

        //$txt .= str_pad("INDEX", 7, ' ', STR_PAD_LEFT);
        //            $txt .= " " . str_pad("HEAD", 4, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("ALIAS", 24, " ", STR_PAD_RIGHT);
        //            $txt .= " " . str_pad("DAY", 4, " ", STR_PAD_LEFT);

        //            $txt .= " " . str_pad("RUNAT", 6, " ", STR_PAD_LEFT);
        //            $txt .= " " . str_pad("ENDAT", 6, " ", STR_PAD_LEFT);

        $txt .= " " . str_pad("ALIAS_ID", 8, " ", STR_PAD_LEFT);

        $txt .= " " . str_pad("CONTEXT", 6, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";

        foreach ($this->aliases_list as $key => $alias) {

            $txt .= " " . str_pad($alias['alias'], 24, " ", STR_PAD_RIGHT);

            if (!isset($alias['alias_id'])) {
                $alias['alias_id'] = "X";
            }
            if (!isset($alias['context'])) {
                $alias['context'] = "X";
            }

            $txt .= " " . str_pad($alias['alias_id'], 8, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad($alias['context'], 6, " ", STR_PAD_LEFT);

            $txt .= "\n";
        }

        $txt .= "\n";
        $txt .= "---\n";

        $txt .= "alias is " . $this->alias . "\n";
        $txt .= "context is " . $this->context . "\n";
        $txt .= "alias_id is " . $this->alias_id . "\n";

        $txt .= "---";

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;

        return $txt;
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->thing_report['choices'] = false;

        if (!isset($this->index)) {
            $index = "0";
        } else {
            $index = $this->index;
        }

        $this->makeChoices();

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] = 'This is the Aliasing manager.';

        //		return $this->thing_report;
    }

    public function makeChoices()
    {
        if (!isset($this->choices)) {
            $this->thing->choice->Create(
                $this->agent_name,
                $this->node_list,
                "alias"
            );
            $this->choices = $this->thing->choice->makeLinks('alias');
        }
        $this->thing_report['choices'] = $this->choices;
    }

    public function makeSMS()
    {
        if (!isset($this->sms_messages)) {
            $this->sms_messages = [];
        }

        $this->sms_messages[] =
            "ALIAS | Could not find an agent to respond to your message.";
        $this->node_list = ["alias" => ["agent", "message"]];

        $sms = "ALIAS " . strtoupper($this->alias_id);
        $sms .= " | alias " . strtoupper($this->alias);

        $sms .= " | nuuid " . substr($this->variables_agent->uuid, 0, 4);
        $sms .= " | nuuid " . substr($this->alias_thing->uuid, 0, 4);

        $sms .= " | context " . $this->context;
        $sms .= " | alias id " . $this->alias_id;

        $sms .=
            " | ~rtime " .
            number_format($this->thing->elapsed_runtime()) .
            "ms";
        $sms .= $this->response;

        //        $this->sms_messages[] = $sms_message;

        //        $this->sms_message = $this->sms_messages[0];

        //$sms = $sms_message;

        //        $this->thing_report['sms'] = $this->sms_messages[0];
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function isPlace($input)
    {
        // recognize a place on the alias list

        foreach ($this->aliases_list as $key => $alias_list) {
            $alias = $alias_list['alias'];
            $alias_id = $alias_list['alias_id'];
            $alias_timestamp = $alias_list['refreshed_at'];
            $context = $alias_list['context'];

            if ($alias == null) {
                continue;
                echo "meep";
            }

            // building this for two people
            if (strpos($input, strtolower($alias)) !== false) {
                // never like these double-ifs, but it's kind of clear
                // that we are check both the alias first.
                //        echo 'found alias';
                return "green";
            }

            if ($alias_id == null) {
                continue;
            } // ? badly formed alias?

            if (strpos($input, $alias_id) !== false) {
                // alias found the word in it's list of alias_ids
                // possibly tells us the alias_id generator is
                // quite liberal with it's identifiers.
                //        echo 'found alias_id';
                return "green";
            }

            // run - see if it works.  delete comment.  keep going.
        }

        return "red";
    }

    public function readSubject()
    {
        $this->num_hits = 0;

        $input = $this->input;

        $this->extractAlias($input);

        // Bail at this point if
        // only extract wanted.
        if ($this->agent_input == 'extract') {
            // Added return here March 17 2018
            return;
            if ($this->alias != false) {
                return;
            }
        }

        $this->getAliases();

        $this->extractContext();

        $this->input = $input;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // Keyword

        if (count($pieces) == 1) {
            if ($this->input == 'alias') {
                $this->num_hits += 1;
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'drop':
                            $this->dropAlias();
                            break;

                        case 'add':
                            $this->makeAlias();
                            break;

                        case 'is':
                            //$this->alias = $this->input;
                            //$this->alias = $this->extractAlias($input);

                            $this->makeAlias($this->alias);
                            //$this->set();
                            return;

                        default:
                        //$this->read();                                                    //echo 'default';
                    }
                }
            }
        }

        // So we know we don't just have a keyword.

        if (isset($this->alias)) {
            //$this->thing->log('Agent "Block" found a run_at and a run_time and made a Block.'$
            // Likely matching a head_code to a uuid.
            $this->makeAlias($this->alias);
            return;
        }

        if ($pieces[0] == "alias") {
            $this->makeAlias($this->input);
            $this->set();
            //$this->alias = "meepmeep";
            return;
        }

        // Guess we check if it's a Place then?

        if ($this->isPlace($input)) {
            $place_thing = new Place($this->thing); // no agent because this now takes message priority
            $this->thing_report['info'] =
                'Agent "Alias" sent the datagram to Place';
            return;
        }

        $this->readAlias();

        return false;
    }
}
