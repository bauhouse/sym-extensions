<?php
	require_once(TOOLKIT . '/class.administrationpage.php');

	Class contentExtensionGCSEPreferences extends AdministrationPage{

		public $languages;

		function __construct(&$parent){
			parent::__construct($parent);

			$this->languages = array(
				'Disabled'=>'-',
				'Arabic'=>'ar',
				'Bulgarian'=>'bg',
				'Catalan'=>'ca',
				'Chinese (Simplified)'=>'zh-CN',
				'Chinese (Traditional)'=>'zh-TW',
				'Croation'=>'hr',
				'Czech'=>'cs',
				'Danish'=>'da',
				'Dutch'=>'nl',
				'English'=>'en',
				'Estonian'=>'et',
				'Finnish'=>'fi',
				'French'=>'fr',
				'German'=>'de',
				'Greek'=>'el',
				'Hebrew'=>'iw',
				'Hungarian'=>'hu',
				'Icelandic'=>'is',
				'Indonesian'=>'id',
				'Italian'=>'it',
				'Japanese'=>'ja',
				'Korean'=>'ko',
				'Latvian'=>'lv',
				'Lithuanian'=>'lt',
				'Norwegian'=>'no',
				'Polish'=>'pl',
				'Portuguese'=>'pt',
				'Romanian'=>'ro',
				'Russian'=>'ru',
				'Serbian'=>'sr',
				'Slovak'=>'sk',
				'Slovenian'=>'sl',
				'Spanish'=>'es',
				'Swedish'=>'sv',
				'Turkish'=>'tr'
			);
		}

		function view(){
			$fields = $_POST['fields'];

			$this->setPageType('form');
			$this->setTitle('Symphony &ndash; Google Custom Search Engine');
			$this->appendSubheading('Google Custom Search Engine');
			$this->addScriptToHead(URL . '/extensions/gcse/assets/admin.js', 500);

			$link = new XMLElement('link');
			$link->setAttributeArray(array('rel' => 'stylesheet', 'type' => 'text/css', 'media' => 'screen', 'href' => URL . '/extensions/gcse/assets/admin.css'));
			$this->addElementToHead($link, 500);

			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->setAttribute('id', 'help');
			$fieldset->appendChild(new XMLElement('legend', 'Information'));
			$content = <<<END
			<p>With <a href="http://www.google.com/coop/cse/" title="Read more">Google Custom Search Engine</a> and <a href="http://code.google.com/apis/ajaxsearch/">Google AJAX Search API</a> you can add search functionality to your Symphony orchestrated site.</p>
			<p>To do that you have to add "Google Custom Search Engine" data source to page where you want to get results. Data source needs query parameter, which you can pass through URL schema or GET variables. It also handles page parameter, which tells it which page of search results it should provide.</p>
			<p>For example, after configuring URL Parameters of page to "q/p" and using default settings on GCSE page, you can put this in XSLT source of page:</p>
			<p><code>
&lt;xsl:template match="data"&gt;<br />
&lt;form action="{\$root}/{\$current-page}" method="GET"&gt;<br />
&lt;input name="q" value="{gcse/query}" /&gt;<br />
&lt;input type="submit" value="Search" /&gt;<br />
&lt;/form&gt;<br />
&lt;ul class="entryList"&gt;<br />
&lt;xsl:apply-templates select="gcse/entry" /&gt;<br />
&lt;/ul&gt;<br />
&lt;/xsl:template&gt;<br /><br />
&lt;xsl:template match="gcse/entry"&gt;<br />
&lt;li&gt;&lt;dl&gt;<br />
&lt;dt&gt;&lt;a href="{url}" target="_blank"&gt;&lt;xsl:value-of select="title"/&gt;&lt;/a&gt;&lt;/dt&gt;<br />
&lt;dd&gt;&lt;xsl:value-of select="content"/&gt;&lt;/dd&gt;<br />
&lt;/dl&gt;&lt;/li&gt;<br />
&lt;/xsl:template&gt;<br />
			</code></p>
			<p>That will allow users to enter search query, click "Search" button and get results, just like on <a href="http://google.com">Google.com</a> page.</p>
			<p>Now all is left to do is to create <a href="http://sitemaps.org/">SiteMap</a> and ping Google to download it and index Your site :).</p>
END;
			$fieldset->appendChild(new XMLElement('div', $content));
			$this->Form->appendChild($fieldset);


			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', 'Essentials'));

			$p = new XMLElement('p', 'Use <code>{$param}</code> syntax to filter by page parameters.<br /><br />', array('class' => 'help'));
			$p->appendChild(Widget::Anchor('Custom search', 'http://www.google.com/coop/cse/', 'Read Google Custom Search Engine documentation', 'gcse'));
			$fieldset->appendChild($p);

			$label = Widget::Label('Search for');
			$label->appendChild(new XMLElement('i', 'Defaults to "{$q:$url-q}"'));
			if (!($temp = $this->_Parent->Configuration->get('qname', 'gcse'))) $temp = '{$q:$url-q}';
			$label->appendChild(Widget::Input('fields[qname]', $temp));
			$fieldset->appendChild($label);

			$label = Widget::Label('API Key');
			$label->appendChild(new XMLElement('i', 'This optional argument supplies the <a href="http://code.google.com/apis/ajaxsearch/key.html">application\'s key</a>'));
			$label->appendChild(Widget::Input('fields[key]', $this->_Parent->Configuration->get('key', 'gcse')));
			$fieldset->appendChild($label);

			$label = Widget::Label('Unique identifier');
			$label->appendChild(new XMLElement('i', 'This optional argument supplies the <a href="http://www.google.com/coop/docs/cse/resultsxml.html#cxsp">unique id</a> for the Custom Search Engine'));
			$label->appendChild(Widget::Input('fields[cx]', $this->_Parent->Configuration->get('cx', 'gcse')));
			$fieldset->appendChild($label);

			$label = Widget::Label('URL of CSE specification');
			$label->appendChild(new XMLElement('i', 'This optional argument supplies the url of a <a href="http://www.google.com/coop/docs/cse/cref.html">linked</a> Custom Search Engine specification'));
			$label->appendChild(Widget::Input('fields[cref]', $this->_Parent->Configuration->get('cref', 'gcse')));
			$fieldset->appendChild($label);

			$this->Form->appendChild($fieldset);

			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', 'Filter results'));
			$fieldset->appendChild(new XMLElement('p', 'Use <code>{$param}</code> syntax to filter by page parameters.', array('class' => 'help')));

			$div = new XMLElement('div');
			$div->setAttribute('class', 'group');

			$label = Widget::Label('Language');
			$options = array();
			$temp = $this->_Parent->Configuration->get('lang', 'gcse');
			foreach ($this->languages as $name => $code) {
				$options[] = array($code, ($code==$temp), $name);
			}
			$label->appendChild(Widget::Select('fields[lang]', $options));
			$div->appendChild($label);

			$label = Widget::Label('Safety level');
			$vars = array('Disabled' => 'off', 'Moderate' => 'moderate', 'Active' => 'active');
			$options = array();
			$temp = $this->_Parent->Configuration->get('safe', 'gcse');
			foreach ($vars as $name => $code) {
				$options[] = array($code, ($code==$temp), $name);
			}
			$label->appendChild(Widget::Select('fields[safe]', $options));
			$div->appendChild($label);

			$fieldset->appendChild($div);
			$this->Form->appendChild($fieldset);

			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', 'Limiting'));
			$fieldset->appendChild(new XMLElement('p', 'Use <code>{$param}</code> syntax to limit by page parameters.', array('class' => 'help')));

			$div = new XMLElement('div');
			$div->setAttribute('class', 'group');

			$label = Widget::Label();
			$temp = $this->_Parent->Configuration->get('size', 'gcse');
			$options = array(
				array('4', ($temp == 4), '4 results (small)'),
				array('8', ($temp == 8), '8 results (large)')
			);
			$select = Widget::Select('fields[size]', $options, array('style' => 'width: 10em; display: inline;'));
			$label->setValue('Show maximum of ' . $select->generate(false));
			$div->appendChild($label);

			$label = Widget::Label();
			if (!($temp = $this->_Parent->Configuration->get('pname', 'gcse'))) $temp = '{$p:$url-p}';
			$input = Widget::Input('fields[pname]', $temp, NULL, array('size' => 10));
			$label->setValue('Show page ' . $input->generate(false) . ' of results');
			$div->appendChild($label);

			$fieldset->appendChild($div);
			$this->Form->appendChild($fieldset);

			$div = new XMLElement('div');
			$div->setAttribute('class', 'actions');
			$div->appendChild(Widget::Input('action[save]', 'Save Changes', 'submit', array('accesskey' => 's')));

			$this->Form->appendChild($div);
		}

		function action() {
			if (array_key_exists('save', $_POST['action'])) $this->save();
		}

		function save() {
			$fields = $_POST['fields'];

			if ($temp = trim($fields['qname'])) $this->_Parent->Configuration->set('qname', $temp, 'gcse');
			else $this->_Parent->Configuration->set('qname', '{$q:$url-q}', 'gcse');

			if ($temp = trim($fields['pname'])) $this->_Parent->Configuration->set('pname', $temp, 'gcse');
			else $this->_Parent->Configuration->set('pname', '{$p:$url-p}', 'gcse');

			if ($fields['size'] == 4 || $fields['size'] == 8) {
				$this->_Parent->Configuration->set('size', intval($fields['size']), 'gcse');
			}

			foreach (array('key', 'cx', 'cref') as $id) {
				$this->_Parent->Configuration->set($id, trim($fields[$id]), 'gcse');
			}

			if (in_array($fields['lang'], $this->languages)) $this->_Parent->Configuration->set('lang', trim($fields['lang']), 'gcse');

			if (in_array($fields['safe'], array('off', 'moderate', 'active'))) $this->_Parent->Configuration->set('safe', trim($fields['safe']), 'gcse');

			return $this->_Parent->saveConfig();
		}
	}

?>