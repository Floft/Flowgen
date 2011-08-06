<?php
/* Look at search.php, check.php, and urls.php for examples.
   Usage:

require_once "spider.php";

class whatever extends Spider
{
	private $blah;

	public function __construct($startURLs, $replaceURLs=array(), $blah=array())
	{
		parent::__construct($startURLs, $replaceURLs);
		$this->blah = $blah;
	}

	public function use_data($url, $text, $html)
	{
		echo "URL: $url\n";
	}

	public function cleanup()
	{
		//finished
	}
}

$spider = new whatever($startURLs, $replaceURLs, $blah);
$spider->run();
*/

class Spider
{
	private $urls=array();
	private $visited=array();
	private $robots=array();
	private $UserAgent;
	private $maxForwards=10;
	private $startURLs;
	private $replaceURLs;
	
	public function __construct($startURLs, $replaceURLs=array(), $UA="Flowgen/1.0 (http://floft.net/)") {
		if (!is_array($startURLs))
			die("Error: \$startURLs must be an array!\n");
		if (is_array($UA))
			die("Error: \$UA must not be an array!\n");
		
		$this->startURLs=$startURLs;
		$this->replaceURLs=$replaceURLs;
		$this->UserAgent=$UA;
		ini_set('user_agent', $UA);
	}
	
	public function run() {
		foreach ($this->startURLs as $url) {
			//add to robots list
			$this->visited[]=$url;
			$this->robots($url);
			
			$returnurl=$this->parse($url);
			
			if ($returnurl != $url)
				$this->visited[]=$returnurl;
		}
		
		while (count($this->urls) > 0) {
			$url=trim(array_pop($this->urls));
	
			if (!in_array($url,$this->visited)) {
				$this->visited[]=$url;
				
				$returnurl=$this->parse($url);
	
				if ($returnurl != $url)
					$this->visited[]=$returnurl;
			}
		}
		
		$this->cleanup();
	}
	
	public function use_data($url,$title,$text) {
		die("Replace Spider::use_data() with something else.");
	}
	
	public function cleanup() {
		die("Replace Spider::cleanup() with something else.");
	}
	
	private function parse($url) {
		$images=array();
		$canonical=false;
		list($url,$html) = $this->load($url,true);
		
		//parse HTML
		$dom=new DOMDocument();
		@$dom->loadHTML($html);
		
		//canonical
		//<link rel="canonical" href="http://www.floft.net/wiki/Scripts/Flotografy.html" />
		foreach ($dom->getElementsByTagName('link') as $item) {
			$rel=@$item->getAttribute('rel');
	
			if (strtolower($rel)=="canonical") {
				$href=$this->absolutify($url,$item->getAttribute('href'));
	
				if ($href!=$url) {
					similar_text($this->load($href),$html,$diff);
	
					if ($diff > 90) {
						$canonical=true;
						$url = $href;
						break;
					}
				}
			}
		}
		
		//need the query string? (only if not using canonical)
		$url_noq=preg_replace("/[#?].*?$/i","",$url);
		if ($url_noq!=$url&&$canonical==false) {
			$noq=$this->load($url_noq);
			similar_text($noq,$html,$diff);
	
			//use the one without the query string
			if ($diff > 92) {
				$url=$url_noq;
			}
		}
		
		//get URLs
		$this->getURLs($url,$dom);
	
		//get image ALTs
		foreach ($dom->getElementsByTagName('img') as $item) {
			$alt=@$item->getAttribute('alt');
	
			if (!empty($alt)) {
				$images[]="$alt";
			}
		}
		
		//get Text
		$text=$this->getText($html);
		
		if (count($images) > 0)
			$text.=" ".implode(" ",$images);
		
		//up to the programmer from now on
		$this->use_data($url,$text,$html);
		
		return $url;
	}

