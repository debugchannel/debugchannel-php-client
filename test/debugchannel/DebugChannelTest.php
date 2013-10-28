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


        // STRING METHOD

        /** @depends testConstructorDoesNotThrowExceptionWithValidHostAndChannel */
        public function testStringMethodDoesNotThrowException($debugChannel)
        {
            return $debugChannel->string("Hello, World");
        }

        /** @depends testConstructorDoesNotThrowExceptionWithValidHostAndChannel */
        public function testStringMethodReturnsDebugChannel($debugChannel)
        {
            $this->assertEquals($debugChannel, $debugChannel->string("Hello, World"));
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