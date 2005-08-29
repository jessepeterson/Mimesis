<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

/**
 */
class
MimeEntityFileParser
{
	var $_builder;
	var $_fileName;

	function
	__construct (&$builder, $fileName = null)
	{
		$this->_builder =& $builder;
		$this->setFileName ($fileName);
	}

	function
	setFileName ($fileName)
	{
		$this->_fileName = $fileName;
	}

	function
	parse ()
	{
		if ($f = fopen ($this->_fileName, 'r'))
		{
			while (! feof ($f))
			{
				$pos = ftell ($f);

				$line = fgets ($f, 1025);

				// test for CRLF vs. LF
				if (substr ($line, -2, 1) == "\r")
					$this->_builder->handleLine (substr ($line, 0, -2), $pos);
				else
					$this->_builder->handleLine (substr ($line, 0, -1), $pos);
			}

			fclose ($f);
		}
	}

	function
	getMimeEntity ()
	{
		return $this->_builder->getBuiltObject ();
	}
}

?>
