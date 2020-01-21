<?php

defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Background_Process') ) :
    class Wooccredo_Background_Process extends WP_Background_Process {
        /**
         * Background process action.
         * 
         * @since   1.0.0
         */
        protected $action = 'wooccredo_background_processes_action';

        /**
         * Get tasks counts.
         * 
         * @since   1.0.0
         */
        public function tasks() {
            return $this->data;
        }

        /**
         * Background process task.
         * 
         * @since   1.0.0
         */
        protected function task($item) {
            $token = Wooccredo::getToken();

            // Stop the process if token is invalid
            if( !$token || 
                ( $token && isset($token['error']) ) ) :
                $this->cancel_process();
            endif;

            $task = @$item['task'];
            $data = @$item['data'];

            if( !$data )
                return;
            
            // Insert/update invoice.
            if( $task == 'add_update_invoice' ) :
                Wooccredo_Invoice::updateInvoice($data);
            // Insert/update customer.
            elseif( $task == 'add_update_customer' ) :
                $customerName = @$data['CustomerName'];
                $customerCode = @$data['CustomerCode'];

                if( !$customerCode )
                    return;

                Wooccredo_Customers::updateCustomer($customerName, $customerCode);
            // Insert/update sales person.
            elseif( $task == 'add_update_sales_person' ) :
                if( $data['Inactive'] ) 
                    return;

                $salesPersonName = @$data['SalesPersonName'];
                $salesPersonCode = @$data['SalesPersonCode'];

                if( !$salesPersonCode )
                    return;

                Wooccredo_Sales_Persons::updateSalesPerson($salesPersonName, $salesPersonCode);
            // Insert/update sales area.
            elseif( $task == 'add_update_sales_area' ) :
                if( $data['Inactive'] ) 
                    return;

                $salesAreaName = @$data['SalesAreaName'];
                $salesAreaCode = @$data['SalesAreaCode'];

                if( !$salesAreaCode )
                    return;

                Wooccredo_Sales_Areas::updateSalesArea($salesAreaName, $salesAreaCode);
            // Insert/update locatiom.
            elseif( $task == 'add_update_location' ) :
                if( $data['Inactive'] ) 
                    return;

                $locationName = @$data['LocationName'];
                $locationCode = @$data['LocationCode'];

                if( !$locationCode )
                    return;

                Wooccredo_Locations::updateLocation($locationName, $locationCode);
            // Insert/update branch.
            elseif( $task == 'add_update_branch' ) :
                if( $data['Inactive'] ) 
                    return;

                $branchName = @$data['BranchName'];
                $branchCode = @$data['BranchCode'];

                if( !$branchCode )
                    return;

                Wooccredo_Branches::updateBranch($branchName, $branchCode);
            // Insert/update department.
            elseif( $task == 'add_update_department' ) :
                if( $data['Inactive'] ) 
                    return;

                $departmentName = @$data['DepartmentName'];
                $departmentCode = @$data['DepartmentCode'];

                if( !$departmentCode )
                    return;

                Wooccredo_Departments::updateDepartment($departmentName, $departmentCode);
            // Delete invoice
            elseif( $task == 'delete_invoice' ) :
                Wooccredo_Invoice::deleteInvoice($data);
            // Delete customer
            elseif( $task == 'delete_customer' ) :
                Wooccredo_Customers::deleteCustomer($data);
            // Delete sales person
            elseif( $task == 'delete_sales_person' ) :
                Wooccredo_Sales_Persons::deleteSalesPerson($data);
            // Delete sales area
            elseif( $task == 'delete_sales_area' ) :
                Wooccredo_Sales_Areas::deleteSalesArea($data);
            // Delete location
            elseif( $task == 'delete_location' ) :
                Wooccredo_Locations::deleteLocation($data);
            // Delete customer
            elseif( $task == 'delete_branch' ) :
                Wooccredo_Branches::deleteBranch($data);
            // Delete customer
            elseif( $task == 'delete_department' ) :
                Wooccredo_Departments::deleteDepartment($data);
            endif;

            sleep(1);

            return FALSE;
        }

        /**
		 * Dispatch
		 *
		 * @access  public
		 * @return  void
         * @since   1.0.0
		 */
		public function dispatch() {
			// Schedule the cron healthcheck.
			$this->schedule_event();

			// Perform remote post.
			return parent::dispatch();
        }
        
        /**
		 * Cancel Process
		 *
		 * Stop processing queue items, clear cronjob and delete batch.
		 *
         * @since   1.0.0
		 */
		public function cancel_process() {
			if ( ! $this->is_queue_empty() ) :
				$batch = $this->get_batch();

				$this->delete( $batch->key );

                wp_clear_scheduled_hook( $this->cron_hook_identifier );
                
                // Set sync status for all to false.
                update_option('wc_wooccredo_synced', FALSE);

                // Set sync status for invoices to false.
                update_option('wc_wooccredo_invoices_synced', FALSE);
                
                // Set sync status for customers to false.
                update_option('wc_wooccredo_customers_synced', FALSE);

                // Set sync status for sales persons to false.
                update_option('wc_wooccredo_sales_persons_synced', FALSE);

                // Set sync status for sales areas to false.
                update_option('wc_wooccredo_sales_areas_synced', FALSE);

                // Set sync status for locations to false.
                update_option('wc_wooccredo_locations_synced', FALSE);

                // Set sync status for branches to false.
                update_option('wc_wooccredo_branches_synced', FALSE);

                // Set sync status for departments to false.
                update_option('wc_wooccredo_departments_synced', FALSE);

                // Update invoices sync status
                Wooccredo::updateSyncStatus('invoices', '');
                // Update customers sync status
                Wooccredo::updateSyncStatus('customers', '');
                // Update sales persons sync status
                Wooccredo::updateSyncStatus('sales_persons', '');
                // Update invoices sync status
                Wooccredo::updateSyncStatus('sales_areas', '');
                // Update invoices sync status
                Wooccredo::updateSyncStatus('locations', '');
                // Update invoices sync status
                Wooccredo::updateSyncStatus('branches', '');
                // Update invoices sync status
                Wooccredo::updateSyncStatus('departments', '');
            endif;
		}

        /**
         * Background process complete.
         * 
         * @since   1.0.0
         */
        protected function complete() {
            parent::complete();

            // Update invoices sync status
            Wooccredo::updateSyncStatus('invoices', 'done');
            // Update customers sync status
            Wooccredo::updateSyncStatus('customers', 'done');
            // Update sales persons sync status
            Wooccredo::updateSyncStatus('sales_persons', 'done');
            // Update invoices sync status
            Wooccredo::updateSyncStatus('sales_areas', 'done');
            // Update invoices sync status
            Wooccredo::updateSyncStatus('locations', 'done');
            // Update invoices sync status
            Wooccredo::updateSyncStatus('branches', 'done');
            // Update invoices sync status
            Wooccredo::updateSyncStatus('departments', 'done');

            Wooccredo::addLog('Sync done...');
        }
    }
endif;