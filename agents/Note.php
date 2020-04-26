<?php
namespace Nrwtaylor\Stackr;

//require 'vendor/autoload.php';
//use Nrwtaylor\Stackr;

//$argument_array = $argv;
//$thing = new \Nrwtaylor\Stackr\Thing(null); // State contains uuid
//$agent = new \Nrwtaylor\Stackr\Meta($thing,implode(" ", $argument_array));

//var_dump($argument_array);
//var_dump(isset($argv));

//var_dump($agent->to);
//var_dump($agent->from);
//var_dump($agent->subject);

// Need to figure out recursive array iterator in Chocie.php

//$thing->Create($agent->to, $agent->from, $agent->subject);

//$smsgraph = new Note($thing);


class Note
{
	function __construct(\Nrwtaylor\Stackr\Thing $thing, $agent_command = null) {
//    function __construct()
//    {
        //$this->thing = new Agent(null); // State contains uuid

        $this->thing = $thing;

        $verbosity = 1;

        // $this->thing = new \Nrwtaylor\Stackr\Thing(null); // State contains uuid
        //https://github.com/kite1988/nus-sms-corpus/blob/master/README.md
        //https://stackoverflow.com/questions/28053226/generate-wav-tone-in-php
        //https://stackoverflow.com/questions/28053226/generate-wav-tone-in-php

        $this->sample_rate = 44100;

//        $this->tone_agent = new \Nrwtaylor\Stackr\Tone($thing);

//exit();

        $this->initTone();

        $notes = array(array("bpm"=>120),
                array("note"=>"G","length"=>1),
                array("note"=>"F#","length"=>1),
                array("note"=> "E", "length"=>1),
                array("note"=>"E", "length"=>1),

                array("barline"),
                array("note"=>"F#", "length"=>1),
                array("note"=>null, "length"=>1),
                array("note"=>null, "length"=>2),

                array("barline"),
                array("note"=>null,"length"=>1),
                array("note"=>null,"length"=>0.5),
                array("note"=>"A","length"=>0.5),
                array("note"=>"G","length"=>0.5),
                array("note"=> "F#", "length"=>0.5),
                array("note"=>"E", "length"=>0.5),
                array("note"=>"E_", "length"=>0.5),

                array("barline"),
                array("note"=>"E","length"=>0.5),
                array("note"=>"F#","length"=>1),
                array("note"=>null,"length"=>0.5),
                array("note"=> "D", "length"=>1),
                array("note"=>"E", "length"=>0.5),
                array("note"=>"A_", "length"=>0.5),

                array("barline"),
                array("note"=>"A","length"=>1),
                array("note"=>null,"length"=>1),
                array("note"=>null,"length"=>1),
                array("note"=>null,"length"=>0.5),
                array("note"=>"A","length"=>0.5),

                array("barline"),
                array("note"=>"E","length"=>1),
                array("note"=>"F#","length"=>0.5),
                array("note"=>"G","length"=>0.5),
                array("note"=> "G", "length"=>1),
                array("note"=>"E", "length"=>0.5),
                array("note"=>"C#_", "length"=>0.5),

                array("barline"),
                array("note"=>"C#","length"=>0.5),
                array("note"=>"D","length"=>1.5),
                array("note"=>"E","length"=>1),
                array("note"=> "A", "length"=>0.5),
                array("note"=>"A", "length"=>0.5),

                array("barline"),
                array("note"=>"A","length"=>0.5),
                array("note"=>"F#","length"=>1),
                array("note"=>null,"length"=>0.5),
                array("note"=>null, "length"=>2),

                array("barline"),
                array("note"=>null,"length"=>2),
                array("note"=>"G","length"=>0.5),
                array("note"=>"F#","length"=>0.5),
                array("note"=>"E", "length"=>0.5),
                array("note"=>"E", "length"=>0.5),


                array("barline"),
                array("note"=>"F#","length"=>1),
                array("note"=>null,"length"=>1),
                array("note"=>null, "length"=>2),

                array("barline"),
                array("note"=>null,"length"=>1),
                array("note"=>null,"length"=>0.5),
                array("note"=>"A","length"=>0.5),
                array("note"=>"G","length"=>0.5),
                array("note"=>"F#","length"=>0.5),
                array("note"=>"E", "length"=>0.5),
                array("note"=>"E", "length"=>0.5),

                array("barline"),
                array("note"=>"E","length"=>1),
                array("note"=>"F#","length"=>0.5),
                array("note"=>"D","length"=>0.5),
                array("note"=>"D","length"=>1),
                array("note"=>"E","length"=>0.5),
                array("note"=>"A", "length"=>0.5),

                array("barline"),
                array("note"=>"A","length"=>1),
                array("note"=>null,"length"=>3),

                array("barline"),
                array("note"=>"E","length"=>1),
                array("note"=>"F#","length"=>0.5),
                array("note"=>"G","length"=>0.5),
                array("note"=>"G","length"=>1),
                array("note"=>"E", "length"=>0.5),
                array("note"=>"C#","length"=>0.5),

                array("barline"),
                array("note"=>"C#","length"=>1),
                array("note"=>"D","length"=>0.5),
                array("note"=>"E","length"=>0.5),
                array("note"=>"E","length"=>0.5),
                array("note"=>"A","length"=>0.5),
                array("note"=>"D","length"=>0.5),
                array("note"=>"E", "length"=>0.5),

                array("barline"),
                array("note"=>"F","length"=>0.5),
                array("note"=>"E","length"=>0.5),
                array("note"=>"D","length"=>0.5),
                array("note"=>"C","length"=>0.5),
                array("note"=>null,"length"=>1),
                array("note"=>"A","length"=>0.5),
                array("note"=>"A#", "length"=>0.5),

                array("barline"),
                array("note"=>"C","length"=>1),
                array("note"=>"F","length"=>1),
                array("note"=>"E","length"=>0.5),
                array("note"=>"D","length"=>0.5),
                array("note"=>"D","length"=>0.5),
                array("note"=>"C","length"=>0.5),

                array("barline"),
                array("note"=>"D","length"=>0.5),
                array("note"=>"C","length"=>0.5),
                array("note"=>"C","length"=>1),
                array("note"=>"C","length"=>1),
                array("note"=>"A","length"=>0.5),
                array("note"=>"Bb","length"=>0.5),

                array("barline"),
                array("note"=>"C","length"=>1),
                array("note"=>"F","length"=>1),
                array("note"=>"G","length"=>0.5),
                array("note"=>"F","length"=>0.5),
                array("note"=>"E","length"=>0.5),
                array("note"=>"D","length"=>0.5),

                array("barline"),
                array("note"=>"D","length"=>0.5),
                array("note"=>"E","length"=>0.5),
                array("note"=>"F","length"=>1),
                array("note"=>"F","length"=>1),
                array("note"=>"G","length"=>0.5),
                array("note"=>"A","length"=>0.5),

                array("barline"),
                array("note"=>"A#","length"=>0.5),
                array("note"=>"A#","length"=>0.5),
                array("note"=>"A","length"=>1),
                array("note"=>"G","length"=>1),
                array("note"=>"F","length"=>0.5),
                array("note"=>"G","length"=>0.5),

                array("barline"),
                array("note"=>"A","length"=>0.5),
                array("note"=>"A","length"=>0.5),
                array("note"=>"G","length"=>1),
                array("note"=>"F","length"=>1),
                array("note"=>"D","length"=>0.5),
                array("note"=>"C","length"=>0.5),

                array("barline"),
                array("note"=>"D","length"=>0.5),
                array("note"=>"F","length"=>0.5),
                array("note"=>"F","length"=>0.5),
                array("note"=>"E","length"=>0.5),
                array("note"=>"E","length"=>0.5),
                array("note"=>"E","length"=>0.5),
                array("note"=>"F#","length"=>0.5),
                array("note"=>"F","length"=>0.5),




                array("barline")

               );

/*
$notes = array(
                array("barline"),
                array("note"=>"C", "length"=>4),
                array("barline"),
                array("note"=>null, "length"=>4), 
                array("barline"),
                array("note"=>"C", "length"=>4) 

                );
/*
$notes = array(
                array("note"=>null, "length"=>4)

                );
*/

/*



$notes = array(array("note"=>"C","length"=>1),
                array("note"=>"E","length"=>1),
                array("note"=> "F", "length"=>1),
                array("note"=>"G", "length"=>1),
                array("note"=>"C","length"=>1),
                array("note"=>"E","length"=>1),
                array("note"=> "F", "length"=>1),
                array("note"=>"G", "length"=>1),

                array("note"=>"C","length"=>1),
                array("note"=>"E","length"=>1),
                array("note"=> "D", "length"=>1),
                array("note"=>"D", "length"=>1) 


                );
*/
/*
$notes = array(array("note"=>"C_","length"=>1),
                array("note"=>null,"length"=>1),
                array("note"=>"C_","length"=>1),
                array("note"=>"C_","length"=>1)
                );
*/


/*
$notes = array(array("note"=>"C","length"=>1),
            array("note"=>"D","length"=>1),
            array("note"=>"E_","length"=>1),
            array("note"=>"E","length"=>1)
                );
*/


/*
    $prime = array("D","C#","A","A#","F","D#","E","C","G#","G","F#","B");


    // Youmans' Into the Frozen Forest
    $prime = array("C#","A","D#","F#","C","D","B","A#","F","G#","E","G");

    // Carolingian realm tutorial
    $prime = array("C","A","G","D#","E","F","D","B","A#","G#","C#","F#");
     $matrix = $this->matrixTune($prime);
     $rows = $this->generate2Tune($matrix,4);

$matches = [];
foreach ($rows as $x) {
    foreach ($rows as $y) {
        if ($x!=$y) {
           $matches[] = array("levenshtein"=>levenshtein($x,$y),
                        "row"=>$x,
                        "column"=>$y);
        }
    }
}

var_dump($matches);

$levenshtein = array();
foreach ($matches as $key => $row)
{
    $levenshtein[$key] = $row['levenshtein'];
}
array_multisort($levenshtein, SORT_DESC, $matches);

var_dump($matches);


$notes = [];
foreach(range(1,4) as $i) {
$a =  $matches[count($matches) - $i]['row'];
$b= $matches[count($matches) - $i]['column'];

    foreach(explode(" ", $a) as $note) {

        $notes[] = array("note"=>$note,"length"=>1);
    } 

    foreach(explode(" ", $b) as $note) {

        $notes[] = array("note"=>$note,"length"=>1); 
    } 


}

//count($matches);

exit();
//exit();
*/

$samples = array();
$count = 0;
$length = 0;

foreach ($notes as $temp=>$note) {
    //echo "received string: " . implode(" ", $note) . "\n";

    $samples_new = array();
    switch (true) {
        case (!isset($note)):
            echo "no command provided\n";
            break;

        case ( isset($note[0]) and ($note[0] == "barline")):
            $count = 0;
            echo "barline\n";
            break;

        case (isset($note["bpm"])):
            $this->bpm = $note["bpm"];
            echo "bpm " . $this->bpm . "\n";
            break;

        case (isset($note["note"])):
            if (strpos($note["note"], '_') !== false) {
               // slur
                $tone_width = 1.0;
            } else {
                $tone_width = 0.99;
            }
            

        default:
//            var_dump($note["note"]);
            if ($note["note"] == null) {
                //echo "null noted detected\n";
               $frequency = 0;
                $note_text = "rest";
            } else {
                //echo $note["note"] . " detected\n";
                
                $frequency = $this->frequencyTone(rtrim($note["note"],"_"));
                $note_text = $note["note"];
            }

            $count += $note["length"];
            list($samples_new, $l) = $this->sampleTone($frequency,$note["length"], $tone_width);

            $length += $l;
            if ($verbosity >= 8) {
            echo "frequency " . $frequency  . " note " . $note_text . " count " . $count . " length " . $length . "s " . "\n";
            }
            break;

     }
        //https://stackoverflow.com/questions/4268871/php-append-one-array-to-another-not-array-push-or
        //16

        if (count($samples_new) != 0) {
            array_push($samples, ...$samples_new);
        }

        // This was a triumph.

    }
    echo "Sample count: " . count($samples);

//    $samples = $this->highpass($samples);

    $this->saveTone($samples);

    $this->testTone();

    return;






exit();
}


