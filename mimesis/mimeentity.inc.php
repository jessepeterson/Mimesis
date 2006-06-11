<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 * @package MIMESIS_ENTITY
 */

/** @see RefArrayIterator */
require_once ('mimesis/refarrayiterator.inc.php');

/**
 * PHP OOP representation of an RFC 2045 and RFC 2046 MIME entity (RFC
 * 2045 section 2.4).
 *
 * As an overview, MIME messages are MIME entities which have a header
 * composed of header fields and a body that may include embedded
 * MIME entities making a MIME message possibly heirarchially
 * recursive.  Please refer to the related MIME RFCs beginning with
 * RFC 2045.
 *
 * @link http://www.ietf.org/rfc/rfc2045.txt
 * @package MIMESIS_ENTITY
 */
class
MimeEntity
{
	/**
	 * Array of MIME header fields.
	 * 
	 * @var array
	 * @see MimeHeaderField
	 * @access private
	 */
	var $_headerFields = array ();

	/**
	 * Array of components, sub-parts, or embedded entities.
	 *
	 * The nature and relationship of such embedded entities is
	 * typically defined by the entity header's Content-type header
	 * field.
	 *
	 * @var array
	 * @see MimeEntity
	 * @access private
	 */
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
	 * Return first case-insensitively matching header field that
	 * matches the supplied header field name.
	 *
	 * @param string Header field name
	 * @return MimeHeaderField
	 */
	function
	&getHeaderFieldByName ($name)
	{
		foreach (array_keys ($this->_headerFields) as $hdr_key)
		{
			if (strtolower ($this->_headerFields[$hdr_key]->getName ()) == strtolower ($name))
				return $this->_headerFields[$hdr_key];
		}
	}

	/**
	 * Test for the existence of a header field specified by header
	 * field name.
	 *
	 * @param string Header field name
	 * @return boolean
	 */
	function
	headerFieldExists ($name)
	{
		foreach (array_keys ($this->_headerFields) as $hdr_key)
		{
			if (strtolower ($this->_headerFields[$hdr_key]->getName ()) == strtolower ($name))
				return true;
		}

		return false;
	}

	/**
	 * Add child entity to this entity for support of composite
	 * MIME entities such as multipart and message types.
	 *
	 * The nature and relationship of such embedded entities is
	 * typically defined by the entity header's Content-type header
	 * field.
	 *
	 * @param MimeEntity
	 */
	function
	addComponent (&$mimeEntity)
	{
		$this->_components[] =& $mimeEntity;
	}

	/**
	 * Return true if we are a composite entity.
	 *
	 * @return boolean
	 */
	function
	isComposite ()
	{
		if (count ($this->_components))
			return true;
	}

	/**
	 * Return iterator of component MIME entities.
	 *
	 * @return Iterator
	 */
	function
	&getComponentIterator ()
	{
		$iterator =& new RefArrayIterator;
		$iterator->setRefArray ($this->_components);

		return $iterator;
	}

	/**
	 * Return iterator of header field objects.
	 *
	 * @return Iterator
	 */
	function
	&getHeaderFieldIterator ()
	{
		$iterator =& new RefArrayIterator;
		$iterator->setRefArray ($this->_headerFields);

		return $iterator;
	}

	/**
	 * Helper/utility function: Return (possibly guessed) MIME
	 * type of body.
	 *
	 * If no Content-type header exists and we're not a
	 * composite we'll return 'text'.  If we're a composite
	 * and no Content-type header exists we'll return 'multipart'.
	 * If a Content-type header exists we'll simply use the type
	 * from that.
	 *
	 * @return string
	 */
	function
	getType ()
	{
		$contentTypeHdr =& $this->getHeaderFieldByName ('content-type');

		if (is_object ($contentTypeHdr))
		{
			return $contentTypeHdr->getType ();
		}
		else
		{
			if ($this->isComposite ())
				return 'multipart';
			else
				return 'text';
		}
	}

	/**
	 * Helper/utility function: Return (possibly guessed) MIME
	 * sub-type of body.
	 *
	 * If no Content-type header exists and we're not a
	 * composite we'll return 'plain'.  If we're a composite
	 * and no Content-type header exists we'll return 'mixed'.
	 * If a Content-type header exists we'll simply use the
	 * sub-type from that.
	 *
	 * @see getType
	 * @return string
	 */
	function
	getSubType ()
	{
		$contentTypeHdr =& $this->getHeaderFieldByName ('content-type');

		if (is_object ($contentTypeHdr))
		{
			return $contentTypeHdr->getSubType ();
		}
		else
		{
			// if we're composite 'guess' multipart/mixed type
			if ($this->isComposite ())
				return 'mixed';
			else
				return 'plain';
		}
	}
}

?>
