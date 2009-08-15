<?php
	
	if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	
	class FieldAdvancedUpload extends Field {
		protected $_mimes = array();
		
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function __construct(&$parent) {
			parent::__construct($parent);
			
			$this->_name = 'Advanced Upload';
			$this->_required = true;
			$this->_mimes = array(
				'image'	=> array(
					'image/bmp',
					'image/gif',
					'image/jpg',
					'image/jpeg',
					'image/png'
				),
				'text'	=> array(
					'text/plain',
					'text/html'
				)
			);
			
			$this->set('show_column', 'yes');
			$this->set('required', 'yes');
		}
		
		public function createTable() {
			$field_id = $this->get('id');
			
			return $this->_engine->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_entries_data_{$field_id}` (
					`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`entry_id` INT(11) UNSIGNED NOT NULL,
					`name` TEXT DEFAULT NULL,
					`file` TEXT DEFAULT NULL,
					`size` INT(11) UNSIGNED NOT NULL,
					`mimetype` VARCHAR(50) NOT NULL,
					`meta` VARCHAR(255) DEFAULT NULL,
					PRIMARY KEY (`id`),
					KEY `entry_id` (`entry_id`),
					KEY `mimetype` (`mimetype`),
					FULLTEXT KEY `name` (`name`),
					FULLTEXT KEY `file` (`file`)
				)
			");
		}
		
		public function canFilter() {
			return true;
		}
		
		public function canImport() {
			return true;
		}
		
		public function isSortable() {
			return true;
		}	
		
		public function getExampleFormMarkup() {
			$handle = $this->get('element_name');
			
			$label = Widget::Label($this->get('label'));
			$label->appendChild(Widget::Input('fields[{$handle}]', null, 'file'));
			
			return $label;
		}
		
		public function entryDataCleanup($entry_id, $data) {
			$file_location = WORKSPACE . '/' . ltrim($data['file'], '/');
			
			if (file_exists($file_location)) General::deleteFile($file_location);
			
			parent::entryDataCleanup($entry_id);
			
			return true;
		}
		
	/*-------------------------------------------------------------------------
		Settings:
	-------------------------------------------------------------------------*/
		
		public function checkFields(&$errors, $checkForDuplicates = true) {
			if (!is_writable(DOCROOT . $this->get('destination') . '/')) {
				$errors['destination'] = 'Folder is not writable. Please check permissions.';
			}
			
			parent::checkFields($errors, $checkForDuplicates);
		}
		
		public function displaySettingsPanel(&$wrapper, $errors = null) {
			parent::displaySettingsPanel($wrapper, $errors);
			
			$order = $this->get('sortorder');
			
		// Destination --------------------------------------------------------
			
			$ignore = array(
				'events',
				'data-sources',
				'text-formatters',
				'pages',
				'utilities'
			);
			$directories = General::listDirStructure(WORKSPACE, true, 'asc', DOCROOT, $ignore);		   
			
			$label = Widget::Label('Destination Directory');
			
			$options = array(
				array('/workspace', false, '/workspace')
			);
			
			if (!empty($directories) and is_array($directories)) {
				foreach ($directories as $d) {
					$d = '/' . trim($d, '/');
					
					if (!in_array($d, $ignore)) {
						$options[] = array($d, ($this->get('destination') == $d), $d);
					}
				}	
			}
			
			$label->appendChild(Widget::Select(
				"fields[{$order}][destination]", $options
			));
				
			if (isset($errors['destination'])) {
				$label = Widget::wrapFormElementWithError($label, $errors['destination']);
			}
			
			$wrapper->appendChild($label);
			
		// Validator ----------------------------------------------------------
			
			$this->buildValidationSelect($wrapper, $this->get('validator'), "fields[{$order}][validator]", 'upload');
			
			$this->appendRequiredCheckbox($wrapper);
			$this->appendShowColumnCheckbox($wrapper);
		}
		
		public function commit() {
			if (!parent::commit() or $field_id === false) return false;
			
			$field_id = $this->get('id');
			$handle = $this->handle();
			
			$fields = array(
				'field_id'		=> $field_id,
				'destination'	=> $this->get('destination'),
				'validator'		=> $fields['validator'] == 'custom' ? null : $this->get('validator')
			);
			
			$this->_engine->Database->query("
				DELETE FROM
					`tbl_fields_{$handle}`
				WHERE
					`field_id` = '{$field_id}'
				LIMIT 1
			");
			
			return $this->_engine->Database->insert($fields, "tbl_fields_{$handle}");
		}
		
	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/
		
		public function displayPublishPanel(&$wrapper, $data = null, $error = null, $prefix = null, $postfix = null, $entry_id = null) {
			$this->_engine->Page->addStylesheetToHead(URL . '/extensions/advanceduploadfield/assets/form.css', 'screen');
			
			if (!$error and !is_writable(DOCROOT . $this->get('destination') . '/')) {
				$error = 'Destination folder, <code>'.$this->get('destination').'</code>, is not writable. Please check permissions.';
			}
			
			$handle = $this->get('element_name');
			
		// Image --------------------------------------------------------------
			
			$div = new XMLElement('div');
			$div->setAttribute('class', 'imageuploadfield');
			
			$label = Widget::Label($this->get('label'));
			$label->setAttribute('class', 'file');
			
			if ($this->get('required') != 'yes') {
				$label->appendChild(new XMLElement('i', 'Optional'));
			}
			
			$span = new XMLElement('span');
			
			if ($data['file']) {
				$span->appendChild(Widget::Anchor($data['name'], URL . '/workspace' . $data['file']));
			}
			
			$span->appendChild(Widget::Input(
				"fields{$prefix}[{$handle}]{$postfix}",
				$data['file'], ($data['file'] ? 'hidden' : 'file')
			));
			
			$label->appendChild($span);
			
			if ($error != null) {
				$label = Widget::wrapFormElementWithError($label, $error);
			}
			
			$div->appendChild($label);
			
		// Output -------------------------------------------------------------
			
			if ($error == null and !empty($data['file']) and in_array($data['mimetype'], $this->_mimes['image'])) {
				$output = new XMLElement('p');
				$output->setAttribute('class', 'output');
				
				$image = new XMLElement('img');
				$image->setAttribute('src', URL . '/workspace' . $data['file']);
				$image->setAttribute('width', '200');
				
				$output->appendChild($image);
				$div->appendChild($output);
			}
			
			$wrapper->appendChild($div);
		}
		
	/*-------------------------------------------------------------------------
		Input:
	-------------------------------------------------------------------------*/
		
		protected function getHashedFilename($filename) {
			preg_match('/(.*?)(\.[^\.]+)$/', $filename, $meta);
			
			$filename = sprintf(
				'%s-%s%s',
				Lang::createHandle($meta[1]),
				md5(time()), $meta[2]
			);
			
			return $filename;
		}
		
		public function checkPostFieldData($data, &$message, $entry_id = null) {
			$label = $this->get('label');
			$message = null;
			
			if (empty($data) or $data['error'] == UPLOAD_ERR_NO_FILE) {
				if ($this->get('required') == 'yes') {
					$message = "'{$label}' is a required field.";
					
					return self::__MISSING_FIELDS__;		
				}
				
				return self::__OK__;
			}
			
			// Its not an array, so just retain the current data and return
			if (!is_array($data)) return self::__OK__;
			
			if (!is_writable(DOCROOT . $this->get('destination') . '/')) {
				$message = 'Destination folder, <code>' . $this->get('destination') . '</code>, is not writable. Please check permissions.';
				
				return self::__ERROR__;
			}

			if ($data['error'] != UPLOAD_ERR_NO_FILE and $data['error'] != UPLOAD_ERR_OK) {
				switch($data['error']) {
					case UPLOAD_ERR_INI_SIZE:
						$size = (is_numeric(ini_get('upload_max_filesize')) ? General::formatFilesize(ini_get('upload_max_filesize')) : ini_get('upload_max_filesize'));
						$message = "File chosen in '{$label}' exceeds the maximum allowed upload size of {$size} specified by your host.";
						break;
						
					case UPLOAD_ERR_FORM_SIZE:
						$size = General::formatFilesize($this->_engine->Configuration->get('max_upload_size', 'admin'));
						$message = "File chosen in '{$label}' exceeds the maximum allowed upload size of {$size}, specified by Symphony.";
						break;

					case UPLOAD_ERR_PARTIAL:
						$message = "File chosen in '{$label}' was only partially uploaded due to an error.";
						break;

					case UPLOAD_ERR_NO_TMP_DIR:
						$message = "File chosen in '{$label}' was only partially uploaded due to an error.";
						break;

					case UPLOAD_ERR_CANT_WRITE:
						$message = "Uploading '{$label}' failed. Could not write temporary file to disk.";
						break;

					case UPLOAD_ERR_EXTENSION:
						$message = "Uploading '{$label}' failed. File upload stopped by extension.";
						break;
				}
				
				return self::__ERROR_CUSTOM__;
			}
			
			// Sanitize the filename:
			if (is_array($data) and isset($data['name'])) {
				$data['name'] = $this->getHashedFilename($data['name']);
			}
			
			if ($this->get('validator') != null) {
				$rule = $this->get('validator');
				
				if (!General::validateString($data['name'], $rule)) {
					$message = "File chosen in '{$label}' does not match allowable file types for that field.";
					
					return self::__INVALID_FIELDS__;
				}
			}
			
			$abs_path = DOCROOT . '/' . trim($this->get('destination'), '/');
			$new_file = $abs_path . '/' . $data['name'];
			$existing_file = null;
			
			if ($entry_id) {
				$field_id = $this->get('id');
				$row = $this->Database->fetchRow(0, "
					SELECT
						f.*
					FROM
						`tbl_entries_data_{$field_id}` AS f
					WHERE
						f.entry_id = '{$entry_id}'
					LIMIT 1
				");
				$existing_file = $abs_path . '/' . trim($row['file'], '/');
			}
			
			if (($existing_file != $new_file) and file_exists($new_file)) {
				$message = "A file with the name {$data['name']} already exists in " . $this->get('destination') . '. Please rename the file first, or choose another.';
				
				return self::__INVALID_FIELDS__;				
			}
			
			return self::__OK__;
		}
		
		public function processRawFieldData($data, &$status, $simulate = false, $entry_id = null) {
			$status = self::__OK__;
			
			// Its not an array, so just retain the current data and return
			if (!is_array($data)) {
				$status = self::__OK__;
				
				// Recal existing data:
				$current = $this->_engine->Database->fetchRow(
					0, sprintf(
						"
							SELECT
								f.name,
								f.file,
								f.size,
								f.mimetype,
								f.meta
							FROM
								`tbl_entries_data_%s` AS f
							WHERE
								f.entry_id = '%s'
								AND f.file = '%s'
							LIMIT 1
						",
						$this->get('id'), $entry_id,
						$this->cleanValue($data)
					)
				);
				
				// Existing data found:
				if (is_array($current) and count($current) == 5) {
					return $current;
					
				// Look at new file:
				} else {
					return array(
						'name'		=> basename($data),
						'file'		=> $data,
						'mimetype'	=> $this->getMimeType($data),
						'size'		=> filesize(WORKSPACE . $data),
						'meta'		=> serialize($this->getMetaInfo(WORKSPACE . $data, $this->getMimeType($data)))
					);
				}
			}
			
			if ($simulate) return;
			
			if ($data['error'] == UPLOAD_ERR_NO_FILE or $data['error'] != UPLOAD_ERR_OK) return;
			
			// Sanitize the filename:
			if (is_array($data) and isset($data['name'])) {
				$name = $data['name'];
				$data['name'] = $this->getHashedFilename($data['name']);
			}
			
			// Upload the new file:
			$abs_path = DOCROOT . '/' . trim($this->get('destination'), '/');
			$rel_path = str_replace('/workspace', '', $this->get('destination'));

			if (!General::uploadFile($abs_path, $data['name'], $data['tmp_name'], $this->_engine->Configuration->get('write_mode', 'file'))) {
				$message = "There was an error while trying to upload the file <code>{$data['name']}</code> to the target directory <code>workspace/{$rel_path}</code>.";
				$status = self::__ERROR_CUSTOM__;
				return;
			}
			
			if ($entry_id) {
				$field_id = $this->get('id');
				$row = $this->Database->fetchRow(0, "
					SELECT
						f.*
					FROM
						`tbl_entries_data_{$field_id}` AS f
					WHERE
						f.entry_id = '{$entry_id}'
					LIMIT 1
				");
				$existing_file = $abs_path . '/' . basename($row['file']);
				
				General::deleteFile($existing_file);
			}
			
			$status = self::__OK__;
			
			$file = rtrim($rel_path, '/') . '/' . trim($data['name'], '/');
			
			return array(
				'name'		=> $name,
				'file'		=> $file,
				'size'		=> $data['size'],
				'mimetype'	=> $data['type'],
				'meta'		=> serialize($this->getMetaInfo(WORKSPACE . $file, $data['type']))
			);
		}
		
		protected function getMimeType($file) {
			if (in_array('image/' . General::getExtension($file), $this->_mimes['image'])) {
				return 'image/' . General::getExtension($file);
			}
			
			return 'application/octet-stream';
		}
		
		protected function getMetaInfo($file, $type) {
			$meta = array(
				'creation'	=> DateTimeObj::get('c', filemtime($file))
			);
			
			if (in_array($type, $this->_mimes['image'])) {
				if (!$data = @getimagesize($file)) return $meta;
				
				$meta['width']	= $data[0];
				$meta['height']   = $data[1];
				$meta['type']	 = $data[2];
				$meta['channels'] = $data['channels'];
			}
			
			return $meta;
		}
		
	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/
		
		public function appendFormattedElement(&$wrapper, $data) {
			$item = new XMLElement($this->get('element_name'));
			$item->setAttributeArray(array(
				'size'	=> General::formatFilesize(filesize(WORKSPACE . $data['file'])),
				'type'	=> $data['mimetype'],
				'name'	=> General::sanitize($data['name'])
			));
			
			$item->appendChild(new XMLElement('path', str_replace(WORKSPACE, NULL, dirname(WORKSPACE . $data['file']))));
			$item->appendChild(new XMLElement('file', General::sanitize(basename($data['file']))));
			
			$meta = unserialize($data['meta']);
			
			if (is_array($meta) and !empty($meta)) {
				$item->appendChild(new XMLElement('meta', null, $meta));
			}
			
			$wrapper->appendChild($item);
		}
		
		public function prepareTableValue($data, XMLElement $link = null) {
			if (!$file = $data['file']) return null;
			
			if ($link) {
				$link->setValue($data['name']);
				
				return $link->generate();
				
			} else {
				$link = Widget::Anchor($data['name'], URL . '/workspace' . $file);
				
				return $link->generate();
			}
		}
	}
	
?>