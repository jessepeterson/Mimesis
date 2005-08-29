<?php

/**
 * MIME entity builder.
 *
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

class
MimeEntityBuilder
{
	var $_entities = array ();

	function
	MimeEntityBuilder ()
	{
		$this->_entities[0] =& new MimeEntity;
	}

	function
	addComponent ($entityId, $componentEntityId)
	{
		$this->_assureIdExists ($entityId);
		$this->_assureIdExists ($componentEntityId);

		$this->_entities[$entityId]->addComponent (
			$this->_entities[$componentEntityId]
			);
	}

	function
	handleBodyLineByRef ($entityId, &$line, $pos = null)
	{
	}

	function
	handleHeaderField ($entityId, &$headerField)
	{
		$this->_assureIdExists ($entityId);
		$this->_entities[$entityId]->addHeaderField ($headerField);

		// print ('hHF (' . $entityId . ")\n");
	}

	function
	&getBuiltObject ()
	{
		return $this->_entities[0];
	}

	function
	_assureIdExists ($id)
	{
		if (! is_object ($this->_entities[$id]))
			$this->_entities[$id] =& new MimeEntity;
	}
}
