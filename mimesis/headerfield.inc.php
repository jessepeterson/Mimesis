<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

/** */
require_once ('mimesis/parsetok.php');

/**
 * Parent class for MIME header fields.
 *
 * Unlikley to be used directly, its descendents are probably of better use.
 *
 * @package MIMESIS_HEADER
 */
class
MimeHeaderField
{
	/**
	 * @var string
	 * @access private
	 */
	var $_name = null;

	/** @param string */
	function
	setName ($name)
	{
		$this->_name = $name;
	}

	/** @return string */
	function
	getName ()
	{
		if (! empty ($this->_name))
			return $this->_name;
	}

	/**
	 * Retrives the 'assembled' header field body as it might
	 * appear in a raw RFC 822/2046 message.
	 *
	 * @return string Header field body
	 */
	function
	getBody ()
	{
		die ('MimeHeaderField::getBody method must be overridden');
	}

	/**
	 * Parses header field body into component peices of
	 * or arguments of header field.
	 *
	 * @param string $body Header field body text
	 */
	function
	parseBody ()
	{
		die ('MimeHeaderField::parseBody method must be overridden');
	}

	/**
	 * Create and return a MimeHeaderField object based off the name of
	 * a header field.
	 *
	 * @return MimeHeaderField
	 */
	function
	&nameFactory ($headerFieldName)
	{
		switch (strtolower ($headerFieldName))
		{
		case 'content-type':
			$h = new ContentTypeHeaderField;
			return $h;
		case 'to':
		case 'cc':
		case 'bcc':
		case 'from':
		case 'reply-to':
			$h =& new AddressListHeaderField;
			return $h;
		case 'content-disposition':
			$h =& new ContentDispositionHeaderField;
			return $h;
		default:
			$h =& new UnstructuredMimeHeaderField;
			return $h;
		}
	}
}

/**
 * MIME/RFC (2)822 header field that supports 'raw' unstructured header field
 * bodies.
 *
 * @package MIMESIS_HEADER
 */
class
UnstructuredMimeHeaderField
extends
MimeHeaderField
{
	/**
	 * @var string
	 * @access private
	 */
	var $_body;

	/**
	 * Retrives the 'assembled' header field body as it might
	 * appear in a raw RFC 822/2046 message.
	 *
	 * @return string Header field body
	 */
	function
	getBody ()
	{
		return $this->_body;
	}

	/**
	 * See overridden method.
	 *
	 * @param string
	 */
	function
	parseBody ($body)
	{
		$this->_body = $body;
	}
}

/**
 * MIME Content-type header field.
 *
 * Supports MIME parsing and dealing with MIME types and sub-types as well as
 * parameters.
 *
 * @package MIMESIS_HEADER
 */
class
ContentTypeHeaderField
extends
MimeHeaderField
{
	/**
	 * @var string
	 * @access private
	 */
	var $_name = 'Content-Type';

	/**
	 * MIME Content-type Type.
	 *
	 * @var string
	 * @access private
	 */
	var $_type;

	/**
	 * MIME Content-type Sub-Type.
	 *
	 * @var string
	 * @access private
	 */
	var $_subtype;

	/**
	 * MIME Content-type parameters.
	 *
	 * @see setParam
	 * @see getParam
	 * @access private
	 */
	var $_params = array ();

	/** @param string MIME type */
	function
	setType ($type)
	{
		$this->_type = $type;
	}
	
	/** @param string MIME sub-type */
	function
	setSubType ($type)
	{
		$this->_subtype = $type;
	}

	/** @return string MIME type */
	function
	getType ()
	{
		if (! empty ($this->_type))
			return $this->_type;
		else
			return 'text';
	}

	/**
	 * @return string MIME sub-type
	 */
	function
	getSubType ()
	{
		if (! empty ($this->_type))
			return $this->_subtype;
		else
			return 'plain';
	}

	/**
	 * @todo Implement case preservation of parameter names.
	 * @param string
	 * @param string
	 */
	function
	setParam ($name, $value)
	{
		$this->_params[strtolower ($name)] = $value;
	}

	/**
	 * @param string
	 * @return string Parameter value
	 */
	function
	getParam ($name)
	{
		if (isset ($this->_params[strtolower ($name)]))
			return $this->_params[strtolower ($name)];
	}

	/** @implements StructuredMimeHeaderField::getBody
	* @todo quote/etc. escaping of params
	*/
	function
	getBody ()
	{
		$body = ' ' .  $this->getType () .  '/' .  $this->getSubType ();

		foreach ($this->_params as $param => $value)
		{
			$body .= '; ' . $param . '="' . $value . '"';
		}

		return $body;
	}

	/**
	 * Parse type, sub-type, and parameters from Content-type body string.
	 *
	 * @implements StructuredMimeHeaderField::parseBody
	 * @param string
	 */
	function
	parseBody ($body)
	{
		$tokens = filter822tokens (
			tokenize2045 ($body),
			TOKEN_ALL ^ TOKEN_WHITE_SPACE ^ TOKEN_COMMENT
			);

		$this->setType ($tokens[0]['string']);
		$this->setSubType ($tokens[2]['string']);

		// parse parameters
		for ($i = 4; $i < count ($tokens); $i += 4)
		{
			$this->setParam ($tokens[$i]['string'],
			                 $tokens[$i + 2]['string']);
		}
	}

	/**
	 * Returns true if this Content-type designates a Multi-part MIME message.
	 *
	 * Note that this doesn't consult and entities or bodies.  We're simply
	 * reporting what this Content-type header field says.
	 *
	 * @return bool
	 */
	function
	isMultipart ()
	{
		if ('multipart' == strtolower ($this->_type))
			return true;
	}

	/**
	 * Returns true if this Content-type designates a message component
	 * (AKA a Content-type of "message").
	 *
	 * Note that this doesn't consult and entities or bodies.  We're simply
	 * reporting what this Content-type header field says.
	 *
	 * @return bool
	 */
	function
	isMessage ()
	{
		if ('message' == strtolower ($this->_type))
			return true;
	}
}

