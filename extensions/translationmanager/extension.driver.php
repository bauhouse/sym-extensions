<?php
	Class extension_translationmanager extends Extension{
	
		public function about(){
			return array('name' => __('Translation Manager'),
						 'version' => '0.9',
						 'release-date' => '2008-12-24',
						 'author' => array('name' => 'Marcin Konicki',
										   'website' => 'http://ahwayakchih.neoni.net',
										   'email' => 'ahwayakchih@neoni.net'),
						 'description' => __('Import and export translation files, check state of translations.')
			);
		}

		public function fetchNavigation() {
			return array(
				array(
					'location'	=> 200,
					'name'		=> __('Translations'),
					'limit'		=> 'developer',
				)
			);
		}

		// Experimental (and probably temporary) solution for translating navigation and buttons
		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/administration/',
					'delegate' => 'NavigationPreRender',
					'callback' => '__translateNavigation'
				),
				array(
					'page' => '/backend/',
					'delegate' => 'AppendElementBelowView',
					'callback' => '__translateButtons'
				),
			);
		}

		public function __translateNavigation($ctx) {
			if (!is_array($ctx['navigation'])) return;

			for ($i = 0; $i < count($ctx['navigation']); $i++) {
				$ctx['navigation'][$i]['name'] = @__($ctx['navigation'][$i]['name']);
				if (is_array($ctx['navigation'][$i]['children'])) {
					for ($c = 0; $c < count($ctx['navigation'][$i]['children']); $c++) {
						$ctx['navigation'][$i]['children'][$c]['name'] = @__($ctx['navigation'][$i]['children'][$c]['name']);
					}
				}
			}
		}

		public function __translateButtons() {
			if ($this->_Parent->Configuration->get('lang', 'symphony') == 'en') return true;

			if (!function_exists('imagecreatefrompng') || !function_exists('imagettftext')) return false;
			if (!file_exists(EXTENSIONS.'/translationmanager/assets/corners.png') || !file_exists(EXTENSIONS.'/translationmanager/assets/silkscreen.ttf')) return false;

			$css = '';
			foreach ($this->_Parent->Page->_head as $e) {
				if ($e->getAttribute('type') === 'text/css') {
					$temp = $e->getValue();

					if (empty($temp)) {
						$file = $e->getAttribute('href');
						if (empty($file)) continue;
						$css .= $this->getCSS(str_replace(URL, DOCROOT, $file), '');
					}
					else {
						$css .= $this->getCSS(NULL, $temp);
					}
				}
			}

			$buttons = array(
				'images/create.png' => __('create new'),
				'images/delete.png' => __('delete'),
				'images/configure.png' => __('configure'),
				'images/help.png' => __('help'),
			);

			// Calculate ID, to prevent rewriting rules every time
			$cssFile = 'tm_'.md5(implode(', ', $buttons).$css).'.css';
			if (file_exists(CACHE."/$cssFile") && strlen($override = file_get_contents(CACHE."/$cssFile")) > 0) {
					$style = new XMLElement('style', $override);
					$style->setAttribute('type', 'text/css');
					$this->_Parent->Page->addElementToHead($style, 9999);
					return true;
			}

			if (preg_match_all('/[^\{\}]+\{[^\}]+\surl\(([^\)]+)\)[^\}]+\}/', $css, $rules)) {
				$override = '';
				$files = array();
				for ($i = 0; $i < count($rules[1]); $i++) {
					if ($rules[1][$i]{0} == '"' || $rules[1][$i]{0} == "'") $rules[1][$i] = trim($rules[1][$i], $rules[1][$i]{0});
					if (!in_array($rules[1][$i], array_keys($buttons))) continue;

					$file = 'tm_'.md5($buttons[$rules[1][$i]]).'.png';
					if (!isset($files[$file])) {
						$files[$file] = true;
						if (($w = $this->generateButton(CACHE."/$file", $buttons[$rules[1][$i]]))) {
							// Delete button uses width
							if (strpos($rules[0][$i], 'width:') !== false) {
								$rules[0][$i] = preg_replace('/width\: (\d+)px;/', "width: {$w}px;", $rules[0][$i]);
							}
							// Rest of buttons uses padding-left to set width							
							else if (strpos($rules[0][$i], 'padding-left:') !== false) {
								$rules[0][$i] = preg_replace('/padding\-left\: (\d+)px;/', "padding-left: {$w}px;", $rules[0][$i]);
							}
							$override .= str_replace($rules[1][$i], URL."/manifest/cache/$file", $rules[0][$i]);
						}
					}
				}

				if (strlen(trim($override)) > 0) {
					file_put_contents(CACHE."/$cssFile", $override);

					$style = new XMLElement('style', $override);
					$style->setAttribute('type', 'text/css');
					$this->_Parent->Page->addElementToHead($style, 9999);			
				}
			}

			return true;
		}

		private function getCSS($path, $value = '') {
			if (empty($path) && empty($value)) return '';

			if (!empty($path)) {
				$value .= file_get_contents($path);
			}

			if (preg_match_all('/\@import url\(([\'"]|)([^\)\\1]+)\\1\);/', $value, $matches)) {
				if (empty($path)) $path = DOCROOT."/symphony/assets";
				foreach ($matches[2] as $file) {
					$value .= $this->getCSS(dirname($path)."/$file", NULL);
				}
			}

			return $value;
		}

		private function generateButton($path, $text) {
			if (file_exists($path)) {
				$info = getimagesize($img);
				if (is_array($info)) return $info[0];
			}

			$font = EXTENSIONS.'/translationmanager/assets/silkscreen.ttf';
			$fontSize = 6.5;
			$corners = @imagecreatefrompng(EXTENSIONS.'/translationmanager/assets/corners.png');

			if (!$corners) return false;

			$bbox = @imagettfbbox($fontSize, 0, $font, $text);
			$tw = ceil($bbox[2] - $bbox[0]);
			$th = ceil($bbox[3] - $bbox[5]);

			$width = max($tw, 45) + 13;
			$img = @imagecreatetruecolor($width, 15);
			if (!$img) {
				@imagedestroy($corners);
				return false;
			}

			@imagesavealpha($img, true);
			@imagealphablending($img, true);

			$foreground = @imagecolorallocate($img, 255, 255, 255);
    		$background = @imagecolorallocatealpha($img, 0, 0, 0, 127);

		    @imagefill($img, 0, 0, $background);
			@imagecopy($img, $corners, 0, 0, 0, 0, 7, 15);
			@imagecopy($img, $corners, ($width - 6), 0, 7, 0, 7, 15);
			@imagettftext($img, $fontSize, 0, floor((floatval($width) / 2.0) - (floatval($tw) / 2.0)), floor(7.5 + (floatval($th) / 2.0)), $foreground, $font, $text);

			$result = false;
			if (@imagepng($img, $path)) $result = $width;
			@imagedestroy($img);
			@imagedestroy($corners);

			return $result;
		}
	}

