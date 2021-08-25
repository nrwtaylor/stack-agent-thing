<?php
namespace Nrwtaylor\StackAgentThing;

// Only run this when executed on the commandline
//if (php_sapi_name() == "cli") {
//    $obj = new Socket();
//    echo $obj->streamSocket();
//}

class Socket extends Agent
{
    function init()
    {
        $this->initSocket();
    }

    function get()
    {
    }

    function set()
    {
    }

    function __destruct()
    {
        $this->thing->console("destruct");
    }

    function initSocket()
    {
        $this->session_terminator = ".";
        $this->address = "127.0.0.1";
        $this->port = 7075;

        if (is_array($this->agent_input)) {

if (isset($this->agent_input['session_terminator'])) {
$this->session_terminator = $this->agent_input['session_terminator'];}
if (isset($this->agent_input['address'])) {$this->address = $this->agent_input['address'];}
if (isset($this->agent_input['port'])) {$this->port = $this->agent_input['port'];}


        }

    }

    public function createSocket() {

        $address = $this->address;
        $port = $this->port;

        if (
            ($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false
        ) {
            $this->thing->console(
                "Couldn't create socket" .
                    socket_strerror(socket_last_error()) .
                    "\n"
            );
        }

if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
    echo socket_strerror(socket_last_error($socket));
    exit;
} 

        if (socket_bind($socket, $address, $port) === false) {
            $this->thing->console(
                "Bind Error " . socket_strerror(socket_last_error($socket)) . "\n"
            );
        }

        if (socket_listen($socket, 5) === false) {
            $this->thing->console(
                "Listen Failed " .
                    socket_strerror(socket_last_error($socket)) .
                    "\n"
            );
        }

        $this->socket = $socket;
    }

    function streamSocket()
    {
        set_time_limit(0); // disable timeout
        ob_implicit_flush(); // disable output caching

        // Settings
        $address = $this->address;
        $port = $this->port;
        /*
    function socket_create ( int $domain , int $type , int $protocol )
    $domain can be AF_INET, AF_INET6 for IPV6 , AF_UNIX for Local communication protocol
    $protocol can be SOL_TCP, SOL_UDP  (TCP/UDP)
    @returns true on success
*/

        if (
            ($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false
        ) {
            $this->thing->console(
                "Couldn't create socket" .
                    socket_strerror(socket_last_error()) .
                    "\n"
            );
        }

        $this->socket = $socket;

        /*
    socket_bind ( resource $socket , string $address [, int $port = 0 ] )
    Bind socket to listen to address and port
*/

        if (socket_bind($socket, $address, $port) === false) {
            $this->thing->console(
                "Bind Error " . socket_strerror(socket_last_error($socket)) . "\n"
            );
        }

        if (socket_listen($socket, 5) === false) {
            $this->thing->console(
                "Listen Failed " .
                    socket_strerror(socket_last_error($socket)) .
                    "\n"
            );
        }

        do {
            if (($msgsock = socket_accept($socket)) === false) {
                $this->thing->console(
                    "Error: socket_accept: " .
                        socket_strerror(socket_last_error($socket)) .
                        "\n"
                );
                break;
            }

            /* Send Welcome message. */
            $msg = "\nPHP Websocket \n";

            // Listen to user input
            do {
                if (
                    false ===
                    ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))
                ) {
                    $this->thing->console(
                        "socket read error: " .
                            socket_strerror(socket_last_error($msgsock)) .
                            "\n"
                    );
                    break 2;
                }
                if (!($buf = trim($buf))) {
                    continue;
                }

                if ($buf === $this->session_terminator) {
                    $this->closeSocket($socket);
                    break 2;
                }
                $talkback = $this->thingSocket($buf) . "\n";
                // Reply to user with their message
                //                $talkback = "PHP: You said '$buf'.\n";
                socket_write($msgsock, $talkback, strlen($talkback));
                // Print message in terminal
                $this->thing->console("$buf\n");
            } while (true);
            socket_close($msgsock);
        } while (true);

        socket_close($socket);
    }

    public function thingSocket($text)
    {
        $thing = new Thing(null);

        $thing->Create($this->from, $this->to, $text);

        $filtered_command = $text;

        $agent = new Agent($thing, $filtered_command);
        $thing_report = $agent->thing_report;

        return $thing_report["sms"];
    }

    public function readSubject()
    {
        $input = $this->input;

        $filtered_input = $this->assert($input, "socket", false);

        if (strtolower($filtered_input) === "stream") {
            $this->streamSocket();
        }

        $url_port = $this->extractUrl($filtered_input);

        $parts = explode(":", $url_port);
        if (isset($parts[1])) {
            $url = $parts[0];
            $port = $parts[1];

            $this->address = $url;
            $this->port = $port;

            $this->response .= "Saw " . $url . " and " . $port . ". ";
        }

        if (stripos($filtered_input, "stream") !== false) {
            $this->streamSocket();
        }
    }

    public function closeSocket($socket)
    {
        socket_close($socket);
    }
    /*
function shutdown(){
    echo "\033c";                                        // Clear terminal
    system("tput cnorm && tput cup 0 0 && stty echo");   // Restore cursor default
    echo PHP_EOL;                                        // New line
    exit;                                                // Clean quit 
}

register_shutdown_function([$this, "shutdown"]);
//register_shutdown_function("shutdown");                  // Handle END of script

declare(ticks = 1);                                      // Allow posix signal handling
pcntl_signal(SIGINT,[$this,"shutdown"]);
*/
}