    function getLexicon()
    {
        $this->lexicon = [];
        $file_name = "/home/nick/txt/ngrams.txt";

        $csvFile = file($file_name);

        foreach ($csvFile as $csv_line) {

            $line = str_getcsv($csv_line);
            $this->lexicon[] = rtrim($line[0]);


        }

        return $this->lexicon;

        $fd = fopen($file_name, 'rb');
        while( ($line = fgets($fd)) !== false ) {


            $this->lexicon[] = rtrim($line);
        }
        fclose($fd);
        return $this->lexicon;
    }


    function setEnvelope() {

            $this->attack_time = 0.050; //s
            $this->attack_level = 1.0;
            $this->decay_time = 0.050;
            $this->release_time = 0.40;
            //$this->sustain_time = $note_duration * 1000 - ($this->attack_time + $this->decay_time + $this->release_time);
            $this->sustain_level = 0.2;

            $envelope =array("attack"=>array("duration"=>$this->attack_time,"amplitude_end"=>$this->attack_level), 
                            "decay"=>array("duration"=>$this->decay_time, "amplitude_end"=>$this->sustain_level), 
                            "sustain"=>array("duration"=>null, "amplitude_end"=>$this->sustain_level), 
                            "release"=>array("duration"=>$this->release_time, "amplitude_end"=>0.0));

            $this->envelope = $envelope;

        return $envelope;
    }

