<?php

require_once ('mimeentity.inc.php');
require_once ('mimeentitybuilder.inc.php');
require_once ('mimeentitybuilderlinebuilder.inc.php');
require_once ('mimemessagefileparser.inc.php');

if (empty ($argv[1]))
	$f = 'mime-test.eml';
else
	$f = $argv[1];

print ('parsing file "' . $f . "\" ...");

$parser = new MimeEntityFileParser (new MimeEntityBuilderLineBuilder (new MimeEntityBuilder), $f);

$parser->parse ();

print (" done.\n\n");

$a = $parser->getMimeEntity ();

// var_dump ($a->getHeaderFieldByName ('content-type'));
recursent ($a);


function recursent (&$ent)
{
	global $t;
	$t++;
	print (str_repeat ('-+', $t));

	$hdr =& $ent->getHeaderFieldByName ('content-type');

	print ('| ' . $hdr->_type . '/' . $hdr->_subtype . "\n");

	foreach (array_keys ($ent->_components) as $ek)
	{
		recursent ($ent->_components[$ek]);
		$t--;
	}
}

// var_dump ($parser->getMimeEntity ());

?>
