<?php
	
	require_once(TOOLKIT . '/class.event.php');
	
	class EventNewvisiter_trackvisits extends Event {
		const ROOTELEMENT = 'new-visiter';
		
		protected $_driver = null;
		
		public function __construct(&$parent, $env = null) {
			parent::__construct($parent, $env);
			
			$this->_driver = $this->_Parent->ExtensionManager->create('new_visiter');
		}
		
		public static function about() {
			return array(
				'name'				=> 'New Visiter: Track Visits',
				'author'			=> array(
					'name'				=> 'Mark Lewis',
					'website'			=> 'http://casadelewis.com/',
					'email'				=> 'mark@casadelewis.com'
				),
				'version'			=> '1.0',
				'release-date'		=> '2009-04-12'
			);
		}
		
		public static function documentation() {
			return '
				<p>
					Tracks the number of visits and sets a cookie with the info on that visitor\'s computer.
				</p>
			';
		}
		
		public function load() {
			return $this->__trigger();
		}
		
		protected function __trigger() {
			$result = new XMLElement(self::ROOTELEMENT);
			
			$result->setAttribute('visits', $this->_driver->updateVisits());
			$result->setAttribute('threshold', $this->_driver->getThreshold());
			
			$result->appendChild(new XMLElement(
				'message', $this->_driver->getMessage()
			));
						
			return $result;
		}
	}
	
?>