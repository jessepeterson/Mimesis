<?php

require_once ('mimesis/mailbox.inc.php');

class
MailboxTestCase
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
