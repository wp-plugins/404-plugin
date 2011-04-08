=== 404 SEO Plugin ===
Contributors: 404plugin
Donate link: http://www.404plugin.com/
Tags: 404, SEO, Search Optimization, Google, search engine optimization, plugin, page missing

Requires at least: 2.7
Tested up to: 3.1.1
Stable tag: trunk

== Description ==

Give yourself an SEO boost! This simple plugin will give you a customized, smart 'Page Not Found(404)' error message. It will automatically display links to relevant pages on your site, based on the words in the URL that was not found.

Version 2.1 fixes many stability issues and gives cleaner search results.

Installing this plugin is beneficial for SEO (Search Engine Optimization) because it will make your site appear to have relevant pages even for those URLs that you actually don't have. This can increase your search engine ranking.

== Installation ==

Estimated  time: 1-5 minutes

1. Upload the '404-plugin' folder to the /wp-content/plugins/ folder on your site.
2. In WP-Admin, you should see '404 Plugin' listed as an inactive plugin. Click the link to activate it. 
3. Click the 'Editor' link under 'Appearance' in your WP-Admin sidebar.
4. Edit the '404 Template (404.php)'.
5. Add '<?custom404_print404message();?>' to your template, in the place where you want the list of suggestions to appear.

6. What if my theme has no 404.php?
	In your theme's folder (wp-content/themes/yourthemename/) add a file called '404.php' containing this:
<?php get_header(); ?>
  <?custom404_print404message();?>
<?php get_footer(); ?>
*Be sure there are no spaces around this code in the file.*

That's it! You should be all set to go!





