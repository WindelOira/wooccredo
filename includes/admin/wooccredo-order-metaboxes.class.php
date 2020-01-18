<?php
defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Order_Metaboxes') ) :
    class Wooccredo_Order_Metaboxes {
        /**
         * Init
         */
        public static function init() {
            add_action('add_meta_boxes', __CLASS__ .'::addMetaboxes');
        }

        /**
         * Add meta boxes
         */
        public static function addMetaboxes() {
            add_meta_box('wooccredo_order_fields', __('Wooccredo', WOOCCREDO_TEXT_DOMAIN), __CLASS__ .'::orderFields', 'shop_order', 'side', 'high');
        }

        /**
         * Order fields.
         */
        public static function orderFields() {
            global $post;

            $customers = Wooccredo_Customers::getCustomers();
            $selectedCustomer = get_post_meta($post->ID, 'wooccredo_customer', TRUE);

            $salesPersons = Wooccredo_Sales_Persons::getSalesPersons();
            $selectedSalesPerson = get_post_meta($post->ID, 'wooccredo_sales_person', TRUE);

            $salesAreas = Wooccredo_Sales_Areas::getSalesAreas();
            $selectedSalesArea = get_post_meta($post->ID, 'wooccredo_sales_area', TRUE);

            $locations = Wooccredo_Locations::getLocations();
            $selectedLocation = get_post_meta($post->ID, 'wooccredo_location', TRUE) ? get_post_meta($post->ID, 'wooccredo_location', TRUE) : Wooccredo_Locations::getDefaultLocation();

            $branches = Wooccredo_Branches::getBranches();
            $selectedBranch = get_post_meta($post->ID, 'wooccredo_branch', TRUE) ? get_post_meta($post->ID, 'wooccredo_branch', TRUE) : Wooccredo_Branches::getDefaultBranch();

            $departments = Wooccredo_Departments::getDepartments();
            $selectedDepartment = get_post_meta($post->ID, 'wooccredo_department', TRUE) ? get_post_meta($post->ID, 'wooccredo_department', TRUE) : Wooccredo_Departments::getDefaultDepartment();
        ?>
            <ul class="order_actions">
                <li class="wide">
                    <input type="hidden" name="wooccredo_order_nonce" value="<?php echo wp_create_nonce(); ?>">
                    <label for="wooccredo_customer"><?php _e('Customer', WOOCCREDO_TEXT_DOMAIN); ?></label>
                    <select id="wooccredo_customer" name="wooccredo_customer">
                        <option value="">- Add Customer to Accredo -</option>
                        <?php 
                        if( is_array($customers) ) :
                            foreach( $customers as $customer ) : 
                                if( !get_term_meta($customer->term_id, 'customer_code', TRUE) ) continue;
                        ?>
                        <option value="<?php echo get_term_meta($customer->term_id, 'customer_code', TRUE); ?>" <?php selected($selectedCustomer, get_term_meta($customer->term_id, 'customer_code', TRUE), TRUE); ?>><?php echo $customer->name; ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </li>
                <li class="wide">
                    <label for="wooccredo_sales_person"><?php _e('Sales Person', WOOCCREDO_TEXT_DOMAIN); ?></label>
                    <select id="wooccredo_sales_person" name="wooccredo_sales_person">
                        <option value="">- Select Sales Person -</option>
                        <?php 
                        if( is_array($salesPersons)) :
                            foreach( $salesPersons as $salesPerson ) : 
                                if( !get_term_meta($salesPerson->term_id, 'sales_person_code', TRUE) ) continue;
                        ?>
                        <option value="<?php echo get_term_meta($customer->term_id, 'sales_person_code', TRUE); ?>" <?php selected($selectedSalesPerson, get_term_meta($customer->term_id, 'sales_person_code', TRUE), TRUE); ?>><?php echo $salesPerson->name; ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </li>
                <li class="wide">
                    <label for="wooccredo_sales_area"><?php _e('Sales Area', WOOCCREDO_TEXT_DOMAIN); ?></label>
                    <select id="wooccredo_sales_area" name="wooccredo_sales_area">
                        <option value="">- Select Sales Area -</option>
                        <?php 
                        if( is_array($salesAreas) ) :
                            foreach( $salesAreas as $salesArea ) : 
                                if( !get_term_meta($salesArea->term_id, 'sales_area_code', TRUE) ) continue;
                        ?>
                        <option value="<?php echo get_term_meta($salesArea->term_id, 'sales_area_code', TRUE); ?>" <?php selected($selectedSalesArea, get_term_meta($salesArea->term_id, 'sales_area_code', TRUE), TRUE); ?>><?php echo $salesArea->name; ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </li>
                <li class="wide">
                    <label for="wooccredo_location"><?php _e('Location', WOOCCREDO_TEXT_DOMAIN); ?></label>
                    <select id="wooccredo_location" name="wooccredo_location">
                        <option value="">- Select Location -</option>
                        <?php 
                        if( is_array($locations) ) :
                            foreach( $locations as $location ) : 
                                if( !get_term_meta($location->term_id, 'location_code', TRUE) ) continue;
                        ?>
                        <option value="<?php echo get_term_meta($location->term_id, 'location_code', TRUE); ?>" <?php selected($selectedLocation, get_term_meta($location->term_id, 'location_code', TRUE), TRUE); ?>><?php echo $location->name; ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </li>
                <li class="wide">
                    <label for="wooccredo_branch"><?php _e('Branch', WOOCCREDO_TEXT_DOMAIN); ?></label>
                    <select id="wooccredo_branch" name="wooccredo_branch">
                        <option value="">- Select Branch -</option>
                        <?php 
                        if( is_array($branches) ) :
                            foreach( $branches as $branch ) : 
                                if( !get_term_meta($branch->term_id, 'branch_code', TRUE) ) continue;
                        ?>
                        <option value="<?php echo get_term_meta($branch->term_id, 'branch_code', TRUE); ?>" <?php selected($selectedBranch, get_term_meta($branch->term_id, 'branch_code', TRUE), TRUE); ?>><?php echo $branch->name; ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </li>
                <li class="wide">
                    <label for="wooccredo_department"><?php _e('Department', WOOCCREDO_TEXT_DOMAIN); ?></label>
                    <select id="wooccredo_department" name="wooccredo_department">
                        <option value="">- Select Department -</option>
                        <?php 
                        if( is_array($departments) ) :
                            foreach( $departments as $department ) : 
                                if( !get_term_meta($department->term_id, 'department_code', TRUE) ) continue;
                        ?>
                        <option value="<?php echo get_term_meta($department->term_id, 'department_code', TRUE); ?>" <?php selected($selectedDepartment, get_term_meta($department->term_id, 'department_code', TRUE), TRUE); ?>><?php echo $department->name; ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </li>
            </ul>
        <?php
        }
    }

    Wooccredo_Order_Metaboxes::init();
endif;