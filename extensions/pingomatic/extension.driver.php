<?php

	Class Extension_pingomatic extends Extension{
	
		public function about(){
			return array('name' => 'Ping-o-Matic',
						 'version' => '1.1',
						 'release-date' => '2009-06-11',
						 'author' => array('name' => 'Symphony Team',
										   'website' => 'http://www.symphony21.com',
										   'email' => 'team@symphony21.com')
				 		);
		}

		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/publish/',
					'delegate' => 'Delete',
					'callback' => 'notify'
				),
				array(
					'page' => '/publish/new/',
					'delegate' => 'EntryPostCreate',
					'callback' => 'notify'
				),
				array(
					'page' => '/publish/edit/',
					'delegate' => 'EntryPostEdit',
					'callback' => 'notify'
				)
			);
		}
		
		function notify($context){
			
			var_dump($context);
			
			include_once(TOOLKIT . '/class.gateway.php');
           	$ch = new Gateway;
            
            $ch->init();
            $ch->setopt('URL', 'http://rpc.pingomatic.com/');
            $ch->setopt('POST', 1);
            $ch->setopt('CONTENTTYPE', 'text/xml');
            $ch->setopt('HTTPVERSION', '1.0');
            
            ##Create the XML request
            $xml = new XMLElement('methodCall');
            $xml->appendChild(new XMLElement('methodName', 'weblogUpdates.ping'));
            
            $params = new XMLElement('params');
            
            $param = new XMLElement('param');       
            $param->appendChild(new XMLElement('value', $this->_Parent->Configuration->get('sitename', 'general')));
            $params->appendChild($param);            

            $param = new XMLElement('param');
            $param->appendChild(new XMLElement('value', URL));
            $params->appendChild($param);    
            
            $xml->appendChild($params);        
			####
			
            $ch->setopt('POSTFIELDS', $xml->generate(true, 0));

			//Attempt the ping
            $ch->exec(GATEWAY_FORCE_SOCKET);
         
		}
		
	}