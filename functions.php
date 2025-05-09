<?php
/**
 * Created by PhpStorm.
 * User: MSI
 * Date: 21/08/2015
 * Time: 9:45 SA
 */
add_action('wp_enqueue_scripts', 'enqueue_parent_styles', 20);

function enqueue_parent_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_uri());
}

add_filter('st_partner_tour_tabs', function ($fields) {
    if (isset($fields['ical'])) {
        unset($fields['ical']); // Supprime iCal Sync
    }
    return $fields;
}, 20);


// Quand je commente  dans la fonction st_partner_activity_info dans le fichier, wp-content/themes/traveler/inc/layouts/modern/class/class.user.php ca disparait
add_filter('st_partner_activity_info', function ($fields) {
    foreach ($fields as $key => $field) {
        // Cas 1: Le champ est directement dans le tableau principal
        if (isset($field['name']) && $field['name'] === 'enable_pickup') {
            unset($fields[$key]);
            break;
        }

        // Cas 2: Le champ est imbriqué dans un groupe (ex: sous 'fields')
//        if (isset($field['type']) && $field['type'] === 'group' && !empty($field['fields'])) {
//            foreach ($field['fields'] as $sub_key => $sub_field) {
//                if (isset($sub_field['name']) && $sub_field['name'] === 'enable_pickup') {
//                    unset($fields[$key]['fields'][$sub_key]);
//                }
//            }
//        }
    }
    return $fields;
}, 20, 1);

add_filter('st_partner_activity_content', 'modify_partner_activity_content', 20, 1);

function modify_partner_activity_content($content)
{
    // Supprimer le groupe "Badges & Up Sell" du tableau
    if (isset($content['basic_info'])) {
        foreach ($content['basic_info'] as $key => $section) {
            if (isset($section['type']) && $section['type'] === 'group' && $section['label'] === __('Badges & Up Sell', 'traveler')) {
                unset($content['basic_info'][$key]);
                break;
            }
        }
    }
    return $content;
}


add_filter('st_partner_activity_price', function ($fields) {
    foreach ($fields as $key => $field) {
        // Cas 1: Le champ est directement dans le tableau principal
        if (isset($field['name']) && $field['name'] === 'discount_by_adult') {
            unset($fields[$key]);
            continue;
        }
        if (isset($field['name']) && $field['name'] === 'discount_by_child') {
            unset($fields[$key]);
            continue;
        }
        if (isset($field['name']) && $field['name'] === 'discount_by_people_type') {
            unset($fields[$key]);
            continue;
        }
        if (isset($field['name']) && $field['name'] === 'discount_type') {
            unset($fields[$key]);
            continue;
        }
        if (isset($field['name']) && $field['name'] === 'discount') {
            unset($fields[$key]);
            continue;
        }
        // A decommenter ulterieurement
        if (isset($field['name']) && $field['name'] === 'extra_price') {
            unset($fields[$key]);
            continue;
        }

        if (isset($field['name']) && $field['name'] === 'is_sale_schedule') {
            unset($fields[$key]);
            continue;
        }
        if (isset($field['name']) && $field['name'] === 'deposit_payment_status') {
            unset($fields[$key]);
            continue;
        }
        // Cas 2: Le champ est imbriqué dans un groupe (ex: sous 'fields')
//        if (isset($field['type']) && $field['type'] === 'group' && !empty($field['fields'])) {
//            foreach ($field['fields'] as $sub_key => $sub_field) {
//                if (isset($sub_field['name']) && $sub_field['name'] === 'enable_pickup') {
//                    unset($fields[$key]['fields'][$sub_key]);
//                }
//            }
//        }
    }
    return $fields;
}, 20, 1);

add_filter('st_partner_activity_location', function ($fields) {
    foreach ($fields as $key => $field) {
        if (isset($field['name']) && $field['name'] === 'properties_near_by') {
            unset($fields[$key]);
            continue;
        }
    }
    return $fields;
}, 20, 1);


