<?php

namespace debugchannel\clients\php;

use debugchannel\clients\php\D;
use debugchannel\clients\php\RHtmlSpanFormatter;
use debugchannel\clients\php\LanguageAgnosticParser;

// class DTest extends \Bond\Normality\Tests\NormalityProvider
class DTest extends \PHPUnit_Framework_Testcase
{

    const DEFAULT_HOST = 'localhost';
    const DEFAULT_CHANNEL = 'phpunit';
    const DEFAULT_API_KEY = null;

    private $d;

    public function __construct()
    {

        $options = [
            'showMethods' => true,
            'showPrivateMembers' => true,
            'expLvl' => 2
        ];

        $this->d = new D(
            self::DEFAULT_HOST,
            self::DEFAULT_CHANNEL,
            self::DEFAULT_API_KEY,
            $options
        );

    }

    public function testClear()
    {
        $this->d->log("cleared");
        $this->d->clear();
    }

    public function testLog()
    {
        $this->d->log("testLog");
    }

    public function testInvoke()
    {
        $this->d->__invoke("testInvoke");
    }

    public function testStyntaxHightlight()
    {
        $this->d->syntaxHighlight('SELECT * FROM something;');
    }

    public function testTable()
    {
        $data = array(
            'handler' => 'table',
            'args' => [
                [0,1,2,3,4,5],
                [0,1,2,3,4,5],
                ['<',"<div>",2,3,4,5],
            ]
        );
        $this->d->makeRequest($data);
    }

    public function testImage()
    {
        $data = array(
            'handler' => 'image',
            'args' => [
                base64_encode(file_get_contents(__DIR__.'/testImage.png'))
            ]
        );
        $this->d->makeRequest($data);
    }

    public function testUnknownHandler()
    {
        $this->d->makeRequest(
            array(
                "is this a valid request" => false,
                "who" => __CLASS__,
            )
        );
    }

    public function testChat()
    {
        $this->d->makeRequest(
            array(
                'handler' => 'chat',
                'args' => [
                    'Pete',
                    "Hi."
                ]
            )
        );
    }

    public function testChatAnon()
    {
        $this->d->makeRequest(
            array(
                'handler' => 'chat',
                'args' => [
                    "Hi.",
                ]
            )
        );
    }


/*
    private function getSimpleObject()
    {
        $output = <<<JSON
        {
            "methods": {
                "getClass": [],
                "registerNatives": [],
                "clone": [],
                "equals": [
                    "java.lang.Object"
                ],
                "notify": [],
                "hashCode": [],
                "finalize": [],
                "toString": [],
                "wait": [
                    "long",
                    "int"
                ],
                "notifyAll": []
            },
            "static": {},
            "constants": {},
            "class": [
                "java.lang.Object",
                "java.lang.Object"
            ],
            "properties": {}
        }
JSON;
        return json_decode( $output, true );
    }
    */

}