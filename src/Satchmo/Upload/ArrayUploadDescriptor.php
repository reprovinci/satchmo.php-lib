<?php

namespace Satchmo\Upload;

/**
 * Accepts a $_FILES file like array structure to describe a file upload.
 */
class ArrayUploadDescriptor implements UploadDescriptor
{
	/**
	 * @var boolean
	 */
	protected $moved_uploaded_file = false;

	/**
	 * Accepts a $_FILES file like array structure.
	 * 
	 * @param array $file
	 * @throws InvalidArgumentException Thrown when an argument is missing.
	 * @throws Satchmo\Upload\UploadException Thrown when an error occurred during upload.
	 * @see http://php.net/manual/en/features.file-upload.php
	 */
	public function __construct(array $file)
	{
		foreach (array("name", "tmp_name", "error") as $key)
			if (!isset($file[$key]))
				throw InvalidArgumentException("Missing `$key` in descriptor.");

		# If an error occurred, thrown an exception describing the error.
		$error = false;
		switch ($file["error"]) {
			case UPLOAD_ERR_INI_SIZE:
				$error = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$error = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
				break;
			case UPLOAD_ERR_PARTIAL:
				$error = "The uploaded file was only partially uploaded.";
				break;
			case UPLOAD_ERR_NO_FILE:
				$error = "No file was uploaded.";
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$error = "Missing a temporary folder.";
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$error = "Failed to write file to disk.";
				break;
			case UPLOAD_ERR_EXTENSION:
				$error = "A PHP extension stopped the file upload.";
				break;
		}

		if ($error !== false)
			throw new UploadException($error, $file["error"]);

		$this->filename = $file["name"];
		$this->location = $file["tmp_name"];
	}

	public function destroy()
	{
		if (!file_exists($this->location))
			return false;

		$directory = dirname($this->location);
		if (!is_writable($directory))
			throw new FileNotWritableException("File cannot be deleted.");
		
		if (!unlink($this->location))
			throw new FileNotWritableException("File could not be deleted.");

		return true;
	}

	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * @return int
	 */
	public function getFileSize()
	{
		$location = $this->getLocation();
		$info = new \SplFileInfo($location);

		return $info->getSize();
	}

	public function getLocation()
	{
		return $this->location;
	}

	public function moveFile($location)
	{
		if ($this->moved_uploaded_file) {
			# When the file has already been moved from its temporary location, check whether the source directory is
			# writable.
			$directory = dirname($this->location);
			if (!is_writable($directory))
				throw new FileNotWritableException("Source directory is not writable.");
		}

		# Check whether the target directory is writable.
		$directory = dirname($location);
		if (!is_writable($directory))
			throw new FileNotWritableException("Target directory is not writable.");

		# Proceed to moving the uploaded file.
		if ($this->moved_uploaded_file) {
			# The file has already been moved once and is no longer designated as an uploaded file by PHP.
			if (!rename($this->location, $location))
				throw new FileNotWritableException("Could not move file to new location.");

			$this->location = $location;
		} else {
			# The file has not yet been moved from its temporary location, move it and mark this upload as moved.
			if (!move_uploaded_file($this->location, $location))
				throw new FileNotWritableException("Could not move uploaded file to new location.");

			$this->location = $location;
			$this->moved_uploaded_file = true;
		}
	}
}