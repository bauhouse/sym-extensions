<?php

	require_once(TOOLKIT . '/class.datasource.php');
	
	Class datasourceIPLocationLookup extends Datasource{
		
		public $dsParamROOTELEMENT = 'ip-location-lookup';
		
		public function about(){
			return array(
					 'name' => 'IP Location Lookup',
					 'author' => array(
							'name' => 'Alistair Kearney',
							'website' => 'http://symphony21.com',
							'email' => 'alistair@pointybeard.com'),
					 'version' => '1.0',
					 'release-date' => '2009-04-04');	
		}
		
		public function grab(&$param_pool){
			$result = new XMLElement($this->dsParamROOTELEMENT);
			
			$driver = Frontend::instance()->ExtensionManager->create('iplocation_lookup');
			
			$location = extension_IPLocation_Lookup::lookup();
			
			if(is_null($location)){
				$result->appendChild(new XMLElement('error', 'unknown location'));
			}
			else{
				$result->setValue($location);
			}
			
			return $result;
			
		}
	}

