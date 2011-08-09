<?php
/*
This prints all the external links on the sites.
*/

require_once "spider.php";
require_once "settings.php";

class URLs extends Spider
{
	protected function getURLs($url,$dom)
	{
		parent::getURLs($url,$dom);

		foreach ($dom->getElementsByTagName('a') as $item) {
			$url=$this->absolutify($url,$item->getAttribute('href'));
			
			//only print other sites
			if (!$this->right_site($url))
				echo "$url\n";
		}
	}

	protected function use_data($url, $text, $html) { }

	protected function cleanup() { }
}

$spider = new URLs($startURLs, $replaceURLs);
$spider->run();
?>
