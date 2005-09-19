<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

/**
 * Generate HTML code from a MimeEntity.
 *
 * @package MIMESIS_HTMLGEN
 */
class
MimeEntityHtmlGenerator
{
	/**
	 * @var MimeEntity
	 * @access private
	 */
	var $_entity;


	/**
	 * Header fields (by name) to attempt to display.
	 *
	 * @var array
	 * @access private
	 */
	var $_headers = array (
		'from', 'date', 'to', 'cc', 'bcc', 'subject'
		);

	/**
	 * @param MimeEntity
	 */
	function
	MimeEntityHtmlGenerator (&$entity)
	{
		$this->_entity =& $entity;
	}

	/**
	 * @param MimeEntity
	 */
	function
	&createGenerator (&$entity)
	{
		$type    = strtolower ($entity->getType ());
		$subtype = strtolower ($entity->getSubType ());
		
		if ('text' == $type and 'plain' == $subtype)
			return new PlainTextHtmlGenerator ($entity);
		elseif ('multipart' == $type and 'mixed' == $subtype)
			return new CompositeEntityHtmlGenerator ($entity);
		elseif ('message' == $type and 'rfc822' == $subtype)
			return new CompositeEntityHtmlGenerator ($entity);
		else
			return new UnknownEntityHtmlGenerator ($entity);
	}

	/**
	 * Generate HTML output.
	 */
	function
	generate ()
	{
		print ("\n");
		print ('<div class="entity">' . "\n");
		print (
			'<!-- ' .
			$this->_entity->getType () .
			'/' .
			$this->_entity->getSubType () .
			' -->' . "\n");
		if ($this->generatingHeader ())
			$this->generateHeader ();
		$this->generateBody ();
		print ('</div>' . "\n");
	}

	/**
	 * Return true if we're to generate header HTML.
	 *
	 * @return boolean
	 */
	function
	generatingHeader ()
	{
		if ($this->_entity->headerFieldExists ('from') or
		    $this->_entity->headerFieldExists ('subject'))
			return true;
	}

	/**
	 * Generate stock HTML header
	 *
	 * @return boolean
	 */
	function
	generateHeader ()
	{
		print ('<table class="header">' . "\n");

		foreach ($this->_headers as $findHeader)
		{
			$header =& $this->_entity->getHeaderFieldByName ($findHeader);
			if (is_object ($header))
			{
				print ('<tr>');
				print ('<th>' . $header->getName () . ':</th>');
				print ('<td>' . htmlentities ($header->getBody ()) . '</td>');
				print ('</tr>' . "\n");
			}
		}

		print ('</table>' . "\n");
	}
}

/**
 * @package MIMESIS_HTMLGEN
 */
class
PlainTextHtmlGenerator
extends
MimeEntityHtmlGenerator
{
	function
	generateBody ()
	{
		print ('<p class="body">' . "\n");
		print (nl2br (htmlentities ($this->_entity->body->getBody ())));
		print ('</p>' . "\n");
	}
}

/**
 * @package MIMESIS_HTMLGEN
 */
class
CompositeEntityHtmlGenerator
extends
MimeEntityHtmlGenerator
{
	/**
	 * Loop through component entities and generate HTML from them.
	 */
	function
	generateBody ()
	{
		for ($i =& $this->_entity->getComponentIterator ();
		     $i->valid ();
		     $i->next ()
		     )
		{
			$htmlGen =& MimeEntityHtmlGenerator::createGenerator (
				$i->current ()
				);

			if (is_object ($htmlGen))
				$htmlGen->generate ();
		}
	}
}

/**
 * @package MIMESIS_HTMLGEN
 */
class
UnknownEntityHtmlGenerator
extends
MimeEntityHtmlGenerator
{
	var $_headers = array (
		'content-type', 'content-disposition'
		);

	function
	generatingHeader ()
	{
		if ($this->_entity->isComposite ())
			return true;
	}

	function
	generateBody ()
	{
		if (! $this->_entity->isComposite ())
		{
			print ('<table><tr><td>');
			print ('<img src="doc.png"></img>');
			print ('</td><td>');

			$contentDisposHdr =&
				$this->_entity->getHeaderFieldByName ('content-disposition');
			$contentTypeHdr =&
				$this->_entity->getHeaderFieldByName ('content-type');

			if (is_object ($contentDisposHdr))
			{
				if ('attachment' == strtolower ($contentDisposHdr->getType ()))
					$filename = $contentDisposHdr->getParam ('filename');
			}

			if (empty ($filename))
			{
				if (is_object ($contentTypeHdr))
					$filename = $contentTypeHdr->getParam ('name');
			}

			if (empty ($filename))
				$filename = 'Message Attachment';

			print ('<strong>' . $filename . '</strong><br>');

			print (ucfirst (strtolower ($this->_entity->getType ())) . " (" . $this->_entity->getSubType () . ')');
			print ('</td></tr></table>' . "\n");
		}
		else
		{
			print ('<p style="background-color:red;"><strong>*** UNKNOWN COMPOSITE ENTITY ***</strong><br>[' .
				$this->_entity->getType () . '/' . $this->_entity->getSubType () .
				']</p>' . "\n");
		}
	}
}


?>
