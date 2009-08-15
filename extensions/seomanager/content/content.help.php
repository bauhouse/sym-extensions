<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	
	class contentExtensionSEOManagerHelp extends AdministrationPage {
	/*-------------------------------------------------------------------------
		PCRE
	-------------------------------------------------------------------------*/
		
		public function __viewPCRE() {
			$this->setPageType('form');
			$this->setTitle("Symphony &ndash; SEO Manager &ndash; Help &ndash; Perl Compatible Regular Expressions");
			$this->appendSubheading("Perl Compatible Regular Expressions");
			
		// Documentation ------------------------------------------------------
			
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', 'Documentation'));
			
			$fieldset->appendChild(new XMLElement('p', '
				Perl Compatible Regular Expressions, commonly known as PCRE,
				are a means for identifying strings of text of interest.
			'));
			
			$fieldset->appendChild(new XMLElement('h3', 'Resources'));
			
			$list = new XMLElement('ul');
			
			$list->appendChild(new XMLElement('li', '
				<a href="http://www.regular-expressions.info/tutorial.html">Learn How to Use Regular Expressions</a>
			'));
			$list->appendChild(new XMLElement('li', '
				<a href="http://en.wikipedia.org/wiki/Pcre">PCRE on Wikipedia</a>
			'));
			$list->appendChild(new XMLElement('li', '
				<a href="http://au.php.net/manual/en/reference.pcre.pattern.syntax.php">A technical description of PCRE</a>
			'));
			
			$fieldset->appendChild($list);
			
			$this->Form->appendChild($fieldset);
		}
	}
	
?>