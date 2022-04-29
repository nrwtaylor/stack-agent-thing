<?php
/**
 * Cat.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Break extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {
        $this->agent_name = "cat";
        $this->test= "Development code";
        $this->thing_report["info"] = "This is a cat keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";
    }

    function run() {
       $this->doBreak();
    }

    function doBreak($text = null) {

$input_agent = new Input($this->think, "break");
    }

    /**
     *
     * @return unknown
     */
    public function readSubject() {
        return false;
    }


}
