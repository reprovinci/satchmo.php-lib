<?php

namespace Satchmo\HTTP\Response;

use Satchmo\HTTP\Response;
use DOMDocument;

/**
 * Writes a Satchmo HTTP response as an HTML document.
 */
class ResponseHTMLWriter
{
	/**
	 * @param Satchmo\HTTP\Response $response
	 * @return string
	 */
	public function write(Response $response, $http_version = "1.1")
	{
		$status_code = $response->getStatusCode();
		$status_text = $response->getStatusText();
		if ($status_text === null) {
			$status_text_attribute = "";
		} else {
			$status_text_html = htmlentities($status_text);
			$status_text_attribute = " status-text=\"$status_text_html\"";
		}

		ob_start();

		print "<!DOCTYPE html>";
		print "<body status=\"$status_code\"$status_text_attribute>";

		$headers = $response->getHeaders();
		if (is_array($headers) && count($headers) > 1) {
			print "<ul>";

			foreach ($headers as $header)
				print  "<li>".htmlentities($header)."</li>";

			print "</ul>";
		}

		print "<pre>".htmlentities($response->getBody())."</pre>";

		print "</body>";

		$html = ob_get_clean();

		# Write output!
		header("HTTP/$http_version 200 OK");
		header("Content-Type: text/html; charset=".$response->getEncoding());
		print $html;
	}
}