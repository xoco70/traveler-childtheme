<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13-11-2018
 * Time: 8:27 AM
 * Since: 1.0.0
 * Updated: 1.0.0
 */
if (!is_user_logged_in()) {
    $popup_enable = st()->get_option('enable_popup_login', 'on');
    if ($popup_enable == 'on') {
    ?>
        <li class="topbar-item login-item">
            <a href="#" class="login" data-bs-toggle="modal"
            data-bs-target="#st-login-form" aria-label="Connexion"><?php echo esc_html__('Login', 'traveler') ?></a>
        </li>
        <a href="#" class="login" data-bs-toggle="modal" data-bs-target="#st-login-form" aria-label="Connexion">
            <i class="fa fa-user"></i>
        </a>

        <li class="topbar-item signup-item">
            <a href="#" class="signup" data-bs-toggle="modal"
            data-bs-target="#st-register-form"><?php echo esc_html__('Sign Up', 'traveler') ?></a>
        </li>
    <?php
    } else {
        $login_page = get_the_permalink(st()->get_option("page_user_login"));
        $register_page = get_the_permalink(st()->get_option("page_user_register"));
        ?>
        <li class="topbar-item login-item">
            <a href="<?php echo esc_url($login_page) ?>"><?php echo __('Login', 'traveler') ?></a>
        </li>

        <li class="topbar-item signup-item">
            <a href="<?php echo esc_url($register_page) ?>"><?php echo __('Sign Up', 'traveler') ?></a>
        </li>
    <?php }
} else {
    $userdata = wp_get_current_user();
    $account_dashboard = st()->get_option('page_my_account_dashboard');
    ?>
    <li class="dropdown dropdown-user-dashboard">
        <?php
        if (!empty($in_header)) {
            echo st_get_profile_avatar($userdata->ID, 40);
        }
        ?>
        <a href="#" class="dropdown-toggle"  role="button" id="dropdown-dashboard" data-bs-toggle="dropdown" aria-expanded="false">
            <?php echo __('Hi, ', 'traveler') . TravelHelper::get_username($userdata->ID); ?>
            <i class="fa fa-angle-down"></i>
        </a>
        <ul class="dropdown-menu" aria-labelledby="dropdown-dashboard">
            <li>
                <a href="<?php echo esc_url(get_the_permalink($account_dashboard)) ?>"><?php echo __('Dashboard', 'traveler') ?></a>
            </li>
            <li>
                <a href="<?php echo add_query_arg('sc', 'booking-history', get_the_permalink($account_dashboard)) ?>"><?php echo __('Booking History', 'traveler') ?></a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a href="<?php echo wp_logout_url() ?>"><?php echo __('Log out', 'traveler') ?></a>
            </li>
        </ul>
    </li>
    <?php
}
