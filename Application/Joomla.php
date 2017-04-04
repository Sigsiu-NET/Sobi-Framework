<?php
/**
 * @package: Sobi Framework
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * @created Tue, Mar 28, 2017 11:34:22
 */

namespace Sobi\Application;


use Sobi\Framework;
use Sobi\Input\Input;
use Sobi\Lib\Instance;
use Sobi\Utils\StringUtils;

class Joomla
{
	use Instance;
	/**
	 * @param string $title
	 * @param bool $forceAdd
	 */
	public function setTitle( $title, $forceAdd = false )
	{
		$document = \JFactory::getDocument();
		if ( !( is_array( $title ) ) && ( Framework::Cfg( 'browser.add_title', true ) || $forceAdd ) ) {
			$title = [ $title ];
		}
		if ( is_array( $title ) ) {
			//browser.add_title = true: adds the Joomla part (this is normally the menu item) in front of it (works only if full_title is also set to true)
			$jTitle = $document->getTitle(); //get the title Joomla has set
			if ( Framework::Cfg( 'browser.add_title', true ) || $forceAdd ) {
				if ( $title[ 0 ] != $jTitle ) {
					array_unshift( $title, $jTitle );
				}
			}
			else {
				if ( $title[ 0 ] == $jTitle ) {
					array_shift( $title );
				}
			}
			//if ( Sobi::Cfg( 'browser.full_title', true ) || true ) {
			//browser.full_title = true: if title is array, use only the last. That's e.g. the entry name without categories for SobiPro standard title
			if ( count( $title ) ) {
				if ( is_array( $title ) ) {
					if ( Framework::Cfg( 'browser.reverse_title', false ) ) {
						$title = array_reverse( $title );
					}
					$title = implode( Framework::Cfg( 'browser.title_separator', ' - ' ), $title );
				}
				else {
					$title = isset( $title[ count( $title ) - 1 ] ) ? $title[ count( $title ) - 1 ] : $title[ 0 ];
				}
			}
			else {
				$title = null;
			}

		}
		if ( strlen( $title ) ) {
			if ( !( defined( 'SOBIPRO_ADM' ) ) ) {
				if ( \JFactory::getApplication()->get( 'sitename_pagetitles', 0 ) == 1 ) {
					$title = \JText::sprintf( 'JPAGETITLE', \JFactory::getApplication()->get( 'sitename' ), $title );
				}
				elseif ( \JFactory::getApplication()->get( 'sitename_pagetitles', 0 ) == 2 ) {
					$title = \JText::sprintf( 'JPAGETITLE', $title, \JFactory::getApplication()->get( 'sitename' ) );
				}
			}
			$document->setTitle( StringUtils::Clean( html_entity_decode( $title ) ) );
		}
	}

	/**
	 * @param array $head
	 * @return bool
	 */
	public function addHead( $head )
	{
		if ( strlen( Input::Cmd( 'format' ) ) && Input::Cmd( 'format' ) != 'html' ) {
			return true;
		}
		$document = \JFactory::getDocument();
		$c = 0;
		if ( count( $head ) ) {
			foreach ( $head as $type => $code ) {
				switch ( $type ) {
					default:
						if ( count( $code ) ) {
							foreach ( $code as $html ) {
								++$c;
								$document->addCustomTag( $html );
							}
						}
						break;
					case 'robots' :
					case 'author':
						if ( !( defined( 'SOBI_ADM_PATH' ) ) ) {
							$document->setMetadata( $type, implode( ', ', $code ) );
						}
						break;
					case 'keywords':
						if ( !( defined( 'SOBI_ADM_PATH' ) ) ) {
							$metaKeys = trim( implode( ', ', $code ) );
							if ( Framework::Cfg( 'meta.keys_append', true ) ) {
								$metaKeys .= Framework::Cfg( 'string.meta_keys_separator', ',' ) . $document->getMetaData( 'keywords' );
							}
							$metaKeys = explode( Framework::Cfg( 'string.meta_keys_separator', ',' ), $metaKeys );
							if ( count( $metaKeys ) ) {
								foreach ( $metaKeys as $i => $p ) {
									if ( strlen( trim( $p ) ) ) {
										$metaKeys[ $i ] = trim( $p );
									}
									else {
										unset( $metaKeys[ $i ] );
									}
								}
								$metaKeys = implode( ', ', $metaKeys );
							}
							else {
								$metaKeys = null;
							}
							$document->setMetadata( 'keywords', $metaKeys );
						}
						break;
					case 'description':
						$metaDesc = implode( Framework::Cfg( 'string.meta_desc_separator', ' ' ), $code );
						if ( strlen( $metaDesc ) && !( defined( 'SOBI_ADM_PATH' ) ) ) {
							if ( Framework::Cfg( 'meta.desc_append', true ) ) {
								$metaDesc .= ' ' . $document->get( 'description' );
							}
							$metaDesc = explode( ' ', $metaDesc );
							if ( count( $metaDesc ) ) {
								foreach ( $metaDesc as $i => $p ) {
									if ( strlen( trim( $p ) ) ) {
										$metaDesc[ $i ] = trim( $p );
									}
									else {
										unset( $metaDesc[ $i ] );
									}
								}
								$metaDesc = implode( ' ', $metaDesc );
							}
							else {
								$metaDesc = null;
							}
							$document->setDescription( $metaDesc );
						}
						break;
				}
			}
			$jsUrl = Sobi::FixPath( Framework::Cfg( 'live_site' ) . ( defined( 'SOBI_ADM_FOLDER' ) ? SOBI_ADM_FOLDER . '/' : '' ) . self::Url( [ 'task' => 'txt.js', 'format' => 'json' ], true, false ) );
			$document->addCustomTag( "\n\t<script type=\"text/javascript\" src=\"" . str_replace( '&', '&amp;', $jsUrl ) . "\"></script>\n" );
			$c++;
			$document->addCustomTag( "\n\t<!--  SobiPro ({$c}) Head Tags Output -->\n" );
		}
	}
}
