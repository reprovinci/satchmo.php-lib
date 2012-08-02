<?php

namespace Satchmo\Upload;

/**
 * The Satchmo upload stores files in a temporary location and returns a unique key for later retrieval. The descriptor
 * is stored in a PHP session.
 */
class SessionUploadStore implements UploadStore
{
	const FILE_NS = "satchmo.";

	/**
	 * @var string
	 */
	protected $directory;

	/**
	 * @param string $directory A writable directory to store uploaded files.
	 * @throws Satchmo\Upload\FileNotWritableException Thrown when the storage directory is not writable.
	 */
	public function __construct($directory)
	{
		if (!is_writable($directory))
			throw new FileNotWritableException("Session upload store's storage directory is not writable.");

		$this->directory = $directory;
		$this->namespace = "satchmo-store://$directory";
	}

	public function clean($date)
	{
		$success = true;

		$files = glob("{$this->directory}/".self::FILE_NS."*");
		$start = strlen(self::FILE_NS);
		foreach ($files as $file) {
			if(filemtime($file) < $date)
				continue;

			$key = substr($file, $start);
			try {
				$this->destroy($key);
			} catch(FileNotWritableException $e) {
				$success = false;
			}
		}

		return $success;
	}

	public function destroy($key)
	{
		$descriptor = $this->retrieve($key);

		if($descriptor === null || !$descriptor instanceof UploadDescriptor)
			return false;

		unset($_SESSION[$this->namespace][$key]);
		$descriptor->destroy();

		return true;
	}

	public function retrieve($key)
	{
		if(!isset($_SESSION[$this->namespace][$key]))
			return null;

		return $_SESSION[$this->namespace][$key];
	}

	/**
	 * Stores the given descriptor and returns a unique key for later retrieval.
	 *
	 * @param Satchmo\Upload\UploadDescriptor $descriptor
	 * @return string
	 * @throws Satchmo\Upload\FileNotFoundException Thrown when the uploaded file cannot be located on disk.
	 * @throws Satchmo\Upload\UploadException Thrown when an error occurred during upload.
	 */
	public function store(UploadDescriptor $descriptor)
	{
		$key = md5($descriptor->getFilename().uniqid());
		$key_file = "{$this->directory}/".self::FILE_NS.$key;
		
		$extension = end(explode(".", basename($descriptor->getFilename())));
		if ($extension) {
			$key_file .= ".$extension";
		}

		$descriptor->moveFile($key_file);
		$_SESSION[$this->namespace][$key] = $descriptor;

		return $key;
	}
}