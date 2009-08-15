<?php
	
	require_once(EXTENSIONS . '/importmanager/lib/class.importer.php');
	
	class importQuestions_And_Answers extends Importer {
		public function __construct(&$parent) {
			parent::__construct($parent);
		}
		
		public function about() {
			return array(
				'name'			=> 'Questions and Answers',
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
			return (integer)'25';
		}
		
		public function getRootExpression() {
			return '
				/spotlights/entry
			';
		}
		
		public function canUpdate() {
			return true;
		}
		
		public function getFieldMap() {
			return array(
				'126' => 'author/text()',
				'127' => 'book/text()',
				'128' => 'content/text()',
			);
		}
		
		public function allowEditorToParse() {
			return true;
		}
	}
	
?>