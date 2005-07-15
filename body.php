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
	 * @var BodyContent
	 */
	var $_bodyContent;

	/**
	 * When parsing use this factory to create body content for specialized
	 * locations of body content data (such as mid-file for a single MIME
	 * message for example).
	 *
	 * @access private
	 * @var object
	 */
	var $_bodyContentFactory;

	/**
	 * @param object $factory
	 */
	function
	setParseBodyContentFactory (&$factory)
	{
		$this->_bodyContentFactory =& $factory;
	}

	/**
	 * @return object
	 */
	function
	&getParseBodyContentFactory ()
	{
		return $this->_bodyContentFactory;
	}

	/**
	 * @return BodyContent
	 */
	function
	&getBodyContent ()
	{
		return $this->_bodyContent;
	}

	/**
	 * @param BodyContent $bodyContent Body content object.
	 */
	function
	setBodyContent (&$bodyContent)
	{
		$this->_bodyContent =& $bodyContent;
	}

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
		$content = $this->getBodyContent ();
		if (! is_object ($content))
		{
			$bodyContentFactory = $this->getParseBodyContentFactory ();

			if (is_object ($bodyContentFactory))
			{
				$this->setBodyContent (
					$bodyContent =& $bodyContentFactory->factory ());

				$bodyContent->_parseBodyLineFromRef ($line, $pos);
			}
			/*
			else
				var_dump ('MimeEntityBody::_parseBodyLineFromRef - no body content exists and no body content factory object exists');
			 */

			/*
			XXX can throw away if not needed..

			$this->setBodyContent ($this->get
			$this->setBodyContent ($content =& new PartialFileBodyContent);
			$content->_position = $pos;
			 */
		}		 	
		else
		{
			$content->_parseBodyLineFromRef ($line, $pos);
		}
	}

	/**
	 */
	function
	_parseDone ()
	{
		$content =& $this->getBodyContent ();

		//if (is_object ($content))
			// $content->_parseDone ();
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

	function
	_parseDone ()
	{
		// last 'entity' in our composite body is to be ignored according to
		// RFCs 2045,2046
		unset (
			$this->_compositeEntities[count ($this->_compositeEntities) - 1]);
	}

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

			if (is_object ($this->_curCompositeEntity))
				$this->_curCompositeEntity->_parseDone ();

			unset ($this->_curCompositeEntity);
			$this->_curCompositeEntity =& new MimeEntity;

			$this->_curCompositeEntity->setParseBodyContentFactory (
				$this->getParseBodyContentFactory ());

			$this->addCompositeEntity ($this->_curCompositeEntity);
		}
		else
		{
			// non-boundary line found

			if (is_object ($this->_curCompositeEntity))
				$this->_curCompositeEntity->_parseRawLineFromRef ($line, $pos);
		}
	}

	/**
	 * @return array Reference to array of sub-entities.
	 */
	function
	&getEntities ()
	{
		return $this->_compositeEntities;
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

	/**
	 * kludgy hack!
	 *
	 * @access private
	 */
	var $_haveAssignedContentFactory;

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
		if (empty ($this->_haveAssignedContentFactory))
		{
			$this->_entity->setParseBodyContentFactory (
				$this->getParseBodyContentFactory ());

			$this->_haveAssignedContentFactory = true;
		}

		$this->_entity->_parseRawLineFromRef ($line, $pos);
	}

	/**
	 * @return MimeEntity
	 */
	function
	&getEntity ()
	{
		return $this->_entity;
	}

	function
	_parseDone ()
	{
		$this->_entity->_parseDone ();
	}
}

/**
 * @package MIMESIS_BODY
 */
class
BodyContent
{
}

/**
 * @package MIMESIS_BODY
 */
class
PartialFileBodyContent
extends
BodyContent
{
	/**
	 * @access private
	 */
	var $_fileName = null;

	/**
	 * @access private
	 */
	var $_position = -1;

	/**
	 * @access private
	 */
	var $_length;

	/**
	 * @param string $fileName
	 */
	function
	setFileName ($fileName)
	{
		$this->_fileName = $fileName;
	}

	/**
	 * @return string
	 */
	function
	getFileName ()
	{
		return $this->_fileName;
	}

	/**
	 * @param string $line
	 * @param integer $pos
	 */
	function
	_parseBodyLineFromRef (&$line, $pos)
	{
		if ($this->_position == -1)
			$this->_position = $pos;

		$this->_length += strlen ($line) + 1;
	}
}

/**
 * @package MIMESIS_BODY
 */
class
SingleFileBodyContentFactory
{
	/**
	 * @access private
	 */
	var $_fileName = null;

	/**
	 * @return BodyContent
	 */
	function
	&factory ()
	{
		$bodyContent =& new PartialFileBodyContent;
		$bodyContent->setFileName ($this->getFileName ());

		return $bodyContent;
	}

	/**
	 * @param string $fileName
	 */
	function
	setFileName ($fileName)
	{
		$this->_fileName = $fileName;
	}

	/**
	 * @return string
	 */
	function
	getFileName ()
	{
		return $this->_fileName;
	}
}

?>
