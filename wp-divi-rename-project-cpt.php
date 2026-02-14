<?php
/*
 * Plugin Name:         Rename Divi Projects
 * Version:             2.0.999.9
 * Plugin URI:          https://digitalshed45.co.uk/rename-divi-projects-plugin/
 * Description:         Requires Divi by Elegant Themes. Rename the Divi 'Projects' post type to a user-defined name.
 * Author:              Digital Shed45 - Gareth J M Saunders
 * Author URI:          https://digitalshed45.co.uk
 * Text domain:         wp-divi-rename-project-cpt
 * Domain Path:         /languages/
 * Requires at least:   5.3
 * Tested up to:        6.6.2
 * License:             GPL3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 */


/**
 * Prevent direct access to the plugin file.
 *
 * Exits the script if the plugin is accessed directly,
 * ensuring that the code only runs within the WordPress environment.
 */
if ( !defined('ABSPATH') ) {
    exit;
}


/**
 * Is Divi active?
 * Check if the Divi theme is active during plugin activation.
 *
 * This function runs on plugin activation to verify if the Divi theme is active.
 * If the Divi theme is not active, the plugin is deactivated, and an error message
 * is displayed to the user.
 *
 * @return void
 */
function divi_projects_cpt_rename_check_divi_theme_on_activation() {

    $theme = wp_get_theme();

    // Check if the current theme is Divi by name or template.
    if ( 'Divi' !== $theme->get( 'Name' ) && 'Divi' !== $theme->get( 'Template' ) ) {

        // Deactivate the plugin if Divi theme is not active.
        deactivate_plugins( plugin_basename( __FILE__ ) );
        
        // Display an error message and stop the activation process.
        wp_die(
            esc_html__( 'This plugin requires the Divi theme from Elegant Themes to be active. Please activate the Divi theme and try again.', 'wp-divi-rename-project-cpt' ),
            esc_html__( 'Plugin Activation Error', 'wp-divi-rename-project-cpt' ),
            array( 'back_link' => true )
        );

    }
}
// Register the activation hook for the plugin.
register_activation_hook( __FILE__, 'divi_projects_cpt_rename_check_divi_theme_on_activation' );

/**
 * Flush rewrite rules on plugin activation.
 *
 * This runs once on activation to prevent runtime rewrite flushing on every request.
 *
 * @return void
 */
function divi_projects_cpt_rename_flush_rewrite_rules_on_activation() {
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'divi_projects_cpt_rename_flush_rewrite_rules_on_activation' );


/**
 * Text domain
 * Load the plugin's text domain for translation.
 *
 * This function loads the plugin's text domain to enable translation of
 * strings within the plugin. It looks for translation files in the 
 * '/languages/' directory inside the plugin's folder.
 *
 * @return void
 */
function wpdocs_load_textdomain() {
	load_plugin_textdomain( 
        'wp-divi-rename-project-cpt',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages' 
    ); 
}
// Hook the function to the 'init' action to load the text domain at the appropriate time.
add_action( 'init', 'wpdocs_load_textdomain' );


/**
 * CSS and JS
 * Enqueue custom CSS and JavaScript assets for the admin area.
 *
 * This function enqueues the Dashicons library, a custom JavaScript file,
 * and a custom CSS file specifically for the admin area of WordPress.
 *
 * @return void
 */
function divi_projects_cpt_rename_enqueue_custom_admin_assets( $hook_suffix ) {

    $plugin_page_suffix = '_page_rename-divi-projects-settings';

    if (
        'settings_page_rename-divi-projects-settings' !== $hook_suffix &&
        substr( $hook_suffix, -strlen( $plugin_page_suffix ) ) !== $plugin_page_suffix
    ) {
        return;
    }

    // Enqueue the Dashicons library for use in the admin area.
    wp_enqueue_style( 'dashicons' );
    
    // Enqueue the custom JavaScript file with jQuery as a dependency, loaded in the footer.
    wp_enqueue_script( 'wp-divi-rename-project-cpt-js', plugins_url( '/wp-divi-rename-project-cpt.js', __FILE__ ), ['jquery'], null, true );
    
    // Enqueue the custom CSS file for styling in the admin area.
    wp_enqueue_style( 'wp-divi-rename-project-cpt-css', plugins_url( '/wp-divi-rename-project-cpt.css', __FILE__ ) );
}
// Hook the function to enqueue assets when scripts and styles are enqueued in the admin area.
add_action( 'admin_enqueue_scripts', 'divi_projects_cpt_rename_enqueue_custom_admin_assets' );


/**
 * Admin settings menu item
 * Add a submenu item for plugin settings in site admin.
 *
 * Prefers placing the submenu under Divi when available. Falls back to
 * the Settings menu if a Divi parent slug is not present.
 *
 * @return void
 */
function divi_projects_cpt_rename_add_admin_menu() {
    // Keep settings in site admin only; options are site-specific.
    if ( is_network_admin() ) {
        return;
    }

    $parent_slug = divi_projects_cpt_rename_get_divi_parent_menu_slug();

    if ( empty( $parent_slug ) ) {
        $parent_slug = 'options-general.php';
    }

    add_submenu_page(
        $parent_slug,                           // $parent_slug (string)
        __( 'Rename Divi Projects Settings', 'wp-divi-rename-project-cpt' ),   // $page_title (string)
        __( 'Rename Divi Projects', 'wp-divi-rename-project-cpt' ),            // $menu_title (string)
        'manage_options',                        // $capability (string)
        'rename-divi-projects-settings',         // $menu_slug (string)
        'divi_projects_cpt_rename_options_page'  // $callback_function (callable)
    );
}
// Hook late so Divi has registered its menu first.
add_action( 'admin_menu', 'divi_projects_cpt_rename_add_admin_menu', 99 );

/**
 * Get the Divi top-level parent menu slug when available.
 *
 * @return string Menu slug or empty string if not found.
 */
function divi_projects_cpt_rename_get_divi_parent_menu_slug() {
    global $menu;

    if ( ! is_array( $menu ) ) {
        return '';
    }

    // Known Divi parent slug first, then title-based fallback.
    foreach ( $menu as $item ) {
        if ( isset( $item[2] ) && 'et_divi_options' === $item[2] ) {
            return $item[2];
        }
    }

    foreach ( $menu as $item ) {
        if ( ! isset( $item[0], $item[2] ) ) {
            continue;
        }

        if ( false !== stripos( wp_strip_all_tags( $item[0] ), 'Divi' ) ) {
            return $item[2];
        }
    }

    return '';
}


/**
 * Plugins page Settings link
 * Add a settings link to the plugin's action links on the Plugins page.
 *
 * This function adds a "Settings" link to the plugin's row on the Plugins page,
 * making it easy for users to navigate directly to the plugin's settings page.
 *
 * @param array $links An array of existing action links for the plugin.
 * @return array Modified array of action links with the added settings link.
 */
function divi_projects_cpt_rename_action_links( $links ) {

    // Create the settings link pointing to the plugin's settings page.
    $settings_link = '<a href="admin.php?page=rename-divi-projects-settings">' . __( 'Settings', 'wp-divi-rename-project-cpt' ) . '</a>';
    
    // Prepend the settings link to the existing links array.
    array_unshift( $links, $settings_link );
   
    return $links;
}
// Add the settings link to the action links for this plugin on the Plugins page.
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'divi_projects_cpt_rename_action_links' );


/**
 * Initialize plugin
 * Initialize and registers the plugin's settings, sections, and fields.
 *
 * This function sets up the settings for renaming Divi Projects custom post types, 
 * categories, and tags. It registers the settings, adds sections, and adds fields 
 * to the WordPress settings API, allowing customization through the admin panel.
 *
 * @return void
 */
