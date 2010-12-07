=== Smooth Streaming Video Player (SVP) ===
Contributors: adenova
Tags: video, streaming, silverlight
Requires at least: 2.9
Tested up to: 3.0.2
Stable tag: trunk
License: GPLv2

This plugin allow you to play some Smooth Streaming videos linked to your posts blog.

== Description ==

With this plugin, you can link a Smooth Streaming video to a post and play it inside a Silverlight player on a PC or Mac. You can also play your Smooth Streaming video on a iPhone or iPad because this plugin implements a HTML 5 video tag.

This plugin has these main features :

* SMF 2.2 player implementation 
* Simple administration to configure the plugin 
* Easy to link a Smooth Streaming Video to a post 
* Media RSS exposition of latest Smooth Streaming Videos linked to the blog posts 
* HTML 5 video tag implementation to play Smooth Streaming Videos on iPhone and iPad

Warning : your Smooth Streaming videos must be hosted on a IIS web server with 'Media Services' extension 
installed. But your WordPress website can be stay hosted on a Apache web server.

== Installation ==

1. Just place the svp-silverlight directory in your WordPress plugins directory 
2. Activate the SVP plugin through the 'Plugins' menu in WordPress
3. Place `<?php the_smooth_video_player(); ?>` in your templates (i.e. in 'single.php')

== Frequently Asked Questions ==

= What is 'the_smooth_video_player()' function ? =

Is a WordPress template tag. You can call it in a template of your current theme.

= Is there some parameters can I pass to 'the_smooth_video_player()' template tag ? =

Yes, you can, but these parameters are optionnal. These parameters are, in this order :

* Width : to override the default width or specific width of the player. It can be an integer or a percent, '100%' for example (empty by default).
* Height : to override the default height or specific height of the player. It can be an integer or a percent (empty by default).
* Comments : a boolean to indicate if some comments must be written in the HTML source code before and after the player HTML source code (true by default).
* Container : a boolean to indicate if a identified `<div>` container must be written in the HTML source code (true by default).
* Before : some HTML source code to add before player HTML source code (empty by default).
* After : some HTML source code to add after player HTML source code (empty by default).

Examples of template tag call :

* Example of a player with a 100% parent width : `<?php the_smooth_video_player('100%'); ?>`
* Example of a player with no comments in HTML source code : `<?php the_smooth_video_player('', '', false); ?>`
* Example of a player with HTML source code after : `<?php the_smooth_video_player('', '', true, true, '', '<p>Player implemented with Smooth Streaming Video Player plugin for WordPress.</p>'); ?>`

== Screenshots ==

1. Main configuration page. Here, you must indicate the URL of your videos web server, the directory where
your videos are placed on this web server. You can also configure the default width and height of the player.
The SVP plugin exposes a Media RSS feed : here, you can indicate the count items to show in this feed.
2. This screenshot shows the interface where the administrator can link a video to a post.
2. The same screenshot but after the administrator has linked a video to a post.

== Changelog ==

= 1.4.2 =
* Add <param name="windowless" value="true" /> for a z-index management under IE in "the_smooth_video_player()" template tag HTML output.
* Modify "background" param value from "black" to "transparent" for a better visual integration in "the_smooth_video_player()" template tag HTML output.
* Post ID read fixed from posts page list.
* Translation errors fixed in two messages.

= 1.4.0 =
* Add iPhone and iPad videos play.
* Rights problem on Player.xap file fixed.
* Translation errors fixed.

= 1.2.2 =
* Settings form submission error fixed.

= 1.2.0 =
* Add Media RSS feed.

= 1.0.0 =
* This is the initial version of our plugin.

== Upgrade Notice ==

= 1.4.2 =
* You have to upgrade to this version if you want to show the silverlight player in the posts page list (stable version).

= 1.4.0 =
* With this version, you can play your videos on iPhone and iPad (stable version).

= 1.2.2 =
* Updrage to this version : it fixes a bug in the settings page (beta version).

= 1.2.0 =
* If you want to expose a Media RSS of your videos linked to posts, use this version (beta version).

= 1.0.0 =
* This is the initial version of our plugin (beta version).