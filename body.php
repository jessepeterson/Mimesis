<?php

/**
 * MIME entity body.
 *
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 * @package MIMESIS_BODY
 */

/**
 * @package MIMESIS_BODY
 */
class
MimeEntityBody
{
	/**
	 * @param MimeEntity $entity
	 * @return MimeEntityBody
	 */
	function
	&entityFactory (&$entity)
	{
		$cTypeHdrFld =& $entity->getHeaderFieldByName ('content-type');

		// XXX: yuck.
		if (! is_object ($cTypeHdrFld))
			return new MimeEntityBody;

		switch (strtolower ($cTypeHdrFld->getType ()))
		{
		case 'multipart':
			$body =& new CompositeBody;
			$body->setCompositeBoundary (
				$cTypeHdrFld->getParameter ('boundary'));
			return $body;
		case 'message':
			return new MimeEntityEntityBody;
		default:
			return new MimeEntityBody;
		}
	}

	/**
	 */
	function
	_parseBodyLineFromRef (&$line, $pos)
	{
	}
}

/**
 * @package MIMESIS_BODY
 */
class
CompositeBody
extends
MimeEntityBody
{
	/**
	 * MIME multipart boundary string (w/o preceeding dashes).
	 *
	 * @access private
	 * @var string
	 */
	var $_compositeBoundary = null;

	/**
	 * Array of references to composite parts (other entities).
	 *
	 * @access private
	 * @var array
	 */
	var $_compositeEntities = array ();

	/**
	 * Currently parsing entity
	 *
	 * @access private
	 * @var MimeEntity
	 */
	var $_curCompositeEntity;

	/**
	 * Set MIME composite (multipart) boundary string.
	 *
	 * @param string $boundary MIME multipart boundary string WITHOUT
	 *                         preceeding dashes ("--").
	 */
	function
	setCompositeBoundary ($boundary)
	{
		$this->_compositeBoundary = $boundary;
	}

	/**
	 * Return MIME composite (multipart) boundary string.
	 *
	 * @return string MIME multipart boundary string.
	 */
	function
	getCompositeBoundary ()
	{
		if (! empty ($this->_compositeBoundary))
			return $this->_compositeBoundary;
	}

	/**
	 * Add reference to another MimeEntity to our sub-parts.  If no previous
	 * composite entities exist for this entity, this effectively turns this
	 * entity into a composite (multipart) entity.
	 *
	 * TODO: considerations: define default multipart type?  boundary issues?
	 *       type-check param? make default boundary if one not present/first
	 *       composite type?
	 *
	 * @param MimeEntity $entity Reference to entity.
	 */
	function
	addCompositeEntity (&$entity)
	{
		$this->_compositeEntities[] =& $entity;
	}

	/**
	 * @param string $line
	 * @param integer $pos
	 * @access private
	 */
	function
	_parseBodyLineFromRef (&$line, $pos)
	{
		// $boundary = $this->getCompositeBoundary ();
		if (strncmp ('--' . $boundary = $this->getCompositeBoundary (),
		             $line,
		             strlen ($boundary) + 2) === 0)
		{
			// boundary line found

			unset ($this->_curCompositeEntity);
			$this->_curCompositeEntity =& new MimeEntity;

			$this->addCompositeEntity ($this->_curCompositeEntity);
		}
		else
		{
			// non-boundary line found

			if (is_object ($this->_curCompositeEntity))
				$this->_curCompositeEntity->_parseRawLineFromRef ($line, $pos);
		}
	}
}

/**
 * @package MIMESIS_BODY
 */
class
MimeEntityEntityBody
extends
MimeEntityBody
{
	/**
	 * @access private
	 * @var MimeEntity
	 */
	var $_entity;

	function
	MimeEntityEntityBody ()
	{
		$this->_entity =& new MimeEntity;
	}

	/**
	 * @param string $line
	 * @param integer $pos
	 * @access private
	 */
	function
	_parseBodyLineFromRef (&$line, $pos)
	{
		$this->_entity->_parseRawLineFromRef ($line, $pos);
	}
}

?>
