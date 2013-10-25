<?php

namespace debugchannel {

    /**
     * PHP client for debugchannel
     */
    class DebugChannel
    {

        /**
         * default value for senderName when sending chat messages.
         * @const ANON_IDENtIFIER
         */
        const ANON_IDENTIFIER = 'PHP-client';
        const DESCRIPTIVE_IDENTIFIER = '__DESCRIPTIVE__';
        const NO_IDENTIFIER = '__NONE__';

        /**#@+
         * @access private
         */

        /**
         * Hostname of debug channel server
         *
         * eg. eg,  192.168.2.17, 127.0.0.1, localhost
         * Domain names or ip addresses can be used.
         *
         * @var string
         */
        private $host;

        /**
         * @var string Non empty string of the channel you wish to post to
         */
        private $channel;

        /**
         * @var string Apikey to use with a debug channel account. Optional.
         */
        private $apiKey;

        /**
         * See, Allowed options include the phpRef ones below
         */
        private $options = array(
            "showPrivateMembers" => true,
            "expLvl" => 1,
            "maxDepth" => 3
        );

        /**
         * List of the options that'll be passed to phpRef
         * @var array
         */
        private $phpRefOptionsAllowed = array('expLvl', 'maxDepth', 'showIteratorContents', 'showMethods', 'showPrivateMembers', 'showStringMatches');

        /**
         * Private static process identifier
         * @var string
         */
        private static $pid;

        /**
         * Private static machine identifier
         */
        private static $machineId;

        /**
         * Monotonically increasing seqence number for message
         * @var int
         */
        private static $messageSequenceNo;

        /**#@-*/

        /**#@+
         * @access public
         */

        /**
         * Create a DebugChannel object bound to a specific channel and server.
         *
         * options can be provided which customize how explore works.
         * the options available are:
         * <table>
         * <thead><tr>
         * <th align="left">Option</th>
         * <th align="left">Default</th>
         * <th align="left">Description</th>
         * </tr></thead>
         * <tbody>
         * <tr>
         * <td align="left"><code>'expLvl'</code></td>
         * <td align="left"><code>1</code></td>
         * <td align="left">Initially expanded levels (for HTML mode only). A negative value will expand all levels</td>
         * </tr>
         * <tr>
         * <td align="left"><code>'maxDepth'</code></td>
         * <td align="left"><code>6</code></td>
         * <td align="left">Maximum depth (<code>0</code> to disable); note that disabling it or setting a high value can produce a 100+ MB page when input involves large data</td>
         * </tr>
         * <tr>
         * <td align="left"><code>'showIteratorContents'</code></td>
         * <td align="left"><code>FALSE</code></td>
         * <td align="left">Display iterator data (keys and values)</td>
         * </tr>
         * <tr>
         * <td align="left"><code>'showResourceInfo'</code></td>
         * <td align="left"><code>TRUE</code></td>
         * <td align="left">Display additional information about resources</td>
         * </tr>
         * <tr>
         * <td align="left"><code>'showMethods'</code></td>
         * <td align="left"><code>TRUE</code></td>
         * <td align="left">Display methods and parameter information on objects</td>
         * </tr>
         * <tr>
         * <td align="left"><code>'showPrivateMembers'</code></td>
         * <td align="left"><code>FALSE</code></td>
         * <td align="left">Include private properties and methods</td>
         * </tr>
         * <tr>
         * <td align="left"><code>'showStringMatches'</code></td>
         * <td align="left"><code>TRUE</code></td>
         * <td align="left">Perform and display string matches for dates, files, json strings, serialized data, regex patterns etc. (SLOW)</td>
         * </tr>
         * <tr>
         * <td align="left"><code>'formatters'</code></td>
         * <td align="left"><code>array()</code></td>
         * <td align="left">Custom/external formatters (as associative array: format =&gt; className)</td>
         * </tr>
         * <tr>
         * <td align="left"><code>'shortcutFunc'</code></td>
         * <td align="left"><code>array('r', 'rt')</code></td>
         * <td align="left">Shortcut functions used to detect the input expression. If they are namespaced, the namespace must be present as well (methods are not  supported)</td>
         * </tr>
         * <tr>
         * <td align="left"><code>'stylePath'</code></td>
         * <td align="left"><code>'{:dir}/ref.css'</code></td>
         * <td align="left">Local path to a custom stylesheet (HTML only); <code>FALSE</code> means that no CSS is included.</td>
         * </tr>
         * <tr>
         * <td align="left"><code>'scriptPath'</code></td>
         * <td align="left"><code>'{:dir}/ref.js'</code></td>
         * <td align="left">Local path to a custom javascript (HTML only); <code>FALSE</code> means no javascript (tooltips / toggle / kbd shortcuts require JS)</td>
         * </tr>
         * </tbody>
         * </table>
         *
         * @param string $host  the string is the address of debug channel server
         * @param string $channel  the channel to publish all messages on
         * @param string $apiKey the apiKey of the user who is publishing the messages. default is null.
         * @param array $options  options array to configure the way explore traverses the object graph and renders it.
         *
         */
        public function __construct( $host, $channel, $apiKey = null, array $options = array() )
        {
            $this->host = (string) $host;
            $this->setChannel($channel);
            if( null !== $apiKey and !is_string($apiKey) ) {
                throw new \InvalidArgumentException("apiKey must be a string.");
            }
            $this->apiKey = $apiKey;
            $this->setOptions($options);
        }

