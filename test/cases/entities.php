<?php

require_once ('mimesis/mimeentity.inc.php');
require_once ('mimesis/headerfield.inc.php');

class
CompositeEntityTestCase
extends
UnitTestCase
{
	function
	TestCompositeTest ()
	{
		$entity = new MimeEntity;

		$this->assertFalse ($entity->isComposite ());

		$entity->addComponent (new MimeEntity);

		$this->assertTrue ($entity->isComposite ());
	}

	function
	TestComponentIterator ()
	{
		$entity = new MimeEntity;
		$entity->addComponent (new MimeEntity);
		$entity->addComponent (new MimeEntity);

		$iterator =& $entity->getComponentIterator ();
		$this->assertTrue (is_object ($iterator));

		$this->assertTrue ($iterator->valid ());
		$this->assertIsA ($iterator->current (), 'MimeEntity');
		$iterator->next ();

		$this->assertTrue ($iterator->valid ());
		$this->assertIsA ($iterator->current (), 'MimeEntity');
		$iterator->next ();

		$this->assertFalse ($iterator->valid ());
	}
}

class
EntityTypesTestCase
extends
UnitTestCase
{
	function
	TestGuessedMissingContentType ()
	{
		$entity = new MimeEntity;

		$this->assertEqual (strtolower ($entity->getType ()), 'text');
		$this->assertEqual (strtolower ($entity->getSubType ()), 'plain');
	}

	function
	TestGuessedMissingCompositeContentType ()
	{
		$entity = new MimeEntity;
		$entity->addComponent (new MimeEntity);

		$this->assertEqual (strtolower ($entity->getType ()), 'multipart');
		$this->assertEqual (strtolower ($entity->getSubType ()), 'mixed');
	}

	function
	TestContentType ()
	{
		$contentTypeHdr =& new ContentTypeHeaderField;
		$contentTypeHdr->setType ('audio');
		$contentTypeHdr->setSubType ('x-tooloud');

		$entity =& new MimeEntity;
		$entity->addHeaderField ($contentTypeHdr);

		$this->assertEqual (strtolower ($entity->getType ()), 'audio');
		$this->assertEqual (strtolower ($entity->getSubType ()), 'x-tooloud');
	}
}

?>
