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
            // Update sync time
            update_option('wc_wooccredo_sync_started', strtotime('now'));

			// Schedule the cron healthcheck.
			$this->schedule_event();

			// Perform remote post.
			return parent::dispatch();
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

            // Delete unsynced
            // Wooccredo::deleteUnsynced();

            Wooccredo::addLog('Sync done...');
        }
    }
endif;