<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(EXTENSIONS . '/translationmanager/lib/class.translationmanager.php');

	Class contentExtensionTranslationManagerExport extends AdministrationPage{
		private $_tm;

		function __construct(&$parent){
			parent::__construct($parent);

			$this->_tm = new TranslationManager($parent);
		}

		function view(){
			$lang = $this->_context[0];
			$extension = $this->_context[1];
			$isSymphony = (empty($extension) || $extension == 'symphony');

			if (strlen($lang) < 1) {
				$this->setPageType('form');
				$this->pageAlert(__('Language code is required.'), Alert::ERROR);
				return;
			}

			$default = array(
				'about' => array(),
				'dictionary' => $this->_tm->defaultDictionary($extension),
				'transliterations' => array(),
			);

			$warnings = array_shift($default['dictionary']);

			$translation = $this->_tm->get($lang, $extension);
			if (empty($translation)) {
				$translation = array(
					'about' => array('name' => $lang),
					'dictionary' => array(),
					'transliterations' => array(),
				);
				// Try to find language name in other translations for selected language
				$others = $this->_tm->listExtensions($lang);
				foreach ($others as $t_ext) {
					$t_trans = $this->_tm->get($lang, $t_ext);
					if (!empty($t_trans['about']['name'])) {
						$translation['about']['name'] = $t_trans['about']['name'];
						break;
					}
				}
			}

			$dictionary = array();
			$translated = array_intersect_key(array_filter($translation['dictionary'], 'trim'), $default['dictionary']);
			$missing = array_diff_key($default['dictionary'], $translated);
			$obsolete = array_diff_key($translation['dictionary'], $default['dictionary']);
			if (is_array($translated) && count($translated) > 0) {
				$dictionary['%%%%%%TRANSLATED%%%%%%'] = '%%%%%%TRANSLATED%%%%%%';
				$dictionary += $translated;
			}
			if (is_array($missing) && count($missing) > 0) {
				$dictionary['%%%%%%MISSING%%%%%%'] = '%%%%%%MISSING%%%%%%';
				$dictionary += array_fill_keys(array_keys($missing), false);
			}
			if (is_array($obsolete) && count($obsolete) > 0) {
				$dictionary['%%%%%%OBSOLETE%%%%%%'] = '%%%%%%OBSOLETE%%%%%%';
				$dictionary += $obsolete;
			}

			if ((!is_array($translation['transliterations']) || empty($translation['transliterations'])) && $isSymphony) {
				$translation['transliterations'] = TranslationManager::defaultTransliterations();
			}

			if ($isSymphony) $name = 'Symphony';
			else {
				$temp = $this->_Parent->ExtensionManager->about($extension);
				$name = $temp['name'];
			}
			$path = ($isSymphony ? TranslationManager::filePath($lang, 'symphony') : TranslationManager::filePath($lang, $extension));
			$file = basename($path);
			$path = str_replace(DOCROOT, '', dirname($path));
			$php = '<'."?php\n\n";
			$php .= <<<END
/*
	This is translation file for $name.
	To make it available for Symphony installation, rename it $file and upload to $path/ directory on server.
*/

END;
			$php .= '$about = '.var_export($translation['about'], true).";\n\n";
			$php .= <<<END
/*
	Dictionary array contains translations of texts (labels, guidelines, titles, etc...) used by Symphony.

	There are 3 states of translations:
	- Translated: already used by Symphony,
	- Missing: there was no translation available at the time of generating this template file,
	- Obsolete: translated text which is no longer used by Symphony and can be removed from translation file.

	To add missing translations, simply scroll down to part of array marked as "// MISSING"
	and change each "false" value into something like "'New translation of original text'"
	(notice single quotes around translated text! Text has to be wrapped either by them or by double quotes).
	So instead of something like:

		'Original text' =>
		false,

	You'll have something like:

		'Original text' =>
		'Tekst oryginału',

	You should leave all parts of text which look like "%s" or "%1\$s" (usually there is "s" or "d" character there).
	They are placeholders for other text or HTML which will be put in their place when needed by Symphony.
	You can move them around inside translated text, but not remove them. For example:

		'Original %s is here' =>
		'Tu jest oryginalny %s'

	Placeholders with numbers inside them are used when there are more than one of them inside original text.
	You can switch their positions if needed, but do not change numbers into something else.
	For example text used in page titles looks like "Symphony - Language - English" and is generated with:

		'%1\$s &ndash; %2\$s &ndash; %3\$s'

	To make titles look like "Language: English | Symphony" simply move placeholders around:

		'%2\$s: %3\$s | %1\$s'
*/

END;
			$php .= '$dictionary = '.str_replace(' => ', " =>\n  ", str_replace(",\n", ",\n\n", preg_replace('/\n\s+\'%%%%%%(TRANSLATED|MISSING|OBSOLETE)%%%%%%\'\s+=>\s+\'%%%%%%(\1)%%%%%%\',\n/', "\n// \\1\n", var_export($dictionary, true)))).";\n\n";
			$php .= <<<END
/*
	Transliterations are used to generate handles of entry fields and filenames.
	They specify which characters (or bunch of characters) have to be changed, and what should be put into their place when entry or file is saved.
	For example:

		'/_and_/' => '+',

	will change every instance of "_and_" into "+", so:

		me_and_family.jpg

	will turn into:

		me+family.jpg

	Please notice slashes at the beginning and end of original text. They are required there.
	You can change them into different character, but that character cannot be used inside original text or has to be escaped by backslash, like this:

		'/original\/path/' => 'new/path',

	You can use full syntax of regular expressions there too. Read more about it on: http://php.net/manual/en/regexp.reference.php

	Transliterations are required only inside translations of Symphony. They are not needed for extensions.
*/

END;
			$php .= '$transliterations = '.var_export($translation['transliterations'], true).";\n\n";
			$php .= '?>';

			if (!empty($php)) {
				header('Content-Type: application/x-php; charset=utf-8');
				header('Content-Disposition: attachment; filename="'.$extension.'-lang.'.$lang.'.php"');
				header("Content-Description: File Transfer");
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
				echo trim($php);
				exit();
			}
		}
	}

