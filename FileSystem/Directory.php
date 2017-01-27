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
 * @created
 */

namespace Sobi\FileSystem;

defined( 'SOBI' ) || exit( 'Restricted access' );

use Sobi\FileSystem\DirectoryIterator;
use Sobi\FileSystem\FileSystem;

class Directory extends File
{
	/*** @var DirectoryIterator */
	private $_dirIterator = null;

	/**
	 * @param string $string - part or full name of the file to search for
	 * @param bool $exact - search for exact string or the file nam can contain this string
	 * @param $recLevel - recursion level
	 * @return array
	 */
	public function searchFile( $string, $exact = true, $recLevel = 1 )
	{
		$this->iterator();
		$results = [];
		if ( !( is_array( $string ) ) ) {
			$string = [ $string ];
		}
		foreach ( $string as $search ) {
			$this->searchRecursive( $this->_dirIterator, $search, $exact, $recLevel, $results );
		}
		return $results;
	}

	/**
	 * @return DirectoryIterator
	 */
	public function iterator()
	{
		if ( !$this->_dirIterator ) {
			$this->_dirIterator = new DirectoryIterator( $this->_filename );
		}
		return $this->_dirIterator;
	}

	/**
	 * Move files from directory to given path
	 * @param string $target - target path
	 * @param bool $force
	 * @return array
	 */
	public function moveFiles( $target, $force = false )
	{
		$this->iterator();
		$log = [];
		foreach ( $this->_dirIterator as $child ) {
			if ( !( $child->isDot() ) ) {
				if ( ( !( FileSystem::Exists( FileSystem::Clean( $target . '/' . $child->getFileName() ) ) ) ) ) {
					if ( FileSystem::Move( $child->getPathname(), FileSystem::Clean( $target . '/' . $child->getFileName() ) ) ) {
						$log[] = FileSystem::Clean( $target . '/' . $child->getFileName() );
					}
				}
				elseif ( $force && !( is_dir( $child->getPathname() ) ) ) {
					if ( FileSystem::Move( $child->getPathname(), FileSystem::Clean( $target . '/' . $child->getFileName() ) ) ) {
						$log[] = FileSystem::Clean( $target . '/' . $child->getFileName() );
					}
				}
			}
		}
		return $log;
	}

	/**
	 * Remove all files in directory
	 * @return void
	 */
	public function deleteFiles()
	{
		$this->iterator();
		foreach ( $this->_dirIterator as $child ) {
			if ( !( $child->isDot() ) ) {
				FileSystem::Delete( $child->getPathname() );
			}
		}
	}

	/**
	 * @param $dir
	 * @param $string
	 * @param $exact
	 * @param $recLevel
	 * @param $results
	 * @param $level
	 * @return void
	 */
	private function searchRecursive( DirectoryIterator $dir, $string, $exact, $recLevel, &$results, $level = 0 )
	{
		$level++;
		if ( $level > $recLevel ) {
			return;
		}
		$r = $dir->searchFile( $string, $exact );
		$results = array_merge( $results, $r );
		foreach ( $dir as $file ) {
			if ( $file->isDir() && !( $file->isDot() ) ) {
				$this->searchRecursive( new DirectoryIterator( $file->getPathname() ), $string, $exact, $recLevel, $results, $level );
			}
		}
	}
}
