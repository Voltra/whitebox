<?php
namespace Test\Routing;

use GuzzleHttp\Psr7\Uri;
use phpmock\MockBuilder;
use PHPUnit\Framework\TestCase as PHPUnit;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use ReflectionClass;
use WhiteBox\Helpers\NamespaceResolver;
use WhiteBox\Routing\Route;
use WhiteBox\Routing\Router;

class RouterTest extends PHPUnit{
    public static $NAMESPACE;

    public static $location;

    public function setUp(){
        parent::setUp();
        self::$NAMESPACE = NamespaceResolver::getFrom(Router::class);
    }

    public static function mock(string $name, callable $functor, string $namespace = ""){
        return (new MockBuilder())
            ->setNamespace($namespace)
            ->setName($name)
            ->setFunction($functor)
            ->build();
    }


    /**
     * @test
     * @covers Router::urlFor
     */
    public function urlForNonExistingRoute(){
        $app = new Router();
        self::assertEquals(
            "",
            $app->urlFor("unit.test"),
            "Despite the fact that there's no route which such name, the URL is different from the empty string"
        );
    }

    /**
     * @test
     * @covers Router::urlFor
     */
    public function urlForExistingRoute(){
        $app = new Router();
        $app->get("/", function(){})->name("unit.test");

        self::assertEquals(
            "/",
            $app->urlFor("unit.test"),
            "Despite the fact that the route exists, the URL retrieved is incorrect"
        );
    }

    /**
     * @test
     * @covers Router::urlFor
     */
    public function urlWithParamsNonExistingRoute(){
        $app = new Router();
        self::assertEquals(
            "",
            $app->urlFor("unit.test", ["testID" => 42]),
            "Despite the fact that the route doesn't exist and that we gave it params, the URL retrieved is incorrect"
        );
    }

    /**
     * @test
     * @covers Router::urlFor
     */
    public function urlDontNeedParamsGaveParams(){
        $app = new Router();
        $app->get("/unit/test", function(){})->name("unit.test");
        self::assertEquals(
            "/unit/test",
            $app->urlFor("unit.test", ["testID" => 42])
        );
    }

    /**
     * @test
     * @covers Router::urlFor
     */
    public function urlWithParamsForExistingRoute(){
        $app = new Router();
        $app->get("/:testID", function(){})->name("unit.test");

        self::assertEquals(
            "/2ac",
            $app->urlFor("unit.test", ["testID" => "2ac"]),
            "Despite the fact that the route exists and a parameter was sent, the retrieved URL is incorrect"
        );
    }

    /**
     * @test
     * @covers Router::urlFor
     * @covers Router::redirect
     */
    public function redirectRedirects(){
        $mock = self::mock(
            "header",
            function(string $str){
                //return "{$str}";
                self::$location = $str;
            },
            self::$NAMESPACE
        );

        $mock->enable();
        $app = new Router();
        $app->redirect("/");
        self::assertEquals(
            "Location: /",
            self::$location,
            "The redirection fails : wrong url assigned"
        );
        $mock->disable();
    }

    /**
     * @test
     * @covers Router::urlFor
     * @covers Router::redirect
     * @covers Router::redirectTo
     */
    public function redirectsToWorksProperly(){
        $mock = self::mock(
            "header",
            function(string $str){
                //return "{$str}";
                self::$location = $str;
            },
            self::$NAMESPACE
        );
        $mock->enable();

        $app = new Router();
        $app->get("/", function(){})->name("unit.test");

        $app->redirectTo("unit.test");
        self::assertEquals(
            "Location: /",
            self::$location,
            "Something wrong happens while redirecting from a route's name"
        );

        $mock->disable();
    }

    /**
     * @test
     * @covers Router::hasRoute
     */
    public function routeExistHasRoute(){
        $app = new Router();
        $app->get("/", function(){});

        $Router = new ReflectionClass(Router::class);
        $hasRoute = $Router->getMethod("hasRoute");
        $hasRoute->setAccessible(true);
        self::assertTrue(
            $hasRoute->invokeArgs($app, ["GET", "/"]),
            "Even though we created the Route, it is not considered as existing in the Router"
        );
        $hasRoute->setAccessible(false);
    }

