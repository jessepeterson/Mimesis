<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

/**
 * MIME entity builder.
 *
 * @package MIMESIS_BUILD
 */
class
MimeEntityBuilder
{
	/**
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
	 * @param integer
	 * @param integer
	 */
	function
	addComponent ($entityId, $componentEntityId)
	{
		$this->_assureIdExists ($entityId);
		$this->_assureIdExists ($componentEntityId);

		$this->_entities[$entityId]->addComponent (
			$this->_entities[$componentEntityId]
			);
	}

	/**
	 * @todo Not implemented yet!
	 * @param integer
	 * @param string
	 * @param integer
	 */
	function
	handleBodyLineByRef ($entityId, &$line, $pos = null)
	{
	}

	/**
	 * @param integer
	 * @param MimeHeaderField
	 */
	function
	handleHeaderField ($entityId, &$headerField)
	{
		$this->_assureIdExists ($entityId);
		$this->_entities[$entityId]->addHeaderField ($headerField);
	}

	/**
	 * @return MimeEntity
	 */
	function
	&getBuiltObject ()
	{
		return $this->_entities[0];
	}

	/**
	 * @param integer
	 * @access private
	 */
	function
	_assureIdExists ($id)
	{
		if (! is_object ($this->_entities[$id]))
			$this->_entities[$id] =& new MimeEntity;
	}
}
