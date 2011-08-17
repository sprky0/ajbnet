<?php

/**
 * MimeTypes
 * Your one stop shop for mimetypes.
 * 
 * @package AJBnet
 * @subpackage Library
 * @author Avery Brooks
 * @copyright 2009
 * 
 */
class MimeTypes {

	// Image Formats
	const JPEG  = "image/jpeg";
	const JPG   = MimeTypes::JPG;
	const GIF   = "image/gif";
	const PNG   = "image/png";
	const PNG8  = MimeTypes::PNG;
	const PNG24 = "image/png24";

	// Tab Based Formats
	const HTML  = "text/html";
	const XML   = "text/xml";
	const XHTML = MimeTypes::XML;

	// Plaintext Languages
	const TXT   = "text/plain";
	const TEXT  = MimeTypes::TXT;
	const JSON  = "application/json";
	const CSV   = "text/csv";

}

?>