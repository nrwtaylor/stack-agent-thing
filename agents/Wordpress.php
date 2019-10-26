<?php
/**
 * Wordpress.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

 // require wp-load.php to use built-in WordPress functions

class Wordpress extends Agent
{
    public $var = 'hello';

    /**
     *
     */
    public function init() {
        // So I could call
        $this->test = false;
        if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
        // I think.
        // Instead.

            if (isset($this->thing->container['api']['wordpress']['path_to'])) {
                $this->path_to = $this->thing->container['api']['wordpress']['path_to'];
            }

            if (isset($this->thing->container['api']['wordpress']['user_id'])) {
                $this->user_id = $this->thing->container['api']['wordpress']['user_id'];
            }

        // require wp-load.php to use built-in WordPress functions
        // Set the Wordpress location in settings
        require_once($this->path_to . "wp-load.php");

        $this->thing_report['help'] = 'Communicates with the Wordpress API.';

        $this->node_list = array("wordpress"=>array("wordpress"));



    }


    /**
     *
     */
    public function run() {
//        $this->getPing();
//$this->makePost();
    }

    function test() {
       $test_result = "OK";
       if (!isset($this->response)) {$test_result = "Not OK";}
       $test_result = $this->test_result;
    }

function isPost($text = null) {
if ($text == null) {$text = $this->filtered_input;}
  if (post_exists( $text ) == 0) {return true;}
  return false;

}

// https://www.kickstartcommerce.com/programmatically-create-wordpress-posts-pages-using-php.html
function makePost($text = null) {
if ($text == null) {$text = $this->filtered_input;}
 $postType = 'post'; // set to post or page
 $userID = $this->user_id;; // set to user id
 $categoryID = '2'; // set to category id.
 $postStatus = 'future';  // set to future, draft, or publish
 $leadTitle = $text;
// $leadTitle = 'Post today: '.date("n/d/Y");
 $leadContent = ""; 
  // Time related

 $timeStamp = $minuteCounter = 0;  // set all timers to 0;
 $iCounter = 1; // number use to multiply by minute increment;
 $minuteIncrement = 1; // increment which to increase each post time for future schedule
 $adjustClockMinutes = 0; // add 1 hour or 60 minutes - daylight savings
 
 $minuteCounter = $iCounter * $minuteIncrement; // setting how far out in time to post if future.
 $minuteCounter = $minuteCounter + $adjustClockMinutes; // adjusting for server timezone
 
 $timeStamp = date('Y-m-d H:i:s', strtotime("+$minuteCounter min")); // format needed for WordPress


 // Create Wordpress structured data.

 $new_post = array(
 'post_title' => $leadTitle,
 'post_content' => $leadContent,
 'post_status' => $postStatus,
 'post_date' => $timeStamp,
 'post_author' => $userID,
 'post_type' => $postType,
 'post_category' => array($categoryID)
 );

 // Now do the post to Wordpress.
 
 $post_id = wp_insert_post($new_post);
 
 /*******************************************************
 ** SIMPLE ERROR CHECKING
 *******************************************************/
 
 //$finaltext = '';
 
 if($post_id){
 
 $this->response .= 'Made a new post.';
 
 } else{
 
 $this->response .= 'Did not make a new post.';
 
 }
 

}

    /**
     *
     */
    public function makeSms() {
        $this->sms_message = "WORDPRESS | Content. Autommatic. ";
        $this->sms_message .= $this->response;

//        $this->sms_message .= " | TEXT WATSON";
        $this->thing_report['sms'] = $this->sms_message;
    }


    /**
     *
     */
    public function getWordpress() {
    }


    /**
     *
     */
    public function makeMessage() { 
        $message = "Asked Wordpress.";
        $this->sms_message = $message;
        $this->thing_report['message'] = $message;
    }


    /**
     *
     */
    public function readSubject() {

        $input = strtolower($this->subject);
        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'wordpress') {
                $this->response = "Saw the word Wordpress.";
                return;
            }
        }


        $whatIWant = $this->input;
        if (($pos = strpos(strtolower($this->input), "wordpress is")) !== FALSE) { 
            $whatIWant = substr(strtolower($this->input), $pos+strlen("wordpress is")); 
        } elseif (($pos = strpos(strtolower($this->input), "wordpress")) !== FALSE) { 
            $whatIWant = substr(strtolower($this->input), $pos+strlen("wordpress")); 
        }

        $this->filtered_input = ltrim(strtolower($whatIWant), " ");

        $this->makePost($this->filtered_input);

        $this->response = "Responded to a request about Wordpress.";
    }


}
