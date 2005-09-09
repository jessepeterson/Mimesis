<?php

require_once ('parse.inc.php');
require_once ('build.inc.php');
require_once ('fields.ifaces.php');
require_once ('body.inc.php');

if (empty ($argv[1]))
	$file = 'mime-test.eml';
elseif ('-' == $argv[1])
	$file = 'php://stdin';
else
	$file = $argv[1];

$parser =
	new MimeFileLineParser (
		new MimeEntityBuilderLineBuilder (new MemoryMimeEntityBuilder),
//		new MimeEntityBuilderLineBuilder (new MimeEntityBuilder),
		$file
		);

print ('parsing file "' . $file . "\" ...");

$parser->parse ();

print (" done.\n\n");

test_entity_structure_display (
	$parser->getMimeEntity ()
	);

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

	/*
	if (strtolower ($hdr->_type) == 'text' or
	    $hdr->_type == '')
		var_dump ($ent->body->getBody ());
	*/

	foreach (array_keys ($ent->_components) as $ek)
	{
		test_entity_structure_display ($ent->_components[$ek]);
		$t--;
	}
}

?>