function divi_projects_cpt_rename_settings_init() {

    // Check if the current user has the capability to manage options.
    // This ensures that only authorized users can register or modify the plugin settings.
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Register the plugin settings with a sanitization callback.
    register_setting( 
        'divi_projects_cpt_rename_settings_group',
        'divi_projects_cpt_rename_settings',
        'divi_projects_cpt_rename_sanitize_settings'
    );

    // Custom Post Type Settings section
    add_settings_section(
        'divi_projects_cpt_rename_cpt_settings_section',
        __( 'Custom Post Type Settings', 'wp-divi-rename-project-cpt' ),
        null,
        'divi_projects_cpt_rename'
    );

    // Custom Post Type Section
    add_settings_field(
        'divi_projects_cpt_rename_singular_name',
        __( 'Singular Name', 'wp-divi-rename-project-cpt' ),
        'divi_projects_cpt_rename_singular_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_cpt_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_plural_name',
        __( 'Plural Name', 'wp-divi-rename-project-cpt' ),
        'divi_projects_cpt_rename_plural_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_cpt_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_slug',
        __( 'Slug', 'wp-divi-rename-project-cpt' ),
        'divi_projects_cpt_rename_slug_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_cpt_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_menu_icon',
        __( 'Menu Icon', 'wp-divi-rename-project-cpt' ),
        'divi_projects_cpt_rename_menu_icon_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_cpt_settings_section'
    );

    // Category Settings section
    add_settings_section(
        'divi_projects_cpt_rename_category_settings_section',
        __( 'Category Settings', 'wp-divi-rename-project-cpt' ),
        null,
        'divi_projects_cpt_rename'
    );

    add_settings_field(
        'divi_projects_cpt_rename_category_singular_name',
        __( 'Category Singular Name', 'wp-divi-rename-project-cpt' ),
        'divi_projects_cpt_rename_category_singular_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_category_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_category_plural_name',
        __( 'Category Plural Name', 'wp-divi-rename-project-cpt' ),
        'divi_projects_cpt_rename_category_plural_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_category_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_category_slug',
        __( 'Category Slug', 'wp-divi-rename-project-cpt' ),
        'divi_projects_cpt_rename_category_slug_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_category_settings_section'
    );

    // Tag Settings section
    add_settings_section(
        'divi_projects_cpt_rename_tag_settings_section',
        __( 'Tag Settings', 'wp-divi-rename-project-cpt' ),
        null,
        'divi_projects_cpt_rename'
    );

    add_settings_field(
        'divi_projects_cpt_rename_tag_singular_name',
        __( 'Tag Singular Name', 'wp-divi-rename-project-cpt' ),
        'divi_projects_cpt_rename_tag_singular_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_tag_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_tag_plural_name',
        __( 'Tag Plural Name', 'wp-divi-rename-project-cpt' ),
        'divi_projects_cpt_rename_tag_plural_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_tag_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_tag_slug',
        __( 'Tag Slug', 'wp-divi-rename-project-cpt' ),
        'divi_projects_cpt_rename_tag_slug_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_tag_settings_section'
    );

}
// Hook the settings initialization function into the 'admin_init' action.
add_action( 'admin_init', 'divi_projects_cpt_rename_settings_init' );

/**
 * Flush rewrite rules when slug-related settings are updated.
 *
 * @param mixed $old_value The old value of the settings.
 * @param mixed $new_value The new value of the settings.
 * @return void
 */
function divi_projects_cpt_rename_flush_permalinks_after_settings_update( $old_value, $new_value ) {
    $old_value = is_array( $old_value ) ? $old_value : array();
    $new_value = is_array( $new_value ) ? $new_value : array();

    $old_slug          = isset( $old_value['slug'] ) ? $old_value['slug'] : '';
    $new_slug          = isset( $new_value['slug'] ) ? $new_value['slug'] : '';
    $old_category_slug = isset( $old_value['category_slug'] ) ? $old_value['category_slug'] : '';
    $new_category_slug = isset( $new_value['category_slug'] ) ? $new_value['category_slug'] : '';
    $old_tag_slug      = isset( $old_value['tag_slug'] ) ? $old_value['tag_slug'] : '';
    $new_tag_slug      = isset( $new_value['tag_slug'] ) ? $new_value['tag_slug'] : '';

    if (
        $old_slug !== $new_slug ||
        $old_category_slug !== $new_category_slug ||
        $old_tag_slug !== $new_tag_slug
    ) {
        flush_rewrite_rules();
    }
}
add_action( 'update_option_divi_projects_cpt_rename_settings', 'divi_projects_cpt_rename_flush_permalinks_after_settings_update', 10, 2 );


/**
 * Convert a field key into a human-friendly label.
 * Required by Sanitize settings function.
 *
 * @param string $field_key The field key to be transformed.
 * @return string The transformed, human-friendly field name.
 */
function divi_projects_cpt_rename_humanize_field_name( $field_key ) {

    // Replace underscores with spaces
    $human_readable = str_replace( '_', ' ', $field_key );
    
    // Capitalize each word
    $human_readable = ucwords( $human_readable );
    
    return $human_readable;
}


/**
 * Sanitize settings for the Divi Projects CPT Rename plugin.
 *
 * This function checks user capabilities, verifies the nonce for security, ensures that required fields are not empty,
 * and sanitizes the provided settings values before saving them.
 *
 * @param array $settings The array of settings fields to be sanitized.
 * @return array The array of sanitized settings.
 */
function divi_projects_cpt_rename_sanitize_settings( $settings ) {

    // Check if the current user has the capability to manage options.
    // If not, terminate the script with an error message.
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-divi-rename-project-cpt' ) );
    }

    // Verify the nonce to protect against Cross-Site Request Forgery (CSRF) attacks.
    // If the nonce is invalid, terminate the script with an error message.
    if ( ! isset( $_POST['divi_projects_cpt_rename_options_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['divi_projects_cpt_rename_options_nonce'] ), 'divi_projects_cpt_rename_options_verify' ) ) {
        wp_die( esc_html__( 'Nonce verification failed.', 'wp-divi-rename-project-cpt' ) );
    }


    // Initialize the array for sanitized settings
    $sanitized_settings = array();

    // Define the list of required fields.
    // These fields must not be empty. If any required field is empty, an error message will be displayed.
    $required_fields = array(
        'singular_name',
        'plural_name',
        'slug',
        'menu_icon',
        'category_singular_name',
        'category_plural_name',
        'category_slug',
        'tag_singular_name',
        'tag_plural_name',
        'tag_slug'
    );

    // Loop through each setting field, check if the field is required and if it's empty.
    // If a required field is empty, add an error message and skip sanitization for that field.
    // Otherwise, sanitize the field value based on its type.
    foreach ( $settings as $key => $value ) {

        // Check if the field is required and if it's empty
        if ( in_array( $key, $required_fields, true ) && empty( $value ) ) {
            add_settings_error(
                'divi_projects_cpt_rename_settings', // Setting slug
                $key . '_error', // Error code
                sprintf( 
                    /* translators: %s: field name */
                    __( 'The %s field cannot be empty.', 'wp-divi-rename-project-cpt' ), 
                    divi_projects_cpt_rename_humanize_field_name( $key )
                ), // Error message
                'error' // Error type
            );
            // Optionally, you can set a default value or retain the previous value
            // For this example, we'll skip sanitization for empty required fields
            continue;
        }

        // Sanitize setting values based on the field key
        switch ( $key ) {
            case 'singular_name':
            case 'plural_name':
            case 'category_singular_name':
            case 'category_plural_name':
            case 'tag_singular_name':
            case 'tag_plural_name':
                $sanitized_settings[ $key ] = sanitize_text_field( $value );
                break;
            case 'slug':
            case 'category_slug':
            case 'tag_slug':
                // Sanitize the slugs to ensure they are lowercase and use dashes (-)
                $sanitized_settings[ $key ] = sanitize_title_with_dashes( $value );
                break;
            case 'menu_icon':
                // Additional validation if needed
                $sanitized_settings[ $key ] = esc_attr( $value );
                break;
            default:
                // Handle other settings as needed
                $sanitized_settings[ $key ] = wp_kses_post( $value );
                break;
        }
    }

    // Return the sanitized settings
    return $sanitized_settings;
}


/**
 * Singular name
 * Render the input field for the singular name setting of the custom post type.
 *
 * This function outputs a text input field where users may specify the singular
 * name of the custom post type. The field value is retrieved from the plugin's
 * settings, with a default of "Project" if no value is set.
 *
 * @return void
 */
function divi_projects_cpt_rename_singular_name_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[singular_name]" value="<?php echo isset( $options['singular_name'] ) ? esc_attr( $options['singular_name'] ) : 'Project'; ?>">
    <p class="description">
        <?php esc_html_e( 'e.g.', 'wp-divi-rename-project-cpt' ); ?> 
        <kbd><?php esc_html_e( 'Project', 'wp-divi-rename-project-cpt' ); ?></kbd>
    </p>
    <?php
}

