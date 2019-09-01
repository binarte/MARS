<?php
namespace mars;

class System
{

    /**
     * Continue
     *
     * The server, has received the request headers and the client should proceed to send the request body (in the
     * case of a request for which a body needs to be sent; for example, a POST request). Sending a large request body
     * to a server after a request has been rejected for inappropriate headers would be inefficient. To have a server
     * check the request's headers, a client must send Expect: 100-continue as a header in its initial request and
     * receive a 100 Continue status code in response before sending the body. If the client receives an error code
     * such as 403 (Forbidden) or 405 (Method Not Allowed) then it shouldn't send the request's body. The response 417
     * Expectation Failed indicates that the request should be repeated without the Expect header as it indicates that
     * the server doesn't support expectations (this is the case, for example, of HTTP/1.0 servers).
     *
     * @var integer
     */
    const HS_Continue = 100;

    /**
     * Switching Protocols
     *
     * The requester has asked the server to switch protocols and the server has agreed to do so.
     *
     * @var integer
     */
    const HS_SwitchingProtocols = 101;

    /**
     * Processing (WebDAV; RFC 2518)
     *
     * A WebDAV request may contain many sub-requests involving file operations, requiring a long time to complete the
     * request. This code indicates that the server has received and is processing the request, but no response is
     * available yet. This prevents the client from timing out and assuming the request was lost.
     *
     * @var integer
     */
    const HS_Processing = 102;

    /**
     * Early Hints (RFC 8297)
     *
     * Used to return some response headers before final HTTP message.
     *
     * @var integer
     */
    const HS_EarlyHints = 103;

    /**
     * OK
     *
     * Standard response for successful HTTP requests. The actual response will depend on the request method used. In
     * a GET request, the response will contain an entity corresponding to the requested resource. In a POST request,
     * the response will contain an entity describing or containing the result of the action.
     *
     * @var integer
     */
    const HS_Ok = 200;

    /**
     * Created
     *
     * The request has been fulfilled, resulting in the creation of a new resource.
     *
     * @var integer
     */
    const HS_Created = 201;

    /**
     * Accepted
     *
     * The request has been accepted for processing, but the processing has not been completed. The request might or
     * might not be eventually acted upon, and may be disallowed when processing occurs.
     *
     * @var integer
     */
    const HS_Accepted = 202;

    /**
     * Non-Authoritative Information (since HTTP/1.1)
     *
     * The server is a transforming proxy (e.g. a Web accelerator) that received a 200 OK from its origin, but is
     * returning a modified version of the origin's response.
     *
     * @var integer
     */
    const HS_NonAuthoritativeInformation = 203;

    /**
     * No Content
     *
     * The server successfully processed the request and is not returning any content.
     *
     * @var integer
     */
    const HS_NoContent = 204;

    /**
     * Reset Content
     *
     * The server successfully processed the request, but is not returning any content. Unlike a 204 response, this
     * response requires that the requester reset the document view.
     *
     * @var integer
     */
    const HS_ResetContent = 205;

    /**
     * Partial Content (RFC 7233)
     *
     * The server is delivering only part of the resource (byte serving) due to a range header sent by the client. The
     * range header is used by HTTP clients to enable resuming of interrupted downloads, or split a download into
     * multiple simultaneous streams.
     *
     * @var integer
     */
    const HS_PartialContent = 206;

    /**
     * Multi-Status (WebDAV; RFC 4918)
     *
     * The message body that follows is by default an XML message and can contain a number of separate response codes,
     * depending on how many sub-requests were made.
     *
     * @var integer
     */
    const HS_MultiStatus = 207;

    /**
     * Already Reported (WebDAV; RFC 5842)
     *
     * The members of a DAV binding have already been enumerated in a preceding part of the (multistatus) response,
     * and are not being included again.
     *
     * @var integer
     */
    const HS_AlreadyReported = 208;

