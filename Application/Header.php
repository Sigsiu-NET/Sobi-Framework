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
 * @created Mon, Mar 27, 2017 14:30:04
 */

namespace Sobi\Application;

use Sobi\Framework;
use Sobi\Input\Input;
use Sobi\Utils\Arr;
use Sobi\Utils\StringUtils;

defined( 'SOBI' ) || exit( 'Restricted access' );

class Header
{

	/*** @var array */
	private $head = [];
	/*** @var array */
	private $css = [];
	/*** @var array */
	private $cssFiles = [];
	/*** @var array */
	private $js = [];
	/*** @var array */
	private $links = [];
	/*** @var array */
	private $jsFiles = [];
	/*** @var array */
	private $author = [];
	/*** @var array */
	private $title = [];
	/*** @var array */
	private $robots = [];
	/*** @var array */
	private $description = [];
	/*** @var array */
	private $keywords = [];
	/*** @var array */
	private $raw = [];
	/*** @var int */
	private $count = 0;
	/*** @var array */
	private $_cache = [ 'js' => [], 'css' => [] ];
	/** @var array */
	private $_store = [];
	/** @var array */
	private $_checksums = [];

	/**
	 * @return $this
	 */
	public static function & getInstance()
	{
		static $head = null;
		if ( !$head || !( $head instanceof self ) ) {
			$head = new self();
		}

		return $head;
	}

	/**
	 * @param bool $adm
	 *
	 * @return $this
	 *
	 * @since version
	 */
	public function & initBase( $adm = false )
	{
		if ( $adm ) {
			$this->addCssFile( [ 'bootstrap.bootstrap', 'admicons', 'adm.sobipro' ] )
					->addJsFile( [ 'sobipro', 'adm.sobipro', 'jquery', 'jqnc', 'bootstrap', 'adm.interface' ] );
		}
		else {
			$this->addCssFile( [ 'sobipro' ] )
					->addJsFile( [ 'sobipro', 'jquery', 'jqnc' ] );
			if ( Framework::Cfg( 'template.bootstrap3-load', false ) && !defined( 'SOBIPRO_ADM' ) ) {
				if ( Framework::Cfg( 'template.bootstrap3-source', true ) ) { //true=local, false=CDN
					$this->addCssFile( 'b3bootstrap.b3bootstrap' )
							->addJsFile( 'b3bootstrap' );
				}
				else {
					$this->addHeadLink( Framework::Cfg( 'template.bs3_css', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' ), null, null, 'stylesheet' )
							->addJsUrl( Framework::Cfg( 'template.bs3_js', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js' ) );
				}
			}
			else {
				$this->addCssFile( 'bootstrap.bootstrap' )
						->addJsFile( 'bootstrap' );
			}
			$fonts = Framework::Cfg( 'template.icon_fonts_arr', [] );
			if ( count( $fonts ) ) {
				foreach ( $fonts as $font ) {
					if ( $font == 'font-awesome-3-local' ) {
						$this->addCssFile( 'admicons' );
					}
					elseif ( Framework::Cfg( 'icon-fonts.' . $font ) ) {
						$this->addHeadLink( Framework::Cfg( 'icon-fonts.' . $font ), null, null, 'stylesheet' );
					}
				}
			}
		}
		return $this;
	}

	protected function store( $args, $id )
	{
		if ( isset( $args[ 'this' ] ) ) {
			unset( $args[ 'this' ] );
		}
		$this->_store[ $id ][] = $args;

	}

	/**
	 * Add raw code to the site header
	 *
	 * @param string $html
	 *
	 * @return $this
	 */
	public function & add( $html )
	{
		$checksum = md5( $html );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->raw[ ++$this->count ] = $html;
			$this->store( get_defined_vars(), __FUNCTION__ );
		}

		return $this;
	}

	/**
	 * @deprecated @see SPHeader::meta
	 *
	 * @param string $name
	 * @param string $content
	 * @param array $attributes
	 *
	 * @return $this
	 */
	public function & addMeta( $name, $content, $attributes = [] )
	{
		$checksum = md5( json_encode( get_defined_vars() ) );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->store( get_defined_vars(), __FUNCTION__ );
			$custom = null;
			if ( count( $attributes ) ) {
				foreach ( $attributes as $attribute => $value ) {
					$custom .= $attribute . '="' . $value . '"';
				}
			}
			if ( strlen( $name ) ) {
				$name = " name=\"{$name}\" ";
			}
			$this->raw[ ++$this->count ] = "<meta{$name} content=\"{$content}\" {$custom}/>";
		}

		return $this;
	}

	/**
	 * Add JavaScript code to the site header
	 *
	 * @param $content
	 * @param string $name
	 * @param array $attributes
	 *
	 * @internal param string $js
	 * @return $this
	 */
	public function & meta( $content, $name = null, $attributes = [] )
	{
		$checksum = md5( json_encode( get_defined_vars() ) );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->store( get_defined_vars(), __FUNCTION__ );
			$custom = null;
			if ( strlen( $name ) ) {
				$name = "name=\"{$name}\" ";
			}
			if ( count( $attributes ) ) {
				foreach ( $attributes as $attr => $value ) {
					$custom .= $attr . '="' . $value . '"';
				}
			}
			$this->raw[ ++$this->count ] = "<meta {$name}content=\"{$content}\" {$custom}/>";
		}

		return $this;
	}

	/**
	 * Add JavaScript code to the site header
	 *
	 * @param string $js
	 *
	 * @return $this
	 */
	public function & addJsCode( $js )
	{
		$checksum = md5( json_encode( get_defined_vars() ) );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->store( get_defined_vars(), __FUNCTION__ );
			$this->js[ ++$this->count ] = $js;
		}

		return $this;
	}

