<?php

namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';


//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


echo "test_json 2.php";

// Make a new thing and get a random matching keyword record from the db.
//



$subject = "subject ". time();
$thing = new Thing(null);
$thing->Create("test@stackr.ca", "test json", $subject);

$test_agent = new Test($thing, "test");

echo "\n";
echo "Test 1.1: Change a variable\n";

$test_value = 123.01;
$variable_path = array("agent1","c", "b");

$test_array = array("agent1"=>array("var a"=>1, "var b"=>"Hello World", "c" => array("a"=>1.3,"b"=>"BatCar"),"d" => 5),"agent2"=>array("a"=>1.0,"b"=>"FooBar"), "var a" => 4.123, "b" => "FooFoo");
$test_json = '{"agent":["a",1234,"hello world",1,2,4.0,8.2,9,"5.123","123,000","test"]}';

$expected_array = array("agent1"=>array("a"=>1, "b"=>123.01),"agent2"=>array("a"=>1.0,"b"=>"FooBar"), "a" => 4.123, "b" => "FooFoo");;
$expected_json = '{"agent":["a",1234,123.01,"hello world",1,2,4.0,8.2,9,"5.123","123,000","test"]}';

$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);
$value1 = $message->readVariable($variable_path);


$message->writeVariable($variable_path, $test_value);
$value2 = $message->readVariable($variable_path);


if($value1 == "BatCar") {echo "Pass 1  ";} else {
echo "Fail 1  \n";
echo 'returned'; print_r($message->json_data); echo '\n';
echo '$expected_response: '; print_r($expected_json); echo '\n';

	}
if($value2 == $test_value) {echo "Pass 2 ";} else {
	echo "Fail 2 \n";
echo 'returned'; print_r($message->json_data); echo '\n';
echo '$expected_response: '; print_r($expected_json); echo '\n';

}


echo "\n";
echo "Test 1.2: Attempt to read a non-existent variable using readVariable\n";

$variable_path = array("level 1 1","level 2 1");

$test_array = array("level 1 1"=>array("level 2 1"=>1, "level 2 2"=>"Hello World", "level 2 3" => array("level 3 1"=>1.3,"level 3 2"=>"BatCar"),"level 2 4" => 5),"level 1 2"=>array("a"=>1.0,"b"=>"FooBar"), "var a" => 4.123, "b" => "FooFoo");



$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);


$value = $message->readVariable($variable_path);

$expected_value = 1;



if($value == $expected_value) {echo "Pass  \n";} else {
	echo "Fail  \n";
echo 'returned $value1'; print_r($value); echo '\n';
echo '$expected_response: '; print_r($expected_value); echo '\n';

	}



echo "\n";
echo "Test 1.3: Attempt to read a non-existent variable using readVariable\n";

$variable_path = array("level 1 1","level 2 3");

$test_array = array("level 1 1"=>array("level 2 1"=>1, "level 2 2"=>"Hello World", "level 2 3" => array("level 3 1"=>1.3,"level 3 2"=>"BatCar"),"level 2 4" => 5),"level 1 2"=>array("a"=>1.0,"b"=>"FooBar"), "var a" => 4.123, "b" => "FooFoo");

$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);

$value = $message->readVariable($variable_path);

$expected_value = array('level 3 1'=>1.3, 'level 3 2'=>'BatCar');

if($value == $expected_value) {echo "Pass  ";} else {
	echo "Fail  \n";
    echo 'returned $value1'; print_r($value); echo '\n';
    echo '$expected_response: '; print_r($expected_value); echo '\n';
}


echo "\n";
echo "Test 1.4: Attempt to read a non-existent variable using readVariable\n";

$variable_path = array("level 1 1","level 2 3","level 3 2");

$test_array = array("level 1 1"=>array("level 2 1"=>1, "level 2 2"=>"Hello World", "level 2 3" => array("level 3 1"=>1.3,"level 3 2"=>"BatCar"),"level 2 4" => 5),"level 1 2"=>array("a"=>1.0,"b"=>"FooBar"), "var a" => 4.123, "b" => "FooFoo");



$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);




$value = $message->readVariable($variable_path);

$expected_value = 'BatCar';



if($value == $expected_value) {echo "Pass  \n";} else {
	echo "Fail  \n";
echo 'returned $value1'; print_r($value); echo '\n';
echo '$expected_response: '; print_r($expected_value); echo '\n';

	}



echo "\n";
echo "Test 1.5: Attempt to read a non-existent variable using readVariable\n";

