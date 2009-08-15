<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(EXTENSIONS . '/translationmanager/lib/class.translationmanager.php');

	Class contentExtensionTranslationManagerEdit extends AdministrationPage{
		private $_tm;

		function __construct(&$parent){
			parent::__construct($parent);

			$this->_tm = new TranslationManager($parent);
		}

		function view(){
			$language = array();

			$isCurrent = false;
			if ($this->_context[0]) {
				$language = $this->_tm->get($this->_context[0]);
				if ($this->_context[0] == $this->_Parent->Configuration->get('lang', 'symphony')) $isCurrent = true;
			}

			$fields = $_POST['fields'];

			$name = ($fields['name'] ? $fields['name'] : ($language['about']['name'] ? $language['about']['name'] : __('Untitled')));
			$this->setPageType('table');
			$this->setPageType('form');
			$this->setTitle(__('%1$s &ndash; %2$s &ndash; %3$s', array(__('Symphony'), __('Language'), $name)));
			$this->appendSubheading($name);

			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', __('Essentials')));

			$div = new XMLElement('div');
			$div->setAttribute('class', (($this->_context[0] && !$isCurrent) ? 'triple group' : 'group'));

			$label = Widget::Label(__('Name'));
			$label->appendChild(new XMLElement('i', __('Example: "English"')));
			$label->appendChild(Widget::Input('fields[name]', $name));
			$div->appendChild((isset($this->_errors['name']) ? $this->wrapFormElementWithError($label, $this->_errors['name']) : $label));

			$label = Widget::Label(__('Code'));
			$label->appendChild(new XMLElement('i', __('Example: "en"')));
			$label->appendChild(Widget::Input('fields[code]', ($this->_context[0] ? $this->_context[0] : $fields['code']), 'text'));
			$div->appendChild((isset($this->_errors['code']) ? $this->wrapFormElementWithError($label, $this->_errors['code']) : $label));

			if ($this->_context[0] && !$isCurrent) {
				$label = Widget::Label(__('Current'));
				$label->appendChild(new XMLElement('i', __('"There can be only one!"')));
				$label->appendChild(Widget::Select('fields[current]', array(array('yes', $isCurrent, __('Yes')), array('no', !$isCurrent, __('No')))));
				$div->appendChild($label);
			}

			$fieldset->appendChild($div);

			if (!$this->_context[0]) {
				$options = array(array('symphony', (is_array($fields['extensions']) ? in_array('symphony', $fields['extensions']): true), __('Symphony')));
				foreach ($this->_Parent->ExtensionManager->listAll() as $extension => $about) {
					$options[] = array($extension, in_array($extension, $fields['extensions']), $about['name']);
				}
				$label = Widget::Label(__('Includes translations of'));
				$label->appendChild(Widget::Select('fields[extensions][]', $options, array('multiple' => 'multiple')));
				$fieldset->appendChild((isset($this->_errors['extensions']) ? $this->wrapFormElementWithError($label, $this->_errors['extensions']) : $label));
			}

			$this->Form->appendChild($fieldset);

			if ($this->_context[0]) {
				$link = new XMLElement('link');
				$link->setAttributeArray(array('rel' => 'stylesheet', 'type' => 'text/css', 'media' => 'screen', 'href' => URL.'/extensions/translationmanager/assets/admin.css'));
				$this->addElementToHead($link, 500);
				$this->addScriptToHead(URL . '/extensions/translationmanager/assets/admin.js', 501);

				$fieldset = new XMLElement('fieldset');
				$fieldset->setAttribute('class', 'settings');
				$fieldset->appendChild(new XMLElement('legend', __('Deleting')));
				$fieldset->appendChild(new XMLElement('p', __('Selected translation files will be deleted'), array('class' => 'help')));

				$aTableHead = array(
					array(__('Name'), 'col'),
					array(__('Translated'), 'col'),
					array(__('Obsolete'), 'col'),
					array(__('Parser warnings'), 'col'),
				);

				$extensions = $this->_tm->listExtensions($this->_context[0]);

				$allextensions = $this->_Parent->ExtensionManager->listAll();
				$allextensions['symphony']['name'] = __('Symphony');

				$aTableBody = array();

				if(!is_array($extensions) || empty($extensions)){
					$aTableBody = array(Widget::TableRow(array(Widget::TableData(__('None Found.'), 'inactive', NULL, count($aTableHead)))));
				}
				else {
					foreach ($extensions as $extension) {
						$default = $this->_tm->defaultDictionary($extension);
						$warnings = array_shift($default);
						$language = $this->_tm->get($this->_context[0], $extension);
						$translated = array_intersect_key(array_filter($language['dictionary'], 'trim'), $default);
						$obsolete = array_diff_key($language['dictionary'], $default);
						$percent = floor(count($translated) / count($default) * 100);
						$td1 = Widget::TableData(Widget::Anchor($allextensions[$extension]['name'], URL."/symphony/extension/translationmanager/export/{$this->_context[0]}/$extension/", __('Download template')));
						$td2 = Widget::TableData((string)count($translated).'/'.(string)count($default).' <small>('.$percent.'%)</small>');
						$td3 = Widget::TableData((string)count($obsolete));
						$td4 = Widget::TableData((string)count($warnings));

						if (!$isCurrent || $extension != 'symphony') $td1->appendChild(Widget::Input('delete['.$extension.']', NULL, 'checkbox'));

						## Add a row to the body array, assigning each cell to the row
						$aTableBody[] = Widget::TableRow(array($td1, $td2, $td3, $td4));
					}

					$aTableBody2 = array();
					foreach (array_diff(array_keys($allextensions), $extensions) as $extension) {
						$default = $this->_tm->defaultDictionary($extension);
						$warnings = array_shift($default);
						$language = $this->_tm->get($this->_context[0], $extension);
						$translated = array_intersect_key(array_filter($language['dictionary'], 'trim'), $default);
						$obsolete = array_diff_key($language['dictionary'], $default);

						$percent = floor(count($translated) / count($default) * 100);
						$td1 = Widget::TableData(Widget::Anchor($allextensions[$extension]['name'], URL."/symphony/extension/translationmanager/export/{$this->_context[0]}/$extension/", __('Download template')));
						$td2 = Widget::TableData((string)count($translated).'/'.(string)count($default).' <small>('.$percent.'%)</small>');
						$td3 = Widget::TableData((string)count($obsolete));
						$td4 = Widget::TableData((string)count($warnings));

						$td1->appendChild(Widget::Input('create['.$extension.']', NULL, 'checkbox'));

						## Add a row to the body array, assigning each cell to the row
						$aTableBody2[] = Widget::TableRow(array($td1, $td2, $td3, $td4), 'inactive');
					}
				}

				$table = Widget::Table(Widget::TableHead($aTableHead), NULL, Widget::TableBody($aTableBody), 'selectable translations deleting'.($isCurrent ? ' current' : ''));
				$fieldset->appendChild($table);
				$this->Form->appendChild($fieldset);

				if (count($aTableBody2) > 0) {
					$fieldset = new XMLElement('fieldset');
					$fieldset->setAttribute('class', 'settings');
					$fieldset->appendChild(new XMLElement('legend', __('Creating')));
					$fieldset->appendChild(new XMLElement('p', __('Selected translation files will be created'), array('class' => 'help')));

					$table = Widget::Table(Widget::TableHead($aTableHead), NULL, Widget::TableBody($aTableBody2), 'selectable translations');
					$fieldset->appendChild($table);
					$this->Form->appendChild($fieldset);
				}
			}

			$div = new XMLElement('div');
			$div->setAttribute('class', 'actions');
			$div->appendChild(Widget::Input('action[save]', ($this->_context[0] ? __('Save Changes') : __('Create translation')), 'submit', array('accesskey' => 's')));
			
			if($this->_context[0] && !$isCurrent){
				$button = new XMLElement('button', __('Delete'));
				$button->setAttributeArray(array('name' => 'action[delete]', 'class' => 'confirm delete', 'title' => __('Delete this translation')));
				$div->appendChild($button);
			}

			$this->Form->appendChild($div);
		}

		function action() {
			if (array_key_exists('save', $_POST['action'])) $this->save();
			else if (array_key_exists('delete', $_POST['action'])) $this->delete();
		}

		function save() {
			$isCurrent = ($this->_context[0] == $this->_Parent->Configuration->get('lang', 'symphony'));

			$name = trim($_POST['fields']['name']);
			if (strlen($name) < 1) {
				$this->_errors['name'] = __('You have to specify name for translation.');
				return;
			}

			$code = strtolower(trim($_POST['fields']['code']));
			if (strlen($code) < 1) {
				$this->_errors['code'] = __('You have to specify code for translation.');
				return;
			}

			if (!$this->_context[0] && (!is_array($_POST['fields']['extensions']) || empty($_POST['fields']['extensions']))) {
				$this->_errors['extensions'] = __('You have to select at least Symphony or one of the extensions.');
				return;
			}

			if ($this->_context[0] != $code && $isCurrent) {
				$this->_errors['code'] = __('Cannot change code of translation which is in use. Change language used by symphony and try again.');
				return;
			}

			$queueForDeletion = NULL;
			$translations = $this->_tm->listAll();
			if (!$this->_context[0] || $this->_context[0] != $code) {
				if (!empty($translations) && is_array($translations[$code])) $this->_errors['code'] = __('Translation <code>%s</code> already exists.', array($code));
				else $queueForDeletion = $this->_context[0];
			}

			if (!empty($this->_errors)){
				return;
			}

			$extensions = ($this->_context[0] ? $this->_tm->listExtensions($this->_context[0]) : $_POST['fields']['extensions']);
			if (is_array($_POST['delete']) && count($_POST['delete']) > 0) {
				if (count($_POST['delete']) >= count($extensions) && (!is_array($_POST['create']) || empty($_POST['create']))) return $this->delete();

				$deleted = array();
				foreach ($_POST['delete'] as $extension => $v) {
					if (in_array($extension, $extensions) && $v == 'on') {
						if ($this->_tm->remove($this->_context[0], $extension)) $deleted[] = $extension;
					}
				}
				$extensions = array_diff($extensions, $deleted);
			}

			if (is_array($_POST['create']) && count($_POST['create']) > 0) {
				foreach ($_POST['create'] as $extension => $v) {
					if ($v == 'on') $extensions[] = $extension;
				}
			}

			// If nothing is changed (name and code are the same, no import, no create, etc...) we can return earlier - no need to overwrite files every time :).
			$noNeedToWrite = false;
			if (empty($extensions) || (
					$this->_context[0] == $code &&
					(!is_array($_POST['create']) || empty($_POST['create']))
				)
			) {
				$noNeedToWrite = true;
			}

			$written = array();
			foreach ($extensions as $extension) {
				$translation = array();

				if ($this->_context[0]) $translation = $this->_tm->get($this->_context[0], $extension);

				if ($noNeedToWrite && $translation['about']['name'] == $name) continue;

				$translation['about']['name'] = $name;
				if ($this->_tm->set($code, $translation, $extension)) $written[] = $extension;
			}

			if ($noNeedToWrite && empty($written) && $_POST['fields']['current'] != 'yes') {
				if (empty($deleted)) $this->pageAlert(__('There was nothing to write.'), Alert::NOTICE);
				return;
			}

			if (!$noNeedToWrite && count($written) != count($extensions)) {
				$missing = array_diff($extensions, $written);

				if (count($missing) > 1) $last = __(' and ').array_pop($missing);
				$this->pageAlert(__('Failed to write translation files for <code>%s</code>. Please check permissions.', array(implode(',', $missing).$last)), Alert::NOTICE);
			}

			if (!$noNeedToWrite && empty($written)) return;

			$deleted = array();
			if ($queueForDeletion && !$this->_tm->remove($this->_context[0])) {
				$this->pageAlert(__('Failed to delete translation <code>%s</code>. Please check file permissions.', array($this->_context[0])), Alert::NOTICE);
			}

			if ($_POST['fields']['current'] == 'yes' && in_array('symphony', $extensions)) $this->_tm->enable($code);

			if ($this->_context[0] != $code) redirect(URL . '/symphony/extension/translationmanager/edit/'.$code.'/');
		}

		function delete() {
			if ($this->_context[0] == $this->_Parent->Configuration->get('lang', 'symphony'))
				$this->pageAlert(__('Cannot delete language in use. Please change language used by Symphony and try again.'), Alert::ERROR);
			else if (!$this->_tm->remove($this->_context[0]))
				$this->pageAlert(__('Failed to delete translation <code>%s</code>. Please check file permissions or if it is not in use.', array($this->_context[0])), Alert::ERROR);
			else
				redirect(URL . '/symphony/extension/translationmanager/');
		}

	}

