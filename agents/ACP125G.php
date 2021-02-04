<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class ACP125G extends Agent
{
	public $var = 'hello';

    public function init()
    {

        $this->node_list = array("acp125g"=>array("acp125g"));

        $this->unit = "FUEL";

        $this->default_state = "X";

        $this->getNuuid();

        $this->character = new Character($this->thing, "character is Rocket J. Squirrel");

        // Get the remaining persistence of the message.
        $agent = new Persistence($this->thing, "persistence 60 minutes");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        if (
            isset($this->thing->container['stack']['font'])
        ) {
            $this->font =
                $this->thing->container['stack']['font'];
        }

        $this->variable = new Variables($this->thing, "variables acp125g " . $this->from);
	}

    function isACP125G($state = null)
    {

        if ($state == null) {
            if (!isset($this->state)) {$this->state = "easy";}

            $state = $this->state;
        }

        if (($state == "easy") or ($state == "hard")
            ) {return false;}

        return true;
    }

    function set($requested_state = null)
    {
        $this->thing->json->setField("variables");

        $this->thing->json->writeVariable( array("acp125g", "inject"), $this->inject );

        $this->refreshed_at = $this->current_time;

        $this->variable->setVariable("state", $this->state);
        $this->variable->setVariable("refreshed_at", $this->current_time);

        $this->thing->log($this->agent_prefix . 'set ACP 125(G) to ' . $this->state, "INFORMATION");
    }

    function get()
    {
        $this->previous_state = $this->variable->getVariable("state");

        $this->refreshed_at = $this->variable->getVariable("refreshed_at");

        $this->thing->log($this->agent_prefix . 'got from db ' . $this->previous_state, "INFORMATION");

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isACP125G($this->previous_state)) {
            $this->state = $this->previous_state;
        } else {
            $this->state = $this->default_state;
        }

        if ($this->state == false) {
            $this->state = $this->default_state;
        }

        $this->thing->log($this->agent_prefix . 'got a ' . strtoupper($this->state) . ' FLAG.' , "INFORMATION");

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("acp125g", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("acp125g", "refreshed_at"), $time_string );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->inject = $this->thing->json->readVariable( array("acp125g", "inject") );
    }

    function getNuuid()
    {
        $agent = new Nuuid($this->thing, "nuuid");
        $this->nuuid_png = $agent->PNG_embed;
    }

    function getUuid()
    {
        $agent = new Uuid($this->thing, "uuid");
        $this->uuid_png = $agent->PNG_embed;
    }

    function getQuickresponse($text = null)
    {
        if ($text == null) {$text = $this->web_prefix;}
        $agent = new Qr($this->thing, $text);
        $this->quick_response_png = $agent->PNG_embed;
    }


    function setState($state)
    {
        $this->state = "easy";
        if ((strtolower($state) == "hard") or (strtolower($state) == "easy")) {
            $this->state = $state;
        }
    }

    function getState()
    {
        if (!isset($this->state)) {$this->state = "easy";}
        return $this->state;

    }

    function setBank($bank = null)
    {
        //if (($bank == "easy") or ($bank == null)) {
        //    $this->bank = "easy-a03";
        //}

        //if ($bank == "hard") {
        //    $this->bank = "hard-a05";
        //}

        //if ($bank == "16ln") {
            $this->bank = "16ln-a02";
        //}

    }

    function getBank()
    {
        if (!isset($this->bank)) {
            $this->bank = "16ln-a02";
        }

        if (isset($this->inject) and ($this->inject != false)) {
            $arr = explode("-",$this->inject);
            $this->bank = $arr[0] . "-" . $arr[1];
        }
        return $this->bank;
    }

	public function respond()
    {

        $this->getResponse();

		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "acp125g";

        $this->makePNG();

        $this->makeSMS();

        $this->makeMessage();
        // $this->makeTXT();
        $this->makeChoices();

 		$this->thing_report["info"] = "This creates an exercise message.";
 		$this->thing_report["help"] = 'Try CHARLEY. Or NONSENSE.';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        $this->makeWeb();

        $this->makeTXT();
        $this->makePDF();
	}

    function makeChoices ()
    {
       $this->thing->choice->Create($this->agent_name, $this->node_list, "acp125g");
       $this->choices = $this->thing->choice->makeLinks('acp125g');

       $this->thing_report['choices'] = $this->choices;
    }

    function makeSMS()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/acp125g.txt';

        $sms = "ACP 125(G) " . $this->inject . " > \n";