    /**
     * IM Used (RFC 3229)
     *
     * The server has fulfilled a request for the resource, and the response is a representation of the result of one
     * or more instance-manipulations applied to the current instance.
     *
     * @var integer
     */
    const HS_ImUsed = 226;

    /**
     * Multiple Choices
     *
     * Indicates multiple options for the resource from which the client may choose (via agent-driven content
     * negotiation). For example, this code could be used to present multiple video format options, to list files with
     * different filename extensions, or to suggest word-sense disambiguation.
     *
     * @var integer
     */
    const HS_MultipleChoices = 300;

    /**
     * Moved Permanently
     *
     * This and all future requests should be directed to the given URI.
     *
     * @var integer
     */
    const HS_MovedPermanently = 301;

    /**
     * Found (Previously "Moved temporarily")
     *
     * Tells the client to look at (browse to) another URL. 302 has been superseded by 303 and 307. This is an example
     * of industry practice contradicting the standard. The HTTP/1.0 specification (RFC 1945) required the client to
     * perform a temporary redirect (the original describing phrase was "Moved Temporarily"), but popular browsers
     * implemented 302 with the functionality of a 303 See Other. Therefore, HTTP/1.1 added status codes 303 and 307
     * to distinguish between the two behaviours. However, some Web applications and frameworks use the 302 status
     * code as if it were the 303.
     *
     * @var integer
     */
    const HS_Found = 302;

    /**
     * See Other (since HTTP/1.1)
     *
     * The response to the request can be found under another URI using the GET method. When received in response to a
     * POST (or PUT/DELETE), the client should presume that the server has received the data and should issue a new
     * GET request to the given URI.
     *
     * @var integer
     */
    const HS_SeeOther = 303;

    /**
     * Not Modified (RFC 7232)
     *
     * Indicates that the resource has not been modified since the version specified by the request headers
     * If-Modified-Since or If-None-Match. In such case, there is no need to retransmit the resource since the client
     * still has a previously-downloaded copy.
     *
     * @var integer
     */
    const HS_NotModified = 304;

    /**
     * Use Proxy (since HTTP/1.1)
     *
     * The requested resource is available only through a proxy, the address for which is provided in the response.
     * For security reasons, many HTTP clients (such as Mozilla Firefox and Internet Explorer) do not obey this status
     * code.
     *
     * @var integer
     */
    const HS_UseProxy = 305;

    /**
     * Switch Proxy
     *
     * No longer used. Originally meant "Subsequent requests should use the specified proxy."
     *
     * @var integer
     */
    const HS_SwitchProxy = 306;

    /**
     * Temporary Redirect (since HTTP/1.1)
     *
     * In this case, the request should be repeated with another URI; however, future requests should still use the
     * original URI. In contrast to how 302 was historically implemented, the request method is not allowed to be
     * changed when reissuing the original request. For example, a POST request should be repeated using another POST
     * request.
     *
     * @var integer
     */
    const HS_TemporaryRedirect = 307;

    /**
     * Permanent Redirect (RFC 7538)
     *
     * The request and all future requests should be repeated using another URI. 307 and 308 parallel the behaviors of
     * 302 and 301, but do not allow the HTTP method to change. So, for example, submitting a form to a permanently
     * redirected resource may continue smoothly.
     *
     * @var integer
     */
    const HS_PermanentRedirect = 308;

    /**
     * Bad Request
     *
     * The server cannot or will not process the request due to an apparent client error (e.g., malformed request
     * syntax, size too large, invalid request message framing, or deceptive request routing).
     *
     * @var integer
     */
    const HS_BadRequest = 400;

    /**
     * Unauthorized (RFC 7235)
     *
     * Similar to 403 Forbidden, but specifically for use when authentication is required and has failed or has not yet
     * been provided. The response must include a WWW-Authenticate header field containing a challenge applicable to
     * the requested resource. See Basic access authentication and Digest access authentication. 401 semantically
     * means "unauthorised", the user does not have valid authentication credentials for the target resource.
     *
     * Note: Some sites incorrectly issue HTTP 401 when an IP address is banned from the website (usually the website
     * domain) and that specific address is refused permission to access a website.
     *
     * @var integer
     */
    const HS_Unauthorized = 401;

