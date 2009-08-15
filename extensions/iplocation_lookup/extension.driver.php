<?php

	Class extension_IPLocation_Lookup extends Extension{
	
		public function about(){
			return array('name' => 'IP Location Lookup',
						 'version' => '1.0',
						 'release-date' => '2008-05-04',
						 'author' => array('name' => 'Alistair Kearney',
										   'website' => 'http://www.symphony21.com',
										   'email' => 'alistair@symphony21.com')
				 		);
		}		
		
		public static function lookup($ip=NULL){
			require_once('lib/class.iplookup.php');
			
			if(is_null($ip)) $ip = $_SERVER['REMOTE_ADDR'];

			return IPLookup::lookup($ip);
			
		}
		
	}