//        $sms .= $this->short_message . "\n" . $this->response;
        $sms .= $this->line_12 . " " . $link . "\n" . $this->response;


        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeACP125G($message = null)
    {
        if (!isset($message['station_destination'])) {$message['station_destination']="X";}

        $sms = "ACP 125(G) " . $this->inject . " > \n";
        $line[1] = "."; // not used
        $line[2] = $message['station_destination'];
        $line[3] = "DE " . $message['station_origin'] . " " . $message['place_filed'];
        $line[4] = $message['number'];
        $line[5] = $message['precedence'] . " " . $message['date_filed'] . " " . $message['time_filed'];
        $line[6] = $message['name_from'] ."/" . $message['name_from'] ."/" .$message['organization_from'] ."/" . $message['number_from'];
        $line[7] = $message['name_to'] ."/" . $message['name_to'] ."/" .$message['organization_to'] ."/" . $message['number_to'];

        $line[8] = "."; // not used - information_addresses
        $line[9] = "."; // not used - exempted_addresses;
        $line[10] = "."; // accounting
        $line[11] = "BT";
        $line[12] = $message['text'];
        $line[13] = "BT";
        $line[14] = ".";
        $line[15] = ".";
        $line[16] = "NNNN";

        //$acp125g = "meep". " | " . $this->response;
        $this->acp125g_message = $line[1] . "\n" .
            $line[2] . "\n" . 
            $line[3] . "\n" . 
            $line[4] . "\n" . 
            $line[5] . "\n" . 
            $line[6] . "\n" . 
            $line[7] . "\n" . 
            $line[8] . "\n" . 
            $line[9] . "\n" . 
            $line[10] . "\n" . 
            $line[11] . "\n" . 
            $line[12] . "\n" . 
            $line[13] . "\n" . 
            $line[14] . "\n" . 
            $line[15] . "\n" . 
            $line[16] . "\n"; 

        $this->thing_report['acp125g'] = $this->acp125g_message;

    }


    function getCast()
    {
        // Load in the cast. And roles.
        // Use the Charley cast.

        // Not used yet.  But will allow use of
        // message bank messages with scenario specific team.

        $file = $this->resource_path .'/charley/charley.txt';
        $contents = file_get_contents($file);

        $handle = fopen($file, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {

                $person_name = $line;
                $arr = explode(",",$line);
                $name= trim($arr[0]);
                if(isset($arr[1])) {$role = trim($arr[1]);} else {$role = "X";}

                // Unique name <> Role mappings. Check?
                $this->name_list[$role] = $name;
                $this->role_list[$name] = $role;

                $this->cast[] = array("name"=>$name, "role"=>$role); 
            }

            fclose($handle);
        } else {
            // error opening the file.
        }
    }

    function getResponse()
    {
        if (isset($this->response)) {return;}
    }

    function getMember()
    {
        if (isset($this->member)) {return;}

        // Load in the cast. And roles.
        $file = $this->resource_path .'/vector/members.txt';
        $contents = file_get_contents($file);

        $handle = fopen($file, "r");

        $count = 0;

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $arr = explode(",",$line);
                $member_call_sign = trim($arr[0]);
                $member_sms = trim($arr[1]);

                if ($this->from == $member_sms) {
                    $this->member['call_sign'] = $member_call_sign;
                    $this->member['sms'] = $member_sms;
                    return;
                }

            }
        }

        if (!isset($this->member['call_sign'])) {
            $this->member['call_sign'] = "ROCKY";
            $this->member['sms'] = "(XXX) XXX-XXXX";
        }
    }

    function getMessages()
    {
        if (isset($this->messages)) {return;}

        // Load in the name of the message bank.
        $this->getBank();

        // Latest transcribed sets.
        $filename = "/vector/messages-" . $this->bank . ".txt";

        $this->filename = $this->bank . ".txt";

        $file = $this->resource_path . $filename;
        $contents = file_get_contents($file);

        $handle = fopen($file, "r");

        $count = 0;

        $bank_info = null;

        if ($handle) {
            while (($line = fgets($handle)) !== false) {

            $line = trim($line);

            $count += 1;

            if ($line == "---") {

                $line_count = count($message) - 1;

                if ($bank_info == null) {
                    $this->title = $message[1];
                    $this->author = $message[2];
                    $this->date = $message[3];
                    $this->version = $message[4];

                    $count = 0;
                    $message = null;

                    $bank_info = array("title"=>$this->title, "author"=>$this->author, "date"=>$this->date, "version"=>$this->version);
                    continue;
                }

/*
                if ($line_count == 10) {
                // recognize as J-format

                $meta = $message[1];

                $name_to = $message[2];
                $position_to = $message[3];
                $organization_to = $message[4];
                $number_to = $message[5];

                $text = $message[6];

                $name_from = $message[7];
                $position_from = $message[8];
                $organization_from = $message[9];
                $number_from = $message[10];

                $message_array = array("meta"=>$meta, "name_to"=>$name_to,
                    "position_to"=>$position_to,  "organization_to"=>$organization_to,
                    "number_to"=>$number_to, "text"=>$text,
                    "name_from"=>$name_from, "position_from"=>$position_from,
                    "organization_from"=>$organization_from,
                    "number_from"=>$number_from );


                $this->messages[] = $message_array;
                }
*/
        if ($line_count == 16) {

                $line = array();

                $message_array = array("line_1"=>$message[1],
                    "line_2"=>$message[2],
                    "line_3"=>$message[3],
                    "line_4"=>$message[4],
                    "line_5"=>$message[5],
                    "line_6"=>$message[6],
                    "line_7"=>$message[7],
                    "line_8"=>$message[8],
                    "line_9"=>$message[9],
                    "line_10"=>$message[10],
                    "line_11"=>$message[11],
                    "line_12"=>$message[12],
                    "line_13"=>$message[13],
                    "line_14"=>$message[14],
                    "line_15"=>$message[15],
                    "line_16"=>$message[16]
                );

                $this->messages[] = $message_array;
        }




                $count = 0;
                $message = null;
                //}
            }


            $message[] = $line;


        }

            fclose($handle);
        } else {
            // error opening the file.
        }
    }

    function getInject()
    {
        $this->getMessages();

        if ((!isset($this->inject)) or ($this->inject == false)) {
            $this->num = array_rand($this->messages);
            $this->inject = $this->bank . "-" .$this->num; 
        }

        if ($this->inject == null) {
            // Pick a random message
            $this->num = array_rand($this->messages);

            $this->inject = $this->bank . "-" . $this->num;
        } else {
            $arr = explode("-",$this->inject);
            $this->bank = $arr[0] . "-" . $arr[1];
            $this->num = $arr[2];
        }
 
   }

    function getMessage()
    {
        $this->getInject();

        $this->getMessages();

        $this->message = $this->messages[$this->num];

        $this->line_1 = $this->message['line_1'];
        $this->line_2 = $this->message['line_2'];
        $this->line_3 = $this->message['line_3'];
        $this->line_4 = $this->message['line_4'];
        $this->line_5 = $this->message['line_5'];
        $this->line_6 = $this->message['line_6'];
        $this->line_7 = $this->message['line_7'];
        $this->line_8 = $this->message['line_8'];
        $this->line_9 = $this->message['line_9'];
        $this->line_10 = $this->message['line_10'];
        $this->line_11 = $this->message['line_11'];
        $this->line_12 = $this->message['line_12'];
        $this->line_13 = $this->message['line_13'];
        $this->line_14 = $this->message['line_14'];
        $this->line_15 = $this->message['line_15'];
        $this->line_16 = $this->message['line_16'];

        $this->short_message = $this->line_1 . "\n" .
            $this->line_2 . "\n" .
            $this->line_3 . "\n" .
            $this->line_4 . "\n" .
            $this->line_5 . "\n" .
            $this->line_6 . "\n" .
            $this->line_7 . "\n" .
            $this->line_8 . "\n" .
            $this->line_9 . "\n" .
            $this->line_10 . "\n" .
            $this->line_11. "\n" .
            $this->line_12 . "\n" .
            $this->line_13 . "\n" .
            $this->line_14 . "\n" .
            $this->line_15 . "\n" . 
            $this->line_16 . "\n";

        $from = explode("/", $this->message['line_6']);
        $this->message['name_from'] = $from[0];
        $this->message['position_from'] = $from[1];
        $this->message['organization_from'] = $from[2];
        $this->message['number_from'] = "X";
        if(isset($from[3])) {$this->message['number_from'] = $from[3];}

        $to = explode("/", $this->message['line_7']);

        $this->message['name_to'] = $to[0];
        $this->message['position_to'] = $to[1];
        $this->message['organization_to'] = $to[2];
        $this->message['number_to'] = "X";
        if(isset($to[3])) {$this->message['number_to'] = $to[3];}

        $this->message['text'] = $this->message['line_12'];

        $this->message['number'] = $this->message['line_4'];
        $this->message['precedence'] = $this->message['line_5'];
        $this->message['hx'] = null; // Not used?
        $this->message['station_origin'] = $this->message['line_3'];
        $this->message['station_destination'] = $this->message['line_2'];

        $this->message['check'] = null;
        $this->message['place_filed'] = null;
        $this->message['time_filed'] = $this->message['line_5'];
        $this->message['date_filed'] = $this->message['line_5'];
    }

    function makeMessage()
    {
        $message = $this->short_message . "<br>";
        $uuid = $this->uuid;
        $message .= "<p>" . $this->web_prefix . "thing/$uuid/acp125g\n \n\n<br> ";
        $this->thing_report['message'] = $message;
    }

    function getBar()
    {
        $this->bar = new Bar($this->thing, "display");
    }


    function setInject()
    {
    }

    //function getInject()
    //{
    //}

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/acp125g';

        $this->node_list = array("acp125g"=>array("acp125g", "rocky", "bullwinkle","charley"));
        // Make buttons
        //$this->thing->choice->Create($this->agent_name, $this->node_list, "rocky");
        //$choices = $this->thing->choice->makeLinks('rocky');

        if (!isset($this->html_image)) {$this->makePNG();}

        $web = "<b>ACP 125(G) Agent</b>";
        $web .= "<p>";


        //$web .= '<a href="' . $link . '">'. $this->html_image . "</a>";
        //$web .= "<br>";

        $web .= "Inject Bank";
        $web .= "<p>";
        $web .= $this->filename . "<br>";
        $web .= $this->title . "<br>";
        $web .= $this->author . "<br>";
        $web .= $this->date . "<br>";
        $web .= $this->version . "<br>";


        $web .= "<p>";

        $web .= "ACP 125(G) inject";
        $web .= "<p>";

        $web .= $this->inject . "<br>";

        $web .= nl2br($this->short_message) . "<br>";

        $web .= "<p>";
        $web .= "Parsed inject";
        $web .= "<p>";


        $web .= "# " . $this->message['number'] . "<br>";
        $web .= "PRECEDENCE " . $this->message['precedence'] . "<br>";
        $web .= "HX " .  $this->message['hx'] . "<br>"; // Not used?
        $web .= "STATION ORIGIN " . $this->message['station_origin'] . "<br>";
        $web .= "CHECKSUM " .$this->message['check'] . "<br>";
        $web .= "PLACE FILED " . $this->message['place_filed'] . "<br>";
        $web .= "TIME FILED " . $this->message['time_filed'] . "<br>";
        $web .= "DATE FILED " . $this->message['date_filed'] . "<br>";

        $web .= "<p>";

        $web .= "TO (NAME) " . $this->message['name_to'] . "<br>";
        $web .= "TO (POSITION) " . $this->message['position_to'] . "<br>";
        $web .= "TO (ORGANIZATION) " . $this->message['organization_to'] . "<br>";
        $web .= "TO (NUMBER) " . $this->message['number_to'] . "<br>";

        $web .= "<p>";

        $web .= $this->message['text'] . "<br>";

        $web .= "<p>";

        $web .= "FROM (NAME) " . $this->message['name_from'] . "<br>";
        $web .= "FROM (POSITION) " . $this->message['position_from'] . "<br>";
        $web .= "FROM (ORGANIZATION) " . $this->message['organization_from'] . "<br>";
        $web .= "FROM (NUMBER) " . $this->message['number_from'] . "<br>";


