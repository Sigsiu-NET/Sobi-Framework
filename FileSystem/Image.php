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

use Grafika\Grafika;
use Sobi\Framework;
use Sobi\Error\Exception;

class Image extends File
{
	/*** @var array */
	protected $exif = [];
	/*** @var bool */
	protected $transparency = true;
	/*** @var \Grafika\EditorInterface */
	protected $editor = null;
	/*** @var \Grafika\ImageInterface */
	protected $image = null;

	public function __construct( $filename = null )
	{
		parent::__construct( $filename );
		$this->createEditor();
	}

	/**
	 * @param $transparency
	 */
	public function setTransparency( $transparency )
	{
		$this->transparency = $transparency;
		if ( method_exists( $this->image, 'fullAlphaMode' ) ) {
			$this->image->fullAlphaMode( $transparency );
		}
	}

	/**
	 * @param int $sections
	 * @param bool $array
	 * @return array|bool
	 */
	public function exif( $sections = 0, $array = true )
	{
		if ( function_exists( 'exif_read_data' ) && $this->_filename ) {
			if ( in_array( strtolower( FileSystem::GetExt( $this->_filename ) ), [ 'jpg', 'jpeg', 'tiff' ] ) ) {
				$this->exif = exif_read_data( $this->_filename, $sections, $array );
			}
			return $this->exif;
		}
		else {
			return false;
		}
	}


	/**
	 * Resample image
	 * @param $width
	 * @param $height
	 * @return $this
	 */
	public function & crop( $width, $height )
	{
		$this->editor->crop( $this->image, $width, $height );
		return $this;
	}

	/**
	 * Resample image
	 * @param $width
	 * @param $height
	 * @throws Exception
	 * @return $this
	 */
	public function & resample( $width, $height )
	{
		$this->editor->resizeExact( $this->image, $width, $height );
		return $this;
	}

	public function saveAs( $path )
	{
		return $this->editor
				->save( $this->image, $path, null, Framework::Cfg( 'image.jpeg_quality', 90 ) );
	}

	public function save()
	{
		return $this->editor
				->save( $this->image, $this->_filename, null, Framework::Cfg( 'image.jpeg_quality', 90 ) );
	}


	/**
	 * Rotate image
	 * @param $angle
	 * @return $this
	 */
	public function & rotate( $angle )
	{
		$this->editor->rotate( $image, $angle );
		return $this;
	}

	/**
	 * @return bool
	 */
	public function fixRotation()
	{
		$return = false;
		if ( isset( $this->exif[ 'IFD0' ][ 'Orientation' ] ) ) {
			switch ( $this->exif[ 'IFD0' ][ 'Orientation' ] ) {
				case 3:
					$return = true;
					$this->rotate( 180 );
					break;
				case 6:
					$return = true;
					$this->rotate( -90 );
					break;
				case 8:
					$return = true;
					$this->rotate( 90 );
					break;
			}
		}
		return $return;
	}


	public function upload( $name, $destination )
	{
		$file = parent::upload( $name, $destination );
		$this->createEditor();
		return $file;
	}

	/**
	 */
	protected function createEditor()
	{
		if ( $this->_filename ) {
			$this->editor = Grafika::createEditor()
					->open( $this->image, $this->_filename );
		}
	}
}
