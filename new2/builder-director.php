<?php

/**
 * Director for MIME entity builder.
 */
class
MimeMessageFileParser
{
	/**
	 * @var MimeEntityBuilder
	 * @access private
	 */
	var $_builder;

	/**
	 * File name to parse MIME message from.
	 *
	 * @var string
	 * @access private
	 */
	var $_fileName;

	/**
	 * @param MimeEntityBuilder MIME entity builder object.
	 * @param string File name to parse MIME message from.
	 */
	function
	MimeMessageFileParser (&$builder, $filename = null)
	{
		$this->_builder =& $builder;
		$this->setFileName ($filename);
	}

	/**
	 * Setter.
	 *
	 * @param string File name.
	 */
	function
	setFileName ($fileName)
	{
		$this->_fileName = $fileName;
	}

	/**
	 * Read file and dispatch to line handler.
	 *
	 * @access private
	 */
	function
	_readFile ()
	{
		if ($f = fopen ($this->_fileName, 'r'))
		{
			while (! feof ($f))
			{
				// PHP (< 4.2.0) default of 1024 bytes
				$this->_handleRawLine (
					ftell ($f),
					fgets ($f, 1025)
					);
			}

			fclose ($f);
		}
	}

	/**
	 * @access private
	 */
	function
	_handleRawLine ($pos, $line)
	{
		//print (++$this->_counter . "\n");
		//print $line;
		//var_dump (strlen ($line));
		var_dump ($pos, $line);
	}
}

class A {}

//$b = new MimeMessageFileParser (new A, 'test-builder.txt');
$b = new MimeMessageFileParser (new A, 'test-builder.txt');

$b->_readFile ();

?>
