<?php
	
	require_once(EXTENSIONS . '/importmanager/lib/class.importer.php');
	
	class importAll_Authors extends Importer {
		public function __construct(&$parent) {
			parent::__construct($parent);
		}
		
		public function about() {
			return array(
				'name'			=> 'All Authors',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'version'		=> '1.002',
				'release-date'	=> '20 January 2009'
			);	
		}
		
		public function getSection() {
			return (integer)'12';
		}
		
		public function getRootExpression() {
			return '
				/data/authors/entry
			';
		}
		
		public function getUniqueField() {
			return '57';
		}
		
		public function canUpdate() {
			return true;
		}
		
		public function getFieldMap() {
			return array(
				'57' => 'title/text()',
				'58' => 'biblio-id/text()',
				'60' => 'biography/text()'
			);
		}
		
		public function allowEditorToParse() {
			return true;
		}
	}
	
?>