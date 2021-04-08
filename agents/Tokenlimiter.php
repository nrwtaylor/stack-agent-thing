<?php
/**
 * Tokenlimiter.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Tokenlimiter {


    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function __construct(Thing $thing, $agent_input = null) {

        if ($agent_input == null) {$agent_input = "";}
        $this->agent_input = $agent_input;
        $this->thing = $thing;


        // Call the TokenLimiter which will then 'on-call' the service you are requesting.

        $this->tokens = array('red', 'red', 'blue', 'red', 'sms', 'facebook', 'slack', 'email','ntp');

        // Set default rate at 1 per 15 minutes.
        $this->token_window = 30;




        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("tokenlimiter", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("tokenlimiter", "refreshed_at"), $time_string );
        }

        $this->thing->json->setField("variables");
        $tokens = $this->thing->json->readVariable( array("tokenlimiter", "tokens") );


        if ($tokens == false) {


            $this->initTokens();
        } else {$this->tokens = $tokens;}



        $elapsed_time = time() - strtotime($time_string);

        // And so at this point we have a timer model.

        // So created a token_generated_time field.

        if ($elapsed_time > $this->token_window) {
            $this->refreshTokens();
        }


        // Does agent input have a clear token request




        //$this->agent_input $this->tokens
        //  $this->token_request = 'blue';
        $this->token_request = $this->agent_input;

        //$this->thing->log( '<pre> meep </pre>' );

        foreach ($this->tokens as $key=>$token) {
            if ($token == $this->token_request) {

                unset($this->tokens[$key]);


                $this->thing->json->setField("variables");
                $this->thing->json->writeVariable( array("tokenlimiter", "tokens"), $this->tokens );

                //callAgent($this->thing->uuid, $token);
                $c = new Callagent($this->thing);
                $c->callAgent($this->thing->uuid, $token);

                $message = 'Agent "Token Limiter" issued a ' . ucfirst($token) . " Token to Thing " . $this->thing->nuuid . ".";
                $this->thing->log( '<pre> ' . $message . '</pre>' );
                $this->thing_report['token'] = $token;

                return;
            }
        }

        $this->thing_report['token'] = false;

        //$this->thing->json->setField("variables");
        //$this->thing->json->writeVariable( array("tokenlimiter", "tokens"), $this->tokens );

        //             $this->thing->log("<pre>Token issue" . print_r($this->tokens) . "</pre>");

        $this->thing->log( 'Agent "Token Limiter" did not provide a Token.' );

        //Agenthandler::callAgent($uuid, $to = null);
        //callAgent($thing->uuid, $agent_input);

        return;

        $this->test= "Development code";

        $this->thing = $thing;


        // Example
        $this->api_key = $this->thing->container['api']['translink'];



        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;



        $this->node_list = array("token store"=>array("token create"=>array("token use", "token store"), "token use"));

        $this->thing->log( '<pre> Agent "Tokenlimiter" running on Thing ' .  $this->thing->nuuid . '.</pre>');
        $this->thing->log( '<pre> Agent "Tokenlimiter" received this Thing "' .  $this->subject . '".</pre>');

        $this->state = $thing->choice->load('token'); //this might cause problems

        $balance = array('amount'=>0, 'attribute'=>'transferable', 'unit'=>'tokens');
        $t = $this->thing->newAccount($this->uuid, 'token', $balance); //This might be a problem

        $this->thing->account['token']->Credit(1);




        $this->readSubject();
        $this->respond();


        // Err ... making sure the state is saved.
        $this->thing->choice->Choose($this->state);

        // Which means at this point, we have a UUID
        // whether or not the record exists is another question.

        // But we don't need to find, it because the UUID is randomly created.
        // Chance of collision super-super-small.

        // So just return the contents of thing.  false if it doesn't exist.

        $this->state = $thing->choice->load('token');

        return;



    }


    /**
     *
     */
    function initTokens() {

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("tokenlimiter", "refreshed_at"), $this->thing->json->time() );

        $this->tokens = array('red', 'red', 'blue', 'red', 'orange', 'orange', 'sms', 'facebook', 'slack', 'email', 'satoshi', 'satoshi', 'microsoft','ntp');

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("tokenlimiter", "tokens"), $this->tokens );

        return;
    }


    /**
     *
     */
    function refreshTokens() {

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("tokenlimiter", "refreshed_at"), $this->thing->json->time() );

        $this->tokens = array('red', 'red', 'blue', 'red', 'orange', 'orange', 'satoshi');

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("tokenlimiter", "tokens"), $this->tokens );

        return;
    }

    function revokeTokens() {

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("tokenlimiter", "refreshed_at"), $this->thing->json->time() );

        $this->tokens = array();

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("tokenlimiter", "tokens"), $this->tokens );

    }


    /**
     *
     */
    function generateToken() {

    }



    /**
     *
     */
    private function respond() {

        // Thing actions


        $this->thing->flagGreen();

        // Generate email response.

        $to = $this->thing->from;
        $from = "token";

        $choices = $this->thing->choice->makeLinks($this->state);

        $test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
        $test_message .= '<br>Hive state: ' . $this->state . '<br>';

        $this->thing->email->sendGeneric($to, $from, $this->subject, $test_message, $choices);

        $this->thing_report = array('thing' => $this->thing->thing, 'choices' => $choices, 'info' => 'This is a hive state engine.', 'help' => 'Ants.  Lots of ants.');

    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->response = null;

        if ($this->state == null) {



            switch ($this->subject) {
            case "token create":
                $this->create();
                break;
            case "token store":
                break;
            case "token use":
                $this->thing->choice->Choose("token use");

                break;
            default:
                $this->create();
            }


        }


        $this->state = $this->thing->choice->load('token');

        // Will need to develop this to only only valid state changes.

        switch ($this->state) {
        case "token create":
            break;
        case "token store":
            //$this->kill();
            break;
        case "token use":
            //$this->thing->choice->Choose("foraging");

            break;
        case "inside nest":
            //$this->thing->choice->Choose("in nest");
            break;
        case "nest maintenance":
            //$this->thing->choice->Choose("nest maintenance");
            break;
        case "patrolling":
            //$this->thing->choice->Choose("patrolling");
            break;
        case "midden work":
            //$this->thing->choice->Choose("midden work");
            $this->middenwork();

            // Need to figure out how to set flag to red given that respond will then reflag it as green.
            // Can green reflag red?  Think about reset conditions.

            break;
        default:

        }

        $this->thing->choice->Create('token', $this->node_list, $this->state);




        return false;

    }


    /**
     *
     */
    function createToken() {

    }


    /**
     *
     */
    function create() {
        $ant_pheromone['stack'] = 4;

        if ((rand(0, 5) + 1) <= $ant_pheromone['stack']) {
            $this->thing->choice->Create('token', $this->node_list, "inside nest");
        } else {
            $this->thing->choice->Create('token', $this->node_list, "midden work");
        }

        $this->thing->flagGreen();

        return;
    }


    /**
     *
     * @return unknown
     */
    function kill() {
        // No messing about.
        return $this->thing->Forget();
    }



    //function arrayFlatten($array) {
    //        $flattern = array();
    //        foreach ($array as $key => $value){
    //            $new_key = array_keys($value);
    //            $flattern[] = $value[$new_key[0]];
    //        }
    //        return $flattern;
    //}

}


?>
