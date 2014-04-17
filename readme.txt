=== Plugin Name ===
Contributors: raubvogel
Donate link: http://fabulierer.de/
Tags: YouTube, widget, video, sidebar, player, Editor, permissions
Requires at least: 3.8
Tested up to: 3.9
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add YouTube videos to your sidebars with this highly customizable widget.

== Description ==

= General Information and Features =

The Falconiform YouTube Widget allows you to add YouTube videos to your sidebars. This is easily done by adding the widget to a sidebar in the administration interface. After that you simply paste the link (URL) of the desired YouTube video (or the video ID) into the appropriate widget settings field. You can adjust the following settings:

* widget title
* YouTube video ID
* player width (automatic width possible)
* player height (automatic height possible)
* player theme
* color of the player’s video progress bar
* player controls visibility
* hide controls / progress bar at playback
* start playback automatically
* restart the video automatically after playback ends (loop video)
* show related videos after playback ends
* show video information like the video title
* show video annotations
* show fullscreen button
* hide YouTube logo in the controls bar
* disable keyboard control for the player

= Editors Can Specify Videos  =

Optionally, the Falconiform YouTube Widget allows users with Editor rights to specify the video that is shown in the sidebar. In WordPress Editors are not allowed to change widget settings. Therefore the Falconiform YouTube Widget adds an extra menu to the administration interface that allows Editors to specify videos that are linked with one or more YouTube widgets (via index number).

If you don’t want Editors to specify videos, you can disable this in the widget settings (disabled by default).

= Technical Details =

The Falconiform YouTube Widget embeds the YouTube player as [HTML5 iFrame](http://www.w3.org/html/wg/drafts/html/master/embedded-content-0.html#the-iframe-element). Details about the YouTube player can be found at [YouTube Embedded Players and Player Parameters](https://developers.google.com/youtube/player_parameters). The HTML output of this plugin was checked against HTML5 conformance successfully.

= Support =

The Falconiform YouTube Widget is delivered in the following languages:

* English (en_US)
* German (de_DE)

If you want to translate this plugin in your language, you can use [Poedit](http://www.poedit.net/download.php). The “pot” file is located in the `/falconiform-youtube-widget/languages/` directory. You can send me your translation (a “po” file) to youtube-widget@falconiform.de and I will add it to the next release.

I use the Falconiform YouTube Widget myself on a website. Hence, I will update this plugin for future WordPress versions and add new features.

If you have found a bug or wish to have new features, please add a ticket in the Support section or send an email to youtube-widget@falconiform.de

== Installation ==

1. Download and extract the plugin’s archive.
1. Upload the `falconiform-youtube-widget` directory to the `/wp-content/plugins/` directory.
1. Activate the plugin “Falconiform YouTube Widget” through the “Plugins” menu in WordPress.
1. Add a YouTube widget through the “Appearance” -> “Widgets” menu in WordPress.

== Frequently Asked Questions ==

= How can I allow an Editor to specify a video? =

Go to an added YouTube widget (menu “Appearance” -> “Widgets”). Open the YouTube widget settings. Set “Index of the video ID from dedicated settings page” to 1. Check the box “Take video from dedicated settings page via index”. Click the “Save” button. Now an Editor can go to menu “YouTube Widget” and add a YouTube video link (or video ID) by setting “Video ID 1”. After that the YouTube widget shows the desired YouTube video.

= Is it possible that an Editor specifies more than x videos? =

Generally, yes. But it’s hard-coded at the moment. Please contact me if you need to specify more videos. However, you can add as many YouTube widgets as you like.

= Can I style the Falconiform YouTube Widget? =

Yes. The YouTube player is loaded in an iFrame, which has the css class `.ff-youtube-widget-player-iframe`. You can add this class for example in your theme’s css file and add css styles.

== Screenshots ==

1. Shows the YouTube widget in action. It’s located in the right sidebar of the theme and has the custom title “Video of the Week”.
2. Shows the rich settings of an added YouTube widget.
3. Shows the separate settings page that can be accessed by an Editor user. An Editor can specify videos to if enabled.

== Changelog ==

= 1.0.0 =
* Fixed an issue where the YouTube Widget was not shown in the live widget preview.

= 1.0.0 =
* Initial version.

== Upgrade Notice ==

= 1.0.0 =
* Fixed an issue where the YouTube Widget was not shown in the live widget preview.

= 1.0.0 =
Initial version.