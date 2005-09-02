<?php

/**
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

require_once ('parsetok.php');

$mailbox_list = 'Jesse Peterson <jpeterson275@comcast.net>, '
	.	'joebob@123.org, ' 
	.	'"System" "Administrator" <root@some.box.com>';

$tokens = filter822tokens (tokenize822 ($mailbox_list),
	TOKEN_ALL ^ TOKEN_WHITE_SPACE ^ TOKEN_COMMENT);

$mailbox_tokens = array ();
foreach ($tokens as $tok)
{
	if (TOKEN_SPECIAL == $tok['type'] and
	    ',' == $tok['string'])
	{
		parse_mailbox ($mailbox_tokens);
		$mailbox_tokens = array ();
	}
	else
	{
		$mailbox_tokens[] = $tok;
	}
}

parse_mailbox ($mailbox_tokens);
$mailbox_tokens = array ();

function parse_mailbox ($mbox_toks)
{
	if (TOKEN_SPECIAL == $mbox_toks[count ($mbox_toks) - 1]['type'] and
	    '>'           == $mbox_toks[count ($mbox_toks) - 1]['string'])
	{
		foreach ($mbox_toks as $tok)
		{
			if (TOKEN_SPECIAL == $tok['type'] and
				'<'           == $tok['string'])
			{
				$reached_addr_spec = true;
			}
			elseif (TOKEN_SPECIAL == $tok['type'] and
				    '>'           == $tok['string'])
			{
				print ('-> "' . trim ($display_name) . '" ' . $addr_spec . "\n");
				return;
			}
			elseif ($reached_addr_spec)
			{
				$addr_spec .= $tok['string'];
			}
			else
			{
				$display_name .= $tok['string'] . ' ';
			}
		}
	}
	else
	{
		foreach ($mbox_toks as $tok)
		{
			$addr_spec .= $tok['string'];
		}

		print ('-> ' . $addr_spec . "\n");
		return;
	}
}

?>
