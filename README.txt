=== Plugin Name ===
Contributors: markparnell
Tags: christianityworks, fresh, daily devotional
Requires at least: 5.3.0
Tested up to: 6.7
Stable tag: 1.4.0
License: Copyright Spark Web Solutions and Christianityworks. Unauthorised distribution of this software, with or without modifications is expressly prohibited.

Embed FRESH on your site.

== Description ==

Embed the FRESH daily devotional from Christianityworks on your site.

* Checks the Christianityworks site daily and pulls down a copy of today's FRESH devotional in selected languages
* Adds both archive and single views of the FRESH devotionals
* Produces an RSS feed you can use e.g. in a MailChimp campaign

== Installation ==

1. Upload `spark-cw-fresh.zip` via Plugins -> Add New. Alternately extract the zip file and upload the `spark-cw-fresh` folder to the `/wp-content/plugins/` directory via FTP.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Select your preferred language in FRESH -> Settings.

== Changelog ==

= 1.4.1 =
* Updated RSS feed logic. It will now return an error if the latest Fresh episode does not have today's date instead of just generating a feed with an old episode. This is designed to prevent duplicate emails being sent in the case where the plugin is unable to retrieve today's episode.

= 1.4.0 =
* Added additional options to banners. Banners can now be set to show on the web as well as (or instead of) in the RSS feed. They can also now be set to display only on specific days of the week, rather than all days within the selected range. Existing banners will be shown on all days in the RSS feed as per previous versions.
* Added 'display' and 'image_size' parameters to [fresh_today] shortcode to allow for more control over output
* Added custom action links in plugins list to plugin settings and documentation

= 1.3.4 =
* Added Chinese (Simplified) and Swahili
* Fixed fatal error on PHP 8 when creating/editing banners
* Cleaned up various PHP warnings

= 1.3.3 =
* Reworked activation/deactivation logic for more reliable setup of rewrite rules, especially on multisite

= 1.3.2 =
* Made sure extra cron is only added when retrieval of feed does actually fail

= 1.3.1 =
* If today's episode can't be retrieved from Christianityworks for any reason, the plugin will now automatically try again an hour later

= 1.3.0 =
* Added ability to set up custom banners for inclusion in RSS feeds for selected languages

= 1.2.3 =
* Centred image in single episode in case it isn't large enough to fill the available space
* Added Hindi and Zulu

= 1.2.2 =
* Bug fixes

= 1.2.1 =
* Added Afrikaans
* Single post template will now fall back to featured image if no video is set
* Code cleanup

= 1.2.0 =
* Added full multi-language support
* Added check to avoid duplicate posts

= 1.1.0 =
* Added MailChimp RSS feed

= 1.0.0 =
* Initial release
