<?php

/**
 * Interface for building MimeEntity objects and specifying raw header
 * fields.
 *
 * Handling of complex/heirarchial MIME structures is based around
 * the use of an identifying string.  For example, two entities
 * may be constructed and once constructed one could be placed inside
 * the other by calling ::addCompositePart with each entity's Id.
 */
interface
RawHeaderFieldMimeEntityBuilder ()
{
	/**
	 * Before using an entity Id in any of the methods herein we must
	 * register said Id.  This prevents erroneous Ids that may lead to
	 * bugs.
	 */
	public function
	registerEntityId ($entityId);

	public function
	handleBodyLine ($entityId, $bodyLine);

	/**
	 * Build composite structures.
	 *
	 * Pass in parent, child entity Ids to construct composite MIME entities.
	 */
	public function
	addCompositePart ($destEntityId, $sourceEntityId);

	/**
	 * Handle a "raw" header field.
	 *
	 * Pass in field name and body as it may appear in text.
	 */
	public function
	handleRawHeaderField ($entityId, $headerField);

	// handleFromHeaderField
	// handleToHeaderField
	// handleDateHeaderField
	// handleSubjectHeaderField
	// handleMimeVersionHeaderField
	// handleContentTypeHeaderField
	// ???

	/* preamble and epilogue should be ignored
	public function
	handlePreambleLine ($entityId, $preambleLine);

	public function
	handleEpilogueLine ($entityId, $epilogueLine);
	 */

	/**
	 * Return built MIME entity.
	 */
	public function
	&getMimeEntity ();
}

/////// ------------- ////////

$b =& new MimeEntityBuilder;


$root =& $b->newEntity ();

$b->addFromHeaderField (new MailboxSpecifier ('Nathaniel Borenstein', 'nsb@bellcore.com'));
$b->addToHeaderField (array (new MailboxSpecifier ('Ned Freed', 'ned@innosoft.com'));
$b->addDateHeaderField ('Sun, Mar 1993 23:56:48 -0800 (PST)');
$b->addSubjectHeaderField ('Sample Message');


$id1 = $b->newEntity ();
$b->handleFromHeaderField ($id1, 'test@example.com');
$b->handleBodyLine ($id1);

$id2 = $b->newEntity ();
$b->handleFromHeaderField ($id2, 'test@example.com');

$b->addCompositePart ($id1, $id2);


?>