/**
 * Header fields supporting the "address-list", "mailbox-list" (and other
 * sub/related ABNF rules).
 *
 * Examples include the To, Cc, and Bcc header fields.
 *
 * @todo Address-list "group" support
 * @package MIMESIS_HEADER
 */
class
AddressListHeaderField
extends
MimeHeaderField
{
	/**
	 * Array of Mailboxes.
	 *
	 * @see Mailbox
	 * @var array
	 * @access private
	 */
	var $_mailboxes = array ();

	/**
	 */
	function
	getBody ()
	{
	}

	/**
	 * See overridden method.
	 *
	 * @param string
	 */
	function
	parseBody ($body)
	{
		// parse body into (2)822 tokens removing white-space and comments
		$toks = filter822tokens (
			tokenize822 ($body),
			TOKEN_ALL ^ TOKEN_WHITE_SPACE ^ TOKEN_COMMENT
			);

		$addr_toks = array (array ());
		$addr_toks_pos = 0;
		$mailbox_toks = array ();

		foreach ($toks as $tok)
		{
			/**
			if (TOKEN_SPECIAL == $tok['type'] and
			    ':' == $tok['string'])
			{
			}
			elseif ($group and
			        TOKEN_SPECIAL == $tok['type'] and
			        ';' == $tok['string'])
			{
			}
			 */

			if (isset ($tok['type']) and TOKEN_SPECIAL == $tok['type'] and
			    ',' == $tok['string'])
			{
				$this->_mailboxes[] =& $this->_parseMailboxToks ($mailbox_toks);
				$mailbox_toks = array ();
			}
			else
			{
				$mailbox_toks[] = $tok;
			}
		}

		if (count ($mailbox_toks))
			$this->_mailboxes[] =& $this->_parseMailboxToks ($mailbox_toks);
	}

	/**
	 * @param array
	 * @return Mailbox
	 * @access private
	 */
	function
	&_parseMailboxToks (&$toks)
	{
		require_once ('mimesis/mailbox.inc.php');

		$mailbox =& new Mailbox;

		foreach ($toks as $tok)
		{
			if (isset ($tok['type']) and TOKEN_SPECIAL == $tok['type'] and
			    '<' == $tok['string'])
			{
				$in_angle_br = true;

				if (count ($cur_strings))
				{
					$mailbox->setDisplayName (implode (' ', $cur_strings));
					$cur_strings = array ();
				}
			}
			elseif (isset ($tok['type']) and TOKEN_SPECIAL == $tok['type'] and
			    '@' == $tok['string'])
			{
				$mailbox->setLocalPart (implode (null, $cur_strings));
				$cur_strings = array ();
			}
			elseif (isset ($tok['type']) and TOKEN_SPECIAL == $tok['type'] and
			    '>' == $tok['string'])
			{
				break;
			}
			else
			{
				$cur_strings[] = $tok['string'];
			}
		}
		$mailbox->setDomain (implode (null, $cur_strings));

		return $mailbox;
	}
}

/**
 * RFC 2183 Content-Disposition header field.
 *
 * @package MIMESIS_HEADER
 */
class
ContentDispositionHeaderField
extends
MimeHeaderField
{
	/**
	 * @var string
	 * @access private
	 */
	var $_name = 'Content-Disposition';

	/**
	 * Disposition type.
	 *
	 * @var string
	 * @access private
	 */
	var $_type;

	/**
	 * Disposition-type parameters.
	 *
	 * @see setParam
	 * @see getParam
	 * @access private
	 */
	var $_params = array ();

	/** @param string MIME type */
	function
	setType ($type)
	{
		$this->_type = $type;
	}
	
	/** @return string MIME type */
	function
	getType ()
	{
		if (! empty ($this->_type))
			return $this->_type;
		else
			return 'text';
	}

	/**
	 * @todo Implement case preservation of parameter names.
	 * @param string
	 * @param string
	 */
	function
	setParam ($name, $value)
	{
		$this->_params[strtolower ($name)] = $value;
	}

	/**
	 * @param string
	 * @return string Parameter value
	 */
	function
	getParam ($name)
	{
		if (isset ($this->_params[strtolower ($name)]))
			return $this->_params[strtolower ($name)];
	}

	/** @implements StructuredMimeHeaderField::getBody
	* @todo quote/etc. escaping of params
	*/
	function
	getBody ()
	{
		$body = ' ' .  $this->getType ();

		foreach ($this->_params as $param => $value)
		{
			$body .= '; ' . $param . '="' . $value . '"';
		}

		return $body;
	}

	/**
	 * Parse type, sub-type, and parameters from Content-type body string.
	 *
	 * @implements StructuredMimeHeaderField::parseBody
	 * @param string
	 */
	function
	parseBody ($body)
	{
		$tokens = filter822tokens (
			tokenize2045 ($body),
			TOKEN_ALL ^ TOKEN_WHITE_SPACE ^ TOKEN_COMMENT
			);

		$this->setType ($tokens[0]['string']);

		// parse parameters
		for ($i = 2; $i < count ($tokens); $i += 4)
		{
			$this->setParam ($tokens[$i]['string'],
			                 $tokens[$i + 2]['string']);
		}
	}
}

?>
