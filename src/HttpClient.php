<?php

namespace Youthweb\Api;

use Exception;
use GuzzleHttp;

/**
 * Http client based on GuzzleHttp
 */
class HttpClient extends GuzzleHttp\Client implements HttpClientInterface { }
