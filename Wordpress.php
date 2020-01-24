<?php
namespace Nrwtaylor\StackAgentThing;

use WP_Query;

// Display all errors in production.
// The site must run clean transparent code.
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//ini_set("allow_url_fopen", 1);

// This is written to be understandable.
// Apologies.


class Post extends Agent
{
    public $var = 'hello';


    public function init()
    {
        $this->thing_report['info'] = 'Post did not add anything useful.';
        $this->thing_report['help'] =
            "An agent which updates wordpress posts.";

        $this->thing->log("Initialized post.", "DEBUG");

        // An items list which will hold the current search set.
        $this->items = array();

        // Load in the Post variables
        // There are several paramets to tweak.
        $url = '/home/nick/codebase/gimmu/agents/settings.php';
        $settings = require $url;

        $this->container = new \Slim\Container($settings);

        $this->container['gimmu'] = function ($c) {
            $db = $c['settings']['gimmu'];
            return $db;
        };
        // Allow switched between an all grid.
        // Card and grid view.
        // Or 'prefer grid'
        //        $this->mode = "card and grid";
        //        $this->mode = "allow grouping";
        $this->mode = $this->container['gimmu']['mode'];
        $this->post_cache = $this->container['gimmu']['post_cache'];

    }

    function randomPost()
    {

$args = array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => 1,
    'orderby' => 'rand'
);

$my_random_post = new WP_Query ( $args );
//echo "bar";
while ( $my_random_post->have_posts () ) {
$my_random_post->the_post();
$post = get_post();

$url = get_permalink();
$title = get_the_title();
}
return $post;

//return $title;

}

    function oldestPost()
    {

$args = array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => 1,
    'orderby' => 'modified',
     'order' => 'ASC',
);

$my_random_post = new WP_Query ( $args );
//echo "bar";
while ( $my_random_post->have_posts () ) {
$my_random_post->the_post();
$post = get_post();
$url = get_permalink();
$title = get_the_title();
$modified_time = get_post_modified_time('F d, Y g:i a', true);
//echo $title . " " . $modified_time . "\n";
}
return $post;
//return $title;

}

    function meanPost()
    {

// Lets leave this for a while.
// Requires a big pull of all the posts.
// Which is probably not a great plan.

return true;

$args = array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'orderby' => 'modified',
'nopaging' => true,
     'order' => 'DESC',
);

$my_random_post = new WP_Query ( $args );
//echo "bar";
while ( $my_random_post->have_posts () ) {
$my_random_post->the_post();
$post = get_post();

$url = get_permalink();
$title = get_the_title();
$modified_time =  get_post_modified_time('F d, Y g:i a', true);
//echo $title . " " . $modified_time . "\n";


}
return $title;

}


    function getPost($text = "random")
    {

switch (strtolower($text)) {
    case "random":
$post = $this->randomPost();
//echo "random " . $post;
//exit();
break;
    case "mean":
$post = $this->meanPost();
break;
    case "oldest":
$post = $this->oldestPost();
break;
default:
$post = $this->findPost($text); 

}
//return $post;
//$post = $this->findPost($text); 

$this->post = $post;
        $this->post_title = get_the_title($this->post);
        $this->post_id = get_the_title($this->post);
        $this->post_link = get_page_link($this->post);


return $this->post;

}

public function findPost($text = null) {

/*
$args = array(
    's' => $text,
    'post_type' => 'post',
    'post_status' => 'publish',
    'orderby' => 'modified',
'nopaging' => true,
     'order' => 'DESC',
);
*/

$args = array(
    's' => $text,
    'post_type' => 'post',
    'orderby' => 'modified',
'nopaging' => true,
     'order' => 'DESC',
);


$my_random_post = new WP_Query ( $args );
//echo "bar";
while ( $my_random_post->have_posts () ) {
$my_random_post->the_post();
$post = get_post();

$url = get_permalink();
$title = get_the_title();
$modified_time =  get_post_modified_time('F d, Y g:i a', true);

//echo $title . " " . $modified_time . "\n";

return $post;

}
return true;


}

    public function run()
    {
// Don't run anything.
return;
    }


