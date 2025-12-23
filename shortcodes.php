<?php
function get_current_year() {
	return date("Y");
}
add_shortcode("current_year", "get_current_year");

function bp_add_currency_switcher() {
    $current_currency = isset($_SESSION['selected_currency']) ? $_SESSION['selected_currency'] : 'CAD';
	
	$selected_option_html = '<span class="cr-txt">Currency </span><img src="/wp-content/uploads/2025/04/canada-flag.png" alt="canada-flag" />';
	if ($current_currency == 'USD') {
		$selected_option_html = '<span class="cr-txt">Currency </span><img src="/wp-content/uploads/2025/04/usa-flag.png" alt="usa-flag" />';
	}
	ob_start();
    ?>
    <div class="currency-switcher">
        <form action="" method="post">
            <!--<label for="currency"><?php //_e('Currency', 'woocommerce'); ?></label>-->
            <select name="currency" id="currencies" style="display: none;">
                <option value="CAD" <?php selected($current_currency, 'CAD'); ?>>Currency CAD</option>
                <option value="USD" <?php selected($current_currency, 'USD'); ?>>Currency USD</option>
            </select>
			<div class="currency-dropdown">
				<div class="selected-option">
					<span><?php echo $selected_option_html; ?></span>
					<svg xmlns="http://www.w3.org/2000/svg" width="9" height="5" viewBox="0 0 9 5" fill="none">
						<path d="M1.0575 0L4.5 3.09299L7.9425 0L9 0.956873L4.5 5L0 0.956873L1.0575 0Z" fill="#7C7C7C"/>
					</svg>
				</div>
				<ul id="currencies-list" class="currency-options">
					<li data-currency="CAD"><span class="cr-txt">Currency </span><img src="/wp-content/uploads/2025/04/canada-flag.png" alt="canada-flag" /></li>
					<li data-currency="USD"><span class="cr-txt">Currency </span><img src="/wp-content/uploads/2025/04/usa-flag.png" alt="usa-flag" /></li>
				</ul>
			</div>
        </form>
    </div>
    <?php
	return ob_get_clean();
}
add_shortcode('currency_switcher', 'bp_add_currency_switcher');

/**
 * Shortcode to display latest 6 featured products
 */
function bp_display_featured_products_grid() {
    ob_start();
    
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 6,
        'post_status'    => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_visibility',
                'field'    => 'name',
                'terms'    => 'featured',
            ),
        ),
        'orderby'       => 'date',
        'order'         => 'DESC'
    );
    
    $products = new WP_Query($args);
    
    if ($products->have_posts()) {
        echo '<div class="featured-products-grid">';
        
        while ($products->have_posts()) {
            $products->the_post();
            $product = wc_get_product(get_the_ID());
            
            echo '<div class="featured-product">';
            
            // Product Image
            echo '<a href="' . esc_url(get_permalink()) . '">';
            echo $product->get_image('woocommerce_thumbnail');
            echo '</a>';
            
            // Product Title
            echo '<h3><a href="' . esc_url(get_permalink()) . '">' . get_the_title() . '</a></h3>';
			
			echo get_product_color_swatches( get_the_ID() );
            
            // Review Stars and Count - Updated version
            if (wc_review_ratings_enabled()) {
                $review_count = $product->get_review_count();
                $average_rating = $product->get_average_rating();
                
                echo '<div class="product-rating">';
                
                if ($average_rating > 0) {
                    echo '<div class="star-rating" role="img" aria-label="' . sprintf(
                        esc_attr__('Rated %s out of 5', 'woocommerce'), 
                        $average_rating
                    ) . '">';
                    echo '<span style="width:' . (($average_rating / 5) * 100) . '%">';
                    echo sprintf(
                        esc_html__('Rated %s out of 5', 'woocommerce'), 
                        $average_rating
                    );
                    echo '</span>';
                    echo '</div>';
                } else {
                    //echo '<div class="star-rating" style="color:#ccc;">' . __('No reviews yet', 'woocommerce') . '</div>';
                }
                
                if ($review_count > 0) {
					$rvw_txt = 'Reviews';
					if ($review_count == 1) {
						$rvw_txt = 'Review';
					}
                    echo '<span class="review-count">' . esc_html($review_count) . ' ' . $rvw_txt . '</span>';
                } else {
                    //echo '<span class="review-count">(' . __('No reviews', 'woocommerce') . ')</span>';
                }
                
                echo '</div>';
            }
            
            echo '</div>'; // end .featured-product
        }
        
        echo '</div>'; // end .featured-products-grid
        
        wp_reset_postdata();
    } else {
        //echo '<p>No featured products found.</p>';
    }
    
    return ob_get_clean();
}
add_shortcode('featured_products_grid', 'bp_display_featured_products_grid');

