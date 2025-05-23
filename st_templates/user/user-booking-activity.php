<?php
/**
 * @package WordPress
 * @subpackage Traveler
 * @since 1.1.0
 *
 * User hotel booking
 *
 * Created by ShineTheme
 *
 */
$format = TravelHelper::getDateFormat();

?>
<div class="st-create">
    <h2><?php _e("Activity Booking", 'traveler') ?></h2>
    <?php
    $arr_query_arg = array(
        'sc' => 'booking-activity',
        'scaction' => 'email-notification'
    );
    if (STInput::get('scaction') != 'email-notification') {
        ?>
        <a href="<?php echo add_query_arg($arr_query_arg, get_permalink()); ?>"
           class="btn btn-primary btn-sm btn-sendmail-notice-link"
           title="<?php echo __('Send email notification depart date', 'traveler'); ?>"><?php echo __('Send email notification', 'traveler'); ?></a>
    <?php } ?>
</div>
<?php
$paged = get_query_var('paged') ? intval(get_query_var('paged')) : 1;
$limit = 10;
$offset = ($paged - 1) * $limit;
if (STInput::get('scaction') != 'email-notification') {
    $data_post = STUser_f::get_history_bookings('st_activity', $offset, $limit, $data->ID);
} else {
    $data_post = STUser_f::get_history_bookings_send_mail('st_activity', $offset, $limit, $data->ID);
}
$posts = $data_post['rows'];
$total = ceil($data_post['total'] / $limit);

