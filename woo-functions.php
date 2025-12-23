<?php
/**
 * Add USD price fields to product edit page
 */
function bp_add_usd_price_fields() {
    global $woocommerce, $post;

    echo '<div class="options_group">';
    woocommerce_wp_text_input(array(
        'id' => '_usd_regular_price',
        'label' => __('Regular Price (USD)', 'woocommerce'),
        'placeholder' => '',
        'desc_tip' => 'true',
        'description' => __('Enter the regular price in USD.', 'woocommerce'),
        'type' => 'number',
        'custom_attributes' => array(
            'step' => 'any',
            'min' => '0'
        )
    ));

    woocommerce_wp_text_input(array(
        'id' => '_usd_sale_price',
        'label' => __('Sale Price (USD)', 'woocommerce'),
        'placeholder' => '',
        'desc_tip' => 'true',
        'description' => __('Enter the sale price in USD.', 'woocommerce'),
        'type' => 'number',
        'custom_attributes' => array(
            'step' => 'any',
            'min' => '0'
        )
    ));
    echo '</div>';
}
add_action('woocommerce_product_options_pricing', 'bp_add_usd_price_fields');

/**
 * Save USD price fields
 */
function bp_save_usd_price_fields($post_id) {
    $usd_regular_price = isset($_POST['_usd_regular_price']) ? wc_format_decimal($_POST['_usd_regular_price']) : '';
    $usd_sale_price = isset($_POST['_usd_sale_price']) ? wc_format_decimal($_POST['_usd_sale_price']) : '';

    update_post_meta($post_id, '_usd_regular_price', $usd_regular_price);
    update_post_meta($post_id, '_usd_sale_price', $usd_sale_price);
}
add_action('woocommerce_process_product_meta_simple', 'bp_save_usd_price_fields');

// 1. Add custom field input @ Product Data > Variations > Single Variation
add_action( 'woocommerce_variation_options_pricing', 'bp_add_custom_field_to_variations', 10, 3 );
function bp_add_custom_field_to_variations( $loop, $variation_data, $variation ) {
	woocommerce_wp_text_input( array(
		'id' => '_usd_regular_price[' . $loop . ']',
		'class' => 'short',
		'label' => __( 'Regular Price (USD)', 'woocommerce' ),
		'value' => get_post_meta( $variation->ID, '_usd_regular_price', true )
	) );
	woocommerce_wp_text_input( array(
		'id' => '_usd_sale_price[' . $loop . ']',
		'class' => 'short',
		'label' => __( 'Sale Price (USD)', 'woocommerce' ),
		'value' => get_post_meta( $variation->ID, '_usd_sale_price', true )
	) );
}
 
// 2. Save custom field on product variation save
add_action( 'woocommerce_save_product_variation', 'bp_save_custom_field_variations', 10, 2 );
function bp_save_custom_field_variations( $variation_id, $i ) {
	$usd_regular_price = $_POST['_usd_regular_price'][$i];
	if ( isset( $usd_regular_price ) ) update_post_meta( $variation_id, '_usd_regular_price', esc_attr( $usd_regular_price ) );
	
	$usd_sale_price = $_POST['_usd_sale_price'][$i];
	if ( isset( $usd_sale_price ) ) update_post_meta( $variation_id, '_usd_sale_price', esc_attr( $usd_sale_price ) );
}
 
// 3. Store custom field value into variation data
add_filter( 'woocommerce_available_variation', 'bp_add_custom_field_variation_data' );
function bp_add_custom_field_variation_data( $variations ) {
	$variations['_usd_regular_price'] = '<div class="woocommerce_usd_regular_price">Regular Price (USD): <span>' . get_post_meta( $variations[ 'variation_id' ], '_usd_regular_price', true ) . '</span></div>';
	
	$variations['_usd_sale_price'] = '<div class="woocommerce_usd_sale_price">Sale Price (USD): <span>' . get_post_meta( $variations[ 'variation_id' ], '_usd_sale_price', true ) . '</span></div>';
	
	return $variations;
}

/**
 * Add currency parameter to product URLs to bust cache
 */
function bp_add_currency_to_product_urls($url, $product) {
    $current_currency = isset($_SESSION['selected_currency']) ? $_SESSION['selected_currency'] : 'CAD';
    if ($current_currency !== 'CAD') {
        $url = add_query_arg('currency', $current_currency, $url);
    }
    return $url;
}
add_filter('woocommerce_product_get_permalink', 'bp_add_currency_to_product_urls', 10, 2);