/**
 * Shortcode to display latest 6 featured products slider
 */
function bp_display_featured_products_slider() {
    ob_start();
    
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 6,
        'post_status'    => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_visibility',
                'field'    => 'name',
                'terms'    => 'featured',
            ),
        ),
        'orderby'       => 'date',
        'order'         => 'DESC'
    );
    
    $products = new WP_Query($args);
    
    if ($products->have_posts()) {
        echo '<div class="featured-products-slider">';
        
        while ($products->have_posts()) {
            $products->the_post();
            $product = wc_get_product(get_the_ID());
            
            echo '<div class="featured-product">';
            
            // Product Image
            echo '<a href="' . esc_url(get_permalink()) . '">';
            echo $product->get_image('woocommerce_thumbnail');
            echo '</a>';
            
            // Product Title
            echo '<h3><a href="' . esc_url(get_permalink()) . '">' . get_the_title() . '</a></h3>';
            
            // Review Stars and Count - Updated version
            if (wc_review_ratings_enabled()) {
                $review_count = $product->get_review_count();
                $average_rating = $product->get_average_rating();
                
                echo '<div class="product-rating">';
                
                if ($average_rating > 0) {
                    echo '<div class="star-rating" role="img" aria-label="' . sprintf(
                        esc_attr__('Rated %s out of 5', 'woocommerce'), 
                        $average_rating
                    ) . '">';
                    echo '<span style="width:' . (($average_rating / 5) * 100) . '%">';
                    echo sprintf(
                        esc_html__('Rated %s out of 5', 'woocommerce'), 
                        $average_rating
                    );
                    echo '</span>';
                    echo '</div>';
                } else {
                    //echo '<div class="star-rating" style="color:#ccc;">' . __('No reviews yet', 'woocommerce') . '</div>';
                }
                
                if ($review_count > 0) {
					$rvw_txt = 'Reviews';
					if ($review_count == 1) {
						$rvw_txt = 'Review';
					}
                    echo '<span class="review-count">' . esc_html($review_count) . ' ' . $rvw_txt . '</span>';
                } else {
                    //echo '<span class="review-count">(' . __('No reviews', 'woocommerce') . ')</span>';
                }
                
                echo '</div>';
            }
            
            echo '</div>'; // end .featured-product
        }
        
        echo '</div>'; // end .featured-products-slider
        
        wp_reset_postdata();
    } else {
        //echo '<p>No featured products found.</p>';
    }
    
    return ob_get_clean();
}
add_shortcode('featured_products_slider', 'bp_display_featured_products_slider');

/**
 * Recent Product Reviews Slider Shortcode
 */
