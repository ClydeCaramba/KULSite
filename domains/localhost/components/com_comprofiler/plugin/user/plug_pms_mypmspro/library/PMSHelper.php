<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2018 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Plugin\PMS;

use CBLib\Application\Application;
use CBLib\Registry\GetterInterface;
use CBLib\Registry\Registry;

defined('CBLIB') or die();

class PMSHelper
{

	/**
	 * @return Registry
	 */
	static public function getGlobalParams()
	{
		global $_PLUGINS;

		static $params	=	null;

		if ( ! $params ) {
			$plugin		=	$_PLUGINS->getLoadedPlugin( 'user', 'pms.mypmspro' );
			$params		=	new Registry();

			if ( $plugin ) {
				$params->load( $plugin->params );
			}
		}

		return $params;
	}

	/**
	 * Returns the path to a template file
	 *
	 * @param null|string $template
	 * @param null|string $file
	 * @param bool|array  $headers
	 * @return null|string
	 */
	static public function getTemplate( $template = null, $file = null, $headers = array( 'template', 'override' ) )
	{
		global $_CB_framework, $_PLUGINS;

		$plugin							=	$_PLUGINS->getLoadedPlugin( 'user', 'pms.mypmspro' );

		if ( ! $plugin ) {
			return null;
		}

		static $defaultTemplate			=	null;

		if ( $defaultTemplate === null ) {
			$defaultTemplate			=	self::getGlobalParams()->get( 'general_template', 'default', GetterInterface::STRING );
		}

		if ( ( $template === '' ) || ( $template === null ) || ( $template === '-1' ) ) {
			$template					=	$defaultTemplate;
		}

		if ( ! $template ) {
			$template					=	'default';
		}

		$livePath						=	$_PLUGINS->getPluginLivePath( $plugin );
		$absPath						=	$_PLUGINS->getPluginPath( $plugin );

		$template						=	preg_replace( '/[^-a-zA-Z0-9_]/', '', $template );
		$file							=	preg_replace( '/[^-a-zA-Z0-9_]/', '', $file );
		$return							=	null;

		if ( $file ) {
			if ( $headers !== false ) {
				$headers[]				=	$file;
			}

			$php						=	$absPath . '/templates/' . $template . '/' . $file . '.php';

			if ( ! file_exists( $php ) ) {
				$php					=	$absPath . '/templates/default/' . $file . '.php';
			}

			if ( file_exists( $php ) ) {
				$return					=	$php;
			}
		}

		if ( $headers !== false ) {
			static $loaded				=	array();

			$loaded[$template]			=	array();

			// Global CSS File:
			if ( in_array( 'template', $headers ) && ( ! in_array( 'template', $loaded[$template] ) ) ) {
				$global					=	'/templates/' . $template . '/template.css';

				if ( ! file_exists( $absPath . $global ) ) {
					$global				=	'/templates/default/template.css';
				}

				if ( file_exists( $absPath . $global ) ) {
					$_CB_framework->document->addHeadStyleSheet( $livePath . $global );
				}

				$loaded[$template][]	=	'template';
			}

			// File or Custom CSS/JS Headers:
			foreach ( $headers as $header ) {
				if ( in_array( $header, $loaded[$template] ) || in_array( $header, array( 'template', 'override' ) ) ) {
					continue;
				}

				$header					=	preg_replace( '/[^-a-zA-Z0-9_]/', '', $header );

				if ( ! $header ) {
					continue;
				}

				$css					=	'/templates/' . $template . '/' . $header . '.css';
				$js						=	'/templates/' . $template . '/' . $header . '.js';

				if ( ! file_exists( $absPath . $css ) ) {
					$css				=	'/templates/default/' . $header . '.css';
				}

				if ( file_exists( $absPath . $css ) ) {
					$_CB_framework->document->addHeadStyleSheet( $livePath . $css );
				}

				if ( ! file_exists( $absPath . $js ) ) {
					$js					=	'/templates/default/' . $header . '.js';
				}

				if ( file_exists( $absPath . $js ) ) {
					$_CB_framework->document->addHeadScriptUrl( $livePath . $js );
				}

				$loaded[$template][]	=	$header;
			}

			// Override CSS File:
			if ( in_array( 'override', $headers ) && ( ! in_array( 'override', $loaded[$template] ) ) ) {
				$override				=	'/templates/' . $template . '/override.css';

				if ( file_exists( $absPath . $override ) ) {
					$_CB_framework->document->addHeadStyleSheet( $livePath . $override );
				}

				$loaded[$template][]	=	'override';
			}
		}

		return $return;
	}

	/**
	 * Returns the current return url or generates one from current page
	 *
	 * @param bool|false $current
	 * @param bool|false $raw
	 * @return null|string
	 */
	static public function getReturn( $current = false, $raw = false )
	{
		static $cache				=	array();

		if ( ! isset( $cache[$current] ) ) {
			$url					=	null;

			if ( $current ) {
				$returnUrl			=	Application::Input()->get( 'get/return', '', GetterInterface::BASE64 );

				if ( $returnUrl ) {
					$returnUrl		=	base64_decode( $returnUrl );

					if ( Application::Router()->isInternal( $returnUrl ) ) {
						$url		=	$returnUrl;
					}
				}
			} else {
				$url				=	Application::Router()->getCurrentURL();
			}

			$cache[$current]		=	$url;
		}

		$return						=	$cache[$current];

		if ( ( ! $raw ) && $return ) {
			$return					=	base64_encode( $return );
		}

		return $return;
	}