    /**
     * Payment Required
     *
     * Reserved for future use. The original intention was that this code might be used as part of some form of
     * digital cash or micropayment scheme, as proposed, for example, by GNU Taler, but that has not yet happened, and
     * this code is not usually used. Google Developers API uses this status if a particular developer has exceeded
     * the daily limit on requests. Sipgate uses this code if an account does not have sufficient funds to start a
     * call. Shopify uses this code when the store has not paid their fees and is temporarily disabled.
     *
     * @var integer
     */
    const HS_PaymentRequired = 402;

    /**
     * Forbidden
     *
     * The request was valid, but the server is refusing action. The user might not have the necessary permissions for
     * a resource, or may need an account of some sort. This code is also typically used if the request provided
     * authentication via the WWW-Authenticate header field, but the server did not accept that authentication.
     *
     * @var integer
     */
    const HS_Forbidden = 403;

    /**
     * Not Found
     *
     * The requested resource could not be found but may be available in the future. Subsequent requests by the client
     * are permissible.
     *
     * @var integer
     */
    const HS_NotFound = 404;

    /**
     * Method Not Allowed
     *
     * A request method is not supported for the requested resource; for example, a GET request on a form that
     * requires data to be presented via POST, or a PUT request on a read-only resource.
     *
     * @var integer
     */
    const HS_MethodNotAllowed = 405;

    /**
     * Not Acceptable
     *
     * The requested resource is capable of generating only content not acceptable according to the Accept headers
     * sent in the request. See Content negotiation.
     *
     * @var integer
     */
    const HS_NotAcceptable = 406;

    /**
     * Proxy Authentication Required (RFC 7235)
     *
     * The client must first authenticate itself with the proxy.
     *
     * @var integer
     */
    const HS_ProxyAuthenticationRequired = 407;

    /**
     * Request Timeout
     *
     * The server timed out waiting for the request. According to HTTP specifications: "The client did not produce a
     * request within the time that the server was prepared to wait. The client MAY repeat the request without
     * modifications at any later time."
     *
     * @var integer
     */
    const HS_RequestTimeout = 408;

    /**
     * Conflict
     *
     * Indicates that the request could not be processed because of conflict in the current state of the resource,
     * such as an edit conflict between multiple simultaneous updates.
     *
     * @var integer
     */
    const HS_Conflict = 409;

    /**
     * Gone
     *
     * Indicates that the resource requested is no longer available and will not be available again. This should be
     * used when a resource has been intentionally removed and the resource should be purged. Upon receiving a 410
     * status code, the client should not request the resource in the future. Clients such as search engines should
     * remove the resource from their indices. Most use cases do not require clients and search engines to purge the
     * resource, and a "404 Not Found" may be used instead.
     *
     * @var integer
     */
    const HS_Gone = 410;

    /**
     * Length Required
     *
     * The request did not specify the length of its content, which is required by the requested resource.
     *
     * @var integer
     */
    const HS_LengthRequired = 411;

    /**
     * Precondition Failed (RFC 7232)
     *
     * The server does not meet one of the preconditions that the requester put on the request header fields.
     *
     * @var integer
     */
    const HS_PreconditionFailed = 412;

    /**
     * Payload Too Large (RFC 7231)
     *
     * The request is larger than the server is willing or able to process. Previously called "Request Entity Too
     * Large".
     *
     * @var integer
     */
    const HS_PayloadTooLarge = 413;

    /**
     * URI Too Long (RFC 7231)
     *
     * The URI provided was too long for the server to process. Often the result of too much data being encoded as a
     * query-string of a GET request, in which case it should be converted to a POST request. Called "Request-URI Too
     * Long" previously.
     *
     * @var integer
     */
    const HS_UriTooLong = 414;

