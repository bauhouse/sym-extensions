<?php
	
	require_once(EXTENSIONS . '/curlinputfield/lib/curl.php');
	
	class Extension_CurlInputField extends Extension {
		public function about() {
			return array(
				'name'			=> 'Field: Curl Input',
				'version'		=> '1.0.4',
				'release-date'	=> '2009-03-30',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'description' => 'A field that validates and tests URLs with curl.'
			);
		}
		
		public function uninstall() {
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_curlinput`");
		}
		
		public function install() {
			return $this->_Parent->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_curlinput` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`field_id` int(11) unsigned NOT NULL,
					`valid_status` varchar(255) default NULL,
					`valid_type` varchar(255) default NULL,
					PRIMARY KEY (`id`),
					KEY `field_id` (`field_id`)
				)
			");
		}
	}
	
?>
