<?php
	
	class Extension_SubSectionField extends Extension {
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function about() {
			return array(
				'name'			=> 'Field: Sub Section',
				'version'		=> '1.009',
				'release-date'	=> '2009-01-29',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'description'	=> 'Edit linked sections within a parent section.'
			);
		}
		
		public function uninstall() {
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_subsection`");
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_subsectionchild`");
		}
		
		public function install() {
			$this->_Parent->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_subsection` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`field_id` int(11) unsigned NOT NULL,
					`child_section_id` int(11) unsigned default NULL,
					`child_field_id` int(11) unsigned default NULL,
					`allow_multiple` enum('yes','no') default NULL,
					PRIMARY KEY (`id`),
					KEY `field_id` (`field_id`),
					KEY `child_section_id` (`child_section_id`),
					KEY `child_field_id` (`child_field_id`)
				)
			");
			
			$this->_Parent->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_subsectionchild` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`field_id` int(11) unsigned NOT NULL,
					`parent_section_id` int(11) unsigned default NULL,
					`parent_field_id` int(11) unsigned default NULL,
					PRIMARY KEY (`id`),
					KEY `field_id` (`field_id`),
					KEY `parent_section_id` (`parent_section_id`),
					KEY `parent_field_id` (`parent_field_id`)
				)
			");
			
			return true;
		}
	}
	
?>