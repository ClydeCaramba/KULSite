<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/17/14 11:34 PM $
* @copyright (C) 2004-2018 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\PluginTable;

defined('CBLIB') or die();

/**
 * cbPMS Class implementation
 * PMS handler class
 */
class cbPMS extends cbPMSHandler
{
	/**
	 * List of installed and enabled PMS plugins
	 * @var PluginTable[]
	 */
	protected $PMSpluginsList;

	/**
	 * Constructor
	 */
	public function _construct()
	{
		parent::__construct();

		$this->PMSpluginsList = null;
	}

	/**
	 * Calls the plugin $plug for class $pluginClass method $method with $args
	 *
	 * @param  PluginTable  $plug
	 * @param  array        $args
	 * @param  string       $pluginClass
	 * @param  string       $method
	 * @return mixed|null
	 */
	private function _callPlugin( $plug, $args, $pluginClass, $method )
	{
		global $_PLUGINS;

		$results	=	null;

		if ( $plug->id ) {
			if( $_PLUGINS->loadPluginGroup( 'user', array( (int) $plug->id ) ) ) {
				$results=$_PLUGINS->call( $plug->id, $method, $pluginClass, $args, $plug->params );
			}
		}

		return $results;
	}

	/**
	 * Calls the plugin type's method $methodName with $args
	 *
	 * @param  string  $type        Plugin element
	 * @param  string  $methodName  Method name
	 * @param  array   $args        Arguments to pass to method
	 * @return array                Results of the calls to the plugins
	 */
	private function _callPluginTypeMethod( $type, $methodName, $args)
	{
		global $_CB_database;

		$results					=	array();

		if ($this->PMSpluginsList === null) {
			$_CB_database->setQuery( "SELECT * FROM #__comprofiler_plugin p"
				. "\n WHERE p.published=1 "
				. "\n AND p.element LIKE '%" . $_CB_database->getEscaped( trim( strtolower( $_CB_database->getEscaped($type) ) ), true ) . ".%' "
				. "\n ORDER BY p.ordering" );

			$this->PMSpluginsList	=	$_CB_database->loadObjectList( null, '\CB\Database\Table\PluginTable' );

			if ( $_CB_database->getErrorNum() ) {
				return $results;
			}
		}

		foreach($this->PMSpluginsList AS $plug) {
			$className = 'get'.substr($plug->element, strlen($type)+1).'Tab';
			$results[] = $this->_callPlugin($plug, $args, $className, $methodName);
		}

		return $results;
	}

	/**
	 * Sends a PMS message on the enabled "pms.*" plugins
	 *
	 * @param  int     $toUserId        User Id of receiver
	 * @param  int     $fromUserId      User Id of sender
	 * @param  string  $subject         Subject of PMS message (UNESCAPED)
	 * @param  string  $message         Body of PMS message (UNESCAPED)
	 * @param  boolean $systemGenerated FALSE: real user-to-user message; TRUE: system-Generated by an action from user $fromid (if non-null)
	 * @param  string  $fromName        The name of the public sender
	 * @param  string  $fromEmail       The email address of the public sender
	 * @return boolean[]                TRUE for OK, or FALSE if ErrorMSG generated. Special error: CBTxt::T( 'UE_PMS_TYPE_UNSUPPORTED', 'This private message type is not supported by the selected PMS system!') : if anonym fromid>=0 sysgenerated unsupported
	 */
	public function sendPMSMSG( $toUserId, $fromUserId, $subject, $message, $systemGenerated = false, $fromName = null, $fromEmail = null )
	{
		$args = array($toUserId, $fromUserId, $subject, $message, $systemGenerated, $fromName, $fromEmail);
		return $this->_callPluginTypeMethod('pms', 'sendUserPMS', $args);
	}

	/**
	 * Returns all the parameters needed for a hyperlink or a menu entry to do a pms action
	 *
	 * @param  int      $toUserId     User Id of receiver
	 * @param  int      $fromUserId   User Id of sender
	 * @param  string   $subject      Subject of PMS message (UNESCAPED)
	 * @param  string   $message      Body of PMS message (UNESCAPED)
	 * @param  int      $kind         Kind of link: 1: link to compose new PMS message for $toid user. 2: link to inbox of $fromid user; 3: outbox, 4: trashbox, 5: link to edit pms options
	 * @return array                  Array of string {"caption" => menu-text ,"url" => NON-cbSef relative url-link, "tooltip" => description} or false and errorMSG
	 */
	public function getPMSlinks( $toUserId = 0, $fromUserId = 0, $subject = '', $message = '', $kind )
	{
		$args = array($toUserId, $fromUserId, $subject, $message, $kind);
		return $this->_callPluginTypeMethod('pms', 'getPMSlink', $args);
	}

	/**
	 * gets PMS system capabilities
	 *
	 * @return array  array of string {"subject" => boolean, "body" => boolean, "public" => boolean} or false if ErrorMSG generated
	 */
	public function getPMScapabilites( )
	{
		$args = array();
		return $this->_callPluginTypeMethod('pms', 'getPMScapabilites', $args);
	}

	/**
	 * gets PMS unread messages count
	 *
	 * @param	int    $userId  User id
	 * @return	int[]           Number of messages unread by user $userid for each PMS system (normally only one)
	 */
	public function getPMSunreadCount( $userId )
	{
		$args = array( $userId );
		return $this->_callPluginTypeMethod('pms', 'getPMSunreadCount', $args);
	}
}
