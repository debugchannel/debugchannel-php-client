<?php

namespace debugchannel;

use debugchannel\D;
use debugchannel\RHtmlSpanFormatter;
use debugchannel\LanguageAgnosticParser;

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

    public function powerset (array $items)
    {
        if(count($items) == 0) return [[]];

        $newitems = array_values($items);
        $first = array_shift($newitems);
        $permutations = $this->powerset($newitems);
        return array_merge(
            $permutations,
            array_map(
                    function($set)use($first){return array_merge($set, [$first]);},
                    $permutations
                )
        );
    }


    public function provideValidLogObjects()
    {
        $items = [
            0,
            33.3,
            -1,
            "",
            "0",
            "\\\\",
            array(),
            array(-1 => 0),
            new \stdclass(),
            $this
        ];
        $permutations = $this->powerset($items);
        return $permutations;
    }

    /** @dataProvider provideValidLogObjects */
    public function testLogIntegerDoesNotThrowException()
    {
        // print_r(func_get_args());
        call_user_func_array([$this->d, "log"], func_get_args());
    }

    public function testInvoke()
    {
        $this->d->__invoke("testInvoke");
    }

    public function testCode()
    {
        $this->d->code('SELECT * FROM something;');
    }

    public function testTable()
    {
        $table = [
                [0,1,2,3,4,5],
                [0,1,2,3,4,5],
                ['<',"<div>",2,3,4,5],
            ];
        $this->d->table($table);
    }

    public function testImage()
    {
        $this->d->image('testImage.png');
    }

    public function testChat()
    {
        $this->d->chat('Hi', 'Pete');
    }

    public function testChatAnon()
    {
        $this->d->chat('Hi');
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