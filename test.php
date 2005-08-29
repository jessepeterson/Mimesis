<?php

if ($f = fopen ('test.txt', 'r'))
{
	while (! feof ($f))
	{
		echo fgets ($f, 1025) . "\n===\n";
	}

	fclose ($f);
}


?>
