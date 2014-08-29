Title: Search
Created: 1310406662
URL: /search
Options: notitle
-
<?php
$ScriptURL = "/search";
$FormAction = "/search";
$Site2Search="";
$HiddenField = null;
$searchsize=40;

if ($_SERVER['HTTP_HOST']=="floft.linuxd.net"||$_SERVER['HTTP_HOST']=="fluxbb.wopto.net") {
	$dbname="";
	$dbuser="";
	$enable_spelling=false;
} else {
	$dbname="";
	$dbuser="";
	$enable_spelling=true;
}

//define some functions
function GetAddressVar($name)
{
	if (isset($_REQUEST[$name]))	$name = $_REQUEST[$name];
	else $name = null;
	return $name;
}
function replace_sites($url)
{
	return str_replace("www.floft.net",$_SERVER['HTTP_HOST'],$url);
}
function connect() {
	global $dbname, $dbuser;
	$pass="";
	$dbh=mysql_connect("localhost", $dbuser, $pass) or
		die ('I cannot connect to the database because: ' . mysql_error());
	mysql_select_db($dbname);
}
function forward($url) {
    // Would require the header/footer to be displayed later
    //header('location: ' . $url);

    // Javascript method:
    echo <<<EOF
<script type="text/javascript">
<!--
window.location.replace("$url");
// -->
</script>
<noscript>
Link: <a href="$url">$url</a>
</noscript>
EOF;
}

//set the timestampzone
if (function_exists('date_default_timestampzone_set')) date_default_timestampzone_set('America/Los_Angeles');

