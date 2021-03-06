<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) or exit;

/**
 * @class Review_Rating
 */
class Review_Rating extends Abstract_Number {

	public $data_item = 'review';

	public $support_floats = false;


	function init() {
		$this->title = __( 'Review - Rating', 'automatewoo' );
		$this->group = __( 'Review', 'automatewoo' );
	}


	/**
	 * @param $review \AutomateWoo\Review
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $review, $compare, $value ) {
		return $this->validate_number( $review->get_rating(), $compare, $value );
	}

}

return new Review_Rating();
