<?
/*
Plugin Name: 404 SEO Plugin
Version: 2.1
Plugin URI: http://www.404plugin.com
Description: Give yourself an SEO boost! Replaces 404 error with suggestions of related pages on the site. After installing, <a href='theme-editor.php'>go here</a>, and add &lt;?custom404_print404message();?&gt; to your 404 Template where you want the suggestions to appear. Version 2.1 fixes stability issues and gives cleaner, improved search results.
Author: 404 Plugin
Author URI: http://www.404plugin.com
*/

function custom404_print404message()
{
  global $custom404_server;
  global $custom404_missingpagestr;

  $custom404_missingpagestr = custom404_getmissingpagestr();
  $custom404_missingpagestr = str_replace(' ', '+', $custom404_missingpagestr);
  $custom404_headline =  'Related pages on this site';
  $custom404_server[] =  "+site%3A$_SERVER[SERVER_NAME]";
  //new version
  $custom404_googleresults = custom404_getgoogleresults($custom404_missingpagestr, $_SERVER[SERVER_NAME]);
  
  if (!($custom404_googleresults))
  {
      $custom404_headline = 'No local results found. From the web:';  
	
      $custom404_googleresults = custom404_getgoogleresults($custom404_missingpagestr);
      $custom404_yahooresults = custom404_getyahooresults($custom404_missingpagestr);	  
	  $custom404_yahooresultsarr = explode("\n", $custom404_yahooresults);
	  $custom404_formattedyahooresults = '';
	  foreach ($custom404_yahooresultsarr as $custom404_yahooresult)
	  { 
	    if (strlen(trim($custom404_yahooresult)))
	      if (strpos($custom404_googleresults, $custom404_yahooresult) === false && 
		  strpos($custom404_googleresults, str_replace('rel=nofollow', '', $custom404_yahooresult)) === false )
		    $custom404_formattedyahooresults .= "$custom404_yahooresult\n";
	  }
	  
      if (!($custom404_googleresults) && !($custom404_formattedyahooresults))
      {
          $custom404_googleresults = '<br>No results found on the web.';
      }
      else
      {
	      $custom404_googleresults = ($custom404_googleresults == '0') ? '' : $custom404_googleresults;
	      $custom404_formattedyahooresults = ($custom404_formattedyahooresults == '0') ? '' : $custom404_formattedyahooresults; 
          $custom404_googleresults = utf8_encode($custom404_googleresults );
		  $custom404_formattedyahooresults = utf8_encode($custom404_formattedyahooresults );
	  }
  }
  $custom404_googleresults = $custom404_headline . "<br>" . $custom404_googleresults;
  
  $custom404_formattedquerystring = $_SERVER[REDIRECT_QUERY_STRING] ? "?$_SERVER[REDIRECT_QUERY_STRING]":"";
  print "<iframe name='404pluginV15' src='http://www.404plugin.com/track.php' scrolling=no height=1 width=1 marginheight=0 marginwitdh=0 frameborder=0></iframe>
   <table border=0 width=100%>
  <tr>
   <td valign=top>
   <h3>Suggestions</h3>
   <!--Google results-->
   $custom404_googleresults
  
   <!--Yahoo results-->
   
   $custom404_formattedyahooresults
   
   </td></tr>
   </table>
   
   <!--404Plugin 2.0 released 4.2011-->";
}

function custom404_findurls($Page)
{
  preg_match_all ("/a[\s]+[^>]*?href[\s]?=[\s\"\']+".
                    "(.*?)[\"\']+.*?>"."([^<]+|.*?)?<\/a>/",
                    $Page, &$matches);
  $matches = $matches[1];
  return $matches;
}

function custom404_sendheader()
{
 if(is_404()){
     header('HTTP/1.0 200 OK');
     header('Cache-Control: max-age=360000, public');
     header('Pragma: public');
     $offset = 60 * 60 * 24 * 14; //2 weeks
     $ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
     header($ExpStr);
     }
}

function custom404_404title($title)
{
  if(is_404())
  {
      $title = custom404_getmissingpagestr();
  }
  return $title;
}

function custom404_getmissingpagestr()
{
  $custom404_missingpagestr = urldecode($_SERVER[REDIRECT_URL]);
  if (strlen($custom404_missingpagestr) - strrpos($custom404_missingpagestr, '.') <= 5)
  {
    $custom404_filename = substr($custom404_missingpagestr, 0, strrpos($custom404_missingpagestr, '.'));
    $custom404_ext = str_replace("$custom404_filename.", '', $custom404_missingpagestr);
    $custom404_missingpagestr = $custom404_filename;
  }
  $custom404_missingpagestr .= ' ' . urldecode($_SERVER[REDIRECT_QUERY_STRING]);
  $custom404_replacewithspace = array( '-', '/', '_', '&', '?', '#', '+', '=');
  $custom404_missingpagestr = str_replace($custom404_replacewithspace, ' ', $custom404_missingpagestr);
  $custom404_missingpagestr = trim($custom404_missingpagestr);
  return $custom404_missingpagestr;
}

function custom404_loadpage($url)
{
  $UserAgents = array("Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1",
                      "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)");
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_USERAGENT, $UserAgents[array_rand($UserAgents)]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
  $ret = curl_exec($ch);
  curl_close($ch);
  if (!strlen($ret)) $ret = ("An error occured.");
  return $ret;
}

function custom404_gettextbetweentags ($start, $end, $string)
{
    $startpos = $start ? strpos($string, $start) + strlen($start) : 0;
    $matchlength =  strpos($string, $end, $startpos) - $startpos ? strpos($string, $end, $startpos) - $startpos : strlen($string);
    $foundstring = trim (substr($string, $startpos, $matchlength));
    return $foundstring;
}