    function getEnvelope($time_point, $note_duration )
    {


            $attack_time = $this->attack_time; //0.0; //ms
            $attack_level = $this->attack_level; //1.0;
            $decay_time = $this->decay_time; //250.0;
            $release_time = $this->release_time; //250.0;
            $sustain_time = $note_duration - ($attack_time + $decay_time + $release_time);
            if ($sustain_time < 0) {$sustain_time = 0;}
            $sustain_level = $this->sustain_level;

            $envelope =array("attack"=>array("duration"=>$attack_time,"amplitude_end"=>$attack_level), 
                            "decay"=>array("duration"=>$decay_time, "amplitude_end"=>$sustain_level), 
                            "sustain"=>array("duration"=>$sustain_time, "amplitude_end"=>$sustain_level), 
                            "release"=>array("duration"=>$release_time, "amplitude_end"=>0.0));

            $envelope = $this->envelope;
            $envelope["sustain"]["duration"] = $sustain_time;



            $sustain_time = $note_duration - ($attack_time + $decay_time + $release_time);

            // Build amplitude envelope
            $amplitude = array();
            $amplitude_start = 0.0;
            foreach($envelope as $key=>$value) {

                $amplitude_end = $value['amplitude_end'];
                $amplitude[$key] = array($amplitude_start, $amplitude_end);
                $amplitude_start = $amplitude_end;

            }

        $elapsed_note_time = 0.0;
        foreach($envelope as $key=>$value) {
            $prior_note_time = $elapsed_note_time;
            $elapsed_note_time += $value['duration'];

            if (($time_point < $elapsed_note_time) and ($time_point > $prior_note_time)) {
                // We are is $key part of the envelope.
                //echo "amplitude key\n";
                //var_dump($amplitude[$key]);

                $t = ($time_point - $prior_note_time) / ($envelope[$key]['duration'] );
                //var_dump($t);
                $a = $amplitude[$key][0] + ($amplitude[$key][1] - $amplitude[$key][0]) * $t;
                //echo $t . " " . $a . "\n";
                return $a;
            }

        }
        return true;

    }

    function makeNote($transition_width, $note_time, $frequency,$time_point, $s, $samples) 
    {

        

        // Length of note to be played.
        // $time_point - maximum note length

        $steps = 100;
        $num_samples = $this->sample_rate * $note_time;
/*
if ($frequency !=0) {
    $samples_in_cycle = $this->sample_rate / $frequency;  // 60 Hz - 60 cycles per second.  sample 44100 sample per s
    $num_cycles_in_sample = $s / $frequency; // number of cycles in the sample.

    $cycle_fraction = $num_cycles_in_sample - floor($num_cycles_in_sample);
    $cycle_integer = $num_cycles_in_sample - $cycle_fraction;
var_dump($s);
echo $samples_in_cycle . " " .$num_cycles_in_sample . " " . $cycle_fraction . " " . $cycle_integer . " " . "\n";
}
*/
//        $s = $cycle_integer * $frequency;
//    } else {
//        $s = ($cycle_integer + 1) * $frequency;


        foreach (range(0,$steps - 1) as $step) {

            $s = $num_samples * $transition_width / $steps;
            $time_point += $s / $this->sample_rate;

            if ($time_point > $note_time) {break;}

            $amplitude = $this->getEnvelope($time_point, $note_time);
/*
if ($frequency == 0) {
  //  echo "zero ";
    //$samples_per_cycle = 1;
    //$num_cycles = $samplesCount / 440;
} else {
    $samples_in_cycle = $this->sample_rate / $frequency;  // 60 Hz - 60 cycles per second.  sample 44100 sample per s
    $num_cycles_in_sample = $s / $frequency; // number of cycles in the sample.

    $cycle_fraction = $num_cycles_in_sample - floor($num_cycles_in_sample);
    $cycle_integer = $num_cycles_in_sample - $cycle_fraction;

    echo $num_cycles_in_sample . " " . $cycle_fraction . " " . $cycle_integer . " " . "\n";

    if ($cycle_fraction < 0.5) {
        $s = $cycle_integer * $frequency;
    } else {
        $s = ($cycle_integer + 1) * $frequency;
    }

}
echo $s . " " . $frequency . "\n";
*/

            $samples_add = $this->toneTone($frequency, $s, $amplitude);

            if (count($samples_add) != 0) {
                array_push($samples, ...$samples_add);
            }
        }

        $this->time_pointer = $time_point;
        $this->sample_pointer = $s;
        $this->phase = 0;

        return array($time_point, $s, $samples);


    }


