<?php

require_once ('entity.php');
require_once ('body.php');
require_once ('headerfield.php');

$test = '/home/jpeterson/devel/resources/mime-tests/torture-test.eml';
// $test = '/home/jpeterson/devel/resources/mime-tests/rf-mime-torture-test-1.0.eml';

$bodyContentFact =& new SingleFileBodyContentFactory;
$bodyContentFact->setFileName ($test);

$testEnt =& new MimeEntity;
$testEnt->setParseBodyContentFactory ($bodyContentFact);

// BodyContent::factory
// ParsedFileBodyContent::factoryq:

// $testEnt->setParseSourceFile ($test);

$f = fopen ($test, 'r');
while ($buf = fread ($f, 8192))
	$testEnt->_parseRawChunkFromRef ($buf);
fclose ($f);

$testEnt->_parseDone ();

parseEnt ($testEnt);
// var_dump ($testEnt);
// var_dump ($testEnt->getHeaderFieldByName ('content-type'));

function parseEnt (&$entity, $level = 0)
{
	$level++;

	$cTypeHeader =& $entity->getHeaderFieldByName ('content-type');
	if (is_object ($cTypeHeader))
		print (str_repeat ('-+', $level) . '|  ' .
			($type = $cTypeHeader->getType ()) . '/' .
			($subtype = $cTypeHeader->getSubType ()) . "\n");
	else
		print (str_repeat ('-+', $level) . "\n");

	$body =& $entity->getBody ();

	switch (get_class ($body))
	{
	case 'CompositeBody':

		$composite_ents =& $body->getEntities ();

		foreach (array_keys ($composite_ents) as $ent_key)
		{
			parseEnt ($composite_ents[$ent_key], $level);
		}

		break;
	case 'MimeEntityEntityBody':

		parseEnt ($body->getEntity (), $level);

		break;
	default:

		/*
		if ('text' == $type and
		    'plain' == $subtype)
		{
			$content =& $body->getBodyContent ();

			$fle = fopen ($content->getFileName (), 'r');
			fseek ($fle, $content->_position);
			print (fread ($fle, $content->_length));
			fclose ($fle);
		}
		*/

		break;
	}
}

?>
