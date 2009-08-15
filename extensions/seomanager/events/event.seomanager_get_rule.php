<?php
	
	require_once(TOOLKIT . '/class.event.php');
	
	class EventSEOManager_Get_Rule extends Event {
		const ROOTELEMENT = 'seomanager-get-rule';
		
		protected $_driver = null;
		protected $_rule = null;
		
		public function __construct(&$parent, $env = null) {
			parent::__construct($parent, $env);
			
			$this->_driver = $this->_Parent->ExtensionManager->create('seomanager');
		}
		
		public static function about() {
			return array(
				'name'				=> 'SEO Manager: Get Rule',
				'author'			=> array(
					'name'				=> 'Rowan Lewis',
					'website'			=> 'http://pixelcarnage.com/',
					'email'				=> 'rowan@pixelcarnage.com'
				),
				'version'			=> '1.001',
				'release-date'		=> '2008-12-10'
			);
		}
		
		public static function documentation() {
			return '
				<p>
					Gets the rule matching the current page, if any are found.
				</p>
			';
		}
		
		public function load() {
			$rule = $this->_driver->getCurrentRule();
			
			if (!empty($rule)) {
				$this->_rule = $rule;
				
				return $this->__trigger();
			}
			
			return false;
		}
		
		protected function __trigger() {
			$result = new XMLElement(self::ROOTELEMENT);
			
			$result->setAttribute('rule-id', $this->_rule->id);
			
			$result->appendChild(new XMLElement(
				'title', General::sanitize($this->_rule->title)
			));
			
			$result->appendChild(new XMLElement(
				'description', General::sanitize($this->_rule->description)
			));
			
			$keywords = new XMLElement('keywords');
			
			foreach ($this->_rule->keywords as $keyword) {
				$keywords->appendChild(
					new XMLElement('item', General::sanitize($keyword))
				);
			}
			
			$result->appendChild($keywords);
			
			$result->appendChild(new XMLElement(
				'expression', General::sanitize($this->_rule->expression)
			));
			
			return $result;
		}
	}
	
?>