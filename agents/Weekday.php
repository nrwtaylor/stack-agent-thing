<?php
/**
 * Day.php
 *
 * @package default
 */

// TODO
// PDF and PNG rendering

// https://nrc.canada.ca/en/research-development/products-services/software-applications/sun-calculator/

namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Weekday extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {
        $this->dateline = false;
        $this->test = "Development code";

        $this->thing_report["info"] =
            "A WEEKDAY is the index of a 7 day cycle.";
        $this->thing_report["help"] = "Click on the image for a PDF.";

        $this->resource_path = $GLOBALS["stack_path"] . "resources/";

        $command_line = null;

        $this->node_list = ["weekday" => ["weekday", "day", "year", "uuid"]];

        $this->projected_time = $this->current_time;

        // Get some stuff from the stack which will be helpful.
        $this->entity_name = $this->thing->container["stack"]["entity_name"];


        $this->week_day_indicators = [
            "MON" => ["monday", "mon", "M"],
            "TUE" => ["tuesday", "tue", "Tu"],
            "WED" => ["wednesday", "wed", "wday", "W"],
            "THU" => ["thursday", "thur", "Thu", "Th"],
            "FRI" => ["friday", "fri", "Fr", "F"],
            "SAT" => ["saturday", "sat", "Sa"],
            "SUN" => ["sunday", "sun", "Su"],
        ];

$this->week_days = [];

        // dev factor up to agent
        if (!isset($this->week_day)) {
            $this->initWeekday();
        }
    }

    public function initWeekday()
    {
        $this->week_day = false;
        $this->time_agent = new Time($this->thing, "time");
        $this->working_datum = $this->time_agent->datumTime(
            $this->projected_time
        );

    }

