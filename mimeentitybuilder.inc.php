<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

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
	 * @todo Not implemented yet!
	 * @param integer Entity ID
	 * @param string Line of body text
	 * @param integer Optional byte position from source
	 */
	function
	handleBodyLineByRef ($entityId, &$line, $pos = null)
	{
	}

	/**
	 * @todo Not implemented yet!
	 * @param integer Entity ID
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
	 * @see MimeEntity
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
