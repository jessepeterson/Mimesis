<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 * @package MIMESIS_TEST
 */

/** */
require_once ('mimesis/body.inc.php');

/** @package MIMESIS_TEST */
class
IntraFileMimeBodyTestCase
extends
UnitTestCase
{
	function
	TestBounds ()
	{
		$body =& new IntraFileMimeBody;

		$body->setFileName ('testfile.txt');

		$line = 'abcdefghiklmnopqrstuvwxyz';
		$body->handleRawLineByRef ($line, 8);

		$this->assertEqual ($body->getBody (), $line);
	}
}

?>