/**
 * Modify displayed prices based on selected currency
 */
function bp_convert_prices_based_on_currency($price, $product) {
    if (is_admin()) return $price;
    
    $current_currency = isset($_SESSION['selected_currency']) ? $_SESSION['selected_currency'] : 'CAD';
    
    if ($current_currency === 'USD') {
        $usd_price = '';
        
        if ($product->is_on_sale() && $product->get_meta('_usd_sale_price')) {
            $usd_price = $product->get_meta('_usd_sale_price');
        } elseif ($product->get_meta('_usd_regular_price')) {
            $usd_price = $product->get_meta('_usd_regular_price');
        }
        
        if ($usd_price !== '') {
            return wc_price($usd_price, array('currency' => 'USD'));
        }
    }
    
    // Default to CAD
    return wc_price($price, array('currency' => 'CAD'));
}
//add_filter('woocommerce_get_price_html', 'bp_convert_prices_based_on_currency', 10, 2);

/**
 * Modify cart/checkout prices based on selected currency
 */
function bp_convert_cart_prices($price, $product, $quantity, $cart) {
    $current_currency = isset($_SESSION['selected_currency']) ? $_SESSION['selected_currency'] : 'CAD';
    
    if ($current_currency === 'USD') {
        $usd_price = $product->get_meta('_usd_regular_price');
        if ($usd_price) {
            return wc_price($usd_price * $quantity, array('currency' => 'USD'));
        }
    }
    
    return wc_price($price, array('currency' => 'CAD'));
}
//add_filter('woocommerce_cart_item_price', 'bp_convert_cart_prices', 10, 4);

//Hide “From:$X”
add_filter('woocommerce_get_price_html', 'bp_hide_woo_variation_price', 10, 2);
function bp_hide_woo_variation_price( $v_price, $v_product ) {
	$v_product_types = array( 'variable');
	if ( in_array ( $v_product->product_type, $v_product_types ) && !(is_shop()) ) {
		return '';
	}
	// return regular price
	return $v_price;
}

// Hooks for simple, grouped, external and variation products
add_filter('woocommerce_product_get_price', 'cvn_custom_price_role', 99, 2 );
add_filter('woocommerce_product_get_regular_price', 'cvn_custom_price_role', 99, 2 );
add_filter('woocommerce_product_variation_get_regular_price', 'cvn_custom_price_role', 99, 2 );
add_filter('woocommerce_product_variation_get_price', 'cvn_custom_price_role', 99, 2 );
function cvn_custom_price_role( $price, $product ) {
	$price = bp_custom_price_handling( $price, $product );  
	return $price;
}

// Variable (price range)
//add_filter('woocommerce_variation_prices_price', 'bp_custom_variable_price', 99, 3 );
//add_filter('woocommerce_variation_prices_regular_price', 'bp_custom_variable_price', 99, 3 );
function bp_custom_variable_price( $price, $variation, $product ) {
	$price = bp_custom_price_handling( $price, $product );  
	return $price;
}
 
function bp_custom_price_handling($price, $product) {
	if (is_admin()) return $price;
    
    $current_currency = isset($_SESSION['selected_currency']) ? $_SESSION['selected_currency'] : 'CAD';
	
	if ($current_currency === 'USD') {
		$usd_price = '';
        
        if ($product->is_on_sale() && $product->get_meta('_usd_sale_price')) {
            $usd_price = $product->get_meta('_usd_sale_price');
        } elseif ($product->get_meta('_usd_regular_price')) {
            $usd_price = $product->get_meta('_usd_regular_price');
        }
        
        if ($usd_price !== '') {
            //return wc_price($usd_price, array('currency' => 'USD'));
			return $usd_price;
        }
	}

	return $price;
}

remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
add_action( 'woocommerce_archive_description', 'woocommerce_result_count', 8 );

function show_recent_reviews_in_shop_archive() {
	echo '<h2 class="woo-archive-sec-title">Customer Reviews</h2>';
	
	echo do_shortcode("[recent_reviews_slider]");
}
add_action( 'woocommerce_after_shop_loop', 'show_recent_reviews_in_shop_archive', 10 );

// Remove default thumbnail display
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);

remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );

