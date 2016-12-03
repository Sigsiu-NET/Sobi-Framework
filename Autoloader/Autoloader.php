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
 * @created Thu, Dec 1, 2016 11:59:50
 */

namespace Sobi\Autoloader;

defined( 'SOBI' ) || exit( 'Restricted access' );

use Sobi\Error\Exception;

class Autoloader
{

	/**
	 * @return Autoloader
	 */
	public static function getInstance()
	{
		static $self = null;
		if ( !is_object( $self ) ) {
			$self = new self();
		}
		return $self;
	}


	/**
	 * @return $this
	 */
	public function & register()
	{
		spl_autoload_register( array( $this, 'load' ), true );
		return $this;
	}


	/**
	 * @return Autoloader
	 */
	public function & unregister()
	{
		spl_autoload_unregister( array( $this, 'load' ) );
		return $this;
	}

	/**
	 * @param $class
	 * @throws Exception
	 */
	protected function load( $class )
	{
		$path = explode( '\\', $class );
		if ( $path[ 0 ] == 'Sobi' ) {
			unset( $path[ 0 ] );
			$path = implode( '/', $path );
			if ( file_exists( dirname( __DIR__ . '../' ) . '/' . $path . '.php' ) ) {
				include_once dirname( __DIR__ . '../' ) . '/' . $path . '.php';
			}
			else {
				throw new Exception( "Can't find class {$class} definition" );
			}
		}
	}
}
