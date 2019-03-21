<?php

class HttpTest extends \PHPUnit\Framework\TestCase
{

    public function testMultipartBoundary()
    {
        $values = [
            bin2hex(random_bytes(100)),
            bin2hex(random_bytes(100)),
            bin2hex(random_bytes(100)),
        ];
        $boundary = \func_all\http_multipart_boundary($values);
        foreach ($values as $value) {
            $this->assertStringNotContainsString($boundary, $value);
        }
    }

    public function testMultipartEncode()
    {
        $binaryData = random_bytes(100);
        $params = [
            [
                ['Content-Disposition: form-data; name="DestAddress"'],
                'brutal-vasya@example.com'
            ],
            [
                ['Content-Disposition: form-data; name="MessageTitle"'],
                'Я негодую'
            ],
            [
                [
                    'Content-Disposition: form-data; name="AttachedFile1"; filename="horror-photo-1.jpg"',
                    'Content-Type: image/jpeg',
                ],
                $binaryData
            ],
        ];
        $boundary = 'Asrf456BGe4h';
        $result = \func_all\http_multipart_encode($params, $boundary);
        $this->assertSame(
            "--$boundary\r\n"
                        . "Content-Disposition: form-data; name=\"DestAddress\"\r\n\r\n"
                        . "brutal-vasya@example.com\r\n"
                        . "--$boundary\r\n"
                        . "Content-Disposition: form-data; name=\"MessageTitle\"\r\n\r\n"
                        . "Я негодую\r\n"
                        . "--$boundary\r\n"
                        . "Content-Disposition: form-data; name=\"AttachedFile1\"; filename=\"horror-photo-1.jpg\"\r\n"
                        . "Content-Type: image/jpeg\r\n\r\n"
                        . "$binaryData\r\n"
                        . "--$boundary--\r\n\r\n",
            $result
        );
    }

}