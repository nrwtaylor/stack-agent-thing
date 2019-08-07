<?php
/**
 * Cat.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Amateurradioservice extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {
        $this->agent_name = "amateurradioservice";
        $this->test= "Development code";

        $this->thing_report["info"] = "This is an operator with frequencies.";
        $this->thing_report["help"] = "Provides information useful to the Amateur Radio Service. Try HAM 146.480.";

        $data_source = $this->resource_path . "vector/channels.url";


        //        if (!($this->robot->is_allowed)) {return true;}

        //$data = file_get_contents($data_source, NULL, NULL, 0, 4000);

        $data = file_get_contents($data_source);
        $this->link = $data;
    }


    /**
     *
     */
    function run() {


        $data_source = $this->resource_path . "vector/channels.txt";

        $file_flag = false;

        $data = file_get_contents($data_source);
        $file_flag = true;

        if ($data === false) {

            // Handle quietly.

            $data_source = trim($this->link);

            $data = file_get_contents($data_source);
            if ($data === false) {
                // Handle quietly.
            }



            $file = $this->resource_path . "vector/channels.txt";
            try {

                if ($file_flag == false) {
                    file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
                }
            } catch (Exception $e) {
                // Handle quietly.
            }
        }
        $this->data = $data;

    }


    /**
     *
     */
    function getVector() {

        $data_source = $this->resource_path . "vector/channels.txt";

        $file_flag = true;

        $data = @file_get_contents($data_source);
        if ($data === false) {
            $file_flag = false;
            $this->thing->log( "Data source " . $data_source . " not accessible." );

            // Handle quietly.

            $data_source = trim($this->link);

            $data = @file_get_contents($data_source);
            if ($data === false) {
                $this->thing->log( "Data source " . $data_source . " not accessible." );
                // Handle quietly.
                return;
            }


            $data_target = $this->resource_path . "vector/channels.txt";

            try {

                if ($file_flag === false) {
                    @file_put_contents($data_target, $data, FILE_APPEND | LOCK_EX);
                    $this->thing->log("Data source " . $data_source . " created.");

                }
            } catch (Exception $e) {
                // Handle quietly.
            }
        }
        $this->channel['vector'] = $data;



    }


    /**
     *
     * @return unknown
     */
    public function respond() {
        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "amateurradioservice";

        $this->makeSMS();
        $this->makeChoices();

        $this->thing_report["info"] = "This is an operator with frequencies.";
        $this->thing_report["help"] = "Provides information useful to the Amateur Radio Service. Try HAM 146.480.";

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        if ($this->agent_input == null) {

            $message_thing = new Message($this->thing, $this->thing_report);
            $thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        return $this->thing_report;
    }


    /**
     *
     */
    function makeSMS() {
        if ((!isset($this->response)) or ($this->response == null)){$this->response = "Not found.";}
        //var_dump($this->response);
        $this->node_list = array("amateur radio service"=>array("amateur radio service"));
        $m = strtoupper("AMATEUR RADIO SERVICE") . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create('channel', $this->node_list, "amateur radio service");
        $choices = $this->thing->choice->makeLinks('amateur radio service');
        $this->thing_report['choices'] = $choices;
    }


    /**
     *
     * @param unknown $text (optional)
     */
    function doAmateurradioservice($text = null) {
        $text = trim($text);
        // Yawn.
        $this->getVector();
        $data = $this->channel['vector'];

        $librex = new Librex($this->thing, "librex");

        $librex->librex = $data;
        $librex->getMatches($text, "CSV");
        $channel = reset($librex->matches)[0];

        //        if ($this->agent_input == null) {

        $channel_text =  $this->channelString($channel);
        if (!is_string($channel_text)) {
            $array = array('fizz', 'static', 'pop', 'chatter', 'hiss');
            $k = array_rand($array);
            $v = $array[$k];

            $this->response = strtolower($v);
        }
        $this->response = $channel_text;
        $this->message = $this->response;
        //        } else {

        //            $this->message = $this->agent_input;
        //        }

    }


    /**
     *
     * @param unknown $channel
     * @return unknown
     */
    public function channelString($channel) {
        if ($channel == null) {return;}
        if (!is_array($channel)) {return;}

        $l = "";

        $t = explode(",", $channel["english"]);
        $channel = trim($t[0]);
        $channel_name = trim($t[1]);
        $rx_freq = trim($t[4]);
        $offset = trim($t[5]);
        $tx_freq = trim($t[6]);
        $tx_tone = $t[7]; if ($tx_tone == null) {$tx_tone = "X";}
        $t_sql_output_tone = trim($t[8]); if ($t_sql_output_tone == null) {$t_sql_output_tone = "X";}

        $notes = trim($t[9]);

        $channel_string = "channel id " . $channel . " " . strtoupper($channel_name) ." | rx freq " . $rx_freq . " offset " . $offset . "TX freq " . $tx_freq . " TX tone " . $tx_tone . " TSQL output tone " . $t_sql_output_tone . " note " . $notes;

        return $channel_string;

    }



    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $input= $this->input;
        //var_dump($this->input);
        $strip_words = array("amateur radio service", "ham", "ars", "amateur radio", "amateurradioservice", "frequency");


        foreach ($strip_words as $i=>$strip_word) {

            $whatIWant = $input;
            if (($pos = strpos(strtolower($input), $strip_word. " is")) !== FALSE) {
                $whatIWant = substr(strtolower($input), $pos+strlen($strip_word . " is"));
            } elseif (($pos = strpos(strtolower($input), $strip_word)) !== FALSE) {
                $whatIWant = substr(strtolower($input), $pos+strlen($strip_word));
            }

            $input = $whatIWant;
        }

        //var_dump($input);
        $this->doAmateurradioservice($input);
        return false;
    }


}
