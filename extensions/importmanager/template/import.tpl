<?php
	
	require_once(EXTENSIONS . '/importmanager/lib/class.importer.php');
	
	class import/* CLASS_NAME */ extends Importer {
		/* FIELDS */
		
		/* NODES */
		
		public function __construct(&$parent, $env = null, $process_params = true) {
			parent::__construct($parent, $env, $process_params);
		}
		
		public function about() {
			return array(
				'name'			=> '/* NAME */',
				'author'		=> array(
					'name'			=> '/* AUTHOR_NAME */',
					'website'		=> '/* AUTHOR_WEBSITE */',
					'email'			=> '/* AUTHOR_EMAIL */'
				),
				'version'		=> '/* VERSION */',
				'release-date'	=> '/* RELEASE_DATE */'
			);	
		}
		
		public function getSource() {
			return '/* SOURCE */';
		}
		
		public function allowEditorToParse() {
			return true;
		}
		
		public function import() {
			$result = new XMLElement($this->dsParamROOTELEMENT);
			
			try{
				/* GRAB */
				
			} catch (Exception $e) {
				$result->appendChild(new XMLElement('error', $e->getMessage()));
				
				return $result;
			}	
			
			if ($this->_force_empty_result) $result = $this->emptyXMLSet();
			
			/* EXTRAS */
			
			return $result;
		}
	}
	
?>