$variable_path = array("level 1 1","level 2 3","level 3 3");

$test_array = array("level 1 1"=>array("level 2 1"=>1, "level 2 2"=>"Hello World", "level 2 3" => array("level 3 1"=>1.3,"level 3 2"=>"BatCar"),"level 2 4" => 5),"level 1 2"=>array("a"=>1.0,"b"=>"FooBar"), "var a" => 4.123, "b" => "FooFoo");



$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);




$value = $message->readVariable($variable_path);

$expected_value = false;



if($value == $expected_value) {echo "Pass  \n";} else {
	echo "Fail  \n";
echo 'returned $value1'; print_r($value); echo '\n';
echo '$expected_response: '; print_r($expected_value); echo '\n';

	}


echo "\n";
echo "Test 1.6: Attempt to read a non-existent variable using readVariable\n";

$variable_path = array("level 1 1","level 2 5","level 3 3");

$test_array = array("level 1 1"=>array("level 2 1"=>1, "level 2 2"=>"Hello World", "level 2 3" => array("level 3 1"=>1.3,"level 3 2"=>"BatCar"),"level 2 4" => 5),"level 1 2"=>array("a"=>1.0,"b"=>"FooBar"), "var a" => 4.123, "b" => "FooFoo");



$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);

$value = $message->readVariable($variable_path);

$expected_value = false;



if($value == $expected_value) {echo "Pass  \n";} else {
	echo "Fail  \n";
echo 'returned $value1'; print_r($value); echo '\n';
echo '$expected_response: '; print_r($expected_value); echo '\n';

	}

//var_dump($thing);

echo "\n";
echo "Test 1.7: Check read invalid variable returns false from readVariable call\n";

$thing = new Thing(null);
$thing->Create();

$settings = '{"agent":[],"choice":[{"inside nest":{"nest maintenance":{"patrolling":"foraging","0":"foraging"}},"midden work":"foraging"}]}';

$variables = '{"agent":[],"29fc63bb-1715-42ac-98d6-391b1195d962":{"choices":["patrolling","foraging"],"decision":null}';

$thing->json->setField("settings");
$thing->json->setJson($settings);



$response = $thing->json->readVariable(array("choice2"));


$expected_response = false;

if($expected_response == $response) {
	echo "Pass 1  \n";
	} else {
	echo "Fail 1  \n";
	echo ''; print_r($response); echo '\n';
	}


echo "\n";
echo "Test 1.2: Check read valid variable returns expected array\n";



//	echo '<pre> $thing->json->array_data: '; print_r($thing->json->array_data['choice'][0]); echo '</pre>';

$response = $thing->json->readVariable(array("choice",0,"inside nest"));

$expected_response = array("nest maintenance"=>array("patrolling"=>"foraging","foraging"));

if($expected_response === $response) {
	echo "Pass 1  \n";
	} else {
	echo "Fail 1  \n";
	echo '$response: '; print_r($response); echo '\n';
	echo '$expected_response: '; print_r($expected_response); echo '\n';
	echo '$array_diff: '; print_r(array_diff($expected_response,$response)); echo '\n';
	}








//$thing->json->setField("variables");
//$thing->json->setJson($variables);


//	echo '<pre>'; print_r($thing->json->readVariable(array("decision"))); echo '</pre>';

//Test readJson()

echo "\n";
echo "Test 1: array>SQL>json\n";
echo "Create an array and write it to message0 as JSON\n";

$expected_result = '{"key":1234}';

$message = new Json($thing->uuid);
$message->setField("message0");




$message->array_data = array("key"=>1234);
$message->arraytoJson();
$message->write();


$message->read();

echo $message->json_data;

if($expected_result ==$message->json_data) {
	echo "Pass\n";
	} else {
	echo "Fail\n";
	}


echo "\n";
echo "Test 2: array>SQL>json\n";
echo "Create an array and use setArray to write it to message0 as JSON\n";

$expected_result = '{"key":1234}';

$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray(array("key"=>1234));

$message->read();

echo $message->json_data;

if($expected_result ==$message->json_data) {
	echo "Pass\n";
	} else {
	echo "Fail\n";
	}


echo "\n";
echo "Test 3: json>SQL>array \n";

$expected_result = array("key"=>1234);


$message = new Json($thing->uuid);
$message->setField("message0");
$message->setJson('{"key":1234}');

$message->read();