    /**
     * Unsupported Media Type (RFC 7231)
     *
     * The request entity has a media type which the server or resource does not support. For example, the client
     * uploads an image as image/svg+xml, but the server requires that images use a different format.
     *
     * @var integer
     */
    const HS_UnsupportedMediaType = 415;

    /**
     * Range Not Satisfiable (RFC 7233)
     *
     * The client has asked for a portion of the file (byte serving), but the server cannot supply that portion. For
     * example, if the client asked for a part of the file that lies beyond the end of the file. Called "Requested
     * Range Not Satisfiable" previously.
     *
     * @var integer
     */
    const HS_RangeNotSatisfiable = 416;

    /**
     * Expectation Failed
     *
     * The server cannot meet the requirements of the Expect request-header field.
     *
     * @var integer
     */
    const HS_ExpectationFailed = 417;

    /**
     * I'm a teapot (RFC 2324, RFC 7168)
     *
     * This code was defined in 1998 as one of the traditional IETF April Fools' jokes, in RFC 2324, Hyper Text Coffee
     * Pot Control Protocol, and is not expected to be implemented by actual HTTP servers. The RFC specifies this code
     * should be returned by teapots requested to brew coffee. This HTTP status is used as an Easter egg in some
     * websites, including Google.com.
     *
     * @var integer
     */
    const HS_ImATeapot = 418;

    /**
     * Misdirected Request (RFC 7540)
     *
     * The request was directed at a server that is not able to produce a response (for example because of connection
     * reuse).
     *
     * @var integer
     */
    const HS_MisdirectedRequest = 421;

    /**
     * Unprocessable Entity (WebDAV; RFC 4918)
     *
     * The request was well-formed but was unable to be followed due to semantic errors.
     *
     * @var integer
     */
    const HS_UnprocessableEntity = 422;

    /**
     * Locked (WebDAV; RFC 4918)
     *
     * The resource that is being accessed is locked.
     *
     * @var integer
     */
    const HS_Locked = 423;

    /**
     * Failed Dependency (WebDAV; RFC 4918)
     *
     * The request failed because it depended on another request and that request failed (e.g., a PROPPATCH).
     *
     * @var integer
     */
    const HS_FailedDependency = 424;

    /**
     * Too Early (RFC 8470)
     *
     * Indicates that the server is unwilling to risk processing a request that might be replayed.
     *
     * @var integer
     */
    const HS_TooEarly = 425;

    /**
     * Upgrade Required
     *
     * The client should switch to a different protocol such as TLS/1.0, given in the Upgrade header field.
     *
     * @var integer
     */
    const HS_UpgradeRequired = 426;

    /**
     * Precondition Required (RFC 6585)
     *
     * The origin server requires the request to be conditional. Intended to prevent the 'lost update' problem, where
     * a client GETs a resource's state, modifies it, and PUTs it back to the server, when meanwhile a third party has
     * modified the state on the server, leading to a conflict.
     *
     * @var integer
     */
    const HS_PreconditionRequired = 428;

    /**
     * Too Many Requests (RFC 6585)
     *
     * The user has sent too many requests in a given amount of time. Intended for use with rate-limiting schemes.
     *
     * @var integer
     */
    const HS_TooManyRequests = 429;

    /**
     * Request Header Fields Too Large (RFC 6585)
     *
     * The server is unwilling to process the request because either an individual header field, or all the header
     * fields collectively, are too large.
     *
     * @var integer
     */
    const HS_RequestHeaderFieldsTooLarge = 431;

    /**
     * Unavailable For Legal Reasons (RFC 7725)
     *
     * A server operator has received a legal demand to deny access to a resource or to a set of resources that
     * includes the requested resource. The code 451 was chosen as a reference to the novel Fahrenheit 451 (see the Acknowledgements in the RFC).
     *
     * @var integer
     */
    const HS_UnavailableForLegalReasons = 451;

