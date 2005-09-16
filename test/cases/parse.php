<?php

require_once ('mimesis/parse.inc.php');
require_once ('mimesis/build.inc.php');
require_once ('mimesis/headerfield.inc.php');
require_once ('mimesis/body.inc.php');

class
ParseTestCase
extends
UnitTestCase
{
	function
	testNullFile ()
	{
		$parser = new MimeFileLineParser (
			new MimeEntityBuilderLineBuilder (new MimeEntityBuilder),
			'/nonexistant'
			);

		$this->assertFalse ($parser->parse ());
	}
}

?>