if($expected_result ==$message->array_data) {
	echo "Pass\n";
	} else {
	echo "Fail\n";
	}


// Document structure.

echo "\n";
echo "Test 4.1: Document json structure\n";
echo "Create an array and write it to message0 as JSON\n";

$test_array = array("thing"=>array("a"=>1234, "b"=>'hello world'), 1=>"test numeric");
$test_json = '{"thing":{"a":1234,"b":"hello world"},"1":"test numeric"}';

$expected_json = '{"thing":{"a":1234,"b":"hello world"},"1":"test numeric"}';
$expected_array = array("thing"=>array("a"=>1234, "b"=>'hello world'), "1"=>"test numeric");


$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);

$message->write();

unset($message);
$message = new Json($thing->uuid);
$message->setField("message0");

$message->read();

if($expected_json ==$message->json_data) {
	echo "Pass 1 - json_data as expected  \n";
	} else {
	echo "Fail 1  \n";
	echo ''; print_r($message->json_data); echo '\n';
	}


if($expected_array ==$message->array_data) {
	echo "Pass 2 - array_data as expected\n";
	} else {
	echo "Fail 2 \n";
	echo ''; print_r($message->array_data); echo '\n';
	}




$message->setJson($test_json);

$message->write();

unset($message);
$message = new Json($thing->uuid);
$message->setField("message0");

$message->read();

//echo $message->json_data;

if($expected_json ==$message->json_data) {
	echo "Pass 3  \n";
	} else {
	echo "Fail 3  \n";
	}


if($expected_array ==$message->array_data) {
	echo "Pass 4 \n";
	} else {
	echo "Fail 4 \n";
	}



// Stream structure.

echo "\n";
echo "Test 4.2: Stream json structure\n";

$test_array = array("agent"=>array("a", 1234, 'hello world', 1, 2, 4.0, 8.2, 9, "5.123", "123,000", "test"));
$test_json = '{"agent":["a",1234,"hello world",1,2,4.0,8.2,9,"5.123","123,000","test"]}';

$expected_array = $test_array;
$expected_json = $test_json;

$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);

$message->write();
$message->read();

if($expected_json ==$message->json_data) {echo "Pass 1  \n";} else {
	echo "Fail 1  \n";
	echo ''; print_r($message->json_data); echo '\n';
	}
if($expected_array ==$message->array_data) {echo "Pass 2 \n";} else {echo "Fail 2 \n";}

$message->setJson($test_json);

$message->write();
$message->read();

if($expected_json ==$message->json_data) {echo "Pass 3  \n";} else {echo "Fail 3  \n";}
if($expected_array ==$message->array_data) {echo "Pass 4 \n";} else {echo "Fail 4 \n";}


echo "\n";
echo "Test 5.1.1: popStream(x)\n";


$test_array = array("agent"=>array("a", 1234, 'hello world', 1, 2, 4.0, 8.2, 9, "5.123", "123,000", "test"));
$test_json = '{"agent":["a",1234,"hello world",1,2,4.0,8.2,9,"5.123","123,000","test"]}';
$expected_array = array("agent"=>array("a", 1234, 'hello world', 1, 4.0, 8.2, 9, "5.123", "123,000", "test"));

$expected_json = '{"agent":["a",1234,"hello world",1,4.0,8.2,9,"5.123","123,000","test"]}';

$message = new Json($thing->uuid);
$message->setField("message0");
$message->setJson($test_json);


$message->popStream(4);


if($expected_json ==$message->json_data) {echo "Pass 1  \n";} else {
	echo "Fail 1  \n";
	echo 'returned'; print_r($message->json_data); echo '\n';
	echo 'expected'; print_r($expected_json); echo '\n';
	}
if($expected_array ==$message->array_data) {echo "Pass 2 \n";} else {
	echo "Fail 2 ";
	echo 'returned'; print_r($message->array_data); echo '\n';
	echo 'expected'; print_r($expected_array); echo '\n';
}


echo "\n";
echo "Test 5.1.2: popStream()\n";


$test_array = array("agent"=>array("a", 1234, 'hello world', 1, 2, 4.0, 8.2, 9, "5.123", "123,000", "test"));
$test_json = '{"agent":["a",1234,"hello world",1,2,4.0,8.2,9,"5.123","123,000","test"]}';
$expected_array = array("agent"=>array("a", 1234, 'hello world', 1, 2, 4.0, 8.2, 9, "5.123", "123,000"));

