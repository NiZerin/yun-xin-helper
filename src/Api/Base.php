<?php

namespace YunXinHelper\Api;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use YunXinHelper\Exception\YunXinBusinessException;
use YunXinHelper\Exception\YunXinInnerException;
use YunXinHelper\Exception\YunXinNetworkException;

/**
 * User: NiZerin
 * Date: 18-12-12
 * Time: 上午9:27
 */
class Base
{
    private $baseUrl = 'https://api.netease.im/nimserver/';

    const HEX_DIGITS = "0123456789abcdefghijklmn";

    const BUSINESS_SUCCESS_CODE = 200;
    const BUSINESS_CLIENT_BAD_VERSION_CODE = 201; // 客户端版本不对，需升级sdk
    const BUSINESS_FORBIDDEN_CODE = 301; // 被封禁
    const BUSINESS_USERNAME_OR_PASSWD_ERROR_CODE = 302; // 用户名或密码错误
    const BUSINESS_IP_LIMIT_CODE = 315; // IP限制
    const BUSINESS_NOT_PERMITTED_CODE = 403; // 非法操作或没有权限
    const BUSINESS_NOT_EXIST_CODE = 404; // 对象不存在
    const BUSINESS_ARG_TOO_LONG_CODE = 405; // 参数长度过长
    const BUSINESS_READ_ONLY_CODE = 406; // 对象只读
    const BUSINESS_CLIENT_TIMEOUT_CODE = 408; // 客户端请求超时
    const BUSINESS_SMS_VERIFY_FAILED_CODE = 413; // 验证失败(短信服务)
    const BUSINESS_ARG_NOT_CORRECT_CODE = 414; // 参数错误
    const BUSINESS_CLIENT_NETWORK_ERROR_CODE = 415; // 客户端网络问题
    const BUSINESS_RATE_CONTROL_CODE = 416; // 频率控制
    const BUSINESS_DUPLICATE_OPERATION_CODE = 417; // 重复操作
    const BUSINESS_CHANNEL_NOT_AVAILABLE_CODE = 418; // 通道不可用(短信服务)
    const BUSINESS_NUM_EXCEED_LIMIT_CODE = 419; // 数量超过上限
    const BUSINESS_ACCOUNT_BANNED_CODE = 422; // 账号被禁用
    const BUSINESS_ACCOUNT_CHAT_NOT_PERMITTED_CODE = 423; // 帐号被禁言
    const BUSINESS_HTTP_REQUEST_DUPLICATE_CODE = 431; // HTTP重复请求
    const BUSINESS_SERVER_INNER_ERROR_CODE = 500; // 服务器内部错误
    const BUSINESS_SERVER_TOO_BUSY_CODE = 503; // 服务器繁忙
    const BUSINESS_RECALL_MSG_TIMEOUT_CODE = 508; // 消息撤回时间超限
    const BUSINESS_BAD_PROTOCOL_CODE = 509; // 无效协议
    const BUSINESS_SERVICE_NOT_AVAILABLE_CODE = 514; // 服务不可用
    const BUSINESS_UNPACK_ERROR_CODE = 998; // 解包错误
    const BUSINESS_PACK_ERROR_CODE = 999; // 打包错误

    const ACCID_LEGAL_LENGTH = 32;


    const CHAT_TYPE_TEXT = 0;
    const CHAT_TYPE_PICTURE = 1;
    const CHAT_TYPE_AUDIO = 2;
    const CHAT_TYPE_VIDEO = 3;
    const CHAT_TYPE_POSITION = 4;
    const CHAT_TYPE_FILE = 6;
    const CHAT_TYPE_TIPS = 10;
    const CHAT_TYPE_CUSTOM = 100;


    /**
     * 网易云信分配的账号
     * @var string $appKey
     */
    private $appKey;

    /**
     * 网易云信分配的密钥
     * @var string $appSecrt
     */
    private $appSecrt;

    /**
     * 随机数（最大长度128个字符）
     * @var string $nonce
     */
    public $nonceStr;

    /**
     * 当前UTC时间戳，从1970年1月1日0点0 分0 秒开始到现在的秒数(String)
     * @var string $curTime
     */
    public $curTime;

    /**
     * 校验码
     * SHA1(AppSecret + Nonce + CurTime)
     * 三个参数拼接的字符串，进行SHA1哈希计算，转化成16进制字符(String，小写)
     * @var string
     */
    public $checkSum;


    /**
     * http 超时时间
     * @var int $timeout
     */
    private $timeout = 5;


    public function __construct($appKey, $appSecrt)
    {
        $this->appKey = $appKey;
        $this->appSecrt = $appSecrt;
    }


    /**
     * API checksum校验生成
     * @param void
     * @return $CheckSum(对象私有属性)
     */
    public function checkSumBuilder()
    {
        //此部分生成随机字符串
        $hexDigits = self::HEX_DIGITS;
        $digitsLen = strlen($hexDigits);
        $this->nonceStr;
        for ($i = 0; $i < 128; $i++) {
            $this->nonceStr .= $hexDigits[rand(0, $digitsLen - 1)];
        }
        $this->curTime = (string)(time());    //当前时间戳，以秒为单位

        $joinString = $this->appSecrt . $this->nonceStr . $this->curTime;
        $this->checkSum = sha1($joinString);
    }


    /**
     * 设置超时时间
     * @param $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * 发送请求
     * @param string $uri
     * @param array $data
     * @return mixed
     * @throws YunXinBusinessException
     * @throws YunXinInnerException
     * @throws YunXinNetworkException
     * @throws GuzzleException
     */
    protected function sendRequest($uri, array $data)
    {
        $this->checkSumBuilder();

        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->baseUrl,
            // You can set any number of default request options.
            'timeout' => $this->timeout,
        ]);
        $response = $client->request('POST', $uri, [
            'verify' => false, // 证书校验主动关闭
            'headers' => [
                'User-Agent' => 'WebWorker/2.0',
                'AppKey' => $this->appKey,
                'Nonce' => $this->nonceStr,
                'CurTime' => $this->curTime,
                'CheckSum' => $this->checkSum,
            ],
            'form_params' => $data
        ]);
        $code = $response->getStatusCode();
        $body = $response->getBody();
        if ($code != 200) {
            throw new YunXinNetworkException('NetEase Network Error: ' . $body, $code);
        }
        $jsonRes = json_decode((string)$body, true);
        if ($jsonRes && is_array($jsonRes) && $jsonRes['code'] == self::BUSINESS_SUCCESS_CODE) {
            return $jsonRes;
        } elseif ($jsonRes && is_array($jsonRes)) {
            throw new YunXinBusinessException($jsonRes['desc'], $jsonRes['code']);
        } else {
            throw new YunXinInnerException('NetEase inner error: ' . $body);
        }
    }

    protected function bool2String($var)
    {
        return $var ? 'true' : 'false';
    }
}