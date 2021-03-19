<?php
/**
 * Tone.php
 *
 * @package default
 */

// devstack work on extraction of chords ... then tones.

namespace Nrwtaylor\StackAgentThing;

class Tone extends Agent
{
    public $var = 'hello';

    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init()
    {
        //        $this->agent_name = "bear";
        $this->test = "Development code";
        $this->thing_report["info"] = "This is a tone.";
        $this->thing_report["help"] = "Holds a tone for the channel.";

        $this->sample_rate = 44100;

        $this->initTone();
    }

    /**
     *
     */
    private function getNegativetime()
    {
        // And example of using another agent to get information the cat needs.
        $agent = new Negativetime($this->thing, "tone");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    /**
     *
     */
    function makeSMS()
    {
        $this->node_list = ["tone" => ["tone"]];
        $m = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }

    /**
     *
     * @return unknown
     */
    public function respond()
    {
        $this->thing->flagGreen();

        $this->makeSMS();
        $this->makeChoices();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    /**
     *
     */
    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "tone");
        $choices = $this->thing->choice->makeLinks('bear');
        $this->thing_report['choices'] = $choices;
    }

    /**
     *
     * @param unknown $text (optional)
     */
    function doTone($text = null)
    {
        // Yawn.

        $this->response .= ". ";

        if ($this->agent_input == null) {
            $this->bear_message = $this->response;
        } else {
            $this->bear_message = $this->agent_input;
        }
    }

    public function extractTones($text)
    {
        $array = str_split($text);

        $this->semitones = [];
        foreach ($this->semitone_array as $semitone => $frequency) {
            $this->semitones[] = $semitone;
        }

        $tokens = explode(" ", $text);
        $matches = [];
        foreach ($tokens as $i => $token) {
            foreach ($this->semitones as $j => $semitone) {
                $flag = false;
                if (strtolower($token) == strtolower($semitone)) {
                    $flag = true;
                }
                if (strtolower($token) == '[' . strtolower($semitone) . ']') {
                    $flag = true;
                }

                if ($flag == true) {
                    $matches[] = $semitone;
                }
            }
        }

        $this->tones = $matches;

if ($matches == array()) {
$this->response .= "Did not see tones. ";
} else {
        $this->response .= 'Found ' . implode(' ', $matches) . ". ";
}

        return;

// devstack

        $regex_string =
            '^\b([CDEFGAB](?:b|bb)*(?:#|##|sus|maj|min|aug)*[\d\/]*(?:[CDEFGAB](?:b|bb)*(?:#|##|sus|maj|min|aug)*[\d\/]*)*)(?=\s|$)(?! \w)/i^';
        //$regex_string = '^\b[A-G](?:m|b|#|sus|\d)*(?:\b|(?<=#))^';

        preg_match_all($regex_string, $text, $matches);

        $list = "/\b(?:" . implode($this->semitones, "|") . ")\b/i"; // <-- Here, the (?:...) groups alternatives
        //$my_string= "This is my testing";
        if (preg_match($list, $text, $matches, PREG_OFFSET_CAPTURE)) {
        }

        foreach ($array as $char) {
            foreach ($this->semitone_array as $semitone => $frequency) {
            }
        }
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $i = $this->input;

        // Strip out references to @ednabot.
        // devstack This should be handled by Agent.
        $whatIWant = $this->input;
        if (($pos = strpos(strtolower($this->input), "tone")) !== false) {
            $whatIWant = substr(
                strtolower($this->input),
                $pos + strlen("tone")
            );
        } elseif (($pos = strpos(strtolower($this->input), "tone")) !== false) {
            $whatIWant = substr(
                strtolower($this->input),
                $pos + strlen("tone")
            );
        }
        $i = trim($whatIWant);

        $this->extractTones($i);

        $this->tone_response = "No tone.";

        $this->doTone($i);
        return false;
    }

    //        $this->sample_rate = 44100;

    //        $this->initTone();