$expected_json = '{"agent":["a",1234,"hello world",1,2,4.0,8.2,9,"5.123","123,000"]}';

$message = new Json($thing->uuid);
$message->setField("message0");
$message->setJson($test_json);


$message->popStream();


if($expected_json ==$message->json_data) {echo "Pass 1  \n";} else {
	echo "Fail 1  \n";
	echo 'returned'; print_r($message->json_data); echo '\n';
	echo 'expected'; print_r($expected_json); echo '\n';
	}
if($expected_array ==$message->array_data) {echo "Pass 2 \n";} else {
	echo "Fail 2 \n";
	echo 'returned'; print_r($message->array_data); echo '\n';
	echo 'expected'; print_r($expected_array); echo '\n';
}



echo "\n";
echo "Test 5.2: pushStream string\n";

$test_value = "pusher";
$test_array = array("agent"=>array("a", 1234, 'hello world', 1, 2, 4.0, 8.2, 9, "5.123", "123,000", "test"));
$test_json = '{"agent":["a",1234,"hello world",1,2,4.0,8.2,9,"5.123","123,000","test"]}';

$expected_array = array("agent"=>array("a", "pusher", 1234, 'hello world', 1, 2, 4.0, 8.2, 9, "5.123", "123,000", "test"));

$expected_json = '{"agent":["a","pusher",1234,"hello world",1,2,4.0,8.2,9,"5.123","123,000","test"]}';

$message = new Json($thing->uuid);
$message->setField("message0");
$message->setJson($test_json);


$message->pushStream($test_value,1);

if($expected_json ==$message->json_data) {echo "Pass 1  \n";} else {
	echo "Fail 1  \n";
echo ' returned'; print_r($message->json_data); echo '\n';
echo ' expected'; print_r($expected_json); echo '\n';
	}
if($expected_array ==$message->array_data) {echo "Pass 2 \n";} else {
	echo "Fail 2 \n";
echo ' returned'; print_r($message->array_data); echo '\n';
echo ' expected'; print_r($expected_array); echo '\n';
}






echo "\n";
echo "Test 5.3: pushStream value\n";

$test_value = 123.01;
$test_array = array("agent"=>array("a", 1234, 'hello world', 1, 2, 4.0, 8.2, 9, "5.123", "123,000", "test"));
$test_json = '{"agent":["a",1234,"hello world",1,2,4.0,8.2,9,"5.123","123,000","test"]}';

$expected_array = array("agent"=>array("a", 1234, 123.01, 'hello world', 1, 2, 4.0, 8.2, 9, "5.123", "123,000", "test"));

$expected_json = '{"agent":["a",1234,123.01,"hello world",1,2,4.0,8.2,9,"5.123","123,000","test"]}';

$message = new Json($thing->uuid);
$message->setField("message0");
$message->setJson($test_json);


$message->pushStream($test_value,2);

if($expected_json ==$message->json_data) {echo "Pass 1  \n";} else {
	echo "Fail 1  \n";
echo ' returned'; print_r($message->json_data); echo '\n';
echo ' expected'; print_r($expected_json); echo '\n';
	}
if($expected_array ==$message->array_data) {echo "Pass 2 \n";} else {
	echo "Fail 2 \n";
echo ' returned'; print_r($message->array_data); echo '\n';
echo ' expected'; print_r($expected_array); echo '\n';
}




echo "\n";
echo "Test 6.2: Delete a variable\n";

//$test_value = 123.01;

$variable_path = array("agent1","c", "b");

$test_array = array("agent1"=>array("var a"=>1, "var b"=>"Hello World", "c" => array("a"=>1.3,"b"=>"BatCar"),"d" => 5),"agent2"=>array("a"=>1.0,"b"=>"FooBar"), "var a" => 4.123, "b" => "FooFoo");
$test_json = '{"agent1":{"var a":1,"var b":"Hello World","c":{"a":1.3,"b":"BatCar"},"d":5},"agent2":{"a":1.0,"b":"FooBar"},"var a":4.123,"b":"FooFoo"}';

$expected_array = array("agent1"=>array("var a"=>1, "var b"=>"Hello World", "c" => array("a"=>1.3),"d" => 5),"agent2"=>array("a"=>1.0,"b"=>"FooBar"), "var a" => 4.123, "b" => "FooFoo");
$expected_json = '{"agent1":{"var a":1,"var b":"Hello World","c":{"a":1.3},"d":5},"agent2":{"a":1.0,"b":"FooBar"},"var a":4.123,"b":"FooFoo"}';

