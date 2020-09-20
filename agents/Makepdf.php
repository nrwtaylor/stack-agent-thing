<?php
namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);ini_set('display_errors', 1);

// First off this is called 'make pdf' intentionally to mimic the command structure.
// Touchy much?

// This seems to be necessary.  So lets leave all this at the start for now.


use setasign\Fpdi;
//require_once('../lib/fpdf.php');
//require_once('../lib/fpdi.php');

//include_once('../src/phpqrcode.php'); 


// These are the minimum graphics tools a Thing now knows.
// Rotate,translate and scale images provided to a pdf. 
/*
class PDF_Rotate extends FPDF
{
var $angle=0;

function Rotate($angle,$x=-1,$y=-1)
{
    if($x==-1)
        $x=$this->x;
    if($y==-1)
        $y=$this->y;
    if($this->angle!=0)
        $this->_out('Q');
    $this->angle=$angle;
    if($angle!=0)
    {
        $angle*=M_PI/180;
        $c=cos($angle);
        $s=sin($angle);
        $cx=$x*$this->k;
        $cy=($this->h-$y)*$this->k;
        $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
    }
}

function _endpage()
{
    if($this->angle!=0)
    {
        $this->angle=0;
        $this->_out('Q');
    }
    parent::_endpage();
}



}



class PDF extends PDF_Rotate
{

function RotatedText($x,$y,$txt,$angle)
{
    //Text rotated around its origin
    $this->Rotate($angle,$x,$y);
    $this->Text($x,$y,$txt);
    $this->Rotate(0);
}

function RotatedImage($file,$x,$y,$w,$h,$angle)
{
    //Image rotated around its upper-left corner
    $this->Rotate($angle,$x,$y);
    $this->Image($file,$x,$y,$w,$h);
    $this->Rotate(0);
}

}


// And now the Pdf class
// Let's call give it an N-gram to facilitate command 'make pdf'.
// Also means post-poning tackling what Pdf is actually defined as.
// Which might be exactly how it should be.
*/
class makePdf {


       public $var = 'hello';


    	function __construct(Thing $thing, $input = null) {

	$this->input = $input;

	$agent_thing = new Agent($thing, $input);
        $this->thing_report = array('thing' => $thing->thing, 
                        'pdf' => $agent_thing->thing_report['pdf']);

	}



}


?>
