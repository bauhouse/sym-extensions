<?php
	
	class EventFMM_Status extends Event {
		public static function about() {
			return array(
				'name'				=> 'Frontend Member Manager: Status',
				'author'			=> array(
					'name'				=> 'Rowan Lewis',
					'website'			=> 'http://www.pixelcarnage.com/',
					'email'				=> 'rowan@pixelcarnage.com'
				),
				'version'			=> '1.0',
				'release-date'		=> '2009-02-03'
			);
		}
		
		public function load() {
			return $this->__trigger();
		}
		
		protected function __trigger() {
			$driver = $this->_Parent->ExtensionManager->create('frontendmembermanager');
			return $driver->actionStatus();
		}
	}
	
?>