    function sampleTone($frequency, $num_beats, $bpm = 120, $tone_width = 0.2)
    {
        if (!isset($this->phase)) {$this->phase = 0;}
        $this->setEnvelope(null,null);

        $attack_width = $this->envelope['attack']['duration']; //s
        $decay_width = $this->envelope['decay']['duration']; //s
        $release_width = $this->envelope['attack']['duration']; //s


        $samples = array();
        $time_point = 0;
        //$tone_width = 0.5;
        $marked_note = 1/4;
        $marked_beat = 1;

        $bpm = 120;
        // 4/4 means that there are 4 beats per measure and a quarter note gets one count. 
        // basically, top=how many beats/measure. bottom=what kind of note gets one count. 

        $seconds_per_beat = 60.0 / $bpm;
        $sample_rate = 44100;
        $marked_note_samples = 44100 * $seconds_per_beat * 1; // 1 beat

        // Tone

        $num_samples = $sample_rate * $seconds_per_beat * $num_beats;
        $note_time = $num_beats * $seconds_per_beat;


        $sustain_width = $note_time - ($attack_width + $decay_width + $release_width);
        if ($sustain_width < 0) {$sustain_width = 0;}

        $mode = "attack";
        
        switch ($mode) {
        case "attack":
            $transition_width = $attack_width; // 0.25; // Percentage of silent
            $s = 0;
            list($time_point, $s, $samples) = $this->makeNote($attack_width,$note_time,$frequency,$time_point,$s,$samples);
 
        case "decay":
            // Decay
            list($time_point, $s, $samples) = $this->makeNote($decay_width,$note_time,$frequency,$time_point,$s,$samples);

        case "sustain":
            // Sustain
            $sustain_width = $note_time - ($attack_width + $decay_width);
            $sustain_width = 2; // Cut out on note width
            //$transition_width = $sustain_width; //0.25; // Percentage of silent

            list($time_point, $s, $samples) = $this->makeNote($sustain_width,$note_time,$frequency,$time_point,$s,$samples);

        case "release":
            // Release
            //$transition_width = $release_width; // 0.25; // Percentage of silent

            list($time_point, $s, $samples) = $this->makeNote($release_width,$note_time,$frequency,$time_point,$s,$samples);

//echo "actual note time " . ($attack_width + $decay_width + $sustain_width + $release_width) . "s \n";

            $pad_width = $note_time- ($attack_width + $decay_width + $sustain_width + $release_width);

            list($time_point, $s, $samples) = $this->makeNote($pad_width,$note_time,null,$time_point,$s,$samples);

    }



        $length =  $seconds_per_beat * $num_beats;

        return array($samples, $length);
    }

    function generateTune($matrix, $num_rows = 1)
    {
        $notes = [];
        foreach(range(0,$num_rows) as $temp) {

            // Pick an integer

            $n = rand(0,11);
            $retrograde = rand(0,1);
            $inversion = rand(0,1);

            $line = [];
            foreach(range(0,11) as $value) {

                if ($inversion == 0) {
                    $line[] = $matrix[$n][$value];
                } else {
                    $line[] = $matrix[$value][$n];
                }
            }

            if ($retrograde == 0) {array_reverse($line);}
            //$notes = [];
            foreach($line as $value) {
//                echo "-".$value."-";
                echo str_pad($this->reverse_clock[$value],3, " ", STR_PAD_RIGHT); 
                $notes[] = array("note"=>$this->reverse_clock[$value],"length"=>1);
            }
            echo "\n";
        }
        return $notes;


    }

    function generate2Tune($matrix, $num_rows = 1)
    {
        $rows = [];
        $notes = [];
        //foreach(range(0,$num_rows) as $temp) {

            // Pick an integer
        foreach(range(0,11) as $value) {

            $prime = [];
            $inverse = [];
            foreach(range(0,11) as $n) {
                    $prime[] = $matrix[$n][$value];
                    $inverse[] = $matrix[$value][$n];
            }
            $prime_retrograde = array_reverse($prime);
            $inverse_retrograde = array_reverse($inverse);

             $rows[] = $prime;
             $rows[] = $inverse;
             $rows[] = $prime_retrograde;
             $rows[] = $inverse_retrograde;
        }

        $note_strings = [];
        foreach($rows as $row) {
            $note_string = "";
            foreach($row as $value) {
                echo str_pad($this->reverse_clock[$value],3, " ", STR_PAD_RIGHT); 
//                $notes[] = array("note"=>$this->reverse_clock[$value],"length"=>1);
                $note_string .= $this->reverse_clock[$value] . " ";
            }
            $note_strings[] = rtrim($note_string);
        }

            echo "\n";
        
//var_dump($note_strings);
//exit();
        return $note_strings;


    }



