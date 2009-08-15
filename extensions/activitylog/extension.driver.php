<?php
	
	class Extension_ActivityLog extends Extension {
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function about() {
			return array(
				'name'			=> 'Activity Log',
				'version'		=> '1.0.1',
				'release-date'	=> '2009-05-19',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				)
			);
		}
		
		public function install() {
			return true;
		}
		
		public function uninstall() {
			return true;
		}
		
		public function fetchNavigation() {
			return array(
				array(
					'location'	=> 200,
					'name'	=> 'Activity Log',
					'link'	=> '/view/'
				)
			);
		}
		
		public function readLog() {
			$items = array();
			
			if (is_readable(MANIFEST . '/logs/main')) {
				$lines = explode("\n", file_get_contents(MANIFEST . '/logs/main'));
				
				// Skip log info:
				while (count($lines)) {
					$line = trim(array_shift($lines));
					
					if ($line == '--------------------------------------------') break;
				}
				
				// Create items:
				foreach ($lines as $line) {
					preg_match('/^([0-9]{2}:[0-9]{2}:[0-9]{2}) > (.*)/', trim($line), $matches);
					
					if (count($matches) == 3) {
						$items[] = (object)array(
							'timestamp'	=> strtotime($matches[1]),
							'message'	=> $matches[2]
						);
					}
				}
				
				// Reverse order:
				$items = array_reverse($items);
			}
			
			return $items;
		}
	}
		
?>