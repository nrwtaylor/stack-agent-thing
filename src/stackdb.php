<?php



//ini_set('display_startup_errors', 1);
//ini_set('display_errors', 1);
//error_reporting(-1);

require '/var/www/html/stackr.ca/vendor/autoload.php';

ini_set("allow_url_fopen", 1);





class stackDatabase {
       	
	public $var = 'hello';


    function __construct() {




		// create container and configure it
		$settings = require 'settings.php';
		$this->container = new \Slim\Container($settings);
		// create app instance
		$app = new \Slim\App($this->container);
		$this->container = $app->getContainer();
		$this->test= "hhhh";
		$this->container['db'] = function ($c) {
			$db = $c['settings']['db'];
			$pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
				$db['user'], $db['pass']);
			//$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			return $pdo;
			};





		$this->container['stack'] = function ($c) {
			$db = $c['settings']['stack'];
			return $db;
			};



		//echo $this->char_max;


		// Remove this code.  Only Thing.php should be assigning new
		// uuids.  Keep that function in one place.






		// Which means at this point, we have a UUID
		// whether or not the record exists is another question.

		// But we don't need to find, it because the UUID is randomly created.	
		// Chance of collision super-super-small.

		// So just return the contents of thing.  false if it doesn't exist.

		//return $this->Get();


		}


	function agent($agent_search, $keyword) {

		$query = "SELECT * FROM stack WHERE nom_to LIKE '%$agent_search%' AND task LIKE '%$keyword%' ORDER BY created_at DESC";


		$sth = $this->container->db->prepare($query);

		$sth->execute();
		$things = $sth->fetchAll();

		//var_dump($thing); // for testing

		$thingreport = array('thing' => $things, 'info' => 'So here are three things you put on the stack.  That\'s what you wanted.','help' => 'It is up to you what you do with these.');



		return $thingreport;
		}


	function userRecords($user_search, $limit = null) {

		if ($limit == null) {$limit = 3;}

		$query = "SELECT * FROM stack WHERE nom_from LIKE '%$user_search%' ORDER BY created_at DESC LIMIT $limit";


		$sth = $this->container->db->prepare($query);

		$sth->execute();
		$things = $sth->fetchAll();

		if ($things == array()) {$things = false;}

		//var_dump($thing); // for testing

		$thingreport = array('thing' => $things, 'info' => 'So here are three things you put on the stack.  That\'s what you wanted.','help' => 'It is up to you what you do with these.');



		return $thingreport;
		}













	function keyword() {


		$sth = $this->container->db->prepare("SELECT * FROM stack WHERE nom_to NOT LIKE '%@%' ORDER BY RAND( ) LIMIT 1");


		//$sth->bindParam("nom_from", $nom_from);
		$sth->execute();
		$thing = $sth->fetchObject();

		//var_dump($thing); // for testing

		$this->uuid = $thing->uuid;

		$this->to = $thing->nom_to;
		$this->from = $thing->nom_from;
		$this->subject = $thing->task;

		$thingreport = array('thing' => $thing, 'info' => 'So here are three things you put on the stack.  That\'s what you wanted.','help' => 'It is up to you what you do with these.');



		return $thingreport;
		}



	function byWordlist(Array $words) {

		$word_list = implode("|", $words);

		//echo $word_list;

		$query = "SELECT * FROM stack
			WHERE variables REGEXP '$word_list'
			ORDER BY RAND()
			";

		$sth = $this->container->db->prepare($query);


		//$sth->bindParam("nom_from", $nom_from);
		$sth->execute();
		//$thing = $sth->fetchObject();
		$things = $sth->fetchAll();

		//var_dump($things);

		//var_dump($thing); // for testing

		//$this->uuid = $thing->uuid;

		//$this->to = $thing->nom_to;
		//$this->from = $thing->nom_from;
		//$this->subject = $thing->task;

		$thingreport = array('thing' => $things, 'info' => 'So here are Things matching at least one of the words provided. That\'s what you wanted.','help' => 'It is up to you what you do with these.', 'whatisthis' => 'A list of Things which match at least one keyword.');



		return $thingreport;
		}

	function byPhrase($phrase){

		$query = "SELECT * FROM stack
			WHERE variables LIKE '%$phrase%'
			ORDER BY RAND()
			";

		$sth = $this->container->db->prepare($query);
		$sth->execute();
		$things = $sth->fetchAll();


		$thingreport = array('thing' => $things, 'info' => 'So here are Things with the phrase you provided in \$variables. That\'s what you wanted.','help' => 'It is up to you what you do with these.', 'whatisthis' => 'A list of Things which match at the provided phrase.');



		return $thingreport;
		}



	function excludeWordlist(Array $words) {
//http://www.sqltrainingonline.com/sql-not-like-with-multiple-values/
		//echo $word_list;
		
		//echo $words;

		// SELECT * FROM stack WHERE not (variables like '%dispatch%' or variables like '%iching%' or variables like '%credit%');
		$query = 'SELECT * FROM stack WHERE not (';


		$flag = false;
		foreach ($words as $word) {
			if ($flag == true) {$query .= ' OR ';}
			$query .= "variables LIKE '%$word%'";
			$flag = true;
		}
		$query .= ')';

		$query .= " AND not (variables LIKE '%{\"status\":\"green\"}%')";



		//echo "Query: " .$query . '<br>';

		$sth = $this->container->db->prepare($query);


		//$sth->bindParam("nom_from", $nom_from);
		$sth->execute();
		//$thing = $sth->fetchObject();
		$things = $sth->fetchAll();

		//var_dump($things);

		//var_dump($thing); // for testing

		//$this->uuid = $thing->uuid;

		//$this->to = $thing->nom_to;
		//$this->from = $thing->nom_from;
		//$this->subject = $thing->task;

		$thingreport = array('thing' => $things, 'info' => 'So here are Things matching at least one of the words provided. That\'s what you wanted.','help' => 'It is up to you what you do with these.', 'whatisthis' => 'A list of Things which match at least one keyword.');



		return $thingreport;
		}

	function getRed() {
//http://www.sqltrainingonline.com/sql-not-like-with-multiple-values/
		//echo $word_list;
		
		//echo $words;

		// SELECT * FROM stack WHERE not (variables like '%dispatch%' or variables like '%iching%' or variables like '%credit%');

		$search_term = "'%{\"status\":\"red\"}%'";


		$query = "SELECT * FROM stack WHERE variables LIKE " . $search_term;


		$sth = $this->container->db->prepare($query);


		//$sth->bindParam("nom_from", $nom_from);
		$sth->execute();
		//$thing = $sth->fetchObject();
		$things = $sth->fetchAll();

		//var_dump($things);

		//var_dump($thing); // for testing

		//$this->uuid = $thing->uuid;

		//$this->to = $thing->nom_to;
		//$this->from = $thing->nom_from;
		//$this->subject = $thing->task;

		$thingreport = array('thing' => $things, 'info' => 'So here are Things which are flagged red.','help' => 'It is up to you what you do with these.', 'whatisthis' => 'A list of Things which have status red.');



		return $thingreport;
		}



	function getNew() 
    {

		$query = "SELECT * FROM stack WHERE variables is NULL";

		$sth = $this->container->db->prepare($query);
		$sth->execute();
		$things = $sth->fetchAll();

		$thingreport = array('thing' => $things, 'info' => 'So here are Things which are flagged red.','help' => 'It is up to you what you do with these.', 'whatisthis' => 'A list of Things which have status red.');

		return $thingreport;
    }

	function deleteNullID() {
		$query = "DELETE FROM stack WHERE uuid is NULL OR uuid = ''";
		//echo $query;

		$sth = $this->container->db->prepare($query);
		$sth->execute();
		//$things = $sth->fetchAll();

		$thingreport = array('thing' => false, 'info' => 'So all UUIDs with null IDs were deleted.','help' => 'Should have tidied things up.', 'whatisthis' => 'Confirmation items with null ID were deleted.');



		return $thingreport;
		}




	function connections() {

		// NOT IMPLEMENTED

//http://www.sqltrainingonline.com/sql-not-like-with-multiple-values/
		//echo $word_list;



		// SELECT * FROM stack WHERE not (variables like '%dispatch%' or variables like '%iching%' or variables like '%credit%');
		$query = "SHOW STATUS WHERE `variable_name` = 'Threads_connected'";

		$sth = $this->container->db->prepare($query);


		//$sth->bindParam("nom_from", $nom_from);
		$sth->execute();
		//$thing = $sth->fetchObject();
		$response = $sth->fetchAll();

//echo '<pre> db.php $response: '; print_r($response); echo '</pre>';

//		var_dump($response);
		$keys = array_keys($response);
		//echo $keys;
		//echo $response[$keys[0]];

		//var_dump($things);

		//var_dump($thing); // for testing

		//$this->uuid = $thing->uuid;

		//$this->to = $thing->nom_to;
		//$this->from = $thing->nom_from;
		//$this->subject = $thing->task;

		$thingreport = array('thing' => false, 'db' => $response, 'info' => 'So here are Things matching at least one of the words provided. That\'s what you wanted.','help' => 'It is up to you what you do with these.', 'whatisthis' => 'A list of Things which match at least one keyword.');

		//$thingreport = false;

		return $thingreport;
		}


	function random() {


		$sth = $this->container->db->prepare("SELECT * FROM stack ORDER BY RAND() LIMIT 1");
		//$sth->bindParam("nom_from", $nom_from);
		$sth->execute();
		$thing = $sth->fetchObject();

		//var_dump($thing); // for testing

		$this->to = $thing->nom_to;
		$this->from = $thing->nom_from;
		$this->subject = $thing->task;

		$thingreport = array('things' => $thing, 'info' => 'So here are three things you put on the stack.  That\'s what you wanted.','help' => 'It is up to you what you do with these.');



		return $thingreport;
		}


	function randomN($nom_from, $n=3) {
		//echo $this->test;

		//echo $this->uuid;

		//$sth = $this->container->db->prepare("SELECT * FROM stack WHERE uuid=:uuid");
		//$sth->bindParam("uuid", $this->uuid);
		//$sth->execute();
		//$query = $sth->fetchObject();

//echo '<pre> test_db.php $thing->uuid: '; print_r($query); echo '</pre>';

		//$nom_from = $query->nom_from;
		//echo $nom_from;

		//$name = $uuid;

		$sth = $this->container->db->prepare("SELECT * FROM stack WHERE nom_from=:nom_from ORDER BY RAND() LIMIT 3");
		$sth->bindParam("nom_from", $nom_from);
		$sth->execute();
		$things = $sth->fetchAll();

		$thingreport = array('thing' => $things, 'info' => 'So here are three things you put on the stack.  That\'s what you wanted.','help' => 'It is up to you what you do with these.');



		return $thingreport;
		}


	}











?>
