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
 * @created  Thu, Dec 1, 2016 12:02:59
 */

namespace Sobi\FileSystem;

defined( 'SOBI' ) || exit( 'Restricted access' );

use Sobi\C;
use Sobi\Framework;
use Sobi\Error\Exception;

abstract class FileSystem
{
	/**
	 **
	 * @param string $file
	 * @return bool
	 */
	public static function Exists( $file )
	{
		return file_exists( $file );
	}

	/**
	 *     *
	 * @param string $file
	 * @param bool $safe
	 * @return bool
	 */
	public static function Clean( $file, $safe = false )
	{
		$file = str_replace( C::DS, '/', $file );
		$file = preg_replace( '|([^:])(//)+([^/]*)|', '\1/\3', $file );
		$file = str_replace( '__BCKSL__', '\\', preg_replace( '|([^:])(\\\\)+([^\\\])|', "$1__BCKSL__$3", $file ) );
		$file = str_replace( '\\', '/', $file );
		if ( $safe ) {
			$file = \Jfile::makeSafe( $file );
		}
		if ( !( strstr( $file, ':' ) ) ) {
			while ( strstr( $file, '//' ) ) {
				$file = str_replace( '//', '/', $file );
			}
		}
		return $file;
	}

	/**
	 *     *
	 * @param string $file
	 * @return bool
	 */
	public static function GetExt( $file )
	{
		$ext = explode( ".", $file );
		return $ext[ count( $ext ) - 1 ];
	}

	/**
	 *     *
	 * @param string $file
	 * @return bool
	 */
	public static function GetFileName( $file )
	{
		$ext = explode( '/', $file );
		return $ext[ count( $ext ) - 1 ];
	}

	/**
	 *     *
	 * @param string $source
	 * @param string $destination
	 * @return bool
	 */
	public static function Copy( $source, $destination )
	{
		$destination = self::Clean( str_replace( '\\', '/', $destination ) );
		$path = explode( '/', str_replace( [ C::ROOT, str_replace( '\\', '/', C::ROOT ) ], null, $destination ) );
		$part = C::ROOT;
		$i = count( $path );
		/** clean the path */
		/** @noinspection PhpExpressionResultUnusedInspection */
		for ( $i; $i != 0; $i-- ) {
			if( isset( $path[ $i ] ) && !( $path[ $i ] ) ) {
				unset( $path[ $i ] );
			}
		}
		array_pop( $path );
		if ( !( is_string( $path ) ) && count( $path ) ) {
			foreach ( $path as $dir ) {
				$part .= "/{$dir}";
				if ( $dir && !( file_exists( $part ) ) ) {
					self::Mkdir( $part );
				}
			}
		}
		if ( !( is_dir( $source ) ) ) {
			return \Jfile::copy( self::Clean( $source ), self::Clean( $destination ) );
		}
		else {
			return \Jfolder::copy( self::Clean( $source ), self::Clean( $destination ) );
		}
	}

	/**
	 *     *
	 * @param string $file
	 * @throws Exception
	 * @return bool
	 */
	public static function Delete( $file )
	{
		$file = self::FixPath( $file );
		if ( is_dir( $file ) ) {
			if ( $file == C::ROOT || dirname( $file ) == C::ROOT ) {
				throw new Exception( Framework::Txt( 'Fatal error. Trying to delete not allowed path "%s"', $file ) );
			}
			return \Jfolder::delete( $file );
		}
		else {
			return \Jfile::delete( $file );
		}
	}

	/**
	 *     *
	 * @param string $source
	 * @param string $destination
	 * @return bool
	 */
	public static function Move( $source, $destination )
	{
		return \Jfile::move( $source, $destination );
	}

	/**
	 *     *
	 * @param string $file
	 * @return bool
	 */
	public static function Read( $file )
	{
		return file_get_contents( $file );
	}

	public static function FixPath( $path )
	{
		return str_replace( C::DS . C::DS, C::DS, str_replace( C::DS . C::DS, C::DS, str_replace( '\\', '/', $path ) ) );
	}

	/**
	 * @param string $file
	 * @param string $buffer
	 * @param bool $append
	 * @throws Exception
	 * @return bool
	 */
	public static function Write( $file, &$buffer, $append = false )
	{
		if ( $append ) {
			$content = self::Read( $file );
			$buffer = $content . $buffer;
		}
		$return = \Jfile::write( $file, $buffer );
		if ( $return === false ) {
			/**
			 * @todo how to translate from here */
			throw new Exception( Framework::Txt( 'CANNOT_WRITE_TO_FILE_AT', $file ) );
		}
		else {
			return $return;
		}
	}

	/**
	 * @param string $name
	 * @param string $destination
	 * @return bool
	 */
	public static function Upload( $name, $destination )
	{
		if ( !( file_exists( dirname( $destination ) ) ) ) {
			self::Mkdir( dirname( $destination ) );
		}
		/** Ajax uploader exception
		 * @todo: have to be moved to component
		 */
		if ( strstr( $name, str_replace( '\\', '/', SOBI_PATH ) ) ) {
			return self::Move( $name, $destination );
		}
		return \Jfile::upload( $name, $destination, false, true );
	}

	/**
	 * @param string $path
	 * @param string $hex
	 * @return bool
	 */
	public static function Chmod( $path, $hex )
	{
		return \Jfile::chmod( $path, $hex );
	}

	/**
	 * @param string $path
	 * @param int $mode
	 * @throws Exception
	 * @return bool
	 */
	public static function Mkdir( $path, $mode = 0755 )
	{
		$path = self::Clean( $path );
		if ( !( \JFolder::create( $path, $mode ) ) ) {
			throw new Exception( Framework::Txt( 'CANNOT_CREATE_DIR', str_replace( C::ROOT, null, $path ) ) );
		}
		else {
			return true;
		}
	}

	/**
	 *     *
	 * @param string $path
	 * @return bool
	 */
	public static function Rmdir( $path )
	{
		return \JFolder::delete( $path );
	}

	/**
	 *     *
	 * @param string $path
	 * @return bool
	 */
	public static function Readable( $path )
	{
		return \Jfile::isReadable( $path );
	}

	/**
	 *     *
	 * @param string $path
	 * @return bool
	 */
	public static function Writable( $path )
	{
		return \Jfile::isWritable( $path );
	}

	/**
	 *     *
	 * @param string $path
	 * @return bool
	 */
	public static function Owner( $path )
	{
		return fileowner( $path );
	}

	/**
	 *     *
	 * @param string $source
	 * @param string $destination
	 * @return bool
	 */
	public static function Rename( $source, $destination )
	{
		return self::Move( $source, $destination );
	}
}
