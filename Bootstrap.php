<?php

spl_autoload_register(function ($class) {
	$namespace = "Mirarus\\TeamSpeakCFDNS\\";
	if (strncmp($namespace, $class, strlen($namespace)) !== 0) {
		return;
	}
	$file = __DIR__ . '/src/' . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($namespace))) . '.php';
	if (file_exists($file)) {
		require $file;
	}
});