<?php
// phpcs:ignoreFile

namespace AutomateWoo;

defined( 'ABSPATH' ) or exit;

/**
 * Class DateTime
 * @since 4.3.0
 */
class DateTime extends \DateTime {

	/**
	 * Same as parent but forces UTC timezone if no timezone is supplied instead of using the PHP default.
	 *
	 * @param string               $time
	 * @param \DateTimeZone|string $timezone
	 */
	public function __construct( $time = 'now', $timezone = null ) {
		if ( ! $timezone ) {
			$timezone = new \DateTimeZone( 'UTC' );
		}

		parent::__construct( $time, $timezone instanceof \DateTimeZone ? $timezone : null );
	}


	/**
	 * Convert DateTime from site timezone to UTC.
	 *
	 * Note this doesn't actually set the timezone property, it directly modifies the date.
	 *
	 * @return $this
	 */
	public function convert_to_utc_time() {
		Time_Helper::convert_to_gmt( $this );
		return $this;
	}


	/**
	 * Convert DateTime from UTC to the site timezone.
	 *
	 * Note this doesn't actually set the timezone property, it directly modifies the date.
	 *
	 * @return $this
	 */
	public function convert_to_site_time() {
		Time_Helper::convert_from_gmt( $this );
		return $this;
	}


	/**
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public function to_mysql_string() {
		return $this->format( 'Y-m-d H:i:s' );
	}


}