/**
 * Plural name
 * Render the input field for the plural name setting of the custom post type.
 *
 * This function outputs a text input field where users can specify the plural
 * name of the custom post type. The field value is retrieved from the plugin's
 * settings, with a default of "Projects" if no value is set.
 *
 * @return void
 */
function divi_projects_cpt_rename_plural_name_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[plural_name]" value="<?php echo isset( $options['plural_name'] ) ? esc_attr( $options['plural_name'] ) : 'Projects'; ?>">
    <p class="description">
        <?php esc_html_e( 'e.g.', 'wp-divi-rename-project-cpt' ); ?> 
        <kbd><?php esc_html_e( 'Projects', 'wp-divi-rename-project-cpt' ); ?></kbd>
    </p>
    <?php
}


/**
 * Slug
 * Render the input field for the slug setting of the custom post type.
 *
 * This function outputs a text input field where users can specify the slug
 * for the custom post type. The field value is retrieved from the plugin's
 * settings, with a default of "project" if no value is set.
 *
 * @return void
 */
function divi_projects_cpt_rename_slug_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    $slug = isset( $options['slug'] ) ? $options['slug'] : 'project';

    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[slug]" value="<?php echo esc_attr( $slug ); ?>">
    <p class="description">
        <?php esc_html_e( 'e.g.', 'wp-divi-rename-project-cpt' ); ?> 
        <kbd><?php esc_html_e( 'project', 'wp-divi-rename-project-cpt' ); ?></kbd>
    </p>
    <?php
}


/**
 * Menu
 * Render the dropdown menu for selecting the menu icon for the custom post type.
 *
 * This function outputs a dropdown menu that allows users to select a menu icon for the custom
 * post type. The selected icon value is retrieved from the plugin's settings, with a default
 * of 'dashicons-portfolio' if no value is set. The available icons are whitelisted and grouped
 * by categories for easier selection. The enqueued Select2 jQuery library displays the dashicon
 * on the dropdown menu.
 *
 * @link https://developer.wordpress.org/resource/dashicons/
 * @return void
 */
