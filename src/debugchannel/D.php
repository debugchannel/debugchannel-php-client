<?php

namespace debugchannel {

    use ref;

    /**
     * PHP counterpart for uberdebug
     */
    class D
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
         * address of debug channel server 
         * 
         * potential values would be localhost, 192.168.2.17, 127.0.0.1.
         * either domain names or ip addresses can be used.
         *
         * @var string 
         * 
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
            'includeSequence' => true,
        );

        /**
         * List of the options that'll be passed to phpRef
         * @var array
         */
        private $phpRefOptionsAllowed = ['expLvl', 'maxDepth', 'showIteratorContents', 'showMethods', 'showPrivateMembers', 'showStringMatches' ];

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

        /**
         * Create a D object bound to a specific channel and server.
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
         * @access public
         * @param string $host  the string is the address of debug channel server
         * @param string $channel  the channel to publish all messages on
         * @param string $apiKey the apiKey of the user who is publishing the messages. default is null.
         * @param array $options  options array to configure the way explore traverses the object graph and renders it.
         *
         */
        public function __construct( $host, $channel, $apiKey = null, array $options = ["showPrivateMembers" => true, "expLvl" => 3] )
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
         * Magic getter.
         * @param string propertyName
         * @return mixed
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
         * @param string Channel use use
         * @return Bond\D
         */
        public function setChannel( $channel )
        {
            $this->channel = ltrim( (string) $channel, '/' );
            return $this;
        }

        /**
         * Set phpref options that will be used by this instance of D
         * @param array
         * @return Bond\D
         */
        public function setOptions( array $options )
        {
            $this->options = $options;
            return $this;
        }

        /**
         * Get options to pass to phpref
         * @return array
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
         * Get the debug request url
         * @return string The url where the debugger can be accessed from
         */
        public function getRequestUrl()
        {
            return "http://{$this->host}:1025/{$this->channel}";
        }

        /**
         * Handy shortcut fo ->log().
         */
        public function __invoke()
        {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $this->makePhpRefCall( $trace, func_get_args() );
            return $this;
        }


        /**
         * Debug a arbritary number of objects
         *
         * @param mixed Item to debug
         * @param ...
         */
        public function log()
        {
            foreach (func_get_args() as $arg) {
                $this->explore($arg);
            }
        }

        /**
         * publishes an interactable object graph
         *
         * if val is an object or array it will generate an object graph.
         * if val is a primitive such as int, string, etc then it just displays the value. 
         * It can detect recursion, replacing the reference with a "RECURSION" string.
         * $val is not modified.
         * @access public
         * @param mixed $val  the mixed value to publish
         * @return D  the D object bound to $this
         */
        public function explore($val)
        {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $this->makePhpRefCall( $trace, [$val]);
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
         * @access public
         * @param array $table  a 2-dimensional array of values, where dimension 1 is rows, dimension 2 is columns
         * @return D the D instance bound to $this
         */
        public function table(array $table)
        {
            return $this->sendDebug('table', [$table]);
        }

        /**
         * publishes a raw string as is
         *
         * the string is publishes as a plain string without formatting.
         * it cannot be null, and cannot be any other primtive such as int. 
         *
         * @access public
         * @param string $text  the string to publish as raw text
         * @return D the D instance bound to $this.
         */
        public function string($text)
        {
            return $this->sendDebug('string', text);
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
         * @access public
         * @param string $text  the string which contains the code to syntax highlight
         * @param string $lang  the string that represents the language the $text is in. 
         * some languages will have a slight varient on what its called, ie c++ is cpp.
         * Default sql.
         * @param bool $deIndent  bool is true when you want the identation in the text to be ignored, false otherwise
         * @return D  the instance of D that $this is bound to.
         */
        public function code( $text, $lang = 'sql', $deIndent = true )
        {
            if( $deIndent ) {
                $text = $this->deIndent($text);
            }
            $trace = $this->formatTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            return $this->sendDebug('syntaxHighlight', [$text, $lang, $trace]);
        }

        /** 
         * publishes an image to the browser.
         *
         * encodes an image in using base64 encoding to be rendered as an image resized to fit the debug box.
         * the image can be specified by its location in the filesystem or as a base64 encoded string.
         * the following file formats are allowed: jpg, bmp, and png.
         * 
         * @access public
         * @param string $identifier  the string can be the location of the image in the filesystem either fully qualified or relative. 
         * the string can also contain the image in base64 format.
         * @return D  the instance of D that $this is bound to.
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
         * @access public
         * @param string $message  the string containing the message to publish as IM message
         * @param string $senderName  the name of the sender that will be displayed next to the message
         * @return D  the D instance bound to $this
         */
        public function chat($message, $senderName="php-client")
        {
            return $this->sendDebug('chat', [$senderName, $message]);
        }

        /**
         * removes all debugs in the channel for all users
         * 
         * can be called at any point, event if there are no debugs in the channel.
         * if multiple clients are publishing to the same channel, this will remove their debugs as well.
         * if multiple people are viewing the channel in browser then every user will be effected.
         *
         * @access public
         * @return D  the instance of D bound to $this
         */
        public function clear()
        {
            return $this->sendDebug('clear');
        }


        private function makePhpRefCall( array $trace, array $args )
        {

            $trace = $this->formatTrace($trace);
            $originalRefOptions = $this->setRefConfig($this->getPhpRefOptions());

            // use the custom formatter which doesn't have the "multiple levels of nesting break out of their container' bug
            $ref = new ref(new RHtmlSpanFormatter());

            foreach( $args as $arg ) {

                ob_start();
                $ref->query( $arg, null );
                $html = ob_get_clean();

                $this->sendDebug('php-ref', [$html, $trace]);
            }

            $this->setRefConfig($originalRefOptions);
        }

        private function sendDebug ($handler, $args=[], $stacktrace=[])
        {
            $this->makeRequest(
                array(
                    'handler' => $handler,
                    'args' => is_array($args) ? $args : [$args],
                    'stacktrace' => $stacktrace,
                    'timestamp' => $this->getTime()
                )
            );
            return $this;
        }

        private function makeRequest( $data )
        {
            // add apiKey to request if set
            if( null !== $this->apiKey ) {
                $data['apiKey'] = (string) $this->apiKey;
            }

            // process id
            $data['info'] = $this->getInfoArray();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url = $this->getRequestUrl() );
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json'] );
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

        private function getTime()
        {
            return microtime(true);
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