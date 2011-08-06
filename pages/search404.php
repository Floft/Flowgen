Title: Search 404
Created: 1311223774
URL: /search404
Theme: none
-
<?php
function forward($url) {
	header("Location: $url");
	exit();
}
function delete_bad($string) {
	$list = array(
		"php",
		"html",
		"htm",
		"txt"
	);

	foreach ($list as $item) {
		$string = str_replace($item, "", $string);
	}

	return $string;
}

if (isset($_REQUEST['url'])) {
	$url = stripslashes($_GET['url']);
	$url = strip_tags($url);
	$url = preg_replace('/^\//', '', $url);
	$url = preg_replace('/(?<=\\w)(?=[A-Z])/'," $1", $url);
	$url = preg_replace('/[^a-zA-Z0-9]/', ' ', $url);
	$url = delete_bad($url);
	$url = trim($url);
	$url = urlencode($url);
	forward("/search?q=$url");
} else {
	forward("/search");
}
?>
