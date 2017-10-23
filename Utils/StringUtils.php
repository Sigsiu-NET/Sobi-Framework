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

use Sobi\Framework;

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

	/**
	 * @param string $txt
	 * @param bool $unicode
	 * @param bool $forceUnicode
	 * @return string
	 */
	public static function Nid( $txt, $unicode = false, $forceUnicode = false )
	{
		$txt = trim( str_replace( [ '.', '_' ], '-', $txt ) );
		return ( Framework::Cfg( 'sef.unicode' ) && $unicode ) || $forceUnicode ? self::urlSafe( $txt ) : trim( preg_replace( '/(\s|[^A-Za-z0-9\-])+/', '-', \JFactory::getLanguage()->transliterate( $txt ) ), '_-\[\]\(\)' );
	}

	/**
	 * Creates URL safe string
	 * @param string $str
	 * @return string
	 */
	public static function UrlSafe( $str )
	{
		// copy of Joomla! stringURLUnicodeSlug
		// we don't want to have it lowercased
		// @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
		// Replace double byte whitespaces by single byte (East Asian languages)
		$str = preg_replace( '/\xE3\x80\x80/', ' ', $str );
		// Remove any '-' from the string as they will be used as concatenator.
		// Would be great to let the spaces in but only Firefox is friendly with this
		$str = str_replace( '-', ' ', $str );
		// Replace forbidden characters by whitespaces
		$str = preg_replace( '/[:\#\*"@+=;!><&\.%()\]\/\'\\\\|\[]/', "\x20", $str );
		// Delete all '?'
		$str = str_replace( '?', null, $str );
		// Remove any duplicate whitespace and replace whitespaces by hyphens
		$str = preg_replace( '/\x20+/', '-', $str );
		$str = preg_replace( [ '/\s+/', '/[^A-Za-z0-9\p{L}\-\_]/iu' ], [ '-', null ], $str );
		$str = trim( $str, '_-\[\]\(\)' );
		return $str;
	}

	/**
	 * Replaces HTML entities to valid XML entities
	 * @param $txt
	 * @param $amp
	 * @return string
	 */
	public static function Entities( $txt, $amp = false )
	{
		$txt = str_replace( '&', '&#38;', $txt );
		if ( $amp ) {
			return $txt;
		}
		//		$txt = htmlentities( $txt, ENT_QUOTES, 'UTF-8' );
		$entities = [ 'auml' => '&#228;', 'ouml' => '&#246;', 'uuml' => '&#252;', 'szlig' => '&#223;', 'Auml' => '&#196;', 'Ouml' => '&#214;', 'Uuml' => '&#220;', 'nbsp' => '&#160;', 'Agrave' => '&#192;', 'Egrave' => '&#200;', 'Eacute' => '&#201;', 'Ecirc' => '&#202;', 'egrave' => '&#232;', 'eacute' => '&#233;', 'ecirc' => '&#234;', 'agrave' => '&#224;', 'iuml' => '&#239;', 'ugrave' => '&#249;', 'ucirc' => '&#251;', 'ccedil' => '&#231;', 'AElig' => '&#198;', 'aelig' => '&#330;', 'OElig' => '&#338;', 'oelig' => '&#339;', 'angst' => '&#8491;', 'cent' => '&#162;', 'copy' => '&#169;', 'Dagger' => '&#8225;', 'dagger' => '&#8224;', 'deg' => '&#176;', 'emsp' => '&#8195;', 'ensp' => '&#8194;', 'ETH' => '&#208;', 'eth' => '&#240;', 'euro' => '&#8364;', 'half' => '&#189;', 'laquo' => '&#171;', 'ldquo' => '&#8220;', 'lsquo' => '&#8216;', 'mdash' => '&#8212;', 'micro' => '&#181;', 'middot' => '&#183;', 'ndash' => '&#8211;', 'not' => '&#172;', 'numsp' => '&#8199;', 'para' => '&#182;', 'permil' => '&#8240;', 'puncsp' => '&#8200;', 'raquo' => '&#187;', 'rdquo' => '&#8221;', 'rsquo' => '&#8217;', 'reg' => '&#174;', 'sect' => '&#167;', 'THORN' => '&#222;', 'thorn' => '&#254;', 'trade' => '&#8482;' ];
		foreach ( $entities as $ent => $repl ) {
			$txt = preg_replace( '/&' . $ent . ';?/m', $repl, $txt );
		}
		return $txt;
	}
}
