<?php
/**
 * Response
 * @package http
 * @subpackage  elise
 * @author Alex Orlov <mail@alexxorlovv.name>
 * @version 1.0.0
 * @since 2015-02-28
 * @license   MIT
 * @copyright  2015 extriml
 */
namespace elise\http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamableInterface;
use elise\http\Message;
use elise\http\Streamable;

class Response extends Message implements ResponseInterface
{

  protected $phrases = array(
        //Informational 1xx
        100 => '100 Continue',
        101 => '101 Switching Protocols',
        //Successful 2xx
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        203 => '203 Non-Authoritative Information',
        204 => '204 No Content',
        205 => '205 Reset Content',
        206 => '206 Partial Content',
        226 => '226 IM Used',
        //Redirection 3xx
        300 => '300 Multiple Choices',
        301 => '301 Moved Permanently',
        302 => '302 Found',
        303 => '303 See Other',
        304 => '304 Not Modified',
        305 => '305 Use Proxy',
        306 => '306 (Unused)',
        307 => '307 Temporary Redirect',
        //Client Error 4xx
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        402 => '402 Payment Required',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        406 => '406 Not Acceptable',
        407 => '407 Proxy Authentication Required',
        408 => '408 Request Timeout',
        409 => '409 Conflict',
        410 => '410 Gone',
        411 => '411 Length Required',
        412 => '412 Precondition Failed',
        413 => '413 Request Entity Too Large',
        414 => '414 Request-URI Too Long',
        415 => '415 Unsupported Media Type',
        416 => '416 Requested Range Not Satisfiable',
        417 => '417 Expectation Failed',
        418 => '418 I\'m a teapot',
        422 => '422 Unprocessable Entity',
        423 => '423 Locked',
        426 => '426 Upgrade Required',
        428 => '428 Precondition Required',
        429 => '429 Too Many Requests',
        431 => '431 Request Header Fields Too Large',
        //Server Error 5xx
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Timeout',
        505 => '505 HTTP Version Not Supported',
        506 => '506 Variant Also Negotiates',
        510 => '510 Not Extended',
        511 => '511 Network Authentication Required'
    );

	protected $statusCode = 200;

	protected $reasonPhrase;


    public function __construct($body = 'php://memory', $status = 200, array $headers = array())
    {
        if(is_int($status) === true and $this->validateStatus($status)) {
            $this->statusCode = $status;
        } else {
            throw new \InvalidArgumentException("Invalid argument status provider.");
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
     * Gets the response Status-Code.
     *
     * The Status-Code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return integer Status code.
     */
    public function getStatusCode()
    {
    	return (int) $this->statusCode;
    }

    /**
     * Create a new instance with the specified status code, and optionally
     * reason phrase, for the response.
     *
     * If no Reason-Phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * Status-Code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param integer $code The 3-digit integer result code to set.
     * @param null|string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return self
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = null)
    {
        $this->validateStatus($code);
        $response = clone $this;
        $response->statusCode   = (int) $code;
        $response->reasonPhrase = $reasonPhrase;
        return $response;    	
    }

    /**
     * Gets the response Reason-Phrase, a short textual description of the Status-Code.
     *
     * Because a Reason-Phrase is not a required element in a response
     * Status-Line, the Reason-Phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * Status-Code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string|null Reason phrase, or null if unknown.
     */
    public function getReasonPhrase()
    {
    	if (is_string($this->phrases[$this->statusCode]) === true and is_null($this->reasonPhrase) === true) {
    		$this->reasonPhrase = $this->phrases[$this->statusCode];
    	}
    	return $this->reasonPhrase;
    }



 /**
     * Validate a status code.
     *
     * @param int|string $code
     * @throws InvalidArgumentException on an invalid status code.
     */
    private function validateStatus($code)
    {
        if (! is_numeric($code)
            || is_float($code)
            || $code < 100
            || $code >= 600
        ) {
            throw new InvalidArgumentException(sprintf(
                'Invalid status code "%s"; must be an integer between 100 and 599, inclusive',
                (is_scalar($code) ? $code : gettype($code))
            ));
        }
    }    
}