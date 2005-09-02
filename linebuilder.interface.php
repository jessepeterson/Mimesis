<?php

/**
 * Line object interface
 *
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

/**
 * Interface for a line-by-line builder.
 */
interface
LineBuilder
{
	/**
	 * Handle a line of the source data.
	 *
	 * @param string Line data.
	 */
	public function
	handleLine ($line);

	/**
	 * Return result object that was built from lines.
	 *
	 * @return object
	 */
	public function
	&getBuiltObject ();
}

?>
