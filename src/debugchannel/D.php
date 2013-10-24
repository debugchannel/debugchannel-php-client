<?php

namespace debugchannel;

use ref;

/**
 * PHP counterpart for uberdebug
 */
class D
{

    const ANON_IDENTIFIER = '__ANON__';
    const DESCRIPTIVE_IDENTIFIER = '__DESCRIPTIVE__';
    const NO_IDENTIFIER = '__NONE__';

    /**
     * @var string Hostname of the uberdebugging server. Think, 'localhost' or '192.168.2.17'
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

    /**
     * Standard constructor, blah blah
     * @param string Hostname
     * @param string Channel
     * @param array ref options. See, ref.php for list of allowed options
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
     * Clears the uberdebug window
     */
    public function clear()
    {
        $this->makeRequest(
            array(
                'handler' => 'clear',
                'args' => array()
            )
        );
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

    public function getTime()
    {
    	return microtime(true);
    }

    /**
     * Debug a arbritary number of objects
     *
     * @param mixed Item to debug
     * @param ...
     */
    public function log()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $this->makePhpRefCall( $trace, func_get_args() );
        return $this;
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

            $this->makeRequest(
                array(
                    'handler' => 'php-ref',
                    'args' => array(
                        $html,
                        $trace
                    ),
                    'stacktrace' => [],
                    'timestamp' => $this->getTime()
                )
            );
        }

        $this->setRefConfig($originalRefOptions);
    }

    /**
     * Syntax highlight a string
     *
     * @param string Text to highlight
     * @param string Language to highlight it as
     * @param bool Deindent string? This works well for sql
     */
    public function syntaxHighlight( $text, $lang = 'sql', $deIndent = true )
    {

        if( $deIndent ) {
            $text = $this->deIndent($text);
        }

        $trace = $this->formatTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));

        $this->makeRequest(
            array(
                'handler' => 'syntaxHighlight',
                'args' => array(
                    $text,
                    $lang,
                    $trace,
                )
            )
        );

    }

    public function makeRequest( $data )
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

?>