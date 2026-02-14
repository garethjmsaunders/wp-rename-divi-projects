# Rename Divi Projects

Unlock the full potential of Divi Projects to make your site truly unique. Compatible with both Divi 4 and Divi 5.

<!-- MarkdownTOC -->

- [About](#about)
- [Customize the name](#customize-the-name)
- [Features](#features)
- [Documentation](#documentation)
  - [How to install](#how-to-install)
  - [Getting started with Rename Divi Projects](#getting-started-with-rename-divi-projects)
  - [Settings page](#settings-page)
  - [Deleting the plugin](#deleting-the-plugin)
- [How it works](#how-it-works)
- [Changelog](#changelog)

<!-- /MarkdownTOC -->


<a id="about"></a>
## About

Rename Divi Projects customizes Divi's built-in `project` custom post type so you can use it for content that better matches your site (for example: properties, books, albums, case studies).

You can rename labels and rewrite slugs while continuing to use Divi's project-related modules:

- [Portfolio](https://www.elegantthemes.com/modules/portfolio/)
- [Filterable Portfolio](https://www.elegantthemes.com/modules/filterable-portfolio/)
- [Post Carousel ](https://www.elegantthemes.com/blog/theme-releases/flexbox) (Divi 5) or [Fullwidth Portfolio](https://www.elegantthemes.com/modules/portfolio-carousel/) (Divi 4)

<a id="customize-the-name"></a>
## Customize the name

Change the default "Projects" post type labels and slugs to fit your content model without replacing Divi's underlying project system.

<a id="features"></a>
## Features

- **Rename labels** Change singular/plural labels for the Project post type, Project Category taxonomy, and Project Tag taxonomy.
- **Change slugs** Customize rewrite slugs for project posts, project categories, and project tags.
- **Choose a menu icon** Select a Dashicon for the Projects menu item.
- **Control admin menu visibility** Set the minimum role level that can see the Projects menu in wp-admin.
- **Divi single-project "Skills" label override** Replaces Divi's front-end "Skills" label with your configured tag plural label.

<a id="documentation"></a>
## Documentation

<a id="how-to-install"></a>
### How to install

WordPress plugins are PHP scripts that extend WordPress functionality.

Rename Divi Projects is a standard WordPress plugin and can be installed like any other plugin.

#### Option 1: Upload via WordPress Admin (recommended)

1. Download the `Rename Divi Projects` plugin zip file
2. Go to `Plugins > Add New Plugin`
3. Select `Upload Plugin`
4. Select the zip file
5. Select `Install Now`
6. Select `Activate Plugin` after installation completes

#### Option 2: Manual plugin installation via SFTP

1. Download the `Rename Divi Projects` plugin zip file
2. Unzip it locally
3. Upload the extracted plugin folder to `/wp-content/plugins/`
4. In WordPress, go to `Plugins`
5. Find `Rename Divi Projects` and select `Activate`

<a id="getting-started-with-rename-divi-projects"></a>
### Getting started with Rename Divi Projects

After activation, go to `Divi > Rename Divi Projects` to open the plugin settings page to configure:

1. Post type labels and slug
2. Category labels and slug
3. Tag labels and slug
4. Menu icon
5. Admin menu visibility minimum role

The plugin is designed for Divi's `project` ecosystem, so Divi project modules continue to work with your renamed labels and slugs (URLs).

<a id="settings-page"></a>
### Settings page

Location:

- Preferred: `Divi > Rename Divi Projects`
- Fallback (if Divi parent menu is not found): `Settings > Rename Divi Projects`. Although, in truth, if Divi is not installed, the plugin installation will also fail gracefully.

Access:

- Users must have the `manage_options` capability.
- The settings page is added only in site admin (not in network admin).

#### TL;DR

1. Update post type, category, and tag labels.
2. Update slugs.
3. Choose a menu icon.
4. Set the minimum role for Projects menu visibility.
5. Click Save Changes.

#### Page layout

The settings page is grouped into:

1. Custom Post Type Settings
2. Category Settings
3. Tag Settings
4. Admin Menu Visibility

Then a Save Changes button and a reset help section.

#### Custom Post Type Settings

##### Singular Name

- Default: `Project`
- Used in labels such as "Add New {Singular}" and edit screens.

##### Plural Name

- Default: `Projects`
- Used in menu and list labels such as "All {Plural}".

##### Slug

- Default: `project`
- Sanitized on save.
- Used as the project rewrite slug.

##### Menu Icon

- Default: `dashicons-portfolio`
- Choose from a curated Dashicons list (with search-enabled dropdown).

#### Category Settings

##### Category Singular Name

- Default: `Project Category`

##### Category Plural Name

- Default: `Project Categories`

##### Category Slug

- Default: `project_category`
- Sanitized on save.
- Used as the `project_category` taxonomy rewrite slug.

#### Tag Settings

##### Tag Singular Name

- Default: `Project Tag`

##### Tag Plural Name

- Default: `Project Tags`
- Also used for Divi's single-project front-end "Skills" label replacement.

##### Tag Slug

- Default: `project_tag`
- Sanitized on save.
- Used as the `project_tag` taxonomy rewrite slug.

#### Admin Menu Visibility

Set the minimum role level allowed to see the Projects admin menu item.

- Available levels: `Contributor`, `Author`, `Editor`, `Administrator`
- Default: `Contributor`
- Affects the `project` menu page in wp-admin (`edit.php?post_type=project`)
- The rule hides the menu item for users below the selected level

Important behavior:

- This is menu visibility control, not a full capability/access control system.
- It hides the menu item; it does not add explicit blocking for direct URL access.
- On multisite, super admins are not restricted by this setting.
- Custom roles outside the plugin's built-in role map are treated as level `0` in this logic.

#### Save Changes

When you save:

- Settings are stored in the `divi_projects_cpt_rename_settings` option.
- Slug fields are sanitized.
- Rewrite rules are flushed automatically only when one or more slug values changed.

Depending on what changed values you save, you may still need to manually flush the Permalinks. To do this go to `Settings > Permalinks` and simply select the `Save Changes` button.

#### Reset to defaults

To return to Divi defaults in runtime behavior:

1. Deactivate the plugin.
2. Go to Settings > Permalinks and click Save Changes.

Your stored option values remain in the database until the plugin is uninstalled.

<a id="deleting-the-plugin"></a>
### Deleting the plugin

When you uninstall/delete the plugin from WordPress, it removes the stored option in the WordPress database:

- `divi_projects_cpt_rename_settings`

<a id="how-it-works"></a>
## How it works

At a high level, the plugin re-registers Divi's existing project post type and its taxonomies with your configured labels/slugs.

Key mechanics:

- On `init`, it registers:
    - post type key: `project`
    - taxonomy keys: `project_category`, `project_tag`
    - Settings values are pulled from one option: `divi_projects_cpt_rename_settings`.
- On settings update, rewrite rules are flushed only if `slug`, `category_slug`, or `tag_slug` changed.
- For UI placement, it adds the settings submenu under Divi when possible, otherwise under Settings.
- For admin menu visibility, it removes the `project` menu page for users below the configured minimum role level.
- For front-end single project pages, it replaces Divi's 'Skills' label with your configured tag plural:
    - primary path: `gettext` filter for Divi domain strings
    - fallback path: output-buffer string replacement for hard-coded template output

Divi 4 vs Divi 5 note:

- The plugin does not hard-switch by explicit Divi version number.
- The two-step 'Skills' replacement (gettext + output-buffer fallback) exists to handle differences in how Divi templates may output that label across versions/templates.

Scope clarification:

- The plugin changes labels and rewrite slugs for Divi's existing Project custom post type (CPT) entities.
- It does not create a brand-new custom post type (CPT) key.
- Internal keys remain `project`, `project_category`, and `project_tag`.

<a id="changelog"></a>
## Changelog

- v2.2.0  | 2026-02-14 | FIX | Update translations. Other minor updates and tweaks to follow conventions.
- v2.1.0  | 2026-02-14 | FEATURE | Add option to hide Projects (renamed or default) from certain user role levels.
- v2.0.0  | 2026-02-14 | FIX | Optimise plugin to work with both Divi 4 and Divi 5.
- v1.0.10 | 2024-10-03 | FIX | Ensure outputs go through escaping functions and sanitize input variable `$_POST`.