add_action( 'woocommerce_shop_loop_item_title', 'bp_custom_loop_product_title', 10 );
function bp_custom_loop_product_title() {
	?>
	<h2 class="woocommerce-loop-product__title"><a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a></h2>
	<?php
}

// Add custom thumbnail display in separate div
add_action('woocommerce_before_shop_loop_item_title', 'bp_custom_loop_product_thumbnail', 10);
function bp_custom_loop_product_thumbnail() {
	?>
    <div class="product-thumbnail-wrapper">
		<a href="<?php echo esc_url(get_permalink()); ?>">
			<?php woocommerce_template_loop_product_thumbnail(); ?>
		</a>
    </div>
	<?php
}

// Wrap product info in separate div
add_action('woocommerce_shop_loop_item_title', 'open_product_info_wrapper', 5);
function open_product_info_wrapper() {
    echo '<div class="product-info-wrapper">';
}

add_action('woocommerce_after_shop_loop_item', 'close_product_info_wrapper', 10);
function close_product_info_wrapper() {
    echo '</div>';
}

//remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 20 );

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 10 );

add_action( 'woocommerce_before_add_to_cart_quantity', 'bp_show_quantity_label', 10 );
function bp_show_quantity_label() {
	echo '<div class="quantity-label">Quantity</div>';
}

add_action("woocommerce_share", "bp_show_product_content");
function bp_show_product_content() {
	global $post;
    $content = apply_filters( 'the_content', $post->post_content );
    echo '<div class="custom-product-description" style="margin-top:20px;">' . $content . '</div>';
}

add_filter( 'woocommerce_product_tabs', 'bp_remove_description_tab', 98 );
function bp_remove_description_tab( $tabs ) {
    unset( $tabs['description'] );
	unset( $tabs['additional_information'] );
	unset( $tabs['reviews'] );
	
	$tabs['materials'] = array(
		'title'    => __( 'Materials', 'woocommerce' ),
		'priority' => 10,
		'callback' => 'materials_tab_content'
	);
	$tabs['care_guide'] = array(
		'title'    => __( 'Care', 'woocommerce' ),
		'priority' => 11,
		'callback' => 'care_guide_tab_content'
	);
	$tabs['warranty'] = array(
		'title'    => __( 'Warranty', 'woocommerce' ),
		'priority' => 12,
		'callback' => 'warranty_tab_content'
	);
	$tabs['shipping_refunds_and_returns'] = array(
		'title'    => __( 'Shipping, Refunds and Returns', 'woocommerce' ),
		'priority' => 13,
		'callback' => 'shipping_refunds_tab_content'
	);
	
    return $tabs;
}

function materials_tab_content() {
	global $post;
	
	$content = get_field('materials', $post->ID);
	
    echo wpautop($content);
}

function care_guide_tab_content() {
	global $post;
	
	$content = get_field('care_guide', $post->ID);
	
    echo wpautop($content);
}

function warranty_tab_content() {
	global $post;
	
	$content = get_field('warranty', $post->ID);
	
    echo wpautop($content);
}

function shipping_refunds_tab_content() {
	global $post;
	
	$content = get_field('shipping_refunds_and_returns', $post->ID);
	
    echo wpautop($content);
}

