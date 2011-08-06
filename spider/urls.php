<?php
/*
This prints all the external links on the sites.
*/

require_once "spider.php";
require_once "settings.php";

class URLs extends Spider
{
	public function getURLs($url,$dom)
	{
		parent::getURLs($url,$dom);

		foreach ($dom->getElementsByTagName('a') as $item) {
			$url=$this->absolutify($url,$item->getAttribute('href'));
			
			//only print other sites
			if (!$this->right_site($url))
				echo "$url\n";
		}
	}

	public function use_data($url, $text, $html) { }

	public function cleanup() { }
}

$spider = new URLs($startURLs, $replaceURLs);
$spider->run();
?>
