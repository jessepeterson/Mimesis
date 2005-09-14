<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

/**
 * Iterate through an array of references.
 *
 * Of note is that the current() method returns
 * another reference to an element in the array.
 *
 * @package MIMESIS_UTIL
 */
class
RefArrayIterator
{
	/**
	 * @access private
	 * @var array
	 */
	var $_refs;

	/**
	 * @access private
	 * @var array
	 */
	var $_keys;

	/**
	 * Array key counter.
	 *
	 * @access private
	 * @var integer
	 */
	var $_ctr;

	/**
	 * @return MimeEntity
	 */
	function
	&current ()
	{
		return $this->_refs[$this->_keys[$this->_ctr]];
	}

	function
	key ()
	{
		return $this->_keys[$this->_ctr];
	}

	/**
	 * @see valid
	 * @return boolean Validity of current item.
	 */
	function
	next ()
	{
		$this->_ctr++;

		return $this->valid ();
	}

	function
	rewind ()
	{
		$this->_ctr = 0;
	}

	function
	valid ()
	{
		if ($this->_ctr < count ($this->_keys))
			return true;
		else
			return false;
	}

	/**
	 * @param array Array of references.
	 */
	function
	setRefArray (&$array)
	{
		$this->_refs =& $array;
		$this->_keys = array_keys ($array);
		$this->_ctr = 0;
	}
}

?>