add_action('woocommerce_after_single_product_summary', 'bp_custom_product_rating_breakdown', 20);
function bp_custom_product_rating_breakdown() {
    global $product;

    if (!$product) return;

    $product_id = $product->get_id();
    $reviews = get_comments([
        'post_id' => $product_id,
        'status'  => 'approve',
        'type'    => 'review',
    ]);

    // Initialize counters
    $rating_counts = [
        5 => 0,
        4 => 0,
        3 => 0,
        2 => 0,
        1 => 0,
    ];

    foreach ($reviews as $review) {
        $rating = intval(get_comment_meta($review->comment_ID, 'rating', true));
        if ($rating >= 1 && $rating <= 5) {
            $rating_counts[$rating]++;
        }
    }

    $total_reviews = array_sum($rating_counts);
    $average_rating = $product->get_average_rating();
    
    if ($total_reviews === 0) {
        //echo '<p>No reviews yet.</p>';
        //return;
    }

    echo '<div class="custom-rating-section" style="margin-top:30px;">';
	echo '<h3>Customer Reviews</h3>';

    echo '<div class="customer-reviews-bar">';

    // === Left Column: Breakdown ===
    echo '<div class="rating-breakdown">';

    foreach (array_reverse($rating_counts, true) as $stars => $count) {
        $percent = $total_reviews ? ($count / $total_reviews) * 100 : 0;
        echo '<div class="rating-row" style="display: flex; align-items: center; margin-bottom: 6px;">';
        echo '<span class="rating-number" style="width:38px;"><strong>' . $stars . '</strong>★</span>';
        echo '<div style="flex: 1; background: #D9D9D9; border-radius: 4px; overflow: hidden; height: 8px; margin: 0 10px;">';
        echo '<div style="width: ' . $percent . '%; height: 100%; background: #46A6AF;"></div>';
        echo '</div>';
        echo '<span>' . $count . '</span>';
        echo '</div>';
    }
    echo '</div>';

    // === Right Column: Average + Total ===
    echo '<div class="rating-summary" style="text-align: center;">';
	echo '<div class="star-rating" style="float: none;" role="img" aria-label="Rated ' . round($average_rating, 1) . ' out of 5"><span style="width:100%">Rated <strong class="rating">' . round($average_rating, 1) . '</strong> out of 5</span></div>';
    echo '<div style="font-size: 70px; line-height: 70px; font-weight: 400; margin-top: 10px; color: #464646;">' . round($average_rating, 1) . '</div>';
    echo '<p>Based on ' . $total_reviews . ' review' . ($total_reviews > 1 ? 's' : '') . '</p>';
    echo '</div>';
	
	echo '<div class="write-review"><a href="#" class="review-form-btn">Write a Review</a></div>';
	
	echo '</div>'; // <!-- .customer-reviews-bar -->

    echo '</div>';
}

add_action( 'woocommerce_after_single_product_summary', 'bp_show_reviews_below_tabs', 20 );
function bp_show_reviews_below_tabs() {
    global $product;

    echo '<div class="custom-reviews-section" style="margin-top: 40px;">';
    echo '<h2 class="woocommerce-Reviews-title">Customer Reviews</h2>';

    // Load the standard WooCommerce reviews/comments template
    comments_template();

    echo '</div>';
}

add_filter( 'woocommerce_product_related_products_heading', 'bp_custom_related_products_heading' );
function bp_custom_related_products_heading() {
    return 'Other recommended products';
}

remove_action("woocommerce_review_before", "woocommerce_review_display_gravatar", 10);
remove_action("woocommerce_review_meta", "woocommerce_review_display_meta", 10);
add_action("woocommerce_review_after_comment_text", "woocommerce_review_display_meta", 10);

add_filter( 'woocommerce_disable_admin_bar', '__return_false' );
add_filter( 'woocommerce_prevent_admin_access', '__return_false' );

// https://wordpress.org/plugins/woo-variation-swatches/ (Swatches plugin)
function get_product_color_swatches( $product_id ) {
    $product = wc_get_product( $product_id );

    $colors = [];

    // Loop through variations
    if ( $product->is_type( 'variable' ) ) {
        $variations = $product->get_available_variations();

        foreach ( $variations as $variation ) {
            if ( isset( $variation['attributes']['attribute_pa_color'] ) ) {
                $color_slug = $variation['attributes']['attribute_pa_color'];
                if ( ! in_array( $color_slug, $colors ) ) {
                    $colors[] = $color_slug;
                }
            }
        }
    }

    if ( empty( $colors ) ) {
        return '';
    }

	$padding_top = 7;
    if( is_shop() || is_product() ) {
		$padding_top = 0;
	}
	
    $output = '<div class="bp-color-swatches" style="padding-top: '.$padding_top.'px;">';

    foreach ( $colors as $color_slug ) {

        $term = get_term_by( 'slug', $color_slug, 'pa_color' );
        if ( ! $term ) continue;

		$color_value = get_term_meta( $term->term_id, 'product_attribute_color', true );
		
        $output .= '<span class="bp-swatch" 
                       style="background-color:' . esc_attr( $color_value ) . '; width: 22px; height: 22px; display: inline-block; border-radius: 50%; margin: 0 2px; border: 1px solid #000;" 
                       title="' . esc_attr( $term->name ) . '"></span>';
    }

    $output .= '</div>';

    return $output;
}

add_action("woocommerce_after_shop_loop_item_title", "bp_show_product_swatches_in_shop_loop", 1);
function bp_show_product_swatches_in_shop_loop() {
	global $product;
	echo get_product_color_swatches( $product->get_id() );
}
