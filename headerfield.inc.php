<?php

require_once ('parsetok.php');

/**
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
		die ('StructuredMimeHeaderField::getBody method must be overridden');
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
		die ('StructuredMimeHeaderField::parseBody method must be overridden');
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
			return new ContentTypeHeaderField;
		case 'to':
		case 'cc':
		case 'bcc':
		case 'from':
		case 'reply-to':
			return new AddressListHeaderField;
		default:
			return new UnstructuredMimeHeaderField;
		}
	}
}

/**
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
		return $this->_body;
	}

	/**
	 * Parses header field body into component peices of
	 * or arguments of header field.
	 *
	 * @param string $body Header field body text
	 */
	function
	parseBody ($body)
	{
		$this->_body = $body;
	}
}

/**
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

	/** @access private */
	var $_type;

	/** @access private */
	var $_subtype;

	/** @access private */
	var $_params;

	/**
	 * @param string MIME type
	 */
	function
	setType ($type)
	{
		$this->_type = $type;
	}
	
	/**
	 * @param string MIME sub-type
	 */
	function
	setSubType ($type)
	{
		$this->_subtype = $type;
	}

	/**
	 * @return string MIME type
	 */
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
	 * XXX: retain case preservation!!!!!!!!!!!!
	 */
	function
	setParam ($name, $value)
	{
		$this->_params[strtolower ($name)] = $value;
	}

	function
	getParam ($name)
	{
		if (isset ($this->_params[strtolower ($name)]))
			return $this->_params[strtolower ($name)];
	}

	/** @implements StructuredMimeHeaderField::getBody */
	function
	getBody ()
	{
		die ('StructuredMimeHeaderField::getBody method must be overridden');
	}

	/** @implements StructuredMimeHeaderField::parseBody */
	function
	parseBody ($body)
	{
		$tokens = filter822tokens (
			tokenize2045 ($body),
			TOKEN_ALL ^ TOKEN_WHITE_SPACE ^ TOKEN_COMMENT
			);

		$this->setType ($tokens[0]['string']);
		$this->setSubType ($tokens[2]['string']);

		for ($i = 4; $i < count ($tokens); $i += 4)
		{
			$this->setParam ($tokens[$i]['string'],
			                 $tokens[$i + 2]['string']);
		}
	}

	function
	isMultipart ()
	{
		if ('multipart' == strtolower ($this->_type))
			return true;
	}

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
	 * @var array
	 * @access private
	 */
	var $_mailboxes = array ();

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

			if (TOKEN_SPECIAL == $tok['type'] and
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
		require_once ('mailbox.inc.php');

		$mailbox =& new Mailbox;

		foreach ($toks as $tok)
		{
			if (TOKEN_SPECIAL == $tok['type'] and
			    '<' == $tok['string'])
			{
				$in_angle_br = true;

				if (count ($cur_strings))
				{
					$mailbox->setDisplayName (implode (' ', $cur_strings));
					$cur_strings = array ();
				}
			}
			elseif (TOKEN_SPECIAL == $tok['type'] and
			    '@' == $tok['string'])
			{
				$mailbox->setLocalPart (implode (null, $cur_strings));
				$cur_strings = array ();
			}
			elseif (TOKEN_SPECIAL == $tok['type'] and
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

?>
