=== Caching Submodule ===
Contributors: Webkul
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.0.9
Requires PHP: 7.4
Tested up to PHP: 8.3
WC requires at least: 5.0
WC tested up to: 9.0
WPML Compatible: yes
Multisite Compatible: yes

Tags: woocommerce caching, object caching, transient caching, file caching, radis caching

It is a submodule and will be pulled into our other modules to include the caching in each module and avoid repeated work.

WK Caching Submodule adds different types of caching like object caching, file caching and transient caching.

== Installation ==

1. Add submodule to the plugin.
2. Activate the plugin through the Plugins menu in WordPress.
3. Run 'composer install' command to your {plugin_path}/wk_caching to install PHPFasteCache library.
4. Use the caching classes in your modules.

== Frequently Asked Questions ==
No questions asked yet

== Feel free to do so. ==
For any Query please generate a ticket at https://webkul.com/ticket/

== Changelog ==

= 1.0.9 (24-07-01) =
Updated: Tags upto 5 as per WordPress standard.
Updated: Tested upto values for WordPress 6.5 and WooCommerce 9.0
Downgraded: PHPFasteCache library version to v8.1 from v9 to support PHP 7.3 and higher.
Removed: Unnecessary logging and printing.

= 1.0.8 (23-12-27) =
Added: Filter and settings to enable Redis cache from the current module.
Removed: Unused .md files containing php code in PHPFasteCache vendor folder showing security issues on some hosting.

= 1.0.7 (23-12-13) =
Added: A static function to get filtered data from global get and post variables.
Added: Added notice wrapper function for dynamic notice html with WP-6.4
Updated: Tested upto values for php-8.2 and wc-8.4

= 1.0.6 (23-11-17) =
Removed: Some unnecessary log.
Updated: Tested up to values.

= 1.0.5 (23-09-27) =
Fixed: Caching setting is not showing.

= 1.0.4 (23-08-11) =
Added: WC Marketplace lite constant to restricted sellers count.
Added: Auto downloading PHPFasteCache library from wpwebkul github without showing admin notice to run 'composer install'
Updated: WC Tested upto values to 8.0 in readme.txt.

= 1.0.3 (23-07-18) =
Moved: Setting in caching module to add it in required module's page.
Added: Saved cache keys count, show keys and show data links.
Added: Clear all cached data links in settings.
Added: Rest data for PHPFasteCache Driver.

= 1.0.1 (23-06-15) =
Added: Setting for enable/disable caching in core cahing submodule under WordPress General settings.

= 1.0.0 (23-06-02) =
Initial setup.
Implementation of Object Cache, File Cache, Transient Cache and PHPFasteCache 'File' Driver.