    function frequencyTone($note, $octave = 4)
    {
        if ($note == null) {
            return 0;
        }
        $base_octave = 4;
        $n = $this->semitone_array[$note];
        $b = $this->a_frequency * pow(2, $n / 1200);

        $b = $b * pow(2, $octave - $base_octave);

        return $b;
    }

    function testTone()
    {
        if (!$this->frequencyTone("A") == 440) {
            $this->thing->console("ERROR");
        } else {
            $this->thing->console("PASS");
        }

        if (!$this->frequencyTone("A", 3) == 220) {
            $this->thing->console("ERROR");
        } else {
            $this->thing->console("PASS");
        }
        if (!$this->frequencyTone("A", 5) == 880) {
            $this->thing->console("ERROR");
        } else {
            $this->thing->console("PASS");
        }

        foreach ($this->semitone_array as $note => $cent) {
            $this->thing->console($note . " > " . $this->frequencyTone($note) . "\n");
        }

    }

    function initTone($tuning = "other")
    {
        //$this->c_frequency = 440;
        $this->a_frequency = 440;

        switch ($tuning) {
            case "equal tone":
                // Equal tone
                $a = 0.0;
                $b_flat = 100.0;
                $b = 200.0;
                $c = 300.0;
                $c_sharp = 400.0;
                $d = 500.0;
                $e_flat = 600.0;
                $e = 700.0;
                $f = 800.0;
                $f_sharp = 900.0;
                $g = 1000.0;
                $g_sharp = 1100.0;
                break;
            case "other":
                // Other
                $a = 0.0;
                $b_flat = 76.0;
                $b = 193.2;
                $c = 310.3;
                $c_sharp = 386.3;
                $d = 503.4;
                $e_flat = 579.5;
                $e = 696.6;
                $f = 772.6;
                $f_sharp = 889.7;
                $g = 1006.0;
                $g_sharp = 1082.9;
                break;
        }

        if (!isset($d_flat)) {
            $d_flat = $c_sharp;
        }
        if (!isset($d_sharp)) {
            $d_sharp = $e_flat;
        }
        if (!isset($a_sharp)) {
            $a_sharp = $b_flat;
        }
        if (!isset($g_flat)) {
            $g_flat = $f_sharp;
        }

        $this->semitone_array = [
            "A" => $a,
            "Bb" => $b_flat,
            "B" => $b,
            "C" => $c,
            "C#" => $c_sharp,
            "D" => $d,
            "Eb" => $e_flat,
            "E" => $e,
            "F" => $f,
            "F#" => $f_sharp,
            "G" => $g,
            "G#" => $g_sharp,
            //"A"=>$a,
            //"Bb"=>$b_flat,
            //"B"=>$b
        ];

        $this->semitone_array = [
            "A" => $a,
            "A#" => $b_flat,
            "B" => $b,
            "C" => $c,
            "C#" => $c_sharp,
            "D" => $d,
            "D#" => $e_flat,
            "E" => $e,
            "F" => $f,
            "F#" => $f_sharp,
            "G" => $g,
            "G#" => $g_sharp,
            //"A"=>$a,
            //"Bb"=>$b_flat,
            //"B"=>$b
        ];

        $this->semitone_array = [
            "A" => $a,
            "Bb" => $b_flat,
            "A#" => $a_sharp,
            "B" => $b,
            "C" => $c,
            "C#" => $c_sharp,
            "Db" => $d_flat,
            "D" => $d,
            "D#" => $d_sharp,
            "Eb" => $e_flat,
            "E" => $e,
            "F" => $f,
            "F#" => $f_sharp,
            "Gb" => $g_flat,
            "G" => $g,
            "G#" => $g_sharp,
            //"A"=>$a,
            //"Bb"=>$b_flat,
            //"B"=>$b
        ];
    }