        /**
         * provides getter methods for all properties private and public
         *
         * all properties will get a a getter method.
         * for exmaple the private property $name of type string
         * will get a getter method with the signature:
         * <pre><code>public function name() :: string</code></pre>
         *
         * @param string $property  The string which represents the name of the property to return.
         * @return mixed  Value of property.
         * @deprecated Use the explicit getter methods.
         * @throws \InvalidArgumentException when no property exists with the name.
         */
        public function __get( $property )
        {
            if( property_exists( $this, $property ) ) {
                return $this->$property;
            }
            throw new \InvalidArgumentException("Unknown property `{$property}`.");
        }

        /**
         * Set the channel you with to subscribe to
         *
         * the channel is the in form \w+[/\w+]* for example:
         * <ul>
         *   <li>hello/world</li>
         *   <li>logs</li>
         *   <li>project/team/chat</li>
         * </ul>
         *
         * @param string $channel  Channel to use
         * @return debugChannel\DebugChannel
         */
        public function setChannel( $channel )
        {
            $this->channel = ltrim( (string) $channel, '/' );
            return $this;
        }

        /**
         * set phpref options that will be used by this instance of D
         *
         * @param array $options  the associtivate array of options, available options specified in constructors documentation
         * @return debugChannel\DebugChannel
         */
        public function setOptions( array $options )
        {
            $this->options = array_merge($this->options, $options);
            return $this;
        }

        /**
         * gets the options set
         *
         * @return array   associative array of options mapping option name to option value
         */
        private function getPhpRefOptions()
        {
            $phpRefOptions = array_intersect_key(
                $this->options,
                array_flip( $this->phpRefOptionsAllowed )
            );
            $phpRefOptions['stylePath'] = false;
            $phpRefOptions['scriptPath'] = false;
            return $phpRefOptions;
        }

        /**
         * Get the debug server url
         *
         * Contains both the host and channel.
         *
         * @return string   the string is the url where the debugger can be accessed from
         */
        public function getRequestUrl()
        {
            return "http://{$this->host}:1025/{$this->channel}";
        }

        /**
         * Alias for ->explore().
         * @see debugchannel\DebugChannel
         */
        public function __invoke( $dataToLog, array $tags = array() )
        {
            return call_user_func(
                array( $this, 'log'),
                func_get_args()
            );
        }

        /**
         * Alias for ->explore().
         * @see debugchannel\DebugChannel
         */
        public function log( $dataToLog, array $tags = array() )
        {
            return call_user_func(
                array( $this, 'log'),
                func_get_args()
            );
        }

