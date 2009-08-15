<?php
	
	class Extension_DateTagListField extends Extension {
	/*-------------------------------------------------------------------------
		Definition
	-------------------------------------------------------------------------*/
		
		public function about() {
			return array(
				'name'			=> 'Field: Date Tag List',
				'version'		=> '1.002',
				'release-date'	=> '2008-12-05',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'description' => 'A new way of storing dates, merging a tag list with a date field.'
			);
		}
		
		public function uninstall() {
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_datetaglist`");
		}
		
		public function install() {
			return $this->_Parent->Database->query("
				CREATE TABLE `tbl_fields_datetaglist` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`field_id` int(11) unsigned NOT NULL,
					`pre_populate` enum('yes','no') default 'no' NOT NULL,
					PRIMARY KEY (`id`),
					KEY `field_id` (`field_id`)
				)
			");
		}
		
	/*-------------------------------------------------------------------------
		Utilities
	-------------------------------------------------------------------------*/
		
		public function getTags($field_id) {
			return $this->_Parent->Database->fetchCol('value', "
				SELECT DISTINCT
					d.value
				FROM
					`tbl_entries_data_{$field_id}` AS d
				ORDER BY
					d.value ASC
			");
		}
	}
	
?>