//        $web .= "<p>";
//        $web .= "SMS inject";
//        $web .= "<p>";


//        $web .= nl2br($this->short_message) . "<br>";


        $web .= "<p>";
        $web .= "PDF inject";
        $web .= "<p>";

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/acp125g.pdf';
        $web .= '<a href="' . $link . '">'. $link . "</a>";
        $web .= "<br>";
        $web .= "<p>";

        $ago = $this->thing->human_time ( time() - strtotime( $this->thing->thing->created_at ) );

        $web .= "Inject was created about ". $ago . " ago.";
        $web .= "<p>";
        $web .= "Inject " . $this->thing->nuuid . " generated at " . $this->thing->thing->created_at. "\n";

        $togo = $this->thing->human_time($this->time_remaining);
        $web .= " and will expire in " . $togo. ".<br>";

        $web .= "<br>";
        $web .= "This proof-of-concept inject is hosted by the " . ucwords($this->word) . " service.  Read the privacy policy at " . $this->web_prefix . "privacy";

        $web .= "<br>";

        $this->thing_report['web'] = $web;


    }

    function makeTXT()
    {
        $txt = "ACP 125(G) traffic.\n";
        $txt .= 'Duplicate messages may exist. Can you de-duplicate?';
        $txt .= "\n";

        $txt .= $this->short_message;

        $txt .= "\n";


        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    public function makePNG()
    {

        $this->image = imagecreatetruecolor(164, 164);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);
        $this->blue = imagecolorallocate($this->image, 0, 68, 255);

        $this->flag_yellow = imagecolorallocate($this->image, 255, 239, 0);

        imagefilledrectangle($this->image, 0, 0, 164, 164, $this->white);
        $textcolor = imagecolorallocate($this->image, 0, 0, 0);

        // $this->drawRocky(164/2,164/2);

        // Write the string at the top left
        $border = 30;
        $radius = 1.165 * (164 - 2 * $border) / 3;



        // devstack add path
        $font = $this->default_font;
        $text = "EXERCISE EXERCISE EXERCISE WELFARE TEST ROCKY 5";
        $text = "INJET";
        $text = $this->message['text'];

        if (!isset($this->bar)) {$this->getBar();}

        $bar_count = $this->bar->bar_count;

        // Add some shadow to the text

        imagettftext($this->image, 40 , 0, 0 - $this->bar->bar_count*5, 75, $this->grey, $font, $text);

        $size = 72;
        $angle = 0;
        $bbox = imagettfbbox ($size, $angle, $font, $text); 
        $bbox["left"] = 0- min($bbox[0],$bbox[2],$bbox[4],$bbox[6]); 
        $bbox["top"] = 0- min($bbox[1],$bbox[3],$bbox[5],$bbox[7]); 
        $bbox["width"] = max($bbox[0],$bbox[2],$bbox[4],$bbox[6]) - min($bbox[0],$bbox[2],$bbox[4],$bbox[6]); 
        $bbox["height"] = max($bbox[1],$bbox[3],$bbox[5],$bbox[7]) - min($bbox[1],$bbox[3],$bbox[5],$bbox[7]); 
        extract ($bbox, EXTR_PREFIX_ALL, 'bb'); 
        //check width of the image 
        $width = imagesx($this->image); 
        $height = imagesy($this->image);
        $pad = 0;

        imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $textcolor, $font, $this->message['station_origin']);

        $size = 10;

        imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height*4/5, $textcolor, $font, $this->message['station_origin']);


        // Small nuuid text for back-checking.
        imagestring($this->image, 2, 140, 0, $this->thing->nuuid, $textcolor);

        // https://stackoverflow.com/questions/14549110/failed-to-delete-buffer-no-buffer-to-delete
        if (ob_get_contents()) ob_clean();

        ob_start();
        imagepng($this->image);
        $imagedata = ob_get_contents();

        ob_end_clean();

        $this->thing_report['png'] = $imagedata;

        $response = '<img src="data:image/png;base64,'.base64_encode($imagedata).'"alt="snowflake"/>';

        $this->PNG_embed = "data:image/png;base64,".base64_encode($imagedata);

         $this->PNG = $imagedata;

        $this->html_image = $response;

        return $response;
    }

    function extractNuuid($input)
    {
        if (!isset($this->duplicables)) {
            $this->duplicables = array();
        }

        return $this->duplicables;
    }

    public function makePDF()
    {
        $txt = $this->thing_report['txt'];

        // initiate FPDI
        $pdf = new Fpdi\Fpdi();


        // http://www.percs.bc.ca/wp-content/uploads/2014/06/PERCS_Message_Form_Ver1.4.pdf
        $pdf->setSourceFile($this->resource_path . 'percs/PERCS_Message_Form_Ver1.4.pdf');
        $pdf->SetFont('Helvetica','',10);

        $tplidx1 = $pdf->importPage(1, '/MediaBox');

        $s = $pdf->getTemplatesize($tplidx1);

        $pdf->addPage($s['orientation'], $s);
        // $pdf->useTemplate($tplidx1,0,0,215);
        $pdf->useTemplate($tplidx1);

        $pdf->SetTextColor(0,0,0);

        $text = "Inject generated at " . $this->thing->thing->created_at. ".";
        $pdf->SetXY(130, 10);
        $pdf->Write(0, $text);


            $this->getQuickresponse($this->web_prefix . 'thing\\' . $this->uuid . '\\acp125g');
            $pdf->Image($this->quick_response_png,199,2,10,10,'PNG');

        //$pdf->SetXY(15, 20);
        //$pdf->Write(0, $this->message['text']);


        $pdf->SetXY(8, 50);
        $pdf->Write(0, $this->message['number']);

        switch (strtolower($this->message['precedence'])) {
            case 'r':
            case 'routine':
                $pdf->SetXY(24, 52.5);
                $pdf->Write(0, "X");
                break;
            case "p":
            case "priority":
                $pdf->SetXY(24, 46);
                $pdf->Write(0, "X");
                break;
            case "w":
            case "welfare":
                $pdf->SetXY(24, 59);
                $pdf->Write(0, "X");
                break;
            case "e":
            case "emergency":
                $pdf->SetXY(24, 39);
                $pdf->Write(0, "X");
                break;
            default:
        }

        $pdf->SetXY(30, 76);
        $pdf->Write(0, strtoupper($this->message['name_to']));

        $pdf->SetXY(30, 76 + 10);
        $pdf->Write(0, strtoupper($this->message['position_to']));

        $pdf->SetXY(30, 76 + 21);
        $pdf->Write(0, strtoupper($this->message['organization_to']));


        $pdf->SetXY(60+44, 168);
        $pdf->Write(0, strtoupper($this->message['name_from']));

        $pdf->SetXY(60+44, 168 + 10);
        $pdf->Write(0, strtoupper($this->message['position_from']));

        $pdf->SetXY(60+44, 168 + 21);
        $pdf->Write(0, strtoupper($this->message['organization_from']));

        //$pdf->SetXY(30, 40);
        //$pdf->Write(0, $this->message['precedence']);

        $pdf->SetXY(50, 40);
        $pdf->Write(0, $this->message['hx']);

        $pdf->SetXY(80, 50);
        $pdf->Write(0, $this->message['station_origin']);

        $pdf->SetXY(112, 50);
        $pdf->Write(0, $this->message['check']);


        $pdf->SetXY(123, 50);
        $pdf->Write(0, $this->message['place_filed']);

        $pdf->SetXY(166, 50);
        $pdf->Write(0, $this->message['time_filed']);

        $pdf->SetXY(181, 50);
        $pdf->Write(0, $this->message['date_filed']);

        $num_rows = 5;
        $num_columns = 5;
        $offset = 0;
        $page =1;
        //$i = 1;

        $i =0;
        $words = explode(" ", $this->message['text']);

        $col_offset = 59;
        $row_offset = 122;
        $col_spacing = 38;
        $row_spacing = 9;

        $row = 0;
        foreach($words as $index=>$word) {
            $col = $index % 5;
            $pdf->SetXY($col_offset + ($col-1) * $col_spacing, $row_offset + $row *$row_spacing);
            $pdf->Write(0, $word);

            if ($col == 4) {$row += 1;}
        }
        $image = $pdf->Output('', 'S');

        $this->thing_report['pdf'] = $image;

        return $this->thing_report['pdf'];
    }

	public function readSubject()
    {

        if (!$this->getMember()) {$this->response = "Generated an inject.";}

        $input = strtolower($this->subject);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {

            if ($input == 'acp125g') {

                $this->getMessage();

                if ((!isset($this->index)) or 
                    ($this->index == null)) {
                    $this->index = 1;
                }
                return;
            }
        }

        $keywords = array("acp125g", "inject", "hard", "16ln", "easy","hey", "rocky","charley","bullwinkle","natasha","boris");
        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    switch($piece) {

                        case 'hard':
                        case 'easy':
                        case '16ln':
                        case 'acp125g':

                            $this->setState($piece);
                            $this->setBank($piece);

                            $this->getMessage();
                            $this->response .= " Set messages to " . strtoupper($this->state) .".";

                            return;

                        case 'hey':
                            $this->getMember();
                            $this->response = "Hey " . strtoupper($this->member['call_sign']) . ".";

                            return;
                        case 'on':
                        default:
                     }
                }
            }
        }

        $this->getMessage();

        if ((!isset($this->index)) or 
            ($this->index == null)) {
            $this->index = 1;
        }
    }
}
