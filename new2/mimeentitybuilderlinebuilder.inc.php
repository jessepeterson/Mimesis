<?php

/**
 * MIME entity line builder.
 *
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

require_once ('linebuilder.interface.php');
require_once ('fields.ifaces.php');

/**
 */
class
MimeEntityBuilderLineBuilder
implements
LineBuilder
{
	var $_builder;
	var $_nextLineSrcPos;

	var $_curEnt = 0;
	var $_entityCt = 0;

	/**
	 * 0 = entity #
	 * 1 = 'eoh'
	 * 2 = 'boundary'
	 * 3 = 'message'
	 * 4 = found boundaries
	 */
	var $_entityStack = array ();

	var $_headerFieldBody = null;
	var $_headerFieldName = null;

	function
	__construct (&$builder)
	{
		$this->_builder =& $builder;

		// create entity 'marker'
		$this->_entityStack[] = array (
			0 => $this->_entityCt,
			1 => false,
			2 => null,
			3 => false,
			4 => 0
			);
	}

	/**
	 * @param string Line data.
	 * @param integer Byte position.
	 * @implements LineBuilder::handleLine
	 */
	public function
	handleLine ($line, $pos = null)
	{
		if ($this->_entityStack[$this->_curEnt][1])
		{
			// entity body line

			// all boundary lines start with '-', test for it
			if ('-' == $line{0})
			{
				// loop through our entity stack to find boundaries
				for ($ek = $this->_curEnt; $ek >= 0; $ek--)
				{
					$boundary =& $this->_entityStack[$ek][2];

					if ($boundary)
					{
						// test for a boundary line
						if (0 === strpos ($line, '--' . $boundary))
						{
							$found_boundary = true;

							/* loop through our entity stack and remove
							   items which have been processed already */
							for ($j = $this->_curEnt; $j > $ek; $j--)
							{
								array_pop ($this->_entityStack);
								$this->_curEnt--;
							}

							// test for non-end-boundary (aka, entity following)
							if ('--' != substr ($line, strlen ($boundary) + 2, 2))
							{
								// increment our found boundaries for $ek entity
								$this->_entityStack[$ek][4]++;

								// create entity 'marker'
								$this->_entityStack[++$this->_curEnt] = array (
									0 => ++$this->_entityCt,
									1 => false,
									2 => null,
									3 => false,
									4 => 0
									);

								// add new entity to our parent entity
								$this->_builder->addComponent (
									$this->_entityStack[$ek][0],
									$this->_entityCt
									);
							}
							
							// break;
							return;
						}
					}
				}
			}

			$this->_builder->handleBodyLineByRef (
				$this->_entityStack[$this->_curEnt][0],
				$line,
				$pos
				);

		}
		elseif (empty ($line))
		{
			// first blank line (end of header/start of body)

			// mark this entity has having gotten past its header
			$this->_entityStack[$this->_curEnt][1] = true;
			// commit any previous header field we've been unfolding
			$this->_parseHeaderField ();

			// body is marked as being another message (content type of 'message')
			if ($this->_entityStack[$this->_curEnt][3])
			{
				// create entity 'marker'
				$this->_entityStack[++$this->_curEnt] = array (
					0 => ++$this->_entityCt,
					1 => false,
					2 => null,
					3 => false,
					4 => 0
					);

				// add our new entity to our parent entity
				$this->_builder->addComponent (
					$this->_entityCt - 1,
					$this->_entityCt
					);
			}
		}
		else
		{
			// header lines

			if (preg_match ('/^\s/', $line{0}))
			{
				// "folded" header line

				// add ourself to the unfolded line already existing
				$this->_appendHeaderFieldLine ($line);
			}
			elseif (preg_match ('/^([\x21-\x39\x3b-\x7e]*)\x3a(.*)$/',
			                    $line,
			                    $header_field))
			{
				// (possibly start of) header field line

				// commit any previous header field we've been unfolding
				$this->_parseHeaderField ();
				// make this line the current header field
				$this->_setHeaderField ($header_field[1], $header_field[2]);
			}
		}

	}

	function
	_setHeaderField ($name, $body)
	{
		$this->_headerFieldName = $name;
		$this->_headerFieldBody = $body;
	}

	function
	_appendHeaderFieldLine ($line)
	{
		$this->_headerFieldBody .= $line;
	}

	/**
	 * XXX: refactor me!
	 */
	function
	_parseHeaderField ()
	{
		if (! empty ($this->_headerFieldName))
		{
			// do da do da $this->_headerField
			if (! empty ($this->_headerFieldBody))
			{
				$field =& MimeHeaderField::nameFactory ($this->_headerFieldName);

				$field->setName ($this->_headerFieldName);
				$field->parseBody ($this->_headerFieldBody);

				if ('content-type' ==
				    strtolower ($this->_headerFieldName))
				{
					if ($field->isMultipart ())
					{
						$this->_entityStack[$this->_curEnt][2] =
							$field->getParam ('boundary');
					}
					elseif ($field->isMessage ())
					{
						$this->_entityStack[$this->_curEnt][3] = true;
					}
				}

				// add our header field to our entity (builder)
				$this->_builder->handleHeaderField (
					$this->_entityStack[$this->_curEnt][0],
					$field
					);

				$this->_headerFieldBody = null;
				$this->_headerFieldName = null;
			}
		}
	}


	/**
	 * @return MimeEntityBuilder
	 * @implements LineBuilder::getBuiltObject
	 */
	public function
	&getBuiltObject ()
	{
		return $this->_builder->getBuiltObject ();
	}
}

?>
