# Add Fediverse Icons to Jetpack
Add Fediverse SVG icons to Jetpack's [Social Menu](https://jetpack.com/support/social-menu/) module.

## Installation
This plugin is available from the [official WordPress.org repo](https://wordpress.org/plugins/add-fediverse-icons-to-jetpack/)! Simply head over to Plugins > Add New, search for 'add fediverse icons to jetpack', and install from there.

## Instructions
When adding a [Custom Link](https://codex.wordpress.org/Appearance_Menus_Screen#Custom_Links) to your Social Menu, set its Navigation Label to the platform you're linking to, e.g., 'GNU Social', 'Peertube', or 'Mastodon'.
 
While Jetpack itself uses domain names, e.g., `facebook.com`, to decide which icon to apply, that approach obviously wouldn't work here: federated platforms are hosted on all sorts of domains. What does work, though, is to name menu items after the applicable Fediverse platform.

## Example Output
<img alt="'Social menu' displaying Fediverse icons" src="https://janboddez.tech/uploads/2019/01/fediverse_icons.png" width="263" />

## Twenty Twenty Compatibility
This plugin should work with any and all themes that rely on Jetpack's social menu module.

The Twenty Twenty theme, however, is not one of those themes, and uses a slightly different approach for its social menu icons. Two small changes, however, will typically make it behave. (You'd still need Jetpack, though!)

### PHP
Add the following to your (child) theme's `functions.php`:
```
// Unhook the plugin's "default" callback.
remove_filter( 'walker_nav_menu_start_el', array( Fediverse_Icons_Jetpack::get_instance(), 'apply_icon' ), 100 );

// And add our own instead.
add_filter( 'walker_nav_menu_start_el', function( $item_output, $item, $depth, $args ) {
  $social_icons = array(
    'Diaspora'   => 'diaspora',
    'Friendica'  => 'friendica',
    'GNU Social' => 'gnu-social',
    'Mastodon'   => 'mastodon',
    'PeerTube'   => 'peertube',
    'Pixelfed'   => 'pixelfed',
  );

  if ( 'social' === $args->theme_location ) {
    // Twenty Twenty's social menu.
    foreach ( $social_icons as $attr => $value ) {
      if ( false !== stripos( $item_output, $attr ) ) {
        // Only for above Fediverse platforms, replace the icon
        // previously added by Twenty Twenty.
        $item_output = preg_replace(
          '@<svg(.*?)</svg>@i',
          jetpack_social_menu_get_svg( array( 'icon' => esc_attr( $value ) ) ),
          $item_output
        );
      }
    }
  }

  return $item_output;
}, 100, 4 );
```

### CSS
Add the following to your (child) theme's `style.css`:
```
.social-menu .icon-diaspora,
.social-menu .icon-friendica,
.social-menu .icon-gnu-social,
.social-menu .icon-mastodon,
.social-menu .icon-peertube,
.social-menu .icon-pixelfed {
  width: 24px;
  height: 24px;
}
```
