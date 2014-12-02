<?php
//
// Change the following settings
//

$cred = require_once __DIR__ . '/credentials.php';

$now = time();
//$now = 1234567890;

$expires= $now + (60 * 5); // 5 minutes later
$endpoint='http://s3-ap-northeast-1.amazonaws.com';
$bucket = $_GET['bucket'];

$objectKey=$_GET['key'];

$mimeType=$_GET['type'];
$acl = $_GET['acl'];

$amzHeaders = [];
$amzHeaders[] = "x-amz-acl:" . $acl;

$url = getURL($cred['key'], $cred['secret'], $endpoint, $bucket, $objectKey, $expires, $mimeType, $amzHeaders);
header("Content-typte: application/json");
echo json_encode(['url' =>$url]);

function getURL($key, $secret, $endpoint, $bucket, $objectKey, $expires, $contentType, array $amzHeaders)
{
    // for calculation of Signature, see
    // http://docs.aws.amazon.com/AmazonS3/latest/dev/RESTAuthentication.html#ConstructingTheAuthenticationHeader
    $httpVerb = "PUT";
    $contentMD5 = "";
    $canonicalizedResource = sprintf("/%s/%s", $bucket, $objectKey);
    $canonicalizedAmzHeaders =  join("\n", $amzHeaders) . "\n";

    $stringToSign = $httpVerb . "\n"
        . $contentMD5 . "\n"
        . $contentType . "\n"
        . $expires . "\n"
        . $canonicalizedAmzHeaders
        . $canonicalizedResource;

    $sig = base64_encode(hash_hmac('sha1', $stringToSign, $secret, true));
    $url = sprintf("%s/%s/%s?AWSAccessKeyId=%s&Expires=%s&Signature=%s", $endpoint, $bucket, $objectKey   , $key, $expires, urlencode($sig));
    return urlencode($url);
}
