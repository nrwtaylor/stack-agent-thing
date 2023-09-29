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

class IChing extends Agent
{
    /**
     *
     */
    function init()
    {
        $this->thing_report['help'] = 'Text ICHING TELL ME ABOUT SOMETHING.';
        $this->thing_report['info'] = $this->info();

        // Generate an iching reading.

        $this->word = $this->thing->container['stack']['word'];
        $this->email = $this->thing->container['stack']['email'];
        $this->entity_name = $this->thing->container['stack']['entity_name'];

        $this->start_time = $this->thing->elapsed_runtime();
        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->node_list = ["iching" => ["iching", "snowflake"]];

        $this->thing->refresh_at = null; // Never refresh.

    }

    /**
     *
     */
    function get()
    {
        // Take a look at this thing for IChing variables.

        $time_string = $this->thing->Read([
            "iching",
            "refreshed_at",
        ]);

        // And if there is no IChing timestamp create one now.

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["iching", "refreshed_at"],
                $time_string
            );
        }

        $this->reading = $this->thing->Read([
            "iching",
            "reading",
        ]);
    }

    /**
     *
     */
    function set()
    {
        $this->thing->Write(
            ["iching", "reading"],
            $this->reading
        );
    }

    /**
     *
     */
    function run()
    {
        $this->changinglines();
        if ($this->reading == false) {
            $this->reading = $this->hexagramGenerator();
            $this->thing->Write(
                ["iching", "reading"],
                $this->reading
            );
        }
        $this->getHexagram($this->reading);
    }

    /**
     *
     */
    function getReading()
    {
        $line = array();
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
    function setReading()
    {
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
    public function getHexagram($reading = null)
    {
        if ($reading != null) {
            $this->reading = $reading;
        }

        $this->lower = $this->trigramLookup(substr($this->reading, 0, 3));
        $this->upper = $this->trigramLookup(substr($this->reading, 3, 6));

        $this->hexagram_number = $this->readingtoHexagram();
        $this->hexagram_text = $this->interpretHexagram($this->hexagram_number);

        $this->response .= "Got hexagram. ";
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->cost = 50;

        // Thing stuff
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report['txt'] = $this->sms_message;

        // devstack

//        $this->thing->log('found enough balance to send a Message');
        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

//        if ($this->thing->account != false) {
//            $this->thing->account['stack']->Debit($this->cost);
//        }

//        $this->thing->log('NOT enough balance to send a Message');

        $this->thing->Write(
            ["iching", "reading"],
            $this->reading
        );

        return $this->thing_report;
    }

    public function makePNG()
    {
        if (!isset($this->image)) {
            $this->makeImage();
        }

        $agent = new Png($this->thing, "png");
        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;
        $this->PNG_embed = $agent->PNG_embed;

        $this->thing_report["png"] = $agent->PNG;
    }



/*
    public function makePNG()
    {
        $agent = new Png($this->thing, "png");
        $this->makeImage();

        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;

        $this->thing_report['png'] = $agent->image_string;
    }
*/
    /**
     *
     */
    function makeMessage()
    {
        $response = '';
        $response .=
            "Read hexagram " .
            $this->hexagram_number .
            ' ' .
            $this->hexagram_text[0] .
            ' ' .
            $this->hexagram_text[1] .
            ' ' .
            $this->hexagram_text[2] .
            '.';

        $this->message = $response;
        $this->thing_report['message'] = $this->message;
    }

    /**
     *
     */
    function makeEmail()
    {
        if (!isset($this->message)) {
            $this->makeMessage();
        }
        $makeemail_agent = new Makeemail($this->thing, $this->message);

        $this->email_message = $makeemail_agent->email_message;
        $this->thing_report['email'] = $makeemail_agent->email_message;
    }

    /**
     *
     */
    function makeSMS()
    {
        $sms = 'ICHING | ';
        $sms .=
            "Hexagram " .
            $this->hexagram_text[0] .
            " " .
            $this->hexagram_number .
            ' ' .
            $this->hexagram_text[1] .
            ' ' .
            $this->hexagram_text[2];
        $this->changinglines();

        if (count($this->changing_lines) == 0) {
            $sms .= " unchanging.";
        } else {
            if (count($this->changing_lines) == 1) {
                $sms .= " with changing line ";
            } else {
                $sms .= " with changing lines ";
            }
            $sms .= $this->changinglines() . ".";
        }

        $sms .= " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     */
    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $web = "<center>";
        $web .= "<br>";

        $response = '';
        $response = '<div class="content">';
        $response .=
            "Hexagram " .
            $this->hexagram_number .
            ' ' .
            $this->hexagram_text[0] .
            ' ' .
            $this->hexagram_text[1] .
            ' ' .
            $this->hexagram_text[2] .
            '<br>';

        $this->changinglines();
        if (count($this->changing_lines) == 0) {
            $response .= " Unchanging.";
        } else {
            if (count($this->changing_lines) == 1) {
                $response .= "With changing line ";
            } else {
                $response .= "With changing lines ";
            }
            $response .= $this->changinglines() . ".";
        }

        $response .= "<p><br>";

        $response .=
            '<a href = "' .
            $this->cafeausoul($this->hexagram_number)[0] .
            '">Cafe au Soul reading: ' .
            $this->cafeausoul($this->hexagram_number)[1] .
            '</a><br>';

        $response .=
            '<a href = "http://www.jamesdekorne.com/GBCh/hex' .
            $this->hexagram_number .
            '.htm">James deKorne: Hexagram ' .
            $this->hexagram_number .
            '</a>';

        $response .= "<br>";
        $response .= "<p>";

        $response .=
            "upper trigram is " .
            $this->upper[2] .
            ' / ' .
            $this->upper[3] .
            '<br>';
        $response .=
            "lower trigram is " .
            $this->lower[2] .
            ' / ' .
            $this->lower[3] .
            '<br><br>';

        // Embed image
        //        $response .= $this->makeImage();
        $response .= $this->html_image;
        $response .= '<br>';

/*
        $response .=
            '<img src = "' .
            $this->web_prefix .
            'thing/' .
            $this->uuid .
            '/iching.png"
                    alt = "Hexagram ' .
            $this->hexagram_number .
            ' ' .
            $this->hexagram_text[0] .
            ' ' .
            $this->hexagram_text[1] .
            '" longdesc = "' .
            $this->web_prefix .
            'thing/' .
            $this->uuid .
            '/iching.txt">';

        $response .= '<br>';
*/
        $web .= $response;

        $web .= "Hexagram " . $this->hexagram_number . "</center>";

        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    function makeChoices()
    {
        $this->thing->choice->Create("iching", $this->node_list, "iching");

        $choices = $this->thing->choice->makeLinks("iching");
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $status = true;
        return $status;
    }

    /**
     *
     * @return unknown
     */
    public function changinglines()
    {
        $i = 0;
        $x = "";

        $changing_lines = [];
        $lines = str_split(strval($this->reading));

        foreach ($lines as $line) {
            if ($line == 9 or $line == 6) {
                $changing_lines[] = $i + 1;
            }
            $i++;
        }
        $this->changing_lines = $changing_lines;

        foreach ($changing_lines as $index => $changing_line) {
            if ($index + 1 == 1) {
                $x .= $changing_line;
            }
            if ($index + 1 != 1 and $index + 1 < count($changing_lines)) {
                $x .= ", " . $changing_line;
            }
            if (
                $index + 1 == count($changing_lines) and
                count($changing_lines) != 1
            ) {
                $x .= " and " . $changing_line;
            }
        }
        return $x;
    }

    /**
     *
     * @return unknown
     */
    public function readingtoHexagram()
    {
        $i = 0;
        $response = "";

        $input = "";

        foreach (str_split(strval($this->reading)) as $number) {
            if ($number == 9) {
                $input .= 7;
            }
            if ($number == 8) {
                $input .= 6;
            }
            if ($number == 7 or $number == 6) {
                $input .= $number;
            }
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
    public function textHexagram()
    {
        $i = 0;
        $response = "";

        foreach (array_reverse(str_split(strval($this->reading))) as $number) {
            if ($number == 9) {
                $response .= '--- changing<br>';
            }

            if ($number == 8) {
                $response .= '- -<br>';
            }

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
    public function makeImage()
    {
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
            $y_low_left_corner = 4 + 16 * (5 - $i);
            if ($number == 6 or $number == 9) {
                $color = $red;
            } else {
                $color = $black;
            }
            if ($number == 7 or $number == 9) {
                imagefilledrectangle(
                    $im,
                    $border,
                    $y_low_left_corner,
                    $width - $border,
                    $y_low_left_corner + $bar_height,
                    $color
                );
            } else {
                imagefilledrectangle(
                    $im,
                    $border,
                    $y_low_left_corner,
                    26,
                    $y_low_left_corner + $bar_height,
                    $color
                );
                imagefilledrectangle(
                    $im,
                    -4 + 26 + 18 + $border,
                    $y_low_left_corner,
                    $width - $border,
                    $y_low_left_corner + $bar_height,
                    $color
                );
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
    function bitFlip($n_bits)
    {
        $bit_string = "";

        for ($i = 0; $i <= $n_bits - 1; $i++) {
            $bit_string .= rand(0, 1);
        }

        return $bit_string;
    }

    /**
     *
     * @param unknown $trigram_reading
     * @return unknown
     */
    function trigramLookup($trigram_reading)
    {
        $input = "";

        foreach (str_split(strval($trigram_reading)) as $number) {
            if ($number == 7 or $number == 8) {
                $input .= $number;
            }

            if ($number == 6) {
                $input .= 8;
            }

            if ($number == 9) {
                $input .= 7;
            }
        }

        $trigram_lookup = [
            '777' => [0, "Chien", "Chien Qián 乾", "Heaven, Sky, Air"],
            '778' => [1, "Tui", "Tui Duì 兌", "Lake Valley"],
            '787' => [2, "Li", "Li 離", "Fire"],
            '788' => [3, "Chen", "Chen Zhèn 震", "Thunder"],
            '877' => [4, "Sun", "Sun Xùn 巽", "Wind"],
            '878' => [5, "Kan", "K'an 坎", "Water"],
            '887' => [6, "Ken", "Ken Gèn 艮", "Mountain"],
            '888' => [7, "Kun", "K'un Kūn 坤", "Earth"],
        ];

        return $trigram_lookup[$input];
    }

    /**
     *
     * @return unknown
     */
    function trigramGenerator()
    {
        // Heads counts as 2, tails counts as 3

        $random_bits = $this->bitFlip(9);

        $lines = str_split($random_bits, 3);
        $trigram = null;
        $line_readings = [];

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
                    array_push($line_readings, ["yin", "- -", "changing"]);
                    $r .= '6';
                    $trigram .= '0';
                    break;
                case 7:
                    array_push($line_readings, ["yang", "---", ""]);
                    $trigram .= '1';
                    $r .= '7';
                    break;
                case 8:
                    array_push($line_readings, ["yin", "- -", ""]);
                    $trigram .= '0';
                    $r .= '8';

                    break;
                case 9:
                    array_push($line_readings, ["yang", "---", "changing"]);
                    $trigram .= '1';
                    $r .= '9';

                    break;
            }

            $i++;
        }

        return [
            "trigram" => $this->trigramLookup($r),
            "lines" => $line_readings,
            "reading" => $r,
        ];
    }

    /**
     *
     * @param unknown $n
     * @return unknown
     */
    function cafeausoul($n)
    {
        $cafeausoul_lookup = [
            1 => [
                "http://cafeausoul.com/iching/qian-creative",
                "Qián The Creative",
            ],
            2 => [
                "http://cafeausoul.com/iching/kun-receptive",
                "K'un The Receptive",
            ],
            3 => [
                "http://cafeausoul.com/iching/chun-difficult-beginnings",
                "Chun Difficult Beginnings",
            ],
            4 => [
                "http://cafeausoul.com/iching/meng-youthful-folly",
                "Meng Youthful Folly",
            ],
            5 => [
                "http://cafeausoul.com/iching/hsu-nourished-while-waiting",
                "Hsu Nourished While Waiting",
            ],
            6 => [
                "http://cafeausoul.com/iching/sung-conflict",
                "Sung Conflict",
            ],
            7 => ["http://cafeausoul.com/iching/shih-army", "Shih Army"],
            8 => ["http://cafeausoul.com/iching/pi-uniting", "Pi Uniting"],
            9 => [
                "http://cafeausoul.com/iching/hsiao-chu-small-restraint",
                "Hsiao Ch'u Small Restraint",
            ],
            10 => ["http://cafeausoul.com/iching/lu-treading", "Lu Treading"],
            11 => ["http://cafeausoul.com/iching/tai-peace", "T'ai Peace"],
            12 => [
                "http://cafeausoul.com/iching/pi-standstill",
                "P'i Standstill",
            ],
            13 => [
                "http://cafeausoul.com/iching/tung-jen-fellowship",
                "T'ung Jen Fellowship",
            ],
            14 => [
                "http://cafeausoul.com/iching/ta-yu-great-possessing",
                "Ta Yu Great Possessing",
            ],
            15 => [
                "http://cafeausoul.com/iching/qian-authenticity",
                "Qian Authenticity",
            ],
            16 => [
                "http://cafeausoul.com/iching/yu-enthusiasm",
                "Yu Enthusiasm",
            ],
            17 => [
                "http://cafeausoul.com/iching/sui-following",
                "Sui Following",
            ],
            18 => ["http://cafeausoul.com/iching/ku-decay", "Ku Decay"],
            19 => ["http://cafeausoul.com/iching/lin-approach", "Lin Approach"],
            20 => [
                "http://cafeausoul.com/iching/kuan-contemplation",
                "Kuan Contemplation",
            ],
            21 => [
                "http://cafeausoul.com/iching/shi-ho-biting-through",
                "Shi Ho Biting Through",
            ],
            22 => ["http://cafeausoul.com/iching/bi-grace", "Bi Grace"],
            23 => [
                "http://cafeausoul.com/iching/po-split-apart",
                "Po Split Apart",
            ],
            24 => ["http://cafeausoul.com/iching/fu-return", "Fu Return"],
            25 => [
                "http://cafeausoul.com/iching/wu-wang-innocence",
                "Wu Wang Innocence",
            ],
            26 => [
                "http://cafeausoul.com/iching/ta-chu-controlled-power",
                "Ta Ch’u Controlled Power",
            ],
            27 => [
                "http://cafeausoul.com/iching/yi-nourishing-vision",
                "Yi Nourishing Vision",
            ],
            28 => [
                "http://cafeausoul.com/iching/ta-kuo-critical-mass",
                "Ta Kuo Critical Mass",
            ],
            29 => ["http://cafeausoul.com/iching/kn-abyss", "Kǎn Abyss"],
            30 => ["http://cafeausoul.com/iching/li-clarity", "Li Clarity"],
            31 => [
                "http://cafeausoul.com/iching/hsien-influencewooing",
                "Hsien Influence/Wooing",
            ],
            32 => [
                "http://cafeausoul.com/iching/heng-duration",
                "Heng Duration",
            ],
            33 => ["http://cafeausoul.com/iching/tun-retreat", "Tun Retreat"],
            34 => [
                "http://cafeausoul.com/iching/da-zhuang-great-power",
                "Da Zhuang Great Power",
            ],
            35 => [
                "http://cafeausoul.com/iching/chin-progress",
                "Chin Progress",
            ],
            36 => [
                "http://cafeausoul.com/iching/ming-yi-brightness-hiding",
                "Ming Yi Brightness Hiding",
            ],
            37 => [
                "http://cafeausoul.com/iching/chia-jen-family",
                "Chia Jen Family",
            ],
            38 => [
                "http://cafeausoul.com/iching/kuei-opposition",
                "K’uei Opposition",
            ],
            39 => [
                "http://cafeausoul.com/iching/jian-obstruction",
                "Jian Obstruction",
            ],
            40 => [
                "http://cafeausoul.com/iching/jie-liberation",
                "Jie Liberation",
            ],
            41 => ["http://cafeausoul.com/iching/sun-decrease", "Sun Decrease"],
            42 => ["http://cafeausoul.com/iching/yi-increase", "Yi Increase"],
            43 => [
                "http://cafeausoul.com/iching/guai-determination",
                "Guai Determination",
            ],
            44 => [
                "http://cafeausoul.com/iching/gou-coming-meet",
                "Gou Coming to Meet",
            ],
            45 => [
                "http://cafeausoul.com/iching/cui-gathering-together",
                "Cui Gathering Together",
            ],
            46 => [
                "http://cafeausoul.com/iching/sheng-pushing-upward",
                "Sheng Pushing Upward",
            ],
            47 => [
                "http://cafeausoul.com/iching/kun-oppressionexhaustion",
                "Kùn Oppression/Exhaustion",
            ],
            48 => ["http://cafeausoul.com/iching/jing-well", "Jing The Well"],
            49 => [
                "http://cafeausoul.com/iching/ko-moltingrevolution",
                "Ko Molting/Revolution",
            ],
            50 => [
                "http://cafeausoul.com/iching/ting-cauldron",
                "Ting Cauldron",
            ],
            51 => [
                "http://cafeausoul.com/iching/zhen-shocking",
                "Zhen Shocking",
            ],
            52 => [
                "http://cafeausoul.com/iching/ken-keeping-still",
                "Ken Keeping Still",
            ],
            53 => [
                "http://cafeausoul.com/iching/jian-development",
                "Ji’an Development",
            ],
            54 => [
                "http://cafeausoul.com/iching/kui-mei-propriety",
                "Kui Mei Propriety",
            ],
            55 => [
                "http://cafeausoul.com/iching/feng-abundance",
                "Feng Abundance",
            ],
            56 => [
                "http://cafeausoul.com/iching/lu-wanderer",
                "Lu The Wanderer",
            ],
            57 => [
                "http://cafeausoul.com/iching/xun-penetration",
                "Xun Penetration",
            ],
            58 => ["http://cafeausoul.com/iching/tui-joy", "Tui Joy"],
            59 => [
                "http://cafeausoul.com/iching/huan-dispersion",
                "Huan Dispersion",
            ],
            60 => [
                "http://cafeausoul.com/iching/jie-limitation",
                "Jie Limitation",
            ],
            61 => [
                "http://cafeausoul.com/iching/zhong-fu-inner-truth",
                "Zhong Fu Inner Truth",
            ],
            62 => [
                "http://cafeausoul.com/iching/xiao-guo-small-exceeding",
                "Xiao Guo Small Exceeding",
            ],
            63 => [
                "http://cafeausoul.com/iching/chi-chi-after-completion",
                "Chi Chi After Completion",
            ],
            64 => [
                "http://cafeausoul.com/iching/wei-chi-completion",
                "Wei Chi Before Completion",
            ],
        ];

        return $cafeausoul_lookup[$n];
    }

    /**
     *
     * @param unknown $n
     * @return unknown
     */
    function interpretHexagram($n)
    {
        $hexagram_lookup = [
            1 => [
                "乾",
                "qián",
                "Force",
                "the creative",
                "strong action",
                "the key",
                "god",
            ],
            2 => [
                "坤",
                "kūn",
                "Field",
                "the receptive",
                "acquiescence",
                "the flow",
            ],
            3 => [
                "屯",
                "zhūn",
                "Sprouting",
                "difficulty at the beginning",
                "gathering support",
                "hoarding",
            ],
            4 => [
                "蒙",
                "méng",
                "Enveloping",
                "youthful folly",
                "the young shoot",
                "discovering",
            ],
            5 => ["需", "xū", "Attending", "waiting", "moistened", "arriving"],
            6 => ["訟", "sòng", "Arguing", "conflict", "lawsuit"],
            7 => ["師", "shī", "Leading", "the army"],
            8 => ["比", "bǐ", "Grouping", "holding together"],
            9 => [
                "小畜",
                "xiǎo chù",
                "Small Accumulating",
                "the taming power of the small",
            ],
            10 => ["履", "lǚ", "Treading", "treading(conduct)"],
            11 => ["泰", "tài", "Pervading", "peace"],
            12 => ["否", "pǐ", "Obstruction", "standstill (stagnation)"],
            13 => ["同人", "tóng rén", "Concording People", "fellowship"],
            14 => [
                "大有",
                "dà yǒu",
                "Great Possessing",
                "possession in great measure",
            ],
            15 => ["謙", "qiān", "Humbling", "modesty"],
            16 => ["豫", "yù", "Providing-For", "enthusiasm"],
            17 => ["隨", "suí", "Following", "following"],
            18 => ["蠱", "gǔ", "Correction", "work on what has been spoiled"],
            19 => ["臨", "lín", "Nearing", "approach", "the forest"],
            20 => [
                "觀",
                "guān",
                "Viewing",
                "contemplation (view)",
                "looking up",
            ],
            21 => [
                "噬嗑",
                "shì kè",
                "Gnawing Bite",
                "biting through",
                "biting and chewing",
            ],
            22 => ["賁", "bì", "Adorning", "grace", "luxuriance"],
            23 => ["剝", "bō", "Stripping", "splitting apart", "flaying"],
            24 => ["復", "fù", "Returning", "return (the turning point)"],
            25 => ["無妄", "wú wàng", "Without Embroiling"],
            26 => ["大畜", "dà chù", "Great Accumulating"],
            27 => ["頤", "yí", "Swallowing"],
            28 => ["大過", "dà guò", "Great Exceeding"],
            29 => ["坎", "kǎn", "Gorge"],
            30 => ["離", "lí", "Radiance"],
            31 => ["咸", "xián", "Conjoining"],
            32 => ["恆", "héng", "Persevering"],
            33 => ["遯", "dùn", "Retiring"],
            34 => ["大壯", "dà zhuàng", "Great Invigorating"],
            35 => ["晉", "jìn", "Prospering"],
            36 => ["明夷", "míng yí", "Darkening of the Light"],
            37 => [
                "家人",
                "jiā rén",
                "Dwelling People",
                "the family (the clan)",
                "family members",
            ],
            38 => ["睽", "kuí", "Polarising", "opposition", "perversion"],
            39 => ["蹇", "jiǎn", "Limping", "obstruction", "afoot"],
            40 => ["解", "xiè", "Taking-Apart"],
            41 => ["損", "sǔn", "Diminishing"],
            42 => ["益", "yì", "Augmenting"],
            43 => ["夬", "guài", "Displacement"],
            44 => ["姤", "gòu", "Coupling"],
            45 => ["萃", "cuì", "Clustering"],
            46 => ["升", "shēng", "Ascending"],
            47 => ["困", "kùn", "Confining"],
            48 => ["井", "jǐng", "Welling"],
            49 => [
                "革",
                "gé",
                "Skinning",
                "revolution (molting)",
                "the bridle",
            ],
            50 => ["鼎", "dǐng", "Holding"],
            51 => ["震", "zhèn", "Shake"],
            52 => ["艮", "gèn", "Bound"],
            53 => ["漸", "jiàn", "Infiltrating"],
            54 => ["歸妹", "guī mèi", "Converting the Maiden"],
            55 => ["豐", "fēng", "Abounding"],
            56 => ["旅", "lǚ", "Sojourning"],
            57 => [
                "巽",
                "xùn",
                "Ground",
                "the gentle (penetrating wind)",
                "calculations",
            ],
            58 => ["兌", "duì", "Open"],
            59 => ["渙", "huàn", "Dispersing"],
            60 => ["節", "jié", "Articulating"],
            61 => [
                "中孚",
                "zhōng fú",
                "Center Returning",
                "inner truth",
                "central return",
            ],
            62 => [
                "小過",
                "xiǎo guò",
                "Small Exceeding",
                "preponderance of the small",
                "small surpassing",
            ],
            63 => [
                "既濟",
                "jì jì",
                "Already Fording",
                "after completion",
                "already completed",
            ],
            64 => [
                "未濟",
                "wèi jì",
                "Not Yet Fording",
                "before completion",
                "not yet completed",
            ],
        ];

        return $hexagram_lookup[$n];
    }

    /**
     *
     * @param unknown $lower_trigram
     * @param unknown $upper_trigram
     * @return unknown
     */
    function hexagramLookup($lower_trigram, $upper_trigram)
    {
        // upper then lower
        $hexagram_number_lookup = [
            'Chien' => [
                'Chien' => 1,
                'Tui' => 10,
                'Li' => 13,
                'Chen' => 25,
                'Sun' => 44,
                'Kan' => 6,
                'Ken' => 33,
                'Kun' => 12,
            ],
            'Tui' => [
                'Chien' => 43,
                'Tui' => 58,
                'Li' => 49,
                'Chen' => 17,
                'Sun' => 28,
                'Kan' => 47,
                'Ken' => 31,
                'Kun' => 45,
            ],
            'Li' => [
                'Chien' => 14,
                'Tui' => 38,
                'Li' => 30,
                'Chen' => 21,
                'Sun' => 50,
                'Kan' => 64,
                'Ken' => 56,
                'Kun' => 35,
            ],
            'Chen' => [
                'Chien' => 34,
                'Tui' => 54,
                'Li' => 55,
                'Chen' => 51,
                'Sun' => 32,
                'Kan' => 40,
                'Ken' => 62,
                'Kun' => 16,
            ],
            'Sun' => [
                'Chien' => 9,
                'Tui' => 61,
                'Li' => 37,
                'Chen' => 42,
                'Sun' => 57,
                'Kan' => 59,
                'Ken' => 53,
                'Kun' => 20,
            ],
            'Kan' => [
                'Chien' => 5,
                'Tui' => 60,
                'Li' => 63,
                'Chen' => 3,
                'Sun' => 48,
                'Kan' => 29,
                'Ken' => 39,
                'Kun' => 8,
            ],
            'Ken' => [
                'Chien' => 26,
                'Tui' => 41,
                'Li' => 22,
                'Chen' => 27,
                'Sun' => 18,
                'Kan' => 4,
                'Ken' => 52,
                'Kun' => 23,
            ],
            'Kun' => [
                'Chien' => 11,
                'Tui' => 19,
                'Li' => 36,
                'Chen' => 24,
                'Sun' => 46,
                'Kan' => 7,
                'Ken' => 15,
                'Kun' => 2,
            ],
        ];

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
    function hexagramGenerator()
    {
        $upper_trigram = $this->trigramGenerator();
        $lower_trigram = $this->trigramGenerator();

        $hexagram_number = $this->hexagramLookup(
            $lower_trigram['trigram'],
            $upper_trigram['trigram']
        );

        $hexagram = $this->interpretHexagram($hexagram_number);

        $reading = $lower_trigram['reading'] . $upper_trigram['reading'];

        return $reading;
    }
}
