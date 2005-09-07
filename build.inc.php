<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

/** @see MimeEntity */
require_once ('mimeentity.inc.php');

/**
 * Line-builder API driven MIME entity builder.
 *
 * Constructs MIME entities using a MimeEntityBuilder by handling
 * requests as a LineBuilder.
 *
 * Implements a LineBuilder-compatible interface.
 *
 * @package MIMESIS_BUILD
 */
class
MimeEntityBuilderLineBuilder
{
	/**
	 * @var MimeEntityBuilder
	 * @access private
	 */
	var $_builder;

	/**
	 * Current processing position within entity stack
	 *
	 * @var integer
	 * @access private
	 */
	var $_curEnt = 0;

	/**
	 * Total count of entities processed.
	 *
	 * @var integer
	 * @access private
	 */
	var $_entityCt = 0;

	/**
	 * Stack of MIME entities being processed.
	 *
	 * Heirarchially constructs MIME message from lines and this array
	 * keeps track of our current possition in the heirarchy and other
	 * information about this MimeEntity.
	 *
	 * Indice = description
	 *
	 * - [0]	entity number (for Builder's reference)
	 * - [1]	end of header
	 * - [2]	boundary text, if multipart
	 * - [3]	boolean, true if this is a message MIME-type
	 * - [4]	current count of found boundaries
	 *
	 * @var array
	 * @access private
	 */
	var $_entityStack = array ();

	/**
	 * Currently processing/parsing header field body.  Gets
	 * incrementally appended to to support folding and unfolding
	 * (RFC 2822 section 3.2.3).
	 *
	 * @var string
	 * @access private
	 */
	var $_headerFieldBody = null;

	/** 
	 * Currently processing header field name.
	 *
	 * @var string
	 * @access private
	 */
	var $_headerFieldName = null;

	/**
	 * @param MimeEntityBuilder
	 */
	function
	MimeEntityBuilderLineBuilder (&$builder)
	{
		$this->_builder =& $builder;

		// create root entity/entity 'marker'
		$this->_entityStack[$this->_curEnt] = array (
			0 => $this->_entityCt,
			1 => false,
			2 => null,
			3 => false,
			4 => 0
			);
	}

	/**
	 * Handle a MIME message line.
	 *
	 * Implements LineBuilder::handleLine interface method.
	 *
	 * @param string Line data
	 * @param integer Byte position of line in source.
	 */
	function
	handleLine ($line, $pos = null)
	{
		if ($this->_entityStack[$this->_curEnt][1])
		{
			// entity body line (end-of-header has been reached)

			// all boundary lines start with '-', test for it
			if ('-' == $line{0})
			{
				// loop through our entity stack to find boundaries
				for ($i = $this->_curEnt; $i >= 0; $i--)
				{
					if ($boundary =& $this->_entityStack[$i][2])
					{
						// test for boundary line
						if (0 === strpos ($line, '--' . $boundary))
						{
							// found boundary

							/* loop through our entity stack and remove
							   items which have been processed already */
							for ($j = $this->_curEnt; $j > $i; $j--)
							{
								array_pop ($this->_entityStack);
								$this->_curEnt--;
							}

							// test for non-end-of-boundary (aka, entity following)
							if ('--' != substr ($line, strlen ($boundary) + 2, 2))
							{
								// increment our found boundaries for $i entity
								$this->_entityStack[$i][4]++;

								// create new entity/entity 'marker'
								$this->_entityStack[++$this->_curEnt] = array (
									0 => ++$this->_entityCt,
									1 => false,
									2 => null,
									3 => false,
									4 => 0
									);

								// add new entity to our parent entity
								$this->_builder->addComponent (
									$this->_entityStack[$i][0],
									$this->_entityCt
									);
							}
							
							/* if we've found a boundary nothing
							 * else can happen with the line */
							return;
						}
					}
				}
			}

			// standard line - pass to builder for handline body text
			$this->_builder->handleBodyLineByRef (
				$this->_entityStack[$this->_curEnt][0],
				$line,
				$pos
				);

		}
		elseif (empty ($line))
		{
			// first blank line (end of header/start of body) has been found

			// mark this entity has having gotten past its header
			$this->_entityStack[$this->_curEnt][1] = true;

			// commit any previous header field we've been unfolding
			$this->_parseHeaderField ();

			// test if body is marked as being another message (content type of 'message')
			if ($this->_entityStack[$this->_curEnt][3])
			{
				// create new entity/entity 'marker'
				$this->_entityStack[++$this->_curEnt] = array (
					0 => ++$this->_entityCt,
					1 => false,
					2 => null,
					3 => false,
					4 => 0
					);

				// add our new entity to our parent entity
				$this->_builder->addComponent (
					$this->_entityCt - 1,
					$this->_entityCt
					);
			}
		}
		else
		{
			// header lines

			if (preg_match ('/^\s/', $line{0}))
			{
				// "folded" header line

				// add ourself to the unfolded line already existing
				$this->_headerFieldBody .= $line;
			}
			elseif (preg_match ('/^([\x21-\x39\x3b-\x7e]*)\x3a(.*)$/',
			                    $line,
			                    $header_field))
			{
				/* header field line - could possibly only be start of due to
				 * folding and unfolding */

				// commit any previous header field we've been unfolding
				$this->_parseHeaderField ();

				// make this line the current header field
				$this->_headerFieldName = $header_field[1];
				$this->_headerFieldBody = $header_field[2];
			}
			/* else {} - we're currently not handling non-header field lines in
			 * the header such as mbox-format message separators ("From ..."
			 * lines) */
		}

	}

	/**
	 * XXX: refactor me!
	 *
	 * @access private
	 */
	function
	_parseHeaderField ()
	{
		if (! empty ($this->_headerFieldName))
		{
			// do da do da $this->_headerField
			if (! empty ($this->_headerFieldBody))
			{
				$field =& MimeHeaderField::nameFactory ($this->_headerFieldName);

				$field->setName ($this->_headerFieldName);
				$field->parseBody ($this->_headerFieldBody);

				if ('content-type' ==
				    strtolower ($this->_headerFieldName))
				{
					if ($field->isMultipart ())
					{
						$this->_entityStack[$this->_curEnt][2] =
							$field->getParam ('boundary');
					}
					elseif ($field->isMessage ())
					{
						$this->_entityStack[$this->_curEnt][3] = true;
					}
				}

				// add our header field to our entity (builder)
				$this->_builder->handleHeaderField (
					$this->_entityStack[$this->_curEnt][0],
					$field
					);

				$this->_headerFieldBody = null;
				$this->_headerFieldName = null;
			}
		}
	}


	/**
	 * Return our built MimeEntity object!
	 *
	 * Implements LineBuilder::getBuiltObject interface method.
	 *
	 * @return MimeEntity
	 */
	function
	&getBuiltObject ()
	{
		return $this->_builder->getBuiltObject ();
	}
}

