<?php
/*
 This script adds all the text of a website into a search database.
*/

require_once "spider.php";
require_once "settings.php";

class UpdateSearch extends Spider
{
	private $sql;
	private $table;
	
	public function __construct($startURLs,$replaceURLs,$mysql=array())
	{
		parent::__construct($startURLs,$replaceURLs);
		
		//connect to database
		if (count($mysql) != 5)
			die("Error: \$mysql = array(host, user, pass, db, table)\n");
		
		$db=mysql_connect($mysql[0], $mysql[1], $mysql[2]) or die ('I cannot connect to the database because: ' . mysql_error());
		mysql_select_db($mysql[3]);
		
		$this->table=$mysql[4];
		$this->sql=$db;
	}
	
	public function use_data($url,$text,$html)
	{
		$table=$this->table;

		//get Title
		preg_match("/\<title\>([^\<]+)\<\/title\>/",$html,$matches);

		if (count($matches) > 1)
			$title=$matches[1];
		else
			$title="Untitled";

		$time=time();
		$mysql_url=mysql_escape_string($url);
		$title=mysql_escape_string($title);
		$text=mysql_escape_string($text);
		$inrobots=$this->in_robots($url);
		
		if ($text!="" && !$inrobots) {
			$result=mysql_query("Select id from `$table` where url = '$mysql_url'");

			if (mysql_numrows($result) > 0) {
				echo "Notice: updating\t$mysql_url\n";
				mysql_query("Update `$table` SET timestamp='$time', title='$title', text='$text' where url='$mysql_url'");
			} else {
				echo "Notice: inserting\t$mysql_url\n";
				mysql_query("Insert into `$table` (timestamp,url,title,text) values ('$time','$mysql_url','$title','$text')");
			}
		} else {
			$result=mysql_query("Select id from `$table` where url = '$mysql_url'");

			if (mysql_numrows($result) > 0) {
				echo "Notice: deleting\t$mysql_url\n";
				mysql_query("Delete from `$table` where url='$mysql_url'");
			} else {
				if ($inrobots)
					echo "Notice: skipping\t$mysql_url\n";
				else
					echo "Notice: blank\t\t$mysql_url\n";
			}
		}
	}
	
	public function cleanup()
	{
		mysql_close();
	}
}

$spider = new UpdateSearch($startURLs, $replaceURLs,$mysql);
$spider->run();
?>
