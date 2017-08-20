<?php
/**
 * Charitable Currency Functions.
 *
 * @package     Charitable/Functions/Currency
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Return currency helper class.
 *
 * @return  Charitable_Currency
 * @since   1.0.0
 */
function charitable_get_currency_helper() {
	return Charitable_Currency::get_instance();
}

/**
 * Return the site currency.
 *
 * @return  string
 * @since   1.0.0
 */
function charitable_get_currency() {
	return charitable_get_option( 'currency', 'AUD' );
}

/**
 * Formats the monetary amount.
 *
 * @param   string    $amount        The amount to be formatted.
 * @param   int|false $decimal_count Optional. If not set, default decimal count will be used.
 * @param   boolean   $db_format     Optional. Whether the amount is in db format (i.e. using decimals for cents, regardless of site settings).
 * @return  string
 * @since   1.1.5
 */
function charitable_format_money( $amount, $decimal_count = false, $db_format = false ) {
	return charitable_get_currency_helper()->get_monetary_amount( $amount, $decimal_count, $db_format );
}

/**
 * Sanitize an amount, converting it into a float.
 *
 * @param   string   $amount   The amount to be sanitized.
 * @param   boolean $db_format Optional. Whether the amount is in db format (i.e. using decimals for cents, regardless of site settings).
 * @return  float|WP_Error
 * @since   1.4.0
 */
function charitable_sanitize_amount( $amount, $db_format = false ) {
	return charitable_get_currency_helper()->sanitize_monetary_amount( $amount, $db_format );
}
