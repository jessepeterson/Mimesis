<?php

require_once ('mimesis/mimeentity.inc.php');

class
Sanity
extends
UnitTestCase
{
	function
	testMimeEntity ()
	{
		$e = new MimeEntity;
		$this->assertIsA ($e, 'MimeEntity');
	}
}

?>
