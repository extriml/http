<?php
/**
 * Request
 * @package http
 * @subpackage  elise
 * @author Alex Orlov <mail@alexxorlovv.name>
 * @version 1.0.0
 * @since 2015-02-28
 * @license   MIT
 * @copyright  2015 extriml
 */
namespace elise\http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamableInterface;
use elise\http\Uri;
use elise\http\Message;
use elise\http\Streamable;


class Request extends Message implements RequestInterface 
{
	private $method;
	private $uri;
	private $requestTarget;

    private $allowedMethods = [
        'CONNECT' => true,
        'DELETE' => true,
        'GET' => true,
        'HEAD' => true,
        'OPTIONS' => true,
        'PATCH' => true,
        'POST' => true,
        'PUT' => true,
        'TRACE' => true
    ];



    function __construct($uri = null, $method = null, $body = "php://memory", $headers = array())
    {
        if ($uri !== null) {
            if (($uri instanceof UriInterface) === true) {
                $this->uri = $uri;
            }elseif (is_string($uri) === true) {
                $this->uri = new Uri($uri);
            }else {
                throw new \InvalidArgumentException("Invalid uri provider.");     
            }
        }

        if($method !== null) {
            if ($this->allowedMethod($method) === true) {
                $this->method = $method;
            } else {
                throw new \InvalidArgumentException("Invalid method.");
            }
        }

        if (is_string($body) === true or is_resource($body) === true) {
            $this->body = new Streamable($body,"r");
        } elseif(($body instanceof StreamableInterface) === true) {
            $this->body = $body;
        } else {
            throw new \InvalidArgumentException("Invalid body provider.");  
        }


        $this->withHeaders($headers);

    }


    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget()
    {
    	if (is_string($this->requestTarget) === true) {
    		return $this->requestTarget;
    	}
    	elseif ($this->uri instanceof UriInterface) {
	    	$target = $this->uri->getPath();

	        if ($this->uri->getQuery()) {
	            $target .= '?' . $this->uri->getQuery();
	        }
	        return $target;
    	}

    	return "/";
    }

    /**
     * Create a new instance with a specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-2.7 (for the various
     *     request-target forms allowed in request messages)
     * @param mixed $requestTarget
     * @return self
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException(
                'Invalid request target provided; cannot contain whitespace'
            );
        }
        $request = clone $this;
        $request->requestTarget = $requestTarget;
        return $request;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod()
    {
    	return (string) $this->method;
    }

    /**
     * Create a new instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * changed request method.
     *
     * @param string $method Case-insensitive method.
     * @return self
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method)
    {
    	if ($this->allowedMethod($method) === false) {
    		throw new \InvalidArgumentException("invalid HTTP method.");
    	}
    	$request = clone $this;
    	$request->method = strtoupper($method);
    	return $request;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request, if any.
     */
    public function getUri()
    {
    	return $this->uri;
    }

    /**
     * Create a new instance with the provided URI.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param UriInterface $uri New request URI to use.
     * @return self
     */
    public function withUri(UriInterface $uri)
    {
    	$request = clone $this;
    	$request->uri = $uri;
    	return $request;
    }



	/**
	 * allowedMethod
	 * @param  string $method
	 * @return bool
	 */
    protected function allowedMethod($method)
    {
    	if (is_string($method) === false) {
    		return false;
    	}

    	if ($this->allowedMethods[strtoupper($method)] !== true) {
    		return false;
    	}

    	return true;
    }
}