	/**
	 * Add JavaScript file to the site header
	 *
	 * @param $script
	 * @param bool $adm
	 * @param string $params
	 * @param bool $force
	 * @param string $ext
	 *
	 * @return $this
	 */
	public function & addJsFile( $script, $adm = false, $params = null, $force = false, $ext = 'js' )
	{
		if ( is_array( $script ) && count( $script ) ) {
			foreach ( $script as $f ) {
				$this->addJsFile( $f, $adm, $params, $force, $ext );
			}
		}
		else {
			$checksum = md5( json_encode( get_defined_vars() ) );
			if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
				$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
				$this->store( get_defined_vars(), __FUNCTION__ );

				if ( $script == 'jquery' ) {
					\JHtml::_( 'jquery.framework' );
					return $this;
				}
				if ( $script == 'bootstrap' ) {
					\JHtml::_( 'bootstrap.framework' );
					return $this;
				}
			}
		}

		return $this;
	}

	/**
	 * Add external JavaScript file to the site header
	 *
	 * @param string $file
	 * @param string $params
	 *
	 * @return $this
	 */
	public function & addJsUrl( $file, $params = null )
	{
		if ( is_array( $file ) && count( $file ) ) {
			foreach ( $file as $f ) {
				$this->addJsUrl( $f );
			}
		}
		else {
			$checksum = md5( json_encode( get_defined_vars() ) );
			if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
				$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
				$this->store( get_defined_vars(), __FUNCTION__ );
				$params = $params ? '?' . $params : null;
				$file = "\n<script type=\"text/javascript\" src=\"{$file}{$params}\"></script>";
				if ( !in_array( $file, $this->jsFiles ) ) {
					$this->jsFiles[ ++$this->count ] = $file;
				}
			}
		}

		return $this;
	}

	/**
	 * Add CSS code to the site header
	 *
	 * @param string $css
	 *
	 * @return $this
	 */
	public function & addCSSCode( $css )
	{
		$checksum = md5( $css );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->store( get_defined_vars(), __FUNCTION__ );
			$this->css[ ++$this->count ] = $css;
		}

		return $this;
	}

	/**
	 * Add CSS file to the site header
	 *
	 * @param string $file file name
	 * @param bool $adm
	 * @param null $media
	 * @param bool $force
	 * @param string $ext
	 * @param string $params
	 *
	 * @return $this
	 */
	public function & addCssFile( $file, $adm = false, $media = null, $force = false, $ext = 'css', $params = null )
	{
		return $this;
	}

	/**
	 * Add alternate link to the site header
	 *
	 * @param string $href
	 * @param string $type
	 * @param string $title
	 * @param string $rel
	 * @param string $relType
	 * @param array $params
	 *
	 * @return $this
	 */
	public function & addHeadLink( $href, $type = null, $title = null, $rel = 'alternate', $relType = 'rel', $params = null )
	{
		$checksum = md5( json_encode( get_defined_vars() ) );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->store( get_defined_vars(), __FUNCTION__ );
			$title = $title ? " title=\"{$title}\" " : null;
			if ( $params && count( $params ) ) {
				$arr = new Arr();
				$params = $arr->toString( $params );
			}
			if ( $type ) {
				$type = "type=\"{$type}\" ";
			}
			$href = preg_replace( '/&(?![#]?[a-z0-9]+;)/i', '&amp;', $href );
			$title = preg_replace( '/&(?![#]?[a-z0-9]+;)/i', '&amp;', $title );
			$this->links[] = "<link href=\"{$href}\" {$relType}=\"{$rel}\" {$type}{$params}{$title}/>";
			$this->links = array_unique( $this->links );
		}

		return $this;
	}

	public function & addCanonical( $url )
	{
		$checksum = md5( $url );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->store( get_defined_vars(), __FUNCTION__ );
			return $this->addHeadLink( $url, null, null, 'canonical' );
		}
	}

	/**
	 * Set Site title
	 *
	 * @param string $title
	 * @param array $site
	 *
	 * @return $this
	 */
	public function & addTitle( $title, $site = [] )
	{
		if ( count( $site ) && $site[ 0 ] > 1 ) {
			if ( !( is_array( $title ) ) ) {
				$title = [ $title ];
			}
			if ( $site[ 1 ] > 1 ) { // no page counter when on page 1
				$title[] = Framework::Txt( 'SITES_COUNTER', $site[ 1 ], $site[ 0 ] );
			}
		}
		if ( is_array( $title ) ) {
			foreach ( $title as $segment ) {
				$this->addTitle( $segment );
			}
		}
		else {
			$checksum = md5( $title );
			if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
				$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
				$args = get_defined_vars();
				unset( $args[ 'site' ] );
				$this->store( $args, __FUNCTION__ );
				$this->title[] = $title;
			}
		}

		return $this;
	}

	/**
	 * Add meta description to the site header
	 *
	 * @param string $desc
	 *
	 * @return $this
	 */
	public function & addDescription( $desc )
	{
		if ( is_string( $desc ) ) {
			$checksum = md5( $desc );
			if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
				$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
				$this->store( get_defined_vars(), __FUNCTION__ );
				if ( strlen( $desc ) ) {
					$this->description[] = strip_tags( str_replace( '"', "'", StringUtils::Entities( $desc, true ) ) );
				}
			}
		}

		return $this;
	}

	/**
	 * Set Site title
	 *
	 * @param string $title
	 *
	 * @return $this
	 */
	public function & setTitle( $title )
	{
		if ( defined( 'SOBIPRO_ADM' ) ) {
			Joomla::Instance()->setTitle( StringUtils::Clean( $title ) );
		}
		if ( is_array( $title ) ) {
			$this->title = $title;
		}
		else {
			$this->title = [ StringUtils::Clean( $title ) ];
		}
		return $this;
	}

	public function addRobots( $robots )
	{
		$this->robots = [ $robots ];
	}

	public function addAuthor( $author )
	{
		$checksum = md5( $author );
		if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
			$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
			$this->store( get_defined_vars(), __FUNCTION__ );
			$this->author[] = $author;
		}
	}

	/**
	 * Add a keywords to the site header
	 *
	 * @param $keys
	 *
	 * @internal param string $key
	 * @return $this
	 */
	public function & addKeyword( $keys )
	{
		if ( is_string( $keys ) ) {
			$checksum = md5( $keys );
			if ( !( isset( $this->_checksums[ __FUNCTION__ ][ $checksum ] ) ) ) {
				$this->_checksums[ __FUNCTION__ ][ $checksum ] = true;
				$this->store( get_defined_vars(), __FUNCTION__ );
				if ( strlen( $keys ) ) {
					$keys = explode( Framework::Cfg( 'string.meta_keys_separator', ',' ), $keys );
					if ( !empty( $keys ) ) {
						$this->count++;
						foreach ( $keys as $key ) {
							$this->keywords[] = strip_tags( trim( StringUtils::Entities( $key, true ) ) );
						}
					}
				}
			}
		}

		return $this;
	}

	public function getData( $index )
	{
		if ( isset( $this->$index ) ) {
			return $this->$index;
		}
		else {
			return null;
		}
	}

	public function & reset()
	{
		$this->keywords = [];
		$this->author = [];
		$this->robots = [];
		$this->description = [];
		$this->cssFiles = [];
		$this->jsFiles = [];
		$this->css = [];
		$this->js = [];
		$this->raw = [];
		$this->head = [];
		$this->_store = [];
		return $this;
	}

	/**
	 * Send the header
	 */
	public function sendHeader()
	{
		if ( count( $this->_store ) ) {
			if ( count( $this->js ) ) {
				$jsCode = null;
				foreach ( $this->js as $js ) {
					$jsCode .= "\n\t" . str_replace( "\n", "\n\t", $js );
				}
				$this->js = [ "\n<script type=\"text/javascript\">\n/*<![CDATA[*/{$jsCode}\n/*]]>*/\n</script>\n" ];
			}
			if ( count( $this->css ) ) {
				$cssCode = null;
				foreach ( $this->css as $css ) {
					$cssCode .= "\n\t" . str_replace( "\n", "\n\t", $css );
				}
				$this->css = [ "<style type=\"text/css\">\n{$cssCode}\n</style>" ];
			}
			$this->head[ 'keywords' ] = array_reverse( $this->keywords );
			$this->head[ 'author' ] = $this->author;
			$this->head[ 'robots' ] = $this->robots;
			$this->head[ 'description' ] = array_reverse( $this->description );
			$this->head[ 'links' ] = $this->links;
			$this->head[ 'css' ] = array_merge( $this->head[ 'css' ], $this->css );
			$this->head[ 'js' ] = array_merge( $this->head[ 'js' ], $this->js );
			$this->head[ 'raw' ] = $this->raw;
			SPFactory::mainframe()->addHead( $this->head );
			if ( count( $this->title ) ) {
				Joomla::Instance()->setTitle( StringUtils::Clean( $this->title ) );
			}
			$this->reset();
		}
	}
}
