<?php
// namespace Nrwtaylor\StackAgentThing;

//use hyperthese\php-serial\PhpSerial;
//include_once '/var/www/stackr.test/vendor/hyperthese/php-serial/src/PhpSerial.php';
//include_once 'PhpSerial.php';
//include 'PhpSerial.php';


// Install
// sudo pecl install dio-0.0.8
// /etc/php/7.1/cli
// extension=dio.so

$portName = '/dev/ttyACM0';
$baudRate = 4800;
$bits = 8;
$spotBit = 1;

//phpinfo();
            if (!function_exists("dio_open"))
            {
                throw new \Exception("PHP Direct IO is not installed, cannot open serial connection!");
            }

    $bbSerialPort = dio_open($portName, O_RDWR | O_NOCTTY | O_NONBLOCK );

        dio_fcntl($bbSerialPort, F_SETFL, O_SYNC);
        //we're on 'nix configure com from php direct io function
        dio_tcsetattr($bbSerialPort, array(
            'baud' => $baudRate,
            'bits' => $bits,
            'stop'  => $spotBit,
            'parity' => 0
        ));


    if(!$bbSerialPort)
    {
        echo "Could not open Serial port {$portName} ";
        exit;
    }

//        $loop_start_time = microtime(true);
//        $loop_end_time = $loop_start_time + 10;
    //echo "Waiting for {$runForSeconds->format('%S')} seconds to recieve data on serial port" ;

    $client= new GearmanClient();
    $client->addServer();
    $line = "";

    while (true) {
        
        $data = dio_read($bbSerialPort, 1); //this is a blocking call
        if ($data) {
            //echo  "Data Recieved: ". $data ;
            //echo "-".$data."-";
            $line .= $data;
            if ($data == "\n") {

                $arr = json_encode(array("to"=>"null@stackr.ca", "from"=>"kaiju", "subject"=>$line));
                //$arr = json_encode(array("uuid"=>$thing->uuid));

                //$client->doNormal("call_agent", $arr);
                $client->doHighBackground("call_agent", $arr);
                echo $line;
                $line = "";

            }
        }
    }
    echo "Closing Port";
  
    dio_close($bbSerialPort);

echo "foo";

exit();




?>