    function matrixTune($prime = null) 
    {
        //$prime = null;
        // https://unitus.org/FULL/12tone.pdf
        if ($prime == null) {
        $i = 0;
        // Create a random line of 12 semi-tones (no duplicates)
        foreach($this->semitone_array as $note=>$temp)
        {
            //echo "-" . $note . "\n";
            $note_array[] = array("note"=>$note,"index"=>$i);
            $i += 1;
        }
        shuffle($note_array);
        $prime = [];
        foreach($note_array as $value=>$note) {$prime[] = $note["note"];}

        }

        $chromatic = array("A","A#","B","C","C#","D","D#","E","F","F#","G","G#");

        $clock = array(); // index to note
        $reverse_clock = array(); //note to index

        foreach($chromatic as $index=>$note) {
            //echo $note ." ".$prime[0] . "\n";
            if ($note == $prime[0]) {break;}
            $c = next($chromatic);
        }

        foreach($prime as $index=>$note) {
            $clock[] = $c;
            $c = next($chromatic);
            if ($c == false) {$c = reset($chromatic);}
        }

        foreach($clock as $index=>$note) {
            $reverse_clock[$note] = $index;
        }

        foreach($prime as $index=>$note) {
            echo $reverse_clock[$note] . " ";
        }
        echo "\n";

        // Initialize matrix
        $matrix = array();
        foreach (range(0,11) as $i) {
            foreach (range(0,11) as $j) {
                $matrix[$i][$j] = null;
            }
        }

        // First row
        $j = 0;
        foreach (range(0,11) as $i) {
            echo $reverse_clock[$prime[$i]];
            $matrix[$i][$j] = $reverse_clock[$prime[$i]];
        }

        // First column
        $i = 0;
        foreach (range(1,11) as $j)
            {$matrix[$i][$j] = ( 12 - $reverse_clock[$prime[$j]]) % 12;
        }

        foreach (range(1,11) as $i) {
            foreach (range(1,11) as $j) {
                $x = $matrix[0][$j];
                $y = $matrix[$i][0];

                $matrix[$i][$j] = ($x + $y) % 12;
            }
        }

        $this->matrixPrint($matrix);

        $this->matrix = $matrix;
        $this->clock = $clock;
        $this->reverse_clock = $clock;

        return $matrix;
    }

    function matrixPrint($matrix)
    {
        echo "\n";
        foreach(range(0,11) as $i) {
            foreach(range(0,11) as $j) {     
                    $text = str_pad($matrix[$j][$i], 4, " ", STR_PAD_LEFT);
                    echo $text;
            }
            echo "\n";
        }
        echo "\n";
    }


    function frequencyTone($note, $octave = 4)
    {
        if ($note == null) {return 0;}
        $base_octave = 4;
        $n = $this->semitone_array[$note];
        $b = $this->a_frequency * pow(2, ($n / 1200));

        $b = $b * pow(2, $octave - $base_octave);

        return $b;
    }


    function testTone()
    {

        if (!$this->frequencyTone("A") == 440) {echo "ERROR";} else {echo "PASS";}

        if (!$this->frequencyTone("A",3) == 220) {echo "ERROR";} else {echo "PASS";}
        if (!$this->frequencyTone("A",5) == 880) {echo "ERROR";} else {echo "PASS";}

        foreach($this->semitone_array as $note=>$cent) {

            echo $note . " > " . $this->frequencyTone($note) . "\n";

        }

//        if (!$this->frequencyTone("A") == 440) {echo "ERROR";} else {echo "PASS";}


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

        $this->semitone_array = array(
                            "A"=>$a,
                            "Bb"=>$b_flat,
                            "B"=>$b,
                            "C"=>$c,
                            "C#"=>$c_sharp,
                            "D"=>$d,
                            "Eb"=>$e_flat,
                            "E"=>$e,
                            "F"=>$f,
                            "F#"=>$f_sharp,
                            "G"=>$g,
                            "G#"=>$g_sharp,
                            //"A"=>$a,
                            //"Bb"=>$b_flat,
                            //"B"=>$b
                            );

        $this->semitone_array = array(
                            "A"=>$a,
                            "A#"=>$b_flat,
                            "B"=>$b,
                            "C"=>$c,
                            "C#"=>$c_sharp,
                            "D"=>$d,
                            "D#"=>$e_flat,
                            "E"=>$e,
                            "F"=>$f,
                            "F#"=>$f_sharp,
                            "G"=>$g,
                            "G#"=>$g_sharp,
                            //"A"=>$a,
                            //"Bb"=>$b_flat,
                            //"B"=>$b
                            );


    }

    function toneTone($freqOfTone = null, $samplesCount = 44100, $amplitude_level = 1.0)
    {

        if (!isset($this->phase)) {$this->phase = 0;}
        if (!isset($this->total_samples)) {$this->total_samples = 0;}

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

            $amplitude = 0; $w=$this->phase;

        } else {
            $amplitude = 0.25 * 32768 * $amplitude_level;
            $w = 2 * pi() * $freqOfTone / $sample_rate;
//            $this->phase = $w;
        }
//echo "w" . $w . "\n";
//echo "div " .$freqOfTone/$sample_rate . "\n";
//echo "mod " . $freqOfTone % $sample_rate . "\n";
//echo "samplescount " . $samplesCount . "\n"; 

// How many samples per cycle?
//$samples_per_cycle = 0;
//if ($freqOfTone == 0) {$freqOfTone = 1;}
//$num_cycles = $samplesCount;
/*
echo $samplesCount . " " ;


if ($freqOfTone == 0) {
    echo "zero ";
    //$samples_per_cycle = 1;
    //$num_cycles = $samplesCount / 440;
} else {
    $samples_per_cycle = $sample_rate / $freqOfTone;
//echo "x" . $samples_per_cycle . "x";
    $num_cycles = $samplesCount / $freqOfTone;

    $cycle_fraction = $num_cycles - floor($num_cycles);
    $cycle_integer = floor($num_cycles);
echo "i" . $cycle_integer . "i";

    if ($cycle_fraction < 0.5) {
        $samplesCount = $cycle_integer * $freqOfTone;
    } else {
        $samplesCount = ($cycle_integer + 1) * $freqOfTone;
    }
}
echo $samplesCount . " " . $freqOfTone . "\n";
*/

        $samples = array();
//        for ($n = 0; $n < $samplesCount; $n++) {
        // $this->total_samples holds the current sample pointer.
        for ($n = ($this->total_samples - 1); $n < $samplesCount + $this->total_samples; $n++) {
            $samples[] = (int) (($amplitude *  sin($n * $w)));
        }

