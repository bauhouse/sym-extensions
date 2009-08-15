<?php

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

	Class extension_new_visiter extends Extension{
	
		public function about(){
			return array('name' => 'New Visiter',
						 'version' => '1.0',
						 'release-date' => '2009-04-12',
						 'author' => array('name' => 'Mark Lewis',
										   'website' => 'http://www.casadelewis.com',
										   'email' => 'mark@casadelewis.com'),
						 'description' => 'Use to differentiate between new and repeat visitors.'
				 		);
		}
				
		public function uninstall() {
			$this->_Parent->Configuration->remove('newvisiter');
			$this->_Parent->saveConfig();
		}
		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page'		=> '/system/preferences/',
					'delegate'	=> 'AddCustomPreferenceFieldsets',
					'callback'	=> 'addCustomPreferenceFieldsets'
				)
			);
		}
		
	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/
		
		public function getMessage() {
			return $this->_Parent->Configuration->get('message', 'newvisiter');
		}
		
		public function getThreshold() {
			return $this->_Parent->Configuration->get('threshold', 'newvisiter');
		}
		
		public function updateVisits() {
			if (isset($_COOKIE['newvisiter_visits']))
			{
				$newvisits = $_COOKIE['newvisiter_visits'] + 1;
			}
			else
			{
				$newvisits = 1;
			}

			setcookie('newvisiter_visits', $newvisits, time()+60*60*24*365);
			
			return $newvisits;
		}
		
	/*-------------------------------------------------------------------------
		Delegates:
	-------------------------------------------------------------------------*/
		
		public function addCustomPreferenceFieldsets($context) {
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(
				new XMLElement('legend', 'New Visiter')
			);
			
			$message = Widget::Label('Message');
			$message->appendChild(Widget::Textarea('settings[newvisiter][message]', '5', '25', General::Sanitize($this->getMessage())));
			$group->appendChild($message);
			
			$thresholds = array(1,2,3,4,5,6,7,8,9,10);
			$options = array();
			
			foreach($thresholds as $t){
				$options[] = array($t, $t == $this->_Parent->Configuration->get('threshold', 'newvisiter'), $t);
			}
			
			$threshold = Widget::Label('Threshold');
			$threshold->appendChild(Widget::Select('settings[newvisiter][threshold]', $options));
			$group->appendChild($threshold);

			$context['wrapper']->appendChild($group);			
		}
	}

?>