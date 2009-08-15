<?php

	require_once(EXTENSIONS . '/translationmanager/lib/class.translationmanager.php');

	Class datasourceXLIFF extends Datasource{
		public $dsParamFILTERS = array(
			'lang' => '{$tm-lang:$url-tm-lang}',
			'extension' => '{$tm-extension:$url-tm-extension}'
		);
		private $_tm;

		function __construct(&$parent, $env=NULL, $process_params=true){
			$this->dsParamFILTERS = $this->__getParamFilters();
			$this->_tm = new TranslationManager($parent);

			parent::__construct($parent, $env, $process_params);
		}

		private function __getParamFilters() {
			global $settings;

			return array(
				'lang' => ($settings['xliff']['lang'] ? $settings['xliff']['lang'] : '{$tm-lang:$url-tm-lang}'),
				'extension' => ($settings['xliff']['extension'] ? $settings['xliff']['extension'] : '{$tm-extension:$url-tm-extension}'),
			);
		}

		function example(){
			return '
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
  <file original="lang.pl.php" source-language="en" target-language="pl" datatype="x-symphony" xml:space="preserve" product-name="symphony" date="2008-12-25" category="Polski">
		<header>
			<tool tool-id="tm" tool-name="Symphony - Translation Manager" tool-version="1.0"/>
			<phase-group>
				<phase phase-name="Name Family" phase-process="translation" tool-id="tm" date="2008-12-27" contact-name="Name Family" contact-email="name.family@example.com" company-name="http://www.example.com"/>
			</phase-group>
		</header>
		<body>
			<group resname="dictionary">
				<trans-unit id="61d51482355486e5d7f0ce896e9a018c">
					<source>A database error occurred while attempting to reorder.</source>
					<target state="new"/>
				</trans-unit>
				<trans-unit id="f36f8cfde93067c8ca6e9852adbf656e">
					<source>%1$s &amp;ndash; %2$s</source>
					<target state="new"/>
				</trans-unit>
			</group>
			<group resname="transliterations">
				<trans-unit id="64da14124a545382825d701b618580d8">
					<source>/À/</source>
					<target state="translated">A</target>
				</trans-unit>
				<trans-unit id="2787fa2df880d1b02f791b18771ee1bb">
					<source>/Á/</source>
					<target state="translated">A</target>
				</trans-unit>
			</group>
		</body>
	</file>
</xliff>		
';
		}

		function about(){
			$params = array();
			foreach (datasourceXLIFF::__getParamFilters() as $k => $v) {
				if (strpos($v, '{$') !== false) $params[] = $v.' // '.$k;
				else $params[] = $v;
			}
			return array(
				'name' => 'XLIFF',
				'description' => __('Returns translation data in <a href="%s">XLIFF</a> format.', array('http://docs.oasis-open.org/xliff/v1.2/os/xliff-core.html')),
				'author' => array(
					'name' => 'Marcin Konicki',
					'website' => 'http://ahwayakchih.neoni.net',
					'email' => 'ahwayakchih@neoni.net'
				),
				'version' => '1.0',
				'release-date' => '2008-12-27',
				'recognised-url-param' => $params,
			);
		}

		function grab($param=array()){
			$lang = trim($this->dsParamFILTERS['lang']);
			$extension = trim($this->dsParamFILTERS['extension']);

			$xliff = new XMLElement('xliff');
			//$xliff->setIncludeHeader(true);
			$xliff->setAttribute('version', '1.2');
			// TODO: for some reason Symphony throws XSLT build error when i try to xsl:copy-of xliff with xmlns attribute set :(
			//$xliff->setAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:1.2');

			if (strlen($lang) < 1) {
				$xliff->appendChild(new XMLElement('error', __('Language code is required.')));
				return $xliff;
			}

			// No extension name given, export all available translations
			if (strlen($extension) < 1) {
				foreach ($this->_tm->listExtensions($lang) as $extension) {
					$this->toXLIFF($lang, $extension, $xliff);
				}
			}
			// Extension name exists in list
			else if (in_array($extension, $this->_tm->listExtensions($lang))) {
				$this->toXLIFF($lang, $extension, $xliff);
			}
			// Invalid extension name, export everything merged into one huge translation
			else {
				$this->toXLIFF($lang, NULL, $xliff);
			}

			return $xliff;
		}

		private function toXLIFF($lang, $extension, &$xliff) {

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

			$file = new XMLElement('file');
			$file->setAttribute('original', basename(TranslationManager::filePath($lang, $extension)));
			$file->setAttribute('source-language', 'en');
			$file->setAttribute('target-language', $lang);
			$file->setAttribute('datatype', 'x-symphony');
			$file->setAttribute('xml:space', 'preserve');
			if ($extension) $file->setAttribute('product-name', $extension);
			if (is_array($temp = $this->_Parent->ExtensionManager->about($extension))) $file->setAttribute('product-version', $temp['version']);
			// TODO: Make sure that date is specified in valid format (http://docs.oasis-open.org/xliff/v1.2/os/xliff-core.html#date)?
			if ($translation['about']['release-date']) $file->setAttribute('date', $translation['about']['release-date']);
			if ($translation['about']['name']) $file->setAttribute('category', $translation['about']['name']);

			$header = new XMLElement('header');

			$tool = new XMLElement('tool');
			$tool->setAttribute('tool-id', 'tm');
			$tool->setAttribute('tool-name', 'Symphony - Translation Manager');
			$tool->setAttribute('tool-version', '1.0');
			$header->appendChild($tool);

			if (is_array($translation['about']['author'])) {
				$group = new XMLElement('phase-group');
				$appended = 0;
				if (is_string($translation['about']['author']['name'])) {
					$temp = $translation['about']['author'];
					$translation['about']['author'][$temp['name']] = $temp;
				}
				foreach ($translation['about']['author'] as $name => $author) {
					if (!$author['name']) continue;
					$phase = new XMLElement('phase');
					$phase->setAttribute('phase-name', $author['name']);
					$phase->setAttribute('phase-process', 'translation');
					$phase->setAttribute('tool-id', 'tm');
					if ($author['release-date']) $phase->setAttribute('date', $author['release-date']);
					if ($author['name']) $phase->setAttribute('contact-name', $author['name']);
					if ($author['email']) $phase->setAttribute('contact-email', $author['email']);
					if ($author['website']) $phase->setAttribute('company-name', $author['website']);
					$group->appendChild($phase);
					$appended++;
				}
				if ($appended) $header->appendChild($group);
			}

			$body = new XMLElement('body');

			$group = new XMLElement('group');
			$group->setAttribute('resname', 'dictionary');

			$sklDictionary = array();

			$translated = array_intersect_key(array_filter($translation['dictionary'], 'trim'), $default['dictionary']);
			foreach ($translated as $k => $v) {
				$sklDictionary[$k] = md5($k);
				$unit = new XMLElement('trans-unit');
				$unit->setAttribute('id', $sklDictionary[$k]);
				$unit->appendChild(new XMLElement('source', General::sanitize($k)));
				$unit->appendChild(new XMLElement('target', General::sanitize($v), array('state' => 'translated')));
				$group->appendChild($unit);
			}
			
			$missing = array_diff_key($default['dictionary'], $translated);
			foreach ($missing as $k => $v) {
				$sklDictionary[$k] = md5($k);
				$unit = new XMLElement('trans-unit');
				$unit->setAttribute('id', $sklDictionary[$k]);
				$unit->appendChild(new XMLElement('source', General::sanitize($k)));
				$unit->appendChild(new XMLElement('target', '', array('state' => 'new')));
				$group->appendChild($unit);
			}

			$obsolete = array_diff_key($translation['dictionary'], $default['dictionary']);
			foreach ($obsolete as $k => $v) {
				$sklDictionary[$k] = md5($k);
				$unit = new XMLElement('trans-unit');
				$unit->setAttribute('id', $sklDictionary[$k]);
				$unit->appendChild(new XMLElement('source', General::sanitize($k)));
				$unit->appendChild(new XMLElement('target', General::sanitize($v), array('state' => 'x-obsolete')));
				$group->appendChild($unit);
			}
			$body->appendChild($group);

			$group = new XMLElement('group');
			$group->setAttribute('resname', 'transliterations');

			$sklTransliterations = array();
			if (is_array($translation['transliterations']) && !empty($translation['transliterations'])) {
				foreach ($translation['transliterations'] as $k => $v) {
					$sklTransliterations[$k] = md5($k);
					$unit = new XMLElement('trans-unit');
					$unit->setAttribute('id', $sklTransliterations[$k]);
					$unit->appendChild(new XMLElement('source', General::sanitize($k)));
					$unit->appendChild(new XMLElement('target', General::sanitize($v), array('state' => 'translated')));
					$group->appendChild($unit);
				}
			}
			else if ($extension == 'symphony' || empty($extension)) {
				foreach (TranslationManager::defaultTransliterations() as $k => $v) {
					$sklTransliterations[$k] = md5($k);
					$unit = new XMLElement('trans-unit');
					$unit->setAttribute('id', $sklTransliterations[$k]);
					$unit->appendChild(new XMLElement('source', General::sanitize($k)));
					$unit->appendChild(new XMLElement('target', General::sanitize($v), array('state' => 'new')));
					$group->appendChild($unit);
				}
			}
			$body->appendChild($group);

			// Generate skeleton
			$skl = new XMLElement('skl');
			$translation['dictionary'] = $sklDictionary;
			$translation['transliterations'] = $sklTransliterations;
			$skl->appendChild(new XMLElement('internal-file', '<![CDATA['.TranslationManager::toPHP($translation).']]>', array('form' => 'application/x-php')));
			$header->appendChild($skl);

			$file->appendChild($header);
			$file->appendChild($body);

			$xliff->appendChild($file);
		}
	}

