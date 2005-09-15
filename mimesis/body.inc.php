<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

/**
 * @package MIMESIS_BODY
 */
class
MemoryMimeBody
// extends/implements MimeBody
{
	/**
	 * @var string
	 * @access private
	 */
	var $_body;

	/**
	 * Handle a body line.  Add to internal representation of body.
	 *
	 * @param string Line of MIME entity body text
	 * @param integer Byte position within source of line
	 */
	function
	handleRawLineByRef (&$line, $pos = null)
	{
		$this->_body .= $line . "\r\n";
	}

	/**
	 * @see addLineByRef
	 * @param string Line of MIME entity body text
	 * @param integer Byte position within source of line
	 */
	function
	handleRawLine ($line, $pos = null)
	{
		$this->addLineByRef ($line, $pos);
	}

	/**
	 * Return full body text.
	 *
	 * @return string
	 */
	function
	getBody ()
	{
		return $this->_body;
	}
}

?>
