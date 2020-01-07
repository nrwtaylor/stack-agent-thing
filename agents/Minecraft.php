<?php
/**
 * Minecraft.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Minecraft extends Agent {

    public $var = 'hello';

    private $host;
    private $port;
    private $timeout;

    private $token = null;

    private $socket;

    private $errstr = "";

    const SESSION_ID = 2;

    const TYPE_HANDSHAKE = 0x09;
    const TYPE_STAT = 0x00;


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {
        $this->agent_name = "minecraft";
        $this->test= "Development code";

        $this->thing_report["info"] = "This is an agent which keeps an eye on a Minecraft server.";
        $this->thing_report["help"] = "Text MINECRAFT <text>.";

        $this->default_state = "on";

        if (isset($this->thing->container['api']['minecraft'])) {

            if (isset($this->thing->container['api']['minecraft']['default_name'])) {
                $this->default_name = $this->thing->container['api']['minecraft']['default_name'];
            }

            if (isset($this->thing->container['api']['minecraft']['default_address'])) {
                $this->default_address = $this->thing->container['api']['minecraft']['default_address'];
            }

            if (isset($this->thing->container['api']['minecraft']['default_port'])) {
                $this->default_port = $this->thing->container['api']['minecraft']['default_port'];
            }

            if (isset($this->thing->container['api']['minecraft']['server_path'])) {
                $this->server_path = $this->thing->container['api']['minecraft']['server_path'];
            }


        }

    }


    /**
     *
     * @param unknown $data
     * @return unknown
     */
    function replace_a_line($data) {

        if (stristr($data, 'motd')) {

            return "motd=A new message\n";
        }
        return $data;
    }


    /**
     *
     */
    public function set() {

        $data = $this->server_properties;
        if ($data != true) {
            $data = array_map(array($this, 'replace_a_line'), $data);
            $server_properties = implode('', $data);
            // Write server.properties file.
            // Explore changing motd.
            //file_put_contents($this->server_path . "server.properties", $server_properties);

        }
        $player_count = "X";
        if (isset($this->player_count)) {$player_count = $this->player_count;}

        $this->thing->json->writeVariable( array("minecraft", "player_count"), $player_count );

        $this->refreshed_at = $this->current_time;

        $this->variable->setVariable("day", $this->day);

        $this->variable->setVariable("state", $this->state);
        $this->variable->setVariable("refreshed_at", $this->current_time);

        if (isset($this->clocktime)) {
            $this->variable->setVariable("clocktime", $this->clocktime);
        }

        $this->thing->log($this->agent_prefix . 'set Minecraft to ' . $this->state, "INFORMATION");


    }


    /**
     *
     */
    public function run() {
        $this->day = $this->previous_day;

        if ((isset($this->clocktime)) and ($this->previous_clocktime > $this->clocktime)) {
            $this->flag_do_not_respond = false;
            $this->day = $this->previous_day + 1;
            $this->doDay();
        }
    }


    /**
     *
     */
    public function doDay() {

        $this->response .="It is now " . $this->clocktime. ". It is day " . $this->day . ". ";

    }


    /**
     *
     */
    public function get() {

        $this->server_properties = $this->getMinecraft();

        $this->variable = new Variables($this->thing, "variables minecraft " . $this->from);

        $this->previous_day = $this->variable->getVariable("day");
        $this->previous_state = $this->variable->getVariable("state");
        $this->previous_clocktime = $this->variable->getVariable("clocktime");


        $this->refreshed_at = $this->variable->getVariable("refreshed_at");

        $this->thing->log($this->agent_prefix . 'got from db ' . $this->previous_state, "INFORMATION");

        // If it is a valid previous_state, then
        // load it into the current state variable.
        //        if (!$this->isKaiju($this->previous_state)) {
        //            $this->state = $this->previous_state;
        //        } else {
        //            $this->state = $this->default_state;
        //        }

        if ((!isset($this->state)) or ($this->state == false)) {
            $this->state = $this->default_state;
        }

        $this->thing->log($this->agent_prefix . 'got a ' . strtoupper($this->state) . ' FLAG.' , "INFORMATION");

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("minecraft", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("minecraft", "refreshed_at"), $time_string );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->previous_player_count = $this->thing->json->readVariable( array("minecraft", "player_count") );

    }


    /**
     *
     * @return unknown
     */
    public function getMinecraft() {

        if (!isset($this->server_path)) {return true;}

        $data_source = $this->server_path . "server.properties";

        $data = @file($data_source);
        return $data;

        $file_flag = true;

        $data = @file_get_contents($data_source);
        if ($data === false) {
            $file_flag = false;
            $this->thing->log( "Data source " . $data_source . " not accessible." );

            // Handle quietly.
            if (!isset($this->link)) {$this->link = null;}
            $data_source = trim($this->link);

            $data = @file_get_contents($data_source);
            if ($data === false) {
                $this->thing->log( "Data source " . $data_source . " not accessible." );
                // Handle quietly.
                return true;
            }



            try {

                if ($file_flag === false) {
                    //                   @file_put_contents($data_target, $data, LOCK_EX);
                    //                    @file_put_contents($data_target, $data, FILE_APPEND | LOCK_EX);
                    //                    $this->thing->log("Data source " . $data_source . " created.");

                }
            } catch (Exception $e) {
                // Handle quietly.
            }
        }

        return $data;

    }


    /**
     *
     */
    function loadMinecraft() {

    }


    /**
     *
     * @param unknown $text
     * @param unknown $port (optional)
     */
    function doQuery($text, $port = 25565) {
        $host = $text;

        $timeout = 3;
        $auto_connect = false;

        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;

        if (is_array($host)) {
            $this->host = $host['host'];
            $this->port = empty($host['port'])?$port:$host['port'];
            $this->timeout = empty($host['timeout'])?$timeout:$host['timeout'];
            $auto_connect = empty($host['auto_connect'])?$auto_connect:$host['auto_connect'];
        }

        if ($auto_connect === true) {
            $this->connect();
        }


    }


    /**
     *
     */
    private function getNegativetime() {

        // And example of using another agent to get information the cat needs.
        $agent = new Negativetime($this->thing, "minecraft");
        $this->negative_time = $agent->negative_time; //negative time is asking

    }


    /**
     *
     * @return unknown
     */
    public function respondResponse() {

        // Logging for debug
        $thing = new Thing(null);
        $thing->create("merp", "merp", 's/ ' . $this->thing_report['sms']);

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;

        return $this->thing_report;
    }


    /**
     *
     */
    function makeSMS() {

        $response = $this->response;
        //if ((!isset($this->response)) or ($this->response == null)) {$response = "No response. X";}

        if ((isset($this->flag_do_not_respond)) and ($this->flag_do_not_respond === true)) {
            $response = "No response.";
        }


        $this->node_list = null;
        $m = strtoupper($this->agent_name) . " | " . $response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }


    /**
     *
     */
    function makeChoices() {
    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function doMinecraft($text = null) {
        // Yawn.
        if (!isset($this->default_address)) {return true;}
        if (!isset($this->default_port)) {return true;}
        $this->doQuery($this->default_address, $this->default_port);

        if ($this->connect()) {
            $info = $this->get_info();

            $hostname = $info['hostname'];
            $gametype = $info['gametype'];
            $version = $info['version'];
            $map = $info['map'];
            $players = $info['players'];


            $players_text = implode(" ", $players);
            if (mb_strlen($players_text) > 130) {$players_text = "Lots of players on the server.";}
            if (count($players) == 0) {$players_text = "No players on the server.";}

            //  print_r($info);
        }


        if ($this->agent_input == null) {
            //            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            //            $k = array_rand($array);
            //            $v = $array[$k];

            //            $this->response = strtolower($v);

            $this->response .= $players_text;
            $this->minecraft_message = $this->response;
        } else {
            $this->minecraft_message = $this->agent_input;
        }

    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->flag_do_not_respond = true;

        if (stripos($this->subject, 'minecraft quiet') !== false) {
            $this->flag_do_not_respond = true;
        }

        if (stripos($this->subject, 'joined the game') !== false) {
            $this->response .= "Hey. Welcome to this server. ";
            $this->flag_do_not_respond = false;
            return;
        }

        if (stripos($this->subject, 'There are') !== false) {

            $t = explode(":", $this->subject);
            $list = trim($t[1]);
            $this->response .= $list;

            $number_agent = new Number($this->thing, $this->subject);
            $numbers = $number_agent->extractNumbers($this->subject);
            $this->player_count = $numbers[0];
            return;
        }

        if (stripos($this->subject, 'The time is') !== false) {
            $number_agent = new Number($this->thing, $this->subject);
            $number = $number_agent->extracted_number;
            $this->clocktime = $number;
            $this->response .= $number;
            return;
        }

        if (stripos($this->subject, 'tick') !== false) {
            $number_agent = new Number($this->thing, $this->subject);
            $this->tick = $number_agent->extracted_number;
            $this->response = "No response.";
            return;
        }

        if (stripos($this->subject, 'bar') !== false) {
            $number_agent = new Number($this->thing, $this->subject);
            $this->bar = $number_agent->extracted_number;
            $this->response = "No response.";

            if ($this->bar == 1) {
                $this->response .= "Text EDNA PRIVACY for this server's privacy policy. ";
                $this->flag_do_not_respond = false;
            }

            return;
        }

        if (stripos($this->subject, 'thing') !== false) {
            //$this->flag_do_not_respond = true;
            $this->response = "No response.";
            return;
        }


        $this->doMinecraft($this->input);
        return false;
    }



    /**
     * Checks whether or not the current connection is established.
     *
     * @return boolean - True if connected; false otherwise.
     */
    public function is_connected() {
        if (empty($this->token)) return false;
        return true;
    }


    /**
     * Disconnects!
     * duh
     */
    public function disconnect() {
        if ($this->socket) {
            fclose($this->socket);
        }
    }


    /**
     * Connects to the host via UDP with the provided credentials.
     *
     * @return boolean - true if successful, false otherwise.
     */
    public function connect() {
        $this->socket = fsockopen( 'udp://' . $this->host, $this->port, $errno, $errstr, $this->timeout );


        if (!$this->socket) {
            $this->errstr = $errstr;
            return false;
        }

        stream_set_timeout( $this->socket, $this->timeout );
        stream_set_blocking( $this->socket, true );

        return $this->get_challenge();

    }


    /**
     * Authenticates with the host server and saves the authentication token to a class var.
     *
     * @return boolean - True if succesfull; false otherwise.
     */
    private function get_challenge() {
        if (!$this->socket) {
            return false;
        }

        //build packet to get challenge.
        //                $packet = pack("c3N", 0xFE, 0xFD, Query::TYPE_HANDSHAKE, Query::SESSION_ID);
        $packet = pack("c3N", 0xFE, 0xFD, Minecraft::TYPE_HANDSHAKE, Minecraft::SESSION_ID);

        //write packet
        if ( fwrite($this->socket, $packet, strlen($packet)) === FALSE) {
            $this->errstr = "Unable to write to socket";
            return false;
        }

        //read packet.
        $response = fread($this->socket, 2056);

        if (empty($response)) {
            $this->errstr = "Unable to authenticate connection";
            return false;
        }

        $response_data = unpack("c1type/N1id/a*token", $response);

        if (!isset($response_data['token']) || empty($response_data['token'])) {
            $this->errstr = "Unable to authenticate connection.";
            return false;
        }

        $this->token = $response_data['token'];


        return true;

    }


    /**
     * Gets all the info from the server.
     *
     * @return boolean|array - Returns the data in an array, or false if there was an error.
     */
    public function get_info() {
        if (!$this->is_connected()) {
            $this->errstr = "Not connected to host";
            return false;
        }
        //build packet to get info
        //                $packet = pack("c3N2", 0xFE, 0xFD, Query::TYPE_STAT, Query::SESSION_ID, $this->token);
        $packet = pack("c3N2", 0xFE, 0xFD, Minecraft::TYPE_STAT, Minecraft::SESSION_ID, $this->token);
        //add the full stat thingy.
        $packet = $packet . pack("c4", 0x00, 0x00, 0x00, 0x00);

        //write packet
        if (!fwrite($this->socket, $packet, strlen($packet))) {
            $this->errstr = "Unable to write to socket.";
            return false;
        }

        //read packet header
        $response = fread($this->socket, 16);
        //$response = stream_get_contents($this->socket);

        // first byte is type. next 4 are id. dont know what the last 11 are for.
        $response_data = unpack("c1type/N1id", $response);

        //read the rest of the stream.
        $response = fread($this->socket, 2056);

        //split the response into 2 parts.
        $payload = explode( "\x00\x01player_\x00\x00" , $response);

        $info_raw = explode("\x00",  rtrim($payload[0], "\x00"));

        //extract key->value chunks from info
        $info = array();
        foreach (array_chunk($info_raw, 2) as $pair) {
            list($key, $value) = $pair;
            //strip possible color format codes from hostname
            if ($key == "hostname") {
                $value = $this->strip_color_codes($value);
            }
            $info[$key] = $value;
        }

        //get player data.
        $players_raw = rtrim($payload[1], "\x00");
        $players = array();
        if (!empty($players_raw)) {
            $players = explode("\x00", $players_raw);
        }

        //attach player data to info for simplicity
        $info['players'] = $players;

        return $info;
    }


    /**
     * Clears Minecraft color codes from a string.
     *
     * @param String  $string - the string to remove the codes from
     * @return String - a clean string.
     */
    public function strip_color_codes($string) {
        return preg_replace('/[\x00-\x1F\x80-\xFF]./', '', $string);
    }


}
