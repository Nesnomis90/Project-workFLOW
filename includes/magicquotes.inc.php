<?php
// Code to deal with MAGICQUOTES, a feature added to protect against dangerous characters.
// But it's not wise to use with SQL statement, so we remove them
// And instead we use PREPARED STATEMENTS when interfering with the database
// Which already deals with these dangerous characters by expecting a value and not a query.

if (get_magic_quotes_gpc())
{
	$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
	while (list($key, $val) = each($process))
	{
		foreach ($val as $k => $v)
		{
			unset($process[$key][$k]);
			if (is_array($v))
			{
				$process[$key][stripslashes($k)] = $v;
				$process[] = &$process[$key][stripslashes($k)];
			}
			else
			{
				$process[$key][stripslashes($k)] = stripslashes($v);
			}
		}
	}
	unset($process);
}
?>