function divi_projects_cpt_rename_menu_icon_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    $selected_icon = isset( $options['menu_icon'] ) ? esc_attr( $options['menu_icon'] ) : 'dashicons-portfolio';

    // Whitelisted menu icon values grouped by categories
    $menu_icons = array(
        'Admin Menu' => array(
            'dashicons-admin-appearance'            => 'appearance',
            'dashicons-admin-collapse'              => 'collapse',
            'dashicons-admin-comments'              => 'comments',
            'dashicons-admin-customizer'            => 'customizer',
            'dashicons-dashboard'                   => 'dashboard',
            'dashicons-filter'                      => 'filter',
            'dashicons-admin-generic'               => 'generic',
            'dashicons-admin-home'                  => 'home',
            'dashicons-admin-links'                 => 'links',
            'dashicons-admin-media'                 => 'media',
            'dashicons-menu'                        => 'menu',
            'dashicons-menu-alt'                    => 'menu (alt)',
            'dashicons-menu-alt2'                   => 'menu (alt 2)',
            'dashicons-menu-alt3'                   => 'menu (alt 3)',
            'dashicons-admin-multisite'             => 'multisite',
            'dashicons-admin-network'               => 'network',
            'dashicons-admin-page'                  => 'page',
            'dashicons-admin-plugins'               => 'plugins',
            'dashicons-plugins-checked'             => 'plugins checked',
            'dashicons-admin-post'                  => 'post',
            'dashicons-admin-settings'              => 'settings',
            'dashicons-admin-site'                  => 'site',
            'dashicons-admin-site-alt'              => 'site (alt)',
            'dashicons-admin-site-alt2'             => 'site (alt 2)',
            'dashicons-admin-site-alt3'             => 'site (alt 3)',
            'dashicons-admin-tools'                 => 'tools',
            'dashicons-admin-users'                 => 'users',
        ),
        'Block Editor' => array(
            'dashicons-align-full-width' => 'align full width',
            'dashicons-align-pull-left'  => 'align pull left',
            'dashicons-align-pull-right' => 'align pull right',
            'dashicons-align-wide'       => 'align wide',
            'dashicons-block-default'    => 'block default',
            'dashicons-button'           => 'button',
            'dashicons-cloud-saved'      => 'cloud saved',
            'dashicons-cloud-upload'     => 'cloud upload',
            'dashicons-columns'          => 'columns',
            'dashicons-cover-image'      => 'cover image',
            'dashicons-ellipsis'         => 'ellipsis',
            'dashicons-embed-audio'      => 'embed audio',
            'dashicons-embed-generic'    => 'embed generic',
            'dashicons-embed-photo'      => 'embed photo',
            'dashicons-embed-post'       => 'embed post',
            'dashicons-embed-video'      => 'embed video',
            'dashicons-exit'             => 'exit',
            'dashicons-heading'          => 'heading',
            'dashicons-html'             => 'HTML',
            'dashicons-info-outline'     => 'info (outline)',
            'dashicons-insert'           => 'insert',
            'dashicons-insert-after'     => 'insert after',
            'dashicons-insert-before'    => 'insert before',
            'dashicons-remove'           => 'remove',
            'dashicons-saved'            => 'saved (tick, check)',
            'dashicons-shortcode'        => 'shortcode',
            'dashicons-table-col-after'  => 'table column after',
            'dashicons-table-col-before' => 'table column before',
            'dashicons-table-col-delete' => 'table column delete',
            'dashicons-table-row-after'  => 'table row after',
            'dashicons-table-row-before' => 'table row before',
            'dashicons-table-row-delete' => 'table row delete',
        ),
        'Buddicons' => array(
            'dashicons-buddicons-activity'        => 'activity',
            'dashicons-buddicons-bbpress-logo'    => 'bbpress logo',
            'dashicons-buddicons-buddypress-logo' => 'buddypress logo',
            'dashicons-buddicons-community'       => 'community',
            'dashicons-buddicons-forums'          => 'forums',
            'dashicons-buddicons-friends'         => 'friends',
            'dashicons-buddicons-groups'          => 'groups',
            'dashicons-buddicons-pm'              => 'pm (personal message)',
            'dashicons-buddicons-replies'         => 'replies',
            'dashicons-buddicons-topics'          => 'topics',
            'dashicons-buddicons-tracking'        => 'tracking',
        ),
        'Databases' => array(
            'dashicons-database'        => 'database',
            'dashicons-database-add'    => 'database add',
            'dashicons-database-remove' => 'database remove',
            'dashicons-database-export' => 'database export',
            'dashicons-database-import' => 'database import',
            'dashicons-database-view'   => 'database view',
        ),
        'Image Editing' => array(
            'dashicons-image-crop'            => 'image crop',
            'dashicons-image-filter'          => 'filter',
            'dashicons-image-flip-horizontal' => 'flip horizontal',
            'dashicons-image-flip-vertical'   => 'flip vertical',
            'dashicons-redo'                  => 'redo',
            'dashicons-image-rotate'          => 'rotate',
            'dashicons-image-rotate-left'     => 'rotate left',
            'dashicons-image-rotate-right'    => 'rotate right',
            'dashicons-undo'                  => 'undo',
        ),
        'Media' => array(
            'dashicons-controls-skipback'    => 'skip back',
            'dashicons-controls-back'        => 'back',
            'dashicons-controls-play'        => 'play',
            'dashicons-controls-pause'       => 'pause',
            'dashicons-controls-forward'     => 'forward',
            'dashicons-controls-skipforward' => 'skip forward',
            'dashicons-controls-repeat'      => 'repeat',
            'dashicons-controls-volumeoff'   => 'volume off',
            'dashicons-controls-volumeon'    => 'volume on',
            'dashicons-media-archive'        => 'archive',
            'dashicons-media-audio'          => 'audio',
            'dashicons-media-code'           => 'code',
            'dashicons-media-default'        => 'default',
            'dashicons-media-document'       => 'document',
            'dashicons-media-interactive'    => 'interactive',
            'dashicons-playlist-audio'       => 'playlist audio',
            'dashicons-playlist-video'       => 'playlist video',
            'dashicons-media-spreadsheet'    => 'spreadsheet',
            'dashicons-media-text'           => 'text',
            'dashicons-media-video'          => 'video',
        ),
        'Miscellaneous' => array(
            'dashicons-airplane'            => 'airplane',
            'dashicons-album'               => 'album',
            'dashicons-analytics'           =>  'analytics',
            'dashicons-awards'              =>  'awards',
            'dashicons-backup'              =>  'backup',
            'dashicons-bank'                =>  'bank',
            'dashicons-beer'                =>  'beer',
            'dashicons-book'                =>  'book',
            'dashicons-book-alt'            =>  'book (alt)',
            'dashicons-building'            =>  'building',
            'dashicons-businessman'         =>  'businessman',
            'dashicons-businessperson'      =>  'businessperson',
            'dashicons-businesswoman'       =>  'businesswoman',
            'dashicons-calculator'          =>  'calculator',
            'dashicons-car'                 =>  'car',
            'dashicons-carrot'              =>  'carrot',
            'dashicons-chart-area'          =>  'chart area',
            'dashicons-chart-bar'           =>  'chart bar',
            'dashicons-chart-line'          =>  'chart line',
            'dashicons-chart-pie'           =>  'chart pie',
            'dashicons-clock'               =>  'clock',
            'dashicons-coffee'              =>  'coffee',
            'dashicons-color-picker'        =>  'color picker',
            'dashicons-desktop'             =>  'desktop',
            'dashicons-download'            =>  'download',
            'dashicons-drumstick'           =>  'drumstick',
            'dashicons-edit-large'          =>  'edit large',
            'dashicons-edit-page'           =>  'edit page',
            'dashicons-food'                =>  'food',
            'dashicons-forms'               =>  'forms',
            'dashicons-fullscreen-alt'      =>  'fullscreen (alt)',
            'dashicons-fullscreen-exit-alt' =>  'fullscreen exit (alt)',
            'dashicons-games'               =>  'games',
            'dashicons-groups'              =>  'groups',
            'dashicons-hourglass'           =>  'hourglass',
            'dashicons-id'                  =>  'id',
            'dashicons-id-alt'              =>  'id (alt)',
            'dashicons-index-card'          =>  'index card',
            'dashicons-laptop'              =>  'laptop',
            'dashicons-layout'              =>  'layout',
            'dashicons-lightbulb'           =>  'lightbulb',
            'dashicons-location'            =>  'location',
            'dashicons-location-alt'        =>  'location (alt)',
            'dashicons-microphone'          =>  'microphone',
            'dashicons-money'               =>  'money',
            'dashicons-money-alt'           =>  'money (alt)',
            'dashicons-open-folder'         =>  'open folder',
            'dashicons-palmtree'            =>  'palm tree',
            'dashicons-paperclip'           =>  'paperclip',
            'dashicons-pdf'                 =>  'pdf',
            'dashicons-pets'                =>  'pets',
            'dashicons-phone'               =>  'phone',
            'dashicons-portfolio'           =>  'portfolio',
            'dashicons-printer'             =>  'printer',
            'dashicons-privacy'             =>  'privacy',
            'dashicons-products'            =>  'products',
            'dashicons-search'              =>  'search',
            'dashicons-shield'              =>  'shield',
            'dashicons-shield-alt'          =>  'shield (alt)',
            'dashicons-slides'              =>  'slides',
            'dashicons-smartphone'          =>  'smartphone',
            'dashicons-smiley'              =>  'smiley',
            'dashicons-sos'                 =>  'sos',
            'dashicons-store'               =>  'store',
            'dashicons-superhero'           =>  'superhero',
            'dashicons-superhero-alt'       =>  'superhero (alt)',
            'dashicons-tablet'              =>  'tablet',
            'dashicons-testimonial'         =>  'testimonial',
            'dashicons-text-page'           =>  'text page',
            'dashicons-thumbs-down'         =>  'thumbs down',
            'dashicons-thumbs-up'           =>  'thumbs up',
            'dashicons-tickets-alt'         =>  'tickets (alt)',
            'dashicons-upload'              =>  'upload',
            'dashicons-vault'               =>  'vault',
        ),
         'Notifications' => array(
            'dashicons-bell'        =>  'bell',
            'dashicons-yes'         =>  'yes',
            'dashicons-yes-alt'     =>  'yes (alt) ',
            'dashicons-no'          =>  'no',
            'dashicons-no-alt'      =>  'no (alt)',
            'dashicons-plus'        =>  'plus',
            'dashicons-plus-alt'    =>  'plus (alt)',
            'dashicons-plus-alt2'   =>  'plus (alt 2)',
            'dashicons-minus'       =>  'minus',
            'dashicons-dismiss'     =>  'dismiss',
            'dashicons-marker'      =>  'marker',
            'dashicons-star-filled' =>  'star filled',
            'dashicons-star-half'   =>  'star half',
            'dashicons-star-empty'  =>  'star empty',
            'dashicons-flag'        =>  'flag',
            'dashicons-warning'     =>  'warning',
        ),
         'Post Formats' => array(
            'dashicons-format-aside'   => 'aside',
            'dashicons-format-audio'   => 'audio',
            'dashicons-camera'         => 'camera',
            'dashicons-camera-alt'     => 'camera (alt)',
            'dashicons-format-chat'    => 'chat',
            'dashicons-format-gallery' => 'gallery',
            'dashicons-format-image'   => 'image',
            'dashicons-images-alt'     => 'images (alt)',
            'dashicons-images-alt2'    => 'images (alt 2)',
            'dashicons-format-quote'   => 'quote',
            'dashicons-format-status'  => 'status',
            'dashicons-format-video'   => 'video',
            'dashicons-video-alt'      => 'video (alt)',
            'dashicons-video-alt2'     => 'video (alt 2)',
            'dashicons-video-alt3'     => 'video (alt 3)',
        ),
         'Posts Screen' => array(
            'dashicons-align-center' => 'align center',
            'dashicons-align-left'   => 'align left',
            'dashicons-align-none'   => 'align none',
            'dashicons-align-right'  => 'align right',
            'dashicons-calendar'     => 'calendar',
            'dashicons-calendar-alt' => 'calendar (alt)',
            'dashicons-edit'         => 'edit',
            'dashicons-hidden'       => 'hidden',
            'dashicons-lock'         => 'lock',
            'dashicons-post-status'  => 'post status',
            'dashicons-sticky'       => 'sticky',
            'dashicons-trash'        => 'trash',
            'dashicons-unlock'       => 'unlock',
            'dashicons-visibility'   => 'visibility',
        ),
         'Products' => array(
            'dashicons-cart'          => 'cart',
            'dashicons-cloud'         => 'cloud',
            'dashicons-feedback'      => 'feedback',
            'dashicons-info'          => 'info',
            'dashicons-pressthis'     => 'pressthis',
            'dashicons-screenoptions' => 'screen options',
            'dashicons-translation'   => 'translation',
            'dashicons-update'        => 'update',
            'dashicons-update-alt'    => 'update (alt)',
            'dashicons-wordpress'     => 'wordpress',
            'dashicons-wordpress-alt' => 'wordpress (alt)',
        ),
         'Sorting' => array(
            'dashicons-arrow-down'       => 'arrow down',
            'dashicons-arrow-down-alt'   => 'arrow down (alt)',
            'dashicons-arrow-down-alt2'  => 'arrow down (alt 2)',
            'dashicons-arrow-left'       => 'arrow left',
            'dashicons-arrow-left-alt'   => 'arrow left (alt)',
            'dashicons-arrow-left-alt2'  => 'arrow left (alt 2)',
            'dashicons-arrow-right'      => 'arrow right',
            'dashicons-arrow-right-alt'  => 'arrow right (alt)',
            'dashicons-arrow-right-alt2' => 'arrow right (alt 2)',
            'dashicons-arrow-up'         => 'arrow up',
            'dashicons-arrow-up-alt'     => 'arrow up (alt)',
            'dashicons-arrow-up-alt2'    => 'arrow up (alt 2)',
            'dashicons-excerpt-view'     => 'excerpt view',
            'dashicons-external'         => 'external',
            'dashicons-grid-view'        => 'grid view',
            'dashicons-leftright'        => 'left right',
            'dashicons-list-view'        => 'list view',
            'dashicons-move'             => 'move',
            'dashicons-randomize'        => 'randomize',
            'dashicons-sort'             => 'sort',
        ),
         'Social' => array(
            'dashicons-amazon'       => 'amazon',
            'dashicons-email'        => 'email',
            'dashicons-email-alt'    => 'email (alt)',
            'dashicons-email-alt2'   => 'email (alt 2)',
            'dashicons-facebook'     => 'facebook',
            'dashicons-facebook-alt' => 'facebook (alt)',
            'dashicons-google'       => 'google',
            'dashicons-instagram'    => 'instagram',
            'dashicons-linkedin'     => 'linkedin',
            'dashicons-networking'   => 'networking',
            'dashicons-pinterest'    => 'pinterest',
            'dashicons-podio'        => 'podio',
            'dashicons-reddit'       => 'reddit',
            'dashicons-rss'          => 'rss',
            'dashicons-share'        => 'share',
            'dashicons-share-alt'    => 'share (alt)',
            'dashicons-share-alt2'   => 'share (alt 2)',
            'dashicons-spotify'      => 'spotify',
            'dashicons-twitch'       => 'twitch',
            'dashicons-twitter'      => 'twitter',
            'dashicons-twitter-alt'  => 'twitter (alt)',
            'dashicons-whatsapp'     => 'whatsapp',
            'dashicons-xing'         => 'xing',
            'dashicons-youtube'      => 'youtube',
        ),
         'Taxonomies' => array(
            'dashicons-tag'      => 'tag',
            'dashicons-category' => 'category',
        ),
         'TinyMCE' => array(
            'dashicons-editor-aligncenter'      =>  'align center',
            'dashicons-editor-alignleft'        =>  'align left',
            'dashicons-editor-alignright'       =>  'align right',
            'dashicons-editor-bold'             =>  'bold',
            'dashicons-editor-break'            =>  'break',
            'dashicons-editor-code'             =>  'code',
            'dashicons-editor-contract'         =>  'contract',
            'dashicons-editor-customchar'       =>  'custom character',
            'dashicons-editor-expand'           =>  'expand',
            'dashicons-editor-help'             =>  'help',
            'dashicons-editor-indent'           =>  'indent',
            'dashicons-editor-insertmore'       =>  'insert more',
            'dashicons-editor-italic'           =>  'italic',
            'dashicons-editor-justify'          =>  'justify',
            'dashicons-editor-kitchensink'      =>  'kitchen sink',
            'dashicons-editor-ltr'              =>  'ltr (left to right)',
            'dashicons-editor-ol'               =>  'ordered list',
            'dashicons-editor-ol-rtl'           =>  'ordered list (rtl)',
            'dashicons-editor-outdent'          =>  'outdent',
            'dashicons-editor-paragraph'        =>  'paragraph',
            'dashicons-editor-paste-text'       =>  'paste text',
            'dashicons-editor-paste-word'       =>  'paste word',
            'dashicons-editor-quote'            =>  'quote',
            'dashicons-editor-removeformatting' =>  'remove formatting',
            'dashicons-editor-rtl'              =>  'rtl (right to left)',
            'dashicons-editor-spellcheck'       =>  'spellcheck',
            'dashicons-editor-strikethrough'    =>  'strikethrough',
            'dashicons-editor-table'            =>  'table',
            'dashicons-editor-textcolor'        =>  'textcolor',
            'dashicons-editor-ul'               =>  'unordered list',
            'dashicons-editor-underline'        =>  'underline',
            'dashicons-editor-unlink'           =>  'unlink',
            'dashicons-editor-video'            =>  'video',
        ),
         'Welcome Screen' => array(
            'dashicons-welcome-add-page'      =>  'add page',
            'dashicons-welcome-comments'      =>  'comments',
            'dashicons-welcome-learn-more'    =>  'learn more',
            'dashicons-welcome-view-site'     =>  'view site',
            'dashicons-welcome-widgets-menus' =>  'widgets menus',
            'dashicons-welcome-write-blog'    =>  'write blog',
        ),
         'Widgets' => array(
            'dashicons-archive'  =>  'archive',
            'dashicons-tagcloud' =>  'tag cloud',
            'dashicons-text'     =>  'text',
        ),
         'WordPress.org' => array(
            'dashicons-art'                  => 'art',
            'dashicons-clipboard'            => 'clipboard',
            'dashicons-code-standards'       => 'code-standards',
            'dashicons-hammer'               => 'hammer',
            'dashicons-heart'                => 'heart',
            'dashicons-megaphone'            => 'megaphone',
            'dashicons-migrate'              => 'migrate',
            'dashicons-nametag'              => 'nametag',
            'dashicons-performance'          => 'performance',
            'dashicons-rest-api'             => 'rest api',
            'dashicons-schedule'             => 'schedule',
            'dashicons-tickets'              => 'tickets',
            'dashicons-tide'                 => 'tide',
            'dashicons-universal-access'     => 'universal access ',
            'dashicons-universal-access-alt' => 'universal access (alt)',
        ),
    );

