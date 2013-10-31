<?php

namespace debugchannel;

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


class DebugChannelTest extends \PHPUnit_Framework_TestCase
{

    const IP = "127.0.0.1";
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
        $data = $this->debugChannel->getData();
        $args = $data["args"];

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
            $this->debugChannel->getData()
        );
    }

    public function testTableMethodGeneratesRequestWithValidArgsArray()
    {
        $this->debugChannel->table(array(array(1), array(2), array(3)));
        $data = $this->debugChannel->getData();
        $args = $data["args"];
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




    // CODE METHOD

    public function provideValidValuesForCodeMethod()
    {
        return array(
            array(""),
            array("SELECT * FROM MyTable"),
        );
    }

    public function provideInvalidValuesForCodeMethod()
    {
        return array(
            array(null),
            array(new \stdclass()),
            array(array()),
            array(array(1,2,3))
        );
    }

    /** @dataProvider provideValidValuesForCodeMethod */
    public function testCodeMethodDoesNotThrowExceptionWithValidValues($value)
    {
        $this->debugChannel->code($value);
    }


    /** @dataProvider provideInvalidValuesForCodeMethod */
    public function testCodeMethodThrowsExceptionWhenObjectProvidedAsCode($value)
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->debugChannel->code($value);
    }

    /** @expectedException \Exception */
    public function testCodeMethodThrowsExceptionWhenLanguageIsSetToNull()
    {
        $this->debugChannel->code("SELECT FROM Address", null);
    }

    public function testCodeMethodGeneratesRequestWithRequiredFields()
    {
        $this->debugChannel->code("yield 4","python");
        $this->assertArrayHasKeysDeep(
            $this->requestFields,
            $this->debugChannel->getData()
        );
    }

    /** @depends testCodeMethodGeneratesRequestWithRequiredFields */
    public function testCodeMethodGeneratesRequestWithDefaultLanguageSet()
    {
        $this->debugChannel->code("SELECT * FROM Address");
        $data = $this->debugChannel->getData();
        $args = $data["args"];
        $this->assertEquals('sql', $args[1]);
    }

    /** @depends testCodeMethodGeneratesRequestWithRequiredFields */
    public function testCodeMethodGeneratesRequestWithLanguageSpecified()
    {
        $this->debugChannel->code("int i = 4;", "java");
        $data = $this->debugChannel->getData();
        $args = $data["args"];
        $this->assertEquals('java', $args[1]);
    }

    /** @depends testCodeMethodGeneratesRequestWithRequiredFields */
    public function testCodeMethodGeneratesRequestWhichContainsTheCodeString()
    {
        $this->debugChannel->code("int i = 4;", "java");
        $data = $this->debugChannel->getData();
        $args = $data["args"];
        $this->assertEquals('int i = 4;', $args[0]);
    }




    // IMAGE METHOD

    public function provideInvalidValuesForImageMethod()
    {
        return array(
            array(null),
            array(""),
            array(".")
        );
    }

    public function provideValidValuesForImageMethod()
    {
        // expects testMethod to require args ($identifier, $isFileName)
        return array(
            // absolute paths
            array(__DIR__ . "/testImage.png", true),
            array(
                base64_encode(file_get_contents(__DIR__ . "/testImage.png")),
                false
            ),
            // relative paths
            array("test/debugchannel/testImage.png", true),
            array(
                base64_encode(file_get_contents("test/debugchannel/testImage.png")),
                false
            )
        );
    }

    public function testImageMethodReturnsSameInstanceOfDebugChannel()
    {
        $this->assertEquals(
            $this->debugChannel,
            $this->debugChannel->image(__DIR__ . "/testImage.png")
        );
    }

    /** @dataProvider provideInvalidValuesForImageMethod */
    public function testImageMethodThrowsExceptionWithInvalidValue($value)
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->debugChannel->image($value);
    }

    /** @dataProvider provideValidValuesForImageMethod */
    public function testImageMethodDoesNotThrowExceptionWithValidValues($value)
    {
        $this->debugChannel->image($value);
    }

    /** @dataProvider provideValidValuesForImageMethod */
    public function testImageMethodGeneratesRequestWithRequiredFields($value)
    {
        $this->debugChannel->image($value);
        $this->assertArrayHasKeysDeep(
            $this->requestFields,
            $this->debugChannel->getData()
        );
    }

    /** @dataProvider provideValidValuesForImageMethod */
    public function testImageMethodGeneratesRequestWithValidHandler($value)
    {
        $this->debugChannel->image($value);
        $data = $this->debugChannel->getData();
        $this->assertEquals(
            "image",
            $data["handler"]
        );
    }

    /** @dataProvider provideValidVAluesForImageMethod */
    public function testImageMethodGeneratesRequestWithCorrectImageData($value, $isFileName)
    {
        $base64Content = $isFileName ? base64_encode(file_get_contents($value)) : $value;
        $this->debugChannel->image($value);
        $data = $this->debugChannel->getData();
        $args = $data["args"];
        $this->assertEquals(1, count($args));
        $this->assertEquals($base64Content, $args[0]);
    }




    // CHAT METHOD

    public function provideValidChatArgs()
    {
        $validMessages = array(
            "",
            "hello",
            json_encode(array("name" => 'john', 'age' => 105)),
            'aasafdaslfasjldf;kjas;lfkjasdl;fkjasl;fkjasd;lfkjsadgjhdflgkjadflgkjdagsdafsafsafasfasdfsdfasfsafdasfasdfasdfasdfasdfasdfasdf'
        );

        $validSenderNames = array(
            null,
            "",
            "john",
            "<john>",
            "!@£$%^&*(){};:\\'",
            'multiple words',
            'very long names that will overflow in the window'
        );

        $args = array();
        foreach ($validMessages as $message) {
            foreach ($validSenderNames as $name) {
                $args[] = array($message, $name);
            }
        }
        return $args;
    }

    public function testChatMethodReturnsSameInstanceOfDebugChannel()
    {
        $this->assertEquals(
            $this->debugChannel,
            $this->debugChannel->chat("Hello, World!")
        );
    }

    /** @dataProvider provideValidChatArgs */
    public function testChatMethodDoesNotThrowExceptionWithValidValues($message, $senderName) {
        $this->debugChannel->chat($message, $senderName);
    }

    /** @expectedException InvalidArgumentException */
    public function testChatMethodThrowsExceptionWhenNullPassedAsMessage()
    {
        $this->debugChannel->chat(null);
    }

    /** @dataProvider provideValidChatArgs */
    public function testChatMethodGeneratesRequestWithRequiredFields($message, $sender)
    {
        $this->debugChannel->chat($message, $sender);
        $this->assertArrayHasKeysDeep(
            $this->requestFields,
            $this->debugChannel->getData()
        );
    }

    /** @dataProvider provideValidChatArgs */
    public function testChatMethodGeneratesRequestWithCorrectHandler($message, $sender)
    {
        $this->debugChannel->chat($message, $sender);
        $data = $this->debugChannel->getData();
        $this->assertEquals(
            "chat",
            $data["handler"]
        );
    }

    /** @dataProvider provideValidChatArgs */
    public function testChatMethodGeneratesRequestWithValidArgsArray($message, $sender)
    {
        $this->debugChannel->chat($message, $sender);
        $data = $this->debugChannel->getData();
        $args = $data["args"];
        $this->assertEquals(2, count($args));
        $this->assertEquals($message, $args[1]);
        $this->assertEquals(is_null($sender) ? DebugChannel::ANON_IDENTIFIER : $sender, $args[0]);
    }




    // CLEAR METHOD

    public function testClearMethodDoesNotThrowException()
    {
        $this->debugChannel->clear();
    }

    public function testClearMethodReturnsSameInstanceofDebugChannel()
    {
        $this->assertEquals(
            $this->debugChannel,
            $this->debugChannel->clear()
        );
    }

    public function testClearMethodGeneratesRequestWithRequiredFields()
    {
        $this->assertArrayHasKeysDeep(
            $this->requestFields,
            $this->debugChannel->clear()->getData()
        );
    }

    public function testClearMethodGeneratesRequestWithCorrectHandler()
    {
        $data = $this->debugChannel->clear()->getData();
        $this->assertEquals(
            'clear',
            $data["handler"]
        );
    }

    public function testClearMethodGeneratesRequestWithCorrectArgsArray()
    {
        $data = $this->debugChannel->clear()->getData();
        $args = $data["args"];
        $this->assertEquals(0, count($args));
    }




    // HELP METHOD

    public function testHelpMethodDoesNotThrowException()
    {
        $this->debugChannel->help();
    }

    public function testHelpMethodReturnsSameInstanceOfDebugChannel()
    {
        $this->assertEquals(
            $this->debugChannel,
            $this->debugChannel->help()
        );
    }

    public function testHelpMethodGeneratesRequestWithRequiredFields()
    {
        $this->assertArrayHasKeysDeep(
            $this->requestFields,
            $this->debugChannel->help()->getData()
        );
    }

    public function testHelpMethodGeneratesRequestWithCorrectHandler()
    {
        $data = $this->debugChannel->help()->getData();
        $this->assertEquals(
            'help',
            $data["handler"]
        );
    }

    public function testHelpMethodGeneratesRequestWithCorrectArgsArray()
    {
        $data = $this->debugChannel->help()->getData();
        $args = $data["args"];
        $this->assertEquals(array('php'), $args);
    }



    // UTIL

    private function assertArrayHasKeysDeep($keys, $array)
    {
        foreach ($keys as $key => $value) {
            $this->assertArrayHasKey($key, $array);
            if (is_array($value)) {
                $this->assertArrayHasKeysDeep($value, $array[$key]);
            }
        }
    }

}