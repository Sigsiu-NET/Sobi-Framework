<?php
/**
 * @package: Sobi Framework
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2016 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * @created Thu, Dec 1, 2016 12:03:14
 */

namespace Sobi\Input;

use Sobi\Error\Exception;

defined( 'SOBI' ) || exit( 'Restricted access' );

abstract class Input
{
	/**
	 * @param string $name
	 * @param int|null $default
	 * @param string $request
	 *
	 * @return int
	 *
	 * @since version
	 */
	public static function Int( string $name, int $default = 0, string $request = 'request' )
	{
		return (int)Request::Instance()->{$request}->getInt( $name, $default );
	}

	/**
	 * @param string $name
	 * @param array $default
	 * @param string $request
	 *
	 * @return array
	 *
	 * @since version
	 */
	public static function Arr( string $name, array $default = [], string $request = 'request' )
	{
		/** No need for cleaning - Joomla! is doing it already */
		return (array)Request::Instance()->{$request}->getArray( $name, $default );
	}

	/**
	 * @param string $name
	 * @param string|null $default
	 * @param string $request
	 *
	 * @return string
	 *
	 * @since version
	 */
	public static function Base64( string $name, string $default = null, string $request = 'request' )
	{
		return preg_replace( '/[^A-Za-z0-9\/+=]/', null, Request::Instance()->{$request}->getString( $name, $default ) );
	}

	/**
	 * @param string $name
	 * @param bool $default
	 * @param string $request
	 * @return bool
	 * @since version
	 */
	public static function Bool( string $name, $default = false, string $request = 'request' )
	{
		return (bool)Request::Instance()->{$request}->getBool( $name, $default );
	}

	/**
	 * @param string $name
	 * @param string|null $default
	 * @param string $request
	 *
	 * @return string
	 *
	 * @since version
	 */
	public static function Cmd( string $name, string $default = null, string $request = 'request' )
	{
		return preg_replace( '/[^A-Za-z0-9\/+=\.]/', null, Request::Instance()->{$request}->getString( $name, $default ) );
	}

	/**
	 * @param string $name
	 * @param float $default
	 * @param string $request
	 * @return float
	 * @since version
	 */
	public static function Double( string $name, float $default = 0.0, string $request = 'request' )
	{
		return (float)Request::Instance()->{$request}->getFloat( $name, $default );
	}

	/**
	 * @param string $name
	 * @param float $default
	 * @param string $request
	 *
	 * @return float
	 *
	 * @since version
	 */
	public static function Float( string $name, float $default = 0.0, string $request = 'request' )
	{
		return (float)Request::Instance()->{$request}->getFloat( $name, $default );
	}

	/**
	 * @param string $name
	 * @param string|null $default
	 * @param string $request
	 *
	 * @return string
	 *
	 * @since version
	 */
	public static function Html( string $name, string $default = null, string $request = 'request' )
	{
		return filter_var( Request::Instance()->{$request}->getHtml( $name, $default ), FILTER_SANITIZE_MAGIC_QUOTES );
	}

	/**
	 * @param string $name
	 * @param string|null $default
	 * @param string $request
	 *
	 * @return string
	 *
	 * @since version
	 */
	public static function String( string $name, string $default = null, string $request = 'request' )
	{
		$value = Request::Instance()->{$request}->getString( $name, $default );
		return filter_var( $value, FILTER_SANITIZE_MAGIC_QUOTES );
	}

	/**
	 * @param string $name
	 * @param string|null $default
	 * @param string $request
	 *
	 * @return string
	 *
	 * @since version
	 */
	public static function Ip( string $name, string $default = null, string $request = 'request' )
	{
		return preg_replace( '/[^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}]/', null, Request::Instance()->{$request}->getString( $name, $default ) );
	}

	/**
	 *
	 * @return false|string
	 *
	 * @since version
	 */
	public static function Now()
	{
		return gmdate( 'Y-m-d H:i:s' );
	}

	/**
	 * @param string $name
	 * @param mixed|null $default
	 * @param string $request
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	public static function Raw( string $name, mixed $default = null, string $request = 'request' )
	{
		return Request::Instance()->{$request}->get( $name, $default );
	}

	/**
	 * @param string $request
	 *
	 * @return string
	 *
	 * @since version
	 */
	public static function Task( $request = 'request' )
	{
		return self::Cmd( 'task', null, $request );
	}

	/**
	 * @param string $name
	 * @param array $arguments
	 * @return int
	 * @throws Exception
	 * @since version
	 */
	public static function __callStatic( string $name, $arguments = [] )
	{
		if ( strstr( $name, 'id' ) ) {
			if ( !( count( $arguments ) ) ) {
				$arguments = [ 0 => 0, 1 => 'request' ];
			}
			return self::Int( strtolower( $name ), $arguments[ 0 ], $arguments[ 1 ] );
		}
		else {
			throw new Exception( "Call to undefined method {$name} of class " . __CLASS__ );
		}
		return false;
	}

	/**
	 * Returns double value of requested variable and checks for a valid timestamp
	 * Sun, Jan 5, 2014 11:27:04 changed to double because of 32 bit systems (seriously?!)
	 * @param string $name variable name
	 * @param float|int $default default value
	 * @param string $method request method
	 * @return int
	 */
	static public function Timestamp( string $name, float $default = 0.0, string $method = 'request' )
	{
		$val = self::Double( $name, $default, $method );
		// JavaScript conversion
		return $val > 10000000000 ? $val / 1000 : $val;
	}


	/**
	 * @param string $name
	 * @param string|null $default
	 * @param string $request
	 *
	 * @return string
	 *
	 * @since version
	 */
	public static function Word( string $name, string $default = null, string $request = 'request' )
	{
		return preg_replace( '[^a-zA-Z0-9\p{L}\_\-\s]/u', null, Request::Instance()->{$request}->getString( $name, $default ) );
	}
}
