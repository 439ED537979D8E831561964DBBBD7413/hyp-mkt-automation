<?php
// phpcs:ignoreFile

namespace AutomateWoo;

defined( 'ABSPATH' ) or exit;

/**
 * @class Rule_Subscription_Item_Categories
 */
class Rule_Subscription_Item_Categories extends Rule_Order_Item_Categories {

	public $data_item = 'subscription';


	function init() {
		$this->title = __( 'Subscription - Item Categories', 'automatewoo' );
		$this->group = __( 'Subscription', 'automatewoo' );
	}

}

return new Rule_Subscription_Item_Categories();
