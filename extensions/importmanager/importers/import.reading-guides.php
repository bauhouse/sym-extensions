<?php
	
	require_once(EXTENSIONS . '/importmanager/lib/class.importer.php');
	
	class importReading_Guides extends Importer {
		public function __construct(&$parent) {
			parent::__construct($parent);
		}
		
		public function about() {
			return array(
				'name'			=> 'Reading Guides',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'version'		=> '1.001',
				'release-date'	=> '13 January 2009'
			);	
		}
		
		public function getSection() {
			return (integer)'17';
		}
		
		public function getRootExpression() {
			return '/reading-guides/reading-guide';
		}
		
		public function getUniqueField() {
			return '132';
		}
		
		public function canUpdate() {
			return true;
		}
		
		public function getFieldMap() {
			return array(
				'132' => 'book/text()',
				'133' => 'author/text()',
				'135' => 'interview/text()',
				'136' => 'starting-points/text()',
				'137' => 'further-reading/text()',
				'138' => 'resources/text()'
			);
		}
		
		public function allowEditorToParse() {
			return true;
		}
	}
	
?>