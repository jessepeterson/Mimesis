<?php

/**
 * Parse RFC 822 and RFC 2045 structed header field tokens.
 *
 * @package MIMESIS_PARSE
 * @copyright Copyright (C) 2005 Jesse Peterson.  All rights reserved.
 * @author Jesse Peterson <jpeterson275@comcast.net>
 */

/** */
define ('TOKEN_COMMENT', 1);
define ('TOKEN_QUOTED_STRING', 2);
define ('TOKEN_DOMAIN_LITERAL', 4);
define ('TOKEN_WHITE_SPACE', 8);
define ('TOKEN_SPECIAL', 16);
define ('TOKEN_ATOM', 32);
define ('TOKEN_ALL',
	TOKEN_COMMENT |
	TOKEN_QUOTED_STRING |
	TOKEN_DOMAIN_LITERAL |
	TOKEN_WHITE_SPACE |
	TOKEN_SPECIAL |
	TOKEN_ATOM);

function
filter822tokens ($tokens, $types)
{
	$new_toks = array ();

	foreach ($tokens as $tok)
	{
		if (isset ($tok['type']) and
		    $tok['type'] & $types)
			$new_toks[] = $tok;
		elseif (! isset ($tok['type']) and
		        TOKEN_ATOM & $types)
			$new_toks[] = $tok;
	}

	return $new_toks;
}


/**
 * Parse and tokenize a header field structured header field body.
 *
 * Analyze, parse, and tokenize the given field body into an RFC 822
 * section 3.1.4 lexical symbols and types sequence.  These include special
 * chars, quoted-strings, domain-literals, comments, and "atoms."
 *
 * - Note: Has no knowledge of folding and unfolding.  Assumes any
 *         unfolding has already been done or doesn't exist.
 * - Note: Specials are taken from argument.  If not specified, use RFC
 *         822.
 * - Note: "linear-white-space" tokenization may not be to spec (CRLF?).
 * - Note: Implemented as char-at-a-time parsing.  For PHP this may be
 *         glacially slow!
 *
 * @param string $body Refernce to structured header field body.
 * @param array $specials Special chars.
 * @return array Tokens.
 */
function
tokenize822 ($body, $specials = null)
{
	if (empty ($specials))
	{
		$specials = array (
			')', ']', '<', '>', ':', ';', '@', '\\', ',', '/', '.');
	}

	$tokens = array ();
	$token_pos = 0;

	for ($i = 0; $i < strlen ($body); $i++)
	{
		switch ($body{$i})
		{
		case '(': // comment
			$cmnt_level = 1;
			$token_pos++;

			// loop for end of comment(s)
			for (++$i; $i < strlen ($body); $i++)
			{
				// because of break's, this need be an if,
				// rather than switch
				if (')' == $body{$i})
				{
					if (1 == $cmnt_level)
					{
						$tokens[$token_pos++]["type"] = TOKEN_COMMENT;
						break;
					}
					elseif ($cmnt_level > 1)
					{
						$tokens[$token_pos]["string"] .= ')';
						$cmnt_level--;
					}
				}
				elseif ('(' == $body{$i})
				{
					$cmnt_level++;
					$tokens[$token_pos]["string"] .= '(';
				}
				// preserve our escaped characters in case further/nested
				// comment processing is needed. 
				/*
				elseif ('\\' == $body{$i})
					$tokens[$token_pos]["string"] .= $body{++$i};
				 */
				else
					$tokens[$token_pos]["string"] .= $body{$i};
			}
			break;
		case '"': // quoted-string
			$end_ch = '"';
			$type   = TOKEN_QUOTED_STRING;
		case '[': // domain-literal
			if (empty ($end_ch))
			{
				$end_ch = ']';
				$type   = TOKEN_DOMAIN_LITERAL;
			}

			$token_pos++;
			
			// loop for end of self-delimiting tokens
			for (++$i; $i < strlen ($body); $i++)
			{
				if ($body{$i} == $end_ch)
					break;
				if ('\\' == $body{$i})
					$tokens[$token_pos]["string"] .= $body{++$i};
				else
					$tokens[$token_pos]["string"] .= $body{$i};
			}

			$tokens[$token_pos++]["type"] = $type;
			$end_ch = null;
			break;
		case "\r": // linear-white-space
		case "\n":
		case "\t":
		case ' ':
			/* TODO: uhg, this is just horrid.  recode soon!  perhaps place
					 in default case? */

			// advance string pointer until the end of our white-space
			for (; ($i + 1) < strlen ($body); $i++)
			{
				if (! ("\r" == $body{$i + 1} or "\n" == $body{$i + 1} or
					   "\t" == $body{$i + 1} or ' '  == $body{$i + 1}))
					break;
			}

			$token_pos++;
			$tokens[$token_pos++]['type'] = TOKEN_WHITE_SPACE;
			break;
		default: // specials and atoms
			if (in_array ($body{$i}, $specials))
			{
				$tokens[++$token_pos]["string"] = $body{$i};
				$tokens[$token_pos++]["type"]   = TOKEN_SPECIAL;
			}
			else
				$tokens[$token_pos]["string"] .= $body{$i};
		}
	}

	return $tokens;
}

/**
 * @see tokenize822
 */
function
tokenize2045 ($body)
{
	return tokenize822 ($body, array (
		')', ']', '<', '>', ':', ';', '@', '\\', ',', '/', '?', '='));
}

?>
