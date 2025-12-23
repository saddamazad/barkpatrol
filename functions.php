<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'hello_elementor_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array() );
		
		// Enqueue Slick slider assets
		wp_enqueue_style('slick-slider', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
		wp_enqueue_style('slick-theme', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css');
		wp_enqueue_script('slick-slider', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true);
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

// END ENQUEUE PARENT ACTION

require_once("woo-functions.php");
require_once("shortcodes.php");

/**
 * Handle currency selection
 */
function bp_start_currency_session() {
    if (!session_id()) {
        session_start();
    }
    
    if (isset($_POST['currency']) && in_array($_POST['currency'], array('CAD', 'USD'))) {
        $_SESSION['selected_currency'] = $_POST['currency'];
    }
}
add_action('init', 'bp_start_currency_session', 1);

function bp_load_custom_scripts() {
	?>
	<script>
		document.addEventListener("DOMContentLoaded", function () {
			const currencyDropdown = document.querySelector(".currency-dropdown");
			const selectedOption = document.querySelector(".selected-option span");
			const optionsList = document.getElementById("currencies-list");
			const selectElement = document.getElementById("currencies");

			// Show/hide dropdown
			currencyDropdown.addEventListener("click", function () {
				optionsList.style.display = optionsList.style.display === "block" ? "none" : "block";
			});

			// Handle option click
			optionsList.addEventListener("click", function (event) {
				if (event.target.tagName === "LI" || event.target.closest("LI")) {
					let selectedLi = event.target.closest("LI");
					let currency = selectedLi.getAttribute("data-currency");

					// Update the visible selected option
					selectedOption.innerHTML = selectedLi.innerHTML;

					// Update the hidden select field
					selectElement.value = currency;

					// Submit the form
					selectElement.form.submit();
				}
			});

			// Close dropdown when clicking outside
			document.addEventListener("click", function (event) {
				if (!currencyDropdown.contains(event.target)) {
					optionsList.style.display = "none";
				}
			});
		});
		
		
		jQuery(document).ready(function($) {
			$('.featured-products-slider').slick({
				dots: true,
				infinite: true,
				speed: 300,
				slidesToShow: 1,
				adaptiveHeight: true,
				autoplay: true,
				autoplaySpeed: 5000,
				arrows: false,
				/*responsive: [
					{
						breakpoint: 768,
						settings: {
							arrows: false
						}
					}
				]*/
			});
			
			$('.quick-view-products-slider').slick({
				dots: false,
				infinite: true,
				speed: 300,
				slidesToShow: 4,
				adaptiveHeight: true,
				autoplay: false,
				//autoplaySpeed: 5000,
				arrows: false,
				responsive: [
					{
						breakpoint: 768,
						settings: {
							dots: true,
							slidesToShow: 1
						}
					}
				]
			});
			
			jQuery(".products-view-switcher span").on("click", function() {
				let viewType = jQuery(this).attr("data-view");
				
				if( viewType == "list" && !jQuery("ul.products").hasClass("list-view") ) {
					jQuery("ul.products").addClass("list-view");
				} else if( viewType != "list" ) {
					jQuery("ul.products").removeClass("list-view");
				}
				
				jQuery(".products-view-switcher span").removeClass("current-view");
				jQuery(this).addClass("current-view");
			});
			
			jQuery(".review-form-btn").on("click", function(e) {
				e.preventDefault();
				jQuery("#review_form_wrapper").toggle(100);
			});
		});
	</script>
	<?php
}
add_action("wp_head", "bp_load_custom_scripts");

function load_custom_scripts_admin() {
	?>
	<style>
		#profile-page .user-profile-picture {
			display: none;
		}
	</style>
	<?php
}
add_action("admin_head", "load_custom_scripts_admin");
