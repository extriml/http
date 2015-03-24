<?php
/**
 * Streamable
 * @copyright  2015 extriml
 * @license   MIT
 * @package http
 * @subpackage  elise
 * @author Alex Orlov <mail@alexxorlovv.name>
 * @version 1.0.0
 * @since 2015-02-28
 * 
 */

namespace elise\http;

use Psr\Http\Message\StreamableInterface;

class Streamable implements StreamableInterface
{

    protected $resource;
    protected $stream;
    

    public function __construct($stream, $mode = "r")
    {
        $this->stream = $stream;

        if (is_string($stream) === true) {
            $this->resource = fopen($stream, $mode);
        } elseif(is_resource($stream)) {
            $this->resource = $stream;
        } else {
            throw new \InvalidArgumentException("Invalid stream proivider.");
        }
    }


    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getContents();
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        if (isset($this->resource) === false) {
            return;
        }

        $resource = $this->detach();
        fclose($resource);
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    /**
     * Get the size of the stream if known
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {        
        if (isset($this->resource) === false) {
            return null;
        }
        return $this->getMetadata("unread_bytes");
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int|bool Position of the file pointer or false on error.
     */
    public function tell()
    {
        if (isset($this->resource) === false) {
            return null;
        }

        return ftell($this->resource);
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        if (isset($this->resource) === false) {
            return true;
        }

        return feof($this->resource);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        if (isset($this->resource) === false) {
            return false;
        }

        return (bool) $this->getMetadata("seekable");
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *     based on the seek offset. Valid values are identical to the built-in
     *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *     offset bytes SEEK_CUR: Set position to current location plus offset
     *     SEEK_END: Set position to end-of-stream plus offset.
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if ($this->isSeekable() === false) {
            return false;
        }

        return (bool) (0 === fseek($this->resource, (int) $offset, (int) $whence));
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will return FALSE, indicating
     * failure; otherwise, it will perform a seek(0), and return the status of
     * that operation.
     *
     * @see seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function rewind()
    {
        if ($this->isSeekable() === false) {
            return false;
        }

        return (bool) (0 === fseek($this->resource, 0));
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        if (isset($this->resource) === false) {
            return false;
        }

        return is_writable($this->getMetadata("uri"));
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int|bool Returns the number of bytes written to the stream on
     *     success or FALSE on failure.
     */
    public function write($string)
    {
       if ($this->isWritable() === false) {
        return false; 
       }

       return fwrite($this->resource, (string) $string);
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
      if (isset($this->resource) === false) {
        return false;
      }  

      $mode = $this->getMetadata("mode");
      return (strstr($mode, 'r') or strstr($mode, '+'));
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *     them. Fewer than $length bytes may be returned if underlying stream
     *     call returns fewer bytes.
     * @return string|false Returns the data read from the stream, false if
     *     unable to read or if an error occurs.
     */
    public function read($length)
    {
        if ($this->isReadable() === false) {
            return false;
        }

        if ($this->eof() === true) {
            return "";
        }

        return fread($this->resource, (int) $length);
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     */
    public function getContents()
    {
        if ($this->isReadable() === false) {
            return '';
        }

        return stream_get_contents($this->resource);
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        if (isset($this->resource) === false) {
            return null;
        }

        $meta = stream_get_meta_data($this->resource);
        if ($key !== null) {
            return $meta[$key];
        }
        return $meta;

    }
}