/**
 * Output the HTML select dropdown for choosing the menu icon for the custom post type.
 *
 * This section generates a dropdown list with grouped icon options for the user to select the 
 * desired menu icon for the custom post type. The icons are organized into categories, making 
 * it easier for users to find and select the appropriate icon. It includes a helpful description 
 * and link to the Dashicons resource for reference.
 *
 * @return void
 */
?>
<select name="divi_projects_cpt_rename_settings[menu_icon]" id="menu-icon-select">
    <?php foreach ( $menu_icons as $group => $icons ) : ?>
        <optgroup label="<?php echo esc_html__( $group, 'wp-divi-rename-project-cpt' ); ?>">
            <?php foreach ( $icons as $icon => $label ) : ?>
                <option value="<?php echo esc_attr( $icon ); ?>" <?php selected( $selected_icon, $icon ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </optgroup>
    <?php endforeach; ?>
</select>
    <p class="description">
        <?php esc_html_e( 'See', 'wp-divi-rename-project-cpt' ); ?> <a href="https://developer.wordpress.org/resource/dashicons/#layout" target="_blank"><?php esc_html_e( 'Dashicons', 'wp-divi-rename-project-cpt' ); ?></a> 
            <?php esc_html_e( '(opens new window)', 'wp-divi-rename-project-cpt' ); ?><br />
            <?php esc_html_e( "Divi's default menu icon is", 'wp-divi-rename-project-cpt' ); ?> 
            <kbd><?php esc_html_e( 'Admin Menu &gt; post', 'wp-divi-rename-project-cpt' ); ?></kbd>.
        </p>
    <?php
    }


