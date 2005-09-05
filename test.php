<?php

require_once ('parse.inc.php');
require_once ('build.inc.php');

if (empty ($argv[1]))
	$file = 'mime-test.eml';
elseif ('-' == $argv[1])
	$file = 'php://stdin';
else
	$file = $argv[1];

print ('parsing file "' . $file . "\" ...");

$parser =
	new MimeFileLineParser (
		new MimeEntityBuilderLineBuilder (new MimeEntityBuilder),
		$file
		);

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