function bp_recent_reviews_slider_shortcode($atts) {
    /*$atts = shortcode_atts(array(
        'limit' => 5,
        'show_rating' => true,
        'show_date' => true,
        'show_product' => true
    ), $atts, 'recent_reviews_slider');*/

    // Get recent reviews
    $args = array(
        'status' => 'approve',
        'post_type' => 'product',
        'number' => 10,
        'orderby' => 'comment_date',
        'order' => 'DESC'
    );
    
    $reviews = get_comments($args);
    
    if (empty($reviews)) {
        return '<p>No reviews found.</p>';
    }
    
    ob_start();
    ?>
    <div class="recent-reviews-slider">
        <?php foreach ($reviews as $review) : 
            $product = wc_get_product($review->comment_post_ID);
            $rating = intval(get_comment_meta($review->comment_ID, 'rating', true));
			$author_id = $review->user_id;
            ?>
            <div class="review-item">
                <?php if ($rating) : ?>
					<div class="review-author-thumb">
						<?php if($author_id) { echo get_avatar( $author_id, 114 ); } ?>
					</div>
                    <div class="star-rating">
						
                        <?php echo wc_get_rating_html($rating); ?>
                    </div>
                <?php endif; ?>
                
                <div class="review-content">
                    "<?php echo esc_html($review->comment_content); ?>"
                </div>
                
                <div class="review-author">
                    <?php echo esc_html($review->comment_author); ?>
                </div>
                
                <!--<div class="review-meta">
                    <?php if ($product) : ?>
                        <span class="review-product">
                            <?php //echo esc_html__('on', 'woocommerce') . ' <a href="' . esc_url(get_permalink($product->get_id())) . '">' . esc_html($product->get_name()) . '</a>'; ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php //if ($atts['show_date']) : ?>
                        <span class="review-date">
                            <?php //echo esc_html(date_i18n(get_option('date_format'), strtotime($review->comment_date))); ?>
                        </span>
                    <?php //endif; ?>
                </div>-->
            </div>
        <?php endforeach; ?>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('.recent-reviews-slider').slick({
            dots: false,
            infinite: true,
            speed: 300,
            slidesToShow: 3,
            adaptiveHeight: true,
            autoplay: false,
            //autoplaySpeed: 5000,
            arrows: true,
			prevArrow: '<svg xmlns="http://www.w3.org/2000/svg" class="slick-prev" width="38" height="38" viewBox="0 0 38 38" fill="none"><path d="M27.9117 0C28.2339 0 28.5637 0.127434 28.8111 0.374768C29.3057 0.869436 29.3057 1.6789 28.8111 2.17357L11.8797 19.1124L28.5637 35.7965C29.0584 36.2911 29.0584 37.1007 28.5637 37.5953C28.0691 38.09 27.2596 38.09 26.765 37.5953L9.18155 20.0119C8.68688 19.5172 8.68688 18.7077 9.18155 18.2131L27.0123 0.374805C27.2596 0.127471 27.5894 7.42188e-05 27.9117 7.42188e-05L27.9117 0Z" fill="#A6A6A6"/></svg>',
			nextArrow: '<svg xmlns="http://www.w3.org/2000/svg" class="slick-next" width="38" height="38" viewBox="0 0 38 38" fill="none"><path d="M10.0883 0C9.76605 0 9.43626 0.127434 9.18893 0.374768C8.69426 0.869436 8.69426 1.6789 9.18893 2.17357L26.1203 19.1124L9.43626 35.7965C8.9416 36.2911 8.9416 37.1007 9.43626 37.5953C9.93093 38.09 10.7404 38.09 11.235 37.5953L28.8185 20.0119C29.3131 19.5172 29.3131 18.7077 28.8185 18.2131L10.9877 0.374805C10.7404 0.127471 10.4106 7.42188e-05 10.0883 7.42188e-05L10.0883 0Z" fill="#A6A6A6"/></svg>',
            responsive: [
                {
                    breakpoint: 768,
                    settings: {
                        arrows: false,
						dots: true,
						slidesToShow: 1,
                    }
                }
            ]
        });
    });
    </script>
    <?php
    
    return ob_get_clean();
}
add_shortcode('recent_reviews_slider', 'bp_recent_reviews_slider_shortcode');

/**
 * Shortcode to display latest 4 featured products slider
 */
