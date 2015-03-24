<?php
/**
 * Message
 * @package http
 * @subpackage  elise
 * @author Alex Orlov <mail@alexxorlovv.name>
 * @version 1.0.0
 * @since 2015-02-28
 * @license   MIT
 * @copyright  2015 extriml
 */
namespace elise\http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamableInterface;

class Message implements MessageInterface
{

	private $headers = [];
	private $version = "1.1";
	private $body;

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
    	return (string) $this->version;
    }

    /**
     * Create a new instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return self
     */
    public function withProtocolVersion($version)
    {
    	$new = clone $this;
    	$new->version = (string) $version;
    	return $new;
    }

    /**
     * Retrieves all message headers.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return array Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings.
     */
    public function getHeaders()
    {
    	return (array) $this->headers;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name)
    {
    	return (bool) array_key_exists(strtolower($name), $this->headers);
    }

    /**
     * Retrieve a header by the given case-insensitive name, as a string.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeaderLines() instead
     * and supply your own delimiter when concatenating.
     *
     * @param string $name Case-insensitive header field name.
     * @return string
     */
    public function getHeader($name)
    {
    	return (string) implode(", ",$this->getHeaderLines($name));
    }

    /**
     * Retrieves a header by the given case-insensitive name as an array of strings.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[]
     */
    public function getHeaderLines($name)
    {
    	return (array) $this->headers[$name];
    }

    /**
     * Create a new instance with the provided header, replacing any existing
     * values of any headers with the same case-insensitive name.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new and/or updated header and value.
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return self
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value)
    {
    	$this->nameHeaderValidate($name);
    	$this->valueHeaderValidate($value);

    	$message = clone $this;
    	$name = strtolower($name);
    	$message->headers[$name] = (array) $value;
    	return $message;
    }



    /**
     * Creates a new instance, with the specified header appended with the
     * given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new header and/or value.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return self
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value)
    {
     	$this->nameHeaderValidate($name);
    	$this->valueHeaderValidate($value);
    	   	
    	$message = clone $this;
    	$name = strtolower($name);
    	$message->headers[$name] = array_merge((array)$message->headers[$name], (array)$value);
    	return $message;
    }

    /**
     * Creates a new instance, without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that removes
     * the named header.
     *
     * @param string $name Case-insensitive header field name to remove.
     * @return self
     */
    public function withoutHeader($name)
    {
    	if ($this->hasHeader($name) === false) {
    		return $this;
    	}

    	$message = clone $this;
    	unset($message->headers[$name]);
    	return $message;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamableInterface Returns the body as a stream.
     */
    public function getBody()
    {
    	return $this->body;
    }

    /**
     * Create a new instance, with the specified message body.
     *
     * The body MUST be a StreamableInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamableInterface $body Body.
     * @return self
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamableInterface $body)
    {
    	$message = clone $this;
    	$message->body = $body;
    	return $message;
    }



    /**
     * nameHeaderValidate
     * @param  string $name 
     * @return void       
     * @throws \InvalidArgumentException for invalid header name.
     */
    protected function nameHeaderValidate($name)
    {
	    if (is_string($name) === false) {
    		throw new \InvalidArgumentException("Invalid header name.");
    	}    	
    }



    /**
     * valueHeaderValidate
     * @param  string|string[] $value 
     * @return void       
     * @throws \InvalidArgumentException for invalid header value.
     */
    protected function valueHeaderValidate($value)
    {
		if (is_array($value) === false) {
		    if (is_string($value) === false) {
		   		throw new \InvalidArgumentException("Invalid header value.");
			}
		}else {
		    	for($i = 0; $i < sizeof($value); $i++) {
		    		if (is_string($value[$i]) === false) {
		    			throw new \InvalidArgumentException("Invalid header value.");
		    		}
		    	} 
		    }
    }


    public function withHeaders($headers = array())
    {
        if(is_array($headers) === false) {
            throw new InvalidArgumentException("Invalid headers argument.");            
        }

        if (sizeof($headers) > 0) {
            foreach ($headers as $key => $value) {
                $this->nameHeaderValidate($key);
                $this->valueHeaderValidate($value);
                $this->headers[$key] = $value;
            }
        }
    }
}