function computeWeekday($date) {
    return date('w', strtotime($date));
}

    public function formatDay($text = null)
    {
        // Placeholder.
        // Make more general.
        $date = date_create_from_format("Y-m-j", $text);
        $d = strtoupper(date_format($date, "Y M d D"));

        //$t = $this->current_time;
        //$d = strtoupper(date('Y M d D H:i', $t))

        return $d;
    }

    public function runDay($text = null)
    {
        if ($this->latitude === false or strtolower($this->latitude) == "z") {
            $this->response .= "Latitude not known. ";
        }

        if ($this->latitude === false or $this->longitude === false) {
            return true;
        }

        $latitude = (float) $this->latitude;
        $longitude = (float) $this->longitude;

        $timestamp_epoch = (float) $this->timestampEpoch($text);

        // dev
        // Make this call for the primary place from the list of places.

        // Com

    }


    public function timestampEpoch($text = null)
    {
        //$timestamp_epoch = time();
        $timestamp_epoch = $this->projected_time;

        if ($this->dateline !== false) {
            /*
https://www.w3schools.com/php/func_date_strtotime.asp
Note: Be aware of dates in the m/d/y or d-m-y formats;
 if the separator is a slash (/), then the American m/d/y
 is assumed. If the separator is a dash (-) or a dot (.),
 then the European d-m-y format is assumed.
 To avoid potential errors, you should YYYY-MM-DD dates or date_create_from_format() when possible.
*/
            //$timestamp_epoch = strtotime($this->dateline['year']."-".$this->dateline['month']."-".$this->dateline['day_number']);

            //$timestamp_epoch = strtotime("2021-10-24");
        }

        /*
https://www.php.net/manual/en/function.date-sun-info.php
 info at mobger dot de ¶
10 months ago
The relation between timestamp and geoposition is not good defined.
My try of a definition is:

date_sun_info —
Returns an array with information about sunset/sunrise and twilight begin/end
 as Unix-Timestamp for the the geoposition, which must have the same (local) date
 as the timestamp in the parameter-block for the function `date_sun_info`.
*/

        /*
Dev review against
https://nrc.canada.ca/en/research-development/products-services/software-applications/sun-calculator/
Sunrise sunset full year
10 October 2021
Vancouver
Oct 24 2021,5:38,6:16,6:48,11:57,17:04,17:37,18:14,10.27,1.09,11.36,1:59:19

DAY | DAY astronomical twilight begin 2021/10/24 6:01:53 
 nautical twilight begin 6:38:43 civil twilight begin 7:15:53
 sunrise 7:48:31 transit 12:56:36 sunset 18:04:41
 civil twilight end 18:37:19 nautical twilight end 19:14:29
 astronomical twilight begin 6:03:21 America/Los_Angeles 

*/

        /*

$ agent day twilight
DAY | astronomical twilight | ASTRONOMICAL TWILIGHT astronomical twilight begin 2022/03/30 5:01 nautical twilight begin 5:42 civil twilight begin 6:20 sunrise 6:52 transit 13:16 sunset 19:40 civil twilight end 20:12 nautical twilight end 20:51 astronomical twilight end 21:32 astronomical twilight begin 2022/03/31 4:59 nautical twilight begin 5:40 civil twilight begin 6:18 sunrise 6:50 transit 13:16 sunset 19:42 civil twilight end 20:14 nautical twilight end 20:53 astronomical twilight end 21:33 America/Los_Angeles 
PHP 1,439ms 1c9a8a92-1e64-4bca-aa59-1d4c83feb6ea
X b943 a867 Added to stack.


*/
        //        $timestamp_epoch = time();

        if ($text != null and is_string($text)) {
            $timestamp_epoch = strtotime($text);
        }

        if (is_a($text, "DateTime")) {
            $timestamp_epoch = $text->getTimestamp();
        }

        if ($text == null) {
            $timestamp_epoch = strtotime($this->current_time);
        }

        $this->timestamp_epoch = $timestamp_epoch;
        return $timestamp_epoch;
    }

    public function set()
    {
        $this->setWeekday();
    }

    /**
     *
     * @param unknown $text (optional)
     */
    function getQuickresponse($text = null)
    {
        if ($text == null) {
            $text = $this->web_prefix;
        }
        $agent = new Qr($this->thing, $text);
        $this->quick_response_png = $agent->PNG_embed;
    }

    /**
     *
     */
    public function getNuuid()
    {
        $agent = new Nuuid($this->thing, "nuuid");
        $this->nuuid_png = $agent->PNG_embed;
    }

    /**
     *
     * @param unknown $input
     */
    function getWhatis($input)
    {
        if ($input == null) {
            return;
        }
        $whatis = "weekday";
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), $whatis . " is")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen($whatis . " is")
            );
        } elseif (($pos = strpos(strtolower($input), $whatis)) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen($whatis));
        }

        //$filtered_input = ltrim(strtolower($whatIWant), " ");
        $filtered_input = ltrim($whatIWant, " ");

        $this->whatis = $filtered_input;
    }

    // TODO Remove this function. Refactor as a stamp call.
    /**
     *
     * @param unknown $t (optional)
     * @return unknown
     */
    public function timestampDay($t = null)
    {
        //        $s = $this->thing->thing->created_at;

        if (!isset($this->retain_to)) {
            $text = "X";
        } else {
            $t = $this->retain_to;
            $text = "GOOD UNTIL " . strtoupper(date("Y M d D H:i", $t));
            //$text = "CLICK FOR PDF";
        }
        $this->timestamp = $text;
        return $this->timestamp;
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

//        $this->makeChoices();
//        $this->makePollInterval();
        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        return $this->thing_report;
    }

    /**
     *
     */
    public function makeChoices()
    {
        $this->choices = false;
        $this->thing_report["choices"] = $this->choices;
    }

    public function makeSMS()
    {
        $sms = "WEEKDAY";

        if (
          isset($this->week_day_flag) and
            $this->week_day_flag == "on"
        ) {
        $web .= $this->formatDay($this->datestringDay($this->dateline));
        $web .= "<br>";
}

        $days = [];
        if (isset($this->week_days)) {
            $days = $this->week_days;
        }

        /*
        $day_text = "No day found.";
        if (isset($this->day)) {
            $day_text = $this->day;
            $sms .= " | " . $day_text;
        }
*/
/*
        $day_text = "Merp.";
        if (isset($this->day_time)) {
            $day_time_text = $this->day_time;
            $sms .= " | " . $day_time_text;
        }
*/
        $sms .= " | " . $this->message . " " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     */
    public function makeMessage()
    {
        $message = "Made a day for you.<br>";

        $uuid = $this->uuid;

        $message .=
            "Thank you.\n\n<p>" .
            $this->web_prefix .
            "thing/$uuid/day.png\n \n\n<br> ";
        /*
        $message .=
            '<img src="' .
            $this->web_prefix .
            "thing/" .
            $uuid .
            '/day.png" alt="day" height="92" width="92">';
*/
        $this->thing_report["message"] = $message;
    }

    /**
     *
     */
    public function setWeekday()
    {
        return;
        $this->thing->Write(["weekday", "weekday"], $this->week_day);

        $this->thing->log(
            $this->agent_prefix .
                " saved decimal day " .
                $this->decimal_day .
                ".",
            "INFORMATION"
        );
    }

public function runWeekday() {


          $timestamp = $this->zuluStamp($this->current_time);

            $dateline = $this->extractDateline($timestamp);

            $this->dateline = $dateline;

            $this->projected_time = strtotime($this->current_time);
            $this->working_datum = $this->time_agent->datumTime(
                $this->current_time
            );


$week_day_number = date("w", $this->projected_time);
$week_day_text = strtoupper(date('D', strtotime("Sunday + {$week_day_number} days")));
//$this->week_days[$week_day_text] = true;


$this->response .= $week_day_text . ". ";



}

    /**
     *
     * @return unknown
     */
    public function getWeekday()
    {
        /*
        $longitude_agent = new Longitude($this->thing, "longitude");

        // Cannot calculate local time without knowing longitude.
        if ($longitude_agent->longitude === false) {
            $this->response .= "Longitude not known. ";
        }

        $this->longitude = $longitude_agent->longitude;


        $latitude_agent = new Latitude($this->thing, "latitude");
        $this->latitude = $latitude_agent->latitude;
*/
    }

    public function run()
    {
        //  $this->runDay();
    }

    public function textWeekday()
    {
        return $this->textHtml($this->snippetWeekday());
    }

    public function snippetWeekday()
    {
        $web = "";

        $web .= $this->week_day;
        $web .= "<br>";

        return $web;
    }

    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/day.pdf";
        $this->node_list = ["weekday" => ["weekday"]];

        $web = "";
        $web .= $this->snippetWeekday();

        $web .= '<a href="' . $link . '">';
        $web .= $this->html_image;
        $web .= "</a>";
        $web .= "<br>";

        $web .= $this->htmlTable($this->twilights);

        $this->thing_report["web"] = $web;
    }

    /**
     *
     */
    public function makeTXT()
    {
        $txt = "This is a WEEKDAY";
        $txt .= "\n";

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    /**
     *
     * @param unknown $r
     * @param unknown $g
     * @param unknown $b
     */

    /**
     *
     * @return unknown
     */
    public function deprecate_makePNG()
    {
        if (isset($this->canvas_size_x)) {
            $canvas_size_x = $this->canvas_size_x;
            $canvas_size_y = $this->canvas_size_x;
        } else {
            $canvas_size_x = 164;
            $canvas_size_y = 164;
        }

        $this->image = imagecreatetruecolor($canvas_size_x, $canvas_size_y);

        // dev
        $this->colours_agent = new Colours($this->thing, "colours");
        $this->colours_agent->image = $this->image;
        $this->colours_agent->getColours();

        imagefilledrectangle(
            $this->image,
            0,
            0,
            $canvas_size_x,
            $canvas_size_y,
            $this->colours_agent->white
        );

        $textcolor = imagecolorallocate($this->image, 0, 0, 0);
        //if (isset($this->text_color)) {
        //    $textcolor = $this->text_color;
        //}

        $this->drawDay();

        // Write the string at the top left
        $border = 30;
        $r = 1.165;

        $radius = ($r * ($canvas_size_x - 2 * $border)) / 3;

        // devstack add path
        $font = $this->default_font;
        $text = "A day in slices...";
        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);

        $size = $canvas_size_x - 90;
        $size = 20;
        $angle = 0;
        if (file_exists($font)) {
            $bbox = imagettfbbox($size, $angle, $font, $text);
            $bbox["left"] = 0 - min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $bbox["top"] = 0 - min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
            $bbox["width"] =
                max($bbox[0], $bbox[2], $bbox[4], $bbox[6]) -
                min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $bbox["height"] =
                max($bbox[1], $bbox[3], $bbox[5], $bbox[7]) -
                min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
            extract($bbox, EXTR_PREFIX_ALL, "bb");
            //check width of the image
            $width = imagesx($this->image);
            $height = imagesy($this->image);
            $pad = 0;
        }
        //        imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $this->black, $font, $text);

        //imagestring($this->image, 2, 140, 0, $this->thing->nuuid, $textcolor);

        // https://stackoverflow.com/questions/14549110/failed-to-delete-buffer-no-buffer-to-delete

        if (ob_get_contents()) {
            ob_clean();
        }

        ob_start();
        imagepng($this->image);
        $imagedata = ob_get_contents();

        ob_end_clean();

        $this->thing_report["png"] = $imagedata;

        $response =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="weekday"/>';

        $this->html_image =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="weekday"/>';

        $this->PNG_embed = "data:image/png;base64," . base64_encode($imagedata);

        $this->PNG = $imagedata;

        return $response;

        $this->PNG = $image;
        $this->thing_report["png"] = $image;

        return;
    }


    public function datumText($text)
    {
        // text with a fully qualified time.

        $datum = new \DateTime();
        $datum->setTimestamp(strtotime($text));

        $t = $this->timestampTime($datum);

        return $t;
    }


    public function get()
    {
        $time_string = $this->thing->Read(["weekday", "refreshed_at"]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(["weekday", "refreshed_at"], $time_string);
        }

        $this->getWeekday();
        //$this->getWhatis($this->input);
    }

    public function pdfWeekday($pdf)
    {
        try {
            $this->getNuuid();
            $pdf->Image($this->PNG_embed, 7, 30, 200, 200, "PNG");

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(1, 1);

            $pdf->SetFont("Helvetica", "", 26);
            //            $this->txt = "" . $this->whatis . ""; // Pure uuid.

  //          $pdf->SetXY(140, 7);
  //          $this->getWhatis($this->subject);

            $text = $this->deSlug($this->whatis);
            $line_height = 20;
            $pdf->MultiCell(150, $line_height, $text, 0);

            $pdf->SetFont("Helvetica", "", 14);
            $text = $this->textDay();
            $pdf->SetXY(7, 7);
            //$this->getWhatis($this->subject);

            //$text = $this->whatis;
            $line_height = 20;
            $pdf->MultiCell(150, $line_height, $text, 0);
        } catch (Exception $e) {
            $this->thing->console("Caught exception: ", $e->getMessage(), "\n");
        }

        return $pdf;
    }
    /**
     *
     * @return unknown
     */
    public function makePDF()
    {
return;
        $image = null;
        if (
            $this->default_pdf_page_template === null or
            !file_exists($this->default_pdf_page_template)
        ) {
            $this->thing_report["pdf"] = false;
            return $this->thing_report["pdf"];
        }

        //        $this->getWhatis($this->subject);
        // prod March 16 2022
        //$pdf_handler = new Pdf($this->thing, "pdf");
        $pdf = new Fpdi\Fpdi();

        try {
            // initiate FPDI
            //$pdf = $pdf_handler->pdf;
            $pdf->setSourceFile($this->default_pdf_page_template);
            $pdf->SetFont("Helvetica", "", 10);

            $tplidx1 = $pdf->importPage(1, "/MediaBox");

            $s = $pdf->getTemplatesize($tplidx1);

            $pdf->addPage($s["orientation"], $s);
            $pdf->useTemplate($tplidx1);


            $this->pdfWeekday($pdf);

            // Page 2
            $tplidx2 = $pdf->importPage(2);
            $pdf->addPage($s["orientation"], $s);
            $pdf->useTemplate($tplidx2, 0, 0);

            // Generate some content for page 2

            //$pdf = $this->pdfImpressum($pdf);

            /*
            // Good until?
            $text = $this->timestampDay();
            $pdf->SetXY(175, 35);
            $pdf->MultiCell(30, $line_height, $text, 0, "L");
*/
            // http://fpdf.org/en/doc/output.htm

            //$image = $pdf->Output("S");
        } catch (Exception $e) {
            $this->thing->console("Caught exception: ", $e->getMessage(), "\n");
        }

        $this->thing_report["pdf"] = $image;

        return $this->thing_report["pdf"];
    }

    public function isWeekday($text)
    {
        $this->parsed_date = date_parse($text);
        $day = $this->parsed_date["day"];
        if ($day !== false) {
            return $day;
        }
        foreach ($this->week_day_indicators as $day => $day_indicators) {
//            if (stripos($text, $day) !== false) {
if (strtolower($text) === strtolower($day)) {
                return $day;
            }

            foreach ($day_indicators as $i => $day_indicator) {
if (strtolower($text) === strtolower($day_indicator)) {
   //             if (stripos($text, $day_indicator) !== false) {
            //        return $day_indicator;
return $day;
                }
            }
        }

        return false;
    }

    // TODO
    public function deprecate_extractWeekdays($text = null)
    {
        $days = [];

        if ($text == null or $text == "") {
            return true;
        }

        if (!isset($this->ngram_agent)) {
            $this->ngram_agent = new Ngram($this->thing, "ngram");
        }

        $tokens = [];
        foreach (range(0, 4, 1) as $i) {
            $new_grams = $this->ngram_agent->extractNgrams($text, $i);
            $tokens = array_merge($tokens, $new_grams);
        }
        foreach ($tokens as $i => $token) {
            if ($token == "") {
                continue;
            }
            $response = $this->isWeekday($token);

            if ($response === false) {
                continue;
            }

$days[] = $response;

        }

        // Remove duplicates.
        // https://stackoverflow.com/questions/307674/how-to-remove-duplicate-values-from-a-multi-dimensional-array-in-php
        $serialized = array_map("serialize", $days);
        $unique = array_unique($serialized);
        $days = array_intersect_key($days, $unique);
        return $days;
    }

    // Extract weekday will be something else.

    function extractWeekdays($input = null)
    {
        $day = "X";
        $day_evidence = [];

        $days = $this->week_day_indicators;

        foreach ($days as $i => $day_null) {
            $day_evidence[$i] = [];
        }
/*
        foreach ($days as $key => $day_names) {
            if (strpos(strtolower($input), strtolower($key)) !== false) {
                // $day_evidence[] = $key;
                $day = $key;
                $day_evidence[$day][] = $key;
                //break;
            }

            foreach ($day_names as $day_name) {
                if (
                    strpos(strtolower($input), strtolower($day_name)) !== false
                ) {
                    if (
                        strpos(
                            strtolower($input),
                            strtolower($day_name . " ")
                        ) == false
                    ) {
                        continue;
                    }

                    if (
                        strpos(
                            strtolower($input),
                            strtolower(" " . $day_name)
                        ) == false
                    ) {
                        continue;
                    }

                    //      $day_evidence[] = $day_name;
                    $day = $key;
                    $day_evidence[$key][] = $day_name;

                    //break;
                }
            }
        }
*/



//  if (!isset($this->ngram_agent)) {
//            $this->ngram_agent = new Ngram($this->thing, "ngram");
//        }

        $tokens = [];
        //foreach (range(0, 4, 1) as $i) {
        //    $new_grams = $this->ngram_agent->extractNgrams($input, $i);
        //    $tokens = array_merge($tokens, $new_grams);
        //}
$tokens = explode(" ",$input);
        foreach ($tokens as $i => $token) {
            if ($token == "") {
                continue;
            }
            $response = $this->isWeekday($token);
            if ($response === false) {
                continue;
            }

$day_evidence[$response][] = $token;

//$days[] = $response;

        }
//exit();
        // Remove duplicates.
        // https://stackoverflow.com/questions/307674/how-to-remove-duplicate-values-from-a-multi-dimen>
//        $serialized = array_map("serialize", $days);
//        $unique = array_unique($serialized);
//        $days = array_intersect_key($days, $unique);
//        return $days;















        $dateline = $this->extractDateline($input);

        if (
            $dateline["year"] !== false and
            $dateline["month"] !== false and
            $dateline["day_number"] !== false
        ) {
            $date_string =
                $dateline["year"] .
                "/" .
                $dateline["month"] .
                "/" .
                $dateline["day_number"];

            $unixTimestamp = strtotime($date_string);
            $p_day = strtoupper(date("D", $unixTimestamp));
            if ($day == "X") {
                $day = $p_day;
            }

//            $projected_time = strtotime($this->current_time);
 //           $this->working_datum = $this->time_agent->datumTime(
 //               $this->current_time
 //           );



$week_day_number = date("w", $unixTimestamp);
$week_day_text = strtoupper(date('D', strtotime("Sunday + {$week_day_number} days")));


            $day_evidence[$week_day_text][] = $date_string;




        }

        $scores = [];
        // Process day evidence
        foreach ($day_evidence as $day => $evidence) {
            $scores[$day] = mb_strlen(implode("", $evidence));
        }

        foreach ($scores as $i => $score) {
            if ($score == 0) {
                unset($scores[$i]);
                continue;
            }

            // Allow one character date recognition if the string is 1 long.
            if ($score == 1 and mb_strlen($input) == 1) {
                continue;
            }

            // Allow two character date recognition if the string is 2 long.
            if ($score == 2 and mb_strlen($input) == 2) {
                continue;
            }

            // Now deal with lots of matching letters in a long string
            // Is there more than one line of evidence?
            if (count($day_evidence[$i]) > 1) {
                continue;
            }

            if ($score > 2) {
                continue;
            }

            // Otherwise ignore
            // TODO: Review
            unset($scores[$i]);
        }

        if (count($scores) == 0) {
            return false;
        }

        // Leave it here for now.
        // TODO: Consider three days all with same score
        // TODO: Consider two days wth non-zero scores.

        // TODO: Day of week extraction.
return $scores;
    }