// Set-up test array and JSON for testing
$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);

// Delete the variable at $variable path.
$value1 = $message->deleteVariable($variable_path);



if($message->array_data == $expected_array) {echo "Pass 1 \n";} else {
	echo "Fail 2 \n";
echo ' expected'; print_r($expected_array); echo '\n';
echo ' returned'; print_r($message->array_data); echo '\n';
}

if($message->json_data == $expected_json) {echo "Pass 2 \n";} else {
	echo "Fail 2 \n";
echo ' expected'; print_r($expected_json); echo '\n';
echo ' returned'; print_r($message->json_data); echo '\n';


}



echo "\n";
echo "Test 6.3: Write a new new variable within existing JSON (String)\n";

$test_value = 123.01;

$variable_path = array("agent1","c", "e");

$test_array = array("agent1"=>array("var a"=>1, "var b"=>"Hello World", "c" => array("a"=>1.3,"b"=>"BatCar"),"d" => 5),"agent2"=>array("a"=>1.0,"b"=>"FooBar"), "var a" => 4.123, "b" => "FooFoo");
$test_json = '{"agent1":{"var a":1,"var b":"Hello World","c":{"a":1.3,"b":"BatCar"},"d":5},"agent2":{"a":1.0,"b":"FooBar"},"var a":4.123,"b":"FooFoo"}';

$expected_array = array("agent1"=>array("var a"=>1, "var b"=>"Hello World", "c" => array("a"=>1.3, "b" => "BatCar", "e" => 123.01),"d" => 5),"agent2"=>array("a"=>1.0,"b"=>"FooBar"), "var a" => 4.123, "b" => "FooFoo");
$expected_json = '{"agent1":{"var a":1,"var b":"Hello World","c":{"a":1.3,"b":"BatCar","e":123.01},"d":5},"agent2":{"a":1.0,"b":"FooBar"},"var a":4.123,"b":"FooFoo"}';

$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);

$value1 = $message->writeVariable($variable_path, $test_value);

unset($message);
$message = new Json($thing->uuid);
$message->setField("message0");
$value2 = $message->readVariable($variable_path);





if($message->array_data == $expected_array) {echo "Pass (Array) \n";} else {
	echo "Fail (Array) \n";
echo ' expected'; print_r($expected_array); echo '\n';
echo ' returned'; print_r($message->array_data); echo '\n';
}

if($message->json_data == $expected_json) {echo "Pass (JSON) \n";} else {
	echo "Fail (JSON) \n";
echo ' expected'; print_r($expected_json); echo '\n';
echo ' returned'; print_r($message->json_data); echo '\n';
}

if($value2 == 123.01) {echo "Pass 3 \n";} else {
	echo "Fail 3 ";
echo ' expected'; print_r(123.01); echo '\n';
echo ' returned'; print_r($value2); echo '\n';
}




echo "\n";
echo "Test 6.4: Write a variable (array)\n";

$test_value = array("hello"=>array("new"=>"test"));

$variable_path = array("agent1","c", "e");

$test_array = array("agent1"=>array("var a"=>1, "var b"=>"Hello World", "c" => array("a"=>1.3,"b"=>"BatCar"),"d" => 5),"agent2"=>array("a"=>1.0,"b"=>"FooBar"), "var a" => 4.123, "b" => "FooFoo");

$test_json = '{"agent1":{"var a":1,"var b":"Hello World","c":{"a":1.3,"b":"BatCar"},"d":5},"agent2":{"a":1.0,"b":"FooBar"},"var a":4.123,"b":"FooFoo"}';


$expected_array = array("agent1"=>array("var a"=>1, "var b"=>"Hello World", "c" => array("a"=>1.3,"b"=>"BatCar","e"=>array("hello"=>array("new"=>"test"))),"d" => 5),"agent2"=>array("a"=>1.0,"b"=>"FooBar"), "var a" => 4.123, "b" => "FooFoo");

$expected_json = '{"agent1":{"var a":1,"var b":"Hello World","c":{"a":1.3,"b":"BatCar","e":{"hello":{"new":"test"}}},"d":5},"agent2":{"a":1.0,"b":"FooBar"},"var a":4.123,"b":"FooFoo"}';



$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);
//$message->setJson($test_json);

$value1 = $message->writeVariable($variable_path, $test_value);



unset($message);
$message = new Json($thing->uuid);
$message->setField("message0");
$value2 = $message->readVariable($variable_path);