    /**
     * @test
     * @covers Router::hasRoute
     */
    public function routeDoesntExistDoesntHaveRoute(){
        $app = new Router();

        $Router = new ReflectionClass(Router::class);
        $hasRoute = $Router->getMethod("hasRoute");
        $hasRoute->setAccessible(true);
        self::assertFalse(
            $hasRoute->invokeArgs($app, ["GET", "/unit/test"]),
            "Even though the Route doesn't exist, it is considered as existing in the Router"
        );
        $hasRoute->setAccessible(false);
    }

    /**
     * @test
     * @covers Router::getRoute
     */
    public function getRouteIfDoesntExistReturnsNull(){
        $app = new Router();

        $Router = new ReflectionClass(Router::class);
        $getRoute = $Router->getMethod("getRoute");
        $getRoute->setAccessible(true);

        self::assertNull(
            $getRoute->invokeArgs($app, ["GET", "/unit/test"])
        );
    }

    /**
     * @test
     * @covers Router::getRoute
     */
    public function getRouteIfExistsReturnsCorrectRoute(){
        $app = new Router;
        $getRoute = (new ReflectionClass(Router::class))
            ->getMethod("getRoute");
        $getRoute->setAccessible(true);

        $app->get("/unit/test", function(){});
        $app->get("/unit/est", function(){});
        $app->post("/unit/test", function(){});

        $isCorrect = $getRoute
            ->invokeArgs($app, ["GET", "/unit/test"])
            ->equals(new Route("GET", "/unit/test", function(){}));

        self::assertTrue($isCorrect);
    }

    /**
     * @test
     * @covers Router::getRoutesForMethod
     * @dataProvider noRoutesForGivenMethodProvider
     *
     * @param array $routes
     */
    public function getRoutesForMethodReturnsEmptyArrayIfNone(string $method, array $routes){
        $app = new Router;
        $setupRoute = (new ReflectionClass(Router::class))
        ->getMethod("setupRoute");
        $setupRoute->setAccessible(true);

        array_walk($routes, function(array $route) use($app, $setupRoute){
            $setupRoute->invokeArgs($app, $route);
        });

        $getRoutesForMethod = (new ReflectionClass(Router::class))
            ->getMethod("getRoutesForMethod");
        $getRoutesForMethod->setAccessible(true);

        $result = $getRoutesForMethod->invoke($app, $method);
        self::assertEmpty($result);
    }

    public function noRoutesForGivenMethodProvider(): array{
        return [
            ["POST", [
                ["GET", "/unit/test", function(){}],
                ["GET", "/unit", function(){}],
                ["GET", "/test", function(){}],
            ]],
            ["GET", [
                ["POST", "/unit/test", function(){}],
                ["POST", "/unit", function(){}],
                ["POST", "/test", function(){}],
            ]]
        ];
    }

    /**
     * @test
     * @covers       Router::getRoutesForMethod
     * @dataProvider severalRoutesProvider
     * @param string $method
     * @param array $routes
     */
    public function getRoutesForMethodRetrieveCorrectOnes(string $method, array $routes){
        $correctRoutesFromParams = array_filter($routes, function(array $route) use($method){
            return $route[0] === $method;
        });
        $correctRoutesFromParams = array_map(function(array $route){
            return new Route(...$route);
        }, $correctRoutesFromParams);

        $app = new Router;
        $Router = new ReflectionClass(Router::class);
        $getRoutesForMethod = $Router->getMethod("getRoutesForMethod");
        $setupRoute = $Router->getMethod("setupRoute");
        $getRoutesForMethod->setAccessible(true);
        $setupRoute->setAccessible(true);

        array_walk($routes, function(array $route) use($app, $setupRoute){
            $setupRoute->invokeArgs($app, $route);
        });

        $result = $getRoutesForMethod->invoke($app, $method);

        $correct = array_reduce($correctRoutesFromParams, function(bool $acc, Route $route) use($result){
            return $acc && array_reduce($result, function(bool $ac, Route $r) use($route){
                return $ac || ($r->equals($route));
            }, false);
        }, true);

        self::assertTrue($correct);
    }

