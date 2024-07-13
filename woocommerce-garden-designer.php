<?php
/*
 * Plugin Name: WooCommerce Garden Designer Plugin
 * Description: A custom WooCommerce plugin for garden design
 * Version: 1.0
 * Author: Ilija Iliev Strumjan
 * Text Domain: woocommerce-garden-designer
 * Domain Path: /languages
 * Requires Plugins: woocommerce
*/

if (!defined('ABSPATH')) {
   exit; // Exit if accessed directly
}
// Вчитување на текст доменот
add_action( 'init', 'woocommerce_garden_designer_load_textdomain' );
function woocommerce_garden_designer_load_textdomain() {
    load_plugin_textdomain('woocommerce-garden-designer', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

add_action('wp_enqueue_scripts', 'woocommerce_garden_designer_enqueue_scripts');
function woocommerce_garden_designer_enqueue_scripts() {
    $gardenDesignerScript = plugins_url( 'includes/garden-designer-script.js', __FILE__ );
	wp_enqueue_script('garden-designer-script', $gardenDesignerScript, array('wp-i18n'), null, true);
	
	$gardenDesignerScriptTranslationPath = plugin_dir_path( __FILE__ ) . 'languages';
    wp_set_script_translations('garden-designer-script', 'woocommerce-garden-designer', $gardenDesignerScriptTranslationPath);

    // Пренесување на URL адресата на страницата за наплата до JavaScript
	$options = get_option('woocommerce_garden_designer_options');
    wp_localize_script('garden-designer-script', 'wc_garden_designer_params', array(
        'checkout_url' => wc_get_checkout_url(),
        'height_tag' => isset($options['height_tag']) ? $options['height_tag'] : '',
        'width_tag' => isset($options['width_tag']) ? $options['width_tag'] : '',
        'shade_tag' => isset($options['shade_tag']) ? $options['shade_tag'] : '',
        'part_shade_tag' => isset($options['part_shade_tag']) ? $options['part_shade_tag'] : '',
        'sun_tag' => isset($options['sun_tag']) ? $options['sun_tag'] : ''
    ));

}

// Регистрирање на краток кодот
function woocommerce_garden_designer_shortcode($atts) {
	ob_start();
	include plugin_dir_path(__FILE__) . '/includes/garden-designer-main.php'; // Промени го патот до твојот PHP фајл
	return ob_get_clean();
}
add_shortcode('garden_designer', 'woocommerce_garden_designer_shortcode');

// Додавање на административно мени
add_action('admin_menu', 'woocommerce_garden_designer_admin_menu');
function woocommerce_garden_designer_admin_menu() {
    add_menu_page(
        __('Garden Designer Settings', 'woocommerce-garden-designer'), 
        __('Garden Designer', 'woocommerce-garden-designer'), 
        'manage_options', 
        'woocommerce-garden-designer', 
        'woocommerce_garden_designer_settings_page', 
        plugins_url('includes/cvijet-icon.png', __FILE__)
    );
}

// Функција за приказ на страницата со поставки
function woocommerce_garden_designer_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('"Garden Designer" Settings', 'woocommerce-garden-designer'); ?></h1>
		<p><?php _e('"Garden Designer" allows your customers to optimize their garden based on expert recommendations. After selecting the lighting and surface type, the "Garden Designer" calculates the necessary types and quantities of plants. It also provides a planting layout sketch. Customers can choose from the available plants in stock and add them to their cart with a single click, directing them to your payment page.', 'woocommerce-garden-designer'); ?></p>
		<h2><strong><?php _e('Rules:', 'woocommerce-garden-designer'); ?></strong></h2>
		<dl>
			<dt><?php _e('Lighting is categorized into three types:', 'woocommerce-garden-designer'); ?></dt>
			<dd><?php _e('Sun (6 or more hours of sunlight)', 'woocommerce-garden-designer'); ?></dd>
			<dd><?php _e('Partial shade (4-6 hours of sunlight)', 'woocommerce-garden-designer'); ?></dd>
			<dd><?php _e('Shade (3 or fewer hours of sunlight)', 'woocommerce-garden-designer'); ?></dd>
		</dl>
		<dl>
			<dt><?php _e('Flower bed type options:', 'woocommerce-garden-designer'); ?></dt>
			<dd><?php _e('Along a wall (where at least one side touches a wall)', 'woocommerce-garden-designer'); ?></dd>
			<dd><?php _e('Central (where no side touches a wall)', 'woocommerce-garden-designer'); ?></dd>
		</dl>
		<dl>
			<dt><?php _e('Plants are divided into three groups:', 'woocommerce-garden-designer'); ?></dt>
			<dd><?php _e('Tall plants (90-150 cm)', 'woocommerce-garden-designer'); ?></dd>
			<dd><?php _e('Medium plants (50-80 cm)', 'woocommerce-garden-designer'); ?></dd>
			<dd><?php _e('Short plants (15-40 cm)', 'woocommerce-garden-designer'); ?></dd>
		</dl>
		<dl>
		<dt><?php _e('Customers can choose between two planting methods:', 'woocommerce-garden-designer'); ?></dt>
			<dd><?php _e('Standard spacing (planting distance: tall 60 cm, medium 50 cm, short 50 cm)', 'woocommerce-garden-designer'); ?></dd>
			<dd><?php _e('Dense spacing (planting distance: tall 40 cm, medium 30 cm, short 30 cm)', 'woocommerce-garden-designer'); ?></dd>
		</dl>
		<h2><strong><?php _e('Instructions for site administrators:', 'woocommerce-garden-designer'); ?></strong></h2>
		<ol>
			<li><?php _e('Before installing "Garden Designer," ensure that WooCommerce is already installed.', 'woocommerce-garden-designer'); ?></li>
			<li><?php _e('Define tags for the following characteristics:', 'woocommerce-garden-designer'); ?>
			   <dl>
			   <dt><?php _e('Lighting:', 'woocommerce-garden-designer'); ?></dt>
				 <dd><?php _e('Sun (e.g. sun, light)', 'woocommerce-garden-designer'); ?></dd>
				 <dd><?php _e('Partial shade (e.g. partial-shade, semi-dark)', 'woocommerce-garden-designer'); ?></dd>
				 <dd><?php _e('Shade (e.g. shade, dark)', 'woocommerce-garden-designer'); ?></dd>
			   </dl>
			   <dl>
			   <dt><?php _e('Flower bed type:', 'woocommerce-garden-designer'); ?></dt>
				 <dd><?php _e('Along a wall', 'woocommerce-garden-designer'); ?></dd>
				 <dd><?php _e('Central', 'woocommerce-garden-designer'); ?></dd>
			   </dl>
			   <dl>
			   <dt><?php _e('Plant height:', 'woocommerce-garden-designer'); ?></dt>
				 <dd><?php _e('Tall plants (90-150 cm)', 'woocommerce-garden-designer'); ?></dd>
				 <dd><?php _e('Medium plants (50-80 cm)', 'woocommerce-garden-designer'); ?></dd>
				 <dd><?php _e('Short plants (15-40 cm)', 'woocommerce-garden-designer'); ?></dd>
			   </dl>
			   <dl>
			   <dt><?php _e('Planting method:', 'woocommerce-garden-designer'); ?></dt>
				 <dd><?php _e('Standard spacing (planting distance: tall 60 cm, medium 50 cm, short 50 cm)', 'woocommerce-garden-designer'); ?></dd>
				 <dd><?php _e('Dense spacing (planting distance: tall 40 cm, medium 30 cm, short 30 cm)', 'woocommerce-garden-designer'); ?></dd>
			   </dl>
			</li>
			<li><?php _e('After defining the tags, assign them to each of your products or the plants you offer for sale.', 'woocommerce-garden-designer'); ?></li>
			<li><?php _e('"Garden Designer" will consider only in-stock products and present them to customers.', 'woocommerce-garden-designer'); ?></li>
			<li><?php _e('After selecting desired plants, customers will be directly redirected to your designated payment page.', 'woocommerce-garden-designer'); ?></li>
			<li><?php _e('To use "Garden Designer", insert the following shortcode on any page: [garden_designer].', 'woocommerce-garden-designer'); ?></li>
		</ol>
        <form method="post" action="options.php">
            <?php
            settings_fields('woocommerce_garden_designer_options_group');
            do_settings_sections('woocommerce-garden-designer');
            submit_button();
			_e('<strong>Note</strong>: You can enter only one tag without quotation marks and other special characters in the corresponding field for each tag.<br />After clicking the "Save Changes" button, the tags will be saved in the component\'s settings and automatically added to the WooCommerce "Tags" set.<br />In order for the component to function properly, after determining the tags, you need to assign them to each of your products, that is, to each herb that you offer for sale.<br />You add the "brightness" tags as you defined them.<br />You provide the "width" and "height" labels in the following format: label number cm (where "label" is the label you defined in the settings, "number" is the corresponding width or height of the plant expressed in centimeters, "cm" is for informational display of the dimension. Note that there are spaces between the sections.)<br />For example:<br />- If the label is "W:" and the plant is "30 cm" wide, then enter "W: 30 cm"<br />- If the mark is "H:" and the plant is "120 cm high", then enter "H: 120 cm"<br />Unfortunately, tags can only be typed in one language, although the component itself can be used in multiple languages.', 'woocommerce-garden-designer');
            ?>
        </form>
        <h2><?php _e('Shortcode Usage', 'woocommerce-garden-designer'); ?></h2>
        <p><?php _e('To use the "Garden designer", simply use the following shortcode:', 'woocommerce-garden-designer'); ?></p>
		<div id="successMessage" style="display: none;"><p><strong><?php _e('Shortcode is copied to Clipboard!', 'woocommerce-garden-designer'); ?></strong><p></div>
		<code>[garden_designer]</code>
		<br /><br />
		<button onclick="copyTextToClipboard('[garden_designer]')" class="button button-primary"><?php _e('Copy shortcode to Clipboard', 'woocommerce-garden-designer'); ?></button>
		<br />
		<p><strong><?php _e('Note:', 'woocommerce-garden-designer'); ?></strong> <?php _e('You can also use "Garden Designer" to facilitate garden planning for your clients.', 'woocommerce-garden-designer'); ?></p>
		<p><?php _e('Good luck with your sales!', 'woocommerce-garden-designer'); ?></p>
		<script>
		function copyTextToClipboard(text) {
			var tempInput = document.createElement("input");
			tempInput.value = text;
			document.body.appendChild(tempInput);
			tempInput.select();
			document.execCommand("copy");
			document.body.removeChild(tempInput);
			
			// Прикажи пораката за успешно копирање
			var successMessage = document.getElementById("successMessage");
			successMessage.style.display = "block";
			
			// Скриј ја пораката по 2 секунди
			setTimeout(function() {
				successMessage.style.display = "none";
			}, 5000);
		}
		</script>
    </div>
    <?php
}

// Регистрирање на поставките
add_action('admin_init', 'woocommerce_garden_designer_settings_init');
function woocommerce_garden_designer_settings_init() {
    register_setting('woocommerce_garden_designer_options_group', 'woocommerce_garden_designer_options', 'woocommerce_garden_designer_options_validate');

    add_settings_section('woocommerce_garden_designer_main_section', __('Tag Settings', 'woocommerce-garden-designer'), 'woocommerce_garden_designer_section_text', 'woocommerce-garden-designer');

    add_settings_field('woocommerce_garden_designer_sun_tag', __('Sun Tag', 'woocommerce-garden-designer'), 'woocommerce_garden_designer_sun_tag_input', 'woocommerce-garden-designer', 'woocommerce_garden_designer_main_section');
    add_settings_field('woocommerce_garden_designer_part_shade_tag', __('Part Shade Tag', 'woocommerce-garden-designer'), 'woocommerce_garden_designer_part_shade_tag_input', 'woocommerce-garden-designer', 'woocommerce_garden_designer_main_section');
    add_settings_field('woocommerce_garden_designer_shade_tag', __('Shade Tag', 'woocommerce-garden-designer'), 'woocommerce_garden_designer_shade_tag_input', 'woocommerce-garden-designer', 'woocommerce_garden_designer_main_section');
    add_settings_field('woocommerce_garden_designer_width_tag', __('Width Tag', 'woocommerce-garden-designer'), 'woocommerce_garden_designer_width_tag_input', 'woocommerce-garden-designer', 'woocommerce_garden_designer_main_section');
    add_settings_field('woocommerce_garden_designer_height_tag', __('Height Tag', 'woocommerce-garden-designer'), 'woocommerce_garden_designer_height_tag_input', 'woocommerce-garden-designer', 'woocommerce_garden_designer_main_section');
}

function woocommerce_garden_designer_section_text() {
    echo '<p>' . __('Enter your custom tags for garden designer settings.', 'woocommerce-garden-designer') . '</p>';
}

function woocommerce_garden_designer_height_tag_input() {
    $options = get_option('woocommerce_garden_designer_options');
    echo "<input id='woocommerce_garden_designer_height_tag' name='woocommerce_garden_designer_options[height_tag]' size='40' type='text' value='{$options['height_tag']}' />";
}

function woocommerce_garden_designer_width_tag_input() {
    $options = get_option('woocommerce_garden_designer_options');
    echo "<input id='woocommerce_garden_designer_width_tag' name='woocommerce_garden_designer_options[width_tag]' size='40' type='text' value='{$options['width_tag']}' />";
}

function woocommerce_garden_designer_shade_tag_input() {
    $options = get_option('woocommerce_garden_designer_options');
    echo "<input id='woocommerce_garden_designer_shade_tag' name='woocommerce_garden_designer_options[shade_tag]' size='40' type='text' value='{$options['shade_tag']}' />";
}

function woocommerce_garden_designer_part_shade_tag_input() {
    $options = get_option('woocommerce_garden_designer_options');
    echo "<input id='woocommerce_garden_designer_part_shade_tag' name='woocommerce_garden_designer_options[part_shade_tag]' size='40' type='text' value='{$options['part_shade_tag']}' />";
}

function woocommerce_garden_designer_sun_tag_input() {
    $options = get_option('woocommerce_garden_designer_options');
    echo "<input id='woocommerce_garden_designer_sun_tag' name='woocommerce_garden_designer_options[sun_tag]' size='40' type='text' value='{$options['sun_tag']}' />";
}

function woocommerce_garden_designer_options_validate($input) {
    $new_input = array();
    $new_input['sun_tag'] = sanitize_text_field($input['sun_tag']);
    $new_input['part_shade_tag'] = sanitize_text_field($input['part_shade_tag']);
    $new_input['shade_tag'] = sanitize_text_field($input['shade_tag']);
    $new_input['width_tag'] = sanitize_text_field($input['width_tag']);
    $new_input['height_tag'] = sanitize_text_field($input['height_tag']);

    // Додавање на таговите во WooCommerce
    $tags = array(
        $new_input['sun_tag'],
        $new_input['part_shade_tag'], 
        $new_input['shade_tag'], 
        $new_input['width_tag'], 
        $new_input['height_tag'] 
    );

    foreach ($tags as $tag) {
        if (!term_exists($tag, 'product_tag')) {
            wp_insert_term($tag, 'product_tag');
        }
    }

    return $new_input;
}

// Add multiple products in cart over url
// https://www.example.com/cart/?add-to-cart=12345,43453
// https://www.example.com/cart/?add-to-cart=12345,12345,12345
// https://www.example.com/cart/?add-to-cart=12345&quantity=3
// https://www.example.com/checkout/?add-to-cart=12345
// This component case is productID:Quantity,productID:Quantity,productID:Quantity,...
// https://www.example.com/checkout/?add-to-cart=1575:1,1058:2,963:4,1079:1,1003:2,961:4,927:1
function wgd_add_multiple_products_to_cart( $url = false ) {
    // Make sure WC is installed, and add-to-cart qauery arg exists, and contains at least one comma.
    if ( ! class_exists( 'WC_Form_Handler' ) || empty( $_REQUEST['add-to-cart'] ) || false === strpos( $_REQUEST['add-to-cart'], ',' ) ) {
        return;
    }

    // Remove WooCommerce's hook, as it's useless (doesn't handle multiple products).
    remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );

    $product_ids = explode( ',', $_REQUEST['add-to-cart'] );
    $count       = count( $product_ids );
    $number      = 0;

    foreach ( $product_ids as $id_and_quantity ) {
        // Check for quantities defined in curie notation (<product_id>:<product_quantity>)
        
        $id_and_quantity = explode( ':', $id_and_quantity );
        $product_id = $id_and_quantity[0];

        $_REQUEST['quantity'] = ! empty( $id_and_quantity[1] ) ? absint( $id_and_quantity[1] ) : 1;

        if ( ++$number === $count ) {
            // Ok, final item, let's send it back to woocommerce's add_to_cart_action method for handling.
            $_REQUEST['add-to-cart'] = $product_id;

            return WC_Form_Handler::add_to_cart_action( $url );
        }

        $product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
        $was_added_to_cart = false;
        $adding_to_cart    = wc_get_product( $product_id );

        if ( ! $adding_to_cart ) {
            continue;
        }

        $add_to_cart_handler = apply_filters( 'woocommerce_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

        // Variable product handling
        if ( 'variable' === $add_to_cart_handler ) {
            woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_variable', $product_id );

        // Grouped Products
        } elseif ( 'grouped' === $add_to_cart_handler ) {
            woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_grouped', $product_id );

        // Custom Handler
        } elseif ( has_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler ) ){
            do_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler, $url );

        // Simple Products
        } else {
            woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_simple', $product_id );
        }
    }
}

// Fire before the WC_Form_Handler::add_to_cart_action callback.
add_action( 'wp_loaded', 'wgd_add_multiple_products_to_cart', 15 );


/**
 * Invoke class private method
 *
 * @since   0.1.0
 *
 * @param   string $class_name
 * @param   string $methodName
 *
 * @return  mixed
 */
function woo_hack_invoke_private_method( $class_name, $methodName ) {
    if ( version_compare( phpversion(), '5.3', '<' ) ) {
        throw new Exception( 'PHP version does not support ReflectionClass::setAccessible()', __LINE__ );
    }

    $args = func_get_args();
    unset( $args[0], $args[1] );
    $reflection = new ReflectionClass( $class_name );
    $method = $reflection->getMethod( $methodName );
    $method->setAccessible( true );

    $args = array_merge( array( $reflection ), $args );
    return call_user_func_array( array( $method, 'invoke' ), $args );
}
// End of Add multiple products in cart over url

?>
