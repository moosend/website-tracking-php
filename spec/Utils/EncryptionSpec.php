<?php namespace spec\Moosend\Utils;

use Moosend\Utils\Encryption;
use PhpSpec\ObjectBehavior;

class EncryptionSpec extends ObjectBehavior {

    function it_should_encode_string() {
        $expectedEncodedString = "ZXhhbXBsZUBleGFtcGxlLmNvbQ";
        $this::encode('example@example.com')->shouldReturn($expectedEncodedString);
    }

    function it_should_decode_base64_string() {
        $decodedString = "ZXhhbXBsZUBleGFtcGxlLmNvbQ";
        $expectedDecodedString = "example@example.com";
        $this::decode($decodedString)->shouldReturn($expectedDecodedString);
    }

    function it_should_return_email_if_string_is_not_decoded() {
        $decodedString = "example@example.com";
        $expectedDecodedString = "example@example.com";
        $this::decode($decodedString)->shouldReturn($expectedDecodedString);
    }

}