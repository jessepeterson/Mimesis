<?php

/**
 * MIME entity.
 *
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 * @package MIMESIS_ENTITY
 */

/**
 * @package MIMESIS_ENTITY
 */
class
MimeEntity
{
	/**
	 * Array of header fields/header field objects.
	 *
	 * @access private
	 * @var array
	 */
	var $_headerFields = array ();

	/**
	 * @access private
	 * @var MimeEntityBody
	 */
	var $_body = null;

	/**
	 * While parsing raw chunks of messages, this may contain left-over
	 * portions of lines.
	 *
	 * @access private
	 * @var string
	 */
	var $_rawChunkLeftOver = null;

	/**
	 * Tracking of current position within raw message chunk parsing.
	 *
	 * @access private
	 * @var integer
	 */
	var $_rawChunkPosition = 0;

	/**
	 * End of header/start of body has been found/reached for this entity.
	 * 
	 * @access private
	 * @var bool
	 */
	var $_eoh = false;

	/**
	 * While parsing header fields in a header, this holds the a reference to
	 * the current header field while folding/unfolding is performed.
	 *
	 * @access private
	 * @var MimeHeaderField
	 */
	var $_currentHeaderField;

	/**
	 * @return MimeEntityBody
	 */
	function
	&getBody ()
	{
		if (is_object ($this->_body))
			return $this->_body;
	}

	/**
	 * @param MimeEntityBody $body
	 */
	function
	setBody (&$body)
	{
		$this->_body =& $body;
	}

	/**
	 * Parse raw section of a MIME message passed by reference.
	 *
	 * Main chunk-to-line parsing functionality.  General algorithm is to
	 * parse into LF-terminated lines and pass on to line-parsing functions.
	 *
	 * @access private
	 * @param string $chunk MIME message chunk/section.
	 */
	function
	_parseRawChunkFromRef (&$chunk)
	{
		$last_lf_pos = 0;

		// loop for LF chars in chunk marking positions of LFs
		for ($lf_pos = strpos ($chunk, "\n");
		     $lf_pos !== false;
		     $lf_pos = strpos ($chunk, "\n", $lf_pos + 1))
		{
			if (! empty ($this->_rawChunkLeftOver))
			{
				// use raw left-over chunk to construct this line
				$this->_parseRawLine (
					$this->_rawChunkLeftOver .
						substr ($chunk, $last_lf_pos, $lf_pos - $last_lf_pos),
					$this->_rawChunkPosition -
						strlen ($this->_rawChunkLeftOver)
					);
			
				// we've used our current left-over, now clear it
				$this->_rawChunkLeftOver = null;
			}
			else
			{
				// found line, parse it
				$this->_parseRawLine (
					substr ($chunk, $last_lf_pos, $lf_pos - $last_lf_pos),
					$this->_rawChunkPosition + $last_lf_pos
					);
			}

			// adjust our next line-usage position for last-found LF
			$last_lf_pos = $lf_pos + 1;
		}

		// store any left-over chunk text for subsequent calls
		if (! empty ($this->_rawChunkLeftOver))
		{
			// use previous left-overs in this left-over
			$this->_rawChunkLeftOver =
				$this->_rawChunkLeftOver .
				substr ($chunk, $last_lf_pos, strlen ($chunk));
		}
		else
		{
			$this->_rawChunkLeftOver =
				substr ($chunk, $last_lf_pos, strlen ($chunk));
		}

		// TODO: parse any left-over text if our "EOF" has been reached: need
		//       to detemine how to signal our "EOF"
		/*
		$this->_parseRawLine (
			$this->_rawChunkLeftOver,
			$this->_rawChunkPosition - strlen ($this->_rawChunkLeftOver));
		$this->_rawChunkLeftOver = null;
		 */

		$this->_rawChunkPosition += strlen ($chunk);
	}

	/**
	 * @access private
	 * @param string $line Line to parse
	 * @param integer $pos Byte position within currently parsing file/chunk
	 */
	function
	_parseRawLine ($line, $pos)
	{
		$this->_parseRawLineFromRef ($line, $pos);
	}

	/**
	 * Parse lines into header and body and pass lines to header/body parsers.
	 *
	 * @access private
	 * @param string $line Line to parse
	 * @param integer $pos Byte position within currently parsing file/chunk
	 */
	function
	_parseRawLineFromRef (&$line, $pos)
	{
		if ($this->_eoh)
		{
			// end of header/start of body reached
			if (is_object ($this->_body))
				$this->_body->_parseBodyLineFromRef ($line, $pos);
		}
		else
		{
			// still parsing header
			$this->_parseHeaderLine ($line);
		}
	}

	/**
	 * Parse header lines, performing folding/unfolding tasks as well as
	 * detecting the end of the header.
	 *
	 * @access private
	 * @param string $line Header line to parse.
	 */
	function
	_parseHeaderLine (&$line)
	{
		if ($this->_eoh)
		{
			return;
		}
		elseif ($line === "" or
		        $line === "\r")
		{
			// end of header/start of body found (first blank line)

			if (is_object ($this->_currentHeaderField))
			{
				// have we already started a header field?  commit it.
				$this->addHeaderField ($this->_currentHeaderField);
				unset ($this->_currentHeaderField);
			}

			// create our body object
			$this->_body =& MimeEntityBody::entityFactory ($this);

			$this->_eoh = true;
		}
		elseif (preg_match ('/^\s/', $line{0}))
		{
			// likley, a folded header field portion found

			$this->_currentHeaderField->appendText ($line);
		}
		elseif (preg_match ('/^([\x21-\x39\x3b-\x7e]*)\x3a(.*)$/',
		                    $line,
		                    $header_field))
		{
			// header field found

			if (is_object ($this->_currentHeaderField))
			{
				// have we already started a header field?  commit it.
				$this->addHeaderField ($this->_currentHeaderField);
				unset ($this->_currentHeaderField);
			}

			// create our current MimeHeaderField based off of the field name
			$this->_currentHeaderField =&
				MimeHeaderField::nameFactory ($header_field[1]);

			// set the (possibly beginning) header field text
			$this->_currentHeaderField->setText ($header_field[2]);
		}
	}

	/**
	 * Add a header field to entity.
	 *
	 * @param MimeHeaderField $headerField Header field to add to entity.
	 */
	function
	addHeaderField (&$headerField)
	{
		$this->_headerFields[] =& $headerField;
	}

	/**
	 * Get header field by field name.  Assumes there is only one header field
	 * with a single name.  Matches field names case-insensitively.
	 *
	 * @param string $name Header field name.
	 * @return MimeHeaderField Header field with matching name.
	 */
	function
	&getHeaderFieldByName ($name)
	{
		foreach (array_keys ($this->_headerFields) as $key)
		{
			if (strtolower ($this->_headerFields[$key]->getName ()) ==
			        strtolower ($name))
			{
				return $this->_headerFields[$key];
			}
		}
	}
}

?>
