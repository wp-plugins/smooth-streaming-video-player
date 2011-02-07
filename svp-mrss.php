<?php
// Chargement du Bootstrap WordPress
require_once(ABSPATH . '/wp-load.php');
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
require_once(ABSPATH . PLUGINDIR . '/' . get_plugin_dirname() . '/includes/svp-movies.php');

$plugin_data = get_plugin_data(realpath(dirname(__FILE__)) . "/svp-silverlight.php");
$title = get_bloginfo('name') . ' - ' . $plugin_data['Name'];
$link = get_bloginfo('url');
$description = '';

$helper = new SVP_Movies();
$posts = $helper->get_posts_with_movie();

function implodeParams ($params)
{
	$str = '';
	$i = 0;
	foreach ($params as $key => $value)
	{
		if ($i > 0) {
			$str .= ' ';
		}
		$str .= $key . '="' . $value . '"';
		$i++;
	}
	return $str;
}

function _tabsManager($count = 1)
{
	$tabs = "";
	for ($i = 0; $i < $count; $i++)
		$tabs .= "\t";
	return $tabs;
}

?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">
	<channel>
		<title><![CDATA[<?php print $title; ?>]]></title>
		<link><![CDATA[<?php print $link; ?>]]></link>
		<description><![CDATA[<?php print $description; ?>]]></description>
<?php
foreach ($posts as $post):
	print _tabsManager(2) . '<item>' . PHP_EOL . 
		_tabsManager(3) . '<title><![CDATA[' . $post->title . ']]></title>' . PHP_EOL . 
		_tabsManager(3) . '<link><![CDATA[' . get_permalink($post->id) . ']]></link>' . PHP_EOL . 
		_tabsManager(3) . '<pubDate>' . gmdate(DATE_RSS, strtotime($post->date)) . '</pubDate>' . PHP_EOL . 
		_tabsManager(3) . '<guid isPermaLink="false">' . $post->guid . '</guid>' . PHP_EOL . 
		_tabsManager(3) . '<description><![CDATA[' . $post->content . ']]></description>' . PHP_EOL; 
	$mediaContent = array();
	$mediaContent['url'] = SVP_Movie::url($post->movie);
	$mediaContent['type'] = $post->movie->mime;
	if ($post->movie->filesize > 0) {
		$mediaContent['filesize'] = $post->movie->filesize;
	}
	$mediaContent['expression'] = 'full';
	print _tabsManager(3) . '<media:content ' . implodeParams($mediaContent) . ' />' . PHP_EOL . 
		_tabsManager(2) . '</item>' . PHP_EOL;
endforeach; ?>
	</channel>
</rss>