if($message->array_data == $expected_array) {echo "Pass (Array) \n";} else {
	echo "Fail (Array) \n";
echo ' expected'; print_r($expected_array); echo '\n';
echo ' returned'; print_r($message->array_data); echo '\n';
}

if($message->json_data == $expected_json) {echo "Pass (JSON) \n";} else {
	echo "Fail (JSON) \n";
echo ' expected'; print_r($expected_json); echo '\n';
echo ' returned'; print_r($message->json_data); echo '\n';
}

if($value2 == array("hello"=>array("new"=>"test"))) {echo "Pass 3 ";} else {
	echo "Fail 3 ";
echo '<pre> expected'; print_r(array("hello"=>array("new"=>"test"))); echo '</pre>';
echo '<pre> returned'; print_r($value2); echo '</pre>';
}



echo "\n";
echo "Test 6.5: Write a variable to a set position(array)\n";

$test_value = array("hello"=>array("new"=>"test"));

$variable_path = array("agent1","c", "b");

$test_array = array("agent1"=>array("var a"=>1, "var b"=>"Hello World", "c" => array("a"=>1.3,"b"=>"BatCar"),"d" => 5),"agent2"=>array("a"=>1.0,"b"=>"FooBar"), "var a" => 4.123, "b" => "FooFoo");

$test_json = '{"agent1":{"var a":1,"var b":"Hello World","c":{"a":1.3,"b":"BatCar"},"d":5},"agent2":{"a":1.0,"b":"FooBar"},"var a":4.123,"b":"FooFoo"}';


$expected_array = array("agent1"=>array("var a"=>1, "var b"=>"Hello World", "c" => array("a"=>1.3,"b"=>array("hello"=>array("new"=>"test"))),"d" => 5),"agent2"=>array("a"=>1.0,"b"=>"FooBar"), "var a" => 4.123, "b" => "FooFoo");

$expected_json = '{"agent1":{"var a":1,"var b":"Hello World","c":{"a":1.3,"b":{"hello":{"new":"test"}}},"d":5},"agent2":{"a":1.0,"b":"FooBar"},"var a":4.123,"b":"FooFoo"}';

$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);
//$message->setJson($test_json);

$value1 = $message->writeVariable($variable_path, $test_value);

unset($message);
$message = new Json($thing->uuid);
$message->setField("message0");
$value2 = $message->readVariable($variable_path);

if($message->array_data == $expected_array) {echo "Pass (Array) \n";} else {
	echo "Fail (Array) \n";
echo ' expected'; print_r($expected_array); echo '\n';
echo ' returned'; print_r($message->array_data); echo '\n';
}

if($message->json_data == $expected_json) {echo "Pass (JSON) \n";} else {
	echo "Fail (JSON) \n";
echo ' expected'; print_r($expected_json); echo '\n';
echo ' returned'; print_r($message->json_data); echo '\n';
}

if($value2 == array("hello"=>array("new"=>"test"))) {echo "Pass 3 ";} else {
	echo "Fail 3 \n";
echo ' expected'; print_r(array("hello"=>array("new"=>"test"))); echo '\n';
echo ' returned'; print_r($value2); echo '\n';
}




echo "\n";
echo "Test 6.6: Write a variable to a blank array\n";

$test_value = array("hello"=>array("new"=>"test"));

$variable_path = array("agent1","c", "b");

$test_array = array();

$test_json = '{}';


$expected_array = array("agent1"=>array("c" => array("b"=>array("hello"=>array("new"=>"test")))));

$expected_json = '{"agent1":{"c":{"b":{"hello":{"new":"test"}}}}}';

$message = new Json($thing->uuid);
$message->setField("message1");
$message->setArray($test_array);
//$message->setJson($test_json);

$value1 = $message->writeVariable($variable_path, $test_value);



unset($message);
$message = new Json($thing->uuid);
$message->setField("message1");
$value2 = $message->readVariable($variable_path);





if($message->array_data == $expected_array) {echo "Pass (Array) \n";} else {
	echo "Fail (Array) \n";
echo ' expected'; print_r($expected_array); echo '\n';
echo ' returned'; print_r($message->array_data); echo '\n';
}

if($message->json_data == $expected_json) {echo "Pass (JSON) \n";} else {
	echo "Fail (JSON) \n";
echo ' expected'; print_r($expected_json); echo '\n';
echo ' returned'; print_r($message->json_data); echo '\n';
}

