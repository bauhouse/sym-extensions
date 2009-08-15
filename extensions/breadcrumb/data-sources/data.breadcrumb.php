<?php

	require_once(TOOLKIT . '/class.datasource.php');
	require_once(EXTENSIONS . '/asdc/lib/class.asdc.php');
	
	Class datasourceBreadcrumb extends Datasource{
		
		public $dsParamROOTELEMENT = 'breadcrumb';
		
		private $_database;
		
		public function about(){
			return array(
					 'name' => 'Breadcrumb',
					 'author' => array(
							'name' => 'Alistair Kearney',
							'website' => 'http://pointybeard.com',
							'email' => 'alistair@symphony21.com'),
					 'version' => '1.0',
					 'release-date' => '2009-01-02');	
		}

		public function grab(&$param_pool){
			$result = new XMLElement($this->dsParamROOTELEMENT);
			
			$current_page_id = (int)$this->_env['param']['current-page-id'];
			
			$db = ASDCLoader::instance();
			
			try{
				$results = $db->query("SELECT * FROM `tbl_pages` WHERE `id` = '{$current_page_id}' LIMIT 1");
			}
			catch(Exception $e){
				$result->appendChild(new XMLElement('error', General::sanitize(vsprintf('%d: %s on query "%s"', $db->lastError()))));
				return $result;
			}
			
			while($results->length() > 0){
				
				$current = $results->current();
				
				$result->prependChild(
					new XMLElement('page', $current->title, array('path' => trim("{$current->path}/{$current->handle}", '/')))
				);
				
				if(is_null($current->parent)) break;

				$results = $db->query(sprintf("SELECT * FROM `tbl_pages` WHERE `id` = '%d' LIMIT 1", (int)$current->parent));
				
			}

			return $result;
		}
	}