/**
 * MIME entity builder.
 *
 * Constructs MimeEntity objects through the methods of this classes
 * API methods.
 *
 * Core to operation is the concept of entity IDs.  Each entity you
 * work with is indexed by an entity ID.  For example, if you want to
 * add a header line, you need to know the entity ID of the entity you
 * want to add the header to.  The "root" element is ID zero (0).  When
 * you compose elements (to make MIME heirarchies) you must refer to
 * the parent and child elements by their IDs, as well.  Entity IDs are
 * integer numbers representing entities.
 *
 * @package MIMESIS_BUILD
 * @see MimeEntity
 */
class
MimeEntityBuilder
{
	/**
	 * Entities indexed by 
	 * @var array
	 * @access private
	 */
	var $_entities = array ();

	function
	MimeEntityBuilder ()
	{
		$this->_entities[0] =& new MimeEntity;
	}

	/**
	 * Add a component entity to a parent entity.
	 *
	 * Compose heirarchies of MIME entities by adding entities to
	 * other entities.  For example, Content-types of "multipart" and
	 * "message" are candidates for MIME heirarchies.
	 *
	 * @param integer "Parent" entity ID
	 * @param integer "Component" or "child" entity ID
	 */
	function
	addComponent ($parentEntityId, $componentEntityId)
	{
		$this->_assureIdExists ($parentEntityId);
		$this->_assureIdExists ($componentEntityId);

		$this->_entities[$parentEntityId]->addComponent (
			$this->_entities[$componentEntityId]
			);
	}

	/**
	 * Null-action body line handler.
	 *
	 * @param integer Entity ID
	 * @param string Line of body text
	 * @param integer Optional byte position from source
	 */
	function
	handleBodyLineByRef ($entityId, &$line, $pos = null)
	{
	}

	/**
	 * Null-action body line handler.
	 *
	 * @see handleBodyLineByRef
	 * @param integer
	 * @param string
	 * @param integer
	 */
	function
	handleBodyLine ($entityId, $line, $pos = null)
	{
		$this->_handleBodyLineByRef ($entityId, $line, $pos);
	}

	/**
	 * Assign a header field to an entity.
	 *
	 * Assign a header field (MimeHeaderField object) to this entity.
	 *
	 * @param integer Entity ID
	 * @param MimeHeaderField
	 */
	function
	handleHeaderField ($entityId, &$headerField)
	{
		$this->_assureIdExists ($entityId);
		$this->_entities[$entityId]->addHeaderField ($headerField);
	}

	/**
	 * Return our built MimeEntity object!
	 *
	 * @return MimeEntity
	 */
	function
	&getBuiltObject ()
	{
		return $this->_entities[0];
	}

	/**
	 * Make sure the supplied entity ID exists and create it if it
	 * does not.
	 *
	 * @param integer Entity ID
	 * @access private
	 */
	function
	_assureIdExists ($id)
	{
		if (! is_object ($this->_entities[$id]))
			$this->_entities[$id] =& new MimeEntity;
	}
}

/**
 * Build MIME messages and contain in memory including bodies.
 *
 * Care needs to be taken with large messages as large string handling
 * functions (including concatenation) and memory utilization by PHP
 * are of concern.
 *
 * @package MIMESIS_BUILD
 */
class
MemoryMimeEntityBuilder
extends
MimeEntityBuilder
{
	/**
	 * In-memeory body line handler.
	 *
	 * @param integer Entity ID
	 * @param string Line of body text
	 * @param integer Optional byte position from source
	 */
	function
	handleBodyLineByRef ($entityId, &$line, $pos = null)
	{
		$this->_assureIdExists ($entityId);

		$entity =& $this->_entities[$entityId];

		if (! is_object ($entity->body))
			$entity->body =& new MemoryMimeBody;

		$entity->body->handleRawLineByRef ($line, $pos);
	}

	/**
	 * In-memeory body line handler.
	 *
	 * @see handleBodyLineByRef
	 * @param integer
	 * @param string
	 * @param integer
	 */
	function
	handleBodyLine ($entityId, $line, $pos = null)
	{
		$this->_handleBodyLineByRef ($entityId, $line, $pos);
	}
}

?>
