<?php
/**
 * Client
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

use elise\http\Request;

class Client
{
	protected $request;
	public function __construct($request = null)
	{
		if($request !== null and ($request instanceof Request)) {
			$this->request = $request;
		}
	}

	public function send()
	{

	}


}