	public function absolutify($from,$url) {
		$url=trim($url);
		$from=trim($from);
		
		if (!preg_match("#^https?://#i",$url)) {
			$http=preg_replace("#(https?://).*#i",'\1',$from);
			$http=(empty($http))?"http://":$http;
			$siteurl=preg_replace("#https?://([^/]+).*#i",'\1',$from);
			$from=preg_replace("#(https?://)?[^/]+(.*)#i",'\2',$from);

			if (substr($url,0,1) == "/") {
				$url="$siteurl/$url";
				$url=str_replace("//","/",$url);
				$url="$http$url";
			} else if (substr($url,0,3) == "../") {
				$from=preg_replace("#/[^/]*$#","",$from);
				$from=preg_replace("#(^/|/$)#","",$from);
				$parts=explode("/",$from);
				preg_match_all('/..\//',$url,$matches);
				//get all but the # of ../'s
				$num=count($parts)-count($matches[0]);
				$str="";
	
				for($i=0;$i<$num;$i++) {
					$part=$parts[$i];
	
					if($part!=""&&$part!="..")
						$str.="$part/";
				}
				
				$url=str_replace("../","",$url);
				$url="$http$siteurl/$str$url";
			} else {
				$parts=explode("/",$from);
				//get all but 1
				$num=count($parts)-1;
				$str="";
	
				for($i=0;$i<$num;$i++) {
					$part=$parts[$i];
	
					if($part!=""&&$part!="..")
						$str.="$part/";
				}
				
				$url=str_replace("./","",$url);
				$url="$http$siteurl/$str$url";
			}
		}
		return trim($url);
	}
    
	public function right_type($url) {
		$bad=array("7z", "aif", "aiff", "asf", "atom", "au", "aup", "avi", "bin", "blend", "bmp", "bz2", "cr2", "dcr", "dng", "doc", "dv", "exe", "exr", "flac", "flv", "gif", "gz", "ico", "iso", "jar", "jpeg", "jpg", "m3u", "m4v", "mid", "midi", "mmpz", "mov", "mp2", "mp3", "mp4", "mpa", "mpeg", "mpg", "mpz", "nef", "ogg", "pbm", "pcx", "pdf", "pef", "png", "pnm", "ppm", "ra", "ram", "rgb", "sr2", "svg", "swf", "tar", "tga", "tif", "tiff", "ttf", "vrml", "wav", "wma", "wmp", "wmv", "xcf", "xz", "zip");

		$return=true;
		$url=strtolower($url);

		foreach ($bad as $item) {
			$len=strlen($item);
			
			if (substr($url,-$len,$len)==$item) {
				$return=false;
				break;
			}
		}

		return $return;
	}
	
	public function right_site($url) {
		$return = false;
		$sites=$this->startURLs;
		
		foreach ($sites as $site) {
			$site=(substr($site,-1,1)=="/")?"$site":"$site/";
			if (preg_match("#^$site#i",$url)) {
				$return = true;
				break;
			}
		}
		
		return $return;
	}
	
	public function replace_sites($url,$reverse=false) {
		$replace=$this->replaceURLs;
		$modurl=$url;
	
		foreach ($replace as $site) {
			if ($reverse)
				$modurl=str_replace($site[1],$site[0],$modurl);
			else
				$modurl=str_replace($site[0],$site[1],$modurl);
		}
	
		return $modurl;
	}
	
	public function in_robots($url) {
		$return=false;
		$host = preg_replace("#https?://([^/]+).*#i",'\1',$url);
		//$url_nohost=preg_replace("#^https?://[^/]+(.*)$#im",'\1',$url);
		//$url_nohost=preg_replace("/[#?].*?$/i","",$url_nohost);
	
		if (isset($this->robots["$host"])) {
			foreach ($this->robots["$host"] as $disallow) {
				if (substr($url,0,strlen($disallow))==$disallow) {
					$return=true;
					break;
				}
			}
		}
		
		return $return;
	}
	