// Fix pour le lazyload des iframes Google Maps
add_filter('litespeed_optm_lazy_iframe_uri_excludes', function($excludes) {
    $excludes[] = 'google.com/maps';
    $excludes[] = 'google.fr/maps';
    return $excludes;
});

// Permettre aux partenaires d'approuver leurs propres réservations
add_action('wp_ajax_st_partner_approve_booking', function() {
    check_ajax_referer('st_frontend_security', 'security');

    $post_id = STInput::post('post_id', '');
    $order_id = STInput::post('order_id', '');
    
    // Debug
    error_log('ST Partner Approve Booking - Post ID: ' . $post_id);
    error_log('ST Partner Approve Booking - Order ID: ' . $order_id);
    
    if ($order_id != '' && $post_id != '') {
        // Debug - Vérifier si la classe existe
        error_log('ST Partner Approve Booking - STUser_f class exists: ' . (class_exists('STUser_f') ? 'yes' : 'no'));
        
        // Debug - Vérifier si la méthode existe
        error_log('ST Partner Approve Booking - get_history_bookings_by_id method exists: ' . (method_exists('STUser_f', 'get_history_bookings_by_id') ? 'yes' : 'no'));
        
        $data_order = STUser_f::get_history_bookings_by_id($post_id);
        // Debug
        error_log('ST Partner Approve Booking - Data Order: ' . print_r($data_order, true));
        
        if (!empty($data_order)) {
            // Vérifier si l'utilisateur est admin ou si c'est le partenaire propriétaire de l'activité
            $item_id = $data_order->st_booking_id;
            $is_admin = current_user_can('administrator');
            $is_partner = in_array('partner', wp_get_current_user()->roles) || in_array('st_partner', wp_get_current_user()->roles);
            $is_owner = get_post_field('post_author', $item_id) == get_current_user_id();

            // Debug
            error_log('ST Partner Approve Booking - Item ID: ' . $item_id);
            error_log('ST Partner Approve Booking - Is Admin: ' . ($is_admin ? 'true' : 'false'));
            error_log('ST Partner Approve Booking - Is Partner: ' . ($is_partner ? 'true' : 'false'));
            error_log('ST Partner Approve Booking - Is Owner: ' . ($is_owner ? 'true' : 'false'));

            if (!$is_admin && !($is_partner && $is_owner)) {
                echo json_encode(array(
                    'status' => false,
                    'message' => __("You are not allowed to approve booking", 'traveler')
                ));
                die;
            }

            // Si on arrive ici, l'utilisateur est autorisé
            $status = 'pending';
            if ($data_order->type == "normal_booking") {
                $status = get_post_meta($order_id, 'status', true);
                $res = update_post_meta($order_id, 'status', 'complete');
                if (TravelHelper::checkTableDuplicate('st_tours')) {
                    global $wpdb;
                    $query = "UPDATE {$wpdb->prefix}st_order_item_meta SET status='complete' where order_item_id={$order_id}";
                    $wpdb->query($query);
                }
                STCart::send_mail_after_booking($order_id, true, true);
                $data_status = STUser_f::_get_order_statuses();
                if ($res) {
                    echo json_encode(array(
                        'status' => true,
                        'message' => $data_status['complete']
                    ));
                    die;
                }
            } else {
                $status = $data_order->status;
                $res = update_post_meta($order_id, 'status', 'complete');
                if (TravelHelper::checkTableDuplicate('st_tours')) {
                    global $wpdb;
                    $wpdb->update(
                        $wpdb->prefix . 'st_order_item_meta',
                        array('status' => 'complete'),
                        array('order_item_id' => $order_id)
                    );
                }
                
                // Update status order woocommerce
                $wc_order_id = $data_order->wc_order_id;
                if (!empty($wc_order_id)) {
                    $order = new WC_Order($wc_order_id);
                    $order->update_status('wc-completed');
                }
                
                STCart::send_mail_after_booking($order_id, true, true);
                $data_status = STUser_f::_get_order_statuses();
                if ($res) {
                    echo json_encode(array(
                        'status' => true,
                        'message' => $data_status['wc-completed']
                    ));
                    die;
                }
            }
            
            if ($status == 'incomplete') {
                $res = update_post_meta($order_id, 'status', 'complete');
                if (TravelHelper::checkTableDuplicate('st_tours')) {
                    global $wpdb;
                    $wpdb->update(
                        $wpdb->prefix . 'st_order_item_meta',
                        array('status' => 'complete'),
                        array('order_item_id' => $order_id)
                    );
                }
                STCart::send_mail_after_booking($order_id, true, true);
                $data_status = STUser_f::_get_order_statuses();
                if ($res) {
                    echo json_encode(array(
                        'status' => true,
                        'message' => $data_status['complete']
                    ));
                    die;
                }
            }
        }
    }
    
    echo json_encode(array(
        'status' => false,
        'message' => __('Not found', 'traveler')
    ));
    die;
}, 1); // Priorité 1 pour s'exécuter avant la fonction originale

