<?php
	
	class Extension_FlexDateField extends Extension {
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public static $params = null;
		
		public function about() {
			return array(
				'name'			=> 'Field: Flex Date',
				'version'		=> '1.004',
				'release-date'	=> '2009-03-19',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'description'	=> 'A field that stores an expiry date allowing quick and easy manipulation.'
			);
		}
		
		public function uninstall() {
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_flexdate`");
		}
		
		public function install() {
			return $this->_Parent->Database->query("
				CREATE TABLE  `tbl_fields_flexdate` (
					`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`field_id` INT(11) UNSIGNED NOT NULL,
					`pre_populate` ENUM('yes','no') DEFAULT 'no' NOT NULL,
					PRIMARY KEY (`id`),
					KEY `field_id` (`field_id`)
				)
			");
		}
		
		public function buildAdmin($context) {
			$page = $context['parent']->Page;
			
			$page->addStylesheetToHead(URL . '/extensions/flexdatefield/assets/publish.css', 'screen', 100);
		}
	}
	
?>