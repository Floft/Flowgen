<?php
$startURLs=array(
	"http://www.floft.net/"
	/*"http://pba.floft.net/",
	"http://mom.floft.net/",
	"http://cardinals.floft.net/",
	"http://a.floft.net/",
	"http://u.floft.net/",
	"http://g.floft.net/",
	"http://garotbot.floft.net/",
	"http://music.floft.net/",
	"http://pathfinders.floft.net/",
	"http://thriftshop.floft.net/"*/
);
//if this is hosted locally, load the local version to speed things up
$replaceURLs=array(
	array("www.floft.net", "localhost")
);
$mysql=array(
	"localhost", //host
	"user", //user
	"pass", //pass
	"db", //db
	"search" //table
);
?>