if($value2 == array("hello"=>array("new"=>"test"))) {echo "Pass 3 \n";} else {
	echo "Fail 3 \n";
echo ' expected'; print_r(array("hello"=>array("new"=>"test"))); echo '\n';
echo ' returned'; print_r($value2); echo '\n';
}







echo "/n";
echo "Test 7.1: Post to stream/n";

$test_value = 123.01;

$variable_path = array("agent1","c", "e");

$test_array = array();

$test_json = '{}';


$expected_array = array("agent"=>array(123.01));

$expected_json = '{"agent":[123.01]}';



$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);
//$message->setJson($test_json);

$value1 = $message->pushStream($test_value, 0);



if($expected_json ==$message->json_data) {echo "Pass 1  /n";} else {
	echo "Fail 1  /n";
echo ' returned'; print_r($message->json_data); echo '/n';
echo ' expected'; print_r($expected_json); echo '/n';
	}
if($expected_array ==$message->array_data) {echo "Pass 2 /n";} else {
	echo "Fail 2 ";
echo ' returned'; print_r($message->array_data); echo '/n';
echo ' expected'; print_r($expected_array); echo '/n';
}


echo "\n";
echo "Test 7.2: Post to stream\n";

$test_value = "test text";

$test_array = array("agent"=>array(123.01));

$test_json = '{"agent":[123.01]}';;


$expected_array = array("agent"=>array("test text", 123.01));

$expected_json = '{"agent":["test text",123.01]}';



$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);
//$message->setJson($test_json);

$value1 = $message->pushStream($test_value, 0);



if($expected_json ==$message->json_data) {echo "Pass 1  \n";} else {
	echo "Fail 1  \n";
echo ' returned'; print_r($message->json_data); echo '\n';
echo ' expected'; print_r($expected_json); echo '\n';
	}
if($expected_array ==$message->array_data) {echo "Pass 2 \n";} else {
	echo "Fail 2 \n";
echo ' returned'; print_r($message->array_data); echo '\n';
echo ' expected'; print_r($expected_array); echo '\n';
}



echo "\n";
echo "Test 7.3: Post to end of stream\n";

$test_value = "test text";

$test_array = array("agent"=>array(123.01));

$test_json = '{"agent":[123.01]}';;


$expected_array = array("agent"=>array(123.01, "test text"));

$expected_json = '{"agent":[123.01,"test text"]}';

$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);
//$message->setJson($test_json);

$value1 = $message->pushStream($test_value);

if($expected_json ==$message->json_data) {echo "Pass 1  \n";} else {
	echo "Fail 1  \n";
echo ' returned'; print_r($message->json_data); echo '\n';
echo ' expected'; print_r($expected_json); echo '\n';
	}
if($expected_array ==$message->array_data) {echo "Pass 2 \n";} else {
	echo "Fail 2 \n";
echo ' returned'; print_r($message->array_data); echo '\n';
echo ' expected'; print_r($expected_array); echo '\n';
}



echo "\n";
echo "Test 7.4: Post to end of stream\n";

$test_value = "test text";

$test_array = array("agent"=>array(123.01, 5, 6, "meep"));

$test_json = '{"agent":[123.01]}';;


$expected_array = array("agent"=>array(123.01, 5, 6, "meep", "test text"));

$expected_json = '{"agent":[123.01,5,6,"meep","test text"]}';



$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);
//$message->setJson($test_json);

$value1 = $message->pushStream($test_value);



if($expected_json ==$message->json_data) {echo "Pass 1  \n";} else {
	echo "Fail 1  \n";
echo ' returned'; print_r($message->json_data); echo '\n';
echo ' expected'; print_r($expected_json); echo '\n';
	}
if($expected_array ==$message->array_data) {echo "Pass 2 \n";} else {
	echo "Fail 2 \n";
echo ' returned'; print_r($message->array_data); echo '\n';
echo ' expected'; print_r($expected_array); echo '\n';
}



echo "\n";
echo "Test 8: Post multipe to stream and check field too long response\n";

//$test_value = rand(100000,999999);

$test_array = array("agent"=>array(123.01));

$test_json = '{"agent":[123.01]}';


$expected_array = array("agent"=>array("20-1234567890abcdef","19-1234567890abcdef","18-1234567890abcdef","17-1234567890abcdef","16-1234567890abcdef","15-1234567890abcdef","14-1234567890abcdef","13-1234567890abcdef"));

