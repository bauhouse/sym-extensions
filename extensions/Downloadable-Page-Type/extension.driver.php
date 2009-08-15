<?php

	Final Class extension_Downloadable_Page_Type extends Extension{
		
		public function about(){
			return array('name' => 'Downloadable Page Type',
						 'version' => '0.1',
						 'release-date' => '2009-06-30',
						 'author' => array('name' => 'Andrew Shooner',
										   'website' => 'http://www.andrewshooner.com',
										   'email' => 'ashooner@gmail.com')
				 		);
		}

		
		public function getSubscribedDelegates(){
			return array(
						array(
							'page' => '/frontend/',
							'delegate' => 'FrontendOutputPreGenerate',
							'callback' => 'setContentDisposition'							
						),
			);
		}
		
		public function setContentDisposition(array $context=NULL){
			$page_data = Frontend::Page()->pageData();
			
			foreach($page_data['type'] as $type){
				if(substr($type, 0, 1) == "." ){	
					$FileName = $page_data['handle'];
					Frontend::Page()->addHeaderToPage('Content-Disposition', 'attachment; filename=' . $FileName . $type);				
				}
			} 
			
		}
	}