public function extractWeekday($text) {


$days = $this->extractWeekdays($text);

if ($days === false) {return false;}

if (count($days) == 1) {
return key($days);
}

return false;

}

    /**
     *
     */
    public function readSubject()
    {
        $i = str_replace("-", " ", $this->input);

        $tokens = explode(" ", $i);

        $place_times = [];

        $expand_places_input = $this->input;

        $expand_places_input = str_replace(
            "weekday",
            " ",
            $expand_places_input
        );

// Today?  
/*
          $timestamp = $this->zuluStamp($this->current_time);

            $dateline = $this->extractDateline($timestamp);

            $this->dateline = $dateline;

            $this->projected_time = strtotime($this->current_time);
            $this->working_datum = $this->time_agent->datumTime(
                $this->current_time
            );


$week_day_number = date("w", $this->projected_time);
$week_day_text = strtoupper(date('D', strtotime("Sunday + {$week_day_number} days")));
//$this->week_days[$week_day_text] = true;




*/




        $this->type = "wedge";

        $input = $this->agent_input;
        if ($this->agent_input == "" or $this->agent_input == null) {
            $input = $this->subject;
        }

        if ($input == "weekday") {
            $this->runWeekday();
            return;
        }

//        $this->week_days = $this->extractWeekdays($input);

        $day = $this->extractWeekday($input);

        if ($day !== false) {
            $this->week_day = $day;

$this->response .= $day . ". ";
return;
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "weekday") {
                $this->getWeekday();

/*
                if (!isset($this->decimal_day) or $this->decimal_day == null) {
                    $this->decimal_day = rand(1, rand(1, 10) * 1e11);
                }

                $this->binaryDay($this->decimal_day);
                $p = strlen($this->binary_day);

                $this->max = 13;
                $this->size = 4;
                $this->lattice_size = 40;
                $this->response .= "Made a day. ";
                $this->runDay();
*/
                return;
            }
        }

        $_indicators = [
            "translate" => ["translate", "english", "anglic"],
            "julian" => ["julian"],
            "mesoamerican" => ["maya"],
            "twilight" => [
                "twilight",
                "dawn",
                "sunset",
                "sunrise",
                "transit",
                "noon",
            ],
            "week" => ["week"],
        ];
        $dedash_input = str_replace("-", " ", $input);
        $this->flagAgent($this->week_day_indicators, $dedash_input);
/*
        $input_agent = new Input($this->thing, "input");
        $discriminators = ["wedge", "slice"];
        $input_agent->aliases["wedge"] = ["pizza", "wheel", "wedge"];
        $input_agent->aliases["slice"] = ["slice", "column", "columns"];
        $type = $input_agent->discriminateInput($input, $discriminators);
        if ($type != false) {
            $this->type = $type;
        }

        $keywords = ["day"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        default:
                    }
                }
            }
        }

        $this->getDay();
        $this->runDay();
*/
    }
}
