<?php
/**
 * Created by PhpStorm.
 * User: HanhDo
 * Date: 1/17/2019
 * Time: 11:41 AM
 */
$st_is_woocommerce_checkout = apply_filters('st_is_woocommerce_checkout',false);
$menu_style = st()->get_option( 'menu_style_modern', "" );
$color_cart = '';
$stroke_cart = false;

if($menu_style == '2') {
    $color_cart = '#fff';
    $stroke_cart = true;
}
if($st_is_woocommerce_checkout and function_exists('WC')){
    $cart_url = wc_get_cart_url();
    $cart_total_item = (int) WC()->cart->get_cart_contents_count();
    $cart_total_amount = WC()->cart->get_cart_subtotal();
    ?>
    <li class="dropdown dropdown-minicart">
        <div class="mini-cart dropdown-toggle" role="button" id="dropdown-mini-cart" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Voir le menu panier">
            <?php if ($cart_total_item > 0) { ?>
                <div class="cart-caret"><?php echo esc_html($cart_total_item) ?></div>
            <?php } ?>
            <?php
            if(isset($icon)) {
                echo balanceTags($icon);
            }else{
                echo TravelHelper::getNewIcon('ico_card', $color_cart, '26px', '26px', $stroke_cart);
            }
            ?>
        </div>
        <ul class="woocommerce-mini-cart cart_list product_list_widget  dropdown-menu dropdown-menu-end" aria-labelledby="dropdown-mini-cart">
            <li class="heading">
                <div class="st-heading-section"><?php echo esc_html__('Your Cart', 'traveler') ?></div>
            </li>
            <?php
            do_action( 'woocommerce_before_mini_cart' );
            $items = WC()->cart->get_cart();
            if (!empty($items)):
                do_action( 'woocommerce_before_mini_cart_contents' );
                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
                    $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                    $post_id = (int) $cart_item['st_booking_data']['st_booking_id'];

                    $post_title = $_product->get_title();
                    if( get_post_type( $post_id ) == 'st_hotel' ){
                        $room_id = (int) get_post_meta( $_product->get_id(), 'room_id', true );
                        $post_title = get_the_title( $room_id );
                    }
                    $quantity = (int) $cart_item['quantity'];
                    $price = (float) $cart_item['line_total'];
                    $tax = (float) $cart_item['line_tax'];
                    $price = $price + $tax;
                    $product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                    ?>
                    <li class="cart-item">
                        <div class="media d-flex align-items-top">
                            <div class="media-left">
                                <?php
                                if( has_post_thumbnail( $post_id ) ){
                                    echo get_the_post_thumbnail( $post_id, 'thumbnail', array('class' => 'img-responsive media-object', 'alt' => TravelHelper::get_alt_image(get_post_thumbnail_id($post_id ))) );
                                }
                                ?>
                            </div>
                            <div class="media-body  ms-3">
                                <?php
								if ( $cart_item['st_booking_data']['st_booking_post_type'] == 'car_transfer' ) :
									$room_id = (int) get_post_meta( $_product->ID, 'room_id', true );
                                    ?>
                                    <h4 class="media-heading">
										<?= __( 'Transfer: ', 'traveler' ) ?>
										<a class="st-link c-main" href="<?php echo get_the_permalink($room_id) ?>"><?php echo esc_html($post_title); ?></a>
                                    </h4>
								<?php elseif( get_post_type( $post_id ) == 'st_hotel'):
                                    $room_id = $post_id;
									$post_title = get_the_title( $room_id );
                                    ?>
                                    <h4 class="media-heading"><a class="st-link c-main"
                                                                 href="<?php echo get_the_permalink($room_id) ?>"><?php echo esc_html($post_title); ?></a>
                                    </h4>
                                <?php else: ?>
                                    <h4 class="media-heading"><a class="st-link c-main"
                                                                 href="<?php echo get_the_permalink($post_id) ?>"><?php echo esc_html($post_title); ?></a>
                                    </h4>
                                <?php endif; ?>
                                <div class="price-wrapper"><?php echo __('Price', 'traveler') ?>:
                                    <span class="price"><?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity">' . sprintf( '%s &times; %s', $cart_item['quantity'], $product_price ) . '</span>', $cart_item, $cart_item_key );?></span>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php
                endforeach;
                ?>
                <li class="cart-total">
                    <div class="sub-total"> <span
                                class="price woocommerce-mini-cart__total total"><?php
                                /**
                                 * Hook: woocommerce_widget_shopping_cart_total.
                                 *
                                 * @hooked woocommerce_widget_shopping_cart_subtotal - 10
                                 */
                                do_action( 'woocommerce_widget_shopping_cart_total' );
                                ?></span>
                    </div>
                    <?php
                        do_action( 'woocommerce_widget_shopping_cart_before_buttons' );
                    ?>
                    <a href="<?php echo add_query_arg(['action' => 'st-remove-cart', 'security' => wp_create_nonce('st-security')]); ?>"
                       class="btn btn-danger btn-full upper">
                        <?php echo __('Remove Cart', 'traveler') ?>
                    </a>
                    <a href="<?php echo esc_url(get_permalink( wc_get_page_id( 'checkout' ) )) ?>"
                       class="btn btn-full upper mt10"><?php echo __('Pay Now', 'traveler') ?></a>
                </li>
            <?php
            do_action( 'woocommerce_after_mini_cart' );
            else:
                ?>
                <li><div class="col-lg-12 cart-text-empty text-warning"><?php echo __('Your cart is empty', 'traveler'); ?></div></li>
            <?php
            endif;
            ?>
        </ul>
    </li>
    <?php
}else {
    ?>
    <li class="dropdown dropdown-minicart">
        <?php
        $check_out_url = (int)st()->get_option('page_checkout', '');
        $check_out_url = get_permalink($check_out_url);
        $cart_total_item = (int)STCart::count();
        $cart_total_amount = (float)(STCart::check_cart()) ? STPrice::getTotal() : 0;
        $post_id_global = 0;
        ?>
        <div class="mini-cart dropdown-toggle" role="button" id="dropdown-mini-cart" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Voir le panier">
            <?php if ($cart_total_item > 0) { ?>
                <div class="cart-caret"><?php echo esc_html($cart_total_item) ?></div>
            <?php } ?>
            <?php
            if(isset($icon)){
                echo balanceTags($icon);
            }else{
                echo TravelHelper::getNewIcon('ico_card', $color_cart, '26px', '26px', $stroke_cart);
            }
            ?>
        </div>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdown-mini-cart">
            <li class="heading">
                <div class="st-heading-section"><?php echo esc_html__('Your Cart', 'traveler') ?></div>
            </li>
            <?php
            if (STCart::check_cart()):
                $items = STCart::get_carts();
                foreach ($items as $post_id => $value):
                    $post_id_global = $post_id;

                    $post_title = get_the_title($post_id);
                    if (get_post_type($post_id) == 'st_hotel') {
                        $room_id = (int)$value['data']['room_id'];
                        $post_title = get_the_title($room_id);
                    }



                    $quantity = (int)count($items);
                    $price = (float)STPrice::getTotal();
                    if($post_id == 'travelport_api'){
                        $post_title = $value['data']['fromCode'] . ' -> ' . $value['data']['toCode'];
                    }

                    if ($post_id == 'car_transfer') {

                        $car_transfer_id = (int)$value['data']['car_id'];
                        $post_title = get_the_title($car_transfer_id);
                        $post_id = $car_transfer_id;
                    }

                    ?>
                    <li class="cart-item">
                        <div class="media d-flex align-items-top">
                            <?php if($post_id != 'travelport_api'){ ?>
                            <div class="media-left">
                                <?php
                                if (has_post_thumbnail($post_id)) {
                                    echo get_the_post_thumbnail($post_id, [70, 70], ['class' => 'media-object', 'alt' => TravelHelper::get_alt_image()]);
                                }
                                ?>
                            </div>
                            <?php } ?>
                            <div class="media-body  ms-3">
                                <?php
                                if (get_post_type($post_id) == 'st_hotel'):
                                    $room_id = (int)$value['data']['room_id'];
                                    ?>
                                    <div class="media-heading"><a class="st-link c-main"
                                                                 href="<?php echo get_the_permalink($room_id) ?>"><?php echo esc_html($post_title); ?></a>
                                    </div>
                                <?php else: ?>
                                    <div class="media-heading"><a class="st-link c-main"
                                                                 href="<?php echo get_the_permalink($post_id) ?>"><?php echo esc_html($post_title); ?></a>
                                    </div>
                                <?php endif; ?>

                                <div class="price-wrapper"><?php echo __('Price', 'traveler') ;?>:
                                    <span class="price"><?php echo TravelHelper::format_money($price); ?></span>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo add_query_arg(['action' => 'st-remove-cart', 'security' => wp_create_nonce('st-security')]); ?>"
                           class="cart-delete-item"><i class="fa">
                                <svg width="16px" height="16px" viewBox="0 0 16 16" version="1.1"
                                     xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                    <g id="Menu" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"
                                       stroke-linecap="round" stroke-linejoin="round">
                                        <g id="Menu-Mega" transform="translate(-1355.000000, -383.000000)"
                                           stroke="#A0A9B2">
                                            <g id="cart" transform="translate(1120.000000, 130.000000)">
                                                <g id="hotel" transform="translate(0.000000, 198.000000)">
                                                    <g id="Group" transform="translate(236.000000, 56.000000)">
                                                        <g id="bin-1">
                                                            <path d="M0,2 L14,2" id="Shape"></path>
                                                            <path d="M8.5,0 L5.5,0 C4.94771525,0 4.5,0.44771525 4.5,1 L4.5,2 L9.5,2 L9.5,1 C9.5,0.44771525 9.05228475,0 8.5,0 Z"
                                                                  id="Shape"></path>
                                                            <path d="M5.5,10.5 L5.5,5.5" id="Shape"></path>
                                                            <path d="M8.5,10.5 L8.5,5.5" id="Shape"></path>
                                                            <path d="M11.5766667,13.0826667 C11.5336578,13.6011549 11.100269,14.0000465 10.58,14 L3.42066667,14 C2.9003977,14.0000465 2.4670089,13.6011549 2.424,13.0826667 L1.5,2 L12.5,2 L11.5766667,13.0826667 Z"
                                                                  id="Shape"></path>
                                                        </g>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    </g>
                                </svg>
                            </i></a>
                    </li>
                <?php
                endforeach;
                ?>
                <li class="cart-total">
                    <div class="sub-total"><?php echo __('Subtotal', 'traveler') ?> <span
                                class="price"><?php echo TravelHelper::format_money($cart_total_amount); ?></span>
                    </div>
                    <a href="<?php echo esc_url($check_out_url) ?>"
                       class="btn btn-full upper"><?php _e('Pay Now', 'traveler') ?></a>
                </li>
            <?php
            else:
                ?>
                <li><div class="col-lg-12 cart-text-empty text-warning"><?php echo __('Your cart is empty', 'traveler'); ?></div></li>
            <?php
            endif;
            ?>
        </ul>
    </li>
    <?php
}
?>
