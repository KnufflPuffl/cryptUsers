<?php 





	// Script will go through all Users stored in DB and create username, password (plain), password (encrypted) and salt  


	function rand_string($lng) {
			mt_srand((double)microtime()*1000000);
			$charset = "123456789ABCDEFGHIJKLMNPQRSTUVWXYZ!?<>*~#-_/\\{}[]$%+";
			$length  = strlen($charset)-1;
			$code    = '';
			for($i=0;$i<$lng;$i++) {
			  $code .= $charset{mt_rand(0, $length)};
			}
			return $code;
	}


	
	// User Helper Class
	class User
	{
		
		public $uid; 	
		public $firstname;
		public $lastname; 
		public $username; 
		public $plain; 
		public $encrypted;
		public $salt; 
		public $entry_exists;
	 
	
	
	  public function __construct($uid, $firstname, $lastname, $username, $plain, $encrypted, $salt, $entry_exists)
	  {
		  	$this->uid = $uid; 
			$this->firstname = $firstname; 
			$this->lastname = $lastname;
			$this->username = $username;
			$this->plain = $plain;
			$this->encrypted = $encrypted;
			$this->salt = $salt; 	 
			$this->entry_exists = $entry_exists;  
	  }
	  
	  
	}
	
	
	
	$users = array(); 
	
	
	// Connect to DB and read out all Users (Firstname + Lastname) 
	$db_connect = mysql_connect("mysql04.edis-lps.at", "fe001hob", "968hP4dR");
	$db_selected = mysql_select_db("fe001hob_edlseer-app", $db_connect); 
	
	
	// CHECK KEY FOR SECURITY REASONS 
	$key = mysql_real_escape_string($_GET["key"]); 
	
	if( $key != 'LEjpLMPahe67amHc2snvCbwqRvdfqfRWjHsK4Bz47UTEaX2bwGhQFPTEBfh7pEwd2JJrhvBm7znphJBqRw2UCRAyCf2UvwPfCrQAmWkDSETkJ2yGHzkdFyAVKMLpbCsm') 
	{
		echo '<br/>insufficient rights, see documentation for correct usage...<br/>'; 	
		return -1; 
		exit; 
	}
	
	
	// Create SQL-State..
	$sql_users = mysql_query('SELECT * FROM appusers10_user');
	
	
	while( $row_users = mysql_fetch_array($sql_users)) 
	{
		
		$uid = $row_users["id"]; 
		
		// Create UNIQUE-USERNAME (firstname.l) 
		$fn = trim(strtolower($row_users["firstname"]));
		$ln = trim(strtolower($row_users["lastname"])); 
		
		$uname_gen = $fn.$ln[0];  
		
		// Create a random Password 12 chars long
		$plain = bin2hex(openssl_random_pseudo_bytes(6));;
		
		// Create a random Salt 
		$salt = substr(md5(microtime()), rand(0, 10), 30);
		
		// Generate Encrpyed Password using our previously generated SALT 
		$encrypted = crypt($plain, $salt);
		
		
		$entry_exists = 0; 
		
		
		if( strlen($row_users["plaintext"]) > 0 ) 
		{
			$entry_exists = 1; 	
		}
		
		
		$temp_user = new User($uid, $row_users["firstname"],$row_users["lastname"],$uname_gen,$plain,$encrypted,$salt,$entry_exists); 
		
		array_push($users,$temp_user); 
		
	}
	
	
	
	
	
	
	// Output our entire array and update Entries in Db...
	
	for($i = 0; $i < sizeof($users); $i++) 
	{
		
		echo 'UID:'.$users[$i]->uid.'<br/>'; 
		echo 'FN:'.$users[$i]->firstname.'<br/>';
		echo 'LN:'.$users[$i]->lastname.'<br/>';
		echo 'UN:'.$users[$i]->username.'<br/>';
		echo 'PLAIN:'.$users[$i]->plain.'<br/>';
		echo 'SALT:'.$users[$i]->salt.'<br/>';
		echo 'ENCRYPTED:'.$users[$i]->encrypted.'<br/>';
		echo '<br/>'; 
		
		
		// DO A MYSQL UPDATE (ONLY IF THERE'S NO PASSWORD SET) 
				
		
		if( $users[$i]->entry_exists == 0 ) 
		{
		 
			$update_sql = mysql_query('UPDATE `appusers10_user` SET `username`="'.$users[$i]->username.'",`password`="'.$users[$i]->encrypted.'",`plaintext`="'.$users[$i]->plain.'",`salt`="'.$users[$i]->salt.'" WHERE id='.$users[$i]->uid); 
			
		}
		else 
		{
			echo '<br/>skipped update for User with ID:'.$users[$i]->uid.' because it already has a password set.<br/>';  	
		}
		
	}
	
	
	
	
	
	
	
	
	
	
	
	

?> 