/**
 * Category Singular Name
 * Render the input field for the singular name of the category associated with the custom post type.
 *
 * This function outputs a text input field where users can specify the singular name of the category 
 * associated with the custom post type. The value is retrieved from the plugin's settings, with a default 
 * of "Project Category" if no value is set. It includes a description with example names for guidance.
 *
 * @return void
 */
function divi_projects_cpt_rename_category_singular_name_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[category_singular_name]" value="<?php echo isset( $options['category_singular_name'] ) ? esc_attr( $options['category_singular_name'] ) : 'Project Category'; ?>">
    <p class="description">
        <?php esc_html_e( 'e.g.', 'wp-divi-rename-project-cpt' ); ?> 
        <kbd><?php esc_html_e( 'Project Category', 'wp-divi-rename-project-cpt' ); ?></kbd> 
        <?php esc_html_e( 'or', 'wp-divi-rename-project-cpt' ); ?> 
        <kbd><?php esc_html_e( 'Category', 'wp-divi-rename-project-cpt' ); ?></kbd>
    </p>
    <?php
}


/**
 * Category Plural Name
 * Render the input field for the plural name of the categories associated with the custom post type.
 *
 * This function generates a text input field allowing users to specify the plural name of the categories 
 * related to the custom post type. The input value is fetched from the plugin's settings, defaulting to 
 * "Project Categories" if not set. A description with example names is provided to guide the user.
 *
 * @return void
 */
function divi_projects_cpt_rename_category_plural_name_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[category_plural_name]" value="<?php echo isset( $options['category_plural_name'] ) ? esc_attr( $options['category_plural_name'] ) : 'Project Categories'; ?>">
    <p class="description">
        <?php esc_html_e( 'e.g.', 'wp-divi-rename-project-cpt' ); ?> 
        <kbd><?php esc_html_e( 'Project Categories', 'wp-divi-rename-project-cpt' ); ?></kbd> 
        <?php esc_html_e( 'or', 'wp-divi-rename-project-cpt' ); ?> 
        <kbd><?php esc_html_e( 'Categories', 'wp-divi-rename-project-cpt' ); ?></kbd>
    </p>
    <?php
}


/**
 * Category Slug
 * Render the input field for the slug of the category associated with the custom post type.
 *
 * This function displays a text input field where users can define the slug for the category 
 * related to the custom post type. The slug is fetched from the plugin's settings, defaulting 
 * to "project_category" if not set. A description with examples and guidance on the default 
 * naming convention is provided to assist the user.
 *
 * @return void
 */
function divi_projects_cpt_rename_category_slug_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[category_slug]" value="<?php echo isset( $options['category_slug'] ) ? esc_attr( $options['category_slug'] ) : 'project_category'; ?>">
    <p class="description">
        <?php esc_html_e( 'e.g.', 'wp-divi-rename-project-cpt' ); ?> 
        <kbd><?php esc_html_e( 'project-category', 'wp-divi-rename-project-cpt' ); ?></kbd><br />
        <?php esc_html_e( "Divi's default category slug is", 'wp-divi-rename-project-cpt' ); ?>&nbsp;
        <kbd><?php esc_html_e( 'project_category', 'wp-divi-rename-project-cpt' ); ?></kbd> 
        <?php esc_html_e( 'with an underscore.', 'wp-divi-rename-project-cpt' ); ?>
    </p>
    <?php
}


/**
 * Tag Singular Name
 * Render the input field for the singular name of the tag associated with the custom post type.
 *
 * This function outputs a text input field that allows users to specify the singular name of the tag 
 * related to the custom post type. The value is retrieved from the plugin's settings, with a default 
 * value of "Project Tag" if not set. The function also provides a description with example names to 
 * guide the user in defining the tag's singular name.
 *
 * @return void
 */
function divi_projects_cpt_rename_tag_singular_name_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[tag_singular_name]" value="<?php echo isset( $options['tag_singular_name'] ) ? esc_attr( $options['tag_singular_name'] ) : 'Project Tag'; ?>">
    <p class="description">
        <?php esc_html_e( 'e.g.', 'wp-divi-rename-project-cpt' ); ?> 
        <kbd><?php esc_html_e( 'Project Tag', 'wp-divi-rename-project-cpt' ); ?></kbd> 
        &nbsp;<?php esc_html_e( 'or', 'wp-divi-rename-project-cpt' ); ?> 
        <kbd><?php esc_html_e( 'Tag', 'wp-divi-rename-project-cpt' ); ?></kbd>
    </p>
    <?php
}


/**
 * Tag Plural Name
 * Render the input field for the plural name of the tags associated with the custom post type.
 *
 * This function displays a text input field that allows users to set the plural name for the tags 
 * related to the custom post type. The input value is retrieved from the plugin's settings, defaulting 
 * to "Project Tags" if not set. It includes a description with example names to guide the user in 
 * choosing appropriate labels.
 *
 * @return void
 */
function divi_projects_cpt_rename_tag_plural_name_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[tag_plural_name]" value="<?php echo isset( $options['tag_plural_name'] ) ? esc_attr( $options['tag_plural_name'] ) : 'Project Tags'; ?>">
    <p class="description">
        <?php esc_html_e( 'e.g.', 'wp-divi-rename-project-cpt' ); ?> 
        <kbd><?php esc_html_e( 'Project Tags', 'wp-divi-rename-project-cpt' ); ?></kbd> 
        <?php esc_html_e( 'or', 'wp-divi-rename-project-cpt' ); ?> 
        <kbd><?php esc_html_e( 'Tags', 'wp-divi-rename-project-cpt' ); ?></kbd>
    </p>
    <?php
}


/**
 * Tag Slug
 * Render the input field for the slug of the tag associated with the custom post type.
 *
 * This function generates a text input field for setting the slug of the tag related to the custom 
 * post type. The slug is retrieved from the plugin's settings, defaulting to "project_tag" if not set. 
 * The field includes a description with examples and guidance on the default slug format.
 *
 * @return void
 */
function divi_projects_cpt_rename_tag_slug_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[tag_slug]" value="<?php echo isset( $options['tag_slug'] ) ? esc_attr( $options['tag_slug'] ) : 'project_tag'; ?>">
    <p class="description">
        <?php esc_html_e( 'e.g.', 'wp-divi-rename-project-cpt' ); ?> 
        <kbd><?php esc_html_e( 'project-tag', 'wp-divi-rename-project-cpt' ); ?></kbd><br />
        <?php esc_html_e( "Divi's default tag slug is", 'wp-divi-rename-project-cpt' ); ?> 
        <kbd><?php esc_html_e( 'project_tag', 'wp-divi-rename-project-cpt' ); ?></kbd> 
        <?php esc_html_e( 'with an underscore.', 'wp-divi-rename-project-cpt' ); ?>
    </p>
    <?php
}


