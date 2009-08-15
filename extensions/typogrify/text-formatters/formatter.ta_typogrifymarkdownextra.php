<?php

	Class formatterta_typogrifymarkdownextra extends TextFormatter {

		function about(){
			return array(
			  'name' => 'Typogrify + Markdown Extra Text Formatter',
				'version' => '1.0',
				'release-date' => '2008-03-23',
				'author' => array(
				  'name' => 'Tony Arnold',
				 	'website' => 'http://www.tonyarnold.com',
				 	'email' => 'tony@tonyarnold.com'),
				'description' => 'Format entries using the Typogrify + Markdown Extra formatters'
			);
		}

		function run($string){
		  if(!function_exists('Markdown')) include_once(EXTENSIONS . '/markdown/lib/markdown_extra.php');
		  if(!function_exists('typogrify')) include_once(EXTENSIONS . '/typogrify/lib/php-typogrify.php');

			return stripslashes(typogrify(Markdown($string)));
		}

	}

?>