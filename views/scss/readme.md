# Working with themes
The TAO CSS SDK helps you to change the look and feel of either your items or the platform itself.

## Common scenarios with items

1. You want that all items share your corporate design.
2. You need a number of very similar themes. They should apply to all items or maybe just a certain type.
3. You need custom items. The designs you have in mind are very different from item to item.

You can create the CSS for all these scenarios with the CSS SDK. In all cases the steps are almost the same, only the the deployment process is different for the latter.



## SCSS/themes directory
* `_common` - code shared by item- and platform	themes
* `items` - everything for item themes
	* `_common` code shared by all item themes
		* `inc` include files
			* `interactions` interaction specific code
	* `theme-template` boilerplate code	
* `platform` - everything for platform themes
	* `_common` code shared by all platform themes
		* `inc` include files
	* `theme-template` boilerplate code	


## Grunt task
- Open a command window in views/build

### Install the grunt task
- `npm install .` installs all required elements

### Run the task
Synopsis `grunt task [options]`

- `grunt csssdk` compiles all item themes
- `grunt csssdk:my-theme` compiles only the theme *my-theme*
- `grunt watch:csssdk` creates a watch for each theme
- `grunt watch:my-theme` watches only the theme *my-theme*

#### Options for the task
- `--target` compile for either platform or items (default)
- `--style` e. g. expanded, default is compressed ([SASS docs](http://sass-lang.com/documentation/file.SASS_REFERENCE.html#output_style))

### Grunt built-in options
[See options in the Grunt documentation](http://gruntjs.com/using-the-cli)

