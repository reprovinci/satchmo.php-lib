satchmo.php-lib
===============

A helper library written in PHP for handling Satchmo form submissions.

## Usage
```php
<?php

// Handles our upload.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
	try {
		# Create a new upload descriptor for our uploaded file.
		$upload = new Satchmo\Upload\ArrayUploadDescriptor($_FILES["file"]);
		
		# Create a new upload store, which temporarily stores the uploads in `data/`.
		$store = new Satchmo\Upload\SessionUploadStore("data");
		
		# Store our upload in the store. `$key` can be used in subsequent to use the uploaded file. Don't forget to
		# `destroy()` the descriptor!
		$key = $store->store($upload);
	} catch (Exception $e) {
		# Gotta catch 'em all!

		# If no file was selected, an exception is sent to the browser.
		header("HTTP/1.1 500 Internal Server Error");
		header("Content-Type: application/json");
		print json_encode(array(
			"error"   => get_class($e),
			"code"    => $e->getCode(),
			"message" => $e->getMessage(),
		));

		$response = Satchmo\HTTP\Response::createFromBuffer(500);
		$response->send();
		exit();
	}
	
	# Else, output our important information.
	header("Content-Type: application/json");
	print json_encode(array(
		"data" => array(
			42 => "So long and thanks for all the fish!",
		)
	));

	$response = Satchmo\HTTP\Response::createFromBuffer();
	$response->send();
	exit();
}

```