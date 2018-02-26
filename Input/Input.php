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

use AthosHun\HTMLFilter\Configuration;
use AthosHun\HTMLFilter\HTMLFilter;
use Sobi\Error\Exception;
use Sobi\FileSystem\FileSystem;
use Sobi\Framework;
use Sobi\Utils\Serialiser;

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
		$var = null;
		$input = 'request';
		switch ( strtolower( $request ) ) {
			case 'post':
				$input = 'post';
				$request = $_POST;
				break;
			case 'get':
				$request = $_GET;
				$input = 'get';
				break;
			default:
				$request = $_REQUEST;
				break;
		}
		if ( count( $request ) ) {
			foreach ( $request as $name => $value ) {
				if ( strstr( $name, $search ) ) {
					switch ( gettype( $value ) ) {
						case 'boolean':
							$var = self::Bool( $name, $input );
							break;
						case 'integer':
							$var = self::Int( $name, $input );
							break;
						case 'double':
							$var = self::Double( $name, $input );
							break;
						case 'string':
							$var = self::Html( $name, $input );
							break;
						case 'array':
							$var = self::Arr( $name, $input );
							break;
					}
					break;
				}
			}
		}
		return $var;
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
		static $config = null;
		static $filter = null;
		if ( !( $config ) ) {
			$tags = Framework::Cfg( 'html.allowed_tags_array', [] );
			$attributes = Framework::Cfg( 'html.allowed_attributes_array', [] );

			$config = new Configuration();
			$filter = new HTMLFilter();

			if ( count( $tags ) ) {
				foreach ( $tags as $tag ) {
					$config->allowTag( $tag );
					if ( count( $attributes ) ) {
						foreach ( $attributes as $attribute ) {
							$config->allowAttribute( $tag, $attribute );
						}
					}
				}
			}
		}
		$html = Request::Instance()->{$request}->getHtml( $name, $default );
		return filter_var( $filter->filter( $config, $html ), FILTER_SANITIZE_MAGIC_QUOTES );
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
		$r = null;
		switch ( $request ) {
			case 'post':
				$r = filter_var( $_POST[ $name ], FILTER_SANITIZE_MAGIC_QUOTES );
				break;
			case 'get':
				$r = filter_var( $_GET[ $name ], FILTER_SANITIZE_MAGIC_QUOTES );
				break;
			default:
				$r = filter_var( $_REQUEST[ $name ], FILTER_SANITIZE_MAGIC_QUOTES );
				break;
		}
		return $r ? $r : $default;
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

	/**
	 * Transform data received via PUT/PATCH/etc to $_REQUEST
	 * So ir can be filtered using Joomla's validation methods.
	 * The method assumes that we are getting all those params as a JSON string
	 */
	public static function TransformToRequest()
	{
		$data = json_decode( file_get_contents( 'php://input' ), true );
		if ( count( $data ) ) {
			foreach ( $data as $index => $value ) {
				self::Set( $index, $value );
			}
		}
	}

	/**
	 * @param string $name variable name
	 * @param string $property
	 * @param string $request request method
	 * @return string
	 */
	static public function File( $name, $property = null, $request = 'files' )
	{
		if ( $request == 'files' ) {
			/** check for Ajax uploaded files */
			$check = self::String( $name );
			if ( $check ) {
				$secret = md5( Framework::Cfg( 'secret' ) );
				$fileName = str_replace( 'file://', null, $check );
				$path = Framework::Cfg( 'temp-directory' ). "/files/{$secret}/{$fileName}";
				if ( file_exists( "{$path}.var" ) ) {
					$cfg = FileSystem::Read( "{$path}.var" );
					$data = Serialiser::Unserialise( $cfg );
					$_FILES[ $name ] = $data;
				}
			}
		}
		$data = Request::Instance()->files->get( $name );
		return ( $property && isset( $data[ $property ] ) ) ? $data[ $property ] : $data;
	}
}
