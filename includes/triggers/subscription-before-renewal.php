<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Subscription_Before_Renewal
 * @since 2.6.2
 */
class Trigger_Subscription_Before_Renewal extends Trigger_Abstract_Subscriptions implements Interfaces\Background_Processed_Trigger {

	const SUPPORTS_CUSTOM_TIME_OF_DAY = true;


	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __( 'Subscription Before Renewal', 'automatewoo' );
		$this->description = __( 'This trigger checks for upcoming subscription renewals once every 24 hours.', 'automatewoo' );
	}


	function load_fields() {

		$days_before_renewal = ( new Fields\Number() )
			->set_name( 'days_before_renewal' )
			->set_title( __( 'Days before renewal', 'automatewoo' ) )
			->set_required();

		$this->add_field($days_before_renewal);
		$this->add_field( $this->get_field_time_of_day() );
		$this->add_field_subscription_products();
	}


	function register_hooks() {
		add_action( 'automatewoo/custom_time_of_day_workflow', [ 'AutomateWoo\Workflow_Background_Process_Helper', 'init_process' ] );
	}


	/**
	 * @param Workflow $workflow
	 * @return array
	 */
	function get_background_tasks( $workflow ) {
		$tasks = [];
		$days_before_renewal = absint( $workflow->get_trigger_option( 'days_before_renewal' ) );

		// days before must be set
		if ( ! $days_before_renewal ) {
			return [];
		}

		$date = new DateTime();
		$date->convert_to_site_time();
		$date->modify( "+$days_before_renewal days" );

		foreach ( $this->get_subscriptions_by_next_payment_day( $date ) as $subscription_id ) {
			$tasks[] = [
				'workflow_id' => $workflow->get_id(),
				'workflow_data' => [
					'subscription_id' => $subscription_id
				]
			];
		}

		return $tasks;
	}


	/**
	 * @param Workflow $workflow
	 * @param array $data
	 */
	function handle_background_task( $workflow, $data ) {
		$subscription = isset( $data['subscription_id'] ) ? wcs_get_subscription( Clean::id( $data['subscription_id'] ) ) : false;

		if ( ! $subscription ) {
			return;
		}

		$workflow->maybe_run([
			'subscription' => $subscription,
			'customer' => Customer_Factory::get_by_user_id( $subscription->get_user_id() )
		]);
	}


	/**
	 * Return an array of subscription ids that renew on a specific date
	 *
	 * @param $date DateTime Must be in site time!
	 * @return array
	 */
	function get_subscriptions_by_next_payment_day( $date ) {
		$day_start = clone $date;
		$day_end = clone $date;
		$day_start->setTime(0,0,0);
		$day_end->setTime(23,59,59);

		$day_start->convert_to_utc_time();
		$day_end->convert_to_utc_time();

		$query = new \WP_Query([
			'post_type' => 'shop_subscription',
			'post_status' => 'wc-active',
			'fields' => 'ids',
			'posts_per_page' => -1,
			'no_found_rows' => true,
			'meta_query' => [
				[
					'key' => '_schedule_next_payment',
					'compare' => '>',
					'value' => $day_start->to_mysql_string(),
				],
				[
					'key' => '_schedule_next_payment',
					'compare' => '<',
					'value' => $day_end->to_mysql_string(),
				]
			]
		]);

		return $query->posts;
	}


	/**
	 * @param $workflow Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		$subscription = $workflow->data_layer()->get_subscription();

		if ( ! $subscription ) {
			return false;
		}

		if ( ! $this->validate_subscription_products_field( $workflow ) ) {
			return false;
		}

		// ensure that the workflow has not triggered for this subscription in the last 24  hours
		// this avoids duplication that could arise from timezone/DST changes
		if ( $workflow->has_run_for_data_item( 'subscription', DAY_IN_SECONDS  ) ) {
			return false;
		}

		return true;
	}


	/**
	 * @param Workflow $workflow
	 * @return bool
	 */
	function validate_before_queued_event( $workflow ) {
		$subscription = $workflow->data_layer()->get_subscription();

		if ( ! $subscription ) {
			return false;
		}

		// only trigger for active subscriptions
		if ( ! $subscription->has_status('active') ) {
			return false;
		}

		return true;
	}

}
