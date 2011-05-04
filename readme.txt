=== Smooth Streaming Video Player (SVP) ===
Contributors: adenova
Tags: video, streaming, silverlight
Requires at least: 2.9
Tested up to: 3.1.2
Stable tag: 1.5.1
License: GPLv2

This plugin allow you to play some adaptive streaming (with Smooth Streaming in live mode or not) and progressive download videos linked to your posts blog.

== Description ==

With this plugin, you can link adaptive streaming (with Smooth Streaming in live mode or not) and progressive download videos (MP4 and WMV) to a post and play it inside a Silverlight player on a PC or Mac. You can also play your Smooth Streaming video on a iPhone or iPad because this plugin implements a HTML 5 video tag.

This plugin has these main features :

* SMF 2.3 player implementation 
* Simple administration to configure the plugin
* Easy to link a video to a post
* Media RSS exposition of latest videos linked to the blog posts
* HTML 5 video tag implementation to play videos (MP4 for progressive download videos and Smooth Streaming for adaptive streaming videos) on iPhone and iPad

Warning : your Smooth Streaming videos (in Live mode or not) must be hosted on a IIS web server with 'Media Services' extension installed or on Windows Azure storage. But your WordPress website can be stay hosted on a Apache web server. Other types of videos can be hosted on the server of your choice.

== Installation ==

1. Just place the smooth-streaming-video-player directory in your WordPress plugins directory 
2. Activate the SVP plugin through the 'Plugins' menu in WordPress
3. Add, configure and scan a source of videos
4. Link a video to a post
5. Place `<?php the_smooth_video_player(); ?>` in your templates (i.e. in 'single.php')

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

1. First part of main configuration page. You can configure the default width and height of the player. The SVP plugin exposes a Media RSS feed : here, you can indicate the count items to show in this feed. This feed can be acceded addind `?feed=svp-podcast` after your main blog URL.
2. Second part of main configuration page. Here, you can see the list of your own sources of videos. You can also add a new source of videos.
3. When you choose to add a new source of videos from SVP admin menu, you access to this page to select your type of source of videos to add.
4. This screenshot shows the list of videos founded on a source of videos you choose to scan.
5. Page for updating data of a source of videos.
6. This is the list of selection of a video to be associated with a post. This area of selection is present in the edit page of a post.
7. Preview of a video accessible from the list of videos in the edit page of a post.

== Changelog ==

= 1.5.1 =
* French translation ended.
* Add webroot management in the source configuration form.

= 1.5.0 =
* Add a multiple sources of videos management.
* Add pages to add, edit and delete a source of videos.
* Add a page to scan a source of videos (to retrieve the videos existing on the source).
* Update of selection of a video in post edition page.
* Add a possibility to preview the videos to select in post edition page.

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

= 1.5.1 =
* Upgrade to this version to get webroot management in the source configuration form (stable version).

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