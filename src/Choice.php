<?php
/**
 * Choice.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;
use RecursiveIteratorIterator;
use RecursiveArrayIterator;

ini_set("allow_url_fopen", 1);

class Choice {

    public $var = 'hello';


    /**
     *
     * @param unknown $uuid
     * @return unknown
     */
    function __construct($uuid, $nom_from = null) {

        $this->json = new Json($uuid);

        $this->uuid = $uuid;

        $this->from = $nom_from;

        // Access state settings as required.
        $settings = require $GLOBALS['stack_path'] . "private/settings.php";

        $this->container = new \Slim\Container($settings);

        $this->container['stack'] = function ($c) {
            $db = $c['settings']['stack'];
            return $db;
        };

        if (!isset($_SERVER['HTTP_HOST'])) {
            $this->web_prefix = $this->container['stack']['web_prefix'];

        } else {
            $this->web_prefix = "http://$_SERVER[HTTP_HOST]/";
        }

        $prod = true;
        if ($prod == true) {
            $this->web_prefix = $this->container['stack']['web_prefix'];
        }

        $this->stack_state = $this->container['stack']['state'];

        $this->choice_association = 'off';
        if (isset($this->container['stack']['choice_association'])) {
            $this->choice_association = $this->container['stack']['choice_association'];
        }



        // Watch for issues in test.
        //  $this->state = true;

        // Set default choice to be 'hive'
        // Overwritten when a choice is created.
        $this->name = 'hive';
    }


    /**
     *
     * @param unknown $choice_name  (optional)
     * @param unknown $node_list    (optional)
     * @param unknown $current_node (optional)
     */
    function Create($choice_name = null , $node_list = null, $current_node = null) {
        $this->split_time = microtime(true);

        // So we want to enable the creation of a default state on a choice
        // call.  Leaving Things null means leaving their intent unclear.

        // So establish a 'hive' and ant states by default.

        if ($choice_name == null) {
            $this->name = 'hive';
        } else {
            $this->name = $choice_name;
        }

        // Creates a choice tree in settings and places
        // Re: Elizabeth Gordon these are the
        // are 'inside nest' and 'midden worker'.

        // Move towards a non-Markov state description.

        if ($node_list == null) {
            $this->node_list = array("inside nest"=>
                array("nest maintenance"=>
                    array("patrolling"=>"foraging", "foraging")),
                array("midden work"=>"foraging"));
        } else {
            $this->node_list = $node_list;
        }

        // Place the Thing [uuid] at default position in tree.
        if ($current_node == null) {

            // Load the last known Markov information about this state.
            $this->current_node = $this->load($this->name);

            // Really?  It still is not found?
            if ($this->current_node == null) {

                // Stochastically assign a starting position.
                // Here the two possible states for ants (re: Elizabeth Gordon)
                // are 'inside nest' and 'midden worker'.

                // Get a stack setting here.
                // Pheromone associated with stacking.  How many ants
                // are going to be hanging around at the hive.

                // Decided on a D6 roll.  Ant pheromone can be any
                // number (ie > 6).

                // In this case assign 4, to give 4/6 likelihood that
                // the current node's Markov state will be called 'inside nest'.
                // The Thing will instantiate as 'inside nest' 66.6%
                // of the time.

                $ant_pheromone['stack'] = 4;

                if ((rand(0, 5) + 1) <= $ant_pheromone['stack']) {
                    $this->current_node = "inside nest";
                } else {
                    $this->current_node = "midden work";
                }
            }
        } else {
            $this->current_node = $current_node;
        }

        $this->saveStateMap($this->node_list);

        $this->save($this->current_node, $this->name);
        //        echo "Choice->Create." . number_format(round( (microtime(true) - $this->split_time)*1000 )) . "ms"; echo "<br>";


        // format:
        // {"<34 chars>":{"choices":["Red Team", "Blue Team"], "decision":null}
    }


    // Functions follow to manage the state map and naming and
    // correspondence to $uuid.

    // Lots of echo statement's I'm afraid until I figure out the elegant
    // way to do that.


    /**
     *
     * @return unknown
     */
    function loadStateMap() {
        // A statemap can be saved in any alphanumeric field.
        // So message0-7 and settings and variables.  As well as the from, to, subject, uuid.

        // Thinking ahead to text overwrite of fields (ie ...nomnonnomnonnom...)
        // And this will require a similar reference by field.  But will
        // have to call a text or a json class.

        // Since Thing has control of which, this is satisfactory in terms of
        // over-writes of the $uuid record.

        $this->json->setField("settings");

        // Pretty hacky here with the 0.  This is because of how
        // PHP does the json -> php array conversion and back.
        // Seems to work consistently.  So working on it being the
        // simplest solution.  For now.
        $this->node_list = $this->json->readVariable(array("choice", $this->name, 0));

        return $this->node_list;
    }


    /**
     *
     * @param unknown $state_map (optional)
     * @return unknown
     */
    function saveStateMap($state_map = null) {
        // Settings communicates throught the settings json
        // what settings the Thing can be set to.

        if ($state_map == null) {$state_map = $this->node_list;}

        $this->json->setField("settings");
        $this->json->writeVariable(array("choice", $this->name), array($state_map));
        $this->node_list = $state_map;

        return true;
    }


    /**
     *
     * @param unknown $variable (optional)
     * @return unknown
     */
    function load($variable = null) {
        // Provides a general load function for the variables field only.
        // Allows a null setting which loads the name given to this choice.

        // But provides a more general call available to Things.

        // In the null case, this loads up a pathed variable from the Json
        // variables.  Here as \uuid\<$variable>

        if ($variable == null) {$variable = $this->name;}

        $this->json->setField("variables");
        $this->current_node = $this->json->readVariable(array($this->uuid, $variable));

        // If the variable is not found return false.  Otherwise, return
        // the found state.
        if ($this->current_node == null) {return false;}

        return $this->current_node;
    }


    /**
     *
     * @param unknown $value
     * @param unknown $variable (optional)
     */
    function save($value, $variable = null) {
        // Save is a generic function to save a particular value to a variable

        // Similarly.  Save function to save as \uuid\<$variable>\<$value>

        if ($variable == null) {$variable = $this->name;}

        $this->json->setField("variables");
        $this->json->writeVariable(array($this->uuid, $variable), $value);
    }


    /**
     *
     * @return unknown
     */
    function loadDecision() {
        // Legacy.  To factor out.
        return $this->load('decision');
    }


    /**
     *
     * @param unknown $decision
     * @return unknown
     */
    function saveDecision($decision) {
        // Legacy.  To factor out.
        return $this->save($decision, 'decision');
    }


    /**
     *
     * @param unknown $choice
     */
    function saveChoices($choice) {

        // Deprecated.  I took the decision to re-factor the decisions
        // and choices conceptualization as 'choice' and a name for the
        // 'choice'.  A name for a choice can now be 'decision'.

        // Testing things out before dev deprecate.

        throw Exception("devstack deprecate");

        if ($choice == null) {
            $choice_list = null;
        } else {
            $choice_list = $this->getChoices($choice);
        }

        $this->save($choice_list, 'choice_list');

    }


    /**
     *
     * @param unknown $choice
     * @return unknown
     */
    function Choose($choice) {

        // Make a choice.
        // Create a new Thing with the results of the choice.
        $this->current_node = $choice;
        //$message = $this->saveDecision($choice);

        // Save it in the Json variables as the choice with
        // the name of the choice. /<$choice>/<$name assigned to the choice>

        // Both are descriptive fields ie save('this one','list of choices')

        $message = $this->save($choice, $this->name);

        // Watch for this being an issue in test.
        //$choice_thing->flagGreen();

        return "Thing instruction: choice ".$this->current_node. ' '. $message . ' '.time();

    }


    /**
     *
     * @param unknown $choice
     * @return unknown
     */
    function makeChoice($choice) {
        // Make a choice.
        // Create a new Thing with the results of the choice.

        // Here we establish the records that each choice points towards.
        // It requires creating a null Thing and populating it.

        // I choose to call these ants.  The state map is a relatively
        // generic one to test the full range of stack capabilities.

        $choice_thing = new Thing(null); // This times at 2ms.
        // And at 3, 3, 3, 5 ms on 7 June 2018

        // This takes 6s.
        // As of 27 Feb 2018 - 2,942ms 1169ms, 1134ms
        $this->split_time = microtime(true);

        $from = null;

        if ( (isset($this->choice_association)) and ($this->choice_association == 'random') ) {
            $from = $choice_thing->getUUid();
        }

        if ( (isset($this->choice_association)) and ($this->choice_association == 'from') ) {
            $from = $this->from;

            if ($this->from == null) {

                $from = $choice_thing->getUuid();

            }

        }

        $choice_thing->Create($from, "choice", 's/ is ' . $choice . ' button');
        // Timing at 199, 173, 368, 201ms
        //echo number_format(round( (microtime(true) - $this->split_time)*1000 )) . "ms"; echo "<br>";

        $choice_thing->choice->Create($this->name, $this->node_list, $choice);

        // Write state forward to newly created Thing.
        // [I think 'choice_list' can safely be re-named ant.]

        $choice_thing->choice->saveStateMap($this->node_list);



        $choice_thing->choice->save($choice, 'choice_list');


        // Then flag the Thing green (which I think is the default state
        // out of Thing).
        $choice_thing->flagGreen();

        return $choice_thing->uuid;

    }


    /**
     *
     * @return unknown
     */
    function makeChoices() {
        $choice_uuid_array = array();
        $choices = $this->getChoices($this->current_node);

        if ($choices == null) {return false;}

        foreach ($choices as $choice) {

            $uuid = $this->makeChoice($choice);


            if (strtolower($choice) == 'forget') {
                $uuid = $this->uuid; //override with the Things uuid.
            }

            $choice_uuid_array[] = array("uuid"=>$uuid, "choice"=>$choice);
        }
        return $choice_uuid_array;
    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function alphanumeric($input) {
        $value = preg_replace("/[^a-zA-Z0-9]+/", "", $input);
        $value = substr($value, 0, 34);
        return $value;
    }


    /**
     *
     * @param unknown $state
     * @return unknown
     */
    function isValidState($state) {
        // Valid states for this Thing
        //  $valid_states = $this->validStates();
        $valid_states = $this->getChoices();

        if ($valid_states == null) {return false;}
        if ($valid_states == false) {return false;}
        if (is_string($valid_states)) {$valid_states=array($valid_states);}

        $authorized = false;

        foreach ($valid_states as $key=>$value) {
            $value = $this->alphanumeric($value);

            if ($value == $state) {
                //echo "Authorized" . $state;
                $authorized = true;
            } else {
                //echo "Unauthorized" . $test_message;
            }
        }

        if ($authorized == false) { return false;}
        return true;
    }





    /**
     *
     * @param unknown $state (optional)
     * @return unknown
     */
    function makeLinks($state = null) {
        //$start_time = microtime(true);
        $this->split_time = microtime(true);

        if ($state == null) {
            $state = $this->loadDecision();
            //   $k = rand(0,count($this->validStates()));
            //   $state=$this->validStates()[$k];
        }

        $this->load($this->name);

        // Not sure why I am saving the decision here
        $this->save($state, $this->name);

        $words = null;
        $urls = null;
        $html_links = null;
        $html_buttons = null;
        $links = null;

        $node_list = $this->loadStateMap();

        // It is a valid state, so write the state to the variables.
        $this->Create($this->name, $node_list, $state);


        $this->split_time = microtime(true);

        $choice_list = $this->makeChoices(); //25-26s // As of 27 Feb - 3,857 ms
        // As of 7 June 2018 897ms to 1,116ms on stackr.test

        //echo number_format(round( (microtime(true) - $this->split_time)*1000 )) . "ms"; echo "<br>";

        if ($choice_list != false) {

            foreach ($choice_list as $item) {
                //$url = "http://project-stack.dev:8080/public/thing/".$item['uuid']."/".$this->alphanumeric($item['choice']);
                $word = $item['choice'];

                $slug_agent = new Slug(null, "slug");
                $choice_slug = $slug_agent->getSlug($item['choice']);

                $url = $this->web_prefix . "thing/".$item['uuid']."/".rawurlencode($choice_slug);

                if ($choice_slug == "terms-of-use") {$url = $this->web_prefix . rawurlencode($choice_slug);}
                if ($choice_slug == "privacy") {$url = $this->web_prefix . rawurlencode($choice_slug);}


                $pos = strrpos($url, '/');
                $to = $pos === false ? $url : substr($url, $pos + 1);

                //$word = ucfirst($item['choice']);
                $word = str_replace("-", " ",$word);
                $word = ucwords($word);

                $html = '<a href="' . $url . '"><div class="choice-button">' . $word .'</div></a>';

                $words[] = $word;
                $links[] = $url;

                $html_links .= $html . " ";

                $urls .= $url . " /r/n";
                $html_buttons .= $this->makeButton($url, $word);

            }
            //  } else {
            //   $test_message = "No choices";
        }

        //echo number_format((round(microtime(true) - $this->ref_time)*1000)) . "ms"; echo "<br>";


        //$buttons = quoted_printable_decode($this->testhtml());
/*
        $html_button_set =

            '<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tbody>
		<tr>
			<td colspan="3" valign="top" width="15" height="10">
			</td>
		</tr>
		<tr style="line-height: 0">
			<td>
				<table style="border-spacing: 14px 0px">
					<tr>
						' . $html_buttons . '
</tr>
				</table>
			</td>
		</tr>

		<tr>
			<td colspan="3" valign="top" width="15" height="10">
			</td>
		</tr>
	</tbody>
</table>';
*/
        $html_button_set =

            '<div class="choice-button-set">
                        ' . $html_buttons . '
             </div>';



        //$html_buttons
        //echo quoted_printable_decode($html_button_set);



        $links = array("words"=>$words, "links"=>$links, "url"=>$urls, "link"=>$html_links, "button"=>$html_button_set);

        //return $html_links;

        //echo number_format((round(microtime(true) - $this->ref_time)*1000)) . "ms"; echo "<br>";

        return $links;
    }


    /**
     *
     * @param unknown $url_link
     * @param unknown $word     (optional)
     * @return unknown
     */
    function makeButton($url_link, $word = null) {
        $html_button = '
			<td style="background-color: #0066dd; font-family: \'Helvetica Neue\',Arial,sans-serif;
			font-size: 14px;
			line-height: 18px; padding-left: 7px; padding-right: 7px;
			padding-top: 4px;
			padding-bottom: 4px;
			margin-left: 18px;
			margin-right: 18px;
			font-weight: bold;
			box-shadow: 0px 0px 2px 0 rgb(0.18, 0.18, 0.18);
			border-radius: 0px;
			background: #719e40;">

			<a style="text-decoration: none; color: white;" href="'.
            $url_link . '"> '.  ''. $word . ''.' </a>
			</td>';

        $html_button = '<a href="'.
            $url_link . '"><div class= "choice-button"> '.  ''. $word . ''.' </div></a>
            ';


        return $html_button;

    }



    /**
     *
     * @param unknown $query_node (optional)
     * @return unknown
     */
    function getChoices($query_node = null) {

        // Search through the map of Markov states and return the prior(?)
        // choices based on the current state.

        // Forget is always a choice.
        $message[] = "forget";
        $message = array();

        // $this->current_node is inconsistently set.
        // Need to track this down.  This is place to re-factor to
        // remove this db call.
        if ($query_node == null) {
            //   $query_node = $this->current_node;
            $query_node = $this->load($this->name);
        }

        // If $query_node is still null then the state is undefined.
        if ($query_node == null) {
            $query_node = 'start';
            // start is alway undefined in array to allows stochastic
            // assignment of multiple entry points.
            // if start is set it ?.
        }

        // echo '<pre> thingtest.php $query_node: '; print_r($query_node); echo '</pre>';
        // echo '<pre> thingtest.php $this->node_list: '; print_r($this->node_list); echo '</pre>';

        $found = $this->recursiveFind($this->node_list, $query_node);
        if (is_array($found)) {
            $message = array_merge($message, $found);
        } else {
            // Don't do anything.
        }

        // Unsubscribe is also always a choice.  Let's make it easy,
        // but only a required choice for emails.
        //$message[] = "[email]unsubscribe";
        // Not yet implemented.

        return $message;
    }


    /**
     *
     * @param array   $array
     * @param unknown $needle
     * @return unknown
     */
    function recursiveFind(array $array, $needle) {

        // Generalized needle in haystack with RecursiveArrayIterator
        // by others.

        $iterator  = new RecursiveArrayIterator($array);
        $recursive = new RecursiveIteratorIterator(
            $iterator,
            RecursiveIteratorIterator::SELF_FIRST
        );

        if (is_string($needle)) {
            $needle = $this->alphanumeric($needle);
        }

        foreach ($recursive as $key => $value) {
            if ($this->alphanumeric($key) === $needle) {
                $choices = array();
                if (is_array($value)) {
                    foreach ($value as $child_key=>$child_value) {
                        if (is_numeric($child_key)) {
                            $choices[] = $child_value;
                        } else {
                            $choices[] = $child_key;
                        }
                    }
                    return $choices;
                }

                if (is_string($value)) {return array($value);}
            }

            if ($value === $needle) {return array();}
        }
    }
}
