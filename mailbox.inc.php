<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

class
Mailbox
{
	/** @access public */
	var $addrspec;

	/** @access private */
	var $_displayName;

	function
	Mailbox ()
	{
		$this->addrspec =& new Addrsepc;
	}
}

class
Addrspec
{
	var $_domain;
	var $_localPart;
}

?>
