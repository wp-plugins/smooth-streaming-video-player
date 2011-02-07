<?php
// Chargement du Bootstrap WordPress
require_once( ABSPATH . '/wp-load.php' );
require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
require_once( ABSPATH . PLUGINDIR . '/' . get_plugin_dirname() . '/includes/class-videos.php' );

$plugin_data = get_plugin_data( realpath( dirname( __FILE__ ) ) . '/svp-silverlight.php');
$title = get_bloginfo( 'name' ) . ' - ' . $plugin_data['Name'];
$link = get_bloginfo( 'url' );
$description = '';

$svp_videos = new SVP_Videos();
$posts = $svp_videos->get_posts_with_video();

function implode_params( $params )
{
	$str = '';
	$i = 0;
	foreach ( $params as $key => $value )
	{
		if ( $i > 0 )
			$str .= ' ';
		$str .= $key . '="' . $value . '"';
		$i++;
	}
	return $str;
}

function tabs_manager( $count = 1 )
{
	$tabs = '';
	for ( $i = 0; $i < $count; $i++ )
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
$svp_video = new SVP_Video();
$svp_post = new SVP_Post();
foreach ($posts as $post):
	$svp_post->read( $post->get_ID() );
	print tabs_manager( 2 ) . '<item>' . PHP_EOL . 
		tabs_manager( 3 ) . '<title><![CDATA[' . $post->get_title() . ']]></title>' . PHP_EOL . 
		tabs_manager( 3 ) . '<link><![CDATA[' . get_permalink( $post->get_ID() ) . ']]></link>' . PHP_EOL . 
		tabs_manager( 3 ) . '<pubDate>' . gmdate( DATE_RSS, strtotime( $post->get_date() ) ) . '</pubDate>' . PHP_EOL . 
		tabs_manager( 3 ) . '<guid isPermaLink="false">' . $post->get_guid() . '</guid>' . PHP_EOL . 
		tabs_manager( 3 ) . '<description><![CDATA[' . $post->get_content() . ']]></description>' . PHP_EOL; 
	$media_content = array();
	$media_content['url'] = $svp_post->get_video_url();
	$media_content['medium'] = 'video';
	print tabs_manager( 3 ) . '<media:content ' . implode_params( $media_content ) . '>' . PHP_EOL;
	$thumb = $svp_post->get_thumbnail_url();
	if ( ! empty( $thumb ) )
		print tabs_manager( 4 ) . '<media:thumbnail url="' . $svp_post->get_thumbnail_url() . '" />' . PHP_EOL;
	print tabs_manager( 3 ) . '</media:content>' . PHP_EOL;
	print tabs_manager( 2 ) . '</item>' . PHP_EOL;
endforeach;
?>
	</channel>
</rss>