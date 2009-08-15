<?php

	Class extension_advancedtaglist extends Extension{

		public function about(){
			return array('name' => 'Field: Advanced Taglist',
						 'version' => '1.0',
						 'release-date' => '2009-01-21',
						 'author' => array('name' => 'Alistair Kearney',
										   'website' => 'http://pointybeard.com',
										   'email' => 'alistair@pointybeard.com')
				 		);
		}
		
		public function uninstall() {
			$this->_Parent->Database->query("DROP TABLE `sym_fields_advancedtaglist`");							
		}
		
		public function install() {
			$this->_Parent->Database->query("
				CREATE TABLE `sym_fields_advancedtaglist` (
					  `id` int(11) unsigned NOT NULL auto_increment,
					  `field_id` int(11) unsigned NOT NULL,
					  `validator` varchar(100) default NULL,
					  `pre_populate_source` varchar(255) default NULL,
				  PRIMARY KEY  (`id`),
				  KEY `field_id` (`field_id`),
				  KEY `pre_populate_source` (`pre_populate_source`)
				)
			");
		}	
	}