	public function robots($baseurl) {
		$site = preg_replace("#https?://([^/]+).*#i",'\1',$baseurl);
		$disallow=array();
		$starturl="http://$site";
		$robots=$this->load("$starturl/robots.txt");
	
		if (strlen($robots) > 0) {
			//disallow
			preg_match_all("/^Disallow:(.*)$/im",$robots,$match);
			$num=count($match[1]);
			
			for ($i=0;$i<$num;$i++) {
				$item=trim($match[1][$i]);
				
				if ($item!="/") {
					if (substr($item,0,1) == "/")
						$disallow[]=$this->absolutify($starturl,$item);
					else if (strpos($item,"http://") === false)
						$disallow[]=$this->absolutify($starturl,"/".$item);
				}
			}
			
			//always block robots.txt... in case some guy linked to it on his site
			$disallow[]="$starturl/robots.txt";

			$this->robots["$site"]=$disallow;
			
			//sitemap
			preg_match_all("/^Sitemap:(.*)$/im",$robots,$match);
			$num=count($match[1]);
			
			for ($i=0;$i<$num;$i++) {
				$sitemap=$this->load($this->absolutify($starturl,$match[1][$i]));
				$sitemapurls = explode("\n",$sitemap);
				
				foreach ($sitemapurls as $line) {
					$this_url=$this->absolutify($starturl,$line);
					if (!in_array($this_url, $this->urls) && $this->right_type($this_url) && $this->right_site($this_url)) {
						$this->urls[] = $this_url;
					}
				}
			}
		}
	}
	
	public function getText($html) {
		$text=trim(
				  str_replace("\n"," ",
				  str_replace("\t"," ",
				  preg_replace("/&[^;]+;/","",
				  strip_tags(
				  str_replace("<"," <",
				  str_replace(">","> ",
				  preg_replace("/.*<body[^>]*>(.*)<\/body>.*/is",'\1',
				  preg_replace("/<script[^>]*>.*?<\/script>/is",'',
				  //preg_replace("/.*<div class=\"date\">[^<]+<\/div>(.*)<div class=\"footer\".*/is",'\1',$html)
				  preg_replace("/.*<div class=\"inner\">(.*?)<div class=\"footer\".*/is",'\1',$html)
				  )))))))));
			
			while(strpos($text, "  ") !== false)
				$text=str_replace("  "," ",$text);
		
		return $text;
	}
	
	public function getURLs($url,$dom) {
		foreach ($dom->getElementsByTagName('a') as $item) {
			$new_url=$item->getAttribute('href');
			
			if (strpos($new_url, "#") !== false)
				$new_url=preg_replace("/#.*?$/i","",$new_url);
			
			$new_url=$this->absolutify($url,$new_url);
	
			if (!empty($new_url) &&
				$this->right_type($new_url) &&
				$this->right_site($new_url) &&
				!in_array($new_url,$this->urls) && 
				!in_array($new_url,$this->visited)) {
				$this->urls[] = $new_url;
			}
		}
	}
	
	
	public function load($uri,$return_url=false,$times=0) {
		$uri_new=$this->replace_sites($uri);
		
		//load it
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri_new); 
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->UserAgent);
		$headers=array();
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		//header vs. content
		if($info['http_code'] == 404 || $info['http_code'] == 403 || $info['http_code'] == 500) {
			$body = "";
		} else {
			//from: http://www.bin-co.com/php/scripts/load/
			$header_text = substr($response, 0, $info['header_size']);
			$body = substr($response, $info['header_size']);	
			
			foreach(explode("\n",$header_text) as $line) {
				$parts = explode(": ",$line);
				if(count($parts) == 2) {
					if (isset($headers[$parts[0]])) {
						if (is_array($headers[$parts[0]])) $headers[$parts[0]][] = chop($parts[1]);
						else $headers[$parts[0]] = array($headers[$parts[0]], chop($parts[1]));
					} else {
						$headers[$parts[0]] = chop($parts[1]);
					}
				}
			}
		}
		
		//cool stuff
		$code = $info['http_code'];
		$url=$this->replace_sites($info['url'],true);
		
		$location="";
		if (isset($headers["Location"]))
			$location=$headers["Location"];
		else if (isset($headers["location"]))
			$location=$headers["location"];
		
		//forward
		if ($code==301 || $code==302) {
			if ($times<=$this->maxForwards && !empty($location)) {
				$location=$this->absolutify($url,$location);
				
				if ($return_url)
					list($url,$body)=$this->load($location,true,($times+1));
				else
					$body=$this->load($location,false,($times+1));
			} else {
					$body="";
			}
		}
		
		if ($return_url)
			return array($url,$body);
		else
			return $body;
	}
}
?>