    /**
     * Internal Server Error
     *
     * A generic error message, given when an unexpected condition was encountered and no more specific message is
     * suitable.
     *
     * @var integer
     */
    const HS_InternalServerError = 500;

    /**
     * Not Implemented
     *
     * The server either does not recognize the request method, or it lacks the ability to fulfil the request. Usually
     * this implies future availability (e.g., a new feature of a web-service API).
     *
     * @var integer
     */
    const HS_NotImplemented = 501;

    /**
     * Bad Gateway
     *
     * The server was acting as a gateway or proxy and received an invalid response from the upstream server.
     *
     * @var integer
     */
    const HS_BadGateway = 502;

    /**
     * Service Unavailable
     *
     * The server cannot handle the request (because it is overloaded or down for maintenance). Generally, this is a
     * temporary state.
     *
     * @var integer
     */
    const HS_ServiceUnavailable = 503;

    /**
     * Gateway Timeout
     *
     * The server was acting as a gateway or proxy and did not receive a timely response from the upstream server.
     *
     * @var integer
     */
    const HS_GatewayTimeout = 504;

    /**
     * HTTP Version Not Supported
     *
     * The server does not support the HTTP protocol version used in the request.
     *
     * @var integer
     */
    const HS_HttpVersionNotSupported = 505;

    /**
     * Variant Also Negotiates (RFC 2295)
     *
     * Transparent content negotiation for the request results in a circular reference.
     *
     * @var integer
     */
    const HS_VariantAlsoNegotiates = 506;

    /**
     * Insufficient Storage (WebDAV; RFC 4918)
     *
     * The server is unable to store the representation needed to complete the request.
     *
     * @var integer
     */
    const HS_InsufficientStorage = 507;

    /**
     * Loop Detected (WebDAV; RFC 5842)
     *
     * The server detected an infinite loop while processing the request (sent instead of 208 Already Reported).
     *
     * @var integer
     */
    const HS_LoopDetected = 508;

    /**
     * Not Extended (RFC 2774)
     *
     * Further extensions to the request are required for the server to fulfil it.
     *
     * @var integer
     */
    const HS_NotExtended = 510;

    /**
     * Network Authentication Required (RFC 6585)
     *
     * The client needs to authenticate to gain network access. Intended for use by intercepting proxies used to
     * control access to the network (e.g., "captive portals" used to require agreement to Terms of Service before
     * granting full Internet access via a Wi-Fi hotspot).
     *
     * @var integer
     */
    const HS_NetworkAuthenticationRequired = 511;

