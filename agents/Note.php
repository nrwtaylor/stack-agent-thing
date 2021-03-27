<?php
namespace Nrwtaylor\StackAgentThing;

class Note extends Agent
{
    public function init()
    {
        // TODO Address noise.
        $this->dev = true;

        $verbosity = 1;

        $this->sample_rate = 44100;

        $this->initTone();

        $notes = [
            ["bpm" => 120],
            ["note" => "G", "length" => 1],
            ["note" => "F#", "length" => 1],
            ["note" => "E", "length" => 1],
            ["note" => "E", "length" => 1],

            ["barline"],
            ["note" => "F#", "length" => 1],
            ["note" => null, "length" => 1],
            ["note" => null, "length" => 2],

            ["barline"],
            ["note" => null, "length" => 1],
            ["note" => null, "length" => 0.5],
            ["note" => "A", "length" => 0.5],
            ["note" => "G", "length" => 0.5],
            ["note" => "F#", "length" => 0.5],
            ["note" => "E", "length" => 0.5],
            ["note" => "E_", "length" => 0.5],

            ["barline"],
            ["note" => "E", "length" => 0.5],
            ["note" => "F#", "length" => 1],
            ["note" => null, "length" => 0.5],
            ["note" => "D", "length" => 1],
            ["note" => "E", "length" => 0.5],
            ["note" => "A_", "length" => 0.5],

            ["barline"],
            ["note" => "A", "length" => 1],
            ["note" => null, "length" => 1],
            ["note" => null, "length" => 1],
            ["note" => null, "length" => 0.5],
            ["note" => "A", "length" => 0.5],

            ["barline"],
            ["note" => "E", "length" => 1],
            ["note" => "F#", "length" => 0.5],
            ["note" => "G", "length" => 0.5],
            ["note" => "G", "length" => 1],
            ["note" => "E", "length" => 0.5],
            ["note" => "C#_", "length" => 0.5],

            ["barline"],
            ["note" => "C#", "length" => 0.5],
            ["note" => "D", "length" => 1.5],
            ["note" => "E", "length" => 1],
            ["note" => "A", "length" => 0.5],
            ["note" => "A", "length" => 0.5],

            ["barline"],
            ["note" => "A", "length" => 0.5],
            ["note" => "F#", "length" => 1],
            ["note" => null, "length" => 0.5],
            ["note" => null, "length" => 2],

            ["barline"],
            ["note" => null, "length" => 2],
            ["note" => "G", "length" => 0.5],
            ["note" => "F#", "length" => 0.5],
            ["note" => "E", "length" => 0.5],
            ["note" => "E", "length" => 0.5],

            ["barline"],
            ["note" => "F#", "length" => 1],
            ["note" => null, "length" => 1],
            ["note" => null, "length" => 2],

            ["barline"],
            ["note" => null, "length" => 1],
            ["note" => null, "length" => 0.5],
            ["note" => "A", "length" => 0.5],
            ["note" => "G", "length" => 0.5],
            ["note" => "F#", "length" => 0.5],
            ["note" => "E", "length" => 0.5],
            ["note" => "E", "length" => 0.5],

            ["barline"],
            ["note" => "E", "length" => 1],
            ["note" => "F#", "length" => 0.5],
            ["note" => "D", "length" => 0.5],
            ["note" => "D", "length" => 1],
            ["note" => "E", "length" => 0.5],
            ["note" => "A", "length" => 0.5],

            ["barline"],
            ["note" => "A", "length" => 1],
            ["note" => null, "length" => 3],

            ["barline"],
            ["note" => "E", "length" => 1],
            ["note" => "F#", "length" => 0.5],
            ["note" => "G", "length" => 0.5],
            ["note" => "G", "length" => 1],
            ["note" => "E", "length" => 0.5],
            ["note" => "C#", "length" => 0.5],

            ["barline"],
            ["note" => "C#", "length" => 1],
            ["note" => "D", "length" => 0.5],
            ["note" => "E", "length" => 0.5],
            ["note" => "E", "length" => 0.5],
            ["note" => "A", "length" => 0.5],
            ["note" => "D", "length" => 0.5],
            ["note" => "E", "length" => 0.5],

            ["barline"],
            ["note" => "F", "length" => 0.5],
            ["note" => "E", "length" => 0.5],
            ["note" => "D", "length" => 0.5],
            ["note" => "C", "length" => 0.5],
            ["note" => null, "length" => 1],
            ["note" => "A", "length" => 0.5],
            ["note" => "A#", "length" => 0.5],

            ["barline"],
            ["note" => "C", "length" => 1],
            ["note" => "F", "length" => 1],
            ["note" => "E", "length" => 0.5],
            ["note" => "D", "length" => 0.5],
            ["note" => "D", "length" => 0.5],
            ["note" => "C", "length" => 0.5],

            ["barline"],
            ["note" => "D", "length" => 0.5],
            ["note" => "C", "length" => 0.5],
            ["note" => "C", "length" => 1],
            ["note" => "C", "length" => 1],
            ["note" => "A", "length" => 0.5],
            ["note" => "Bb", "length" => 0.5],

            ["barline"],
            ["note" => "C", "length" => 1],
            ["note" => "F", "length" => 1],
            ["note" => "G", "length" => 0.5],
            ["note" => "F", "length" => 0.5],
            ["note" => "E", "length" => 0.5],
            ["note" => "D", "length" => 0.5],

            ["barline"],
            ["note" => "D", "length" => 0.5],
            ["note" => "E", "length" => 0.5],
            ["note" => "F", "length" => 1],
            ["note" => "F", "length" => 1],
            ["note" => "G", "length" => 0.5],
            ["note" => "A", "length" => 0.5],

            ["barline"],
            ["note" => "A#", "length" => 0.5],
            ["note" => "A#", "length" => 0.5],
            ["note" => "A", "length" => 1],
            ["note" => "G", "length" => 1],
            ["note" => "F", "length" => 0.5],
            ["note" => "G", "length" => 0.5],

            ["barline"],
            ["note" => "A", "length" => 0.5],
            ["note" => "A", "length" => 0.5],
            ["note" => "G", "length" => 1],
            ["note" => "F", "length" => 1],
            ["note" => "D", "length" => 0.5],
            ["note" => "C", "length" => 0.5],

            ["barline"],
            ["note" => "D", "length" => 0.5],
            ["note" => "F", "length" => 0.5],
            ["note" => "F", "length" => 0.5],
            ["note" => "E", "length" => 0.5],
            ["note" => "E", "length" => 0.5],
            ["note" => "E", "length" => 0.5],
            ["note" => "F#", "length" => 0.5],
            ["note" => "F", "length" => 0.5],

            ["barline"],
        ];

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

        $samples = [];
        $count = 0;
        $length = 0;

        foreach ($notes as $temp => $note) {
            $samples_new = [];
            switch (true) {
                case !isset($note):
                    $this->thing->console("no command provided\n");
                    break;

                case isset($note[0]) and $note[0] == "barline":
                    $count = 0;
                    $this->thing->console("barline\n");
                    break;

                case isset($note["bpm"]):
                    $this->bpm = $note["bpm"];
                    $this->thing->console("bpm " . $this->bpm . "\n");
                    break;

                case isset($note["note"]):
                    if (strpos($note["note"], "_") !== false) {
                        // slur
                        $tone_width = 1.0;
                    } else {
                        $tone_width = 0.99;
                    }

                default:
                    if ($note["note"] == null) {
                        $frequency = 0;
                        $note_text = "rest";
                    } else {
                        $frequency = $this->frequencyTone(
                            rtrim($note["note"], "_")
                        );
                        $note_text = $note["note"];
                    }

                    $count += $note["length"];
                    list($samples_new, $l) = $this->sampleTone(
                        $frequency,
                        $note["length"],
                        $tone_width
                    );

                    $length += $l;
                    if ($verbosity >= 8) {
                        $this->thing->console(
                            "frequency " .
                                $frequency .
                                " note " .
                                $note_text .
                                " count " .
                                $count .
                                " length " .
                                $length .
                                "s " .
                                "\n"
                        );
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
        $this->thing->console("Sample count: " . count($samples));

        //    $samples = $this->highpass($samples);

        $this->saveTone($samples);

        $this->testTone();

        return;
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

        $fd = fopen($file_name, "rb");
        while (($line = fgets($fd)) !== false) {
            $this->lexicon[] = rtrim($line);
        }
        fclose($fd);
        return $this->lexicon;
    }

    function setEnvelope()
    {
        $this->attack_time = 0.05; //s
        $this->attack_level = 1.0;
        $this->decay_time = 0.05;
        $this->release_time = 0.4;
        //$this->sustain_time = $note_duration * 1000 - ($this->attack_time + $this->decay_time + $this->release_time);
        $this->sustain_level = 0.2;

        $envelope = [
            "attack" => [
                "duration" => $this->attack_time,
                "amplitude_end" => $this->attack_level,
            ],
            "decay" => [
                "duration" => $this->decay_time,
                "amplitude_end" => $this->sustain_level,
            ],
            "sustain" => [
                "duration" => null,
                "amplitude_end" => $this->sustain_level,
            ],
            "release" => [
                "duration" => $this->release_time,
                "amplitude_end" => 0.0,
            ],
        ];

        $this->envelope = $envelope;

        return $envelope;
    }

    function getEnvelope($time_point, $note_duration)
    {
        $attack_time = $this->attack_time; //0.0; //ms
        $attack_level = $this->attack_level; //1.0;
        $decay_time = $this->decay_time; //250.0;
        $release_time = $this->release_time; //250.0;
        $sustain_time =
            $note_duration - ($attack_time + $decay_time + $release_time);
        if ($sustain_time < 0) {
            $sustain_time = 0;
        }
        $sustain_level = $this->sustain_level;

        $envelope = [
            "attack" => [
                "duration" => $attack_time,
                "amplitude_end" => $attack_level,
            ],
            "decay" => [
                "duration" => $decay_time,
                "amplitude_end" => $sustain_level,
            ],
            "sustain" => [
                "duration" => $sustain_time,
                "amplitude_end" => $sustain_level,
            ],
            "release" => ["duration" => $release_time, "amplitude_end" => 0.0],
        ];

        $envelope = $this->envelope;
        $envelope["sustain"]["duration"] = $sustain_time;

        $sustain_time =
            $note_duration - ($attack_time + $decay_time + $release_time);

        // Build amplitude envelope
        $amplitude = [];
        $amplitude_start = 0.0;
        foreach ($envelope as $key => $value) {
            $amplitude_end = $value["amplitude_end"];
            $amplitude[$key] = [$amplitude_start, $amplitude_end];
            $amplitude_start = $amplitude_end;
        }

        $elapsed_note_time = 0.0;
        foreach ($envelope as $key => $value) {
            $prior_note_time = $elapsed_note_time;
            $elapsed_note_time += $value["duration"];

            if (
                $time_point < $elapsed_note_time and
                $time_point > $prior_note_time
            ) {
                // We are is $key part of the envelope.

                $t =
                    ($time_point - $prior_note_time) /
                    $envelope[$key]["duration"];
                //var_dump($t);
                $a =
                    $amplitude[$key][0] +
                    ($amplitude[$key][1] - $amplitude[$key][0]) * $t;
                return $a;
            }
        }
        return true;
    }

    function makeNote(
        $transition_width,
        $note_time,
        $frequency,
        $time_point,
        $s,
        $samples
    ) {
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

        foreach (range(0, $steps - 1) as $step) {
            $s = ($num_samples * $transition_width) / $steps;
            $time_point += $s / $this->sample_rate;

            if ($time_point > $note_time) {
                break;
            }

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

        return [$time_point, $s, $samples];
    }

    function sampleTone($frequency, $num_beats, $bpm = 120, $tone_width = 0.2)
    {
        if (!isset($this->phase)) {
            $this->phase = 0;
        }
        $this->setEnvelope(null, null);

        $attack_width = $this->envelope["attack"]["duration"]; //s
        $decay_width = $this->envelope["decay"]["duration"]; //s
        $release_width = $this->envelope["attack"]["duration"]; //s

        $samples = [];
        $time_point = 0;
        //$tone_width = 0.5;
        $marked_note = 1 / 4;
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

        $sustain_width =
            $note_time - ($attack_width + $decay_width + $release_width);
        if ($sustain_width < 0) {
            $sustain_width = 0;
        }

        $mode = "attack";

        switch ($mode) {
            case "attack":
                $transition_width = $attack_width; // 0.25; // Percentage of silent
                $s = 0;
                list($time_point, $s, $samples) = $this->makeNote(
                    $attack_width,
                    $note_time,
                    $frequency,
                    $time_point,
                    $s,
                    $samples
                );

            case "decay":
                // Decay
                list($time_point, $s, $samples) = $this->makeNote(
                    $decay_width,
                    $note_time,
                    $frequency,
                    $time_point,
                    $s,
                    $samples
                );

            case "sustain":
                // Sustain
                $sustain_width = $note_time - ($attack_width + $decay_width);
                $sustain_width = 2; // Cut out on note width
                //$transition_width = $sustain_width; //0.25; // Percentage of silent

                list($time_point, $s, $samples) = $this->makeNote(
                    $sustain_width,
                    $note_time,
                    $frequency,
                    $time_point,
                    $s,
                    $samples
                );

            case "release":
                // Release
                //$transition_width = $release_width; // 0.25; // Percentage of silent

                list($time_point, $s, $samples) = $this->makeNote(
                    $release_width,
                    $note_time,
                    $frequency,
                    $time_point,
                    $s,
                    $samples
                );

                //echo "actual note time " . ($attack_width + $decay_width + $sustain_width + $release_width) . "s \n";

                $pad_width =
                    $note_time -
                    ($attack_width +
                        $decay_width +
                        $sustain_width +
                        $release_width);

                list($time_point, $s, $samples) = $this->makeNote(
                    $pad_width,
                    $note_time,
                    null,
                    $time_point,
                    $s,
                    $samples
                );
        }

        $length = $seconds_per_beat * $num_beats;

        return [$samples, $length];
    }

    function generateTune($matrix, $num_rows = 1)
    {
        $notes = [];
        foreach (range(0, $num_rows) as $temp) {
            // Pick an integer

            $n = rand(0, 11);
            $retrograde = rand(0, 1);
            $inversion = rand(0, 1);

            $line = [];
            foreach (range(0, 11) as $value) {
                if ($inversion == 0) {
                    $line[] = $matrix[$n][$value];
                } else {
                    $line[] = $matrix[$value][$n];
                }
            }

            if ($retrograde == 0) {
                array_reverse($line);
            }
            //$notes = [];
            foreach ($line as $value) {
                //                echo "-".$value."-";
                $this->thing->console(
                    str_pad($this->reverse_clock[$value], 3, " ", STR_PAD_RIGHT)
                );
                $notes[] = [
                    "note" => $this->reverse_clock[$value],
                    "length" => 1,
                ];
            }
            $this->thing->console("\n");
        }
        return $notes;
    }

    function generate2Tune($matrix, $num_rows = 1)
    {
        $rows = [];
        $notes = [];
        //foreach(range(0,$num_rows) as $temp) {

        // Pick an integer
        foreach (range(0, 11) as $value) {
            $prime = [];
            $inverse = [];
            foreach (range(0, 11) as $n) {
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
        foreach ($rows as $row) {
            $note_string = "";
            foreach ($row as $value) {
                $this->thing->console(
                    str_pad($this->reverse_clock[$value], 3, " ", STR_PAD_RIGHT)
                );
                //                $notes[] = array("note"=>$this->reverse_clock[$value],"length"=>1);
                $note_string .= $this->reverse_clock[$value] . " ";
            }
            $note_strings[] = rtrim($note_string);
        }

        $this->thing->console("\n");

        return $note_strings;
    }

    function matrixTune($prime = null)
    {
        //$prime = null;
        // https://unitus.org/FULL/12tone.pdf
        if ($prime == null) {
            $i = 0;
            // Create a random line of 12 semi-tones (no duplicates)
            foreach ($this->semitone_array as $note => $temp) {
                $note_array[] = ["note" => $note, "index" => $i];
                $i += 1;
            }
            shuffle($note_array);
            $prime = [];
            foreach ($note_array as $value => $note) {
                $prime[] = $note["note"];
            }
        }

        $chromatic = [
            "A",
            "A#",
            "B",
            "C",
            "C#",
            "D",
            "D#",
            "E",
            "F",
            "F#",
            "G",
            "G#",
        ];

        $clock = []; // index to note
        $reverse_clock = []; //note to index

        foreach ($chromatic as $index => $note) {
            if ($note == $prime[0]) {
                break;
            }
            $c = next($chromatic);
        }

        foreach ($prime as $index => $note) {
            $clock[] = $c;
            $c = next($chromatic);
            if ($c == false) {
                $c = reset($chromatic);
            }
        }

        foreach ($clock as $index => $note) {
            $reverse_clock[$note] = $index;
        }

        foreach ($prime as $index => $note) {
            $this->thing->console($reverse_clock[$note] . " ");
        }
        $this->thing->console("\n");

        // Initialize matrix
        $matrix = [];
        foreach (range(0, 11) as $i) {
            foreach (range(0, 11) as $j) {
                $matrix[$i][$j] = null;
            }
        }

        // First row
        $j = 0;
        foreach (range(0, 11) as $i) {
            $this->thing->console($reverse_clock[$prime[$i]]);
            $matrix[$i][$j] = $reverse_clock[$prime[$i]];
        }

        // First column
        $i = 0;
        foreach (range(1, 11) as $j) {
            $matrix[$i][$j] = (12 - $reverse_clock[$prime[$j]]) % 12;
        }

        foreach (range(1, 11) as $i) {
            foreach (range(1, 11) as $j) {
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
        $this->thing->console("\n");
        foreach (range(0, 11) as $i) {
            foreach (range(0, 11) as $j) {
                $text = str_pad($matrix[$j][$i], 4, " ", STR_PAD_LEFT);
                $this->thing->console($text);
            }
            $this->thing->console("\n");
        }
        $this->thing->console("\n");
    }

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
            $this->thing->console(
                $note . " > " . $this->frequencyTone($note) . "\n"
            );
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
            "Bb" => $b_flat,
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
    }

    function toneTone(
        $freqOfTone = null,
        $samplesCount = 44100,
        $amplitude_level = 1.0
    ) {
        if (!isset($this->phase)) {
            $this->phase = 0;
        }
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
            $w = $this->phase;
        } else {
            $amplitude = 0.25 * 32768 * $amplitude_level;
            $w = (2 * pi() * $freqOfTone) / $sample_rate;
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

        $samples = [];
        //        for ($n = 0; $n < $samplesCount; $n++) {
        // $this->total_samples holds the current sample pointer.
        for (
            $n = $this->total_samples - 1;
            $n < $samplesCount + $this->total_samples;
            $n++
        ) {
            $samples[] = (int) ($amplitude * sin($n * $w));
        }

        $n = count($samples);

        $this->total_samples += $samplesCount;

        //$this->previous_sample - $samples;

        return $samples;
    }

    function highpass($samples)
    {
        $n = count($samples);
        foreach (range(0, $n - 1) as $value) {
            $delta = abs($samples[$value] - $samples[$value - 1]);

            if ($delta > 500) {
                $this->thing->console(
                    "highpass" .
                        " index " .
                        $value .
                        " " .
                        $samples[$value] .
                        "\n"
                );
                $sum = 0;
                $steps = 5;
                foreach (range(0, $steps - 1) as $i) {
                    $sample_index = $value - $i;

                    $sum += $samples[$sample_index];
                }
                $average = $sum / $steps;
                $samples[$value] = $average;
            }
        }
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

        $file_name = "song3.wav"; // Portal

        //        $myfile = fopen($file_name, "wb") or die("Unable to open file!");

        $myfile = fopen($file_name, "wb");
        if ($myfile === false) {
            $this->response .= "Could not open file to write to. ";
            return true;
        }

        fwrite($myfile, $str);
        fclose($myfile);
        $this->thing->console("file written\n");
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

    public function makePNG()
    {
        $this->x_origin = 100;
        $this->y_origin = 100;

        $this->cell_width = 40;
        $this->cell_height = 20;
        $this->height = 1000;
        $this->width = 1000;

        //$this->font = '/home/nick/Downloads/fonts/Lato-Regular.ttf';
        $this->font = $this->default_font;

        $this->image = imagecreatetruecolor($this->width, $this->height);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        imagefilledrectangle(
            $this->image,
            0,
            0,
            $this->width,
            $this->height,
            $this->white
        );

        $textcolor = imagecolorallocate($this->image, 0, 0, 0);

        $this->drawBin();

        // Write the string at the top left
        $border = 30;
        $radius = (1.165 * (125 - 2 * $border)) / 3;

        //$this->font = '/home/nick/Downloads/fonts/Lato-Regular.ttf';

        $text = "test";
        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);

        $size = 72;
        $angle = 0;
        $bbox = imagettfbbox($size, $angle, $this->font, $text);
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
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
        $pad = 0;

        ob_start();
        imagepng($this->image, "file.png");
        $imagedata = ob_get_contents();
        ob_end_clean();

        $this->thing_report["png"] = $imagedata;

        $response =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="tallygraph"/>';
        $this->image_embedded = $response;

        imagedestroy($this->image);

        return $response;
    }

    function drawBin($bin_name = null)
    {
        if ($bin_name == null) {
            $bin_name = $this->bin_name;
        }

        if (!isset($this->image)) {
            $this->makePNG();
        }

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

        if ($column_min < 2005) {
            $column_min = 2005;
        }

        $row_max = $this->bin_meta["row"]["max"];
        $column_max = $this->bin_meta["column"]["max"];

        // Column title
        $row_title_length = 4;
        $col = 0;

        $this->drawText($this->bin_array[1], $column_min - 1, -2, $this->black);

        foreach (range($column_min, $column_max) as $a) {
            if (
                isset($this->bin_array) and
                isset($this->{$this->bin_array[1] . "_labels"})
            ) {
                $text = $this->{$this->bin_array[1] . "_labels"}[$a - 1];
            } else {
                $text = str_pad($a, 4, " ", STR_PAD_LEFT);
            }

            $col += 1;
            $this->drawText($text, $a - $column_min, -1, $this->black);
        }

        $this->drawText(
            $this->bin_array[0],
            $column_min - 2,
            $row_min - 1,
            $this->black,
            90
        );

        $i = 0;
        $row = 0;

        foreach (range($row_min, $row_max) as $a) {
            $row += 1;
            // Row title

            if (isset($this->{$this->bin_array[0] . "_labels"})) {
                $text = $this->{$this->bin_array[0] . "_labels"}[$a - 1];
            } else {
                $text = str_pad($a, 4, " ", STR_PAD_LEFT);
            }

            $this->drawText($text, -1, $a - $row_min, $this->black);

            $this->thing->console(str_pad($a, 4, " ", STR_PAD_LEFT));
            //Draw rectangle

            foreach (range($column_min, $column_max) as $b) {
                if (!isset($bin[$a][$b])) {
                    $value = 0;
                } else {
                    $value = $bin[$a][$b];
                }
                $bin_range =
                    $this->bin_meta["bin"]["max"] -
                    $this->bin_meta["bin"]["min"] +
                    1;

                if ($bin_range == 0) {
                    $normalized_value = null;
                } else {
                    $normalized_value = floor(($value / $bin_range) * 100);
                }

                if (!isset($bin[$a][$b])) {
                    // Draw empty cell.
                    $text = str_pad("-", 4, " ", STR_PAD_LEFT);
                } else {
                    if ($this->normalize == false) {
                        $text = str_pad($value, 4, " ", STR_PAD_LEFT);
                    } else {
                        $text = str_pad(
                            $normalized_value,
                            4,
                            " ",
                            STR_PAD_LEFT
                        );
                    }

                    $alpha = round(((100 - $normalized_value) * 127) / 100);
                    $colour = imagecolorallocatealpha(
                        $this->image,
                        255,
                        0,
                        0,
                        $alpha
                    );

                    $this->drawCell($b - $column_min, $a - $row_min, $colour);
                    // Draw colored rectangle
                }
                $this->thing->console($text);
            }
        }
    }

    function drawCell($column_index, $row_index, $colour = null)
    {
        if (!isset($this->image)) {
            $this->makePNG();
        }

        $x = $this->cell_width * $column_index + $this->x_origin;
        $y = $this->cell_height * $row_index + $this->y_origin;

        imagefilledrectangle(
            $this->image,
            $x,
            $y,
            $x + $this->cell_width,
            $y + $this->cell_height,
            $colour
        );
    }

    function drawText(
        $text,
        $column_index,
        $row_index,
        $colour = null,
        $angle = 0
    ) {
        $size = 12;
        //$angle = 0;

        $x_tweak = 2;
        $y_tweak = -2;

        if ($colour == null) {
            $colour = $this->black;
        }

        $x = $this->cell_width * $column_index + $this->x_origin + $x_tweak;
        $y =
            $this->cell_height * $row_index +
            $this->cell_height +
            $this->y_origin +
            $y_tweak;

        imagettftext(
            $this->image,
            $size,
            $angle,
            $x,
            $y,
            $colour,
            $this->font,
            $text
        );
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
                if (!isset($this->{$bin_name . "_bin"}[$bin])) {
                    $this->{$bin_name . "_bin"}[$bin] = 0;
                }
                $this->{$bin_name . "_bin"}[$bin] += 1;
                break;
            case 2:
                if (!isset($this->{$bin_name . "_bin"}[$bin[0]][$bin[1]])) {
                    $this->{$bin_name . "_bin"}[$bin[0]][$bin[1]] = 0;
                }
                $this->{$bin_name . "_bin"}[$bin[0]][$bin[1]] += 1;

                break;
            case 3:
                // Multidimensional bin
                if (
                    !isset(
                        $this->{$bin_name . "_bin"}[$bin[0]][$bin[1]][$bin[2]]
                    )
                ) {
                    $this->{$bin_name . "_bin"}[$bin[0]][$bin[1]][$bin[2]] = 0;
                }
                $this->{$bin_name . "_bin"}[$bin[0]][$bin[1]][$bin[2]] += 1;
        }
    }

    function binLimits($bin)
    {
        $bin_total = 0;
        $bin_count = 0;
        foreach ($bin as $row_key => $bin_row) {
            if (!isset($row_min)) {
                $row_min = $row_key;
            }
            if (!isset($row_max)) {
                $row_max = $row_key;
            }

            if ($row_key < $row_min) {
                $row_min = $row_key;
            }
            if ($row_key > $row_max) {
                $row_max = $row_key;
            }

            foreach ($bin_row as $column_key => $bin_column) {
                if (!isset($column_min)) {
                    $column_min = $column_key;
                }
                if (!isset($column_max)) {
                    $column_max = $column_key;
                }

                if ($column_key < $column_min) {
                    $column_min = $column_key;
                }
                if ($column_key > $column_max) {
                    $column_max = $column_key;
                }

                $bin_value = $bin[$row_key][$column_key];
                $bin_total += $bin[$row_key][$column_key];
                $bin_count += 1;

                if (!isset($bin_min)) {
                    $bin_min = $bin_value;
                }
                if (!isset($bin_max)) {
                    $bin_max = $bin_value;
                }

                if ($bin_value < $bin_min) {
                    $bin_min = $bin_value;
                }
                if ($bin_value > $bin_max) {
                    $bin_max = $bin_value;
                }
            }
        }

        return [
            "row" => ["min" => $row_min, "max" => $row_max],
            "column" => ["min" => $column_min, "max" => $column_max],
            "bin" => [
                "min" => $bin_min,
                "max" => $bin_max,
                "total" => $bin_total,
                "count" => $bin_count,
            ],
        ];
    }

    function binReport($bin_name = null)
    {
        $bin = $this->{$bin_name . "_bin"};

        $num_dim = $this->countDimension($bin);
        ksort($bin);
        $n = 0;

        $bin_meta = $this->binLimits($bin);

        $row_title_length = 4;
        $this->thing->console("Bin name: " . $bin_name);
        $this->thing->console("\n");

        $this->thing->console("Selector(s): " . implode("-", $this->selectors));
        $this->thing->console("\n");
        $this->thing->console("Intensity (0-100)");
        // Print column headings
        $this->thing->console("\n");
        $this->thing->console(
            str_pad("", $row_title_length, " ", STR_PAD_LEFT)
        );
        foreach (
            range($bin_meta["column"]["min"], $bin_meta["column"]["max"])
            as $a
        ) {
            $text = str_pad($a, 4, " ", STR_PAD_LEFT);
            $this->thing->console($text);
        }

        $i = 0;
        foreach (
            range($bin_meta["row"]["min"], $bin_meta["row"]["max"])
            as $a
        ) {
            $this->thing->console("\n");
            // Print row title
            //https://stackoverflow.com/questions/4742354/php-day-of-week-numeric-to-day-of-week-text
            //$dow_text = date('D', strtotime("Sunday +{$dow_numeric} days"));
            //$dow_text = date('D', strtotime("Sunday +{$a} days"));
            $this->thing->console(str_pad($a, 4, " ", STR_PAD_LEFT));

            foreach (
                range($bin_meta["column"]["min"], $bin_meta["column"]["max"])
                as $b
            ) {
                if (!isset($bin[$a][$b])) {
                    $value = 0;
                } else {
                    $value = $bin[$a][$b];
                }
                $bin_range =
                    $bin_meta["bin"]["max"] - $bin_meta["bin"]["min"] + 1;
                if ($bin_range == 0) {
                    $normalized_value = null;
                } else {
                    $normalized_value = floor(($value / $bin_range) * 100);
                }

                if (!isset($bin[$a][$b])) {
                    $text = str_pad("-", 4, " ", STR_PAD_LEFT);
                } else {
                    if ($this->normalize == false) {
                        $text = str_pad($value, 4, " ", STR_PAD_LEFT);
                    } else {
                        $text = str_pad(
                            $normalized_value,
                            4,
                            " ",
                            STR_PAD_LEFT
                        );
                    }
                }

                $this->thing->console($text);
            }
        }

        $this->thing->console("\n");
        $this->thing->console("\n");
        $this->thing->console(
            "Bin count: " . $bin_meta["bin"]["count"] . " (=100%)"
        );
        $this->thing->console("\n");
        $this->thing->console("Bin total: " . $bin_meta["bin"]["total"]);
        $this->thing->console("\n");
    }

    function countDimension($Array, $count = 0)
    {
        if (is_array($Array)) {
            return $this->countDimension(current($Array), ++$count);
        } else {
            return $count;
        }
    }

    function getUniqueness($data, $message)
    {
        $max_compare_length = 10;
        //return;
        // Not finsihd
        $n = 0;
        $s = 0;
        $max = 0;
        foreach ($data as $key => $data_gram) {
            $n += 1;

            $l = levenshtein(
                substr($data_gram["message"], 0, $max_compare_length),
                substr($message, 0, $max_compare_length)
            );

            if ($l > $max) {
                $max = $l;
            }

            $s = $s + $l;
        }
        $this->thing->console(".");

        $x = round($s / $n);

        return $x;
    }

    function presetFilter($filter_name)
    {
        switch ($filter_name) {
            case "dooby":
                $this->selectors = [
                    "zonk",
                    "dube",
                    "dooby",
                    "duck",
                    "weed",
                    "smoke",
                    "bong",
                ];
                $this->selector_index = 1;
                $this->excludors = [];
                return;
            case "xox":
                $this->selectors = [
                    "xox",
                    "xxx",
                    "xo",
                    "ox",
                    "hug",
                    "luve",
                    "hugs",
                ];
                $this->selector_index = 1;
                $this->excludors = [];
                return;
        }
    }

    public function readSubject()
    {
    }
}
