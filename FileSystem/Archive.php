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

use Sobi\C;
use Sobi\Error\Exception;

class Archive extends File
{
	/**
	 * @param $to
	 * @return bool
	 * @todo use J!'s method, support for other archive types
	 */
	public function extract( $to )
	{
		$r = false;
		$ext = FileSystem::GetExt( $this->_filename );
		switch ( $ext ) {
			case 'zip':
				$zip = new \ZipArchive();
				if ( $zip->open( $this->_filename ) === true ) {
					try {
						$zip->extractTo( $to );
						$zip->close();
						$r = true;
					} catch ( Exception $x ) {
						$t = FileSystem::Clean( C::ROOT . '/tmp/' . md5( microtime() ) );
						SPFs::mkdir( $t, 0777 );
						$dir = new Directory( $t );
						if ( $zip->extractTo( $t ) ) {
							$zip->close();
							$dir->moveFiles( $to );
							$r = true;
						}
						FileSystem::Delete( $dir->getPathname() );
					}
				}
				break;
		}
		return $r;
	}
}