    /**
     *
     * @param $code int
     *            status code to get message from
     */
    static function httpStatusMessage($code)
    {
        switch ($code) {
            case self::HS_Continue:
                return 'Continue';
            case self::HS_SwitchingProtocols:
                return 'Switching Protocols';
            case self::HS_Processing:
                return 'Processing';
            case self::HS_EarlyHints:
                return 'Early Hints';
            case self::HS_Ok:
                return 'OK';
            case self::HS_Created:
                return 'Created';
            case self::HS_Accepted:
                return 'Accepted';
            case self::HS_NonAuthoritativeInformation:
                return 'Non-Authoritative Information';
            case self::HS_NoContent:
                return 'No Content';
            case self::HS_ResetContent:
                return 'Reset Content';
            case self::HS_PartialContent:
                return 'Partial Content';
            case self::HS_MultiStatus:
                return 'Multi-Status';
            case self::HS_AlreadyReported:
                return 'Already Reported';
            case self::HS_ImUsed:
                return 'IM Used';
            case self::HS_MultipleChoices:
                return 'Multiple Choices';
            case self::HS_MovedPermanently:
                return 'Moved Permanently';
            case self::HS_Found:
                return 'Found';
            case self::HS_SeeOther:
                return 'See Other';
            case self::HS_NotModified:
                return 'Not Modified';
            case self::HS_UseProxy:
                return 'Use Proxy';
            case self::HS_SwitchProxy:
                return 'Switch Proxy';
            case self::HS_TemporaryRedirect:
                return 'Temporary Redirect';
            case self::HS_PermanentRedirect:
                return 'Permanent Redirect';
            case self::HS_BadRequest:
                return 'Bad Request';
            case self::HS_Unauthorized:
                return 'Unauthorized';
            case self::HS_PaymentRequired:
                return 'Payment Required';
            case self::HS_Forbidden:
                return 'Forbidden';
            case self::HS_NotFound:
                return 'Not Found';
            case self::HS_MethodNotAllowed:
                return 'Method Not Allowed';
            case self::HS_NotAcceptable:
                return 'Not Acceptable';
            case self::HS_ProxyAuthenticationRequired:
                return 'Proxy Authentication Required';
            case self::HS_RequestTimeout:
                return 'Request Timeout';
            case self::HS_Conflict:
                return 'Conflict';
            case self::HS_Gone:
                return 'Gone';
            case self::HS_LengthRequired:
                return 'Length Required';
            case self::HS_PreconditionFailed:
                return 'Precondition Failed';
            case self::HS_PayloadTooLarge:
                return 'Payload Too Large';
            case self::HS_UriTooLong:
                return 'URI Too Long';
            case self::HS_UnsupportedMediaType:
                return 'Unsupported Media Type';
            case self::HS_RangeNotSatisfiable:
                return 'Range Not Satisfiable';
            case self::HS_ExpectationFailed:
                return 'Expectation Failed';
            case self::HS_ImATeapot:
                return 'I\'m a teapot';
            case self::HS_MisdirectedRequest:
                return 'Misdirected Request';
            case self::HS_UnprocessableEntity:
                return 'Unprocessable Entity';
            case self::HS_Locked:
                return 'Locked';
            case self::HS_FailedDependency:
                return 'Failed Dependency';
            case self::HS_TooEarly:
                return 'Too Early';
            case self::HS_UpgradeRequired:
                return 'Upgrade Required';
            case self::HS_PreconditionRequired:
                return 'Precondition Required';
            case self::HS_TooManyRequests:
                return 'Too Many Requests';
            case self::HS_RequestHeaderFieldsTooLarge:
                return 'Request Header Fields Too Large';
            case self::HS_UnavailableForLegalReasons:
                return 'Unavailable For Legal Reasons';
            case self::HS_InternalServerError:
                return 'Internal Server Error';
            case self::HS_NotImplemented:
                return 'Not Implemented';
            case self::HS_BadGateway:
                return 'Bad Gateway';
            case self::HS_ServiceUnavailable:
                return 'Service Unavailable';
            case self::HS_GatewayTimeout:
                return 'Gateway Timeout';
            case self::HS_HttpVersionNotSupported:
                return 'HTTP Version Not Supported';
            case self::HS_VariantAlsoNegotiates:
                return 'Variant Also Negotiates';
            case self::HS_InsufficientStorage:
                return 'Insufficient Storage';
            case self::HS_LoopDetected:
                return 'Loop Detected';
            case self::HS_NotExtended:
                return 'Not Extended';
            case self::HS_NetworkAuthenticationRequired:
                return 'Network Authentication Required';
        }
        return null;
    }

    private static $logDir;

    static function setLogDir($path)
    {
        $path = rtrim($path, '/') . '/';
        self::$logDir = $path;
    }

    private static $classPaths = [];

    static function addClassPath($path)
    {
        $path = rtrim($path, '/') . '/';
        self::$classPaths[] = $path;
    }

    static function loadClass($class)
    {
        $cpath = str_Replace('\\', '/', $class) . '.php';
        
        foreach (self::$classPaths as $path) {
            $file = $path . $cpath;
            if (file_exists($file)) {
                require_once ($file);
                if (class_exists($class, false) or interface_exists($class, false)) {
                    return true;
                }
            }
        }
        
        throw new ClassLoadException($class, ClassLoadException::FileNotFound);
    }

