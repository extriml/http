<?php
/**
 * Uri
 * @package http
 * @subpackage  elise
 * @author Alex Orlov <mail@alexxorlovv.name>
 * @version 1.0.0
 * @since 2015-02-28
 * @license   MIT
 * @copyright  2015 extriml
 */
namespace elise\http;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    protected $schemeDelimiter = "://";
    
    protected $userInfoDelimiter = ":";

    protected $authorityDelimiter = "@";
    
    protected $portDelimiter =  ":";

    protected $userInfo = "";
    
    protected $host = "";
    
    protected $port;
    
    protected $path = "/";
    
    protected $query = "";
    
    protected $fragment = "";
    
    protected $scheme = "";



    public function __construct($uri = "")
    {   
        if (is_string($uri) === false) {
            throw new \InvalidArgumentException("URI passed to constructor must be a string; received");
        }

        if(empty($uri) === false) {
            $this->parse($uri);
        }
    }




    /**
     * Retrieve the URI scheme.
     *
     * Implementations SHOULD restrict values to "http", "https", or an empty
     * string but MAY accommodate other schemes if required.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The string returned MUST omit the trailing "://" delimiter if present.
     *
     * @return string The scheme of the URI.
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Retrieve the authority portion of the URI.
     *
     * The authority portion of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * This method MUST return an empty string if no authority information is
     * present.
     *
     * @return string Authority portion of the URI, in "[user-info@]host[:port]"
     *     format.
     */
    public function getAuthority()
    {
        if (empty($this->host)) {
            return "";
        }

        $authority = $this->host;
        if (!empty($this->userInfo)) {
            $authority = $this->userInfo.$this->authorityDelimiter.$authority;
        }

        if ($this->isStandartPort($this->scheme, $this->host, $this->port) === false) {
            $authority .= $this->portDelimiter.$this->port;
        }

        return $authority;
    }

    /**
     * Retrieve the user information portion of the URI, if present.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * Implementations MUST NOT return the "@" suffix when returning this value.
     *
     * @return string User information portion of the URI, if present, in
     *     "username[:password]" format.
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * Retrieve the host segment of the URI.
     *
     * This method MUST return a string; if no host segment is present, an
     * empty string MUST be returned.
     *
     * @return string Host segment of the URI.
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Retrieve the port segment of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard port
     * used with the current scheme, this method SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int The port for the URI.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Retrieve the path segment of the URI.
     *
     * This method MUST return a string; if no path is present it MUST return
     * an empty string.
     *
     * @return string The path segment of the URI.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Retrieve the query string of the URI.
     *
     * This method MUST return a string; if no query string is present, it MUST
     * return an empty string.
     *
     * The string returned MUST omit the leading "?" character.
     *
     * @return string The URI query string.
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Retrieve the fragment segment of the URI.
     *
     * This method MUST return a string; if no fragment is present, it MUST
     * return an empty string.
     *
     * The string returned MUST omit the leading "#" character.
     *
     * @return string The URI fragment.
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Create a new instance with the specified scheme.
     *
     * This method MUST retain the state of the current instance, and return
     * a new instance that contains the specified scheme. If the scheme
     * provided includes the "://" delimiter, it MUST be removed.
     *
     * Implementations SHOULD restrict values to "http", "https", or an empty
     * string but MAY accommodate other schemes if required.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param string $scheme The scheme to use with the new instance.
     * @return self A new instance with the specified scheme.
     * @throws \InvalidArgumentException for invalid or unsupported schemes.
     */
    public function withScheme($scheme)
    {
        $scheme = str_replace("://","",strtolower($scheme));
        if($scheme !== "http" and $scheme !== "https") {
            throw new \InvalidArgumentException("invalid or unsupported schemes.");
        }

        $new = clone $this;
        $new->scheme = $scheme;
        return $new;
    }

    /**
     * Create a new instance with the specified user information.
     *
     * This method MUST retain the state of the current instance, and return
     * a new instance that contains the specified user information.
     *
     * Password is optional, but the user information MUST include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * @param string $user User name to use for authority.
     * @param null|string $password Password associated with $user.
     * @return self A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {
        $userInfo = (string) $user;

        if(is_null($password) === false) {
            $userInfo .= $this->userInfoDelimiter.$password;
        }

        $new = clone $this;
        $new->userInfo = $userInfo;
        return $new;
    }

    /**
     * Create a new instance with the specified host.
     *
     * This method MUST retain the state of the current instance, and return
     * a new instance that contains the specified host.
     *
     * An empty host value is equivalent to removing the host.
     *
     * @param string $host Hostname to use with the new instance.
     * @return self A new instance with the specified host.
     * @throws \InvalidArgumentException for invalid hostnames.
     */
    public function withHost($host)
    {
        $new = clone $this;
        $new->host = strtolower($host);
        return $new;
    }

    /**
     * Create a new instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return
     * a new instance that contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int $port Port to use with the new instance; a null value
     *     removes the port information.
     * @return self A new instance with the specified port.
     * @throws \InvalidArgumentException for invalid ports.
     */
    public function withPort($port)
    {
        if ($port !== null) {
            $port = (int) $port;
            if($port > 65535 or $port < 1) {
                throw new \InvalidArgumentException("for invalid ports.");
                
            }
        }
        
        $new = clone $this;
        $new->port = $port;
        return $new;
    }

    /**
     * Create a new instance with the specified path.
     *
     * This method MUST retain the state of the current instance, and return
     * a new instance that contains the specified path.
     *
     * The path MUST be prefixed with "/"; if not, the implementation MAY
     * provide the prefix itself.
     *
     * An empty path value is equivalent to removing the path.
     *
     * @param string $path The path to use with the new instance.
     * @return self A new instance with the specified path.
     * @throws \InvalidArgumentException for invalid paths.
     */
    public function withPath($path)
    {
        if (! is_string($path)) {
            throw new \InvalidArgumentException(
                'Invalid path provided; must be a string'
            );
        }
        if (strpos($path, '?') !== false) {
            throw new \InvalidArgumentException(
                'Invalid path provided; must not contain a query string'
            );
        }
        if (strpos($path, '#') !== false) {
            throw new \InvalidArgumentException(
                'Invalid path provided; must not contain a URI fragment'
            );
        }

        $path = trim($path,"/");
        $path = "/".$path."/";
        $new = clone $this;
        $new->path = $path;
        return $new;
    }

    /**
     * Create a new instance with the specified query string.
     *
     * This method MUST retain the state of the current instance, and return
     * a new instance that contains the specified query string.
     *
     * If the query string is prefixed by "?", that character MUST be removed.
     * Additionally, the query string SHOULD be parseable by parse_str() in
     * order to be valid.
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param string $query The query string to use with the new instance.
     * @return self A new instance with the specified query string.
     * @throws \InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query)
    {
        $query = (string) $query;
        if (!empty($query)) {
            $query = ltrim($query,"?");

            $params = array();
            parse_str($query,$params);
            if (sizeof($params) === 0) {
                throw new \InvalidArgumentException("invalid query strings.");
            }
        }

        $new = clone $this;
        $new->query = $query;
        return $new; 
    }

    /**
     * Create a new instance with the specified URI fragment.
     *
     * This method MUST retain the state of the current instance, and return
     * a new instance that contains the specified URI fragment.
     *
     * If the fragment is prefixed by "#", that character MUST be removed.
     *
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param string $fragment The URI fragment to use with the new instance.
     * @return self A new instance with the specified URI fragment.
     */
    public function withFragment($fragment)
    {
        $fragment = (string) $fragment;
        $fragment = ltrim($fragment,"#");


        $new = clone $this;
        $new->fragment = $fragment;
        return $new;
    }

    /**
     * Return the string representation of the URI.
     *
     * Concatenates the various segments of the URI, using the appropriate
     * delimiters:
     *
     * - If a scheme is present, "://" MUST append the value.
     * - If the authority information is present, that value will be
     *   concatenated.
     * - If a path is present, it MUST be prefixed by a "/" character.
     * - If a query string is present, it MUST be prefixed by a "?" character.
     * - If a URI fragment is present, it MUST be prefixed by a "#" character.
     *
     * @return string
     */
    public function __toString()
    {
    	return $this->builder();
    }


    /**
     * Uri build string
     * @return string
     */
    public function builder()
    {
        $build = "";
        if (!empty($this->scheme)) {
            $build .= $this->scheme.$this->schemeDelimiter;
        }
        $build .= $this->getAuthority();

        $build .= $this->path;

        if(!empty($this->query)) {
            $build .= "?".$this->query;
        }

        if(!empty($this->fragment)) {
            $build .= "#".$this->fragment;
        }

        return $build;
    }   

    /**
     * Parsing uri
     * @param  string $uri
     * @return null
     */
    public function parse($uri)
    {   
        $parsed = parse_url($uri);
        

        if ($parsed === false) {
            throw new InvalidArgumentException("Ivalid uri parameter");
        }

       

        $this->scheme =   (string) $parsed['scheme'];
        $this->host =     (string) $parsed['host'];
        $this->port =     (string) $parsed['port'];
        $this->path =     (string) isset($parsed['path']) ? $parsed['path'] : "/";
        $this->query =    (string) $parsed['query'];
        $this->fragment = (string) $parsed['fragment'];

        if (isset($parsed['user'])) {
            $userInfo = $parsed['user'];
            if (isset($parsed['pass'])) {
                $userInfo .= $this->userInfoDelimiter.$parsed['pass'];
            }
            $this->userInfo = $userInfo;
        }



    }

    /**
     * Port is standart?
     * @param  string  $scheme
     * @param  string  $host
     * @param  int  $port   
     * @return boolean         
     */
    public function isStandartPort($scheme, $host, $port)
    {
        

        if (empty($this->port)) {
            return true;
        }

        if(getservbyname($this->scheme,'tcp') !== $this->port) {
            return false;
        }

        return true;

    }

}