ob_start();
//before anything, see if the user wants the cached thing
if (isset($_REQUEST['cache']))
{
	//get what the user has searched for
	$q = stripslashes($_GET['cache']);
	$q_justquery = $q;
	$prefix = null;

	if(substr($q, -1, 1) == ' ') $q = substr($q, 0, -1);

	connect();

	//get the page text/url from the database (the cache)
	if (!preg_match("/^http:\/\//Di", $q))
	{
		$query="SELECT url,timestamp,text from `search` where url='http://" . addslashes($q) . "' limit 1";
		$result=mysql_query($query);
		$results=mysql_numrows($result);
	}
	else
	{
		$query="SELECT url,timestamp,text from `search` where url='" .addslashes($q) . "' limit 1";
		$result=mysql_query($query);
		$results=mysql_numrows($result);
	}

	if ($results == 1)
	{
		$url = mysql_result($result,0,"url");
		$timestamp = mysql_result($result,0,"timestamp");
		if (!preg_match("/:\/\//", substr($url, 0, 10))) $url = "http://" . $url;

		echo "<table width='97%' bgcolor='#FFFFFF' border='1' align='center'><tr><td align='left'>This is a version of <a href=\"$url\">$url</a> as it looked when our spider crawled the site on " . date("m/d/y", $timestamp) . ". The page below is the version in our index. This is not necessarily the most recent version of the page - to see the most recent version of this page, <a href=\"$url\">visit the actual page</a>.<br /><br /><small><i>Floft.net is not affiliated with the author(s) of this page nor responsible for the content displayed below.</i></small></td></tr></table><br /><br /><base href=\"" . $url . "\" />";
		echo mysql_result($result,0,"text");
	}
	else
	{
		forward($ScriptURL . '?q=cache:' . urlencode($q));
	}

	mysql_close();
}
else
{
	/*echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head><title>Search</title><style type="text/css">
	a {color:#0000CC;}
	a:visited {color:#663399;}
	a.cache {color:#7E9DB9;}
	a.cache:visited {color:#663399;}
	a.pages {color:#000000;}
	span.spelling {color:#DC143C}
	span.currentPage {color:#DC143C;}
	span.link {color:#008000;font-size:small;}
	span.head {font-size:150%;font-weight:bolder;}
</style></head><body>';*/
	echo '<div class="search_results">';

	function returnCheckedVar($num, $match)
	{
		if ($num==$match) return ' selected=\'selected\'';
		else return '';
	}
	function NotCommon($word)
	{
		$common = array("able", "all", "also", "an", "am", "and", "any", "are", "as", "at", "be", "but", "by", "can", "can't", "do", "does", "did", "could", "didn't", "eg", "each", "etc", "ex", "for", "had", "hadn't", "has", "hasn't", "if", "in", "is", "may", "of", "on", "onto", "or", "so", "the", "that", "than", "thats", "that's", "then", "than", "this", "to", "too", "what", "will", "won't", "yet", "were", "went", "your");
		$word = trim(strtolower($word));

		foreach ($common as $match)
			if (strtolower($match) == $word) return false;

		return true;
	}
	function sortSelectedResultText($a, $b)
	{
		if ($a[0] == $b[0]) {
			return 0;
		}
		return ($a[0] < $b[0]) ? -1 : 1;
	}
	function getSelectedResultText($num)
	{
		//create array
		$results = array();
		//create array text
		$results[] = array(10, "<option value=\"10\"" . returnCheckedVar(10, $num) . ">Show 10</option>");
		$results[] = array(20, "<option value=\"20\"" . returnCheckedVar(20, $num) . ">Show 20</option>");
		$results[] = array(30, "<option value=\"30\"" . returnCheckedVar(30, $num) . ">Show 30</option>");
		$results[] = array(40, "<option value=\"40\"" . returnCheckedVar(40, $num) . ">Show 40</option>");
		$results[] = array(50, "<option value=\"50\"" . returnCheckedVar(50, $num) . ">Show 50</option>");
		$results[] = array(60, "<option value=\"60\"" . returnCheckedVar(60, $num) . ">Show 60</option>");
		$results[] = array(70, "<option value=\"70\"" . returnCheckedVar(70, $num) . ">Show 70</option>");
		$results[] = array(80, "<option value=\"80\"" . returnCheckedVar(80, $num) . ">Show 80</option>");
		$results[] = array(90, "<option value=\"90\"" . returnCheckedVar(90, $num) . ">Show 90</option>");
		$results[] = array(100, "<option value=\"100\"" . returnCheckedVar(100, $num) . ">Show 100</option>");

		if($num != 10 && $num != 20 && $num != 30 && $num != 40 && $num != 50 && $num != 60 && $num != 70 && $num != 80 && $num != 90 && $num != 100)
		{
			$results[] = array($num, "<option value=\"$num\"" . returnCheckedVar($num, $num) . ">Show $num</option>");
		}

		usort($results, "sortSelectedResultText");

		$text=null;
		foreach ($results as $value)
		{
			$text .= $value[1];
		}

		return " <select name=\"results\">" . $text . "</select> ";
	}

	function SpellCheck($input)
	{
		function myCorrectWords($word)
		{
			$inArray = 0;

			$words = array();
			$words[] = 'floft';
			$words[] = 'php';
			$words[] = 'sda';
			$words[] = 'cinelerra';
			$words[] = 'blender';
			$words[] = 'gimp';
			$words[] = 'linux';
			$words[] = 'd5000';
			$words[] = 'flotografy';
			$words[] = 'floftweet';
			$words[] = 'blog';

			foreach ($words as $value)
			{
				if($word == strtolower($value)) $inArray = 1;
			}

			return $inArray;
		}

		$sentence = trim($input);
		$spell = pspell_new("en", "american");
		$words = explode(" ", $sentence);
		$output = false;

		foreach($words as $word) {
			if (pspell_check($spell, $word)) {
				$output .= $word . ' ';
			} else if (!myCorrectWords($word)) {
				$suggestions = pspell_suggest($spell, $word);
				if (count($suggestions)) {

				   $similarities = array();

					foreach($suggestions as $suggestion) {
						if (metaphone($word) != metaphone($suggestion)) continue;
						similar_text($word, $suggestion, $similarity);
						$similarity = round($similarity, 2);
						$similarities[$suggestion] = $similarity;
					}

					arsort($similarities);
					$output .= '<b>' . $suggestions[0] . '</b> ';
				} else {
					$output .= $word . ' ';
				}
			}
			else
			{
				$output .= $word . ' ';
			}
		}

		if (trim(strip_tags(strtolower($output))) != strtolower($sentence)) return trim($output);
		else return false;
	}

	echo '<span class="head">Search</span><br />';

	//check the user has searched for anything
	if (isset($_GET['q']) && trim(stripslashes($_GET['q'])) != null)
	{
		//get what the user has searched for
		$q = stripslashes($_GET['q']);
		$q_justquery = $q;
		$prefix = null;

		//number of results per page
		if(isset($_REQUEST['results']) && is_numeric($_REQUEST['results']) && $_REQUEST['results'] > 0 && $_REQUEST['results'] <= 500) $resultNum = stripslashes(rawurldecode($_REQUEST['results']));
		else $resultNum = 10;

		if(substr($q, -1, 1) == ' ') $q = substr($q, 0, -1);

		connect();

		//see if they want to search a specific site
		if (isset($_REQUEST['site']) && trim($_REQUEST['site']) != null)
		{
			$Site2Search = stripslashes($_REQUEST['site']);
			$search_site = "\"" . $Site2Search . "\"";
			$HiddenField = "<input type='hidden' name='site' value='" . $Site2Search . "' />";
			$Site2Search = "&amp;site=" . $Site2Search;

			//select all the pages from the database that contain what they searched for from the specific site
			$query="SELECT *, (MATCH (title) AGAINST ('$q' IN BOOLEAN MODE)*3) +
							(MATCH (url) AGAINST ('$q' IN BOOLEAN MODE)*2) +
							MATCH (text) AGAINST ('$q' IN BOOLEAN MODE) AS Relevance FROM `search` where
							MATCH (url) AGAINST ('$search_site' IN BOOLEAN MODE) HAVING Relevance > 0.2 ORDER BY Relevance DESC";

			$result=mysql_query($query);
			$end=mysql_numrows($result);
		}
		else if (strtolower(substr($q, 0, 5)) == 'site:')
		{
			//search site and what to search for on that site
			$matches = explode(" ", substr(addslashes($q), 5), 2);
			$match_num = count($matches);

			if ($match_num == 1)
			{
				$search_site = "\"" . $matches[0] . "\"";

				//select all the pages from the database that contain what they searched for
				$query="SELECT *, MATCH (url) AGAINST ('" . $search_site . "' IN BOOLEAN MODE) AS Relevance FROM `search` where
								MATCH (url) AGAINST ('" . $search_site . "' IN BOOLEAN MODE) HAVING Relevance > 0.2 ORDER BY Relevance DESC";

				$result=mysql_query($query);
				$end=mysql_numrows($result);

				$prefix = substr($q, 0, 5);
				$q_justquery = substr($q, 5);
			}
			else if ($match_num > 1)
			{
				$search_site = "\"" . $matches[0] . "\"";
				$searchquery = $matches[1];

				//select all the pages from the database that contain what they searched for
				$query="SELECT *, (MATCH (title) AGAINST ('$searchquery' IN BOOLEAN MODE)*3) +
								(MATCH (url) AGAINST ('$searchquery' IN BOOLEAN MODE)*2) +
								MATCH (text) AGAINST ('$searchquery' IN BOOLEAN MODE) AS Relevance FROM `search` where
								MATCH (url) AGAINST ('$search_site' IN BOOLEAN MODE) HAVING Relevance > 0.2 ORDER BY Relevance DESC";

				$result=mysql_query($query);
				$end=mysql_numrows($result);

				$prefix = substr($q, 0, (4+strlen($search_site)));
				$q_justquery = substr($q, (4+strlen($search_site)));
			}
			else $end = 0;
		}
		else if (strtolower(substr($q, 0, 11)) == 'allintitle:')
		{
			//select all the pages from the database that contain what they searched for
			$searchquery = substr(addslashes($q), 11);
			$query="SELECT *, MATCH (title) AGAINST ('$searchquery' IN BOOLEAN MODE) AS Relevance FROM `search` where
							 MATCH (title) AGAINST ('$searchquery' IN BOOLEAN MODE) HAVING Relevance > 0.2 ORDER BY Relevance DESC";
			$result=mysql_query($query);
			$end=mysql_numrows($result);

			$prefix = substr($q, 0, 11);
			$q_justquery = substr($q, 11);
		}
		else if (strtolower(substr($q, 0, 6)) == 'cache:')
		{
			//select all the pages from the database that contain what they searched for
			if (!preg_match("/^http:\/\//Di", $q))
			{
				$query="SELECT * from `search` where url='http://" . substr($q, 6) . "'";
				$result=mysql_query($query);
				$end=mysql_numrows($result);
			}
			else
			{
				$query="SELECT * from `search` where url='" . substr($q, 6) . "'";
				$result=mysql_query($query);
				$end=mysql_numrows($result);
			}

			if ($end == 1)
			{
				forward($ScriptURL . '?cache=' . urlencode(substr($q, 6)));
				mysql_close();
				exit;
			}
			else
			{
				$search_URL = addslashes(substr($q, 6));
				echo $search_URL;

				//select all the pages from the database that contain what they searched for
				$query="SELECT *, MATCH (url) AGAINST ('\"$search_URL\"' IN BOOLEAN MODE) AS Relevance FROM `search` where
								MATCH (url) AGAINST ('\"$search_URL\"' IN BOOLEAN MODE) HAVING Relevance > 0.2 ORDER BY Relevance DESC limit 1";
				$result=mysql_query($query);
				$results=mysql_numrows($result);

				if ($results > 0)
				{
					$url=mysql_result($result,0,"url");

					forward($ScriptURL . '?cache=' . urlencode($url));
					mysql_close();
					exit;
				}
				else
				{
					//select all the pages from the database that contain what they searched for
					$query="SELECT *, (MATCH (title) AGAINST ('\"$search_URL\"' IN BOOLEAN MODE)*3) +
									(MATCH (url) AGAINST ('\"$search_URL\"' IN BOOLEAN MODE)*2) +
									MATCH (text) AGAINST ('\"$search_URL\"' IN BOOLEAN MODE) AS Relevance FROM `search` where
									MATCH (url) AGAINST ('\"$search_URL\"' IN BOOLEAN MODE) HAVING Relevance > 0.2 ORDER BY Relevance DESC";
					$result=mysql_query($query);
					$end=mysql_numrows($result);

					$prefix = substr($q, 0, 6);
					$q_justquery = substr($q, 6);
				}
			}
		}
		else if (strtolower(substr($q, 0, 6)) == 'lucky:')
		{
			$q_justquery = addslashes(substr($q, 6));

			//select all the pages from the database that contain what they searched for
			$query="SELECT *, (MATCH (title) AGAINST ('$q_justquery' IN BOOLEAN MODE)*3) +
							(MATCH (url) AGAINST ('$q_justquery' IN BOOLEAN MODE)*2) +
							MATCH (text) AGAINST ('$q_justquery' IN BOOLEAN MODE) AS Relevance FROM `search` where
							MATCH (title,url,text) AGAINST ('$q_justquery' IN BOOLEAN MODE) HAVING Relevance > 0.2 ORDER BY Relevance DESC";
			$result=mysql_query($query);
			$end=mysql_numrows($result);

			if ($end > 0)
			{
				$lucky_url = mysql_result($result,0,"url");
				if ($lucky_url != null)
				{
					forward($lucky_url);
					mysql_close();
					exit;
				}
			}
		}
		else
		{
			//select all the pages from the database that contain what they searched for
			//$query="SELECT *, MATCH (url, title, text) AGAINST ('" . addslashes($q) . "' IN BOOLEAN MODE) AS Relevance FROM `search` where MATCH (url, title, text) AGAINST ('" . addslashes($q) . "' IN BOOLEAN MODE) HAVING Relevance > 0.2 ";
			$searchquery = addslashes($q);
			$query="SELECT *, (MATCH (title) AGAINST ('$searchquery' IN BOOLEAN MODE)*3) +
							(MATCH (url) AGAINST ('$searchquery' IN BOOLEAN MODE)*2) +
							MATCH (text) AGAINST ('$searchquery' IN BOOLEAN MODE) AS Relevance FROM `search` where
							MATCH (title,url,text) AGAINST ('$searchquery' IN BOOLEAN MODE) HAVING Relevance > 0.2 ORDER BY Relevance DESC";
			$result=mysql_query($query);
			$end=mysql_numrows($result);
		}

		if (isset($_REQUEST['lucky']) && $end > 0)
		{
			$lucky_url = mysql_result($result,0,"url");
			if ($lucky_url != null)
			{
				forward($lucky_url);
				mysql_close();
				exit;
			}
		}

		$trueend = $end;

		//if there are more than 10 results, make the end 10, and create multiple pages.
		if ($end > $resultNum)
		{
			$end = $resultNum;
		}

		//if there is more than one page, the $start variable will say what result to start at
		if (isset($_GET['start']))
		{
			//get the start number
			$start = $_GET['start'];

			//find the number of pages
			$pages = ceil($trueend / $resultNum);
			//find out what page the user is currently on
			$currentpage = ($start / $resultNum) + 1;
			//make a variable that says what page the user is on
			$text195017345 = 'Page ' . $currentpage;

			//I don't remember what this code does
			if ($start + $resultNum <= $trueend)
			{
				$end = $start + $resultNum;
			}
			else if ($start + $resultNum > $trueend)
			{
				$end = $trueend;
			}
		}
		else
		{
			$start = 0;
			//find out how many pages there are
			$pages = ceil($trueend / $resultNum);
			//find out what page the user is currently on
			$currentpage = ($start / $resultNum) + 1;
			//make a variable that says what page the user is on
			$text195017345 = 'Page ' . $currentpage;
		}

		//if there is only one result, display "Result" instead of "Results"
		if ($trueend <= 1) $results_result = 'Result';
		else $results_result = 'Results';

		//if there results echo the search again box and search info
		if ($end >= 1)
		{
			echo "<br /><form method='get' action='" . $FormAction . "' class='c599'>Search: <input type='text' name='q' size='$searchsize' maxlength='2048' value='" . htmlspecialchars($q, ENT_QUOTES) . "' />$HiddenField" . getSelectedResultText($resultNum) . "<input type='submit' value='Search' /></form>";

			if (preg_match('/Firefox/i', $_SERVER['HTTP_USER_AGENT'])) echo '<br />';

			echo '<b>' . $trueend . '&nbsp;' . $results_result . ',&nbsp;' . $text195017345 . '</b><br />Search Results for <i>' . htmlspecialchars($q, ENT_QUOTES) . '</i>:<br />';

			if ($enable_spelling && $correctedWord = SpellCheck($q_justquery)) echo '<br /><span class="spelling">Did you mean <a href="' . $ScriptURL . '?q=' . urlencode(strip_tags($prefix . $correctedWord)) . '&amp;results=' . addslashes(urlencode($resultNum)) . $Site2Search . '">' . $prefix . $correctedWord . '</a>?</span><br />';
		}
		else if ($end <= 0)
		{
			//if there aren't any results say so
			echo "<br /><form method='get' action='" . $FormAction . "' class='c599'>Search: <input type='text' name='q' size='$searchsize' maxlength='2048' value='" . htmlspecialchars($q, ENT_QUOTES) . "' />$HiddenField" . getSelectedResultText($resultNum) . "<input type='submit' value='Search' /></form><br />";

			if ($enable_spelling && $correctedWord = SpellCheck($q_justquery)) echo '<span class="spelling">Did you mean <a href="' . $ScriptURL . '?q=' . urlencode(strip_tags($prefix . $correctedWord)) . '&amp;results=' . addslashes(urlencode($resultNum)) . $Site2Search . '">' . $prefix . $correctedWord . '</a>?</span><br />';

			echo "No search results found for <i>" .  htmlspecialchars($q, ENT_QUOTES) . "</i>.";
		}

		//two variables that I didn't have to put in,
		//I could have just replaced the $startplus10 with "($start+10)"
		//and I could have replaced the $startminus10 with "($start-10)"
		$startplus10 = $start + $resultNum;
		$startminus10 = $start - $resultNum;
		$endplus10 = $end + $resultNum;

		//loop through all the results
		while ($start < $end)
		{
			//get the page information
			$title=mysql_result($result,$start,"title");
			//$description=mysql_result($result,$start,"description");
			$text=mysql_result($result,$start,"text");
			//$relevance=mysql_result($result,$start,"Relevance");

			//file size
			$filesize = ceil(mb_strlen($text) / 1024);

			//get rid of linebreaks and stuff
			$text = preg_replace('/(\n|\r)/', ' ', $text);
			//get rid of javascript/styles
			$text = preg_replace("/\<(script|style).*?<\/(script|style)\>/i", "", $text);
			//get only the body text
			$text = (preg_match('/<body[^>]*>(.*?)<\/body>/si', $text, $regs)?$body=$regs[1]:$text);
			//for Floft, get the main text of the page
			$text = (preg_match('/\<!--body of the page \(begin\)--\>(.*)\<!--body of the page \(end\)--\>/si', $text, $regs)?$body=$regs[1]:$text);
			//get rid of HTML code
			$text = strip_tags($text);
			//get rid of HTML entities, you'll encode them later
			$text = html_entity_decode($text);
			//get rid of extra spaces
			$text = preg_replace("/\s+/", " ", $text);

			$snippet=null;
			$words = explode(" ", $q_justquery);
			//get snippet from the text of the page
			foreach ($words as $word)
			{
				if (eregi($word, $text))
				{
					//$snippet=preg_replace("/(A-Za-z0-9){20}" . $word . "/i", "$1", mysql_result($result,$start,"text"));

					$loc = stripos($text, $word);
					$look = 100;
					$length = $look + $look + strlen($word);

					if ($loc > $look)
					{
						$start_loc = $loc - $look;
						$snippet = "..." . substr($text, $start_loc, $length);
					}
					else
					{
						$start_loc = 0;
						$snippet = substr($text, $start_loc, $length);
					}

					$snippet = htmlspecialchars($snippet);
				}
			}

			$pageId=mysql_result($result,$start,"url");
			$address2 = $pageId;
			$address2 = preg_replace('/(?<!&amp;)&/', '&amp;', $address2);
			$the_address = $address2;

			//shorten Title and URL if nessesary
			if (strlen($title) > 70) $title = substr($title, 0, 70) . "...";
			if (strlen($the_address) > 100) $the_address = substr($the_address, 0, 100) . "...";

			//make words in snippet and url bold if they were in the query
			foreach($words as $word)
			{
				if (strlen($word) > 2 && NotCommon($word))
				{
					$word = preg_replace("/\//", "\/", preg_quote($word));
					$snippet = preg_replace("/(" . $word . "\s+)/i", "<b>$1</b>", $snippet);
					$title = preg_replace("/(" . $word . "\s+)/i", "<b>$1</b>", $title);
					$the_address = preg_replace("/(" . $word . "\s+)/i", "<b>$1</b>", $the_address);

					//highlight words w/ es, s, or other suffix at the end
					$suffix_1 = substr($word, -1, 1); $no_suffix_1 = substr($word, 0, -1);
					$suffix_2 = substr($word, -2, 2); $no_suffix_2 = substr($word, 0, -2);
					$suffix_3 = substr($word, -3, 3); $no_suffix_3 = substr($word, 0, -3);
					$suffix_4 = substr($word, -4, 4); $no_suffix_4 = substr($word, 0, -4);

					if ($suffix_1 == 's' && $suffix_2 != 'es' && $suffix_4 != 'ness') $snippet = preg_replace("/(" . $no_suffix_1 . "\s+)/i", "<b>$1</b>", $snippet);
					else if ($suffix_2 == 'ly' || $suffix_2 == 'ed' || $suffix_2 == 'es') $snippet = preg_replace("/(" . $no_suffix_2 . "\s+)/i", "<b>$1</b>", $snippet);
					else if ($suffix_3 == "\'s") $snippet = preg_replace("/(" . $no_suffix_3 . "\s+)/i", "<b>$1</b>", $snippet);
					else if ($suffix_4 == 'ness') $snippet = preg_replace("/(" . $no_suffix_4 . "\s+)/i", "<b>$1</b>", $snippet);

					if ($suffix_1 == 's' && $suffix_2 != 'es' && $suffix_4 != 'ness') $the_address = preg_replace("/(" . $no_suffix_1 . "\s+)/i", "<b>$1</b>", $the_address);
					else if ($suffix_2 == 'ly' || $suffix_2 == 'ed' || $suffix_2 == 'es') $the_address = preg_replace("/(" . $no_suffix_2 . "\s+)/i", "<b>$1</b>", $the_address);
					else if ($suffix_3 == "\'s") $the_address = preg_replace("/(" . $no_suffix_3 . "\s+)/i", "<b>$1</b>", $the_address);
					else if ($suffix_4 == 'ness') $the_address = preg_replace("/(" . $no_suffix_4 . "\s+)/i", "<b>$1</b>", $the_address);

					if ($suffix_1 == 's' && $suffix_2 != 'es' && $suffix_4 != 'ness') $title = preg_replace("/(" . $no_suffix_1 . "\s+)/i", "<b>$1</b>", $title);
					else if ($suffix_2 == 'ly' || $suffix_2 == 'ed' || $suffix_2 == 'es') $title = preg_replace("/(" . $no_suffix_2 . "\s+)/i", "<b>$1</b>", $title);
					else if ($suffix_3 == "\'s") $title = preg_replace("/(" . $no_suffix_3 . "\s+)/i", "<b>$1</b>", $title);
					else if ($suffix_4 == 'ness') $title = preg_replace("/(" . $no_suffix_4 . "\s+)/i", "<b>$1</b>", $title);
				}
			}

			if ($snippet == "..." || $snippet == null)
			{
				if (substr($text, 0, 210) != null)
				{
					if (substr(trim($text), -3, 3) == '...') $snippet = substr($text, 0, 210);
					else $snippet = substr($text, 0, 210) . "...";
				}
				else $snippet = null;
			}
			else $snippet .= "...";
			$snippet = wordwrap($snippet, 95, "<br />", true);

			$the_address = replace_sites($the_address);
			$the_address = preg_replace("/https?:\/\//i", "", $the_address, 1);
			$address2 = preg_replace("/http:\/\/[^\/]*/i", "", $address2, 1);
			//the cache link
			$cache = "<a href=\"" . $ScriptURL . "?cache=" . urlencode($pageId) . "\" class='cache'>Cache</a>";

			//this code is to make the description not be too long, and to not have the formatting
			/*$description = preg_replace('/\<br \/\>/', '
			', $description);
			$description = preg_replace('/\<br\>/', '
			', $description);
			$description = strip_tags($description);
			$description = substr($description, 0, 200);
			$description = preg_replace('/[\r]/', '&nbsp;', $description);
			$description = preg_replace('/[\t]/', '&nbsp;', $description);
			$description = preg_replace('/[\n]/', '&nbsp;', $description);
			$description .= '<font size="4">...</font>';*/

			//echo the result
			//if ($snippet == null) echo "<p><a href='$address2' class='title'>$title</a><br /><span class='link'>$the_address - ".$filesize."k - $cache</span></p>";
			//else echo "<p><a href='$address2' class='title'>$title</a><br />$snippet<br /><span class='link'>$the_address - ".$filesize."k - $cache</span></p>";
			if ($snippet == null) echo "<p><a href='$address2' class='title'>$title</a><br /><span class='link'>$the_address - ".$filesize."k</span></p>";
			else echo "<p><a href='$address2' class='title'>$title</a><br />$snippet<br /><span class='link'>$the_address - ".$filesize."k</span></p>";

			$start++;
		}

		$q = urlencode($q);

		//if there is more than one page, echo links to the other pages
		if ($trueend > $resultNum)
		{
			//if this is the first page
			if ($start <= $resultNum && $trueend > $resultNum)
			{
				echo '<font color="gray">Previous</font>&nbsp;';

				//if this code helps make only 11 page numbers be displayed on each page

				//starting #
				$pages2 = 1;
				//ending #
				$pages4 = 11;
				//make the links go to the right number
				$pages3 = ($pages2-1)*$resultNum;

				//loop through the number of pages and echo them, like 1 2 3... they are links to other pages
				while ($pages2 <= $pages && $pages2 <= $pages4)
				{
					if ($pages2 != $currentpage) echo '&nbsp;<a href="' . $ScriptURL . '?q=' . $q . '&amp;start=' . $pages3 . '&amp;results=' . addslashes(urlencode($resultNum)) . $Site2Search . '" class="pages">' . $pages2 . '</a>&nbsp;';
					else echo '&nbsp;<span class="currentPage">' . $pages2 . '</span>&nbsp;';

					$pages2++;
					$pages3 = $pages3 + $resultNum;
				}

				echo '&nbsp;<a href="' . $ScriptURL . '?q=' . $q . '&amp;start=' . $startplus10 . '&amp;results=' . addslashes(urlencode($resultNum)) . $Site2Search . '" class="pages">Next</a>';
			}
			else if ($start > $resultNum && $end + 1 <= $trueend)
			{
				//if this is not the first or last page
				echo '<a href="' . $ScriptURL . '?q=' . $q . '&amp;start=' . $startminus10 . '&amp;results=' . addslashes(urlencode($resultNum)) . $Site2Search . '" class="pages">Previous</a>&nbsp;';

				//if this code helps make only 11 page numbers be displayed on each page
				if (($currentpage+6) > $pages && ($currentpage-10) > 0)
				{
					//find what number to start with
					$pages2 = $pages - 10;
					//ending #
					$pages4 = $pages;
				}
				else if ($currentpage > 5)
				{
					//find what number to start with
					$pages2 = $currentpage-5;
					//find what number to end with
					$pages4 = $currentpage+5;
				}
				else
				{
					//starting #
					$pages2 = 1;
					//ending #
					$pages4 = 11;
				}
				//make the links go to the right number
				$pages3 = ($pages2-1)*$resultNum;

				//loop through the number of pages and echo them, like 1 2 3... they are links to other pages
				while ($pages2 <= $pages && $pages2 <= $pages4)
				{
					if ($pages2 != $currentpage) echo '&nbsp;<a href="' . $ScriptURL . '?q=' . $q . '&amp;start=' . $pages3 . '&amp;results=' . addslashes(urlencode($resultNum)) . $Site2Search . '" class="pages">' . $pages2 . '</a>&nbsp;';
					else echo '&nbsp;<span class="currentPage">' . $pages2 . '</span>&nbsp;';

					$pages2++;
					$pages3 = $pages3 + $resultNum;
				}

				echo '&nbsp;<a href="' . $ScriptURL . '?q=' . $q . '&amp;start=' . $startplus10 . '&amp;results=' . addslashes(urlencode($resultNum)) . $Site2Search . '" class="pages">Next</a>';
			}
			else if ($start > $resultNum && $end <= $trueend)
			{
				//if this is the last page
				echo '<a href="' . $ScriptURL . '?q=' . $q . '&amp;start=' . $startminus10 . '&amp;results=' . addslashes(urlencode($resultNum)) . $Site2Search . '" class="pages">Previous</a>&nbsp;';

				//if this code helps make only 11 page numbers be displayed on each page

				//find what number to start with
				if (($currentpage - 11) > 0)
					$pages2 = $currentpage-11;
				else
					$pages2 = 1;

				//make the links go to the right number
				$pages3 = ($pages2-1)*$resultNum;

				//loop through the number of pages and echo them, like 1 2 3... they are links to other pages
				while ($pages2 <= $pages)
				{
					if ($pages2 != $currentpage) echo '&nbsp;<a href="' . $ScriptURL . '?q=' . $q . '&amp;start=' . $pages3 . '&amp;results=' . addslashes(urlencode($resultNum)) . $Site2Search . '" class="pages">' . $pages2 . '</a>&nbsp;';
					else echo '&nbsp;<span class="currentPage">' . $pages2 . '</span>&nbsp;';

					$pages2++;
					$pages3 = $pages3 + $resultNum;
				}

				echo '&nbsp;<font color="gray">Next</font>';
			}
		}
	}
	else
	{
		//number of results per page
		if(isset($_REQUEST['results']) && is_numeric($_REQUEST['results']) && $_REQUEST['results'] > 0 && $_REQUEST['results'] <= 500) $resultNum = stripslashes(rawurldecode($_REQUEST['results']));
		else $resultNum = 10;

		//if the user hasn't searched for anything, display the search form
		echo "<br /><form method='get' action='" . $FormAction . "' class='c599'>Search: <input type='text' name='q' size='$searchsize' maxlength='2048' />$HiddenField" . getSelectedResultText($resultNum) . "<input type='submit' value='Search' /></form>";
	}

	//echo "</body></html>";
	echo "</div>";
}
?>