    static function handleUncaughtException(\Throwable $ex)
    {
        $code = $ex->getCode();
        if ($code == 0) {
            $code = self::HS_InternalServerError;
        }
        $msg = self::httpStatusMessage($code);
        if (! $msg) {
            $code = self::HS_InternalServerError;
            $msg = self::httpStatusMessage($code);
        }
        
        if ($code >= self::HS_InternalServerError){
            self::logException($ex);
        }
        $h = 'HTTP/2.0 '.$code.' '.$msg;
        header ($h);
        echo ($h);
        die;
    }
    
    static function logException(\Throwable $ex){
 
        function addToXml(\SimpleXMLElement $to, $what, $name = null)
        {
            $item = $to->addChild('item');
            if (is_int($name)) {
                $item['i'] = $name;
            } elseif ($name) {
                $item['name'] = str_replace("\0", '\\000', $name);
            }
            
            if (is_Object($what)) {
                $item['class'] = get_class($what);
                foreach ((array) $what as $name => $value) {
                    addToXml($item, $value, str_replace("\0", '-', $name));
                }
            } else {
                $item['type'] = getType($what);
                if (is_Bool($what)) {
                    $item['value'] = $what ? 'true' : 'false';
                } elseif (is_array($what)) {
                    foreach ($what as $name => $value) {
                        addToXml($item, $value, $name);
                    }
                } elseif (is_string($what)) {
                    $item[0] = addcslashes($what, "\0");
                } elseif (! is_null($what)) {
                    $item['value'] = (string) $what;
                }
            }
        }
        
        $trace = $ex->getTrace();
        $line = $ex->getLine();
        $file = $ex->getFile();
        $log = new \SimpleXMLElement('<LogEntry/>');
        $log['date'] = (new \DateTime())->format(\DateTime::W3C);
        $log['class'] = get_Class($ex);
        $log['file'] = $file;
        $log['line'] = $line;
        $log['code'] = $ex->getCode();
        $log->message = $ex->getMessage();
        if ($ex instanceof DatabaseException) {
            $log->sql = $ex->getSql();
        }
        
        foreach ($trace as $tr) {
            if ($file == @$tr['file'] and $line == @$tr['line']) {
                continue;
            }
            if (! $log->backTrace) {
                $log->addChild('backTrace');
            }
            $txml = $log->backTrace->addChild('trace');
            $txml['file'] = @$tr['file'];
            $txml['line'] = @$tr['line'];
            if (@$tr['class']) {
                $txml['class'] = $tr['class'];
            }
            $txml['function'] = @$tr['function'];
            if (isset($tr['args'])) {
                foreach ($tr['args'] as $arg) {
                    if (! $txml->args) {
                        $txml->addChild('args');
                    }
                    addToXml($txml->args, $arg);
                }
            }
        }
        
        if (function_exists('\\getAllHeaders')) {
            $svars = [];
            foreach ($_SERVER as $name => $var) {
                if (stripos($name, 'HTTP_') !== 0) {
                    $svars[$name] = $var;
                }
            }
            $headers = \getAllHeaders();
        } else {
            $svars = $_SERVER;
            $headers = [];
        }
        
        foreach (self::$classPaths as $path) {
            $log->classPaths->path[] = $path;
        }
        
        foreach ([
            'get' => @$_GET,
            'post' => @$_POST,
            'files' => @$_FILES,
            'cookie' => @$_COOKIE,
            'session' => @$_SESSION,
            'headers' => $headers,
            'server' => $svars
        ] as $src => $values)
            if ($values)
                foreach ($values as $param => $value) {
                    if (! $log->params->$src) {
                        if (! $log->params) {
                            $log->addChild('params');
                        }
                        $log->params->addChild($src);
                    }
                    addToXml($log->params->$src, $value, $param);
                }
        
        if (! is_dir(self::$logDir))
            mkdir(self::$logDir, 0755, true);
        
        //$log = $log->saveXML();
        if (self::$sessionDB) {
            self::$sessionDB->rollback();
            self::$sessionDB->logError($log);
        } else {
            echo $log;
            die();
        }
    }

