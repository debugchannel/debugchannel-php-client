<?php

namespace debugchannel {


    class DebugChannelTest extends \PHPUnit_Framework_TestCase
    {
        const IP = "http://127.0.0.1";
        const CHANNEL = "testing";

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

        /** @depends testConstructorDoesNotThrowExceptionWithValidHostAndChannel */
        public function testStringDoesNotThrowException($debugChannel)
        {
            $debugChannel->string("Hello, World");
        }

        /** @depends testConstructorDoesNotThrowExceptionWithValidHostAndChannel */
        public function testStringReturnsDebugChannel($debugChannel)
        {
            $this->assertEquals($debugChannel, $debugChannel->string("Hello, World"));
        }
        public function testClear()
        {
            $this->mockedDebugChannel->clear();
        }

        public function testLog()
        {
            $this->mockedDebugChannel->log("testLog");
        }

        public function testInvoke()
        {
            $this->mockedDebugChannel->__invoke("testInvoke");
        }

        public function testCode()
        {
            $this->mockedDebugChannel->code('SELECT * FROM something;');
        }

        public function testTable()
        {
            $table = [
                    [0,1,2,3,4,5],
                    [0,1,2,3,4,5],
                    ['<',"<div>",2,3,4,5],
                ];
            $this->mockedDebugChannel->table($table);
        }

        public function testImage()
        {
            $this->mockedDebugChannel->image('testImage.png');
        }

        public function testChat()
        {
            $this->mockedDebugChannel->chat('Hi', 'Pete');
        }

        public function testChatAnon()
        {
            $this->mockedDebugChannel->chat('Hi');
        }

    }


    class MockedDebugChannel extends DebugChannel
    {
        private $data;

        public function getData()
        {
            return $this->mockedDebugChannelata;
        }

        protected function makeRequest( $data )
        {
            $this->mockedDebugChannelata = $data;
            return $this;
        }
    }

}