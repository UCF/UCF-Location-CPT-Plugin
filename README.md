# UCF Location Custom Post Type #

Provides a custom post type and custom fields for describing locations.


## Description ##

The UCF Location Custom Post Type plugin provides the `location` custom post type and custom fields for describing physical locations.


## Documentation ##

Head over to the [UCF Location Custom Post Type wiki](https://github.com/UCF/UCF-Location-CPT-Plugin/wiki) for detailed information about this plugin, installation instructions, and more.

## Required Plugins ##
These plugins *must* be activated for the theme to function properly, and/or to satisfy core feature requirements for ucf.edu.
* [UCF Spotlight](https://github.com/UCF/UCF-Spotlights-Plugin)

## Changelog ##

### 0.2.2 ###
Enhancements:
* Updated location import and association scripts to match against existing object types in WordPress and against Map using case-insensitive matching.

### 0.2.1 ###
Enhancements:
* Added field to add a spotlight.

### 0.2.0 ###
Enhancements:
* Updated the location importer to support the updated location feed schema from Map's upcoming v1.13.5 release.
* Updated the location importer's `create_new()` and `update_existing()` methods to require incoming location data to have a title/name set.  If the incoming location doesn't have a name/title, processing of that location is skipped and an error is stored + spat out when the import finishes.
* Bumped minimum PHP version requirement to 7.0 to support null coalescing.

Bug Fixes:
* Moved the step in the location importer that unsets an existing location post from `existing_locations` to only occur when an existing location is successfully updated.
* Fixed errors in the location importer caused by attempting to loop through `map_data` when `get_data()` returns `false`.
* Fixed output of error messages at the bottom of `print_stats()` in the location importer.

### 0.1.3 ###
Enhancements:
* Updated `UCF_Location_Post_Type::location_append_meta()` to ensure `events_markup` post meta is empty when no events are returned.
* Updated CONTRIBUTING doc.

### 0.1.2 ###
Bug Fixes:
* Corrected a syntax error on the admin javascript.

### 0.1.1 ###
Enhancements:
* Adds the `form-control-search` class to the typeahead input.

### 0.1.0 ###
* Initial release


## Upgrade Notice ##

n/a


## Development ##

Note that compiled, minified css and js files are included within the repo.  Changes to these files should be tracked via git (so that users installing the plugin using traditional installation methods will have a working plugin out-of-the-box.)

[Enabling debug mode](https://codex.wordpress.org/Debugging_in_WordPress) in your `wp-config.php` file is recommended during development to help catch warnings and bugs.

### Requirements ###
* node
* gulp-cli

## Plugin Requirements ##
* Advanced Custom Fields Pro or Free > 5.0.0

### Instructions ###
1. Clone the UCF-Location-CPT-Plugin repo into your local development environment, within your WordPress installation's `plugins/` directory: `git clone https://github.com/UCF/UCF-Location-CPT-Plugin.git`
2. `cd` into the new UCF-Location-CPT-Plugin directory, and run `npm install` to install required packages for development into `node_modules/` within the repo
3. Optional: If you'd like to enable [BrowserSync](https://browsersync.io) for local development, or make other changes to this project's default gulp configuration, copy `gulp-config.template.json`, make any desired changes, and save as `gulp-config.json`.

    To enable BrowserSync, set `sync` to `true` and assign `syncTarget` the base URL of a site on your local WordPress instance that will use this plugin, such as `http://localhost/wordpress/my-site/`.  Your `syncTarget` value will vary depending on your local host setup.

    The full list of modifiable config values can be viewed in `gulpfile.js` (see `config` variable).
3. Run `gulp default` to process front-end assets.
4. If you haven't already done so, create a new WordPress site on your development environment to test this plugin against, and [install and activate all plugin dependencies](https://github.com/UCF/UCF-Location-CPT-Plugin/wiki/Installation#installation-requirements).
5. Activate this plugin on your development WordPress site.
6. Configure plugin settings from the WordPress admin under "Locations".
7. Run `gulp watch` to continuously watch changes to scss and js files. If you enabled BrowserSync in `gulp-config.json`, it will also reload your browser when plugin files change.

### Other Notes ###
* This plugin's README.md file is automatically generated. Please only make modifications to the README.txt file, and make sure the `gulp readme` command has been run before committing README changes.  See the [contributing guidelines](https://github.com/UCF/UCF-Location-CPT-Plugin/blob/master/CONTRIBUTING.md) for more information.


## Contributing ##

Want to submit a bug report or feature request?  Check out our [contributing guidelines](https://github.com/UCF/UCF-Location-CPT-Plugin/blob/master/CONTRIBUTING.md) for more information.  We'd love to hear from you!
