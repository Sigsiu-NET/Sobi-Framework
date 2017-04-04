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
 *
 *
 *
 */

namespace Sobi\Input;

use Sobi\Error\Exception;

defined( 'SOBI' ) || exit( 'Restricted access' );

/**
 * @method      integer  Sid()       public static Sid( $request = 'request', $default = 0 )
 * @method      integer  Cid()       public static Cid( $request = 'request', $default = 0 )
 * @method      integer  Pid()       public static Cid( $request = 'request', $default = 0 )
 * @method      integer  Rid()       public static Rid( $request = 'request', $default = 0 )
 * @method      integer  Eid()       public static Eid( $request = 'request', $default = 0 )
 * @method      integer  Fid()       public static Fid( $request = 'request', $default = 0 )
 */
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
	public static function Int( $name, $request = 'request', $default = 0 )
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
	public static function Arr( $name, $request = 'request', array $default = [] )
	{
		/** No need for cleaning - Joomla! is doing it already */
		$arr = Request::Instance()->{$request}->get( $name, $default, 'array' );
		/** if we use the 'array' filter Joomla! will automatically convert it into an array
		 *  so we need to check the original request for its state */
		return isset( $_REQUEST[ $name ] ) && is_array( $_REQUEST[ $name ] ) ? $arr : $default;
	}


	/**
	 * Search for indexes within the requested method
	 *
	 * @param string $search variable name
	 * @param string $request request method
	 * @return mixed
	 */
	static public function Search( $search, $request = 'request' )
	{
//		$r = Request::Instance()->{$request};
//		if ( count( self::$request ) ) {
//			foreach ( self::$request as $name => $value ) {
//				if ( strstr( $name, $search ) ) {
//					self::$val[ $name ] = $value;
//				}
//			}
//		}
//		return self::$val;
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
	public static function Base64( $name, $request = 'request', $default = null )
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
	public static function Bool( $name, $request = 'request', $default = false )
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
	public static function Cmd( $name, $request = 'request', $default = null )
	{
		return preg_replace( '/[^a-zA-Z0-9\p{L}\.\-\_\:]/u', null, Request::Instance()->{$request}->getString( $name, $default ) );
	}

	/**
	 * @param string $name
	 * @param float $default
	 * @param string $request
	 * @return float
	 * @since version
	 */
	public static function Double( $name, $request = 'request', $default = 0.0 )
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
	public static function Float( $name, $request = 'request', $default = 0.0 )
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
	public static function Html( $name, $request = 'request', $default = null )
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
	public static function String( $name, $request = 'request', $default = null )
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
	public static function Ip4( $name = 'REMOTE_ADDR', $request = 'server', $default = null )
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
	public static function Raw( $name, $request = 'request', $default = null )
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
		return self::Cmd( 'task', $request );
	}

	/**
	 * @param string $name
	 * @param array $arguments
	 * @return int
	 * @throws Exception
	 * @since version
	 */
	public static function __callStatic( $name, $arguments = [] )
	{
		if ( strstr( $name, 'id' ) ) {
			if ( !( count( $arguments ) ) ) {
				$arguments = [ 0 => 'request', 1 => 0 ];
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
	static public function Timestamp( $name, $method = 'request', $default = 0.0 )
	{
		$val = self::Double( $name, $method, $default );
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
	public static function Word( $name, $request = 'request', $default = null )
	{
		return preg_replace( '[^a-zA-Z0-9\p{L}\_\-\s]/u', null, Request::Instance()->{$request}->getString( $name, $default ) );
	}

	/**
	 * @param $name
	 * @param $value
	 * @param string $request
	 *
	 *
	 * @since version
	 */
	public static function Set( $name, $value, $request = 'request' )
	{
		Request::Instance()->{$request}->set( $name, $value );
	}
}