// Force l'approbation automatique des réservations d'activités
add_action('st_booking_change_status', function($status, $order_id, $gateway_id) {
    // Vérifier si c'est une activité
    $item_id = get_post_meta($order_id, 'item_id', true);
    if (get_post_type($item_id) === 'st_activity') {
        // Mettre à jour le statut en "complete"
        update_post_meta($order_id, 'status', 'complete');
        
        // Mettre à jour la table st_order_item_meta
        global $wpdb;
        $table = $wpdb->prefix . 'st_order_item_meta';
        $query = "UPDATE {$table} SET status='complete' where order_item_id={$order_id}";
        $wpdb->query($query);
        
        // Envoyer l'email de confirmation
//        STCart::send_mail_after_booking($order_id, true, true);
    }
}, 10, 3);

// Ajouter le JavaScript pour gérer l'annulation
add_action('wp_footer', function() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('.suser-cancel').click(function(e) {
            e.preventDefault();
            var $this = $(this);
            var post_id = $this.data('post-id');
            var order_id = $this.data('order-id');
            var $message = $this.siblings('.suser-message');
            
            if (confirm('<?php echo esc_js(__('Are you sure you want to cancel this booking?', 'traveler')); ?>')) {
                $message.find('.spinner').show();
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'st_partner_cancel_booking',
                        security: '<?php echo wp_create_nonce("st_frontend_security"); ?>',
                        post_id: post_id,
                        order_id: order_id
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message || '<?php echo esc_js(__('An error occurred', 'traveler')); ?>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        alert('<?php echo esc_js(__('An error occurred. Please try again.', 'traveler')); ?>');
                    },
                    complete: function() {
                        $message.find('.spinner').hide();
                    }
                });
            }
        });
    });
    </script>
    <?php
});

