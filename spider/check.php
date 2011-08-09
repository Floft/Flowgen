<?php
/*
 This script validates all the pages of a website.

 Requires: curl and wdg-html-validator (available in the AUR)
*/

require_once "spider.php";
require_once "settings.php";

class Check extends Spider
{
	protected function use_data($url, $text, $html) {
		if ($this->in_robots($url)||strpos($html,"</html>")===false||empty($html)) {
			echo "Notice: skipping $url\n";
		} else {
			echo "Validating: $url\n";
			system("curl ".escapeshellarg($this->replace_sites($url))." 2>/dev/null | validate");
		}
	}

	protected function cleanup() { }
}

$spider = new Check($startURLs, $replaceURLs);
$spider->run();
?>
