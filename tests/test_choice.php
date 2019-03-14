<?php

namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';


//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

echo "test_choice.php";





$thing = new Thing(null);
$thing->Create("null@stackr.ca", "chooser", "Make a choice");



//$thing->choice = new Choice();

echo $thing->uuid . "<br>";

$node_list = array("inside nest"=>array("nest maintenance"=>array("patrolling"=>"foraging","foraging")),"midden work"=>"foraging");



echo "Test 1: Test 'foraging'<br>";

$current_node = "foraging";
$expected_result = array(0=>'forget');
$thing->choice->Create('decision', $node_list, $current_node, 'thing');

$choices = $thing->choice->getChoices();

if(!array_diff($choices, $expected_result) && !array_diff($expected_result, $choices)) {
	echo "Pass  <br>";
	} else {
	echo "Fail  <br>";


	echo '<pre> test_choice.php $choices: '; print_r($choices); echo '</pre>';
	echo '<pre> test_choice.php $expected_result: '; print_r($expected_result); echo '</pre>';

	}



echo "Test Load<br>";

$choice_thing_uuid = $thing->choice->makeChoice("decision");

$thing->choice->loadDecision();
$thing->json->setField("variables");
var_dump($thing->json->read());

var_dump($thing->db->readField("variables"));

echo "<br><br><br>";


$thing = new Thing(null);
$thing->Create("null@stackr.ca", "chooser", "Make a choice");



$choice = new Choice($thing->uuid);

echo $thing->uuid . "<br>";

$node_list = array("inside nest"=>array("nest maintenance"=>array("patrolling"=>"foraging","foraging")),"midden work"=>"foraging");


echo "Test 2: Test 'inside nest'<br>";

$current_node = "inside nest";
$expected_result = array("forget","nest maintenance");
$choice->Create('decision',$node_list, $current_node);
$choices = $choice->getChoices();

if(!array_diff($choices, $expected_result) && !array_diff($expected_result, $choices)) {
	echo "Pass  <br>";
	} else {
	echo "Fail  <br>";


	echo '<pre> test_choice.php $choices: '; print_r($choices); echo '</pre>';
	echo '<pre> test_choice.php $expected_result: '; print_r($expected_result); echo '</pre>';

	}



echo "Test 3: Test 'nest maintenance'<br>";
$current_node = "nest maintenance";
$expected_result = array("forget","patrolling","foraging");


$choice->Create('decision',$node_list, $current_node);
$choices = $choice->getChoices();

if(!array_diff($choices, $expected_result) && !array_diff($expected_result, $choices)) {
	echo "Pass  <br>";
	} else {
	echo "Fail  <br>";


	echo '<pre> test_choice.php $choices: '; print_r($choices); echo '</pre>';
	echo '<pre> test_choice.php $expected_result: '; print_r($expected_result); echo '</pre>';

	}



echo "Test 4: Test 'patrolling'<br>";
$current_node = "patrolling";
$expected_result = array("forget","foraging");


$choice->Create('decision',$node_list, $current_node);
$choices = $choice->getChoices();

if(!array_diff($choices, $expected_result) && !array_diff($expected_result, $choices)) {
	echo "Pass  <br>";
	} else {
	echo "Fail  <br>";


	echo '<pre> test_choice.php $choices: '; print_r($choices); echo '</pre>';
	echo '<pre> test_choice.php $expected_result: '; print_r($expected_result); echo '</pre>';

	}



echo "Test 5.1: Test 'midden work'";
$current_node = "midden work";
$expected_result = array("forget","foraging");


$choice->Create('decision',$node_list, $current_node);
$choices = $choice->getChoices();

if(!array_diff($choices, $expected_result) && !array_diff($expected_result, $choices)) {
	echo "Pass  <br>";
	} else {
	echo "Fail  <br>";


	echo '<pre> test_choice.php $choices: '; print_r($choices); echo '</pre>';
	echo '<pre> test_choice.php $expected_result: '; print_r($expected_result); echo '</pre>';

	}

echo "Test 5.2: Test 'foraging'";
$current_node = "foraging";
$expected_result = array("forget");


$choice->Create('decision',$node_list, $current_node);
$choices = $choice->getChoices();

if(!array_diff($choices, $expected_result) && !array_diff($expected_result, $choices)) {
	echo "Pass  <br>";
	} else {
	echo "Fail  <br>";


	echo '<pre> test_choice.php $choices: '; print_r($choices); echo '</pre>';
	echo '<pre> test_choice.php $expected_result: '; print_r($expected_result); echo '</pre>';

	}

echo "Test X: make choice";
$current_node = "nest maintenance";
$expected_result = array("forget");


$choice->Create('decision',$node_list, $current_node);

$uuid = $choice->makeChoice("patrolling");

echo $uuid;


echo "Test X: make choices";
$current_node = "nest maintenance";
$expected_result = array();


$choice->Create('decision',$node_list, $current_node);

$list = $choice->makeChoices();

	echo '<pre> test_choice.php $uuid-choice list: '; print_r($list); echo '</pre>';





echo "Test 6: Test 'abc'<br>";

$node_list = array("a"=>array("b"=>array("c"=>"a")));

$current_node = "c";
$expected_result = array("forget","a");
$choice->Create('decision',$node_list, $current_node);
$choices = $choice->getChoices();

