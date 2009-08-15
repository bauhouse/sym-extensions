<?php
	
	require_once(EXTENSIONS . '/importmanager/lib/class.importer.php');
	
	class importBooks extends Importer {
		public function __construct(&$parent) {
			parent::__construct($parent);
		}
		
		public function about() {
			return array(
				'name'			=> 'Books',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'version'		=> '1.001',
				'release-date'	=> '16 January 2009'
			);	
		}
		
		public function getSection() {
			return (integer)'14';
		}
		
		public function getRootExpression() {
			return '
				/reading-guides/reading-guide
			';
		}
		
		public function getUniqueField() {
			return '139';
		}
		
		public function canUpdate() {
			return true;
		}
		
		public function getFieldMap() {
			return array(
				'65' => 'book/text()',
				'139' => 'isbn/text()',
				'100' => 'author/text()',
				'68' => 'about-book/text()'
			);
		}
		
		public function allowEditorToParse() {
			return true;
		}
	}
	
?>