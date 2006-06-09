<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

/**
 * File parser and copier for internet messages.
 *
 * Basically, it reads lines from a file, copies those lines to a destination
 * file and hands those lines off to a LineBuilder.  See our parant class.
 *
 * @package MIMESIS_PARSE
 */
class
MimeFileLineParsingCopier
extends
MimeFileLineParser
{
	/**
	 * "Destination" file name.
	 *
	 * @var string
	 * @access private
	 */
	var $_copyFileName;

	/**
	 * @param LineBuilder Builder ojbect
	 * @param string Source file name
	 * @param string Destination file name
	 */
	function
	MimeFileLineParsingCopier (&$builder, $srcFileName = null, $dstFileName = null)
	{
		parent::MimeFileLineParser ($builder, $srcFileName);
		$this->setCopyFileName ($dstFileName);
	}

	/**
	 * @param string Destination file name
	 */
	function
	setCopyFileName ($filename)
	{
		$this->_copyFileName = $filename;
	}

	/**
	 * Parse lines of file, copy to desitnation, and pass to our LineBuilder.
	 *
	 * @see MimeFileLineParser::parse
	 */
	function
	parse ()
	{
		if ($src_f = fopen ($this->_fileName, 'r'))
		{
			if ($dst_f = fopen ($this->_copyFileName, 'w'))
			{
				while (! feof ($src_f))
				{
					$pos = ftell ($src_f);

					fwrite ($dst_f, $line = fgets ($src_f, 1025));

					// test for CRLF vs. LF
					if (substr ($line, -2, 1) == "\r")
						$this->_builder->handleLine (substr ($line, 0, -2), $pos);
					else
						$this->_builder->handleLine (substr ($line, 0, -1), $pos);
				}

				fclose ($dst_f);
			}

			fclose ($src_f);
		}
	}
}

?>