/**
 * Options page
 * Render the options page for the "Rename Divi Projects" plugin.
 *
 * This function generates the settings page for the plugin under the "Settings" menu in the WordPress 
 * admin area. It checks if the current user has the capability to manage options (`manage_options`), 
 * and if not, it terminates execution with an error message.
 *
 * The settings page includes:
 * - A header displaying the plugin title and version.
 * - A form for managing plugin settings, including fields for plugin options and a nonce for security.
 * - A "Reset to defaults" section with instructions on how to revert to the default Divi Project settings 
 *   by deactivating the plugin and flushing rewrite rules.
 *
 * Actions performed:
 * - Uses `settings_fields()` to output nonce, action, and option group fields for the settings form.
 * - Uses `do_settings_sections()` to output all settings sections and fields for the plugin.
 * - Uses `wp_nonce_field()` to add a nonce field for security purposes.
 * - Uses `submit_button()` to render the submit button for the form.
 *
 * If the user lacks the required permissions, the function calls `wp_die()` to halt execution and display
 * an error message.
 *
 * @return void
 */
function divi_projects_cpt_rename_options_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        // Check user capabilities
        // User should not be able to access this plugin admin page as it is
        // listed under Settings but this will double check.
        // If the user doesn't have the capability, display an error message and exit.
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-divi-rename-project-cpt' ) );
    }
    ?>
    <form action="options.php" method="post">

    <header class="divi-purple">
        <h1><?php esc_html_e( 'Rename Divi Projects', 'wp-divi-rename-project-cpt' ); ?> | <span class="plugin-version"> v<?php $plugin_data = get_plugin_data(__FILE__); $plugin_version = $plugin_data['Version']; echo esc_html($plugin_version); ?></span></h1>
    </header>

        <?php
            settings_fields( 'divi_projects_cpt_rename_settings_group' );
            do_settings_sections( 'divi_projects_cpt_rename' );
            wp_nonce_field( 'divi_projects_cpt_rename_options_verify', 'divi_projects_cpt_rename_options_nonce' );
            submit_button();
        ?>
        <h2><?php esc_html_e( 'Reset to defaults', 'wp-divi-rename-project-cpt' ); ?></h2>
        <p class="reset"><?php esc_html_e( 'To', 'wp-divi-rename-project-cpt' ); ?> <strong><?php esc_html_e( 'reset', 'wp-divi-rename-project-cpt' ); ?></strong> <?php esc_html_e( 'this custom post type to the default Divi Project settings (1) navigate to', 'wp-divi-rename-project-cpt' ); ?> <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>"><?php esc_html_e( 'Plugins', 'wp-divi-rename-project-cpt' ); ?></a> <?php esc_html_e( 'and deactivate the', 'wp-divi-rename-project-cpt' ); ?> <strong><?php esc_html_e( 'Rename Divi Projects post type', 'wp-divi-rename-project-cpt' ); ?></strong> <?php esc_html_e( 'plugin then (2) go to', 'wp-divi-rename-project-cpt' ); ?> <a href="options-permalink.php" target="_blank"><?php esc_html_e( 'Settings &gt; Permalinks', 'wp-divi-rename-project-cpt' ); ?></a> <?php esc_html_e( 'and click the Save Changes button to flush the rewrite rules cache.', 'wp-divi-rename-project-cpt' ); ?></p>
    </form>
    <?php
}


/**
 * Retrieve the singular name setting for the custom post type.
 *
 * Gets the value of the 'singular_name' option from the plugin settings. If the option is not set, 
 * it returns a default value of 'Project'.
 * 
 * Ternary operator format: (if statement is true) ? (do this) : (else, do this);
 *
 * @return string The singular name of the custom post type.
 */
function divi_projects_cpt_rename_get_singular_name() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['singular_name'] ) ? $options['singular_name'] : 'Project';
}


/**
 * Retrieve the plural name setting for the custom post type.
 *
 * Gets the value of the 'plural_name' option from the plugin settings. If the option is not set, 
 * it returns a default value of 'Projects'.
 *
 * @return string The plural name of the custom post type.
 */
function divi_projects_cpt_rename_get_plural_name() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['plural_name'] ) ? $options['plural_name'] : 'Projects';
}


/**
 * Retrieve the slug setting for the custom post type.
 *
 * Gets the value of the 'slug' option from the plugin settings. If the option is not set, 
 * it returns a default value of 'project'.
 *
 * @return string The slug of the custom post type.
 */
function divi_projects_cpt_rename_get_slug() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['slug'] ) ? $options['slug'] : 'project';
}


/**
 * Retrieve the menu icon setting for the custom post type.
 *
 * Gets the value of the 'menu_icon' option from the plugin settings. If the option is not set, 
 * it returns a default value of 'dashicons-portfolio'.
 *
 * @return string The menu icon class for the custom post type.
 */
function divi_projects_cpt_rename_get_menu_icon() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['menu_icon'] ) ? $options['menu_icon'] : 'dashicons-portfolio';
}


/**
 * Retrieve the singular name for the category taxonomy.
 *
 * Gets the value of the 'category_singular_name' option from the plugin settings. If the option is not set, 
 * it returns a default value of 'Project Category'.
 *
 * @return string The singular name for the category taxonomy.
 */
function divi_projects_cpt_rename_get_category_singular_name() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['category_singular_name'] ) ? $options['category_singular_name'] : 'Project Category';
}


/**
 * Retrieve the plural name for the category taxonomy.
 *
 * Gets the value of the 'category_plural_name' option from the plugin settings. If the option is not set, 
 * it returns a default value of 'Project Categories'.
 *
 * @return string The plural name for the category taxonomy.
 */
function divi_projects_cpt_rename_get_category_plural_name() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['category_plural_name'] ) ? $options['category_plural_name'] : 'Project Categories';
}


/**
 * Retrieve the slug for the category taxonomy.
 *
 * Gets the value of the 'category_slug' option from the plugin settings. If the option is not set, 
 * it returns a default value of 'project_category'.
 *
 * @return string The slug for the category taxonomy.
 */
function divi_projects_cpt_rename_get_category_slug() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['category_slug'] ) ? $options['category_slug'] : 'project_category';
}


/**
 * Retrieve the singular name for the tag taxonomy.
 *
 * Gets the value of the 'tag_singular_name' option from the plugin settings. If the option is not set, 
 * it returns a default value of 'Project Tag'.
 *
 * @return string The singular name for the tag taxonomy.
 */
function divi_projects_cpt_rename_get_tag_singular_name() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['tag_singular_name'] ) ? $options['tag_singular_name'] : 'Project Tag';
}


/**
 * Retrieve the plural name for the tag taxonomy.
 *
 * Gets the value of the 'tag_plural_name' option from the plugin settings. If the option is not set, 
 * it returns a default value of 'Project Tags'.
 *
 * @return string The plural name for the tag taxonomy.
 */
function divi_projects_cpt_rename_get_tag_plural_name() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['tag_plural_name'] ) ? $options['tag_plural_name'] : 'Project Tags';
}


/**
 * Retrieve the slug for the tag taxonomy.
 *
 * Gets the value of the 'tag_slug' option from the plugin settings. If the option is not set, 
 * it returns a default value of 'project_tag'.
 *
 * @return string The slug for the tag taxonomy.
 */
function divi_projects_cpt_rename_get_tag_slug() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['tag_slug'] ) ? $options['tag_slug'] : 'project_tag';
}


/**
 * Change the Divi Projects custom post type
 * Register the custom post type and taxonomies with new values.
 *
 * This function is hooked to the 'init' action and updates the default 
 * settings for the custom post type 'project' and its associated taxonomies.
 * It uses values obtained from settings options and then registers:
 * - A custom post type with updated labels, icon, and slug.
 * - A hierarchical taxonomy for categories with updated labels and slug.
 * - A hierarchical taxonomy for tags with updated labels and slug.
 *
 * After registering the post type and taxonomies, it flushes rewrite rules
 * to ensure that the changes are reflected immediately.
 *
 * @return void
 */
