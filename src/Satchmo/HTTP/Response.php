<?php

namespace Satchmo\HTTP;

/**
 * Encapsulates the HTTP response to be sent back. Will output custom Satchmo HTML if needed.
 */
class Response
{
	const DEFAULT_ENCODING = "UTF-8";

	/**
	 * @var string
	 */
	protected $body;

	/**
	 * @var string
	 */
	protected $encoding;

	/**
	 * @var array<string>
	 */
	protected $headers;

	/**
	 * @var int
	 */
	protected $status_code;

	/**
	 * @var string
	 */
	protected $status_text;

	/**
	 * @param int $status_code OPTIONAL Defaults to `200`.
	 * @param boolean $read_all_buffers OPTIONAL Whether to get and clean the content of all open buffers.
	 *                                  Defaults to `true`.
	 */
	public static function createFromBuffer($status_code = 200, $read_all_buffers = true)
	{
		# Get our body from the buffer
		$body = "";
		while (($more_body = ob_get_clean()) !== false) {
			$body .= $more_body;

			if(!$read_all_buffers) break;
		}

		$response = new Response($body, $status_code);

		# Add all headers in the buffer and guess response encoding.
		$response->addSentHeaders();
		foreach ($response->getHeaders() as $header) {
			if(preg_match("/^Content-Type:.+;\\s*charset=(.+)$/i", $header, $matches) === 0)
				continue;

			$response->setEncoding($matches[1]);
		}

		return $response;
	}

	/**
	 * @param string $body OPTIONAL
	 * @param int $status_code OPTIONAL Defaults to `200`.
	 */
	public function __construct($body = null, $status_code = 200)
	{
		if($body !== null)
			$this->setBody($body);

		$this->setStatusCode($status_code);
		
		$this->headers = array();
		$this->encoding = self::DEFAULT_ENCODING;
	}

	/**
	 * Sends the response, outputting HTML when `$_POST["Satchmo__wrap"]` exists.
	 */
	public function send()
	{
		if(array_key_exists("Satchmo__wrap", $_POST)) {
			$writer = new Response\ResponseHTMLWriter;
			$writer->write($this);
		} else {
			print $this->getBody();
		}
	}

	/**
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}
	
	/**
	 * @param string $body
	 */
	public function setBody($body)
	{
		$this->body = (string) $body;
	}

	/**
	 * @return string
	 */
	public function getEncoding()
	{
		return $this->encoding;
	}
	
	/**
	 * @param string $encoding
	 */
	public function setEncoding($encoding)
	{
		$this->encoding = (string) $encoding;
	}

	/**
	 * @param string $header_string For example: `Content-Type: application/json`.
	 */
	public function addHeader($header_string)
	{
		$this->headers[] = $header_string;
	}

	/**
	 * Adds all headers in `$headers` to the headers.
	 *
	 * @param array<string> $headers
	 */
	public function addHeaders(array $headers)
	{
		$this->headers = array_merge($this->headers, array_values($headers));
	}

	/**
	 * Adds all headers ready to be sent to the browser.
	 */
	public function addSentHeaders()
	{
		$this->addHeaders(headers_list());
	}

	/**
	 * @return array<string>
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * @return int
	 */
	public function getStatusCode()
	{
		return $this->status_code;
	}
	
	/**
	 * @param int $status_code
	 */
	public function setStatusCode($status_code)
	{
		$this->status_code = (int) $status_code;
	}

	/**
	 * @return string
	 */
	public function getStatusText()
	{
		return $this->status_text;
	}
	
	/**
	 * @param string $status_text
	 */
	public function setStatusText($status_text)
	{
		$this->status_text = (string) $status_text;
	}
}