<?php

namespace debugchannel {


    class DebugChannelTest extends \PHPUnit_Framework_TestCase
    {
        const IP = "http://127.0.0.1";
        const CHANNEL = "testing";

        private $requestFields = array(
            "handler" => null,
            "args" => null,
            "info" => array(
                "machineId" => null,
                "pid" => null,
                "sequenceNo" => null,
                "generationTime" => null
            )
        );

        private $debugChannel;

        public function setup()
        {
            $this->debugChannel = new MockedDebugChannel(
                self::IP,
                self::CHANNEL
            );      
        }




        // CONSTRUCTOR
        public function testConstructorDoesNotThrowExceptionWithValidHostAndChannel()
        {
            return new MockedDebugChannel(
                self::IP,
                self::CHANNEL
            );            
        }

        public function testConstructorDoesNotThrowExceptionWhenApiKeyProvided()
        {
            return new MockedDebugChannel(self::IP, self::CHANNEL, "myAPiKey");
        }

        public function testConstructorDoesNotThrowExceptionWithOptionsSpecified()
        {
            $options = array(
                "showIteratorContents" => true,
                "maxDepth" => 2
            );

            $mockedDebugChannel = new MockedDebugChannel(
                self::IP,
                self::CHANNEL,
                null,
                $options
            );

            return $mockedDebugChannel;
        }

        /** @depends testConstructorDoesNotThrowExceptionWithOptionsSpecified */
        public function testConstructorMergesOptionsWithDefaultsCorrect($debugChannel)
        {
            $options = $debugChannel->options;
            $this->assertEquals(true, $options["showIteratorContents"]);
            $this->assertEquals(2, $options["maxDepth"]);
            $this->assertEquals(1, $options["expLvl"]);
            $this->assertEquals(true, $options["showPrivateMembers"]);
        }




        // EXPLORE METHOD

        public function provideValidExploreValues()
        {
            return array_map(
                function($i)
                {
                    return array($i);
                },
                array(
                    null,
                    "",
                    "hello",
                    234.1,
                    -453,
                    array(),
                    array(1,2,3),
                    array(34, "hello", null),
                    new \stdclass(),
                    array("name" => "testname", "age" => 105),
                    array( array(1,2,3), array(4,5,6), array(7,8,9))
                )
            );
        }

        /** @dataProvider provideValidExploreValues */
        public function testExploreMethodDoesNotThrowExceptionwithValidValues($value)
        {
            $this->debugChannel->explore($value);
        }

        /** @depends testConstructorDoesNotThrowExceptionWithValidHostAndChannel */
        public function testExploreMethodReturnsSameInstanceOfDebugChannel($debugChannel)
        {
            $this->assertEquals($debugChannel, $debugChannel->explore("Hello, World!"));
        }

        public function testExploreMethodGeneratesRequestWithRequiredFields()
        {
            $this->debugChannel->explore(new \stdclass());
            $this->assertArrayHasKeysDeep($this->requestFields, $this->debugChannel->getData());
        }

        public function testExploreMethodGeneratesRequestWithValidArgsArray()
        {
            $val = json_decode(json_encode(array("name" => "testname", "age" => 105)));
            $this->debugChannel->explore($val);
            $args = $this->debugChannel->getData()["args"];

            $this->assertEquals(1, count($args));
        }




        // TABLE METHOD


        public function provideValidTableValues()
        {
            return $this->provideValidExploreValues();
        }


        /** @dataProvider provideValidTableValues */
        public function testTableMethodDoesNotThrowExceptionWithValidValues($value)
        {
            $this->debugChannel->table($value);
        }

        public function testTableMethodReturnsSameInstanceOfDebugChannel()
        {
            $this->assertEquals(
                $this->debugChannel,
                $this->debugChannel->table(array())
            );
        }

        public function testTableMethodGeneratesRequestWithRequiredFields()
        {
            $this->debugChannel->table(array(array(1), array(2), array(3)));
            $this->assertArrayHasKeysDeep(
                $this->requestFields, 
                $this->debugchannel->getData()
            );
        }