	/**
	 * Redirects to the return url if available otherwise to the url specified
	 *
	 * @param string      $url
	 * @param null|string $message
	 * @param string      $messageType
	 */
	static public function returnRedirect( $url, $message = null, $messageType = 'message' )
	{
		$returnUrl		=	self::getReturn( true, true );

		cbRedirect( ( $returnUrl ? $returnUrl : $url ), $message, $messageType );
	}

	/**
	 * Converts BBCode to HTML
	 *
	 * @param string $string
	 * @return string
	 */
	static public function bbcodeToHTML( $string )
	{
		if ( UddeIM::isUddeIM() ) {
			return UddeIM::bbcodeToHTML( $string );
		}

		static $regexp		=	array(	'link'		=>	'#^((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))$#i',
										'email'		=>	'/^[a-z0-9!#$%&\'*+\\\\\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\\\\\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i'
									);

		// Left Align:
		$string				=	preg_replace( '%\[left\](.*)\[/left\]%siU', '<div class="text-left">$1</div>', $string );

		// Center Align:
		$string				=	preg_replace( '%\[center\](.*)\[/center\]%siU', '<div class="text-center">$1</div>', $string );

		// Right Align:
		$string				=	preg_replace( '%\[right\](.*)\[/right\]%siU', '<div class="text-right">$1</div>', $string );

		// Bold:
		$string				=	preg_replace( '%\[b\](.*)\[/b\]%siU', '<strong>$1</strong>', $string );
		$string				=	preg_replace( '%\[bold\](.*)\[/bold\]%siU', '<strong>$1</strong>', $string );
		$string				=	preg_replace( '%\[strong\](.*)\[/strong\]%siU', '<strong>$1</strong>', $string );

		// Small:
		$string				=	preg_replace( '%\[small\](.*)\[/small\]%siU', '<small>$1</small>', $string );

		// Italic:
		$string				=	preg_replace( '%\[i\](.*)\[/i\]%siU', '<em>$1</em>', $string );

		// Underline:
		$string				=	preg_replace( '%\[u\](.*)\[/u\]%siU', '<u>$1</u>', $string );
		$string				=	preg_replace( '%\[ins\](.*)\[/ins\]%siU', '<ins>$1</ins>', $string );

		// Subscript:
		$string				=	preg_replace( '%\[sub\](.*)\[/sub\]%siU', '<sub>$1</sub>', $string );

		// Superscript:
		$string				=	preg_replace( '%\[sup\](.*)\[/sup\]%siU', '<sup>$1</sup>', $string );

		// Mark:
		$string				=	preg_replace( '%\[mark\](.*)\[/mark\]%siU', '<mark>$1</mark>', $string );

		// Strikethrough:
		$string				=	preg_replace( '%\[s\](.*)\[/s\]%siU', '<s>$1</s>', $string );
		$string				=	preg_replace( '%\[del\](.*)\[/del\]%siU', '<del>$1</del>', $string );
		$string				=	preg_replace( '%\[strike\](.*)\[/strike\]%siU', '<s>$1</s>', $string );

		// Font size:
		$string				=	preg_replace( '#\[size="?(8[0-9]|9[0-9]|1[0-9]{2}|200)(?:px|em|pt|%)?"?\](.*)\[/size\]#siU', '<span style="font-size: $1%;">$2</span>', $string );
		$string				=	preg_replace( '#\[style size="?(8[0-9]|9[0-9]|1[0-9]{2}|200)(?:px|em|pt|%)?"?\](.*)\[/style\]#siU', '<span style="font-size: $1%;">$2</span>', $string );

		// Font color:
		$string				=	preg_replace( '%\[color="?([a-zA-Z]+|#[a-zA-Z0-9]+)"?\](.*)\[/color\]%siU', '<span style="color: $1;">$2</span>', $string );
		$string				=	preg_replace( '%\[style color="?([a-zA-Z]+|#[a-zA-Z0-9]+)"?\](.*)\[/style\]%siU', '<span style="color: $1;">$2</span>', $string );

		// Code:
		$string				=	preg_replace( '%\[code\](.*)\[/code\]%siU', '<code>$1</code>', $string );

		// Quote:
		$string				=	preg_replace( '%\[quote\](.*)\[/quote\]%siU', '<blockquote>$1</blockquote>', $string );

		// URLs
		$string				=	preg_replace_callback( '%\[(?:top)?url(?:="?(.*)"?)?\](.*)\[/(?:top)?url\]%iU', function( array $matches ) use ( $regexp ) {
									$url				=	( isset( $matches[1] ) ? cbUnHtmlspecialchars( $matches[1] ) : null );
									$hypertext			=	( isset( $matches[2] ) ? cbUnHtmlspecialchars( $matches[2] ) : null );

									if ( ! $url ) {
										$url			=	$hypertext;
									}

									if ( ! preg_match( $regexp['link'], $url, $match ) ) {
										return $matches[0];
									}

									if ( substr( $url, 0, 3 ) == 'www' ) {
										$url			=	'http://' . $url;
									}

									$newWindow			=	( ! Application::Router()->isInternal( $url ) );

									if ( ! $newWindow ) {
										$extension		=	strtolower( pathinfo( $url, PATHINFO_EXTENSION ) );

										if ( $extension && ( ! preg_match( '/^(php|asp|html)/', $extension ) ) ) {
											$newWindow	=	true;
										}
									}

									return '<a href="' . htmlspecialchars( $url ) . '"' . ( $newWindow ? ' target="_blank" rel="nofollow noopener"' : null ) . '>' . htmlspecialchars( $hypertext ) . '</a>';
								}, $string );

		// Emails
		$string				=	preg_replace_callback( '%\[email\](.*)\[/email\]%iU', function( array $matches ) use ( $regexp ) {
									$email			=	( isset( $matches[1] ) ? cbUnHtmlspecialchars( $matches[1] ) : null );

									if ( ! preg_match( $regexp['email'], $email, $match ) ) {
										return $matches[0];
									}

									return '<a href="mailto:' . htmlspecialchars( $email ) . '">' . htmlspecialchars( $email ) . '</a>';
								}, $string );

		// Images
		$string				=	preg_replace_callback( '%\[img\](.*)\[/img\]%iU', function( array $matches ) use ( $regexp ) {
									$image			=	( isset( $matches[1] ) ? cbUnHtmlspecialchars( $matches[1] ) : null );

									if ( ! preg_match( $regexp['link'], $image, $match ) ) {
										return $matches[0];
									}

									if ( substr( $image, 0, 3 ) == 'www' ) {
										$image		=	'http://' . $image;
									}

									$extension		=	strtolower( preg_replace( '/[^-a-zA-Z0-9_]/', '', pathinfo( $image, PATHINFO_EXTENSION ) ) );

									if ( ! in_array( $extension, array( 'jpg', 'jpeg', 'gif', 'png', 'svg', 'ico', 'bmp' ) ) ) {
										return $matches[0];
									}

									return '<img src="' . htmlspecialchars( $image ) . '" class="img-fluid" />';
								}, $string );

		// Lists
		$string				=	preg_replace_callback( '%(?:\[list\](.*)\[/list\])|(?:\[ol\](.*)\[ol\])|(?:\[ul\](.*)\[ul\])%siU', function( array $matches ) {
									$listType		=	'ul';
									$list			=	( isset( $matches[1] ) ? $matches[1] : ( isset( $matches[2] ) ? $matches[2] : null ) );

									if ( ! $list ) {
										$listType	=	'ol';
										$list		=	( isset( $matches[3] ) ? $matches[3] : null );
									}

									return "<$listType>" . preg_replace( '%\[li\](.*)\[/li\]%siU', '<li>$1</li>', $list ) . "</$listType>";
								}, $string );

		// Tables
		$string				=	preg_replace_callback( '%\[table\](.*)\[/table\]%siU', function( array $matches ) {
									$table			=	( isset( $matches[1] ) ? $matches[1] : null );

									// Table Header:
									$table			=	preg_replace( '%\[thead\](.*)\[/thead\]%siU', '<thead>$1</thead>', $table );

									// Table Body:
									$table			=	preg_replace( '%\[tbody\](.*)\[/tbody\]%siU', '<tbody>$1</tbody>', $table );

									// Table Footer:
									$table			=	preg_replace( '%\[tfoot\](.*)\[/tfoot\]%siU', '<tfoot>$1</tfoot>', $table );

									// Table Row:
									$table			=	preg_replace( '%\[tr\](.*)\[/tr\]%siU', '<tr>$1</tr>', $table );

									// Table Header Column:
									$table			=	preg_replace( '%\[th\](.*)\[/th\]%siU', '<th>$1</th>', $table );

									// Table Column:
									$table			=	preg_replace( '%\[td\](.*)\[/td\]%siU', '<td>$1</td>', $table );

									return '<div class="table-responsive"><table class="table table-bordered">' . $table . '</table></div>';
								}, $string );

		return $string;
	}

	/**
	 * Removes duplicate spacing characters (spaces, tabs, and linebreaks) from supplied message
	 *
	 * @param string $message
	 * @return string
	 */
	static public function removeDuplicateSpacing( $message )
	{
		// Remove duplicate spaces:
		$message	=	preg_replace( '/ {2,}/i', ' ', $message );
		// Remove duplicate tabs:
		$message	=	preg_replace( '/\t{2,}/i', "\t", $message );
		// Remove duplicate linebreaks:
		$message	=	preg_replace( '/((?:\r\n|\r|\n){2})(?:\r\n|\r|\n)*/i', '$1', $message );
		// Remove trailing spaces and linebreaks:
		$message	=	trim( $message );

		return $message;
	}
}
