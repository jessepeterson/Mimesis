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

/**
 * @package MIMESIS_BODY
 */
class
IntraFileMimeBody
{
	/**
	 * @var string
	 * @access private
	 */
	var $_fileName;

	/**
	 * @var integer
	 * @access private
	 */
	var $_start = 0;

	/**
	 * @var integer
	 * @access private
	 */
	var $_endPos = 0;

	/**
	 * Handle a body line passed by reference.
	 *
	 * @param string Line of MIME entity body text
	 * @param integer Byte position within source of line
	 */
	function
	handleRawLineByRef (&$line, $pos = null)
	{
		if ((0 == $this->_start) or ($pos < $this->_start))
			$this->_start = $pos;

		$this->_endPos = $pos + strlen ($line);
	}

	/**
	 * Rreturn full body text.
	 *
	 * @return string
	 */
	function
	getBody ()
	{
		if ($f = fopen ($this->_fileName, 'r'))
		{
			fseek ($f, $this->_start);
			$body = fread ($f, $this->_endPos - $this->_start);
			fclose ($f);

			return $body;
		}
	}

	/**
	 * @param string
	 */
	function
	setFileName ($fileName)
	{
		$this->_fileName = $fileName;
	}
}

?>