        $n = count($samples);

        $this->total_samples += $samplesCount;

        //$this->previous_sample - $samples;

    return $samples;

    }

    function highpass($samples)
    {
        $n = count($samples) ;
        foreach (range(0,$n-1) as $value) {
            $delta = abs($samples[$value] - $samples[$value - 1]);

            if ($delta > 500) {
                echo "highpass" . " index " . $value . " " . $samples[$value]. "\n";
            $sum = 0;
            $steps = 5;
            foreach (range(0,$steps - 1) as $i) {
                $sample_index = $value - $i;
                
                $sum += $samples[$sample_index];
            }
            $average = $sum / $steps;
            $samples[$value] =  $average;

            }

        }
        return $samples;

    }

    function saveTone($samples) 
    {
//echo "Sample size recd" . count($samples);
$srate = $this->sample_rate; //sample rate
$bps = 16; //bits per sample
$Bps = $bps/8; //bytes per sample /// I EDITED

$num_samples = count($samples);

$file_size = 160038 * ($num_samples / 88000);
$chunk_size = 160000 * ($num_samples / 88000);
// http://www.topherlee.com/software/pcm-tut-wavformat.html

$header =         array(//header
            0x46464952, //RIFF
            $file_size,      //File size 160038
            0x45564157, //WAVE
            0x20746d66, //"fmt " (chunk)
            16, //chunk size
            1, //compression
            1, //nchannels
            $srate, //sample rate
            $Bps*$srate, //bytes/second
            $Bps, //block align
            $bps, //bits/sample
            0x61746164, //"data"
            $chunk_size //chunk size
        );

$str = call_user_func_array("pack",
    array_merge(array("VVVVVvvVVvvVVv*"), $header, $samples));



        $file_name = "song3.wav"; // Portal
        $myfile = fopen($file_name, "wb") or die("Unable to open file!");
        fwrite($myfile, $str);
        fclose($myfile);
        echo "file written\n";
}


function show_status($done, $total, $size=30) {
    // https://stackoverflow.com/questions/2124195/command-line-progress-bar-in-php
    // Thanks.

    static $start_time;

    // if we go over our bound, just ignore it
    if($done > $total) return;

    if(empty($start_time)) $start_time=time();
    $now = time();

    $perc=(double)($done/$total);

    $bar=floor($perc*$size);

    $status_bar="\r[";
    $status_bar.=str_repeat("=", $bar);
    if($bar<$size){
        $status_bar.=">";
        $status_bar.=str_repeat(" ", $size-$bar);
    } else {
        $status_bar.="=";
    }

    $disp=number_format($perc*100, 0);

    $status_bar.="] $disp%  $done/$total";

    $rate = ($now-$start_time)/$done;
    $left = $total - $done;
    $eta = round($rate * $left, 2);

    $elapsed = $now - $start_time;

    $status_bar.= " remaining: ".number_format($eta)." sec.  elapsed: ".number_format($elapsed)." sec.";

    echo "$status_bar  ";

    flush();

    // when done, send a newline
    if($done == $total) {
        echo "\n";
    }

}

    function makeBin($bin_name)
    {
	    if (!isset($this->{$bin_name . "_bin"})) {
		    $this->{$bin_name . "_bin"} = array();
	    }
    }


    public function makePNG()
    {

        $this->x_origin = 100;
        $this->y_origin = 100;

        $this->cell_width = 40;
        $this->cell_height = 20;
        $this->height = 1000;
        $this->width = 1000;

        $this->font = '/home/nick/Downloads/fonts/Lato-Regular.ttf';
        //$this->font = '/home/nick/KeepCalm-Medium.ttf';
//        $this->font = 'fonts/CODE Bold.otf';


        $this->image = imagecreatetruecolor($this->width, $this->height);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $this->white);

        $textcolor = imagecolorallocate($this->image, 0, 0, 0);

        $this->drawBin();

        // Write the string at the top left
        $border = 30;
        $radius = 1.165 * (125 - 2 * $border) / 3;


//$this->font = '/home/nick/Downloads/fonts/Lato-Regular.ttf';



//$this->font = '/home/nick/KeepCalm-Medium.ttf';

$text = "test";
// Add some shadow to the text
//imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);

$size = 72;
$angle = 0;
$bbox = imagettfbbox ($size, $angle, $this->font, $text); 
$bbox["left"] = 0- min($bbox[0],$bbox[2],$bbox[4],$bbox[6]); 
$bbox["top"] = 0- min($bbox[1],$bbox[3],$bbox[5],$bbox[7]); 
$bbox["width"] = max($bbox[0],$bbox[2],$bbox[4],$bbox[6]) - min($bbox[0],$bbox[2],$bbox[4],$bbox[6]); 
$bbox["height"] = max($bbox[1],$bbox[3],$bbox[5],$bbox[7]) - min($bbox[1],$bbox[3],$bbox[5],$bbox[7]); 
extract ($bbox, EXTR_PREFIX_ALL, 'bb'); 
//check width of the image 
$this->width = imagesx($this->image); 
$this->height = imagesy($this->image);
$pad = 0;
//imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $grey, $font, $number);


//     imagestring($this->image, 2, 100, 0, $this->thing->nuuid, $textcolor);



        //imagepng($im);

        ob_start();
        imagepng($this->image, "file.png");
        $imagedata = ob_get_contents();
        ob_end_clean();

        $this->thing_report['png'] = $imagedata;

        //echo '<img src="data:image/png;base64,'.base64_encode($imagedata).'"/>';
        $response = '<img src="data:image/png;base64,'.base64_encode($imagedata).'"alt="tallygraph"/>';
        $this->image_embedded = $response;