if(!array_diff($choices, $expected_result) && !array_diff($expected_result, $choices)) {
	echo "Pass  <br>";
	} else {
	echo "Fail  <br>";


	echo '<pre> test_choice.php $choices: '; print_r($choices); echo '</pre>';
	echo '<pre> test_choice.php $expected_result: '; print_r($expected_result); echo '</pre>';

	}

echo "Test 7: Test 'abc'<br>";

$node_list = array("a"=>array("b"=>array("c"=>"a")));

$current_node = "a";
$expected_result = array("forget","b");
$choice->Create('decision',$node_list, $current_node);
$choices = $choice->getChoices();

if(!array_diff($choices, $expected_result) && !array_diff($expected_result, $choices)) {
	echo "Pass  <br>";
	} else {
	echo "Fail  <br>";


	echo '<pre> test_choice.php $choices: '; print_r($choices); echo '</pre>';
	echo '<pre> test_choice.php $expected_result: '; print_r($expected_result); echo '</pre>';

	}

echo "Test 8: Test 'abc'<br>";

$node_list = array("a"=>array("b"=>array("c"=>"a")));

$current_node = "b";
$expected_result = array("forget","c");
$choice->Create('decision',$node_list, $current_node);
$choices = $choice->getChoices();

if(!array_diff($choices, $expected_result) && !array_diff($expected_result, $choices)) {
	echo "Pass  <br>";
	} else {
	echo "Fail  <br>";


	echo '<pre> test_choice.php $choices: '; print_r($choices); echo '</pre>';
	echo '<pre> test_choice.php $expected_result: '; print_r($expected_result); echo '</pre>';

	}


echo "Test 9: Four way choice<br>";

$node_list = array("q"=>array("a","b","c","d"));

$current_node = "q";
$expected_result = array("forget","a","b","c","d");
$choice->Create('decision',$node_list, $current_node);
$choices = $choice->getChoices();

if(!array_diff($choices, $expected_result) && !array_diff($expected_result, $choices)) {
	echo "Pass  <br>";
	} else {
	echo "Fail  <br>";


	echo '<pre> test_choice.php $choices: '; print_r($choices); echo '</pre>';
	echo '<pre> test_choice.php $expected_result: '; print_r($expected_result); echo '</pre>';

	}

echo "Test 10.1: Chain with options<br>";

$node_list = array("q"=>array("a"=>array("b"=>array("c"=>array("d","d_mcguffin"), "c_mcguffin","d"), "b_mcguffin"),"a_mcguffin"),"q_mcguffin");

$current_node = "q";
$expected_result = array("forget","a","a_mcguffin");
$choice->Create('decision',$node_list, $current_node);
$choices = $choice->getChoices();

if(!array_diff($choices, $expected_result) && !array_diff($expected_result, $choices)) {
	echo "Pass  <br>";
	} else {
	echo "Fail  <br>";


	echo '<pre> test_choice.php $choices: '; print_r($choices); echo '</pre>';
	echo '<pre> test_choice.php $expected_result: '; print_r($expected_result); echo '</pre>';

	}

echo "Test 10.2: Chain with options<br>";


$current_node = "c";
$expected_result = array("forget","d","d_mcguffin");
$choice->Create('decision',$node_list, $current_node);
$choices = $choice->getChoices();

if(!array_diff($choices, $expected_result) && !array_diff($expected_result, $choices)) {
	echo "Pass  <br>";
	} else {
	echo "Fail  <br>";


	echo '<pre> test_choice.php $choices: '; print_r($choices); echo '</pre>';
	echo '<pre> test_choice.php $expected_result: '; print_r($expected_result); echo '</pre>';

	}

echo "Test 10.3: Chain with options<br>";


$current_node = "b";
$expected_result = array("forget","c","c_mcguffin","d");
$choice->Create('decision',$node_list, $current_node);
$choices = $choice->getChoices();

if(!array_diff($choices, $expected_result) && !array_diff($expected_result, $choices)) {
	echo "Pass  <br>";
	} else {
	echo "Fail  <br>";


	echo '<pre> test_choice.php $choices: '; print_r($choices); echo '</pre>';
	echo '<pre> test_choice.php $expected_result: '; print_r($expected_result); echo '</pre>';

	}

echo "Test 10.4: Chain with options (without full array definition)<br>";

$node_list = array("a"=>array("b"=>array("c"=>"d"),"d"),"e"=>"d");


$current_node = "b";
$expected_result = array("forget","c");
$choice->Create('decision',$node_list, $current_node);
$choices = $choice->getChoices();

if(!array_diff($choices, $expected_result) && !array_diff($expected_result, $choices)) {
	echo "Pass  <br>";
	} else {
	echo "Fail  <br>";


	echo '<pre> test_choice.php $choices: '; print_r($choices); echo '</pre>';
	echo '<pre> test_choice.php $expected_result: '; print_r($expected_result); echo '</pre>';

	}

echo "Test 10.5: Chain with options (without full array definition)<br>";

$node_list = array("a"=>array("b"=>array("c"=>"d"),"d"),"e"=>"d");


$current_node = "a";
$expected_result = array("forget","b","d");
$choice->Create('decision',$node_list, $current_node);
$choices = $choice->getChoices();

if(!array_diff($choices, $expected_result) && !array_diff($expected_result, $choices)) {
	echo "Pass  <br>";
	} else {
	echo "Fail  <br>";


	echo '<pre> test_choice.php $choices: '; print_r($choices); echo '</pre>';
	echo '<pre> test_choice.php $expected_result: '; print_r($expected_result); echo '</pre>';

	}







?>