$expected_json = '{"agent":["20-1234567890abcdef","19-1234567890abcdef","18-1234567890abcdef","17-1234567890abcdef","16-1234567890abcdef","15-1234567890abcdef","14-1234567890abcdef","13-1234567890abcdef"]}';



$message = new Json($thing->uuid);
$message->setField("message0");
$message->setArray($test_array);
//$message->setJson($test_json);

for ($i = 1; $i <= 20; $i++) {

	$value1 = $message->fallingWater($i . "-" . "1234567890abcdef");
}



if($expected_json ==$message->json_data) {echo "Pass 1  \n";} else {
	echo "Fail 1  \n";
echo ' returned'; print_r($message->json_data); echo '\n';
echo ' expected'; print_r($expected_json); echo '\n';
	}
if($expected_array ==$message->array_data) {echo "Pass 2 \n";} else {
	echo "Fail 2 \n";
echo ' returned'; print_r($message->array_data); echo '\n';
echo ' expected'; print_r($expected_array); echo '\n';
}




echo "\n";
echo "Test 9: Test write to and read from field\n";

//$test_value = rand(100000,999999);

$test_array = array("a"=>"b");

$test_json = '{}';


$expected_array = array("a"=>"b");

$expected_json = '{"a":"b"}';



$message = new Json($thing->uuid);
$message->setField("message3");
$message->setArray($test_array);
//$message->setJson($test_json);

$message->write();

$message->read();

$result_json = $message->json_data;

echo $result_json;




if($message->array_data == $expected_array) {echo "Pass 1 \n";} else {
	echo "Fail 1 \n";
	echo ' returned'; print_r($message->json_data); echo '\n';
	echo ' expected'; print_r($expected_json); echo '\n';

	}

if($message->json_data == $expected_json) {echo "Pass 2 \n";} else {
	echo "Fail 2 \n";
	echo ' returned'; print_r($message->json_data); echo '\n';
	echo ' expected'; print_r($expected_json); echo '\n';

	}

echo "\nend";

exit();

$test_string = "66b23f54-890c-4038-869d-46eb8912886a";
$thing->pushJson("message0",$test_string);

$test_string = "66b23f54-890c-4038-869d-46eb8912886b";
$thing->pushJson("message0",$test_string);

$test_array_out = $thing->readJson("message0");

$expected_array = array("0" => "66b23f54-890c-4038-869d-46eb8912886a","1" => "66b23f54-890c-4038-869d-46eb8912886b");

if ($expected_array == $test_array_out) {echo "Pass<br>";} else {echo "Fail<br>";}

echo "Test 2: Push then read test key and value: ";
$test_array_in = array("test key 1"=>100);
$thing->pushJson("message1",$test_array_in);

$test_array_out = $thing->readJson("message1");
if ($test_array_in == $test_array_out) {echo "Pass<br>";} else {echo "Fail<br>";}

exit();

echo "Test 3: Push another key with same key and value to message 1<br>";
$test_array_in = array("test key 1" => 105);
$thing->pushJson("message1",$test_array_in);

$test_array_out = $thing->readJson("message1");

echo '<pre>'; print_r($test_array_out); echo '</pre>';

exit();



echo "3<br>";

$test_array = array("test key 2"=>210);
$thing->pushJson("message0",$test_array);

echo "4<br>";

$test_array = array("score"=>300);
$thing->pushJson("message0",$test_array);

$test_array = array("score"=>305);
$thing->pushJson("message0",$test_array);


$test_string = array("example uuid");
$thing->pushJson("message0",$test_string);

$test_string = array("example uuid");
$thing->pushJson("message0",$test_string);

echo "Test: Read null Json";
$test_array = $thing->readJson("message1");
if ($test_array == array()) {echo "Pass<br>";} else {echo "Fail<br>";}

echo "Test: Read Json";
$test_array = $thing->readJson("message0");

echo '<pre>'; print_r($test_array); echo '</pre>';




echo "pushJson<br>";



$test_array = $thing->pushJson("message2", $test_array);

echo '<pre>'; print_r($test_array); echo '</pre>';
























echo "End test";

echo "<br>Now try and replace test key 2<br>";


















//$test_array = array("test key 2"=>210);
//$thing->patchJson("message0",$test_array);

//$test_array = $thing->readJson("message0");

//echo '<pre>'; print_r($test_array); echo '</pre>';




//$data = json_decode($test_array);





//if ($test_string == $subject) {echo "pass<br>";} else {echo "fail<br>";}





?>