function custom404_getgoogleresults($term, $domain=0)
{ 
  if ($domain) {$siteclause = "+site%3A$domain";}
  
  $position = 1;
  libxml_use_internal_errors(true);
  $allresults = array();
  $pages = 2;
  $textresults='';
  $totalfound = 0;
 
  for ($currentpage=1;$currentpage<=$pages;$currentpage++)
  {  
    $pagedom = new DOMDocument();
    $start = ($currentpage - 1) * 10;
    $startclause = $start ? "&start=$start" : '';
    $googleurl = "http://www.google.com/search?hl=en&source=hp&q=" . urlencode($term) . $siteclause . $startclause . "&gl=us&pws=0";
	//print $googleurl;
	$pagedom->loadHTMLFile($googleurl);
    
    $spans = $pagedom->getElementsByTagName("span");
    foreach($spans as $span)
      if ($span->getAttribute('class') == 'f' || $span->getAttribute('class') == 'gl')
        $span->nodeValue = '';
    $divs = $pagedom->getElementsByTagName("div");
    foreach($divs as $div)
      if ($div->getAttribute('class') == 'osl')
        $div->nodeValue = '';
        
    $results = $pagedom->getElementsByTagName("h3");
    
	foreach ($results as $result)
    {
      if ($result->getAttribute("class") == "r")
      {
        $children = $result->childNodes;
        foreach ($children as $child)
        {
          if (is_object($child) && $child->getAttribute("class") == "l" && $position <=10)
          { //initialize variables
            $anchor = $child->nodeValue;
            $url = $child->getAttribute("href");
            $urlparts = explode('/', $url);
            $domain = $urlparts[2];
            
            //description from google search results
            $parent = $result->parentNode;
            $siblings = $parent->childNodes;
            $description = '';
            foreach($siblings as $sibling)
            { //var_dump($uncle);
                if (@$sibling->getAttribute("class") == "s")
                {
                  $shorturl = str_replace('http://', '', $url);  
                  $description = custom404_gettextbetweentags("STARTHERE", $shorturl, "STARTHERE$sibling->nodeValue");              
                }  
            }
			$nofollow = $position == 1 ? '' : 'rel=nofollow';
			$textresults .=  "<b><a $nofollow href='$url'>$anchor</a></b><br>";
			
			$position++; $totalfound++;
		  }
		}
	  }
	}
  }  
  $textresults = $totalfound ? $textresults : 0;
  
  return $textresults;
}

function custom404_getyahooresults($key_words, $num_results=10)
{
    global $custom404_server;
    $results = '';
    $terms=str_replace(' ','+',$key_words);
    $pages=$num_results/10;

    for($i=0;$i<$pages;$i++){

    	$b=10*$i+1;
    	$serpurl="http://search.yahoo.com/search?p=$terms&pstart=1&b=$b";
    	$page_results=custom404_getyahooserp($serpurl);
    	if ($page_results)
		  $results .= $page_results;
    }
    if (strlen($results) == 0) $results=0;
    return $results;
}

function custom404_getyahooserp($serpurl)
{ 
    $position = 11;
    $serpOriginal = custom404_loadpage($serpurl);
    $results='';
    if (strpos($serpOriginal,"We did not find results for") ===FALSE){
	    $serpArray = explode("<a class=\"yschttl spt\"",$serpOriginal);
	    array_shift($serpArray);

	    foreach($serpArray as $key=>$result){

		    $url='http://'.strip_tags(custom404_gettextbetweentags('<span class=url>','</span>',$result));
		    if (strpos($url,'...')){
		    	$url=custom404_gettextbetweentags('href="','"',$result);
		    	if (strpos($url,'yahoo.com/click')){
		    		$url=custom404_gettextbetweentags('?u=','&', urldecode($url) );
		    	}
		    	elseif (strpos($url,'rds.yahoo.com')){ //convert urls like        //http://rds.yahoo.com/_ylt=A0oGkmdAhmRJnqsAr9FXNyoA;_ylu=X3oDMTBydHRjbmRzBHNlYwNzcgRwb3MDMwRjb2xvA3NrMQR2dGlkAw--/SIG=137q03skj/EXP=1231411136/**http%3a//commitments.clintonglobalinitiative.org/projects.htm%3fmode=view%26rid=43102
		    		$url=custom404_gettextbetweentags('**','', urldecode($url) );
		    	}
		    }
		    $title=htmlspecialchars_decode(strip_tags(custom404_gettextbetweentags('>','</div>',$result)));
		    if (strpos($result,'<div class="abstr">'))
              $description = custom404_gettextbetweentags('<div class="abstr">','</div>',$result);
		    elseif (strpos($result,'<div class="sm-abs">'))
              $description = custom404_gettextbetweentags('<div class="sm-abs">','</div>',$result);
            // $title = str_replace('Warning: Dangerous Downloads', '', $title);
           if (!(strpos($url, 'ttp://news.search.yahoo.com')) && $position <= 20) //filter news results away
             {
			   $results .="<b><a rel=nofollow href='$url'>$title</a></b><br>\n";
	           $position++;
			 }
		}
	}
	if (strlen($results) == 0) $results = 0;
	return $results;
}


//Override SEO Plugin Title - Comment these lines out to restore.
add_option("aiosp_404_title_format", custom404_getmissingpagestr(), 'All in One SEO Plugin 404 Title Format', 'yes');
update_option('aiosp_404_title_format', custom404_getmissingpagestr());
add_action('get_header', 'custom404_sendheader');
add_filter('wp_title', 'custom404_404title');
?>