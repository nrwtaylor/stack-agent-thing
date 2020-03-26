<?php
namespace Nrwtaylor\StackAgentThing;

// Display all errors in production.
// The site must run clean transparent code.
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//ini_set("allow_url_fopen", 1);

// This is written to be understandable.
// Apologies.


class Text extends Agent
{
    public $var = 'hello';

    public function getNgrams($input, $n = 3) {
if (!isset($this->ngrams)) {$this->ngrams = array();}
        $words = explode(' ', $input);
        $ngrams = array();

        foreach ($words as $key=>$value) {

            if ($key < count($words) - ($n - 1)) {
                $ngram = "";
                for ($i = 0; $i < $n; $i++) {
                    $ngram .= " " . $words[$key + $i];
                }
                $ngrams[] = trim($this->trimAlpha($ngram));
            }
        }


        return $ngrams;
    }



    public function trimAlpha($text) {
$letters = array();
$new_text = "";
$flag = false;
foreach(range(0, mb_strlen($text)) as $i) {

$letter = substr($text,$i,1);
//if (ctype_alpha($letter)) {$flag = true;}
if (ctype_alnum($letter)) {$flag = true;}


//if ((!ctype_alpha($letter)) and ($flag == false)) {$letter = "";}
if ((!ctype_alnum($letter)) and ($flag == false)) {$letter = "";}

$letters[] = $letter;

}

//$text = $new_text;

$new_text = "";
$flag = false;
foreach(array_reverse($letters) as $i=>$letter) {

//$letter = substr($text,$i,1);
//if (ctype_alpha($letter)) {$flag = true;}
if (ctype_alnum($letter)) {$flag = true;}


//if ((!ctype_alpha($letter)) and ($flag == false)) {$letter = "";}
if ((!ctype_alnum($letter)) and ($flag == false)) {$letter = "";}

$n = count($letters) - $i -1;

$letters[$n] = $letter;

}
$new_text = implode("",$letters);

return $new_text;




    }




    public function init()
    {

        $this->node_list = array("start" => array("helpful", "useful"));

        $this->thing_report['info'] = 'Text did not add anything useful.';
        $this->thing_report['help'] =
            "An agent which provides search insight. Click on a button.";

        $this->thing->log("Initialized Text.", "DEBUG");

    }

    public function postfixText($text = null, $post_fix = null, $allowed_length = 64, $part_tokens = false) {
if ($text == null) {return true;}
$text = trim($text);
if ($post_fix == null) {$post_fix = "";}


$tokens = explode(" " , $text);



$new_text = trim(substr($text, 0, $allowed_length - mb_strlen($post_fix)));

$tokens_new = explode(" ", $new_text);
$last_index = count($tokens_new) - 1 ;
if ($tokens_new[$last_index] != $tokens[$last_index]) {

$tokens_new[$last_index] = "";

}
$new_text = trim(implode(" ", $tokens_new)) . $post_fix;

//$wp->text_agent->postfixText($capt, $post_fix, $allowed_length);
return $new_text;

    }

    function extractCodes($input = null)
    {
        if (is_array($input)) {
            return true;
        }
        $tokens = explode(
            ' ',
            str_replace(
                array(',', '*', '(', ')', '[', ']', '!', '&', 'and', '.', '-'),
                ' ',
                $input
            )
        );

        $codes = array();

        //     if (!isset($words) or count($words) == 0) {return $ngrams;}

        // Rare for a model to not have a number.
        // And if it doesn't it should be picked up as an ngram.

        foreach ($tokens as $key => $token) {
            //if(1 === preg_match('~[A-Z][0-9]~', strtolower($value))){
            //    $codes[] = $value;
            //}

            if (
                preg_match('/[A-Za-z]/', $token) &&
                preg_match('/[0-9]/', $token)
            ) {
                $codes[] = $token;
            }
        }
        $this->codes = $codes;
        return $this->codes;
    }


    function extractNumbers($input = null)
    {
// Numbers as text.
// Vs agent number.

        if (is_array($input)) {
            return true;
        }
        $tokens = explode(
            ' ',
            str_replace(
                array(',', '*', '(', ')', '[', ']', '!', '&', 'and', '.', '-'),
                ' ',
                $input
            )
        );

        $numbers = array();

        //     if (!isset($words) or count($words) == 0) {return $ngrams;}

        // Rare for a model to not have a number.
        // And if it doesn't it should be picked up as an ngram.

        foreach ($tokens as $key => $token) {
            //if(1 === preg_match('~[A-Z][0-9]~', strtolower($value))){
            //    $codes[] = $value;
            //}

//            if (
//                preg_match('/[A-Za-z]/', $token) &&
//                preg_match('/[0-9]/', $token)
//            ) {

            if (
//                preg_match('/[0-9]/', $token)
is_numeric($token)
            ) {


                $numbers[] = $token;
            }
        }
        $this->numbers = $numbers;
        return $this->numbers;
    }

