<?php
namespace Nrwtaylor\StackAgentThing;

// Display all errors in production.
// The site must run clean transparent code.
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

//ini_set("allow_url_fopen", 1);

// This is written to be understandable.
// Apologies.

class Unit extends Agent
{
    public $var = "hello";

    public function notUsedinit()
    {
        $this->recognized_units = [
            "V" => [],
            "C" => [],
            "Pa" => [],
            "uT" => [],
            "g" => [],
            "M" => [],
            "C" => [],
            "mm" => [],
        ];

        //https://en.wikipedia.org/wiki/International_System_of_Units
        /*
["name"=>"radian", "symbol"=>"rad", "quantity"=>"plane angle", "base_units"=>"m/m"],
["steradian" 	"sr" 	"solid angle" 	"m2/m2"],
["hertz" 	"Hz" 	"frequency" 	s−1 	
["newton" 	"N" 	"force, weight 	kg⋅m⋅s−2 	
["pascal" 	"Pa" 	"pressure, stress 	kg⋅m−1⋅s−2 	N/m2 = J/m3
["joule" 	"J" 	"energy, work, heat" 	kg⋅m2⋅s−2 	N⋅m = Pa⋅m3
["watt" 	"W" 	"power, radiant flux" 	kg⋅m2⋅s−3 	J/s
["coulomb" 	"C" 	"electric charge 	s⋅A 	
["volt" 	"V" 	"electric potential, voltage, emf 	kg⋅m2⋅s−3⋅A−1 	W/A = J/C
["farad" 	"F" 	capacitance 	kg−1⋅m−2⋅s4⋅A2 	C/V = C2/J
["ohm" 	"Ω" 	"resistance, impedance, reactance 	kg⋅m2⋅s−3⋅A−2 	V/A = J⋅s/C2
["siemens" 	"S" 	"electrical conductance 	kg−1⋅m−2⋅s3⋅A2 	Ω−1
["weber" 	"Wb" 	"magnetic flux 	kg⋅m2⋅s−2⋅A−1 	V⋅s
["tesla" 	"T" 	"magnetic flux density 	kg⋅s−2⋅A−1 	Wb/m2
["henry" 	"H" 	"inductance 	kg⋅m2⋅s−2⋅A−2 	Wb/A
["degree Celsius" 	"°C" 	"temperature relative to 273.15 K 	K 	
["lumen" 	"lm" 	"luminous flux 	cd⋅sr 	cd⋅sr
["lux" 	"lx" 	"illuminance 	cd⋅sr⋅m−2 	lm/m2
["becquerel" 	"Bq" 	"activity referred to a radionuclide (decays per unit time) 	s−1 	
["gray" 	"Gy" 	"absorbed dose (of ionising radiation) 	m2⋅s−2 	J/kg
["sievert" 	"Sv" 	"equivalent dose (of ionising radiation) 	m2⋅s−2 	J/kg
["katal" 	"kat" 	"catalytic activity 	mol⋅s−1 	
*/
    }

    public function isUnit($token)
    {
        $conditionedToken = str_replace(".", "", $token);
        $conditionedToken = str_replace(",", "", $conditionedToken);
        if (
            ctype_alnum($conditionedToken) and
            !ctype_alpha($conditionedToken) and
            !is_numeric($conditionedToken)
        ) {
            return true;
        }

        return false;
    }

    public function getNgrams($input, $n = 3)
    {
        if (!isset($this->ngrams)) {
            $this->ngrams = [];
        }
        $words = explode(" ", $input);
        $ngrams = [];

        foreach ($words as $key => $value) {
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

    public function init()
    {
        $this->recognized_units = [
            "V" => ["text" => "volts"],
            "C" => ["text" => "celcius"],
            "Pa" => ["text" => "pressure"],
            "uT" => ["text" => "tesla"],
            "g" => ["text" => "acceleration"],
            "M" => ["text" => "heading"],
            "mm" => ["text" => "millimetre"],
        ];

        $this->node_list = ["start" => ["helpful", "useful"]];

        $this->thing_report["info"] = "Text did not add anything useful.";
        $this->thing_report["help"] =
            "An agent which provides search insight. Click on a button.";

        $this->thing->log("Initialized Text.", "DEBUG");
    }

    function parseUnit($text)
    {
        $match = false;
        foreach ($this->recognized_units as $recognized_unit => $description) {
            if (str_ends_with($text, $recognized_unit)) {
                $match = true;
                $unit = $recognized_unit;
                break;
            }
        }

        if ($match === false) {
            if ($this->isNumber($text)) {
                return ["value" => $text, "unit" => null];
            }

            return true;
        }

        $value = str_replace($unit, "", $text);

        return ["value" => $value, "unit" => $unit];
    }

    function extractUnits($input = null)
    {
        if (is_array($input)) {
            return true;
        }
        $tokens = explode(
            " ",
            str_replace(
                [",", "*", "(", ")", "[", "]", "!", "&", "and", "-"],
                "",
                $input
            )
        );
        $codes = [];

        //     if (!isset($words) or count($words) == 0) {return $ngrams;}

        // Rare for a model to not have a number.
        // And if it doesn't it should be picked up as an ngram.

        foreach ($tokens as $key => $token) {
            //if(1 === preg_match('~[A-Z][0-9]~', strtolower($value))){
            //    $codes[] = $value;
            //}

            if (
                preg_match("/[A-Za-z]/", $token) &&
                preg_match("/[0-9]/", $token)
            ) {
                $codes[] = $this->parseUnit($token);
                continue;
            }

            if ($this->isNumber($token)) {
                $codes[] = $this->parseUnit($token);
                continue;
            }
        }
        $this->units = $codes;
        return $this->units;
    }

    public function run()
    {
        $this->doUnit();
    }

    public function makeResponse()
    {
        // This is a short simple structured response.
        if (!isset($this->response)) {
            $this->response = "";
        }
        $this->response .= 'Asked about,"' . $this->subject . '"' . ". ";
    }

    //public function make() {}

    public function makeSMS()
    {
        $this->thing_report["sms"] = "UNIT";
    }

    public function doUnit($text = null)
    {
    }

    public function set()
    {
        // Log which agent was requested ie Ebay.
        // And note the time.

        $time_string = $this->thing->time();
        $this->thing->Write(["unit", "refreshed_at"], $time_string);

        /// ?
        //$place_agent thing = new Place($this->thing, $ngram);

        $this->thing->log("Set unit refreshed_at.");
    }

    public function readSubject()
    {
        if ($this->input == "unit") {
            return;
        }
    }
}