function divi_projects_cpt_rename_register_new_values() {
    $singular_name          = divi_projects_cpt_rename_get_singular_name();
    $plural_name            = divi_projects_cpt_rename_get_plural_name();
    $slug                   = divi_projects_cpt_rename_get_slug();
    $menu_icon              = divi_projects_cpt_rename_get_menu_icon();
    $category_singular_name = divi_projects_cpt_rename_get_category_singular_name();
    $category_plural_name   = divi_projects_cpt_rename_get_category_plural_name();
    $category_slug          = divi_projects_cpt_rename_get_category_slug();
    $tag_singular_name      = divi_projects_cpt_rename_get_tag_singular_name();
    $tag_plural_name        = divi_projects_cpt_rename_get_tag_plural_name();
    $tag_slug               = divi_projects_cpt_rename_get_tag_slug();

    // Register the custom post type 'project'
    register_post_type( 'project', [
        'labels'            => [
            'name'          => $plural_name,
            'singular_name' => $singular_name,
            'add_new'       => sprintf( __( 'Add New %s', 'wp-divi-rename-project-cpt' ), $singular_name ),
            'add_new_item'  => sprintf( __( 'Add New %s', 'wp-divi-rename-project-cpt' ), $singular_name ),
            'all_items'     => sprintf( __( 'All %s', 'wp-divi-rename-project-cpt' ), $plural_name ),
            'edit_item'     => sprintf( __( 'Edit %s', 'wp-divi-rename-project-cpt' ), $singular_name ),
            'menu_name'     => $plural_name,
            'new_item'      => sprintf( __( 'New %s', 'wp-divi-rename-project-cpt' ), $singular_name ),
            'search_items'  => sprintf( __( 'Search %s', 'wp-divi-rename-project-cpt' ), $plural_name ),
            'view_item'     => sprintf( __( 'View %s', 'wp-divi-rename-project-cpt' ), $singular_name ),
        ],
        'has_archive'       => true,
        'hierarchical'      => true,
        'menu_icon'         => $menu_icon,
        'menu_position'     => 25,
        'public'            => true,
        'publicly_queryable'=> true,
        'exclude_from_search'=> false,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_rest'      => true,
        'supports'          => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'rewrite'           => [
        'slug'              => $slug,
        'with_front'        => true,
        ],
    ] );

    // Register the taxonomy 'project_category' for the 'project' post type
    register_taxonomy( 'project_category', array( 'project' ), [
        'hierarchical' => true,
        'labels'                => [
            'name'              => $category_plural_name,
            'singular_name'     => $category_singular_name,
            'search_items'      => sprintf( __( 'Search %s', 'wp-divi-rename-project-cpt' ), $category_plural_name ),
            'all_items'         => sprintf( __( 'All %s', 'wp-divi-rename-project-cpt' ), $category_plural_name ),
            'parent_item'       => sprintf( __( 'Parent %s', 'wp-divi-rename-project-cpt' ), $category_singular_name ),
            'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-divi-rename-project-cpt' ), $category_singular_name ),
            'edit_item'         => sprintf( __( 'Edit %s', 'wp-divi-rename-project-cpt' ), $category_singular_name ),
            'update_item'       => sprintf( __( 'Update %s', 'wp-divi-rename-project-cpt' ), $category_singular_name ),
            'add_new_item'      => sprintf( __( 'Add New %s', 'wp-divi-rename-project-cpt' ), $category_singular_name ),
            'new_item_name'     => sprintf( __( 'New %s Name', 'wp-divi-rename-project-cpt' ), $category_singular_name ),
            'menu_name'         => $category_plural_name,
            'not_found'         => sprintf( __( 'You currently don\'t have any %s.', 'wp-divi-rename-project-cpt' ), $category_plural_name ),
        ],
        'show_ui'               => true,
        'show_admin_column'     => true,
        'public'                => true,
        'query_var'             => true,
        'show_in_rest'          => true,
        'rewrite'               => [
            'slug'              => $category_slug,
            'with_front'        => true,
        ],
    ]);

    // Register the taxonomy 'project_tag' for the 'project' post type
    register_taxonomy( 'project_tag', array('project'), [
        'hierarchical' => false,
        'labels'                => [
            'name'              => $tag_plural_name,
            'singular_name'     => $tag_singular_name,
            'search_items'      => sprintf( __( 'Search %s', 'wp-divi-rename-project-cpt' ), $tag_plural_name ),
            'all_items'         => sprintf( __( 'All %s', 'wp-divi-rename-project-cpt' ), $tag_plural_name ),
            'parent_item'       => sprintf( __( 'Parent %s', 'wp-divi-rename-project-cpt' ), $tag_singular_name ),
            'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-divi-rename-project-cpt' ), $tag_singular_name ),
            'edit_item'         => sprintf( __( 'Edit %s', 'wp-divi-rename-project-cpt' ), $tag_singular_name ),
            'update_item'       => sprintf( __( 'Update %s', 'wp-divi-rename-project-cpt' ), $tag_singular_name ),
            'add_new_item'      => sprintf( __( 'Add New %s', 'wp-divi-rename-project-cpt' ), $tag_singular_name ),
            'new_item_name'     => sprintf( __( 'New %s Name', 'wp-divi-rename-project-cpt' ), $tag_singular_name ),
            'menu_name'         => $tag_plural_name,
            'not_found'         => sprintf( __( 'You currently don\'t have any %s.', 'wp-divi-rename-project-cpt' ), $tag_plural_name ),
        ],
        'show_ui'               => true,
        'show_admin_column'     => true,
        'public'                => true,
        'query_var'             => true,
        'show_in_rest'          => true,
        'rewrite'               => [
            'slug'              => $tag_slug,
            'with_front'        => true,
        ],
    ] );

}
// Register the `divi_projects_cpt_rename_register_new_values` function to the `init` action hook.
add_action( 'init', 'divi_projects_cpt_rename_register_new_values' );

/**
 * Enable front-end "Skills" label overrides for single project pages.
 *
 * @return void
 */
function divi_projects_cpt_start_buffer() {
    if ( is_admin() || wp_doing_ajax() ) {
        return;
    }

    if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
        return;
    }

    if ( ! is_singular( 'project' ) ) {
        return;
    }

    // Preferred approach: translate Divi's "Skills" string when it flows through gettext.
    add_filter( 'gettext', 'divi_projects_cpt_replace_skills_gettext', 10, 3 );

    // Compatibility fallback for templates that output hard-coded HTML.
    ob_start( 'divi_projects_cpt_replace_skills_heading' );
}
add_action( 'template_redirect', 'divi_projects_cpt_start_buffer' );

/**
 * Replace Divi's "Skills" string via gettext when emitted from Divi text domain.
 *
 * @param string $translation Translated text.
 * @param string $text Original source text.
 * @param string $domain Text domain.
 * @return string
 */
function divi_projects_cpt_replace_skills_gettext( $translation, $text, $domain ) {
    if ( 'Divi' === $domain && 'Skills' === $text ) {
        return divi_projects_cpt_rename_get_tag_plural_name();
    }

    return $translation;
}

/**
 * Fallback: replace hard-coded Skills HTML heading with configured tag plural label.
 *
 * @param string $buffer The page output buffer.
 * @return string
 */
function divi_projects_cpt_replace_skills_heading( $buffer ) {
    $custom_tag_plural_name = divi_projects_cpt_rename_get_tag_plural_name();
    $default_skills_label   = '<strong class="et_project_meta_title">Skills</strong>';
    $custom_label           = '<strong class="et_project_meta_title">' . esc_html( $custom_tag_plural_name ) . '</strong>';

    return str_replace( $default_skills_label, $custom_label, $buffer );
}
