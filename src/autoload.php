<?php

/* An autoloader for Youthweb\Api classes. This should be require()d by
 * the user before attempting to instantiate any of the classes.
 */

spl_autoload_register(function ($class)
{
	$class = str_replace('\\', '/', $class);
	$class = str_replace('Youthweb/Api/', '', $class);

	$path = dirname(__FILE__).'/'.$class.'.php';

	if ( file_exists($path) )
	{
		require_once $path;
	}

	// Text classes
	$path = dirname(__FILE__).'/../tests/'.$class.'.php';

	if ( file_exists($path) )
	{
		require_once $path;
	}
});
