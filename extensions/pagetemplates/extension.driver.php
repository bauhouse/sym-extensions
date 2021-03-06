<?php
	
	class Extension_PageTemplates extends Extension {
	/*-------------------------------------------------------------------------
		Extension definition
	-------------------------------------------------------------------------*/
		
		public function about() {
			return array(
				'name'			=> 'Page Templates',
				'version'		=> '0.65',
				'release-date'	=> '2009-06-13',
				'author'		=> array(
					'name'			=> 'craig zheng',
					'email'			=> 'cz@mongrl.com'
				),
				'description'	=> 'Create pages from predefined templates.'
			);
		}
		
		public function fetchNavigation() {
			return array(
				array(
					'location'	=> 'Blueprints',
					'name'	=> 'Page Templates',
					'link'	=> '/manage/'
				)
			);
		}
		
		public function uninstall(){
			$this->_Parent->Database->query("DROP TABLE `tbl_pages_templates`");
		}
		
		public function install(){
			mkdir(PAGES . '/templates', 0775);
			return $this->_Parent->Database->query(
				"CREATE TABLE `tbl_pages_templates` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`parent` int(11),
					`title` varchar(255) NOT NULL default '',
					`handle` varchar(255),
					`path` varchar(255),
					`params` varchar(255),
					`data_sources` text,
					`events` text,
					`sortorder` int(11) NOT NULL default '0',
					PRIMARY KEY (`id`),
					KEY `parent` (`parent`)
				) TYPE=MyISAM;"
			);
		}
		
	}
?>
