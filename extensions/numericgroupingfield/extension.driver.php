<?php
	
	class Extension_NumericGroupingField extends Extension {
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function about() {
			return array(
				'name'			=> 'Field: Numeric Grouping',
				'version'		=> '1.0.2',
				'release-date'	=> '2009-03-30',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'description'	=> 'Group entries on output every nth entry.'
			);
		}
		
		public function uninstall() {
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_numericgrouping`");
		}
		
		public function install() {
			$this->_Parent->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_numericgrouping` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`field_id` int(11) unsigned NOT NULL,
					`nth_entry` tinyint default NULL,
					PRIMARY KEY  (`id`),
					KEY `field_id` (`field_id`)
				)
			");
			
			return true;
		}
	}
	
?>