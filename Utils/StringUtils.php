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
 * @created Thu, Dec 1, 2016 12:03:54
 */

namespace Sobi\Utils;

defined( 'SOBI' ) || exit( 'Restricted access' );

abstract class StringUtils
{
	/**
	 * Removes slashes from string
	 * @param string $txt
	 * @return string
	 */
	public static function Clean( $txt )
	{
		while ( strstr( $txt, "\'" ) || strstr( $txt, '\"' ) || strstr( $txt, '\\\\' ) ) {
			$txt = stripslashes( $txt );
		}
		return $txt;
	}

}
