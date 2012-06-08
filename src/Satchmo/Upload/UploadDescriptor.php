<?php

namespace Satchmo\Upload;

/**
 * Represents a file upload.
 */
interface UploadDescriptor
{
	/**
	 * Destroy an upload.
	 * 
	 * @return boolean Returns `true` when the upload was located and destroyed, `false` when no such upload exists.
	 * @throws Satchmo\Upload\FileNotWritableException Thrown when the location is not writable.
	 */
	public function destroy();

	/**
	 * Returns the file's filename on the user's computer.
	 *
	 * @return string
	 */
	public function getFilename();

	/**
	 * Returns the file's current location on disk.
	 *
	 * @return string
	 */
	public function getLocation();

	/**
	 * Moves the file to the given `$location`. Changes the file's current location.
	 *
	 * @param string
	 * @throws Satchmo\Upload\FileNotWritableException Thrown when the location is not writable.
	 */
	public function moveFile($location);
}