<?php

	Class extension_hashfield extends Extension{
	
		public function about(){
			return array('name' => 'Field: Hash',
						 'version' => '1.0',
						 'release-date' => '2008-04-13',
						 'author' => array('name' => 'Symphony Team',
										   'website' => 'http://www.symphony21.com',
										   'email' => 'team@symphony21.com')
				 		);
		}
		
		public function uninstall(){
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_hash`");
		}


		public function install(){

			return $this->_Parent->Database->query("CREATE TABLE `tbl_fields_hash` (
			  `id` int(11) unsigned NOT NULL auto_increment,
			  `field_id` int(11) unsigned NOT NULL,
			  PRIMARY KEY  (`id`),
			  UNIQUE KEY `field_id` (`field_id`)
			) TYPE=MyISAM");

		}
			
	}

?>