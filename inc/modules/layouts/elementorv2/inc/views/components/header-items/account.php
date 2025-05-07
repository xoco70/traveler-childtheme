<?php
if (!is_user_logged_in()) {

    $popup_enable = st()->get_option('enable_popup_login', 'on');
    if ($popup_enable == 'on') {
        ?>
        <li class="dropdown dropdown-user-dashboard">
            <a href="#" class="dropdown-toggle" data-bs-toggle="modal"
               data-bs-target="#st-login-form" aria-label="Connexion">
                <span class="stt-icon stt-icon-user1"></span>
            </a>
        </li>
        <?php
    } else {
        ?>
        <li class="dropdown dropdown-user-dashboard ud2">
            <?php
            $login_page = get_the_permalink(st()->get_option("page_user_login"));
            $register_page = get_the_permalink(st()->get_option("page_user_register"));
            ?>
            <a href="#" class="dropdown-toggle" role="button" id="dropdown-dashboard"
               data-bs-toggle="dropdown" aria-expanded="false" aria-label="Connexion">
                <span class="stt-icon stt-icon-user1"></span>
            </a>
            <ul class="dropdown-menu" aria-labelledby="dropdown-dashboard">
                <li class="user-name">
                    <a href="<?php echo esc_url($login_page) ?>"><?php echo __('Sign in', 'traveler') ?></a>
                </li>
                <li class="user-name">
                    <a href="<?php echo esc_url($register_page) ?>"><?php echo __('Sign up', 'traveler') ?></a>
                </li>
            </ul>
        </li>
        <?php
    }
} else {
    $userdata = wp_get_current_user();
    $account_dashboard = st()->get_option('page_my_account_dashboard');
    $my_account = st()->get_option('page_my_account_dashboard');
    $current_user = wp_get_current_user();
    $lever = $current_user->roles;
    $lever = array_shift($lever);

    ?>
    <li class="dropdown dropdown-user-dashboard">
        <?php
        if (!empty($in_header)) {
            echo st_get_profile_avatar($userdata->ID, 40);
        }
        ?>
        <a href="#" class="dropdown-toggle" role="button" id="dropdown-dashboard"
           data-bs-toggle="dropdown" aria-expanded="false" aria-label="Connexion">
            <span class="stt-icon stt-icon-user1"></span>
        </a>
        <ul class="dropdown-menu" aria-labelledby="dropdown-dashboard">
            <li class="user-name">
                <div class="avatar">
                    <?php echo st_get_profile_avatar($userdata->ID, 40); ?>
                </div>
                <?php echo __('Hi, ', 'traveler') . TravelHelper::get_username($userdata->ID); ?>
            </li>
            <li>
                <a href="<?php echo esc_url(get_the_permalink($account_dashboard)) ?>"><?php echo __('Dashboard', 'traveler') ?></a>
            </li>
            <?php if (STUser_f::check_lever_partner($lever) and st()->get_option('partner_enable_feature') == 'on') { ?>
                <li>
                    <a href="<?php echo add_query_arg('sc', 'my-activity', get_the_permalink($account_dashboard)) ?>"><?php echo __('My Activity', 'traveler') ?></a>
                </li>
                <li>
                    <a href="<?php echo TravelHelper::get_user_dashboared_link(get_the_permalink($account_dashboard), 'booking-activity'); ?>"><?php echo __('Booking History', 'traveler') ?></a>
                </li>
            <?php } else { ?>
                <li>
                    <!--            <a href="-->
                    <?php //echo TravelHelper::get_user_dashboared_link(get_permalink(), 'booking-activity'); ?><!--">-->
                    <?php //echo __('Booking History', 'traveler') ?><!--</a>-->
                    <a href="<?php echo add_query_arg('sc', 'booking-history', get_the_permalink($account_dashboard)) ?>"><?php echo __('Booking History', 'traveler') ?></a>
                </li>
            <?php } ?>

            <li>
                <hr class="dropdown-divider">
            </li>
            <li>
                <a href="<?php echo wp_logout_url() ?>"><?php echo __('Log out', 'traveler') ?></a>
            </li>
        </ul>
    </li>
    <?php
}