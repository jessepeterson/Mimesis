<?php

class
MimeEntityLineBuilder
{
	var $_builder;

	function
	MimeEntityLineBuilder (&$builder)
	{
		$this->_builder =& $builder;
	}

	function
	handleLine ($line)
	{
	}

	function
	&getMimeEntity ()
	{
		return $this->_builder->getMimeEntity ();
	}
}

?>
