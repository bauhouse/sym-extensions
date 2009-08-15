<?php

	Class extension_enable_tabkey extends Extension{
	
		public function about(){
			return array('name' => 'Enable Tabkey',
						 'version' => '1.0',
						 'release-date' => '2009-05-26',
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
			
			$page->addScriptToHead(URL . '/extensions/enable_tabkey/assets/enable_tabkey.js', 3066704);
			$page->addScriptToHead(URL . '/extensions/enable_tabkey/assets/tabby.js', 3066703);
		}
			
	}

?>