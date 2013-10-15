<?php

namespace debugchannel\clients\php;

use debugchannel\clients\php\D;
use debugchannel\clients\php\RHtmlSpanFormatter;
use debugchannel\clients\php\LanguageAgnosticParser;

class TSomething extends \stdclass
{
    public function whatever () {}
}

// class DTest extends \Bond\Normality\Tests\NormalityProvider
class DTest extends \PHPUnit_Framework_Testcase
{
	private $d;

	public function setup()
	{
        $this->d = new D(
            '192.168.2.158',
            'joseph/unitest',
            'joseph',
            [
                'showMethods' => true,
                'showPrivateMembers' => true,
                'expLvl' => 2
            ]
        );

	}

	public function powerset($items, $perms = array( )) {
    if (empty($items)) { 
        return $perms;
    }  else {
        for ($i = count($items) - 1; $i >= 0; --$i) {
             $newitems = $items;
             $newperms = $perms;
             list($foo) = array_splice($newitems, $i, 1);
             array_unshift($newperms, $foo);
             $this->powerset($newitems, $newperms);
         }
    }
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
		print_r(func_get_args());
		call_user_func_array([$this->d, "log"], func_get_args());
	}

    public function testLangAgnosticRefParser()
    {
    	$d = $this->d;

        foreach( range(1,20) as $n ) {
            $d->clear();
            $d($n);
            usleep(100000);
        }


//        $d($this);

//        $d->clear();

        $data = array(
            'handler' => 'unknown',
            'args' => array(
                "what",
                "what",
            )
        );
        $d->makeUberRequest($data);
        return;
        // $d($this);
        // $d->syntaxHighlight("select * from monkey");
        $d(1);

        return;

        $data = array(
            'handler' => 'slickGrid',
            'args' => [ 1 ]
        );

        $d->makeUberRequest($data);


    }


/*
    public function testLangAgnosticRefParser()
    {

        $d = new D(
            '192.168.2.17',
            'unittest/pete',
            [
                'showMethods' => true,
                'showPrivateMembers' => true,
                'expLvl' => 2
            ]
        );


        \ref::config('stylePath', false);
        \ref::config('scriptPath', false);

        $lap = new LanguageAgnosticParser( new RHtmlSpanFormatter() );

        $data = array(
            'handler' => 'php-ref',
            'args' => array(
                null,
                []
            )
        );


        $data['args'][0] = $lap->query($this->getNull());
        $d->makeUberRequest($data);
        $d(null);

        $data['args'][0] = $lap->query($this->getScalarNumeric());
        $d->makeUberRequest($data);
        $d(3);

        $data['args'][0] = $lap->query($this->getScalarString());
        $d->makeUberRequest($data);
        $d("hello world");

        $data['args'][0] = $lap->query($this->getSimpleObject());
        $d->makeUberRequest($data);
        $d(new TSomething());

    }

    public function testSomething()
    {

        $d = new D(
            '192.168.2.17',
            'unittest/pete',
            [
                'showMethods' => true,
                'showPrivateMembers' => true,
                'expLvl' => 2
            ]
        );


        $data = array(
            'handler' => 'tabularData',
            'args' => [
                [
                    [0,1,2,3,4,5],
                    [0,1,2,3,4,5],
                    [0,1,2,3,4,5],
                    [0,1,2,3,4,5],
                    [0,1,2,3,4,5],
                ]
            ]
        );

        $d->makeUberRequest($data);


        return;

        $d("fcuk");

        $d->syntaxHighlight( <<<SQL
            SELECT
                *
            FROM
                sometable
            WHERE
                something = true
SQL
        );

        return;

        $d->syntaxHighlight( <<<'SQL'
var $spanner = '23'
SQL
            , 'javascript'
        );


        return;

        $d( $this );

    }

    private function getNull()
    {
        $output = "null";
        return json_decode( $output, true );
    }

    private function getScalarNumeric()
    {
        $output = <<<JSON
        {
            "scalar": 3
        }
JSON;
        return json_decode( $output, true );
    }

    private function getScalarString()
    {
        $output = <<<JSON
        {
            "scalar": "hello world"
        }
JSON;
        return json_decode( $output, true );
    }

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