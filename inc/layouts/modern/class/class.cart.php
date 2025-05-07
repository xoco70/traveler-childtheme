<?php
/**
 * @package    WordPress
 * @subpackage Traveler
 * @since      1.0
 *
 * Class STCart
 *
 * Created by ShineTheme
 *
 */

if (!class_exists('STCart')) {
    class STCart extends STCart {
        static function _send_custommer_booking_email($order, $made_by_admin = false, $made_by_partner = false) {
            global $order_id;
            $order_id = $order;
            $item_post_type = get_post_meta($order_id, 'st_booking_post_type', true);

            $to = get_post_meta($order_id, 'st_email', true);
            $subject = st()->get_option('email_subject', __('Booking Confirm - ' . get_bloginfo('title'), 'traveler'));
            $subject = sprintf(__('Your booking at %s', 'traveler'), get_bloginfo('title'));

            $item_id = get_post_meta($order_id, 'item_id', true);
            $check_in = get_post_meta($order_id, 'check_in', true);
            $check_out = get_post_meta($order_id, 'check_out', true);

            $date_check_in = @date(TravelHelper::getDateFormat(), strtotime($check_in));
            $date_check_out = @date(TravelHelper::getDateFormat(), strtotime($check_out));

            if ($item_id) {
                $message = "";
                $id_page_email_for_customer = st()->get_option('email_for_customer', '');
                $email_to_custommer = !empty(get_post($id_page_email_for_customer)) ? wp_kses_post(get_post($id_page_email_for_customer)->post_content) : "";
                $message .= TravelHelper::_get_template_email($message, $email_to_custommer);

                $title = '';
                if ($title = get_the_title($item_id)) {
                    $subject = sprintf(__('Your booking at %s: %s - %s', 'traveler'), $title, $date_check_in, $date_check_out);
                }

                if (!empty($item_post_type) and $item_post_type == 'st_tours') {
                    $type_tour = get_post_meta($order_id, 'type_tour', true);
                    if ($type_tour == 'daily_tour') {
                        $duration = get_post_meta($order_id, 'duration', true);
                        $subject = sprintf(__('Your booking at %s: %s - %s', 'traveler'), $title, $date_check_in, $duration);
                    }
                }
                $check = self::_send_mail($to, $subject, $message);
            }

            return $check;
        }
    }
} 