    function extractHyphenates($input = null)
    {
        if (is_array($input)) {
            return true;
        }
        $tokens = explode(
            ' ',
            str_replace(
                array(',', '*', '(', ')', '[', ']', '!', '&', 'and', '.'),
                ' ',
                $input
            )
        );
        $hyphens = array();

        //     if (!isset($words) or count($words) == 0) {return $ngrams;}

        // Rare for a model to not have a number.
        // And if it doesn't it should be picked up as an ngram.

        foreach ($tokens as $key => $token) {
            //if(1 === preg_match('~[A-Z][0-9]~', strtolower($value))){
            //    $codes[] = $value;
            //}

//            if (
//                preg_match('/[A-Za-z]/', $token) &&
//                preg_match('/[0-9]/', $token)
//            ) {

//            if (
//                preg_match('/[A-Za-z]/', $token) &&
//                preg_match('/[0-9]/', $token)
//            ) {
//                $hyphens[] = $token;
//            }

            if (
preg_match('/^[^\W-]+-[^\W-]+$/', $token)
            ) {


                $hyphens[] = $token;
            }


        }
        $this->hyphenates = $hyphens;
        return $this->hyphenates;
    }


    public function run()
    {
        $this->doText();
    }

    public function makeResponse()
    {
        // This is a short simple structured response.
        if (!isset($this->response)) {$this->response = "";}
        $this->response .= 'Asked about,"' . $this->subject . '"' . '. ';
    }


public function textN3($input) {

$p_array = explode(" ",$input);
$text = "";
foreach($p_array as $i=>$word) {
if ($i >= 3) {break;}
$text .= $word ." ";

}
$text = trim($text);
return $text;
}

public function textNouns($input) {

global $wp;
if (!isset($wp->brilltagger_agent)) {
$wp->brilltagger_agent = new Brilltagger($this->thing, "brilltagger");
}

$tags = $wp->brilltagger_agent->tag($input);


//$word_agent = new Word($this->thing, "word");
//$tags = $wp->brilltagger_agent->tags;
$text = "";
foreach ($tags as $index=>$tag) {


if (is_numeric($tag["token"])) {continue;}

if(1 === preg_match('~[0-9]~', $tag["token"])){
continue;
}
$token = $tag["token"];


// False. Is not a word.
//$nearest_word = $word_agent->isWord($token);

//echo $token ." " . $nearest_word . "<br>";
//if ($nearest_word == false) {continue;}

 if (strpos($tag["tag"], 'VB') !== false) {
        $text .= $tag["token"]. " ";
continue;
    }



 if (strpos($tag["tag"], 'JJ') !== false) {
        $text .= $tag["token"]. " ";
continue;
    }




    if (strpos($tag["tag"], 'NN') !== false) {
        $text .= $tag["token"]. " ";
continue;
    }


}

$text = trim($text);
$this->thing->log('text adjectives and nouns built query, "'. $text .'".');
return $text;

}

function textOr($input) {

$text = "(" . trim($input). ")";
$text = str_replace(" ", ",", $text);

$text = trim($text);
// Any words
return $text;

}

public function compressText($text1, $text2) {
$raw = $text1 . " " . $text2;
$raw = strtolower($raw);
$filtered = implode(' ', array_unique(explode(' ', $raw)));
return $filtered;

}


function textNgram($input, $t = "@1") {

$text = "(" . trim($input). ")";
$text = str_replace(" ", ",", $text);

$text = $t . " ". trim($text);
// Any words
return $text;

}

public function posText($text = null, $pattern = "mixed-adjective") {

// dev stack

if ($text == null) {return true;}


$processed_text = $this->tagText($text);
$pattern_tokens = explode("-", $pattern);
$process_text_tokens = explode("-", $pattern);

foreach($pattern_tokens as $i=>$pattern_token) {
}


}

public function tagText($text = null) {

global $wp;
if ($text == null) {return false;}
if (!isset($wp->brilltagger_agent)) {
$wp->brilltagger_agent = new Brilltagger(null, "brilltagger");
}

if (!isset($wp->mixed_agent)) {
$wp->mixed_agent = new Mixed(null, "brilltagger");
}

if (!isset($wp->alpha_agent)) {
$wp->alpha_agent = new Alpha(null, "alpha");
}


$tags = $wp->brilltagger_agent->tag($text);

/*
foreach($tags as $i=>$token_tag) {

$tag= $token_tag['tag'];
$token = $token_tag['token'];
echo $token . "  " . $tag  . "<br>";

}
*/

// --- now it gets tricky.
// https://cs.uwaterloo.ca/~jimmylin/downloads/brill-javadoc/edu/mit/csail/brill/BrillTagger.html

$arr = array("adjective"=>array('JJ', 'JJR', 'JJS'),
"noun"=>array('NN','NNS','NNP','NNPS'),
"pronoun"=>array('PRP','PRPS','WP'),
"verb"=>array('VB','VBD','VBG','VBN','VBP','VBZ'),
"adverb"=>array('RB','RBR','RBS','WRB'),
"preposition"=>array('IN')

);

//$pattern_tokens = explode("-", $pattern);

$processed_text = "";
foreach($tags as $i=>$token_tag){

$tag = $tags[$i]['tag'];
$token = $tags[$i]['token'];
/*
if (!isset($tags[$i]['pos'])) {$tags[$i]['pos'] = "X";}
if ($wp->alpha_agent->isAlpha($token)) {$tags[$i]['pos']= 'alpha';}
if (in_array($tag,$arr)) {$tags[$i]['pos']= 'adjective';}

if ($wp->mixed_agent->isMixed($token)) {$tags[$i]['pos']= 'mixed';}
*/

switch (true) {
    case ($wp->mixed_agent->isMixed($token)):
        $tags[$i]['pos']= 'mixed';
       break;
    case (is_numeric($token)):
        $tags[$i]['pos']= 'numeric'; 
        break;
    case ($wp->alpha_agent->isAlpha($token)):
        if (in_array($tag,$arr['adjective'])) {$tags[$i]['pos']= 'adjective';} else
        {$tags[$i]['pos']= 'alpha';}

        break;
    case (!isset($tags[$i]['pos'])):
    default:
        $tags[$i]['pos'] = "X";
        break;
}



$processed_text .= "-" . $tags[$i]['pos'];
}
$processed_text = trim($processed_text,"-");



return $processed_text;

}






public function make() {}

