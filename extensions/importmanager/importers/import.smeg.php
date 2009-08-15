<?php
	
	require_once(EXTENSIONS . '/importmanager/lib/class.importer.php');
	
	class importSmeg extends Importer {
		/* FIELDS */
		
		/* NODES */
		
		public function __construct(&$parent) {
			parent::__construct($parent);
		}
		
		public function about() {
			return array(
				'name'			=> 'Smeg',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> '/* AUTHOR_WEBSITE */',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'version'		=> '1.005',
				'release-date'	=> '/* RELEASE_DATE */'
			);	
		}
		
		public function getSource() {
			return '/* SOURCE */';
		}
		
		public function allowEditorToParse() {
			return true;
		}
	}
	
?>