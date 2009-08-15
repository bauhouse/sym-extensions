<?php

	Class formatterta_typogrifytextile extends TextFormatter {

		function about(){
			return array(
			  'name' => 'Typogrify + Textile Text Formatter',
				'version' => '1.0',
				'release-date' => '2008-03-23',
				'author' => array(
				  'name' => 'Tony Arnold',
				 	'website' => 'http://www.tonyarnold.com',
				 	'email' => 'tony@tonyarnold.com'),
				'description' => 'Format entries using the Typogrify + Textile formatters'
			);
		}

		function run($string){
		  if(!function_exists('typogrify')) include_once(EXTENSIONS . '/typogrify/lib/php-typogrify.php');
		  if(!class_exists('Textile')) include_once(EXTENSIONS . '/textile/lib/textile.php');
			$textile = new Textile;
			return stripslashes(typogrify($textile->TextileThis($string)));
		}

	}

?>