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
 * @created Sat, Dec 3, 2016 15:45:55
 */

namespace Sobi\FileSystem;

use ArrayObject;
use Sobi\FileSystem\File;
use Sobi\FileSystem\FileSystem;

defined( 'SOBI' ) || exit( 'Restricted access' );

class DirectoryIterator extends \ArrayObject
{
	/**
	 * @var string
	 */
	private $_dir = null;
	/** @noinspection PhpInconsistentReturnPointsInspection */

	/**
	 * @param string $dir - path
	 * @return DirectoryIterator
	 */
	public function __construct( $dir )
	{
		$Dir = scandir( $dir );
		$this->_dir = new ArrayObject();
		foreach ( $Dir as $file ) {
			$this->append( new File( FileSystem::Clean( $dir . '/' . $file ) ) );
		}
		$this->uasort( function ( $from, $to ) {
			/** Wed, Aug 24, 2016 09:24:25 - we need to put directories before files */
			if ( ( $from->isDir() && $to->isFile() ) || ( $from->isFile() && $to->isDir() ) ) {
				return ( $from->isDir() && $to->isFile() ) ? -1 : 1;
			}
			if ( ( $from->isDir() && $to->isDir() ) || ( $from->isFile() && $to->isFile() ) ) {
				return strcmp( $from->getFileName(), $to->getFileName() );
			}
			else {
				return ( $from->isDir() && !( $from->isDot() ) ) ? -1 : 1;
			}
		} );
	}

	/**
	 * @param string $string - part or full name of the file to search for
	 * @param bool $exact - search for exact string or the file nam can contain this string
	 * @return array
	 */
	public function searchFile( $string, $exact = true )
	{
		$results = [];
		foreach ( $this as $item ) {
			if ( $item->isDot() ) {
				continue;
			}
			if ( $exact ) {
				if ( $item->getFileName() == $string ) {
					$results[ $item->getPathname() ] = $item;
				}
			}
			else {
				if ( strstr( $item->getFileName(), $string ) ) {
					$results[ $item->getPathname() ] = $item;
				}
			}
		}
		return $results;
	}
}
