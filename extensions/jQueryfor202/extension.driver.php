<?php

	Class extension_jquery_for_202 extends Extension{
	
		public function about(){
			return array('name' => 'jQuery for 2.0.2',
						 'version' => '1.0',
						 'release-date' => '2009-07-01',
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
				)
			);
		}

		public function initaliseAdminPageHead($context) {
			$page = $context['parent']->Page;
			
			$page->addScriptToHead(URL . '/extensions/jquery_for_202/assets/jquery.js', 3065704);
		}
			
	}

?>