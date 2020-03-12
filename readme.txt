=== Add Fediverse Icons to Jetpack ===
Contributors: janboddez
Tags: jetpack, social, fediverse, icons, mastodon
Tested up to: 5.4
License: GNU General Public License v3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Adds Fediverse icons to Jetpack's Social Menu module.

== Description ==
Enable Fediverse icons in Jetpack's Social Menu module. (Requires Jetpack, and may not be supported by all themes.)

== Installation ==
1. Either visit Plugins > Add New, search "add fediverse icons to jetpack" and install, or manually unpack the ZIP file into `wp-content/plugins`.
2. In WP Admin, head over to Plugins and activate Add Fediverse Icons to Jetpack.
3. From Appearance > Menus, select your (Jetpack) Social Menu and start adding Custom Links to your Fediverse profile(s). Make sure to set each link's Navigation Label to the platform you're linking to, e.g., GNU Social, Peertube, or Mastodon.

While Jetpack itself uses domain names, e.g., `facebook.com`, to decide which icon to apply, that approach wouldn't work here: federated platforms are hosted on all sorts of domains. What does work, though, is to name menu items after the applicable Fediverse platform.

== Supported Platforms ==
This plugin currently provides icons for:
- Diaspora
- Friendica
- GNU Social
- Mastodon
- Peertube
- Pixelfed
