<?php
/**
 * Job.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

use setasign\Fpdi;

class Job extends Agent
{
    /**
     *
     */
    function init()
    {
        $this->short_name = "<us?>";

        $this->web_prefix = $this->thing->container["stack"]["web_prefix"];

        $this->mail_postfix = $this->thing->container["stack"]["mail_postfix"];
        $this->word = "<job?>";
        $this->email = "<email address?>";
        $this->entity_name = "<us?>";

        $this->resource_path = $GLOBALS["stack_path"] . "resources/";

        $state = "off";
        if (isset($this->thing->container["api"]["job"]["state"])) {
            $state = $this->thing->container["api"]["job"]["state"];
        }
        $this->state = $state;

        $this->index_type = "index";

        $this->node_list = [
            "receipt management" => [
                "learning",
                "communicating" => ["more", "less"],
                "channeling" => ["narrowing", "broadening"],
            ],
            "receipt start" => [
                "more" => "receipt management",
                "less" => "receipt management",
            ],
        ];

        $this->aliases = ["learning" => ["good job"]];

        $this->PNG();

        $this->thing_report["help"] =
            "Creates a unique reference for a job. Try WEB.";
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
     */
    public function getIndex()
    {
        $agent = new Index($this->thing, "index");
        $this->index_png = $agent->PNG_embed;
    }

    public function runJob($datagram)
    {
        $to = $datagram["from"];
        $subject = $datagram["subject"];
        $from = $datagram["to"];


        if ((isset($datagram['agent_input'])) and $datagram["agent_input"] == "gearman") {
            $arr = json_encode([
                "to" => $to,
                "from" => $from,
                "subject" => $subject,
            ]);

            $client = new \GearmanClient();
            $client->addServer();
            $client->doLowBackground("call_agent", $arr);

            return false;
        }

        $this->thing->console($subject . "\n");

        $thing = new Thing(null);
        $thing->Create($to, $from, $subject);
        //$thing->Create($from, $to, $subject);
        $agent_handler = new Agent($thing, null);
        $this->thing->console($agent_handler->thing_report["sms"] . "\n");
        $this->response .= "Ran job. ";
        return $agent_handler->thing_report;
    }

    /**
     *
     */
    function set()
    {
        // Record receipt of the request.
        $this->thing->Write(["job", "refreshed_at"], $this->thing->time());

        $this->thing->Write(["job", "response"], $this->response);
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $choices = false;

        $this->thing_report["email"] = $this->message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        return $this->thing_report;
    }

    public function parse($line = null)
    {
        // Parse rules for digesting a line of text
        if (trim($line) == "") {
            return true;
        }

        $first_character = substr($line, 0, 1);

        switch ($first_character) {
            case "#":
                return true;
        }

        $arr = explode(",", $line);
        $period = $arr[0]; // Describer of the card
        $text = trim($arr[1]);
        //$number = trim($arr[2]);
        //$text = trim($arr[3]);

        //$from = "X";
        //if (isset($arr[4])) {
        //    $from = trim($arr[4]);
        //}

        //$to = "X";
        //if (isset($arr[5])) {
        //    $to = trim($arr[5]);
        //}

        //         $this->texts[$nom][$suit] = $text;
        //         $this->numbers[$nom][$suit] = $number;

        $job = ["period" => $period, "text" => $text];

        $this->job_list[] = [$job];

        $this->jobs[$period] = [$job];
    }

    /**
     *
     */
    public function makeImage()
    {
        $text = "job";
        $text = strtoupper($text);

        $image_height = 125;
        $image_width = 125 * 1;

        $image = imagecreatetruecolor($image_width, $image_height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 255, 0);
        $grey = imagecolorallocate($image, 128, 128, 128);

        imagefilledrectangle($image, 0, 0, $image_width, $image_height, $white);
        $textcolor = imagecolorallocate($image, 0, 0, 0);

        //$this->ImageRectangleWithRoundedCorners($image, 0,0, $image_width, $image_height, 12, $black);
        //$this->ImageRectangleWithRoundedCorners($image, 6,6, $image_width-6, $image_height-6, 12-6, $white);

        $font = $this->default_font;

        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);
        $sizes_allowed = [72, 36, 24, 12, 6];

        foreach ($sizes_allowed as $size) {
            $angle = 0;
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
            $width = imagesx($image);
            $height = imagesy($image);
            if ($bbox["width"] < $image_width - 50) {
                break;
            }
        }

        $pad = 0;
        imagettftext(
            $image,
            $size,
            $angle,
            $width / 2 - $bb_width / 2,
            $height / 2 + $bb_height / 2,
            $grey,
            $font,
            $this->subject
        );
        imagestring(
            $image,
            2,
            $image_width - 75,
            10,
            $this->subject,
            $textcolor
        );

        $this->image = $image;
    }

    /**
     *
     */
    function makeMessage()
    {
        $uuid = $this->uuid;
        $nuuid = $this->thing->nuuid;

        $message =
            "Thank you " .
            $this->from .
            ". The job sent to Agent '" .
            $this->to .
            "' has been accepted by " .
            $this->short_name .
            ".";
        $message .= " ";
        $message .= "Keep on stacking.\n";
        //$message .= $this->web_prefix . "thing\" . $this->uuid . "\job";
        $message .= "\n";

        //$message .= '<img src="' . $this->web_prefix . 'thing/'. $this->uuid .'/job.png" alt="a snowflake ' . $this->thing->nuuid .'" height="92" width="92">';
        $message .=
            '<img src="' .
            $this->web_prefix .
            "thing/" .
            $this->uuid .
            '/snowflake.png" alt="look a freezing snowflake">';

        //$message = htmlspecialchars($message . "\n\n");
        $message = nl2br($message);

        $this->message = $message;
        $this->thing_report["message"] = $this->message;
    }

    /**
     *
     * @return unknown
     */
    function makeTXT()
    {
        $this->verbosity = 1;

        $job_name = "<two or three words>";
        $job_commitment =
            "Provide <sometime?> hours during <promised block(s) of time>.";
        $job_mandate =
            "Provide the results of <some work> doing some <thing> for <us?>.";
        $job_proof =
            "I will need this <thing> from <you?> to prove it is done.";
        $job_first = "Thing <thing?> is the first job.";
        $job_manager = "<messagable person identity>";
        $job_address = "<mailable address>";
        $job_payment = "<some monies>";
        $job_work = "<some work>";
        $job_summary = "Basically " . $job_payment . " for " . $job_work . ".";
        $job_insurance = "With <some insurance requirements?>.";

        $this->txt_message = "JOB DESCRIPTION\n\n";

        $this->txt_message .=
            'Here is the "' . $job_name . '" job description.';
        $this->txt_message .= " ";
        $this->txt_message .= $job_commitment . " ";
        $this->txt_message .= $job_mandate . " ";
        $this->txt_message .= $job_proof . " ";

        $this->txt_message .= "\n\n";

        $this->txt_message .= $job_summary;
        $this->txt_message .= "\n\n";
        $this->txt_message .= $job_first;
        $this->txt_message .= "\n\n";

        $this->txt_message .= $job_insurance;
        $this->txt_message .= "\n\n";

        $this->txt_message .=
            $this->web_prefix . "thing/" . $this->uuid . "/start";
        $this->txt_message .= "\n";
        $this->txt_message .= "\n";
        $this->txt_message .= $job_manager;
        $this->txt_message .= "\n";
        $this->txt_message .= $job_address;

        if ($this->verbosity > 5) {
            $this->txt_message .= "\n\n";
            $this->txt_message .= $this->sms_message;
        }

        if ($this->verbosity >= 1) {
            $this->txt_message .= "\n";
            $this->txt_message .= "-\n\n";
            $this->txt_message .=
                "thing to do " .
                $this->thing->nuuid .
                " made up at " .
                $this->thing->thing->created_at .
                "\n";
            $this->txt_message .=
                "This template job is hosted by the " .
                ucwords($this->word) .
                " service.  Read the privacy policy at " .
                $this->web_prefix .
                "privacy";
        }

        $this->thing_report["txt"] = $this->txt_message;

        return $this->txt_message;
    }

    /**
     *
     */
    function makeWeb()
    {
        $head = '<p class="description">';
        $foot = "</p>";

        if (!isset($this->txt_message)) {
            $this->makeTXT();
        }

        $web_message = htmlspecialchars($this->txt_message . "\n\n");
        $web_message = nl2br($web_message);

        switch ($this->index_type) {
            case "index":
                $web_message .=
                    '<img src="' .
                    $this->web_prefix .
                    "thing/" .
                    $this->uuid .
                    '/index.png" alt="look a 4 digit index">';

                break;
            case "nuuid":
                $web_message .=
                    '<img src="' .
                    $this->web_prefix .
                    "thing/" .
                    $this->uuid .
                    '/nuuid.png" alt="look a 4 character semi-unique id">';
                break;
            default:
                $web_message .=
                    '<img src="' .
                    $this->web_prefix .
                    "thing/" .
                    $this->uuid .
                    '/snowflake.png" alt="look a freezing snowflake">';
        }

        $web_message .= "<br>";

        $link = $this->web_prefix . "thing/" . $this->uuid . "/job.txt";
        $web_message .= '<a href="' . $link . '">job.txt</a>';
        $web_message .= " | ";

        $link = $this->web_prefix . "thing/" . $this->uuid . "/job.pdf";
        $web_message .= '<a href="' . $link . '">job.pdf</a>';
        $web_message .= " | ";
        $link = $this->web_prefix . "thing/" . $this->uuid . "/job.log";
        $web_message .= '<a href="' . $link . '">job.log</a>';

        $this->web_message = $head . $web_message . $foot;
        $this->thing_report["web"] = $this->web_message;
    }

    /**
     *
     * @return unknown
     */
    public function makePDF()
    {
        if (
            $this->default_pdf_page_template === null or
            !file_exists($this->default_pdf_page_template)
        ) {
            $this->thing_report["pdf"] = false;
            return $this->thing_report["pdf"];
        }

        $file = $this->default_pdf_page_template;

        if (!file_exists($file)) {
            return true;
        }

        try {
            // initiate FPDI
            $pdf = new Fpdi\Fpdi();

            $pdf->setSourceFile($file);
            $pdf->SetFont("Helvetica", "", 10);

            $tplidx1 = $pdf->importPage(2, "/MediaBox");
            $s = $pdf->getTemplatesize($tplidx1);

            $pdf->addPage($s["orientation"], $s);
            $pdf->useTemplate($tplidx1);

            $pdf->SetFont("Helvetica", "", 12);
            $line_height = 5;
            $pdf->SetXY(16, 243);
            $pdf->MultiCell(94, $line_height, $this->response, 0);

            switch ($this->index_type) {
                case "index":
                    $this->getIndex();
                    $pdf->Image($this->index_png, 5, 18, 20, 20, "PNG");

                    break;
                case "nuuid":
                    $this->getNuuid();
                    $pdf->Image($this->nuuid_png, 5, 18, 20, 20, "PNG");
                    break;
                default:
                    $this->getNuuid();
                    $pdf->Image($this->nuuid_png, 5, 18, 20, 20, "PNG");
            }

            $pdf->SetFont("Helvetica", "", 12);

            $pdf->SetXY(20, 40);
            $pdf->MultiCell(175, 8, $this->txt_message, 0);

            // Page 2
            $tplidx2 = $pdf->importPage(2);

            $pdf->addPage($s["orientation"], $s);
            $pdf->useTemplate($tplidx2, 0, 0);
            // Generate some content for page 2

            $pdf->SetFont("Helvetica", "", 10);
            $this->txt = "" . $this->uuid . ""; // Pure uuid.

            $this->getQuickresponse(
                $this->web_prefix . "thing\\" . $this->uuid . "\\job"
            );
            $pdf->Image($this->quick_response_png, 175, 5, 30, 30, "PNG");

            $pdf->SetTextColor(0, 0, 0);

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(15, 10);
            $t = $this->thing_report["sms"];

            $line_height = 4;

            $pdf->MultiCell(150, $line_height, $t, 0);

            $y = $pdf->GetY() + 1;
            $pdf->SetXY(15, $y);
            $text = "v0.0.1";
            $pdf->MultiCell(
                150,
                $line_height,
                $this->agent_name . " " . $text,
                0
            );

            $y = $pdf->GetY() + 1;
            $pdf->SetXY(15, $y);

            $text =
                "Pre-printed text and graphics (c) 2018-2019 " .
                $this->entity_name;
            $pdf->MultiCell(150, $line_height, $text, 0);

            $image = $pdf->Output("", "S");

            $this->thing_report["pdf"] = $image;
        } catch (Exception $e) {
            $this->thing->log("Caught exception: " . $e->getMessage());
        }

        return $this->thing_report["pdf"];
    }

    /**
     *
     */
    public function makePNG()
    {
        $agent = new Png($this->thing, "png"); // long run

        $this->makeImage();

        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;
        $this->PNG_embed = $agent->PNG_embed;
        $this->thing_report["png"] = $agent->image_string;
    }

    /**
     *
     * @return unknown
     */
    function makeSMS()
    {
        $this->verbosity = 1;
        $this->index = $this->thing->nuuid;
        $sms = "JOB " . strtoupper($this->index);

        if ($this->verbosity > 5) {
            $sms .= " | thing " . $this->uuid . "";
            $sms .= " created " . $this->thing->thing->created_at;
            $sms .= " by " . strtoupper($this->from);
        }

        if ($this->verbosity >= 1) {
            $sms .=
                " | " .
                $this->web_prefix .
                "thing/" .
                $this->uuid .
                "/job" .
                " | Made up a job, '" . $this->subject . "' at " .
                $this->thing->thing->created_at .
                ".";
        }

        $sms .= " " . $this->response;

        $this->sms_message = $sms;

        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        // test
        // $this->state = "on";
        $input = $this->input;
        $filtered_input = strtolower($this->assert($input));
        if ($this->state == "on") {
            if ($filtered_input == "stack") {
                $manager_agent = new Manager($this->thing, "manager");
                if ($manager_agent->queued_jobs > 100) {
                    $this->response .=
                        "Too many (" .
                        $manager_agent->queued_jobs .
                        ") jobs queued. ";
                    return;
                }
                //$manager->workers_running;
                //$manager->workers_connected;
                if (!isset($this->jobs) or $this->jobs == null) {
                    $this->response .= "No jobs found. ";
                    return;
                }

                $job = $this->jobs[array_rand($this->jobs)][0];

                $datagram = [
                    "to" => "null" . $this->mail_postfix,
                    "from" => "job",
                    "subject" => "s/ " . $job["text"],
                ];

                $this->thing->log("spawn Thing");
                $this->thing->spawn($datagram);
                $this->thing->log("spawned " . $job["text"]);
                $this->response .= "Spawned " . $job["text"] . ". ";

                return;
            }
        }
        $this->index = "meep";
        $this->response = "Made a new job sheet.";
        $status = true;
        return $status;
    }

    public function get()
    {
        $contents = $this->load("job/jobs.txt");
$jobs_filename =  $this->resource_path . "job/jobs.php";
if (file_exists($jobs_filename)) {
        $this->jobs_list = require $jobs_filename;
}
        $this->getJobs();
    }

    public function stackJob($bar_number = null)
    {
        foreach ($this->jobs_list as $job_id => $job) {
            if (intval($job["bar_count"]) == $bar_number) {
                $this->runJob($job);
                continue;
            }

            if (substr($job["bar_count"], 0, 1) == "%") {
                $num = intval(ltrim($job["bar_count"], $job["bar_count"][0]));

                if ($bar_number % $num == 0) {
                    $this->runJob($job);
                    continue;
                }
            }

            if ($job["bar_count"] == "*") {
                $this->runJob($job);
                continue;
            }
        }
    }

    public function getJobs()
    {
        $this->run_jobs = [];
        $agent_name = "job";
        $things = $this->getThings("job");

        if ($things == []) {
            return true;
        }

        foreach (array_reverse($things) as $thing) {
            $subject = $thing->subject;
            $variables = $thing->variables;
            $created_at = $thing->created_at;

            if (isset($variables[$agent_name])) {
                $job = ["subject" => $subject, "created_at" => $created_at];

                $job = array_merge($job, $variables[$agent_name]);

                $this->run_jobs[] = $job;
            }
        }
    }

    /**
     *
     * @return unknown
     */
    public function PNG()
    {
        $snowflake_agent = new Snowflake($this->thing, "snowflake");

        $snowflake_agent->makePNG();
        $this->PNG = $snowflake_agent->PNG;

        $this->thing_report["png"] = $this->PNG;

        $response =
            '<img src="data:image/png;base64,' .
            base64_encode($this->PNG) .
            '"alt="this snowflake is melting already">';

        return $response;
    }
}
