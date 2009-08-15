<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(EXTENSIONS . '/translationmanager/lib/class.translationmanager.php');

	Class contentExtensionTranslationManagerIndex extends AdministrationPage{
		private $_tm;

		function __construct(&$parent){
			parent::__construct($parent);

			$this->_tm = new TranslationManager($parent);
		}

		function view(){
			$this->setPageType('table');
			$this->setTitle(__('%1$s &ndash; %2$s', array(__('Symphony'), __('Translation Manager'))));
			$this->appendSubheading(__('Languages'), Widget::Anchor(__('Create New'), $this->_Parent->getCurrentPageURL().'edit/', __('Create new translation'), 'create button'));

			$link = new XMLElement('link');
			$link->setAttributeArray(array('rel' => 'stylesheet', 'type' => 'text/css', 'media' => 'screen', 'href' => URL.'/extensions/translationmanager/assets/admin.css'));
			$this->addElementToHead($link, 500);
			$this->addScriptToHead(URL . '/extensions/translationmanager/assets/admin.js', 501);

			$default = $this->_tm->defaultDictionary();
			$translations = $this->_tm->listAll();
			$allextensions = $this->_Parent->ExtensionManager->listAll();
			$current = $this->_Parent->Configuration->get('lang', 'symphony');

			$warnings = array_shift($default);

			$allnames = array('symphony' => __('Symphony'));
			foreach ($allextensions as $extension => $about) {
				$allnames[$extension] = $about['name'];
			}

			$aTableHead = array(
				array(__('Name'), 'col'),
				array(__('Code'), 'col'),
				array(__('Extensions*'), 'col', array('title' => __('Out of %s (including Symphony)', array(count($allextensions)+1)))),
				array(__('Translated*'), 'col', array('title' => __('Out of %1$s (with %2$s parser warnings)', array(count($default), (count($warnings) > 0 ? count($warnings) : __('no')))))),
				array(__('Obsolete'), 'col'),
				array(__('Current'), 'col'),
			);

			$aTableBody = array();

			if(!is_array($translations) || empty($translations)){
				$aTableBody = array(Widget::TableRow(array(Widget::TableData(__('None Found.'), 'inactive', NULL, count($aTableHead)))));
			}
			else {
				foreach ($translations as $lang => $extensions) {
					$language = $this->_tm->get($lang);
					$translated = array_intersect_key(array_filter($language['dictionary'], 'trim'), $default);
					$obsolete = array_diff_key($language['dictionary'], $default);

					$names = array_intersect_key($allnames, array_fill_keys($extensions, true));

					if (!$language['about']['name']) $language['about']['name'] = $lang;

					$td1 = Widget::TableData(Widget::Anchor($language['about']['name'], $this->_Parent->getCurrentPageURL().'edit/'.$lang.'/', $lang));
					$td2 = Widget::TableData($lang);
					$td3 = Widget::TableData((string)count($extensions), NULL, NULL, NULL, array('title' => implode(', ', $names)));
					$td4 = Widget::TableData(count($translated).' <small>('.floor((count($translated) / count($default)) * 100).'%)</small>');
					$td5 = Widget::TableData((string)count($obsolete));
					$td6 = Widget::TableData(($lang == $current ? __('Yes') : __('No')));

					$td1->appendChild(Widget::Input('item', $lang, 'radio'));

					## Add a row to the body array, assigning each cell to the row
					$aTableBody[] = Widget::TableRow(array($td1, $td2, $td3, $td4, $td5, $td6), 'single'.($lang == $this->_Parent->Configuration->get('lang', 'symphony') ? ' current' : ''));
				}
			}

			$table = Widget::Table(Widget::TableHead($aTableHead), NULL, Widget::TableBody($aTableBody), 'languages');
			$this->Form->appendChild($table);

			$div = new XMLElement('div');
			$div->setAttribute('class', 'actions');

			$options = array(
				array(NULL, false, __('With Selected...')),
				array('delete', false, __('Delete')),
				array('switch', false, __('Make it current')),
				array('export', false, __('Export ZIP')),
			);

			$div->appendChild(Widget::Select('with-selected', $options));
			$div->appendChild(Widget::Input('action[apply]', __('Apply'), 'submit'));

			$this->Form->appendChild($div);
		}

		function action() {
			if (!$_POST['action']['apply'] || !$_POST['item']) return;

			switch ($_POST['with-selected']) {
				case 'delete':
					$this->delete($_POST['item']);
					break;
				case 'switch':
					if ($this->_tm->enable($_POST['item'])) {
						redirect(URL . '/symphony/extension/translationmanager/');
					}
				case 'export':
					$this->exportZIP($_POST['item']);
					break;
			}
		}

		function delete($lang) {
			if ($lang == $this->_Parent->Configuration->get('lang', 'symphony'))
				$this->pageAlert(__('Cannot delete language in use. Please change language used by Symphony and try again.'), Alert::ERROR);
			else if (!$this->_tm->remove($lang))
				$this->pageAlert(__('Failed to delete translation <code>%s</code>. Please check file permissions or if it is not in use.', array($lang)), Alert::ERROR);
		}

		function exportZIP($lang) {
			require_once(TOOLKIT.'/class.archivezip.php');

			$zip = new ArchiveZip();
			foreach ($this->_tm->listExtensions($lang) as $extension) {
				$path = TranslationManager::filePath($lang, $extension);
				if (!$zip->addFromFile($path, str_replace(DOCROOT, '', $path))) {
					$this->pageAlert(__('Cannot add <code>%s</code> to ZIP file. Please check file permissions.', array($path)), Alert::ERROR);
					return false;
				}
			}

			$data = $zip->save();

			if (!$data) {
				$this->pageAlert(__('Cannot generate ZIP data.'), Alert::ERROR);
				return false;
			}

			header('Content-Type: application/zip; charset=utf-8');
			header('Content-Disposition: attachment; filename="symphony-language-'.$lang.'.zip"');
			header("Content-Description: File Transfer");
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
			echo $data;
			exit();
		}
	}