    public function doText($text = null)
    {
}


    public function set()
    {
        // Log which agent was requested ie Ebay.
        // And note the time.

        $time_string = $this->thing->json->time();
        $this->thing->json->writeVariable(
            array("text", "refreshed_at"),
            $time_string
        );


/// ?
//$place_agent thing = new Place($this->thing, $ngram);


        $this->thing->log("Set text refreshed_at.");
    }

    public function readTitle($post_title)
    {
        global $wp;

        $codes = $this->extractCodes($post_title);
        $numbers = $this->extractNumbers($post_title);
        $hyphenates = $wp->text_agent->extractHyphenates($post_title);

$alpha_agent = new Alpha($this->thing,"alpha");
$mixed_agent = new Mixed($this->thing,"mixed");
$word_agent = new Word($this->thing, "word");
$brilltagger_agent = new Brilltagger($this->thing,"brilltagger");
$slug_agent = new Slug($this->thing, "slug");
$singular_agent = new Singular($this->thing, "singular");

$alphas = $alpha_agent->extractAlphas($post_title);
$mixeds = $mixed_agent->extractMixeds($post_title);


        $words = $word_agent->extractWords($post_title);
        $notwords = $word_agent->notwords;

        $t = "";

        $tags = $brilltagger_agent->tag($post_title);

        $tokens = $slug_agent->getSlug($post_title);
        $p = "";
        foreach ($tags as $i => $tag) {
            $token = $tag['token'];

            if (strpos($tag['tag'], 'NNS') !== false) {
                $token = $singular_agent->singularize($token);
            }

            $p .= $token . " ";

     }

        $p = trim($p);
        $post_title = $p;


        $adjectives = "";
        $nouns = "";

        foreach ($tags as $i => $tag) {
            if ($tag['tag'] == "JJ") {
                $adjectives .= $tag['token'] . " ";
            }

            if (strpos($tag['tag'], 'NN') !== false) {
                $nouns .= $tag['token'] . " ";
            }
        }

        $adjectives = trim($adjectives);
        $nouns = trim($nouns);

$processed_text = array("adjectives"=>$adjectives,
"nouns"=>$nouns,
"codes"=>$codes,
"alphas"=>$alphas,
"mixed"=>$mixeds,
"words"=>$words,
"notwords"=>$notwords);
    }








public function readSubject() {

if ($this->input == "text") {return;}

}

}
