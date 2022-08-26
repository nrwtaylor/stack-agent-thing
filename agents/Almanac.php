<?php
namespace Nrwtaylor\StackAgentThing;

class Almanac extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doAlmanac();
    }

    public function doAlmanac()
    {

$this->day_handler = new Day($this->thing, "day twilight maya translate " . $this->input); 
$this->dateline_handler = new Dateline($this->thing, $this->input);
$this->month_handler = new Month($this->thing, $this->dateline_handler->dateline['dateline']);

$this->dateline_handler->questionDateline($this->input);



$v = "Unrecognized date. ";

$v .= $this->dateline_handler->dateline['dateline'];

//if ($this->isDate($this->dateline_handler->dateline['dateline'])) {




$v = "";
$v .= "dateline ";
$v .= $this->dateline_handler->dateline['dateline'];


$v .= $this->day_handler->response;
//$v .= $this->day_handler->thing_report['sms'];
$v .= " ";
$v .= "subject " . $this->subject;
$v .= " ";
$v .= $this->month_handler->response;



//        $contents = $this->urlDateline($url);


/*
$contents = file_get_contents('/var/www/stackr.test/resources/calendar/calendar.txt');
$this->almanac_contents = $contents;
        $paragraphs = $this->paragraphsDateline($contents);

$v .= count($paragraphs);

        $arr = ['year', 'month', 'day', 'day_number', 'hour', 'minute'];

        foreach ($paragraphs as $i => $paragraph) {

            if (trim($paragraph) == "") {
                continue;
            }
            //$dateline = $this->extractDateline($paragraph);
            if ($dateline == false) {
                continue;
            }
        }

$this->almanac_paragraphs = $paragraphs;



*/

//}

        if ($this->agent_input == null) {
//            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
//            $k = array_rand($array);
//            $v = $array[$k];

            $response = "ALMANAC | " . $v . ".";

            $this->almanac_message = $response; // mewsage?
        } else {
            $this->almanac_message = $this->agent_input;
        }
    }

//$this->almanac_message .= $this->day_handler->thing_report['sms'];
//$this->almanac_message .= $this->day_handler->thing_report['sms'];

//$this->almanac_message = "merp";

/*
    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "cat");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }
*/

    // -----------------------
/*
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a cat keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $s .= "" . $this->almanac_message;
$s .= $this->day_handler->html_image;



$s .= $this->day_handler->response;
$s .= $this->day_handler->thing_report['sms'];

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];
    }
*/
function makeSnippet() {

$s = "";
/*
        $s .= "" . $this->almanac_message;
$s .= $this->day_handler->html_image;



$s .= $this->day_handler->response;
$s .= $this->day_handler->thing_report['sms'];

$s .= "<p>";
$s .= "DATELINE";
$s .= "<p>";

$s .= "<br />" . $this->dateline_handler->dateline['text'];
$s .= "<br />" . $this->dateline_handler->dateline['dateline'];
*/
//$dateline = $this->dateline_handler->dateline['dateline'];
$dateline = $this->dateline_handler->thing_report['sms'];

$dateline = str_replace("DATELINE ", "", $dateline);
/*
$s .= "dateline " . $dateline;
// isDate needs development
$s .= "<br />";
*/
$parts = explode("t", strtolower($dateline));
//var_dump("parts",$parts);
$date_part = $parts[0];

$s .= " ";
if (($this->hasText($date_part, "x")) || ($this->hasText($date_part, "-00"))) {
    $s .= "IS NOT DATE ";
} else {
    $s .= "IS DATE ";
//foreach( $this->almanac_paragraphs as $i=>$paragraph) {
//$d = $this->extractDateline($paragraph);
//$s .= $d['dateline'] . $paragraph;
//$s .= "<p>";
//}
$s .= "<p>";

$month = $this->textMonth($this->dateline_handler->dateline['dateline']);
$day = $this->textDay($this->dateline_handler->dateline['dateline']);

$day_number = $this->day_handler->extractDaynumber($day);

//$s .= "month " . $month;
//$s .= "day " . $day;
//$s .= "day number " . $day_number;

$this->wikipedia_handler = new Wikipedia($this->thing, "february 28");

$s .= $this->wikipedia_handler->snippet;

foreach($this->wikipedia_handler->results as $i=>$result) {
$s .= $result['snippet'];
//$s .= " " . $result['ns'] . " " . $result['pageid'];

$wikipedia_url = urlencode('https:\/\/en.wikipedia.org/?curid=' . $result['pageid']);
$wikipedia_result_http_link = '<a href="' . $wikipedia_url . '">' . $result['title'] . '</a>';
//$s .= $this->restoreUrl($wikipedia_url);

$s .= $wikipedia_result_http_link;

//$s .= implode(" ", array_keys($result));
$s .= "<br />";
}



$s .= "date part " . $date_part;
$s .= "<br />";
$s .= "isdate ";
$s .= "xxx";
$s .= $this->almanac_paragraphs[0];
$s .= "xxx";


$s .= "" . $this->almanac_message;
$s .= $this->day_handler->html_image;



$s .= $this->day_handler->response;
$s .= $this->day_handler->thing_report['sms'];


$s .= "<p>";
$s .= "DATELINE";
$s .= "<p>";

$s .= "<br />" . $this->dateline_handler->dateline['text'];
$s .= "<br />" . $this->dateline_handler->dateline['dateline'];
$s .= "dateline " . $dateline;
// isDate needs development
$s .= "<br />";




}






$s .= "<br />" . $this->dateline_handler->response;
$s .= "<br />" . $this->dateline_handler->thing_report['sms'];

$s .= "<p>";
$s .= "THING";
$s .= "<p>";

$s .= "subject " . $this->subject;
$s .= "<p>";

$s .= "<br />" . $this->dateline_handler->dateline['text'];
$s .= "<br />" . $this->dateline_handler->dateline['dateline'];

/*
if ($this->isDate($this->dateline_handler->dateline['dateline'])) {
$v .= "IS DATE ";

} else {
$v .= "IS NOT DATE ";
}
*/




$s .= $this->day_handler->png;
$s .= "<br />";
$s .= "input " . $this->input;
        $this->snippet = $s;
        $this->thing_report['snippet'] = $this->snippet;

}

function makeWeb() {

        $web = $this->snippet;
        $this->thing_report["web"] = $web;


}

    function makeSMS()
    {
        $this->node_list = array("almanac" => array("cat", "dog"));
        $this->sms_message = "" . $this->almanac_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "almanac");
        $choices = $this->thing->choice->makeLinks('almanac');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
