<?php
$enable_tree = st()->get_option( 'bc_show_location_tree', 'off' );
$location_id = STInput::get( 'location_id', '' );
$location_name = STInput::get( 'location_name', '' );
if(empty($location_name)){
    if(!empty($location_id)){
        $location_name = get_the_title($location_id);
    }
}
if ( $enable_tree == 'on' ) {
    $lists     = TravelHelper::getListFullNameLocation( 'st_activity');
    $locations = TravelHelper::buildTreeHasSort( $lists );
} else {
    $locations = TravelHelper::getListFullNameLocation( 'st_activity');
}

$has_icon = ( isset( $has_icon ) ) ? $has_icon : false;
if(is_singular('location')){
    $location_id = get_the_ID();
}
?>
<div class="destination-search st-search-destination-tour border-right">
    <!-- Dropdown Toggle Button -->
    <div id="dropdown-destination-activity" class="form-group d-flex align-items-center form-extra-field dropdown field-detination dropdown-toggle <?php if ( $has_icon ) echo 'has-icon'; ?>" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-haspopup="true" aria-controls="dropdown-menu-activity" role="button" tabindex="0">
        <?php
        if ( $has_icon ) {
            echo '<span class="stt-icon stt-icon-location1"></span>';
        }
        ?>
        <div class="st-form-dropdown-icon">
            <label for="location_name_activity"><?php echo esc_html__( 'Location', 'traveler' ); ?></label>
            <div class="render">
                <?php
                if(empty($location_name)) {
                    $placeholder = esc_html__('Where are you going?', 'traveler');
                } else {
                    $placeholder = esc_html($location_name);
                }
                ?>
                <input type="text" autocomplete="off" onkeyup="stKeyupsmartSearch(this)" id="location_name_activity" name="location_name" value="<?php echo esc_attr($location_name); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" data-post-type="st_activity" data-text-no="<?php echo esc_html__('No locations...', 'traveler'); ?>" aria-autocomplete="list" aria-controls="dropdown-menu-activity" />
            </div>
            <input type="hidden" name="location_id" value="<?php echo esc_attr($location_id); ?>" />
        </div>
    </div>

    <!-- Dropdown Menu -->
    <div id="dropdown-menu-activity" class="dropdown-menu" aria-labelledby="dropdown-destination-activity" role="menu">
        <ul class="st-scrollbar">
            <?php
            if ( $enable_tree == 'on' ) {
                New_Layout_Helper::buildTreeOptionLocation( $locations, $location_id, '<span class="stt-icon stt-icon-location1"></span>', true);
            } else {
                if ( is_array( $locations ) && count( $locations ) ):
                    foreach ( $locations as $key => $value ):
                        ?>
                        <li class="item dropdown-item" role="menuitem" data-value="<?php echo esc_attr($value->ID); ?>">
                            <span class="stt-icon stt-icon-location1"></span>
                            <span><?php echo esc_attr($value->fullname); ?></span>
                        </li>
                    <?php
                    endforeach;
                endif;
            }
            ?>
        </ul>
    </div>
</div>