public function updatePost($text, $user_id = 8) {

$this->thing->log("post agent");
//if (!isset($this->post)) {return true;}

/*
https://codex.wordpress.org/Function_Reference/wp_update_post
Caution - Infinite loop
When executed by an action hooked into save_post (e.g. a custom metabox), wp_update_post() has the potential to create an infinite loop. This happens because (1) wp_update_post() results in save_post being fired and (2) save_post is called twice when revisions are enabled (first when creating the revision, then when updating the original post—resulting in the creation of endless revisions).
*/


// Update post 37
  $my_post = array(
      'ID'           => get_the_id($this->post),
      'post_title'   => get_the_title($this->post),
      'post_content' => $text,
    'post_author' => $user_id
  );

// Update the post into the database
//  wp_update_post( $my_post );
//Processing $wp_error

//If your updates are not working, there could be an error. It is a good idea to set $wp_error to true and display the error immediately after.

//<?php
// Of course, this should be done in an development environment only and commented out or removed after deploying to your production site.

$post_id = wp_update_post( $my_post, true );

if (is_wp_error($post_id)) {
	$errors = $post_id->get_error_messages();
	foreach ($errors as $error) {
//		echo $error;
$this->thing->log($error);
	}
}

$this->thing->log($post_id);
$this->thing->log("Did the post get updated");

}


    // very much dev
    function getPosts()
    {
return;
        $this->tilecode_list = array();
        $this->tilename_list = array();
        $this->tiles = array();

        // See if a headcode record exists.
        //$findagent_thing = new FindAgent($this->thing, 'tile 999');
$thing_report = $this->thing->db->setFrom("gimmu");

$thing_report = $this->thing->db->agentSearch("tile",999);
        $things = $thing_report["things"];
        $count = count($things);
        $this->thing->log('found ' . $count ." tile Things." );

//$things = $findagent_thing->thing_report['things'];

foreach($things as $i=>$thing) {

$variables_json = $thing['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);
if (!isset($variables['tile'])) {continue;}
$tile = $variables['tile'];
$this->tiles[] = $tile;

}
return;
//exit();

        if ( ($findagent_thing->thing_report['things'] == true)) {}

        if (!$this->thing->isPositive($count))
        {
            // No places found
        } else {

        $this->thing->log('adding tile Things to a list.' );


            foreach (array_reverse($things) as $thing_object)
            {
                $uuid = $thing_object['uuid'];
                $variables = $this->thing->json->jsontoArray($thing_object['variables']);
                $message0 = $this->thing->json->jsontoArray($thing_object['message0']);


    $tile = "X";
$tiles = array();
switch (true) {
    case (isset($variables['msg']['agent_input'])):
        $tiles[] = $variables['msg']['agent_input'];
    case (isset($message0['msg']['agent_input'])):
        $tiles[] = $message0['msg']['agent_input'];
    case (isset($variables['tile'])):
        $tiles[] = $variables['tile'];
}
$latest_time = 0;
foreach($tiles as $i=>$temp_tile) {


    if (isset($temp_tile['time'])) {
if (strtotime($temp_tile['time']) > $latest_time) {

$tile = $temp_tile;

}

}
}

if (!isset($tile['time'])) {continue;}

                $tile_code = $this->default_tile_code;
                $tile_name = $this->default_tile_name;
                $refreshed_at = $this->thing->time();

$tile_name = $thing_object['task'];

                if(isset($tile['tile_code'])) {$tile_code = $tile['tile_code'];}
                if(isset($tile['tile_name'])) {$tile_name = $tile['tile_name'];}
//                if(isset($tile['time'])) {$refreshed_at = $tile['time'];}

                $timestamp = $tile["time"];

                $hours = 1e99; // Turn it off. Like a light switch.
                $age = strtotime($this->thing->time) - strtotime($timestamp);

                if ($age > (60 * 60 * $hours)) {continue;} // Over 6 hours old.

//                $tile = array("source"=>"tile cache", "uuid"=> $uuid,  "price"=>$price, "timestamp"=>$timestamp, "title"=>$title, "link"=>$link, "thumbnail"=>$thumbnail, "score"=>$score, "id"=>$service_id, "age"=>$age, "place"=>$place); 
                $this->tiles[] = $tile; 




                $this->tilecode_list[] = $tile_code;
                $this->tilename_list[] = $tile_name;
                //}
            }
        }

        // Indexing not implemented
        $this->max_index = 0;
        return array($this->tilecode_list, $this->tilename_list, $this->tiles);
    }



    public function makeResponse()
    {
        // This is a short simple structured response.
        if (!isset($this->response)) {$this->response = "";}
        $this->response .= 'Did something post related.';
    }


/*
public function selectTiles($text) {
return;
$this->matching_tiles = null;	

if ((!isset($this->tiles)) or ($this->tiles == array())) {$this->getTiles();}

$text_words = explode(" " , strtolower($text));


foreach($this->tiles as $i=>$tile) {

if (!isset($tile['title'])) {return;}
$tile_title = $tile['title'];

$tile_words = explode(" " , strtolower($tile_title));
//$text_words = explode(" " , strtolower($text));
$count = 0;
foreach($tile_words as $j=>$tile_word) {
foreach($text_words as $k=>$text_word) {
//echo $tile_word . " " . $text_word . "<br>";
if (strtolower($tile_word) == strtolower($text_word)) {$count +=1;}
//echo $tile_word ." " . $text_word . "<br>";

}
}

if ($count >= 3) {
$this->matching_tiles[$tile['id']] = $tile;}

}

}
*/

function setPost($post = null) {
//if ($tile == null) {return;}
$post['refreshed_at'] = $this->thing->time();

//if ($this->source == "gimmu") {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            array("post"),
            $post
        );

}


public function make() {}


    public function doPostTile($text = null)
    { // Do your post tile stuff here.
    }

    public function set()
    {
        // Log which agent gimmu requested ie Ebay.
        // And note the time.

        if (isset($this->post)) {
            $this->setPost($this->post);
        }

//        $time_string = $this->thing->json->time();
//        $this->thing->json->writeVariable(
//            array("tile", "refreshed_at"),
//            $time_string
//        );

        $this->thing->log("Set tile.");
    }

    public function readSubject()
    {

        if ($this->input == "post") {

//            $this->postGet();
            $this->getPost();

        }

        //echo "Post called";
        if ($this->post_cache == "off") {return;}

            $this->thing->log("Read subject. Post cache is off.", "DEBUG");
        }
}
