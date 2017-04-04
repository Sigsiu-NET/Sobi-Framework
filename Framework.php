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
 * @created Thu, Dec 1, 2016 12:04:19
 */

namespace Sobi;

defined( '_JEXEC' ) || exit( 'Restricted access' );

use Sobi\Autoloader\Autoloader;
use Sobi\Error\Exception;

abstract class Framework
{
	/** @var array  */
	protected static $translator = [];
	/** @var array  */
	protected static $config;


	/**
	 * @param array $callback
	 */
	public static function SetTranslator( array $callback )
	{
		self::$translator = $callback;
	}

	/**
	 *
	 */
	public static function Init()
	{
		define( 'SOBI', true );
		include_once 'Autoloader/Autoloader.php';
		Autoloader::getInstance()->register();
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public static function Txt()
	{
		if ( is_array( self::$translator ) && count( self::$translator ) == 2 ) {
			$args = func_get_args();
			return call_user_func_array( self::$translator, $args );
		}
		else {
			throw new Exception( 'Translator has not been set' );
		}
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public static function Cfg()
	{
		if ( is_array( self::$config ) && count( self::$config ) == 2 ) {
			$args = func_get_args();
			return call_user_func_array( self::$config, $args );
		}
		else {
			throw new Exception( 'Config has not been set' );
		}
	}
	/**
	 * @param array $config
	 */
	public static function setConfig( array $config )
	{
		self::$config = $config;
	}
}
