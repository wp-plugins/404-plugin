<?

/*

Plugin Name: 404 Plugin

Version: 1.1

Plugin URI: http://www.404plugin.com

Description: Replaces 404  error with suggestions of related pages on the site. After installing, <a href='theme-editor.php'>go here</a>, and add &lt;?custom404_print404message();?&gt; to your 404 Template where you want the suggestions to appear.

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

  $custom404_googleresults = custom404_gettextbetweentags('<div id=res class=med>', '<div style="height:1px;line-height:0">', custom404_enginesearch('google'));

  $custom404_googleresults = str_replace('a href="/', 'a target=_blank rel=nofollow href="http://www.google.com/', $custom404_googleresults);

  $custom404_googleresults = str_replace('Tip: Save time by hitting the return key instead of clicking on "search"', '', $custom404_googleresults);

  $custom404_googleresults = str_replace('Search Results', '', $custom404_googleresults);

  for ($count=1; $count<15; $count++)

  {

    $custom404_nofollow = $count-1 ? 'rel=nofollow' : '';

    $custom404_googleresults = str_replace("onmousedown=\"return clk(this.href,'','','res','$count','')\"", $custom404_nofollow, $custom404_googleresults);

    $custom404_googleresults = str_replace("onmousedown=\"return clk(this.href,'','','clnk','$count','')\"", $custom404_nofollow, $custom404_googleresults);

  }

  if (strpos($custom404_googleresults, 'did not match any documents.'))

  {

      $custom404_headline = 'No local results found. From the web:';

      $custom404_googleresults = custom404_gettextbetweentags('<div id=res class=med>', '<div style="height:1px;line-height:0">', custom404_enginesearch('google', 'www'));

      $custom404_googleresults = str_replace('a href="/', 'a target=_blank rel=nofollow href="http://www.google.com/', $custom404_googleresults);

      $custom404_googleresults = str_replace('Tip: Save time by hitting the return key instead of clicking on "search"', '', $custom404_googleresults);

      $custom404_googleresults = str_replace('Search Results', '', $custom404_googleresults);

      for ($count=1; $count<15; $count++)

      {

        $custom404_nofollow = $count-1 ? 'rel=nofollow' : '';

        $custom404_googleresults = str_replace("onmousedown=\"return clk(this.href,'','','res','$count','')\"", $custom404_nofollow, $custom404_googleresults);

        $custom404_googleresults = str_replace("onmousedown=\"return clk(this.href,'','','clnk','$count','')\"", $custom404_nofollow, $custom404_googleresults);

      }

      if (strpos($custom404_googleresults, 'did not match any documents.'))

      {

          $custom404_googleresults = '<ol>No results found on the web.</ol>';

      }

  }



  $custom404_yahooresults = utf8_encode(custom404_gettextbetweentags('<ol start="1">',  '</ol>', custom404_enginesearch('yahoo')));

  $custom404_yahooresults = str_replace('<h2>WEB RESULTS</h2>', '', $custom404_yahooresults);

  if (strpos($custom404_yahooresults, 'We did not find results for'))

  {

    $custom404_yahooresults = utf8_encode(custom404_gettextbetweentags('<ol start="1">',  '</ol>', custom404_enginesearch('yahoo', 'www')));

    $custom404_yahooresults = str_replace('<h2>WEB RESULTS</h2>', '', $custom404_yahooresults);



    if (strpos($custom404_yahooresults, 'We did not find results for'))

      {

          $custom404_yahooresults = 'No results found on the web.';

      }

  }



  $custom404_formattedquerystring = $_SERVER[REDIRECT_QUERY_STRING] ? "?$_SERVER[REDIRECT_QUERY_STRING]":"";



  print "<h3 align=left>The page http://$_SERVER[SERVER_NAME]$_SERVER[REDIRECT_URL]$custom404_formattedquerystring does not exist.</h3>

   <p>Searching for similar pages on this site (courtesy of <a href=http://www.404plugin.com>The 404 Plugin</a>).

   <iframe src='http://www.404plugin.com/track.php' scrolling=no height=1 width=1 marginheight=0 marginwitdh=0 frameborder=0></iframe>

   <table border=0 width=100%>

   <tr><td valign=top><h2>$custom404_headline</h2>

   <h3> Google suggestions</h3>

   <!--Google results-->

   $custom404_googleresults

   </td>

   </tr><tr>

   <td valign=top><h3>Yahoo suggestions</h3>

   <!--Yahoo results-->

   <ol>

   $custom404_yahooresults

   </ol>

   </td></tr>

   </table>";

}



function custom404_sendheader()

{

 if(is_404()){header('HTTP/1.0 200 OK');}

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



function custom404_enginesearch($engine='google', $www=0)

{

  global $custom404_server;

  global $custom404_missingpagestr;



  $header  = array();

  $referers = array('google' => 'Referer: http://www.google.com', 'yahoo' => 'http://www.yahoo.com');

  array_push ($header, $referers[$engine]);

  if($www)$custom404_server[0] = '';

  $UserAgents = array("Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1",

                      "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)");

  $URLs = array ('google' => "http://www.google.com/search?hl=en&q=$custom404_missingpagestr$custom404_server[0]&btnG=Google+Search&aq=f&oq=",

                 'yahoo' => "http://search.yahoo.com/search?p=$custom404_missingpagestr$custom404_server[0]&fr=yfp-t-501&toggle=1&cop=mss&ei=UTF-8");

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_USERAGENT, $UserAgents[array_rand($UserAgents)]);

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  curl_setopt($ch, CURLOPT_URL, $URLs[$engine]);

  curl_setopt($ch, CURLOPT_COOKIESESSION, 1);

  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

  $ret = curl_exec($ch);

  curl_close($ch);

  if (!strlen($ret)) $ret = ("An error occured when loading the $engine search results.");

  return $ret;

}



function custom404_gettextbetweentags ($start, $end, $string)

{

    $startpos = $start ? strpos($string, $start) + strlen($start) : 0;

    $matchlength =  strpos($string, $end, $startpos) - $startpos ? strpos($string, $end, $startpos) - $startpos : strlen($string);

    $foundstring = trim (substr($string, $startpos, $matchlength));



    return $foundstring;

}



//Override SEO Plugin Title - Comment these lines out to restore.

add_option("aiosp_404_title_format", custom404_getmissingpagestr(), 'All in One SEO Plugin 404 Title Format', 'yes');

update_option('aiosp_404_title_format', custom404_getmissingpagestr());



add_action('get_header', 'custom404_sendheader');

add_filter('wp_title', 'custom404_404title');



?>
