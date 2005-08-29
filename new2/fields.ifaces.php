<?php

require_once ('822tok.php');

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
		default:
			return new UnstructuredMimeHeaderField;
		}
	}
}

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

	function
	setType ($type)
	{
		$this->_type = $type;
	}
	
	function
	setSubType ($type)
	{
		$this->_subtype = $type;
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

?>
