<?php

require_once ('mimesis/parse.inc.php');
require_once ('mimesis/build.inc.php');
require_once ('mimesis/headerfield.inc.php');
require_once ('mimesis/body.inc.php');

if (empty ($argv[1]))
	$file = 'mime-test.eml';
elseif ('-' == $argv[1])
	$file = 'php://stdin';
else
	$file = $argv[1];

$parser =
	new MimeFileLineParser (
		new MimeEntityBuilderLineBuilder (new IntraFileBodyMimeEntityBuilder ($file)),
//		new MimeEntityBuilderLineBuilder (new MemoryBodyMimeEntityBuilder),
//		new MimeEntityBuilderLineBuilder (new MimeEntityBuilder),
		$file
		);

print ('parsing file "' . $file . "\" ...");

$parser->parse ();

print (" done.\n\n");

/*
$rootEntity =& $parser->getMimeEntity ();

$iter = $rootEntity->getComponentIterator ();

for ($iter->rewind (); $iter->valid (); $iter->next ())
{
	$ent =& $iter->current ();
	var_dump (get_class ($ent));
}
 */

test_entity_structure_display (
	$entity =& $parser->getMimeEntity ()
	);

file_put_contents ('myfile.txt', serialize ($entity));
// var_dump ($entity);

function test_entity_structure_display (&$ent)
{
	global $t;
	$t++;

	// print (str_repeat ('-+', $t) . '| ');
	print (str_repeat ('|', $t));

	$hdr =& $ent->getHeaderFieldByName ('content-type');

	if (! is_object ($hdr))
	{
		print ('(no Content-type) ');
	}

	print ($hdr->_type . '/' . $hdr->_subtype);
	print ("\n");
	
	if (isset ($ent->body) and is_object ($ent->body))
	{
		$b = $ent->body->getBody ();
		$end = urlencode (substr ($b, -10));
		$beg = urlencode (substr ($b, 0, 10));
		unset ($b);
		
		print ('body start=' . $ent->body->_start . '(' . $beg . ') end=' . $ent->body->_endPos . '(' . $end . ") file=" . $ent->body->_fileName . "\n");
		// var_dump ($ent->body->_start, $ent->body->_endPos, $ent->body->getBody ());
	}

	for ($i =& $ent->getComponentIterator ();
	     $i->valid ();
	     $i->next ()
		 )
	{
		test_entity_structure_display ($i->current ());
		$t--;
	}
}

?>
