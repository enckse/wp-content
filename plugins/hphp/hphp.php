<?php
/*
Plugin Name: Happy Paws Haven Plugin
Description: Customized wordpress settings for HPH
Version:     {VERSION}
Author:      Sean Enck
License:     GPL-3.0+
Text Domain: happy-paws-haven-plugin
*/
 

// NOTE:
// this is mostly baselined starting from
// https://wordpress.com/blog/2025/07/31/introduction-to-wordpress-plugin-development/
function hphp_register_settings() {
    register_setting( 'hphp_settings_group', 'hphp_json_configuration', array(
        'type' => 'string',
        'default' => '{}',
    ) );
    register_setting( 'hphp_settings_group', 'hphp_pages_onready', array(
        'type' => 'boolean',
        'default' => '',
    ) );
    register_setting( 'hphp_settings_group', 'hphp_pages_allowed', array(
        'type' => 'string',
        'sanitize_callback' => 'hphp_sanitize_comma_list',
        'default' => '',
    ) );
    $counters = hphp_load_counters(get_option("hphp_json_configuration", "{}"));
    foreach ($counters as $key => $value) {
        if ($value == "int" ) {
            register_setting( 'hphp_settings_group', $key, array(
                'type' => 'integer',
                'sanitize_callback' => 'hphp_sanitize_int',
                'default' => 0,
            ) );
        } else if ($value == "sum" ) {
            register_setting( 'hphp_settings_group', $key, array(
                'type' => 'string',
                'sanitize_callback' => 'hphp_sanitize_sum',
                'default' => '',
            ) );
        }
    }
}

add_action( 'admin_init', 'hphp_register_settings' );

function hphp_sanitize_comma_list( $value ) {
    return preg_replace('/[^a-z0-9-,]/', '', $value) ?? '';
}

function hphp_sanitize_sum( $value ) {
    return preg_replace('/[^a-z_]/', '', $value) ?? '';
}
 
function hphp_sanitize_int( $value ) {
    $value = intval($value);
    return ( $value > 0 ) ? $value : 0;
}

function hphp_get_counters( $objects ) {
    $counters = array();
    if (property_exists($objects, "counters")) {
        foreach ($objects->counters as $key => $value) {
            $counters["hphp_counter_" . $key] = $value;
        }
    }
    return $counters;
}

function hphp_load_counters( $json ) {
    return hphp_get_counters(json_decode("$json"));
}
 
function hphp_register_settings_page() {
    add_options_page(
        'HPHP',
        'HPHP',
        'manage_options',
        'hphp-settings',
        'hphp_render_settings_page'
    );
}

function hphp_print_counter_settings( $counters, $type ) {
    foreach ($counters as $key => $value) {
        if ($value != "$type") {
            continue;
        }
        echo "<hr />";
        if ($value == "int") {
            $numeric = get_option( $key , 0 );
            echo "<input name='" . $key . "' type='number' id='" . $key . "' value='" . esc_attr( $numeric ) . "' class='small-text' min='1' />";
        } else if ($value == "sum" ) {
            $str = get_option( $key, "" );
            echo "<input name='" . $key . "' type='text' id='" . $key . "' value='" . esc_attr( $str ) . "' />";
        }
        echo "<label style='padding-left: 5px' for='" . $key . "'>" . str_replace("_", " ", str_replace("hphp_counter_", "", $key)) . "</label>";
        echo "<br />";
    }
}

add_action( 'admin_menu', 'hphp_register_settings_page' );
 
