<?php

	require_once(EXTENSIONS . '/translationmanager/lib/class.translationmanager.php');

	Class datasourceTranslations extends Datasource{

		function example(){
			$xml = $this->grab();
			return $xml->generate(true);
		}

		function about(){
			return array(
				'name' => __('Translations'),
				'description' => __('Returns list of translations installed.'),
				'author' => array("name" => "Marcin Konicki",
					'website' => "http://ahwayakchih.neoni.net",
					'email' => "ahwayakchih@neoni.net"),
				'version' => "1.0",
				'release-date' => "2009-01-05",
			);
		}

		function grab($param=array()){
			$xml = new XMLElement('translations');

			$tm = new TranslationManager($this->_Parent);

			foreach ($tm->listAll() as $lang => $extensions) {
				$temp = $tm->get($lang, $extensions[0]);

				$item = new XMLElement('language');
				$item->setAttribute('handle', $lang);
				$item->setAttribute('name', $temp['about']['name']);

				foreach ($extensions as $extension) {
					$item->appendChild(new XMLElement('extension', NULL, array('handle' => $extension)));
				}

				$xml->appendChild($item);
			}

			return $xml;
		}
	}

