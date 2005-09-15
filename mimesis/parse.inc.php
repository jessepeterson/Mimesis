<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

/**
 * File parser for internet messages.  Basically reads lines from a
 * file and passes lines to a LineBuilder.
 *
 * Only "special" handling this FileLineParser has is that internet
 * messages are restricted to 998-byte long lines (1000 minus CRLF
 * pair) as specified by RFCs 822/2822.
 *
 * Generally used with a MimeEntityBuilderLineBuilder.
 *
 * @package MIMESIS_PARSE
 * @see MimeEntityBuilderLineBuilder
 */
class
MimeFileLineParser
{
	/**
	 * @var MimeEntityBuilderLineBuilder
	 * @access private
	 */
	var $_builder;

	/**
	 * @var string
	 * @access private
	 */
	var $_fileName;

	/**
	 * @param LineBuilder Builder object
	 * @param string File name to parse
	 */
	function
	MimeFileLineParser (&$builder, $fileName = null)
	{
		$this->_builder =& $builder;
		$this->setFileName ($fileName);
	}

	/**
	 * Set filename to open and parse for lines.
	 *
	 * @param string
	 */
	function
	setFileName ($fileName)
	{
		$this->_fileName = $fileName;
	}

	/**
	 * Parse lines of file and pass to our LineBuilder.
	 *
	 * Please be aware of the limitations that the PHP built-in
	 * function fgets() has.  It is passed an argument of 1025
	 * (the default for a previous version of PHP that also
	 * happens to be a good number for internet message line
	 * lengths).  If your file has lines that are larger than
	 * this you will probably get unexpected results.
	 */
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

	/**
	 * Return our completed and built MimeEntity object!
	 *
	 * @return MimeEntity
	 */
	function
	&getMimeEntity ()
	{
		return $this->_builder->getBuiltObject ();
	}
}

?>