// Render the settings page.
function hphp_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Happy Paws Haven Plugin Settings', 'happy-paws-haven' ); ?></h1>
        <hr />
        <h3>Counters</h3>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'hphp_settings_group' );
            do_settings_sections( 'hphp_settings_group' );
            $json_payload = get_option("hphp_json_configuration", "{}");
            $counters = hphp_load_counters( $json_payload );
            ksort($counters);
            hphp_print_counter_settings($counters, "int");
            hphp_print_counter_settings($counters, "sum");
            $pages = get_option("hphp_pages_allowed", "");
            $onready = get_option("hphp_pages_onready", false);
            $checked = "";
            if ($onready) {
                $checked = "checked";
            }
            ?>
            <hr />
            <button type="button" onclick="document.getElementById('hphp_advanced_config').style.display = (document.getElementById('hphp_advanced_config').style.display == 'none' ? 'block' : 'none');">Advanced Configuration</button>
            <div id="hphp_advanced_config" style="display: none; padding: 20px; background-color: #f0f0f0; border: 1px solid #ccc;">
            This value controls which counters are available and how they are enabled, it is a JSON payload (string).
            <br />
            <br />
            It currently accepts a 'counters' key which is a dictionary of name/value pairs where name is the variable and value is one of 'int' or 'sum'. An 'int' type is a numeric and a 'sum' requires a string to match on.
            <textarea name="hphp_json_configuration" id="hphp_json_configuration" rows="10" cols="80"><?php echo esc_attr( $json_payload ); ?></textarea>
            <hr />
            This value controls whether a page is allowed to process/show counter data ([a-z0-9-] strings and comma delimited: ',')
            <br />
            <input type="text" name="hphp_pages_allowed" id="hphp_pages_allowed" value='<?php echo esc_attr( $pages ); ?>'/>
            <hr />
            Indicates whether document ready should fire an init.
            <br />
            <input type="checkbox" name="hphp_pages_onready" id="hphp_pages_onready" <?php echo $checked; ?>/>
            <br />
            <a href="https://github.com/enckse/wp-content">More information online</a>
            </div>
            <hr />
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
 
function hphp_enqueue_assets() {
    $pages = get_option("hphp_pages_allowed", "");
    $json_payload = get_option("hphp_json_configuration", "{}");
    $objects = json_decode("$json_payload");
    if (property_exists($objects, "scripts")) {
        foreach ($objects->scripts as $key => $value) {
            $name = "hphp-" . $value . "-" . "$key";
            $path = plugin_dir_url(__FILE__) . "$key" . "." . $value;
            if ($value == "js") {
              wp_enqueue_script(
                  $name,
                  $path,
                  array("jquery"),
                  '{JS_VERSION}'
              );
            } elseif ($value == "css") {
                wp_enqueue_style(
                  $name,
                  $path,
                  array(),
                  '{CSS_VERSION}'
                );
            }
        }
    }
    $allowed = true;
    $page_name = "";
    $queried = get_queried_object();
    if ($queried && property_exists($queried, 'post_name')) {
        $page_name = $queried->post_name;
    }
    if (!empty($pages) && $page_name != "") {
        $filtering = explode(",", $pages);
        $allowed = false;
        foreach ($filtering as $filtered) {
            if (str_contains($page_name, $filtered)) {
                $allowed = true;
                break;
            }
        }
 
    }
    $counter_data = array();
    if ($allowed) {
        $sums = array();
        $counters = hphp_get_counters($objects);
        foreach ($counters as $key => $value) {
            if ($value == "int") {
                $counter_data[$key] = get_option($key, 0);
            } else if ($value == "sum" ) {
                $str = get_option( $key, "" );
                $sums[$key] = $str;
            }
        }
        foreach ($sums as $key => $str) {
            $value = 0;
            foreach ($counter_data as $counter => $val) {
                if (str_contains($counter, $str)) {
                    $value = $value + $val;
                }
            }
            $counter_data[$key] = $value;
        }
    }
    $payload = array();
    $payload["hphp_page_name"] = $page_name;
    $triggers = array();
    if (!get_option("hphp_pages_onready", false)) {
        array_push($triggers, "onready");
    }
    $payload["triggers"] = $triggers;
    if (!empty($counter_data)) {
        $payload["counters"] = $counter_data;
    }
    $json_output = json_encode($payload);
    $script = "var hphp_data_payload = " . $json_output . ";";

	wp_add_inline_script('hphp-js-plugin', $script, 'before');
}
add_action( 'wp_enqueue_scripts', 'hphp_enqueue_assets' );