    static function handleError($errno, $errstr, $errfile, $errline)
    {
        if (\error_reporting() & $errno) {
            throw new \ErrorException($errstr, 500, $errno, $errfile, $errline);
        }
    }

    private static $sessionDB;

    static function getSessionDb()
    {
        return self::$sessionDB;
    }

    /**
     * The user authenticated in this session
     *
     * @var User
     */
    private static $sessionUser;

    static function initSession(Database $db)
    {
        \set_Exception_Handler(__CLASS__ . '::handleUncaughtException');
        \set_Error_Handler(__CLASS__ . '::handleError');
        \date_default_timezone_set('utc');
        
        foreach ($db->classPaths() as $path) {
            System::addClassPath($path);
        }
        
        self::$sessionDB = $db;
        $defuser = $db->setting('session.defaultUser', '#guest');
        $cookieAuth = $db->setting('session.useCookieAuth', false);
        $auth = $db->setting('session.authCookie', 'Auth');
        $userid = $db->setting('session.userIdCookie', 'UserId');
        $cPath = $db->setting('session.path', '/');
        $cDomain = $db->setting('session.domain', $_SERVER['SERVER_NAME'], null, null, true);
        $cExpire = $db->setting('session.expire', 0, 0);
        
        if ($cExpire != 0) {
            $cExpire += time();
        }
        
        $uid = (int) @$_COOKIE[$userid];
        ObjectBase::setDefaultDb($db);
        $authCookie = false;
        if ($uid) {
            
            self::$sessionUser = new User($db);
            try {
                self::$sessionUser->open($uid, true);
                if (self::$sessionUser->username != '#guest' and self::$sessionUser->username != $defuser) {
                    if ($cookieAuth) {
                        $data = @$_COOKIE[$auth];
                        $data = json_decode($data, true);
                        self::$sessionUser->auth($_SERVER['REQUEST_METHOD'], $data);
                        $authCookie = $data;
                    } else {
                        $data = @$_SERVER['PHP_AUTH_DIGEST'];
                        $data = self::parseDigest($data);
                        self::$sessionUser->auth($_SERVER['REQUEST_METHOD'], $data);
                    }
                }
            } catch (\Exception $ex) {
                setcookie($auth, '', - 1, $cPath, $cDomain, false, true);
                setcookie($userid, '', - 1, $cPath, $cDomain, false, true);
                throw $ex;
            }
        } else {
            if ($cookieAuth) {
                if (@$_POST['_ACTION'] == 'login') {
                    $data = $_POST['credentials'];
                    self::$sessionUser = User::login($_SERVER['REQUEST_METHOD'], $data);
                    $authCookie = $data;
                }
            } elseif (@$_SERVER['PHP_AUTH_DIGEST']) {
                $data = @$_SERVER['PHP_AUTH_DIGEST'];
                $data = self::parseDigest($data);
                self::$sessionUser = User::login($_SERVER['REQUEST_METHOD'], $data);
            }
            
            if (! self::$sessionUser) {
                self::$sessionUser = new User($db);
                if (! self::$sessionUser->open([
                    'username' => $defuser
                ], false)) {
                    self::$sessionUser->username = $defuser;
                    self::$sessionUser->save();
                }
            }
        }
        if ($authCookie !== false) {
            setcookie($auth, json_encode($authCookie), $cExpire, $cPath, $cDomain, false, true);
        }
        setcookie($userid, self::$sessionUser->id, $cExpire, $cPath, $cDomain, false, true);
    }
}