<?php

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Customer last order date rule.
 *
 * @Class Customer_Last_Order_Date
 */
class Customer_Last_Order_Date extends Abstract_Date {
	/**
	 * What date we're using to validate.
	 *
	 * @var string
	 */
	public $data_item = 'customer';

	/**
	 * Customer_Last_Order_Date constructor.
	 */
	public function __construct() {
		$this->has_is_past_comparision = true;

		parent::__construct();
	}

	/**
	 * Init.
	 */
	public function init() {
		$this->title = __( 'Customer - Last Paid Order Date', 'automatewoo' );
		$this->group = __( 'Customer', 'automatewoo' );
	}

	/**
	 * Validates rule.
	 *
	 * @param \AutomateWoo\Customer $customer The customer.
	 * @param string                $compare  What variables we're using to compare.
	 * @param array|null            $value    The values we have to compare. Null is only allowed when $compare is is_not_set.
	 *
	 * @return bool
	 */
	public function validate( $customer, $compare, $value = null ) {
		return $this->validate_date( $compare, $value, $customer->get_date_last_purchased() );
	}
}

return new Customer_Last_Order_Date();