//        $this->thing_report['png'] = $image;

        imagedestroy($this->image);

        return $response;
    }


    function drawBin($bin_name = null)
    {
        if ($bin_name == null) {$bin_name = $this->bin_name;}

        if (!isset($this->image)) {$this->makePNG();}

        $bin = $this->{$bin_name . "_bin"};

        $num_dim = $this->countDimension($bin);
        ksort($bin);
        $n = 0;

        if (!isset($this->bin_meta)) {
            $this->bin_meta = $this->binLimits($bin);
        }

        $row_min_display = 2010;
        $row_max_display = 2018;

        $row_min = $this->bin_meta["row"]["min"];
        $column_min = $this->bin_meta["column"]["min"];

        if ($column_min < 2005) {$column_min = 2005;}

        $row_max = $this->bin_meta["row"]["max"];
        $column_max = $this->bin_meta["column"]["max"];


        // Column title
        $row_title_length = 4;
        $col = 0;
//        echo str_pad("", $row_title_length," ", STR_PAD_LEFT);

//        $this->drawText($this->bin_array[1], $this->bin_meta["column"]["min"]-1, -2, $this->black);
        $this->drawText($this->bin_array[1], $column_min-1, -2, $this->black);



//        foreach(range($this->bin_meta["column"]["min"], $this->bin_meta["column"]["max"]) as $a) {
        foreach(range($column_min, $column_max) as $a) {

            if (isset($this->{$this->bin_array[1]. "_labels"})) {
                $text = $this->{$this->bin_array[1] . "_labels"}[($a-1)];
            } else {
                $text = str_pad($a, 4, " ", STR_PAD_LEFT);
            }

  //         echo $text;
            $col += 1;
            $this->drawText($text, $a - $column_min, -1, $this->black);
//var_dump($col);
        }

//        $this->drawText($this->bin_array[0], $this->bin_meta["column"]["min"]-2,
//                $this->bin_meta["row"]["min"]-1, $this->black, 90);

        $this->drawText($this->bin_array[0], $column_min-2,
                $row_min-1, $this->black, 90);

        $i = 0;
        $row = 0;

        foreach(range($row_min, $row_max) as $a) {

//        foreach(range($this->bin_meta["row"]["min"], $this->bin_meta["row"]["max"]) as $a) {
            $row += 1;
            // Row title


            if (isset($this->{$this->bin_array[0]. "_labels"})) {
                $text = $this->{$this->bin_array[0] . "_labels"}[($a-1)];
            } else {
                $text = str_pad($a, 4, " ", STR_PAD_LEFT);
            }


            $this->drawText($text, -1, $a - $row_min, $this->black);

            echo str_pad($a, 4, " ", STR_PAD_LEFT);
            //Draw rectangle


//            foreach(range($this->bin_meta["column"]["min"], $this->bin_meta["column"]["max"]) as $b) {
            foreach(range($column_min, $column_max) as $b) {

                if (!isset($bin[$a][$b])) {
                    $value = 0;
                } else {
                $value = $bin[$a][$b];
                }
                $bin_range = $this->bin_meta["bin"]["max"] - $this->bin_meta["bin"]["min"] + 1;

                if ($bin_range == 0) {
                    $normalized_value = null;
                } else {
                    $normalized_value = floor($value / $bin_range *100);
                }

                if (!isset($bin[$a][$b])) {
                    // Draw empty cell.
                    $text = str_pad("-",4," ",STR_PAD_LEFT);
                } else {
                    if ($this->normalize == false) {
                        $text = str_pad($value, 4, " ", STR_PAD_LEFT);
                    } else {
                        $text = str_pad($normalized_value, 4, " ", STR_PAD_LEFT);
                    }
//        $colour = imagecolorallocate($this->image, round((255-$normalized_value) * 255 / 100), 0, 0);
        $alpha = round((100-$normalized_value) * 127 / 100);
        $colour = imagecolorallocatealpha($this->image, 255, 0, 0,$alpha);

                    $this->drawCell($b - $column_min,$a - $row_min,$colour);
                    // Draw colored rectangle
                }
                echo $text;
                // . " " . round($value/$n * 100, 0) . "%";
                //echo "\n";
            }
        }
    }

    function drawCell($column_index, $row_index, $colour = null)
    {
        if (!isset($this->image)) {$this->makePNG();}

        $x = $this->cell_width * $column_index + $this->x_origin;
        $y = $this->cell_height * $row_index + $this->y_origin;

//       imageline($this->image,
//                    $x , $y,
//                    $x + $this->cell_width, $y + $this->cell_height,
//                    $this->black);
        imagefilledrectangle($this->image,
                    $x , $y,
                    $x + $this->cell_width, $y + $this->cell_height,
                    $colour);
    }

    function drawText($text, $column_index, $row_index, $colour = null, $angle = 0) {
        $size = 12;
        //$angle = 0;

        $x_tweak = 2;
        $y_tweak = -2;

        if ($colour == null) {$colour = $this->black;}

        $x = $this->cell_width * $column_index + $this->x_origin + $x_tweak;
        $y = $this->cell_height * $row_index + $this->cell_height + $this->y_origin + $y_tweak;

//$font = '/home/nick/KeepCalm-Medium.ttf';


        imagettftext($this->image, $size, $angle, $x, $y, $colour, $this->font, $text);


    }

    function incrementBin($bin_name, $bin)
    {
	if (is_array($bin)) {
	    $dimension = count($bin);
	} else {
	    $dimension = 1;
	}

        switch ($dimension) {
            //case 0:
		// Assume one dimensional bin
            //   break;
            case 1:
                // Assume one dimensional bin
                if (!isset($this->{$bin_name ."_bin"}[$bin])) {$this->{$bin_name ."_bin"}[$bin] = 0;}
                $this->{$bin_name . "_bin"}[$bin] += 1;
                break;
            case 2:
                if (!isset($this->{$bin_name ."_bin"}[$bin[0]][$bin[1]])) {$this->{$bin_name ."_bin"}[$bin[0]][$bin[1]] = 0;}
                $this->{$bin_name . "_bin"}[$bin[0]][$bin[1]] += 1;

                break;
            case 3:

		// Multidimensional bin
            if (!isset($this->{$bin_name ."_bin"}[$bin[0]][$bin[1]][$bin[2]])) {$this->{$bin_name ."_bin"}[$bin[0]][$bin[1]][$bin[2]] = 0;}
    	    $this->{$bin_name . "_bin"}[$bin[0]][$bin[1]][$bin[2]] += 1;

	}
    }

    function binLimits($bin) {
	$bin_total = 0;
        $bin_count = 0;
        foreach($bin as $row_key=>$bin_row) {

                if (!isset($row_min)) {$row_min = $row_key;}
                if (!isset($row_max)) {$row_max = $row_key;}

                if ($row_key<$row_min) {$row_min = $row_key;}
                if ($row_key>$row_max) {$row_max = $row_key;}


            foreach($bin_row as $column_key=>$bin_column) {

                if (!isset($column_min)) {$column_min = $column_key;}
                if (!isset($column_max)) {$column_max = $column_key;}

                if ($column_key<$column_min) {$column_min = $column_key;}
                if ($column_key>$column_max) {$column_max = $column_key;}

                $bin_value = $bin[$row_key][$column_key];
                $bin_total += $bin[$row_key][$column_key];
                $bin_count += 1;


                if (!isset($bin_min)) {$bin_min = $bin_value;}
                if (!isset($bin_max)) {$bin_max = $bin_value;}

                if ($bin_value<$bin_min) {$bin_min = $bin_value;}
                if ($bin_value>$bin_max) {$bin_max = $bin_value;}

            }
        }

	return array("row"=>array("min"=>$row_min,"max"=>$row_max),
			"column"=>array("min"=>$column_min,"max"=>$column_max),
			"bin"=>array("min"=>$bin_min,"max"=>$bin_max,"total"=>$bin_total, "count"=>$bin_count));

    }

    function binReport($bin_name = null) 
    {


    	$bin = $this->{$bin_name . "_bin"};

        $num_dim = $this->countDimension($bin);
        ksort($bin);
        $n = 0;

	    $bin_meta = $this->binLimits($bin);

        $row_title_length = 4;
	echo "Bin name: " . $bin_name;
	echo "\n";

        echo "Selector(s): ". implode("-",$this->selectors);
        echo "\n";
        echo "Intensity (0-100)";
        // Print column headings
        echo "\n";
        echo str_pad("", $row_title_length," ", STR_PAD_LEFT);
        foreach(range($bin_meta["column"]["min"], $bin_meta["column"]["max"]) as $a) {
           $text = str_pad($a, 4, " ", STR_PAD_LEFT);
           echo $text;
	}

        $i = 0;
        foreach(range($bin_meta["row"]["min"], $bin_meta["row"]["max"]) as $a) {
	    echo "\n";
            // Print row title
            //https://stackoverflow.com/questions/4742354/php-day-of-week-numeric-to-day-of-week-text
            //$dow_text = date('D', strtotime("Sunday +{$dow_numeric} days"));
            //$dow_text = date('D', strtotime("Sunday +{$a} days"));
            echo str_pad($a, 4, " ", STR_PAD_LEFT);



            foreach(range($bin_meta["column"]["min"], $bin_meta["column"]["max"]) as $b) {

		if (!isset($bin[$a][$b])) {
                    $value = 0;
                } else {
	            $value = $bin[$a][$b];
                }
                $bin_range = $bin_meta["bin"]["max"] - $bin_meta["bin"]["min"] + 1;
                if ($bin_range == 0) {
                    $normalized_value = null;
                } else {
		     $normalized_value = floor($value / $bin_range *100);
                }

                if (!isset($bin[$a][$b])) {$text = str_pad("-",4," ",STR_PAD_LEFT);} else {

			if ($this->normalize == false) {

                    $text = str_pad($value, 4, " ", STR_PAD_LEFT);
} else {
                    $text = str_pad($normalized_value, 4, " ", STR_PAD_LEFT);

}


                }

                //echo $key . " " . $value;

                echo $text;
                // . " " . round($value/$n * 100, 0) . "%";
                //echo "\n";
            }
        }

        echo "\n";
        echo "\n";
        echo "Bin count: " . $bin_meta["bin"]["count"] . " (=100%)";
        echo "\n";
        echo "Bin total: " . $bin_meta["bin"]["total"] ;
	echo "\n";

//echo "meep";
 //       echo $bin_meta["bin"]["max"];

}