        public function testTableMethodgeneratesRequestWithValidArgsArray()
        {
            $this->debugChannel->table(array(array(1), array(2), array(3)));
            $args = $this->debugchannel->getData()["args"];
            $this->assertEquals(1, count($args));
        }




        // STRING METHOD

        public function testStringMethodDoesNotThrowException()
        {
            return $this->debugChannel->string("Hello, World");
        }

        public function testStringMethodReturnsDebugChannel()
        {
            $this->assertEquals(
                $this->debugChannel, 
                $this->debugChannel->string("Hello, World")
            );
        }

        public function testStringMethodDoesNotThrowExceptionWhenPassedNullValue()
        {
            $this->debugChannel->string(null);
        }

        public function testStringMethodGeneratesRequestOfTypeArray()
        {
            $this->debugChannel->string("Hello, World!");
            $request = $this->debugChannel->getData();
            $this->assertTrue(is_array($request));
            return $request;
        }

        public function testStringMethodGeneratesRequestWithAllRequiredKeys()
        {
            $this->debugChannel->string("Hello, World!");
            $this->assertArrayHasKeysDeep($this->requestFields, $this->debugChannel->getData());
        }

        /** @depends testStringMethodGeneratesRequestOfTypeArray */
        public function testStringMethodGeneratesRequestWithValidHandler($request)
        {
            $this->assertEquals("string", $request["handler"]);
        }


        /** @depends testStringMethodGeneratesRequestOfTypeArray */
        public function testStringMethodGeneratesRequestWithValidArgs($request)
        {
            $args = $request["args"];
            $this->assertEquals(1, count($args));
            $this->assertEquals("Hello, World!", $args[0]);
        }


        /** @depends testStringMethodGeneratesRequestOfTypeArray */
        public function testStringMethodGeneratesRequestWithValidMachineId($request)
        {
            $this->assertNotEquals(null, $request["info"]["machineId"]);
            $this->assertNotEquals("", $request["info"]["machineId"]);
        }

        /** @depends testConstructorDoesNotThrowExceptionWithValidHostAndChannel */
        public function testClear($debugChannel)
        {
            $debugChannel->clear();
        }

        /** @depends testConstructorDoesNotThrowExceptionWithValidHostAndChannel */
        public function testLog($debugChannel)
        {
            $debugChannel->log("testLog");
        }

        /** @depends testConstructorDoesNotThrowExceptionWithValidHostAndChannel */
        public function testInvoke($debugChannel)
        {
            $debugChannel->__invoke("testInvoke");
        }

        /** @depends testConstructorDoesNotThrowExceptionWithValidHostAndChannel */
        public function testCode($debugChannel)
        {
            $debugChannel->code('SELECT * FROM something;');
        }

        /** @depends testConstructorDoesNotThrowExceptionWithValidHostAndChannel */
        public function testTable($debugChannel)
        {
            $table = [
                    [0,1,2,3,4,5],
                    [0,1,2,3,4,5],
                    ['<',"<div>",2,3,4,5],
                ];
            $debugChannel->table($table);
        }

        /** @depends testConstructorDoesNotThrowExceptionWithValidHostAndChannel */
        public function testImage($debugChannel)
        {
            $debugChannel->image('testImage.png');
        }

        /** @depends testConstructorDoesNotThrowExceptionWithValidHostAndChannel */
        public function testChat($debugChannel)
        {
            $debugChannel->chat('Hi', 'Pete');
        }

        /** @depends testConstructorDoesNotThrowExceptionWithValidHostAndChannel */
        public function testChatAnon($debugChannel)
        {
            $debugChannel->chat('Hi');
        }

    }


    class MockedDebugChannel extends DebugChannel
    {
        private $data;

        public function getData()
        {
            return $this->data;
        }

        protected function makeRequest( $data )
        {
            $data = $this->filloutRequest($data);            
            $this->data = $data;
            return $this;
        }
    }

}