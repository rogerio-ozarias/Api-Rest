<?php
function autoloader($class) {		
	if (strpos($class, "_")!== false) {
	    $path = explode('_', $class);
	    foreach ($path as $p) {
	        $cp .= DIRECTORY_SEPARATOR . $p;
	    }
	    include __DIR__ . $cp . '.php';
	} else {
		$path = explode('\\', $class);
	    foreach ($path as $p) {
	        $cp .= DIRECTORY_SEPARATOR . $p;
	    }
	    include __DIR__ ."/..". $cp . '.php';
	}
}
spl_autoload_register(autoloader);
