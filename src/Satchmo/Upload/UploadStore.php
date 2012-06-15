<?php

namespace Satchmo\Upload;

/**
 * An upload store stores uploaded files and returns a unique key for later retrieval. Files can be uploaded through
 * another request (eg. an AJAX request) and used in a subsequent request.
 */
interface UploadStore
{
	/**
	 * Destroys all uploads created before `$date`.
	 *
	 * @param int $date
	 * @return boolean Returns `true` when all expired uploads were succesfully removed, `false` when some failed to be
	 *                 removed.
	 */
	public function clean($date);

	/**
	 * Destroy an upload.
	 * 
	 * @param string $key
	 * @return boolean Returns `true` when the upload was located and destroyed, `false` when no such upload exists.
	 * @throws Satchmo\Upload\FileNotWritableException Thrown when the location is not writable.
	 */
	public function destroy($key);

	/**
	 * @param string $key
	 * @return Satchmo\Upload\UploadDescriptor|null `null` is returned when no descriptor under `$key` exists.
	 */
	public function retrieve($key);

	/**
	 * Stores the given descriptor and returns a unique key for later retrieval.
	 *
	 * @param Satchmo\Upload\UploadDescriptor $descriptor
	 * @return string
	 * @throws Satchmo\Upload\FileNotFoundException Thrown when the uploaded file cannot be located on disk.
	 * @throws Satchmo\Upload\UploadException Thrown when an error occurred during upload.
	 */
	public function store(UploadDescriptor $descriptor);
}