    public function severalRoutesProvider(): array{
        return [
            ["GET", [
                ["GET", "/unit/test", function(){}],
                ["POST", "/unit/test", function(){}],
                ["GET", "/unit", function(){}],
                ["GET", "/test", function(){}],
            ]],
            ["GET", [
                ["GET", "/unit/test", function(){}],
                ["GET", "/unit/test/admin", function(){}],
                ["GET", "/unit", function(){}],
                ["GET", "/test", function(){}],
            ]],
            ["POST", [
                ["GET", "/unit/test", function(){}],
                ["POST", "/unit/test", function(){}],
                ["POST", "/unit", function(){}],
                ["POST", "/test", function(){}],
            ]]
        ];
    }

    /**
     * @test
     * @covers Router::setupRoute
     */
    public function setupCreatesCorrectRoutesWithCorrectParameters(){
        $app = new Router();
        $app->get("/unit/test/:testID", function(){ return "unit"; }, function(){ return "test"; });

        $Router = new ReflectionClass(Router::class);
        $routes = $Router->getProperty("routes");
        $routes->setAccessible(true);

        $routesArray = $routes->getValue($app);
        $route = new Route(
            "GET",
            "/unit/test/:testID",
            function(){ return "unit"; },
            function(){ return "test"; }
        );

        $contains = array_reduce($routesArray, function($acc, $elem) use($route){
            return $acc || $route->equals($elem);
        }, false);

        self::assertTrue(
            $contains,
            "Even though the Route was supposedly added to this Router, it is not in its routes array"
        );
    }

    /**
     * @test
     * @covers Router::handleRequest
     */
    public function handleRequestDispatchesTo404IfNoMatch(){
        $app = new Router();
        $handleRequest = (new ReflectionClass(Router::class))
            ->getMethod("handleRequest");
        $handleRequest->setAccessible(true);
        $errMsg = "404";
        $app->error404(function() use($errMsg): string{
            return $errMsg;
        });

        self::assertEquals($errMsg, $handleRequest->invoke($app, new FakeRequest()));
    }
}

class FakeRequest implements ServerRequestInterface{
    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion() {
        return "1.1";
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return static
     */
    public function withProtocolVersion($version) {
        return $this;
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders() {
        return [];
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name) {
        return true;
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name) {
        return [];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name) {
        return "";
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value) {
        return $this;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value) {
        return $this;
    }

    /**
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name Case-insensitive header field name to remove.
     * @return static
     */
    public function withoutHeader($name) {
        return $this;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody() {
        return null;
    }

    /**
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body Body.
     * @return static
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body) {
        return $this;
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
    public function getRequestTarget() {
        return "";
    }

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-5.3 (for the various
     *     request-target forms allowed in request messages)
     * @param mixed $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget) {
        return $this;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod() {
        return "GET";
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param string $method Case-sensitive method.
     * @return static
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method) {
        return $this;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request.
     */
    public function getUri() {
        return new Uri("/unit/test");
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param UriInterface $uri New request URI to use.
     * @param bool $preserveHost Preserve the original state of the Host header.
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false) {
        return $this;
    }

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams() {
        return [];
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams() {
        return [];
    }

    /**
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * This method MUST NOT update the related Cookie header of the request
     * instance, nor related values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated cookie values.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     * @return static
     */
    public function withCookieParams(array $cookies) {
        return $this;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams() {
        return [];
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * These values SHOULD remain immutable over the course of the incoming
     * request. They MAY be injected during instantiation, such as from PHP's
     * $_GET superglobal, or MAY be derived from some other value such as the
     * URI. In cases where the arguments are parsed from the URI, the data
     * MUST be compatible with what PHP's parse_str() would return for
     * purposes of how duplicate query parameters are handled, and how nested
     * sets are handled.
     *
     * Setting query string arguments MUST NOT change the URI stored by the
     * request, nor the values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query string arguments.
     *
     * @param array $query Array of query string arguments, typically from
     *     $_GET.
     * @return static
     */
    public function withQueryParams(array $query) {
        return $this;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles() {
        return [];
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     * @return static
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles) {
        return $this;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     */
    public function getParsedBody() {
        return null;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *     typically be in an array or object.
     * @return static
     * @throws \InvalidArgumentException if an unsupported argument type is
     *     provided.
     */
    public function withParsedBody($data) {
        return $this;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes() {
        return [];
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     */
    public function getAttribute($name, $default = null) {
        return [];
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     * @return static
     */
    public function withAttribute($name, $value) {
        return $this;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @return static
     */
    public function withoutAttribute($name) {
        return $this;
    }
}
