<?php
/**
 * Iching.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;


class IChing extends Agent {


    /**
     *
     */
    function init() {
        $this->thing_report['help'] = 'Text ICHING TELL ME ABOUT SOMETHING.';

        // Generate an iching reading.

        $this->word = $this->thing->container['stack']['word'];
        $this->email = $this->thing->container['stack']['email'];
        $this->entity_name = $this->thing->container['stack']['entity_name'];


        $this->start_time = $this->thing->elapsed_runtime();
        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->node_list = array("iching"=>array("iching", "snowflake"));

    }


    /**
     *
     */
    function get() {

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("iching", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("iching", "refreshed_at"), $time_string );
        }

        $this->thing->json->setField("variables");
        $this->reading = $this->thing->json->readVariable( array("iching", "reading") );

    }


    /**
     *
     */
    function set() {
        $this->thing->json->writeVariable(array("iching", "reading"), $this->reading);
    }


    /**
     *
     */
    function run() {

        $this->changinglines();
        if ( ($this->reading == false) ) {
            $response = $this->hexagramGenerator();
            $this->thing->json->writeVariable( array("iching", "reading"), $this->reading );
        }
        $this->getHexagram($this->reading);

    }


    /**
     *
     */
    function getReading() {

        foreach (str_split(strval($this->reading)) as $number) {

            if ($number == 9) {
                $line[0] = 'yin';
                $line[2] = 'changing';
            }

            if ($number == 7) {
                $line[0] = 'yin';
                $line[2] = '';
            }


            if ($number == 8) {
                $line[0] = 'yang';
                $line[2] = '';
            }

            if ($number == 8) {
                $line[0] = 'yang';
                $line[2] = 'changing';
            }

            $lines[] = $line;

        }
    }


    /**
     *
     * @return unknown
     */
    function setReading() {
        // Not used

        $r = "";

        foreach ($this->lines as $line) {
            if ($line[0] == 'yin') {
                if ($line[2] == 'changing') {
                    $r .= "9";
                } else {
                    $r .= "7";
                }
            }

            if ($line[0] == 'yang') {
                if ($line[2] == 'changing') {
                    $r .= "6";
                } else {
                    $r .= "8";
                }
            }
        }

        $this->reading = $r;
        return $this->reading;
    }


    /**
     *
     * @param unknown $reading (optional)
     */
    public function getHexagram($reading = null) {

        if ($reading != null) {$this->reading = $reading;}

        $this->lower = $this->trigramLookup( substr($this->reading, 0, 3));
        $this->upper = $this->trigramLookup( substr($this->reading, 3, 6));

        $this->hexagram_number = $this->readingtoHexagram();
        $this->hexagram_text = $this->interpretHexagram($this->hexagram_number);

    }


    /**
     *
     * @return unknown
     */
    public function respond() {

        $this->cost = 100;

        // Thing stuff
        $this->thing->flagGreen();


        $this->response = false;

        $this->thing->log( 'divined a reading of ' . $this->reading. "." );

        $this->makePNG();

        $this->makeMessage();
        $this->makeEmail();
        $this->makeSMS();

        $this->makeChoices();
        $this->makeWeb();

        $this->thing_report['txt'] = $this->sms_message;

        if ($this->thing->account['stack']->balance['amount'] >= $this->cost) {

            $this->thing->log('found enough balance to send a Message');
            $message_thing = new Message($this->thing, $this->thing_report);

            $this->thing_report['info'] = $message_thing->thing_report['info'] ;

            $this->thing->account['stack']->Debit($this->cost);

        } else {

            $this->thing->log( 'NOT enough balance to send a Message');

        }


        $this->thing->json->writeVariable(array("iching", "reading"), $this->reading);

        return $this->thing_report;
    }

    public function makePNG() {
        //if (!isset($this->image)) {$this->makeImage();}

        $agent = new Png($this->thing, "png");
        $this->makeImage();

        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;

        //$this->thing_report['png'] = $agent->PNG;
        $this->thing_report['png'] = $agent->image_string;

    }


    /**
     *
     */
    function makeMessage() {

        $response = '';
        $response .= "Read hexagram ". $this->hexagram_number . ' ' .$this->hexagram_text[0] . ' ' . $this->hexagram_text[1]
            . ' ' . $this->hexagram_text[2] . '.';

        $this->message = $response;
        $this->thing_report['message'] = $this->message;

    }


    /**
     *
     */
    function makeEmail() {

        $makeemail_agent = new Makeemail($this->thing, $this->message);

        $this->email_message = $makeemail_agent->email_message;
        $this->thing_report['email'] = $makeemail_agent->email_message;

    }


    /**
     *
     */
    function makeSMS() {
        $this->sms_message = 'ICHING | ';
        $this->sms_message .= "Hexagram ". $this->hexagram_text[0] ." ". $this->hexagram_number . ' ' . $this->hexagram_text[1]  . ' ' . $this->hexagram_text[2];
        $this->changinglines();
        if (count($this->changing_lines ) == 0) {
            $this->sms_message .= " unchanging.";
        } else {
            // Hexagram 小畜 9 xiǎo chù Small Accumulating with changing line 5
            //            if (mb_strlen($this->changinglines()) == 1) {
            if (count($this->changing_lines ) == 1) {

                $this->sms_message .= " with changing line ";
            } else {
                $this->sms_message .= " with changing lines ";
            }
            $this->sms_message .= $this->changinglines() . ".";
        }
        $this->thing_report['sms'] = $this->sms_message;
    }


    /**
     *
     */
    function makeWeb() {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $web = "<center>";
        $web .= "<br>";
        //        $web .= $this->response;


        $response = '';
        $response = '<div class="content">';
        $response .= "Hexagram ". $this->hexagram_number . ' ' .$this->hexagram_text[0] . ' ' . $this->hexagram_text[1]
            . ' ' . $this->hexagram_text[2] . '<br>';

        /*
        if (mb_strlen($this->changinglines()) == 0) {
            $response .= "Unchanging";
        } else {
            $response .= "Changing lines are";
            $response .= $this->changinglines();
        }
        $response .= '<br><br>';
*/
        /*
        if (mb_strlen($this->changinglines()) == 0) {
            $response .= " Unchanging.";
        } else {
            // Hexagram 小畜 9 xiǎo chù Small Accumulating with changing line 5
            if (mb_strlen($this->changinglines()) == 2) {
                $response .= "With changing line ";
            } else {
                $response .= "With changing lines ";
            }
            $response .= $this->changinglines() . ".";
        }
*/
        $this->changinglines();
        if (count($this->changing_lines ) == 0) {
            $response .= " Unchanging.";
        } else {
            // Hexagram 小畜 9 xiǎo chù Small Accumulating with changing line 5
            //            if (mb_strlen($this->changinglines()) == 1) {
            if (count($this->changing_lines ) == 1) {

                $response .= "With changing line ";
            } else {
                $response .= "With changing lines ";
            }
            $response .= $this->changinglines() . ".";
        }





        $response .= "<p><br>";

        $response .= '<a href = "' . $this->cafeausoul($this->hexagram_number)[0] . '">Cafe au Soul reading: ' . $this->cafeausoul($this->hexagram_number)[1] . '</a><br>';


        $response .= '<a href = "http://www.jamesdekorne.com/GBCh/hex' . $this->hexagram_number . '.htm">James deKorne: Hexagram ' .$this->hexagram_number.'</a>';

        $response .= "<br>";
        $response .= "<p>";

        $response .= "upper trigram is ".$this->upper[2] . ' / ' .$this->upper[3] . '<br>';
        $response .= "lower trigram is ".$this->lower[2] . ' / ' .$this->lower[3] . '<br><br>';

        // Embed image
        //        $response .= $this->makeImage();
        $response .= $this->html_image;
        $response .= '<br>';

                $response .= '<img src = "' . $this->web_prefix . 'thing/' . $this->uuid . '/iching.png"
                    alt = "Hexagram ' . $this->hexagram_number . ' ' . $this->hexagram_text[0] . ' '.  $this->hexagram_text[1] . '" longdesc = "' . $this->web_prefix . 'thing/' . $this->uuid . '/iching.txt">';


        $response .= '<br>';


        $web .= $response;

        $web .= "Hexagram " . $this->hexagram_number . "</center>";

        $this->thing_report['web'] = $web;
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create("iching", $this->node_list, "iching");

        $choices = $this->thing->choice->makeLinks("iching");
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {
        $status = true;
        return $status;
    }



    /**
     *
     * @return unknown
     */
    public function changinglines() {
        $i = 0;
        $x = "";

        $changing_lines = array();
        $lines = str_split(strval($this->reading));

        foreach ($lines as $line) {

            if ( ($line == 9 ) or ($line == 6) ) {
                $changing_lines[] = ($i+1);
            }
            $i++;
        }
        $this->changing_lines = $changing_lines;

        foreach ($changing_lines as $index=>$changing_line) {

            if (($index+1) == 1) {$x .= $changing_line;}
            if ( (($index+1) != 1) and (($index+1) < count($changing_lines))) {$x .= ", " . $changing_line;}
            if ( (($index+1) == count($changing_lines)) and (count($changing_lines) != 1) ) {$x .= " and " . $changing_line;}


        }
        return $x;
    }


    /**
     *
     * @return unknown
     */
    public function readingtoHexagram() {
        $i = 0;
        $response ="";

        $input = "";

        foreach (str_split(strval($this->reading)) as $number) {

            if ( $number == 9 ) {$input .= 7;}
            if ( $number == 8) {$input .= 6;}
            if ( ($number == 7) or ($number == 6) ) {$input .= $number;}

        }

        $lower = substr($input, 0, 3);
        $upper = substr($input, 3, 6);

        $lower_trigram = $this->trigramLookup($lower);
        $upper_trigram = $this->trigramLookup($upper);

        $number = $this->hexagramLookup($lower_trigram, $upper_trigram);

        return $number;
    }



    /**
     *
     * @return unknown
     */
    public function textHexagram() {
        $i = 0;
        $response ="";

        foreach (array_reverse(str_split(strval($this->reading))) as $number) {

            if ( $number == 9 ) {$response .= '--- changing<br>';}

            if ( $number == 8) {$response .= '- -<br>';}


            if ($number == 7) {
                $response .= '---<br>';
            }


            if ($number == 6) {
                $response .= '- - changing<br>';
            }

        }
        $this->text_hexagram = $response;
        return $this->text_hexagram;
    }


    /**
     *
     * @return unknown
     */
    public function makeImage() {

        $width = 70;
        $bar_height = 8;
        $border = 4;

        // Create a 70x100 image
        $im = imagecreatetruecolor($width, 100);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        $red = imagecolorallocate($im, 255, 0, 100);

        // Make the background transparent
        imagecolortransparent($im, $white);
        imagefilledrectangle($im, 0, 0, 70, 100, $white);

        $x_centre = $width / 2;

        $i = 0;

        foreach (str_split(strval($this->reading)) as $number) {

            $y_low_left_corner = 4+16*(5-$i);
            if ( ($number == 6) or ($number == 9) ) {$color = $red;} else {$color = $black;}
            if ( ($number == 7) or ($number == 9) ) {
                imagefilledrectangle($im, $border, $y_low_left_corner, $width-$border,  $y_low_left_corner + $bar_height, $color);
            } else {
                imagefilledrectangle($im, $border, $y_low_left_corner, 26, $y_low_left_corner + $bar_height, $color);
                imagefilledrectangle($im, -4+26+18+$border, $y_low_left_corner, $width-$border, $y_low_left_corner + $bar_height, $color);
            }
            $i++;
        }

        $this->image = $im;
    }


    /**
     *
     * @param unknown $n_bits
     * @return unknown
     */
    function bitFlip($n_bits) {

        $bit_string = "";

        for ($i = 0; $i <= $n_bits-1; $i++) {

            $bit_string .= rand(0, 1);

        }



        return $bit_string;
    }



    /**
     *
     * @param unknown $trigram_reading
     * @return unknown
     */
    function trigramLookup($trigram_reading) {
        $input = "";

        foreach (str_split(strval($trigram_reading)) as $number) {
            if (($number == 7) or ($number == 8)) {
                $input .= $number;
            }

            if ( $number == 6) {
                $input .= 8;
            }

            if ( $number == 9) {
                $input .= 7;
            }


        }

        $trigram_lookup = array('777' => array(0, "Chien", "Chien Qián 乾", "Heaven, Sky, Air"),
            '778' => array(1, "Tui", "Tui Duì 兌", "Lake Valley"),
            '787' => array(2, "Li", "Li 離", "Fire"),
            '788' => array(3, "Chen", "Chen Zhèn 震", "Thunder"),
            '877' => array(4, "Sun", "Sun Xùn 巽", "Wind"),
            '878' => array(5, "Kan", "K'an 坎", "Water"),
            '887' => array(6, "Ken", "Ken Gèn 艮", "Mountain"),
            '888' => array(7, "Kun", "K'un Kūn 坤", "Earth"),
        );

        return $trigram_lookup[$input];

    }


    /**
     *
     * @return unknown
     */
    function trigramGenerator() {

        // Heads counts as 2, tails counts as 3

        $random_bits = $this->bitFlip(9);

        $lines = str_split($random_bits, 3);
        $trigram = null;
        $line_readings = array();

        $i = 0;

        $r = "";


        foreach ($lines as $line) {

            $line_sum = null;
            $line = str_split($line, 1);

            foreach ($line as $cointoss) {
                if ($cointoss == 1) {
                    $line_sum = $line_sum + 2;
                } else {

                    $line_sum = $line_sum + 3;
                }

            }


            switch ($line_sum) {
            case 6:
                array_push($line_readings, array("yin", "- -", "changing"));
                $r .= '6';
                $trigram .= '0';
                break;
            case 7:
                array_push($line_readings, array("yang", "---", ""));
                $trigram .= '1';
                $r .= '7';
                break;
            case 8:
                array_push($line_readings, array("yin", "- -", ""));
                $trigram .= '0';
                $r .= '8';

                break;
            case 9:
                array_push($line_readings, array("yang", "---", "changing"));
                $trigram .= '1';
                $r .= '9';

                break;
            }



            $i++;
        }

        return array("trigram"=>$this->trigramLookup($r), "lines"=>$line_readings, "reading"=>$r);
    }


    /**
     *
     * @param unknown $n
     * @return unknown
     */
    function cafeausoul($n) {

        $cafeausoul_lookup = array(
            1 => array("http://cafeausoul.com/iching/qian-creative", "Qián The Creative"),
            2 => array("http://cafeausoul.com/iching/kun-receptive", "K'un The Receptive"),
            3 => array("http://cafeausoul.com/iching/chun-difficult-beginnings", "Chun Difficult Beginnings"),
            4 => array("http://cafeausoul.com/iching/meng-youthful-folly", "Meng Youthful Folly"),
            5 => array("http://cafeausoul.com/iching/hsu-nourished-while-waiting", "Hsu Nourished While Waiting"),
            6 => array("http://cafeausoul.com/iching/sung-conflict", "Sung Conflict"),
            7 => array("http://cafeausoul.com/iching/shih-army", "Shih Army"),
            8 => array("http://cafeausoul.com/iching/pi-uniting", "Pi Uniting"),
            9 => array("http://cafeausoul.com/iching/hsiao-chu-small-restraint", "Hsiao Ch'u Small Restraint"),
            10 => array("http://cafeausoul.com/iching/lu-treading", "Lu Treading"),
            11 => array("http://cafeausoul.com/iching/tai-peace", "T'ai Peace"),
            12 => array("http://cafeausoul.com/iching/pi-standstill", "P'i Standstill"),
            13 => array("http://cafeausoul.com/iching/tung-jen-fellowship", "T'ung Jen Fellowship"),
            14 => array("http://cafeausoul.com/iching/ta-yu-great-possessing", "Ta Yu Great Possessing"),
            15 => array("http://cafeausoul.com/iching/qian-authenticity", "Qian Authenticity"),
            16 => array("http://cafeausoul.com/iching/yu-enthusiasm", "Yu Enthusiasm"),
            17 => array("http://cafeausoul.com/iching/sui-following", "Sui Following"),
            18 => array("http://cafeausoul.com/iching/ku-decay", "Ku Decay"),
            19 => array("http://cafeausoul.com/iching/lin-approach", "Lin Approach"),
            20 => array("http://cafeausoul.com/iching/kuan-contemplation", "Kuan Contemplation"),
            21 => array("http://cafeausoul.com/iching/shi-ho-biting-through", "Shi Ho Biting Through"),
            22 => array("http://cafeausoul.com/iching/bi-grace", "Bi Grace"),
            23 => array("http://cafeausoul.com/iching/po-split-apart", "Po Split Apart"),
            24 => array("http://cafeausoul.com/iching/fu-return", "Fu Return"),
            25 => array("http://cafeausoul.com/iching/wu-wang-innocence", "Wu Wang Innocence"),
            26 => array("http://cafeausoul.com/iching/ta-chu-controlled-power", "Ta Ch’u Controlled Power"),
            27 => array("http://cafeausoul.com/iching/yi-nourishing-vision", "Yi Nourishing Vision"),
            28 => array("http://cafeausoul.com/iching/ta-kuo-critical-mass", "Ta Kuo Critical Mass"),
            29 => array("http://cafeausoul.com/iching/kn-abyss", "Kǎn Abyss"),
            30 => array("http://cafeausoul.com/iching/li-clarity", "Li Clarity"),
            31 => array("http://cafeausoul.com/iching/hsien-influencewooing", "Hsien Influence/Wooing"),
            32 => array("http://cafeausoul.com/iching/heng-duration", "Heng Duration"),
            33 => array("http://cafeausoul.com/iching/tun-retreat", "Tun Retreat"),
            34 => array("http://cafeausoul.com/iching/da-zhuang-great-power", "Da Zhuang Great Power"),
            35 => array("http://cafeausoul.com/iching/chin-progress", "Chin Progress"),
            36 => array("http://cafeausoul.com/iching/ming-yi-brightness-hiding", "Ming Yi Brightness Hiding"),
            37 => array("http://cafeausoul.com/iching/chia-jen-family", "Chia Jen Family"),
            38 => array("http://cafeausoul.com/iching/kuei-opposition", "K’uei Opposition"),
            39 => array("http://cafeausoul.com/iching/jian-obstruction", "Jian Obstruction"),
            40 => array("http://cafeausoul.com/iching/jie-liberation", "Jie Liberation"),
            41 => array("http://cafeausoul.com/iching/sun-decrease", "Sun Decrease"),
            42 => array("http://cafeausoul.com/iching/yi-increase", "Yi Increase"),
            43 => array("http://cafeausoul.com/iching/guai-determination", "Guai Determination"),
            44 => array("http://cafeausoul.com/iching/gou-coming-meet", "Gou Coming to Meet"),
            45 => array("http://cafeausoul.com/iching/cui-gathering-together", "Cui Gathering Together"),
            46 => array("http://cafeausoul.com/iching/sheng-pushing-upward", "Sheng Pushing Upward"),
            47 => array("http://cafeausoul.com/iching/kun-oppressionexhaustion", "Kùn Oppression/Exhaustion"),
            48 => array("http://cafeausoul.com/iching/jing-well", "Jing The Well"),
            49 => array("http://cafeausoul.com/iching/ko-moltingrevolution", "Ko Molting/Revolution"),
            50 => array("http://cafeausoul.com/iching/ting-cauldron", "Ting Cauldron"),
            51 => array("http://cafeausoul.com/iching/zhen-shocking", "Zhen Shocking"),
            52 => array("http://cafeausoul.com/iching/ken-keeping-still", "Ken Keeping Still"),
            53 => array("http://cafeausoul.com/iching/jian-development", "Ji’an Development"),
            54 => array("http://cafeausoul.com/iching/kui-mei-propriety", "Kui Mei Propriety"),
            55 => array("http://cafeausoul.com/iching/feng-abundance", "Feng Abundance"),
            56 => array("http://cafeausoul.com/iching/lu-wanderer", "Lu The Wanderer"),
            57 => array("http://cafeausoul.com/iching/xun-penetration", "Xun Penetration"),
            58 => array("http://cafeausoul.com/iching/tui-joy", "Tui Joy"),
            59 => array("http://cafeausoul.com/iching/huan-dispersion", "Huan Dispersion"),
            60 => array("http://cafeausoul.com/iching/jie-limitation", "Jie Limitation"),
            61 => array("http://cafeausoul.com/iching/zhong-fu-inner-truth", "Zhong Fu Inner Truth"),
            62 => array("http://cafeausoul.com/iching/xiao-guo-small-exceeding", "Xiao Guo Small Exceeding"),
            63 => array("http://cafeausoul.com/iching/chi-chi-after-completion", "Chi Chi After Completion"),
            64 => array("http://cafeausoul.com/iching/wei-chi-completion", "Wei Chi Before Completion"));


        return $cafeausoul_lookup[$n];
    }


    /**
     *
     * @param unknown $n
     * @return unknown
     */
    function interpretHexagram($n) {



        $hexagram_lookup = array(
            1 => array("乾" , "qián", "Force", "the creative", "strong action", "the key", "god"),
            2 => array("坤" , "kūn", "Field", "the receptive", "acquiescence", "the flow"),
            3 => array("屯", "zhūn", "Sprouting", "difficulty at the beginning", "gathering support", "hoarding"),
            4 => array("蒙", "méng", "Enveloping", "youthful folly", "the young shoot", "discovering"),
            5 => array("需", "xū", "Attending", "waiting", "moistened", "arriving"),
            6 => array("訟", "sòng", "Arguing", "conflict", "lawsuit"),
            7 => array("師", "shī", "Leading", "the army"),
            8 => array("比", "bǐ", "Grouping", "holding together"),
            9 => array("小畜", "xiǎo chù", "Small Accumulating", "the taming power of the small"),
            10 => array("履", "lǚ", "Treading", "treading(conduct)"),
            11 => array("泰", "tài", "Pervading", "peace"),
            12 => array("否", "pǐ", "Obstruction", "standstill (stagnation)"),
            13 => array("同人", "tóng rén", "Concording People", "fellowship"),
            14 => array("大有", "dà yǒu", "Great Possessing", "possession in great measure"),
            15 => array("謙", "qiān", "Humbling", "modesty"),
            16 => array("豫", "yù", "Providing-For", "enthusiasm"),
            17 => array("隨", "suí", "Following" , "following"),
            18 => array("蠱", "gǔ", "Correction", "work on what has been spoiled"),
            19 => array("臨", "lín", "Nearing", "approach", "the forest"),
            20 => array("觀", "guān", "Viewing", "contemplation (view)", "looking up"),
            21 => array("噬嗑", "shì kè", "Gnawing Bite", "biting through", "biting and chewing"),
            22 => array("賁", "bì", "Adorning", "grace", "luxuriance"),
            23 => array("剝", "bō", "Stripping", "splitting apart", "flaying"),
            24 => array("復", "fù", "Returning", "return (the turning point)"),
            25 => array("無妄", "wú wàng", "Without Embroiling"),
            26 => array("大畜", "dà chù", "Great Accumulating"),
            27 => array("頤", "yí", "Swallowing"),
            28 => array("大過", "dà guò", "Great Exceeding"),
            29 => array("坎", "kǎn", "Gorge"),
            30 => array("離", "lí", "Radiance"),
            31 => array("咸", "xián", "Conjoining"),
            32 => array("恆", "héng", "Persevering"),
            33 => array("遯", "dùn", "Retiring"),
            34 => array("大壯", "dà zhuàng", "Great Invigorating"),
            35 => array("晉", "jìn", "Prospering"),
            36 => array("明夷", "míng yí", "Darkening of the Light"),
            37 => array("家人", "jiā rén", "Dwelling People", "the family (the clan)", "family members"),
            38 => array("睽", "kuí", "Polarising", "opposition", "perversion"),
            39 => array("蹇", "jiǎn", "Limping", "obstruction", "afoot"),
            40 => array("解", "xiè", "Taking-Apart"),
            41 => array("損", "sǔn", "Diminishing"),
            42 => array("益", "yì", "Augmenting"),
            43 => array("夬", "guài", "Displacement"),
            44 => array("姤", "gòu", "Coupling"),
            45 => array("萃", "cuì", "Clustering"),
            46 => array("升", "shēng", "Ascending"),
            47 => array("困", "kùn", "Confining"),
            48 => array("井", "jǐng", "Welling"),
            49 => array("革", "gé", "Skinning", "revolution (molting)", "the bridle"),
            50 => array("鼎", "dǐng", "Holding"),
            51 => array("震", "zhèn", "Shake"),
            52 => array("艮", "gèn", "Bound"),
            53 => array("漸", "jiàn", "Infiltrating"),
            54 => array("歸妹", "guī mèi", "Converting the Maiden"),
            55 => array("豐", "fēng", "Abounding"),
            56 => array("旅", "lǚ", "Sojourning"),
            57 => array("巽", "xùn", "Ground", "the gentle (penetrating wind)", "calculations"),
            58 => array("兌", "duì", "Open"),
            59 => array("渙", "huàn", "Dispersing"),
            60 => array("節", "jié", "Articulating"),
            61 => array("中孚", "zhōng fú", "Center Returning", "inner truth", "central return"),
            62 => array("小過", "xiǎo guò", "Small Exceeding", "preponderance of the small", "small surpassing"),
            63 => array("既濟", "jì jì", "Already Fording", "after completion", "already completed"),
            64 => array("未濟", "wèi jì", "Not Yet Fording", "before completion", "not yet completed")
        );


        return $hexagram_lookup[$n];
    }


    /**
     *
     * @param unknown $lower_trigram
     * @param unknown $upper_trigram
     * @return unknown
     */
    function hexagramLookup($lower_trigram, $upper_trigram) {
        // upper then lower
        $hexagram_number_lookup = array('Chien' => array('Chien' => 1, 'Tui'=>10, 'Li'=> 13, 'Chen'=>25, 'Sun' => 44, 'Kan' =>6, 'Ken'=>33, 'Kun' =>12),
            'Tui' => array('Chien' => 43, 'Tui'=>58, 'Li'=> 49, 'Chen'=>17, 'Sun' => 28, 'Kan' => 47, 'Ken'=>31, 'Kun' => 45),
            'Li' => array('Chien' => 14, 'Tui'=>38, 'Li'=> 30, 'Chen'=>21, 'Sun' => 50, 'Kan' => 64, 'Ken'=>56, 'Kun' => 35),
            'Chen' => array('Chien' => 34, 'Tui'=> 54, 'Li'=> 55, 'Chen'=> 51, 'Sun' => 32, 'Kan' => 40, 'Ken'=> 62, 'Kun' => 16),
            'Sun' => array('Chien' => 9, 'Tui'=> 61, 'Li'=> 37, 'Chen'=> 42, 'Sun' => 57, 'Kan' => 59, 'Ken'=> 53, 'Kun' => 20),
            'Kan' => array('Chien' => 5, 'Tui'=> 60, 'Li'=> 63, 'Chen'=> 3, 'Sun' => 48, 'Kan' => 29, 'Ken'=> 39, 'Kun' => 8),
            'Ken' => array('Chien' => 26, 'Tui'=> 41, 'Li'=> 22, 'Chen'=> 27, 'Sun' => 18, 'Kan' => 4, 'Ken'=> 52, 'Kun' => 23),
            'Kun' => array('Chien' => 11, 'Tui'=> 19, 'Li'=> 36, 'Chen'=> 24, 'Sun' => 46, 'Kan' => 7, 'Ken'=> 15, 'Kun' => 2));

        // https://en.wikipedia.org/wiki/List_of_hexagrams_of_the_I_Ching

        $u = $upper_trigram[1];
        $l = $lower_trigram[1];

        $this->hexagram_number = $hexagram_number_lookup[$u][$l];

        return $this->hexagram_number;
    }


    /**
     *
     * @return unknown
     */
    function hexagramGenerator() {

        $upper_trigram = $this->trigramGenerator();
        $lower_trigram = $this->trigramGenerator();

        $hexagram_number = $this->hexagramLookup( $lower_trigram['trigram'] , $upper_trigram['trigram'] );

        $hexagram = $this->interpretHexagram($hexagram_number);

        $this->reading = $lower_trigram['reading'] . $upper_trigram['reading'];

        return $this->reading;
    }


}