// Fonction pour traiter l'annulation de réservation
add_action('wp_ajax_st_partner_cancel_booking', function() {
    try {
        check_ajax_referer('st_frontend_security', 'security');

        $post_id = STInput::post('post_id', '');
        $order_id = STInput::post('order_id', '');
        
        error_log('ST Partner Cancel Booking - Post ID: ' . $post_id);
        error_log('ST Partner Cancel Booking - Order ID: ' . $order_id);
        
        if ($order_id != '' && $post_id != '') {
            $data_order = STUser_f::get_history_bookings_by_id($post_id);
            error_log('ST Partner Cancel Booking - Data Order: ' . print_r($data_order, true));
            
            if (!empty($data_order)) {
                // Vérifier si l'utilisateur est admin ou si c'est le partenaire propriétaire de l'activité
                $item_id = $data_order->st_booking_id;
                $is_admin = current_user_can('administrator');
                $is_partner = in_array('partner', wp_get_current_user()->roles) || in_array('st_partner', wp_get_current_user()->roles);
                $is_owner = get_post_field('post_author', $item_id) == get_current_user_id();

//                error_log('ST Partner Cancel Booking - Item ID: ' . $item_id);
//                error_log('ST Partner Cancel Booking - Is Admin: ' . ($is_admin ? 'true' : 'false'));
//                error_log('ST Partner Cancel Booking - Is Partner: ' . ($is_partner ? 'true' : 'false'));
//                error_log('ST Partner Cancel Booking - Is Owner: ' . ($is_owner ? 'true' : 'false'));

                if (!$is_admin && !($is_partner && $is_owner)) {
                    wp_send_json_error(array(
                        'message' => __("You are not allowed to cancel this booking", 'traveler')
                    ));
                }

                // Mettre à jour le statut en "cancelled"
                $update_meta = update_post_meta($order_id, 'status', 'cancelled');
//                error_log('ST Partner Cancel Booking - Update Meta Result: ' . ($update_meta ? 'success' : 'failed'));
                
                // Mettre à jour la table st_order_item_meta
                global $wpdb;
                $table = $wpdb->prefix . 'st_order_item_meta';
                $update_table = $wpdb->update(
                    $table,
                    array('status' => 'cancelled'),
                    array('order_item_id' => $order_id)
                );
//                error_log('ST Partner Cancel Booking - Update Table Result: ' . ($update_table !== false ? 'success' : 'failed'));
                
                // Si c'est une commande WooCommerce, mettre à jour son statut
                $wc_order_id = $data_order->wc_order_id;
                if (!empty($wc_order_id)) {
                    try {
                        $order = new WC_Order($wc_order_id);
                        $order->update_status('cancelled');
//                        error_log('ST Partner Cancel Booking - WooCommerce Order Updated');
                    } catch (Exception $e) {
                        error_log('ST Partner Cancel Booking - WooCommerce Error: ' . $e->getMessage());
                    }
                }

                // Mettre à jour le statut dans la table st_booking
                $booking_table = $wpdb->prefix . 'st_booking';
                $wpdb->update(
                    $booking_table,
                    array('status' => 'cancelled'),
                    array('order_item_id' => $order_id)
                );
                
                // Envoyer l'email de notification
                try {
                    STCart::send_mail_after_booking($order_id, true, true);
//                    error_log('ST Partner Cancel Booking - Email Sent');
                } catch (Exception $e) {
                    error_log('ST Partner Cancel Booking - Email Error: ' . $e->getMessage());
                }
                
                wp_send_json_success(array(
                    'message' => __('Booking cancelled successfully', 'traveler')
                ));
            }
        }
        
        wp_send_json_error(array(
            'message' => __('Booking not found', 'traveler')
        ));
    } catch (Exception $e) {
        error_log('ST Partner Cancel Booking - Exception: ' . $e->getMessage());
        wp_send_json_error(array(
            'message' => __('An error occurred while cancelling the booking', 'traveler')
        ));
    }
});

/**
 * Récupère la liste des activités pour le widget Elementor
 * @return array Liste des activités avec ID => Titre
 */
function st_child_get_activity_list() {
    $activities = get_posts([
        'post_type' => 'st_activity',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);

    $options = [];
    foreach ($activities as $activity) {
        $options[$activity->ID] = $activity->post_title;
    }

    return $options;
}

/**
 * S'assure que les widgets du thème enfant sont chargés avant ceux du thème parent
 */
add_filter('st-list-element-widget', function($elements) {
    // Vérifier si le widget list-service existe dans le thème enfant
    $child_widget_path = get_stylesheet_directory() . '/st_templates/layouts/elementor/elements/list-service/settings.php';
    if (file_exists($child_widget_path)) {
        // Charger le widget du thème enfant
        require_once $child_widget_path;
    }
    return $elements;
}, 5);