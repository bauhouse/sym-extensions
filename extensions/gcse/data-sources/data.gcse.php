<?php
	Class datasourceGCSE extends Datasource{
		public $dsParamFILTERS = array(
			'query' => '{$q:$url-q}',
			'page' => '{$p:$url-p}'
		);

		function __construct(&$parent, $env=NULL, $process_params=true){
			$this->dsParamFILTERS = $this->__getParamFilters();

			parent::__construct($parent, $env, $process_params);
		}

		function __getParamFilters() {
			global $settings;

			$result = array(
				'query' => ($settings['gcse']['qname'] ? $settings['gcse']['qname'] : '{$q:$url-q}'),
				'page' => ($settings['gcse']['pname'] ? $settings['gcse']['pname'] : '{$p:$url-p}'),
				'size' => ($settings['gcse']['size'] ? $settings['gcse']['size'] : '4'),
				'safe' => ($settings['gcse']['safe'] ? $settings['gcse']['safe'] : 'moderate'),
			);

			if ($settings['gcse']['lang'] && $settings['gcse']['lang'] != '-') $result['lang'] = $settings['gcse']['lang'];

			foreach (array('key', 'cx', 'cref') as $id) {
				if ($settings['gcse'][$id]) $result[$id] = $settings['gcse'][$id];
			}

			return $result;
		}

		function example(){
			return '
<gcse query="test">
	<pagination-info total-entries="2" total-pages="1" entries-per-page="4" current-page="1" more-url="http://www.google.com/search?oe=utf8&#x26;ie=utf8&#x26;source=uds&#x26;start=0&#x26;q=test" more-url-title="Google.com" />
	<entry>
		<url>http://yourdomain.com/entries/some-title-here/</url>
		<title>Yourdomain.com - Some Title here</title>
		<content><b>Test</b> some titles and other content here <b>...</b></content>
	</entry>
	<entry>
		<url>http://yourdomain.com/entries/i-just-won-billion-dollars/</url>
		<title>Yourdomain.com - OMG I ams so f**** rich now!</title>
		<content>Just for a <b>test</b> i bought lottery ticket and... I WON main prize of <b>...</b></content>
	</entry>
</gcse>
';
		}

		function about(){
			$params = array();
			foreach (datasourceGCSE::__getParamFilters() as $k => $v) {
				if (strpos($v, '{$') !== false) $params[] = $v.' // '.$k;
			}
			return array(
				"name" => "Google Custom Search Engine",
				"description" => "Calls Google AJAX Search API and returns results in XML.",
				"author" => array("name" => "Marcin Konicki",
					"website" => "http://ahwayakchih.neoni.net",
					"email" => "ahwayakchih@neoni.net"),
				"version" => "2.2",
				"release-date" => "2008-12-18",
				"recognised-url-param" => $params,
			);
		}

		function grab($param=array()){
			$q = trim($this->dsParamFILTERS['query']);
			if (!$q) return NULL;

			$p = '';
			$q = preg_replace('/\\\\(["\'])/', '$1', urldecode($q)); // TODO: Symphony tries to do some magic behind the scenes so we have to change it back :( Find out cleaner solution.

			$size = $this->dsParamFILTERS['size'];
			if ($size <= 4) {
				$size = 4;
				$p .= '&rsz=small';
			}
			else {
				$size = 8;
				$p .= '&rsz=large';
			}

			$page = intval($this->dsParamFILTERS['page']);
			$page -= 1; // Pagination counts from 1, not 0
			if (!$page || $page < 0) $page = 0;
			$p .= '&start='.($page*$size);

			if ($this->dsParamFILTERS['lang'] && $this->dsParamFILTERS['lang'] != '-') $p .= '&hl='.$this->dsParamFILTERS['lang'].'&lr=lang_'.$this->dsParamFILTERS['lang'];
			if ($this->dsParamFILTERS['cx']) $p .= '&cx='.urlencode($this->dsParamFILTERS['cx']);
			if ($this->dsParamFILTERS['cref']) $p .= '&cref='.urlencode($this->dsParamFILTERS['cref']);
			if ($this->dsParamFILTERS['key']) $p .= '&key='.urlencode($this->dsParamFILTERS['key']);
			if ($this->dsParamFILTERS['safe']) $p .= '&safe='.$this->dsParamFILTERS['safe'];

			$googleURL = 'http://ajax.googleapis.com/ajax/services/search/web?v=1.0'.$p.'&q='.urlencode($q);

			$xml = new XMLElement('gcse');
			$xml->appendChild(new XMLElement('query', $q));

			if (!function_exists('curl_init')) {
				$error = new XMLElement('error', 'cURL not installed.');
				$xml->appendChild($error);
				return $xml;
			}

			$ch = curl_init();
			if (!$ch) {
				$error = new XMLElement('error', 'Cannot initialize cURL object.');
				$xml->appendChild($error);
				return $xml;
			}

			curl_setopt($ch, CURLOPT_URL, $googleURL);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_REFERER, URL);
			$body = trim(curl_exec($ch));
			curl_close($ch);

			if (!$body) {
				$error = new XMLElement('error', 'No data received.');
				$xml->appendChild($error);
				return $xml;
			}

			$data = NULL;
			if (function_exists('json_decode')) {
				$data = json_decode($body);
			}
			else {
				@require_once(EXTENSIONS . "/gcse/lib/json.php");
				$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
				$data = $json->decode($body);
			}

			if (!$data || $data->responseStatus != 200 || !is_array($data->responseData->results)) {
				$error = new XMLElement('error', ($data->responseDetails ? $data->responseDetails : 'No data found.'));
				$xml->appendChild($error);
				return $xml;
			}

			if (count($data->responseData->results) < $size) {
				$data->responseData->cursor->estimatedResultCount = ($data->responseData->cursor->currentPageIndex * $size) + count($data->responseData->results);
			}

			$pagination = new XMLElement('pagination-info');
			$pagination->setAttribute('total-entries', $data->responseData->cursor->estimatedResultCount);
			$pagination->setAttribute('total-pages', ceil($data->responseData->cursor->estimatedResultCount / $size));
			$pagination->setAttribute('entries-per-page', $size);
			$pagination->setAttribute('current-page', $data->responseData->cursor->currentPageIndex + 1);
			$pagination->setAttribute('more-url', General::sanitize($data->responseData->cursor->moreResultsUrl));
			$pagination->setAttribute('more-url-title', 'Google.com');
			$xml->appendChild($pagination);

			foreach ($data->responseData->results as $result) {
				$entry = new XMLElement('entry');
				$entry->appendChild(new XMLElement('url', $result->url));
				$entry->appendChild(new XMLElement('title', $result->titleNoFormatting));
				$entry->appendChild(new XMLElement('content', $result->content));
				$xml->appendChild($entry);
			}
/*
			if($param['caching'] && $cache = $this->check_cache($hash_id, time() + (CACHE_LIFETIME - 3600))){ // keep cache of results for 1 hour
				return $cache;
				exit();
			}


			##Write To Cache
			if($param['caching']){
				$result = $xml->generate($param['indent'], $param['indent-depth']);
				$this->write_to_cache($hash_id, $result, $this->_cache_sections);
				return $result;
			}
*/
			return $xml;
		}
	}

?>