#LDL Http Router

## A router based on phroute (for now)

LDL Http Router is a type of URL router which uses a chain of responsibility for pre, main, and post dispatchers.

### Single "controller" (Dispatcher)

Routes consist of a main dispatcher class, which (ideally) contains a single dispatch method 
(plus needed dependencies injected via __construct or other methods).

By having just a "single controller" class with the dispatch method, all other logic such as user validation or other 
pre conditions can be simplified in a single pre dispatcher and reused among other routes.

### Response parsers

Route dispatchers return data, just like any regular PHP method, and they are not allowed to modify the response body.
For that, route dispatchers exist, which basically take in all the data from a request (pre, main and post dispatchers)
and transform it to a string representation.

By default, this router focuses on REST type responses and thus, the results from the entire route
will be JSON encoded, however other types of responses can be provided, for example using straight HTML.

Please see the <a href="https://github.com/pthreat/ldl-http-router-template">Template response parser</a> plugin
if you need any kind of templating, this plugin makes it possible to use any kind of templating engine you desire.

### Pre and post dispatchers

These are actions that come before (pre) or after (post) the main dispatcher execution.

Pre and post dispatchers can be specified at: 

- Router level (all routes will apply said pre and post dispatchers)
- Route level (only one or more routes implement some pre and post dispatch handlers) 

All pre and post dispatchers are part of a middleware chain collection, each element of this collection has:

- A namespace and a name, with this in mind, no dispatchers compete with other dispatchers in the HTTP Response
- A priority, which establishes the order of execution of the middleware chain
- An active flag, to enable or disable a dispatcher 

### Exception handlers

Exception handlers are used when an exception is thrown from any type of dispatcher (be it pre, main or post)
with exception handlers the developer can act on certain types of exceptions thrown and be able to provide a proper 
HTTP Response code, plus doing other actions, such as error logging, etc. 
   
If an exception for which no exception handler exists is thrown, then the exception will simply throw a fatal error 
as expected.

Exception handlers can also be applied at: 

- A router level (for example, when a route is not found)
- A route level (for example, when you want to handle a custom exception on a single route)

### Route configuration file

You can add any route you like to the router object manually, however this is discouraged.
For this purpose you can add a routes.json configuration file with your own route definitions.

<a href="https://github.com/pthreat/ldl-http-router/blob/master/example/routes.json">Check this example</a> to know how to define a route in  a json configuration file.

### Useful plugins

- <a href="https://github.com/pthreat/ldl-http-router-auth">Authentication plugin</a>
- <a href="https://github.com/pthreat/ldl-http-router-cache">Cache plugin</a>
- <a href="https://github.com/pthreat/ldl-http-router-schema">Schema/Parameter validation plugin</a> 
- <a href="https://github.com/pthreat/ldl-http-router-template">Template response parser</a>

### Examples

<a href="https://github.com/pthreat/ldl-http-router/blob/master/example">Make sure to check the examples at the example folder</a>

### TODO

- Add other config file parsers such as YML or XML
- Add more documentation regarding routes.json

### Bugs

If you'd like to report a bug, please open an issue here at our github page