    function toneTone(
        $freqOfTone = null,
        $samplesCount = 44100,
        $amplitude_level = 1.0
    ) {
        if (!isset($this->total_samples)) {
            $this->total_samples = 0;
        }

        //https://github.com/kite1988/nus-sms-corpus/blob/master/README.md
        //https://stackoverflow.com/questions/28053226/generate-wav-tone-in-php
        //https://stackoverflow.com/questions/28053226/generate-wav-tone-in-php
        //$freqOfTone = 440;
        if (!isset($this->sample_rate)) {
            $sample_rate = 44100;
        } else {
            $sample_rate = $this->sample_rate;
        }

        //$sampleRate = 44100;
        //$samplesCount = 80000;

        if ($freqOfTone == null) {
            $amplitude = 0;
            $w = 0;
        } else {
            $amplitude = 0.25 * 32768 * $amplitude_level;
            $w = (2 * pi() * $freqOfTone) / $sample_rate;
        }

        $mod = $freqOfTone % $sample_rate;
        $mod = 0;
        $samples = [];

        for (
            $n = $this->total_samples;
            $n < $samplesCount - $mod + $this->total_samples;
            $n++
        ) {
            $samples[] = (int) ($amplitude * sin($n * $w));
        }

        $n = count($samples);

        $this->total_samples += $samplesCount;

        //$this->previous_sample - $samples;

        return $samples;
    }

    function saveTone($samples)
    {
        $srate = $this->sample_rate; //sample rate
        $bps = 16; //bits per sample
        $Bps = $bps / 8; //bytes per sample /// I EDITED

        $num_samples = count($samples);

        $file_size = 160038 * ($num_samples / 88000);
        $chunk_size = 160000 * ($num_samples / 88000);
        // http://www.topherlee.com/software/pcm-tut-wavformat.html

        $header = [
            //header
            0x46464952, //RIFF
            $file_size, //File size 160038
            0x45564157, //WAVE
            0x20746d66, //"fmt " (chunk)
            16, //chunk size
            1, //compression
            1, //nchannels
            $srate, //sample rate
            $Bps * $srate, //bytes/second
            $Bps, //block align
            $bps, //bits/sample
            0x61746164, //"data"
            $chunk_size, //chunk size
        ];

        $str = call_user_func_array(
            "pack",
            array_merge(["VVVVVvvVVvvVVv*"], $header, $samples)
        );

        $file_name = "song3.wav"; // Wicked
        ($myfile = fopen($file_name, "wb")) or die("Unable to open file!");
        fwrite($myfile, $str);
        fclose($myfile);
    }

    function show_status($done, $total, $size = 30)
    {
        // https://stackoverflow.com/questions/2124195/command-line-progress-bar-in-php
        // Thanks.

        static $start_time;

        // if we go over our bound, just ignore it
        if ($done > $total) {
            return;
        }

        if (empty($start_time)) {
            $start_time = time();
        }
        $now = time();

        $perc = (float) ($done / $total);

        $bar = floor($perc * $size);

        $status_bar = "\r[";
        $status_bar .= str_repeat("=", $bar);
        if ($bar < $size) {
            $status_bar .= ">";
            $status_bar .= str_repeat(" ", $size - $bar);
        } else {
            $status_bar .= "=";
        }

        $disp = number_format($perc * 100, 0);

        $status_bar .= "] $disp%  $done/$total";

        $rate = ($now - $start_time) / $done;
        $left = $total - $done;
        $eta = round($rate * $left, 2);

        $elapsed = $now - $start_time;

        $status_bar .=
            " remaining: " .
            number_format($eta) .
            " sec.  elapsed: " .
            number_format($elapsed) .
            " sec.";

        $this->thing->console("$status_bar  ");

        flush();

        // when done, send a newline
        if ($done == $total) {
            $this->thing->console("\n");
        }
    }

    function makeBin($bin_name)
    {
        if (!isset($this->{$bin_name . "_bin"})) {
            $this->{$bin_name . "_bin"} = [];
        }
    }
}