function countDimension($Array, $count = 0) {
   if(is_array($Array)) {
      return $this->countDimension(current($Array), ++$count);
   } else {
      return $count;
   }
}
/*
function getSignificance($message) {
	$significance = 0;
	$words = explode(" ", $message);
	foreach ($words as $key=>$word) {
//var_dump($word);
	   if (strlen($word) > 4) {
              $significance += 1;

           }
	}
//var_dump($significance);
//exit();
//exit();
	return $significance;

}
*/
function getUniqueness($data,$message) 
{

$max_compare_length = 10;
//return;
// Not finsihd
 $n = 0;
 $s = 0;
 $max = 0;
//var_dump($data);
 foreach ($data as $key=>$data_gram) {
//var_dump($data_gram["message"]);
//var_dump($message);
  $n += 1;

  $l = levenshtein(substr($data_gram["message"],0,$max_compare_length), substr($message,0,$max_compare_length));
//var_dump($l);

  if ($l > $max) {$max = $l;}

  $s = $s + $l;
 }
//var_dump($message);
echo ".";
//var_dump($s/$n);
$x = round($s/$n);
//exit();
//var_dump($x);
 return $x;

}

    function presetFilter($filter_name)
    {
        switch($filter_name) {
            case "dooby":    

                $this->selectors = array("zonk","dube","dooby","duck","weed","smoke","bong");
                $this->selector_index = 1;
                $this->excludors = array();
                return;
            case "xox":
                $this->selectors = array("xox","xxx","xo","ox","hug","luve","hugs");
                $this->selector_index = 1;
                $this->excludors = array();
                return;


        }

    }

}
?>
