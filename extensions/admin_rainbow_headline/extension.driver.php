<?php

	Class extension_admin_rainbow_headline extends Extension{
	
		public function about(){
			return array('name' => 'Admin Rainbow Headline',
						 'version' => '1.0',
						 'release-date' => '2009-08-08',
						 'author' => array('name' => 'Nils Werner',
										   'website' => 'http://www.phoque.com/projekte/symphony',
										   'email' => 'nils.werner@gmail.com')
				 		);
		}
		
		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page'		=> '/backend/',
					'delegate'	=> 'InitaliseAdminPageHead',
					'callback'	=> 'initaliseAdminPageHead'
				),
						
				array(
					'page' => '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => 'appendPreferences'
				),	
				
				array(

					'page' => '/system/preferences/',
					'delegate' => 'Save',
					'callback' => 'savePreferences'

				),
			);
		}
                
                public function uninstall(){
                        Administration::instance()->Configuration->remove('admin_rainbow_headline');            
                        Administration::instance()->saveConfig();
                }


		public function initaliseAdminPageHead($context) {
			$page = $context['parent']->Page;
			
			if(General::Sanitize(Administration::instance()->Configuration->get('headline_color', 'admin_rainbow_headline')) != "") {
				$page->addElementToHead(new XMLElement("style", "
				body form h1, body form ul#usr { background-color: " . General::Sanitize(Administration::instance()->Configuration->get('headline_color', 'admin_rainbow_headline')) . "; }
			", array("type" => "text/css", "media" => "screen, projection")), 100012);
			}
		}
		
		public function savePreferences($context){
			
			$headline_color = trim(strtoupper($context['settings']['admin_rainbow_headline']['headline_color']));
			if(strlen($headline_color) > 0 and substr($headline_color,0,1) != "#") {
				$headline_color = "#" . $headline_color;
			}
			
			$context['settings']['admin_rainbow_headline'] = array("headline_color" => substr(trim($headline_color), 0, 7));


		}

		public function appendPreferences($context){
			
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', 'Admin Rainbow Headline'));


			$label = Widget::Label('Headline Background Color');
			$label->appendChild(Widget::Input('settings[admin_rainbow_headline][headline_color]', General::Sanitize(Administration::instance()->Configuration->get('headline_color', 'admin_rainbow_headline'))));		
			$group->appendChild($label);
			$group->appendChild(new XMLElement('p', 'This can be any RGB-Hexvalue, for example <code>#97712B</code>', array('class' => 'help')));
						
			$context['wrapper']->appendChild($group);
						
		}
			
	}

?>