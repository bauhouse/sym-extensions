<?php

	Class formatterta_typogrify extends TextFormatter {
		
		function about(){
			return array(
			  'name' => 'Typogrify Text Formatter',
				'version' => '1.0',
				'release-date' => '2008-03-23',
				'author' => array(
				  'name' => 'Tony Arnold',
				 	'website' => 'http://www.tonyarnold.com',
				 	'email' => 'tony@tonyarnold.com'),
				'description' => 'Format entries using the Typogrify formatter'
			);
		}	
		
		function run($string){
			if(!function_exists('typogrify'))  include_once(EXTENSIONS . '/typogrify/lib/php-typogrify.php');
			return stripslashes(typogrify($string)); 
		}
		
	}

?>