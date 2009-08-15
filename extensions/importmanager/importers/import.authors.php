<?php
	
	require_once(EXTENSIONS . '/importmanager/lib/class.importer.php');
	
	class importAuthors extends Importer {
		public function __construct(&$parent) {
			parent::__construct($parent);
		}
		
		public function about() {
			return array(
				'name'			=> 'Authors',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'version'		=> '1.002',
				'release-date'	=> '16 January 2009'
			);	
		}
		
		public function getSection() {
			return (integer)'12';
		}
		
		public function getRootExpression() {
			return '
				/reading-guides/reading-guide[not(author = preceding-sibling::*/author)]
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
				'57' => 'author/text()',
				'58' => 'count(preceding-sibling::*)',
				'60' => 'about-author/text()'
			);
		}
		
		public function allowEditorToParse() {
			return true;
		}
	}
	
?>