        /**
         * publishes an interactable object graph
         *
         * if val is an object or array it will generate an object graph.
         * if val is a primitive such as int, string, etc then it just displays the value.
         * It can detect recursion, replacing the reference with a "RECURSION" string.
         * $val is not modified.
         * @param mixed $val  the mixed value to publish
         * @return DebugChannel  the DebugChannel object bound to $this
         */
        public function explore( $dataToLog, array $tags = array() )
        {
            $originalRefOptions = $this->setRefConfig($this->getPhpRefOptions());

            // use the custom formatter which doesn't have the "multiple levels of nesting break out of their container' bug
            $ref = new Ref(new RHtmlSpanFormatter());

            ob_start();
            $ref->query( $dataToLog, null );
            $html = ob_get_clean();

            $this->makeRequest(
                array(
                    'handler' => 'php-ref',
                    'args' => array(
                        $html,
                    ),
                    'tags' => $tags,
                )
            );

            $this->setRefConfig($originalRefOptions);
            return $this;
        }

        /**
         * publishes the 2-dimensional array as a table
         *
         * given a 2-dimensional array, treat it as a table when rendering it.
         * In the browser it will be shown as a table, with the first dimension being
         * the rows, and the second dimension being columns.
         * The values of each cell should be primtives, ie string, int, etc but can be objects.
         * the exact method of displaying the objects is undefined hence it is advised that the
         * cells are primtives.
         *
         * @param array $table  a 2-dimensional array of values, where dimension 1 is rows, dimension 2 is columns
         * @return DebugChannel  the DebugChannel instance bound to $this
         */
        public function table(array $table)
        {
            //TODO - check this is two dimensional
            return $this->sendDebug('table', array($table));
        }

        /**
         * publishes a raw string as is
         *
         * the string is publishes as a plain string without formatting.
         * it cannot be null, and cannot be any other primtive such as int.
         *
         * @param string $text  the string to publish as raw text
         * @return DebugChannel the DebugChannel instance bound to $this.
         */
        public function string($text)
        {
            return $this->sendDebug('string', $text);
        }

        /**
         * publishes a string with syntax highlighting for the given language.
         *
         * the string is treaded as code and highlighed and formatted for that given language.
         * the complete list of languages that are supported are available <a href="https://github.com/isagalaev/highlight.js/tree/master/src/languages">here</a>.
         * this list includes:
         * <ul>
         *   <ui>bash</ui>
         *   <ui>cpp(c++)</ui>
         *   <ui>cs(c#)</ui>
         *   <ui>java</ui>
         *   <ui>javascript<ui>
         *   <ui>python</ui>
         *   <ui>php</ui>
         *   <ui>sql</ui>
         *   <ui>xml</ui>
         *   <ui>json</ui>
         * </ul>
         *
         * @param string $text  the string which contains the code to syntax highlight
         * @param string $lang  the string that represents the language the $text is in.
         * some languages will have a slight varient on what its called, ie c++ is cpp.
         * Default sql.
         * @param bool $deIndent  bool is true when you want the identation in the text to be ignored, false otherwise
         * @return DebugChannel  the DebugChannel instance bound to $this.
         */
        public function code( $text, $lang = 'sql', $deIndent = true )
        {
            if( $deIndent ) {
                $text = $this->deIndent($text);
            }
            $trace = $this->formatTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            return $this->sendDebug('syntaxHighlight', array($text, $lang, $trace));
        }

        /**
         * publishes an image to the browser.
         *
         * encodes an image in using base64 encoding to be rendered as an image resized to fit the debug box.
         * the image can be specified by its location in the filesystem or as a base64 encoded string.
         * the following file formats are allowed: jpg, bmp, and png.
         *
         * @param string $identifier  the string can be the location of the image in the filesystem either fully qualified or relative.
         * the string can also contain the image in base64 format.
         * @return DebugChannel  the DebugChannel instance bound to $this.
         */
        public function image($identifier)
        {
            assert(is_string($identifier));
            $base64 = file_exists($identifier) ? base64_encode(file_get_contents($identifier)) : $identifier;
            return $this->sendDebug('image', $base64);
        }


        /**
         * publishes a messages like a chat message in an IM client.
         *
         *
         * publishes the message text with a senders name attached.
         * the senderName can be anything, and  does not need to be the same on every consecutive call.
         *
         * @param string $message  the string containing the message to publish as IM message
         * @param string $senderName  the name of the sender that will be displayed next to the message. Default 'PHP-client'.
         * @return DebugChannel  the DebugChannel instance bound to $this.
         */
        public function chat($message, $senderName)
        {
            $senderName = $senderName ? $senderName : self::ANON_IDENTIFIER;

            return $this->sendDebug('chat', [$senderName, $message]);
        }

