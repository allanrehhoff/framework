<?php
/**
 * Backed enum for HTTP request methods
 */
namespace Http {
	enum Method: string {
		case GET = "GET";
		case POST = "POST";
		case HEAD = "HEAD";
		case PUT = "PUT";
		case DELETE = "DELETE";
		case PATCH = "PATCH";
		case TRACE = "TRACE";
		case CONNECT = "CONNECT";
		case OPTIONS = "OPTIONS";
	}
}