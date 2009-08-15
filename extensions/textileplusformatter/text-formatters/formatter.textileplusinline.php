<?php
	
	class FormatterTextilePlusInline extends TextFormatter {
		const COL_CHARACTER = 0;
		const COL_NAMED = 1;
		const COL_DECIMAL = 2;
		const COL_HEXADECIMAL = 3;
		
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function about() {
			return array(
				'name'			=> 'Textile Plus Inline',
				'version'		=> '1.0.1',
				'release-date'	=> '2009-05-11',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'description'	=> 'Format text using Textile with some extra features, inline only.'
			);
		}
		
	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/
		
		protected function table($key, $value, $special = true) {
			$handle = fopen(EXTENSIONS . '/textileplusformatter/assets/characters.csv', 'r');
			$table = array();
			
			while (($item = fgetcsv($handle, 256, ',')) !== false) {
				if (count($item) != 6) continue;
				if (!$special and $item[4] == 'yes') continue;
				
				$table[$item[$key]] = $item[$value];
			}
			
			fclose($handle);
			
			return $table;
		}
		
		protected function entities($source) {
			// Replace wonky < and >:
			$source = preg_replace('/(^|[\s])<([\s]|$)/', '\1&lt;\2', $source);
			$source = preg_replace('/(^|[\s])>([\s]|$)/', '\1&gt;\2', $source);
			
			// Pad numeric entities correctly:
			$source = preg_replace_callback(
				'/&#([0-9]+);/', array($this, 'entities_decimal'), $source
			);
			$source = preg_replace_callback(
				'/&#x([0-9]+);/', array($this, 'entities_hexadecimal'), $source
			);
			
			// Convert all entities to numeric entities:
			$table = $this->table(self::COL_CHARACTER, self::COL_DECIMAL, false);
			$source = str_replace(array_keys($table), array_values($table), $source);
			
			$table = $this->table(self::COL_NAMED, self::COL_DECIMAL);
			$source = str_replace(array_keys($table), array_values($table), $source);
			
			$table = $this->table(self::COL_HEXADECIMAL, self::COL_DECIMAL);
			$source = str_ireplace(array_keys($table), array_values($table), $source);
			
			// Replace broken entities:
			$source = preg_replace('/&(?!#[0-9]+;)([^;\s]+;?)?/', '&#38;\\1', $source);
			
			return $source;
		}
		
		protected function entities_decimal($matches) {
			return '&#' . ltrim($matches[1], '0') . ';';
		}
		
		protected function entities_hexadecimal($matches) {
			return '&#x' . str_pad(ltrim($matches[1], '0'), 4, '0', STR_PAD_LEFT) . ';';
		}
		
		protected function dump($source) {
			header('content-type: text/plain');
			
			echo $source, "\n";
		}
		
	/*-------------------------------------------------------------------------
		Formatter:
	-------------------------------------------------------------------------*/
		
		public function run($source) {
			require_once(EXTENSIONS . '/textileplusformatter/lib/lib.textile.php');
			
			// Repair/sanitize entities:
			$source = $this->entities($source);
			
			// Convert - to n dash and -- to m dash:
			$source = preg_replace(
				array('/(\s?)--(\s?)/', '/\s-(?:\s|$)/'),
				array('\\1&#8212;\\2', ' &#8211; '), $source
			);
			
			// Put a thin space before and after a dash:
			$source = preg_replace(
				'/(\s|&#160;)*(&#8212;)(\s|&#160;)*/',
				'&#8201;<span class="dash">\\2</span>&#8201;', $source
			);
			
			// Prevent widows by inserting non breaking spaces:
			$source = preg_replace(
				'/([^\s])\s+(((<(a|span|i|b|em|strong|acronym|caps|sub|sup|abbr|big|small|code|cite|tt)[^>]*>)*\s*[^\s<>]+)(<\/(a|span|i|b|em|strong|acronym|caps|sub|sup|abbr|big|small|code|cite|tt)>)*[^\s<>]*\s*(<\/(p|h[1-6]|li)>|$))/i',
				'\\1&#160;\\2', $source
			);
			
			// Wrap amptersands with a span:
			$source = preg_replace(
				'/(\s|&#160;|&#8201;)(&#38;)(\s|&#160;|&#8201;)/',
				'\\1<span class="amp">&#38;</span>\\3', $source
			);
			
			// Apply textile:
			$textile = new Extension_TextPlusFormatter_Textile();
			$source = $textile->TextileInline($source);
			
		    // Wrap quotation marks with a span:
		    $source = str_replace(
		    	array('&#8216;', '&#x2018', '&lsquo;'),
		    	'<span class="lsquo squo">&#8216;</span>', $source
		    );
		    $source = str_replace(
		    	array('&#8217;', '&#x2019', '&rsquo;'),
		    	'<span class="rsquo squo">&#8217;</span>', $source
		    );
		    $source = str_replace(
		    	array('&#8220;', '&#x201c', '&ldquo;'),
		    	'<span class="ldquo dquo">&#8220;</span>', $source
		    );
		    $source = str_replace(
		    	array('&#8221;', '&#x201d', '&rdquo;'),
		    	'<span class="rdquo dquo">&#8221;</span>', $source
		    );
		    $source = str_replace(
		    	array('&#171;', '&#x00ab', '&laquo;'),
		    	'<span class="laquo aquo">&#8221;</span>', $source
		    );
		    $source = str_replace(
		    	array('&#187;', '&#x00bb', '&raquo;'),
		    	'<span class="raquo aquo">&#8221;</span>', $source
		    );
			
			// Wrap ellipsis with a span:
			$source = str_replace(
				'&#8230;', '<span class="ellipsis">&#8230;</span>', $source
			);
			
			$document = new DOMDocument('1.0', 'UTF-8');
			$document->loadHTML($source);
			$source = '';
			
			$xpath = new DOMXPath($document);
			
			foreach ($xpath->query('/html/body/p/node()') as $child) {
				$source .= $document->saveXML($child);
			}
			
			$source = $this->entities($source);
			
			return $source;
		}
	}

?>