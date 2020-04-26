<?php

       $arr = json_encode(array("to"=>"test", "from"=>"test", "subject"=>"s/ worker func test"));

            $client= new GearmanClient();
            $client->addServer();
            $client->doNormal("call_agent", $arr);
            //$client->doHighBackground("call_agent", $arr);
var_dump($client);
