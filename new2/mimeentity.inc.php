<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

/**
 * @package MIMESIS_CORE
 */
class
MimeEntity
{
	/** @access private */
	var $_headerFields = array ();

	/** @access private */
	var $_components = array ();

	/**
	 * @param MimeHeaderField
	 */
	function
	addHeaderField (&$headerField)
	{
		$this->_headerFields[] =& $headerField;
	}

	/**
	 * Get first matching header field name.
	 *
	 * @param string Header field name
	 * @return MimeHeaderField
	 */
	function
	&getHeaderFieldByName ($name)
	{
		$name = strtolower ($name);

		foreach (array_keys ($this->_headerFields) as $hdr_key)
		{
			if (strtolower ($this->_headerFields[$hdr_key]->getName ()) == $name)
				return $this->_headerFields[$hdr_key];		
		}
	}

	/**
	 * Add child entity to this entity for support of composite
	 * MIME entities such as multipart and message types.
	 *
	 * @param MimeEntity
	 */
	function
	addComponent (&$mimeEntity)
	{
		$this->_components[] =& $mimeEntity;
	}
}

?>