        /**
         * removes all debugs in the channel for all users
         *
         * can be called at any point, event if there are no debugs in the channel.
         * if multiple clients are publishing to the same channel, this will remove their debugs as well.
         * if multiple people are viewing the channel in browser then every user will be effected.
         *
         * @return DebugChannel  the DebugChannel instance bound to $this.
         */
        public function clear()
        {
            return $this->sendDebug('clear');
        }

        /**#@-*/

        private function sendDebug ($handler, $args = array(), $stacktrace = array())
        {
            $this->makeRequest(
                array(
                    'handler' => $handler,
                    'args' => is_array($args) ? $args : array($args),
                    'stacktrace' => $stacktrace
                )
            );
            return $this;
        }

        private function filloutRequest( array $data )
        {

            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $offset = 0;
            // this loop construct starts on the second element
            while( $working = next($trace) and isset($working['class']) and $working['class'] === __CLASS__ ) {
                $offset++;
            }
            // exclude all but the first call to __CLASS__, renumber array
            $data['trace'] = $this->formatTrace( array_slice($trace, $offset) );
            // tags are a required field
            $data['tags'] = isset($data['tags']) ? $data['tags'] : array();

            // add apiKey to request if set
            if( null !== $this->apiKey ) {
                $data['apiKey'] = (string) $this->apiKey;
            }

            // process id
            $data['info'] = $this->getInfoArray();

            return $data;

        }

        private function makeRequest( $data )
        {

            $data = $this->filloutRequest($data);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url = $this->getRequestUrl() );
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json') );
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data) );

            $response = curl_exec($ch);
            $curlInfo = curl_getinfo($ch);

            // have any problems
            if( $response === false ) {
                throw new \Exception("Unable to connect to debugger as `{$url}`");
            } elseif ( $curlInfo['http_code'] !== 200 ) {
                throw new \Exception($response);
            }

            return $curlInfo;

        }

        /**
         * Get client identifier
         * @return bool|string
         */
        private function getIdentifier()
        {
            switch( $options['identifier'] ) {
                case self::ANON_IDENTIFIER:
                    return 'anon';
                case self::DESCRIPTIVE_IDENTIFIER:
                    return 'descriptive';
                case self::NO_IDENTIFIER;
                    return false;
            }
            return $options['identifier'];
        }

        private function setRefConfig( array $options )
        {
            $output = array();
            foreach( $options as $option => $value ) {
                $output[$option] = ref::config($option);
                ref::config($option, $value);
            }
            return $output;
        }

        private function formatTrace( $trace )
        {
            return array_map(
                function ( $component ) {
                    if( isset($component['file'], $component['line']) and $component['line'] > 0 ) {
                        $location = sprintf( "%s(%s): ", $component['file'], $component['line'] );
                    } else {
                        $location = '';
                    }

                    $fn = isset( $component['class'] ) ? "{$component['class']}{$component['type']}" : '';
                    $fn .= "{$component['function']}()";

                    return array(
                        'location' => $location,
                        'fn' => $fn
                    );
                },
                $trace
             );
        }

        private function deIndent( $text )
        {
            $leadingWhitespace = array();
            $text = explode("\n", $text);
            foreach( $text as $line ) {
                if( !empty( $line ) ) {
                    $leadingWhitespace[] = strlen( $line ) - strlen( ltrim( $line ) );
                }
            }
            $indent = min( $leadingWhitespace );
            foreach( $text as &$line ) {
                $line = substr( $line, $indent );
            }
            return implode("\n", $text);
        }

        private function getInfoArray()
        {
            return array(
                'machineId' => $this->getMachineId(),
                'pid' => $this->getPid(),
                'sequenceNo' => ++self::$messageSequenceNo,
                'generationTime' => microtime(true),
            );
        }

        private function getPid()
        {
            // process information
            if( !isset(self::$pid) ) {
                // whatever this can change
                self::$pid = md5( microtime(). getmypid() );
            }
            return self::$pid;
        }

        private function getMachineId()
        {
            if( !isset(self::$machineId) ) {
                self::$machineId = php_uname('n');
            }
            return self::$machineId;
        }

    }
}
?>