if (STInput::get('scaction') == 'email-notification') {
    echo st()->load_template('user/user-booking-activity', 'email', array('posts' => $posts, 'offset' => $offset));
} else {
    ?>
    <?php
    $screen = "st_activity";
    do_action('export_booking_history_button',$screen) ?>
    <table class="table table-bordered table-striped table-booking-history">
        <thead>
        <tr>
            <th class="hidden-xs"><?php echo __('#ID', 'traveler'); ?></th>
            <th><?php _e("Customer", 'traveler') ?></th>
            <th><?php _e("Name Activity", 'traveler') ?></th>
            <th class="hidden-xs"><?php _e("Check-in/Check-out", 'traveler') ?></th>
            <th><?php _e("Price", 'traveler') ?></th>
            <th class="hidden-xs" width="10%"><?php _e("Order Date", 'traveler') ?></th>
            <th class="hidden-xs"><?php _e("Status", 'traveler') ?></th>
            <th width="10%"><?php _e("Action", 'traveler') ?></th>
            <?php do_action('export_booking_item_title') ?>
        </tr>
        </thead>
        <tbody id="data_history_book booking-history-title">
        <?php if (!empty($posts)) {
            $i = 1 + $offset;
            foreach ($posts as $key => $value) {
                $post_id = $value->wc_order_id;
                $item_id = $value->st_booking_id;
                ?>
                <tr>
                    <td class="hidden-xs"><?php echo esc_html($value->wc_order_id); ?></td>
                    <td class="booking-history-type">
                        <?php
                        if ($post_id) {
                            $name = get_post_meta($post_id, 'st_first_name', true);
                            if (!empty($name)) {
                                $name .= " " . get_post_meta($post_id, 'st_last_name', true);
                            }
                            if (!$name) {
                                $name = get_post_meta($post_id, 'st_name', true);
                            }
                            if (!$name) {
                                $name = get_post_meta($post_id, 'st_email', true);
                            }
                            if (!$name) {
                                $billing_first_name = get_post_meta( $post_id, '_billing_first_name', true );
                                if ( empty( $billing_first_name ) ) {
                                    global $wpdb;
                                    $querystr           = 'SELECT first_name
												FROM  ' . $wpdb->prefix . "wc_order_addresses
												WHERE order_id = '{$post_id}'
												";
                                    $billing_first_name = $wpdb->get_row( $querystr, OBJECT )->first_name;
                                }

                                $billing_last_name  = get_post_meta( $post_id, '_billing_last_name', true );
                                if ( empty( $billing_last_name ) ) {
                                    global $wpdb;
                                    $querystr          = 'SELECT last_name
												FROM  ' . $wpdb->prefix . "wc_order_addresses
												WHERE
												order_id = '{$post_id}'
												";
                                    $billing_last_name = $wpdb->get_row( $querystr, OBJECT )->last_name;
                                }

                                $name = $billing_first_name . ' ' . $billing_last_name;
                            }
                            echo esc_html($name);
                        }
                        ?>
                    </td>
                    <td class=""> <?php
                        if ($item_id) {
                            if ($item_id) {
                                echo "<a href='" . esc_attr(get_the_permalink($item_id)) . "' target='_blank'>" . esc_html(get_the_title($item_id)) . "</a>";
                            }
                        }
                        ?>
                    </td>
                    <td class="hidden-xs">
                        <?php $date = $value->check_in;
                        if ($date) echo date('d/m/Y', strtotime($date)); ?><br>
                        <i class="fa fa-long-arrow-right"></i><br>
                        <?php $date = $value->check_out;
                        if ($date) echo date('d/m/Y', strtotime($date)); ?><br />
                        <?php echo '<small>' . ($value->starttime == '' ? '' : __('Start Time', 'traveler') . ': ' . esc_attr($value->starttime)) . '</small>'; ?>
                    </td>
                    <td> <?php
                        if ($value->type == "normal_booking") {
                            $total_price = get_post_meta($post_id, 'total_price', true);
                        } else {
                            $total_price = get_post_meta($post_id, '_order_total', true);
                            if ( empty( $total_price ) ) {
                                global $wpdb;
                                $querystr = "SELECT total_amount
											FROM  " . $wpdb->prefix . "wc_orders
											WHERE
											id = '{$post_id}'
											";
                                $total_price = $wpdb->get_row( $querystr, OBJECT )->total_amount;
                            }
                        }

                        $currency = get_post_meta( $post_id, 'currency', true );
                        echo TravelHelper::format_money_from_db($total_price, $currency);
                        ?>
                    </td>
                    <td class="hidden-xs"><?php echo date_i18n($format, strtotime($value->created)) ?></td>
                    <td class="hidden-xs">
                        <?php
                        $data_status = STUser_f::_get_order_statuses();
                        $status = 'pending';
                        if ($value->type == "normal_booking") {
                            $status = esc_html(get_post_meta($value->order_item_id, 'status', true));
                        } else {
                            $status = esc_html($value->status);
                        }
                        $data_status_all = STUser_f::_get_all_order_statuses();
                        $status_string = '';
                        if(array_key_exists($status, $data_status)){
                            $status_string = $data_status[$status];
                        }else{
                            if(array_key_exists($status, $data_status_all)){
                                $status_string = $data_status_all[$status];
                            }
                        }
                        $status_text  = '';
                        $status_color = '';
                        switch ( $status ) {
                            case "pending":
                                $status_text  = __( 'Pending', 'traveler' );
                                $status_color = '#E02020';
                                break;
                            case "complete":
                            case "wc-completed":
                                $status_text  = __( 'Completed', 'traveler' );
                                $status_color = '#10CD78';
                                break;
                            case "incomplete":
                                $status_text  = __( 'Incomplete', 'traveler' );
                                $status_color = '#FFAD19';
                                break;
                            case "cancelled":
                            case "wc-cancelled":
                                $status_text  = __( 'Cancelled', 'traveler' );
                                $status_color = '#7A7A7A';
                                break;
                            case "refunded":
                            case "wc-refunded":
                                $status_text  = __( 'Refunded', 'traveler' );
                                $status_color = '#7A7A7A';
                                break;
                            case "checkout-draft":
                            case "wc-checkout-draft":
                                $status_text  = __( 'Draft', 'traveler' );
                                $status_color = '#7A7A7A';
                                break;
                            default:
                                $status_text = $status_string;
                        }
                        echo '<span class="suser-status"><span style="color: ' . esc_attr( $status_color ) . '">' . esc_html( $status_text ) . '</span></span>';

                        // Afficher le bouton d'approbation pour les réservations en attente
                        if (
                            ($status == 'incomplete' || $status == 'pending' || $status == 'wc-processing' || $status == 'wc-on-hold')
                            && array_intersect(['administrator', 'partner', 'st_partner'], wp_get_current_user()->roles)
                        ) {
                            ?>
                            <a data-post-id="<?php echo esc_attr($value->order_item_id); ?>" data-order-id="<?php echo esc_attr($value->order_item_id); ?>" href="#" class="suser-approve"><?php echo __('Approve', 'traveler'); ?> </a>
                            <div class="suser-message"><div class="spinner"></div></div>
                        <?php } ?>

                        <!-- Afficher le bouton d'annulation pour les réservations complétées -->
                        <?php
                        $item_id = $value->st_booking_id;
                        $is_admin = current_user_can('administrator');
                        $is_partner = in_array('partner', wp_get_current_user()->roles) || in_array('st_partner', wp_get_current_user()->roles);
                        $is_owner = get_post_field('post_author', $item_id) == get_current_user_id();

                         ?>
                    </td>
                    <td class="">
                        <a data-toggle="modal" data-target="#info-booking-modal"
                           class="btn btn-xs btn-primary mt5 btn-info-booking"
                           data-service_id='<?php echo esc_html($item_id) ?>'
                           data-order_id="<?php echo esc_html($post_id) ?>" href="javascript: void(0);"><i
                                class="fa fa-info-circle"></i><?php _e('Details', 'traveler') ?></a>
                        <?php
                        if (($is_admin || ($is_partner && $is_owner)) &&
                            ($status == 'complete' || $status == 'wc-completed') &&
                            $status != 'cancelled' &&
                            $status != 'wc-cancelled') {
                            ?>
                            <a data-post-id="<?php echo esc_attr($value->order_item_id); ?>" data-order-id="<?php echo esc_attr($value->order_item_id); ?>" href="#" class="btn btn-xs btn-primary mt5 suser-cancel cancel btn-info-booking"><i
                                        class="fa fa-info-circle"></i><?php echo __('Cancel', 'traveler'); ?> </a>
                        <?php } ?>
                    </td>
                    <?php do_action( 'export_booking_item_buttons', $value->order_item_id ); ?>
                </tr>
                <?php
                $i++;
            }
        } else {
            echo '<h5>' . __('No Activity', 'traveler') . '</h5>';
        }
        ?>
        </tbody>
    </table>
<?php } ?>
<?php st_paging_nav('', null, $total) ?>
<div class="modal fade modal-cancel-booking modal-info-booking" id="info-booking-modal" tabindex="-1" role="dialog"
     aria-labelledby="cancelBookingLabel">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-label="<?php echo __('Close', 'traveler'); ?>"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="cancelBookingLabel"><?php echo __('Booking Details', 'traveler'); ?></h4>
            </div>
            <div class="modal-body">
                <div style="display: none;" class="overlay-form"><i class="fa fa-spinner text-color"></i></div>
                <div class="modal-content-inner"></div>
            </div>
        </div>
    </div>
</div>