function bp_display_quick_view_products_slider() {
    ob_start();
    
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 4,
        'post_status'    => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_visibility',
                'field'    => 'name',
                'terms'    => 'featured',
            ),
        ),
        'orderby'       => 'date',
        'order'         => 'DESC'
    );
    
    $products = new WP_Query($args);
    
    if ($products->have_posts()) {
        echo '<div class="quick-view-products-slider">';
        
        while ($products->have_posts()) {
            $products->the_post();
            $product = wc_get_product(get_the_ID());
            
            echo '<div class="featured-product">';
            
            // Product Image
            echo '<a href="' . esc_url(get_permalink()) . '">';
            echo $product->get_image('woocommerce_thumbnail');
            echo '</a>';
            
            // Product Title
            echo '<h3><a href="' . esc_url(get_permalink()) . '">' . get_the_title() . '</a></h3>';
            
            // Review Stars and Count - Updated version
            if (wc_review_ratings_enabled()) {
                $review_count = $product->get_review_count();
                $average_rating = $product->get_average_rating();
                
                echo '<div class="product-rating">';
                
                if ($average_rating > 0) {
                    echo '<div class="star-rating" role="img" aria-label="' . sprintf(
                        esc_attr__('Rated %s out of 5', 'woocommerce'), 
                        $average_rating
                    ) . '">';
                    echo '<span style="width:' . (($average_rating / 5) * 100) . '%">';
                    echo sprintf(
                        esc_html__('Rated %s out of 5', 'woocommerce'), 
                        $average_rating
                    );
                    echo '</span>';
                    echo '</div>';
                } else {
                    //echo '<div class="star-rating" style="color:#ccc;">' . __('No reviews yet', 'woocommerce') . '</div>';
                }
                
                if ($review_count > 0) {
					$rvw_txt = 'Reviews';
					if ($review_count == 1) {
						$rvw_txt = 'Review';
					}
                    echo '<span class="review-count">' . esc_html($review_count) . ' ' . $rvw_txt . '</span>';
                } else {
                    //echo '<span class="review-count">(' . __('No reviews', 'woocommerce') . ')</span>';
                }
				
				echo '<a href="' . esc_url(get_permalink()) . '" class="quick-view-button">Quick View</a>';
                
                echo '</div>';
            }
            
            echo '</div>'; // end .featured-product
        }
        
        echo '</div>'; // end .featured-products-slider
        
        wp_reset_postdata();
    } else {
        //echo '<p>No featured products found.</p>';
    }
    
    return ob_get_clean();
}
add_shortcode('quick_view_products_slider', 'bp_display_quick_view_products_slider');

/**
 * Shortcode to display WooCommerce products with 'show_on_banner' meta field set to 'Yes'
 */
function bp_get_banner_products($atts) {
    // Setup query args
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 2,
        'meta_query'     => array(
            array(
                'key'     => 'show_on_banner',
                'value'   => 'Yes',
                'compare' => '='
            )
        )
    );
    
    // Get products
    $products = new WP_Query($args);
    
    // Start output buffering
    ob_start();
    
    if ($products->have_posts()) {
        echo '<div class="banner-products-container">';
        
        while ($products->have_posts()) : $products->the_post();
            global $product;
            
            echo '<div class="banner-product">';
            
            // Product image
            echo '<div class="banner-product-image">';
            echo $product->get_image('thumbnail'); // You can change 'medium' to other sizes
            echo '</div>';
            
            // Product title
            //echo '<h3 class="banner-product-title">' . get_the_title() . '</h3>';
            
            // Product short description
            echo '<div class="banner-product-description">';
            echo apply_filters('the_excerpt', get_the_excerpt());
            echo '</div>';
            
            // Product price
            echo '<div class="banner-product-price">';
            echo $product->get_price_html();
            echo '</div>';
		
			echo '<a href="'.get_permalink().'" class="bp-svg-btn"><svg xmlns="http://www.w3.org/2000/svg" width="21" height="17" viewBox="0 0 21 17" fill="none"><path d="M2.2998 9.81836H18.0146C18.7429 9.81836 19.333 9.22826 19.333 8.5C19.333 7.77174 18.7429 7.18164 18.0146 7.18164H2.2998C1.57154 7.18164 0.981445 7.77174 0.981445 8.5C0.981445 9.22826 1.57154 9.81836 2.2998 9.81836Z" fill="white"/>
<path d="M11.9771 16.0542C12.2977 16.0542 12.6194 15.9377 12.873 15.7025L19.5966 9.46664C19.8677 9.2151 20.0212 8.86126 20.0185 8.49106C20.0159 8.12139 19.8582 7.76913 19.5835 7.52128L12.6753 1.28544C12.1347 0.797642 11.301 0.840357 10.8132 1.38088C10.3254 1.92141 10.3681 2.75514 10.9087 3.24293L16.7474 8.51321L11.08 13.7692C10.5464 14.2644 10.5147 15.0987 11.0099 15.6323C11.2699 15.9124 11.6227 16.0542 11.9771 16.0542Z" fill="white"/></svg></a>';
            
            echo '</div>'; // end .banner-product
            
        endwhile;
        
        echo '</div>'; // end .banner-products-container
    } else {
        //echo '<p>No featured products found.</p>';
    }
    
    wp_reset_postdata();
    
    // Return the buffered content
    return ob_get_clean();
}
add_shortcode('banner_products', 'bp_get_banner_products');
