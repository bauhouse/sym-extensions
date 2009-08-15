<?php
	
	class extension_version extends Extension {

		public function about() {
			return array(
				'name'			=> 'Version',
				'version'		=> '1.0',
				'release-date'	=> '2009-08-11',
				'author'		=> array(
					'name'			=> 'Stephen Bau',
					'website'		=> 'http://www.domain7.com/',
					'email'			=> 'stephen@domain7.com'
				),
				'description'	=> 'Symphony System Preference for viewing the currently installed version of Symphony'
	 		);
		}

		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => 'appendPreferences'
				)
			);
		}

		public function appendPreferences($context){
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', 'Version'));	

			$useragent = $this->_Parent->Configuration->get('useragent', 'general');
			$label = new XMLElement('label', $useragent);			
			
			$group->appendChild($label);						
			$context['wrapper']->appendChild($group);
		}
	}
	
?>