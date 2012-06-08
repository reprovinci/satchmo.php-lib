<?php

namespace Satchmo\Upload;

use RuntimeException;

/**
 * Thrown when an upload's file cannot be located on disk.
 */
class FileNotFoundException extends RuntimeException
{
}