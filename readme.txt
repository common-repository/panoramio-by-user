=== Panoramio by User ===
Contributors: n8schatten
Donate link: -
Tags: Panoramio
Requires at least: 2.8.0
Tested up to: 3.0.4
Stable tag: trunk

Panoramio by User allows displaying pictures submitted to Panoramio by a certain user.

== Description ==

Show Panoramio pictures uploaded by a certain user on your WP.
This Plugin offers the possibility to display single randomly chosen pictures submitted by a certain user in articles or pages, as well as in the template. Furthermore, you can display all pictures of the given user in a gallery.

== Installation ==

1. Upload `PanoramioByUser.php`, `load.php` and `PbU_style.css` into an own folder in the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place `<?php PbU_showRandomPicture() ?>` in your templates in order to display a single random picture provided by the
		user or `<?php PbU_showGallery() ?>` to display all pictures submited by the given user in a gallery.
		Furthermore you can use ##PbU_Random## (resp. ##PbU_Gallery##) in artciles or pages to achieve the same.

== Frequently Asked Questions ==

None yet.

== Screenshots ==

None yet.

== Changelog ==

= 1.0 =
* First upload to the repository.

= 1.1 =
* Restarted work on the plugin
* Reviewed code
* Fixed some issues

== Next Steps ==
* Make calls to Panoramio asynchronous