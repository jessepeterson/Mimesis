<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

/**
 * @package MIMESIS_HEADER
 */
class
Mailbox
{
	/** @access private */
	var $_displayName;

	/** @access private */
	var $_localPart;

	/** @access private */
	var $_domain;

	/** @param string */
	function
	setDisplayName ($name)
	{
		$this->_displayName = $name;
	}

	/** @param string */
	function
	setLocalPart ($localPart)
	{
		$this->_localPart = $localPart;
	}

	/** @param string */
	function
	setDomain ($domain)
	{
		$this->_domain = $domain;
	}
}

?>
