<?php
/**
 * User: NiZerin
 * Date: 18-12-12
 * Time: 上午11:48
 */

namespace YunXinHelper\Api;


use GuzzleHttp\Exception\GuzzleException;
use YunXinHelper\Exception\YunXinArgException;
use YunXinHelper\Exception\YunXinBusinessException;
use YunXinHelper\Exception\YunXinInnerException;
use YunXinHelper\Exception\YunXinNetworkException;

class Chat extends Base
{
    const CHAT_SEND_LIMIT = 500;

    const CHAT_ATTACH_MSG_PUSH_CONTENT_LIMIT = 500;
    const CHAT_ATTACH_MSG_ATTACH_LIMIT = 4096;
    const CHAT_ATTACH_MSG_PAYLOAD_LIMIT = 2000;

    const CHAT_ONT_TO_ONE = 0;
    const CHAT_ONT_TO_GROUP = 1;
    const CHAT_MSG_BODY_LIMIT = 5000;

    const ONE_DAY_SECONDS = 24 * 60 * 60;

    const FILE_UPLOAD_TAG_LIMIT = 32;

    const RECALL_TYPE_ONE_TO_ONE = 7;
    const RECALL_TYPE_ONE_TO_GROUP = 8;

    /**
     * 发送普通消息
     * @param  string  $accidFrom
     * @param  string  $accidTo
     * @param  int  $open  0：点对点个人消息，1：群消息（高级群），其他返回414
     * @param  int  $type
     * @param  string  $body  最大长度5000字符，为一个JSON串
     * @param  string  $antispam
     * @param  array  $antispamCustom
     * @param  string  $option
     * @param  string  $pushContent
     * @param  array  $payload
     * @param  string  $ext
     * @param  array  $forcePushList
     * @param  string  $forcePushContent
     * @param  bool  $forcePushAll
     * @param  string  $bid
     * @param  int  $useYidun
     * @param  int  $markRead
     * @param  string  $checkFriend
     * @return array
     * @throws YunXinArgException
     * @throws YunXinBusinessException
     * @throws YunXinInnerException
     * @throws YunXinNetworkException
     * @throws GuzzleException
     */
    private function sendMsg(
        $accidFrom,
        $accidTo,
        $open,
        $type,
        $body,
        $antispam = 'false',
        array $antispamCustom = [],
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        array $forcePushList = [],
        $forcePushContent = '',
        $forcePushAll = false,
        $bid = '',
        $useYidun = null,
        $markRead = 0,
        $checkFriend = 'false'
    ) {
        if (!$accidFrom || !is_string($accidFrom)) {
            throw new YunXinArgException('发送者id不能为空！');
        }
        if (strlen($accidFrom) > self::ACCID_LEGAL_LENGTH) {
            throw new YunXinArgException('发送者id超过限制！');
        }
        if (!$accidTo || !is_string($accidTo)) {
            throw new YunXinArgException('接受者id不能为空！');
        }
        if (strlen($accidTo) > self::ACCID_LEGAL_LENGTH) {
            throw new YunXinArgException('接受者id超过限制！');
        }

        if (strlen($body) > self::CHAT_MSG_BODY_LIMIT) {
            throw new YunXinArgException('body内容超过限制！');
        }
        $openLegalArr = [self::CHAT_ONT_TO_ONE, self::CHAT_ONT_TO_GROUP];
        if (!in_array($open, $openLegalArr)) {
            throw new YunXinArgException('send msg open参数不合法');
        }

        $res = $this->sendRequest('msg/sendMsg.action', [
            'from' => $accidFrom,
            'ope' => $open,
            'to' => $accidTo,
            'type' => $type,
            'body' => $body,
            'antispam' => $antispam,
            'antispamCustom' => $antispamCustom,
            'option' => $option,
            'pushcontent' => $pushContent,
//            'payload' => json_encode($payload),
            'ext' => $ext,
            'forcepushlist' => json_encode($forcePushList),
            'forcepushcontent' => $forcePushContent,
            'forcepushall' => $forcePushAll,
            'bid' => $bid,
            'useYidun' => $useYidun,
            'markRead' => $markRead,
//            'checkFriend' => $checkFriend,
        ]);
        return $res;
    }

    /**
     * 发送文本消息
     * @param $accidFrom
     * @param $to
     * @param  int  $open  0：点对点个人消息，1：群消息（高级群），其他返回414
     * @param  string  $text
     * @param  string  $antispam
     * @param  array  $antispamCustom
     * @param  string  $option
     * @param  string  $pushContent
     * @param  array  $payload
     * @param  string  $ext
     * @param  array  $forcePushList
     * @param  string  $forcePushContent
     * @param  bool  $forcePushAll
     * @param  string  $bid
     * @param $useYidun
     * @param  int  $markRead
     * @param  bool  $checkFriend
     * @return array
     * @throws YunXinArgException
     * @throws YunXinBusinessException
     * @throws YunXinInnerException
     * @throws YunXinNetworkException
     */
    public function sendTextMsg(
        $accidFrom,
        $to,
        $open,
        $text,
        $antispam = 'false',
        array $antispamCustom = [],
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        array $forcePushList = [],
        $forcePushContent = '',
        $forcePushAll = false,
        $bid = '',
        $useYidun = null,
        $markRead = 0,
        $checkFriend = false
    ) {
        $body = json_encode([
            'msg' => $text
        ]);

        $res = $this->sendMsg(
            $accidFrom,
            $to,
            $open,
            self::CHAT_TYPE_TEXT,
            $body,
            $antispam,
            $antispamCustom,
            $option,
            $pushContent,
            $payload,
            $ext,
            $forcePushList,
            $forcePushContent,
            $forcePushAll,
            $bid,
            $useYidun,
            $markRead,
            $checkFriend
        );
        return $res;
    }

    /**
     * 发送图片消息
     * @param  string  $accidFrom
     * @param  string  $to
     * @param  int  $open
     * @param  string  $picName
     * @param  string  $picMD5
     * @param  string  $picUrl
     * @param  string  $picExt  etc:jpg
     * @param  int  $picWidth
     * @param  int  $picHeight
     * @param  int  $picSize
     * @param  string  $antispam
     * @param  array  $antispamCustom
     * @param  string  $option
     * @param  string  $pushContent
     * @param  array  $payload
     * @param  string  $ext
     * @param  array  $forcePushList
     * @param  string  $forcePushContent
     * @param  bool  $forcePushAll
     * @param  string  $bid
     * @param $useYidun
     * @param  int  $markRead
     * @param  bool  $checkFriend
     * @return array
     * @throws YunXinArgException
     * @throws YunXinBusinessException
     * @throws YunXinInnerException
     * @throws YunXinNetworkException
     */
    public function sendPictureMsg(
        $accidFrom,
        $to,
        $open,
        $picName,
        $picMD5,
        $picUrl,
        $picExt,
        $picWidth,
        $picHeight,
        $picSize,
        $antispam = 'false',
        array $antispamCustom = [],
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        array $forcePushList = [],
        $forcePushContent = '',
        $forcePushAll = false,
        $bid = '',
        $useYidun = null,
        $markRead = 0,
        $checkFriend = false
    ) {
        $picWidth = intval($picWidth);
        $picHeight = intval($picHeight);
        $picSize = intval($picSize);
        if (!$picWidth || !$picHeight) {
            throw new YunXinArgException('图片宽度和高度不能为0！');
        }
        if (!$picSize) {
            throw new YunXinArgException('图片尺寸不能为0！');
        }

        $body = json_encode([
            "name" => $picName,   // 图片name
            "md5" => $picMD5,    // 图片文件md5
            "url" => $picUrl,    // 生成的url
            "ext" => $picExt,    // 图片后缀
            "w" => $picWidth,    // 宽
            "h" => $picHeight,    // 高
            "size" => $picSize    // 图片大小
        ]);

        $res = $this->sendMsg(
            $accidFrom,
            $to,
            $open,
            self::CHAT_TYPE_PICTURE,
            $body,
            $antispam,
            $antispamCustom,
            $option,
            $pushContent,
            $payload,
            $ext,
            $forcePushList,
            $forcePushContent,
            $forcePushAll,
            $bid,
            $useYidun,
            $markRead,
            $checkFriend
        );
        return $res;
    }


    /**
     * 发送语音消息
     * @param  string  $accidFrom
     * @param  string  $to
     * @param  int  $open
     * @param  int  $audioDur
     * @param  string  $audioMD5
     * @param  string  $audioUrl
     * @param  string  $audioExt
     * @param  int  $audioSize
     * @param  string  $antispam
     * @param  array  $antispamCustom
     * @param  string  $option
     * @param  string  $pushContent
     * @param  array  $payload
     * @param  string  $ext
     * @param  array  $forcePushList
     * @param  string  $forcePushContent
     * @param  bool  $forcePushAll
     * @param  string  $bid
     * @param $useYidun
     * @param  int  $markRead
     * @param  bool  $checkFriend
     * @return array
     * @throws YunXinArgException
     * @throws YunXinBusinessException
     * @throws YunXinInnerException
     * @throws YunXinNetworkException
     */
    public function sendAudioMsg(
        $accidFrom,
        $to,
        $open,
        $audioDur,
        $audioMD5,
        $audioUrl,
        $audioExt,
        $audioSize,
        $antispam = 'false',
        array $antispamCustom = [],
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        array $forcePushList = [],
        $forcePushContent = '',
        $forcePushAll = false,
        $bid = '',
        $useYidun = null,
        $markRead = 0,
        $checkFriend = false
    ) {
        $audioSize = intval($audioSize);

        if (!$audioDur) {
            throw new YunXinArgException('语音时长不能为0！');
        }
        if (!$audioSize) {
            throw new YunXinArgException('语音文件尺寸不能为0！');
        }
        if (!is_string($audioExt) || $audioExt != 'aac') {
            throw new YunXinArgException('语音文件后缀只能为acc！');
        }

        $body = json_encode([
            "dur" => $audioDur,   // 语音持续时长ms
            "md5" => $audioMD5,    // 语音文件的md5值
            "url" => $audioUrl,    // 生成的url
            "ext" => $audioExt,    // 语音消息格式，只能是aac格式
            "size" => $audioSize    // 语音文件大小
        ]);

        $res = $this->sendMsg(
            $accidFrom,
            $to,
            $open,
            self::CHAT_TYPE_AUDIO,
            $body,
            $antispam,
            $antispamCustom,
            $option,
            $pushContent,
            $payload,
            $ext,
            $forcePushList,
            $forcePushContent,
            $forcePushAll,
            $bid,
            $useYidun,
            $markRead,
            $checkFriend
        );
        return $res;
    }


    /**
     * 发送视频消息
     * @param $accidFrom
     * @param $to
     * @param $open
     * @param  int  $videoDur  /视频持续时长ms
     * @param $videoMD5
     * @param $videoUrl
     * @param $videoExt
     * @param  int  $videoWidth
     * @param  int  $videoHeight
     * @param  int  $videoSize
     * @param  string  $antispam
     * @param  array  $antispamCustom
     * @param  string  $option
     * @param  string  $pushContent
     * @param  array  $payload
     * @param  string  $ext
     * @param  array  $forcePushList
     * @param  string  $forcePushContent
     * @param  bool  $forcePushAll
     * @param  string  $bid
     * @param $useYidun
     * @param  int  $markRead
     * @param  bool  $checkFriend
     * @return array
     * @throws YunXinArgException
     * @throws YunXinBusinessException
     * @throws YunXinInnerException
     * @throws YunXinNetworkException
     */
    public function sendVideoMsg(
        $accidFrom,
        $to,
        $open,
        $videoDur,
        $videoMD5,
        $videoUrl,
        $videoExt,
        $videoWidth,
        $videoHeight,
        $videoSize,
        $antispam = 'false',
        array $antispamCustom = [],
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        array $forcePushList = [],
        $forcePushContent = '',
        $forcePushAll = false,
        $bid = '',
        $useYidun = null,
        $markRead = 0,
        $checkFriend = false
    ) {
        $videoDur = intval($videoDur);
        $videoWidth = intval($videoWidth);
        $videoHeight = intval($videoHeight);
        $videoSize = intval($videoSize);

        if (!$videoDur) {
            throw new YunXinArgException('视频时长不能为0！');
        }
        if (!$videoWidth || $videoHeight) {
            throw new YunXinArgException('视频宽度和高度不能为0！');
        }
        if (!$videoSize) {
            throw new YunXinArgException('视频文件尺寸不能为0！');
        }

        $body = json_encode([
            "dur" => $videoDur,   // 视频name
            "md5" => $videoMD5,    // 视频文件md5
            "url" => $videoUrl,    // 生成的url
            "ext" => $videoExt,    // 视频后缀
            "w" => $videoWidth,    // 宽
            "h" => $videoHeight,    // 高
            "size" => $videoSize    // 视频大小
        ]);

        $res = $this->sendMsg(
            $accidFrom,
            $to,
            $open,
            self::CHAT_TYPE_VIDEO,
            $body,
            $antispam,
            $antispamCustom,
            $option,
            $pushContent,
            $payload,
            $ext,
            $forcePushList,
            $forcePushContent,
            $forcePushAll,
            $bid,
            $useYidun,
            $markRead,
            $checkFriend
        );
        return $res;
    }


    /**
     * 发送地理位置消息
     * @param  string  $accidFrom
     * @param  string  $to
     * @param $open
     * @param $title
     * @param $lng
     * @param $lat
     * @param $antispam
     * @param  array  $antispamCustom
     * @param $option
     * @param $pushContent
     * @param $payload
     * @param $ext
     * @param  array  $forcePushList
     * @param $forcePushContent
     * @param $forcePushAll
     * @param $bid
     * @param $useYidun
     * @param $markRead
     * @param $checkFriend
     * @return array
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function sendPositionMsg(
        $accidFrom,
        $to,
        $open,
        $title,
        $lng,
        $lat,
        $antispam = 'false',
        array $antispamCustom = [],
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        array $forcePushList = [],
        $forcePushContent = '',
        $forcePushAll = false,
        $bid = '',
        $useYidun = null,
        $markRead = 0,
        $checkFriend = false
    ) {

        $body = json_encode([
            'title' => $title,
            'lng' => $lng, // 经度
            'lat' => $lat, // 纬度
        ]);

        $res = $this->sendMsg(
            $accidFrom,
            $to,
            $open,
            self::CHAT_TYPE_POSITION,
            $body,
            $antispam,
            $antispamCustom,
            $option,
            $pushContent,
            $payload,
            $ext,
            $forcePushList,
            $forcePushContent,
            $forcePushAll,
            $bid,
            $useYidun,
            $markRead,
            $checkFriend
        );

        return $res;
    }

    /**
     * 发送文件消息
     * @param  string  $accidFrom
     * @param  string  $to
     * @param $open
     * @param $fileName
     * @param $fileMD5
     * @param $fileUrl
     * @param $fileExt
     * @param $fileSize
     * @param $antispam
     * @param  array  $antispamCustom
     * @param $option
     * @param $pushContent
     * @param $payload
     * @param $ext
     * @param  array  $forcePushList
     * @param $forcePushContent
     * @param $forcePushAll
     * @param $bid
     * @param $useYidun
     * @param $markRead
     * @param $checkFriend
     * @return array
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function sendFileMsg(
        $accidFrom,
        $to,
        $open,
        $fileName,
        $fileMD5,
        $fileUrl,
        $fileExt,
        $fileSize,
        $antispam = 'false',
        array $antispamCustom = [],
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        array $forcePushList = [],
        $forcePushContent = '',
        $forcePushAll = false,
        $bid = '',
        $useYidun = null,
        $markRead = 0,
        $checkFriend = false
    ) {
        $fileSize = intval($fileSize);

        if (!$fileSize) {
            throw new YunXinArgException('文件尺寸不能为0！');
        }

        $body = json_encode([
            "name" => $fileName,   // 文件name
            "md5" => $fileMD5,    // 文件md5
            "url" => $fileUrl,    // 生成的url
            "ext" => $fileExt,    // 文件后缀
            "size" => $fileSize    // 文件大小
        ]);

        $res = $this->sendMsg(
            $accidFrom,
            $to,
            $open,
            self::CHAT_TYPE_FILE,
            $body,
            $antispam,
            $antispamCustom,
            $option,
            $pushContent,
            $payload,
            $ext,
            $forcePushList,
            $forcePushContent,
            $forcePushAll,
            $bid,
            $useYidun,
            $markRead,
            $checkFriend
        );
        return $res;
    }

    /**
     * 发送自定义消息
     * @param  string  $accidFrom
     * @param  string  $accidTo
     * @param $open
     * @param  array  $arr
     * @param $antispam
     * @param  array  $antispamCustom
     * @param $option
     * @param $pushContent
     * @param $payload
     * @param $ext
     * @param  array  $forcePushList
     * @param $forcePushContent
     * @param $forcePushAll
     * @param $bid
     * @param $useYidun
     * @param $markRead
     * @param $checkFriend
     * @return array
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function sendCustomMsg(
        $accidFrom,
        $accidTo,
        $open,
        array $arr,
        $antispam,
        array $antispamCustom = [],
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        array $forcePushList = [],
        $forcePushContent = '',
        $forcePushAll = false,
        $bid = '',
        $useYidun = null,
        $markRead = 0,
        $checkFriend = false
    ) {


        $res = $this->sendMsg(
            $accidFrom,
            $accidTo,
            $open,
            self::CHAT_TYPE_CUSTOM,
            json_encode($arr),
            $antispam,
            $antispamCustom,
            $option,
            $pushContent,
            $payload,
            $ext,
            $forcePushList,
            $forcePushContent,
            $forcePushAll,
            $bid,
            $useYidun,
            $markRead,
            $checkFriend
        );
        return $res;
    }


    /**
     * 批量发送点对点普通消息
     * @param $accidFrom
     * @param  array  $accidsTo
     * @param $type
     * @param $body
     * @param $option
     * @param $pushContent
     * @param $payload
     * @param $ext
     * @param $bid
     * @param  int  $useYidun
     * @param $returnMsgid
     * @return array
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    private function sendBatchMsg(
        $accidFrom,
        array $accidsTo,
        $type,
        $body,
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        $bid = '',
        $useYidun = null,
        $returnMsgid = false
    ) {
        if (!$accidFrom || !is_string($accidFrom)) {
            throw new YunXinArgException('发送者id不能为空！');
        }
        if (strlen($accidFrom) > self::ACCID_LEGAL_LENGTH) {
            throw new YunXinArgException('发送者id超过限制！');
        }
        if (empty($accidsTo)) {
            throw new YunXinArgException('接受者id组不能为空！');
        }
        if (count($accidsTo) > self::CHAT_SEND_LIMIT) {
            throw new YunXinArgException('接受者人数'.count($accidsTo).'超过限制！');
        }
        if (strlen($body) > self::CHAT_MSG_BODY_LIMIT) {
            throw new YunXinArgException('body内容超过限制！');
        }

        $res = $this->sendRequest('msg/sendBatchMsg.action', [
            'fromAccid' => $accidFrom,
            'toAccids' => json_encode($accidsTo),
            'type' => $type,
            'body' => $body,
            'option' => $option,
            'pushcontent' => $pushContent,
            //'payload' => json_encode($payload),
            'ext' => $ext,
            'bid' => $bid,
            'useYidun' => $useYidun,
            //'returnMsgid' => $returnMsgid,
        ]);
        return $res;
    }

    /**
     * 批量发送文本消息
     * @param $accidFrom
     * @param  array  $accidsTo
     * @param $text
     * @param $option
     * @param $pushContent
     * @param $payload
     * @param $ext
     * @param $bid
     * @param  int  $useYidun
     * @param $returnMsgid
     * @return array
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function sendTextBatchMsg(
        $accidFrom,
        array $accidsTo,
        $text,
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        $bid = '',
        $useYidun = null,
        $returnMsgid = false
    ) {
        $body = json_encode([
            'msg' => $text
        ]);

        $res = $this->sendBatchMsg(
            $accidFrom,
            $accidsTo,
            self::CHAT_TYPE_TEXT,
            $body,
            $option,
            $pushContent,
            $payload,
            $ext,
            $bid,
            $useYidun,
            $returnMsgid
        );
        return $res;
    }

    /**
     * 发送批量图片消息
     * @param $accidFrom
     * @param  array  $accidsTo
     * @param $picName
     * @param $picMD5
     * @param $picUrl
     * @param $picExt
     * @param $picWidth
     * @param $picHeight
     * @param $picSize
     * @param  string  $option
     * @param  string  $pushContent
     * @param  array  $payload
     * @param  string  $ext
     * @param  string  $bid
     * @param  int  $useYidun
     * @param  bool  $returnMsgid
     * @return array
     * @throws YunXinArgException
     * @throws YunXinBusinessException
     * @throws YunXinInnerException
     * @throws YunXinNetworkException
     */
    public function sendPictureBatchMsg(
        $accidFrom,
        array $accidsTo,
        $picName,
        $picMD5,
        $picUrl,
        $picExt,
        $picWidth,
        $picHeight,
        $picSize,
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        $bid = '',
        $useYidun = null,
        $returnMsgid = false
    ) {
        $picWidth = intval($picWidth);
        $picHeight = intval($picHeight);
        $picSize = intval($picSize);

        if (!$picWidth || $picHeight) {
            throw new YunXinArgException('图片宽度和高度不能为0！');
        }
        if (!$picSize) {
            throw new YunXinArgException('图片尺寸不能为0！');
        }

        $body = json_encode([
            "name" => $picName,   // 图片name
            "md5" => $picMD5,    // 图片文件md5
            "url" => $picUrl,    // 生成的url
            "ext" => $picExt,    // 图片后缀
            "w" => $picWidth,    // 宽
            "h" => $picHeight,    // 高
            "size" => $picSize    // 图片大小
        ]);

        $res = $this->sendBatchMsg(
            $accidFrom,
            $accidsTo,
            self::CHAT_TYPE_PICTURE,
            $body,
            $option,
            $pushContent,
            $payload,
            $ext,
            $bid,
            $useYidun,
            $returnMsgid
        );
        return $res;
    }

    /**
     * 发送批量语音消息
     * @param $accidFrom
     * @param  array  $accidsTo
     * @param $audioDur
     * @param $audioMD5
     * @param $audioUrl
     * @param $audioExt
     * @param $audioSize
     * @param $option
     * @param $pushContent
     * @param $payload
     * @param $ext
     * @param $bid
     * @param  int  $useYidun
     * @param $returnMsgid
     * @return array
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function sendAudioBatchMsg(
        $accidFrom,
        array $accidsTo,
        $audioDur,
        $audioMD5,
        $audioUrl,
        $audioExt,
        $audioSize,
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        $bid = '',
        $useYidun = null,
        $returnMsgid = false
    ) {
        $audioDur = intval($audioDur);
        $audioSize = intval($audioSize);

        if (!$audioDur) {
            throw new YunXinArgException('语音时长不能为0！');
        }
        if (!$audioSize) {
            throw new YunXinArgException('语音文件尺寸不能为0！');
        }
        if (!is_string($audioExt) || $audioExt != 'aac') {
            throw new YunXinArgException('语音文件后缀只能为acc！');
        }

        $body = json_encode([
            "dur" => $audioDur,   // 语音持续时长ms
            "md5" => $audioMD5,    // 语音文件的md5值
            "url" => $audioUrl,    // 生成的url
            "ext" => $audioExt,    // 语音消息格式，只能是aac格式
            "size" => $audioSize    // 语音文件大小
        ]);

        $res = $this->sendBatchMsg(
            $accidFrom,
            $accidsTo,
            self::CHAT_TYPE_AUDIO,
            $body,
            $option,
            $pushContent,
            $payload,
            $ext,
            $bid,
            $useYidun,
            $returnMsgid
        );
        return $res;
    }

    /**
     * 发送批量视频消息
     * @param $accidFrom
     * @param  array  $accidsTo
     * @param $videoDur
     * @param $videoMD5
     * @param $videoUrl
     * @param $videoExt
     * @param $videoWidth
     * @param $videoHeight
     * @param $videoSize
     * @param $option
     * @param $pushContent
     * @param $payload
     * @param $ext
     * @param $bid
     * @param $useYidun
     * @param $returnMsgid
     * @return array
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function sendVideoBatchMsg(
        $accidFrom,
        array $accidsTo,
        $videoDur,
        $videoMD5,
        $videoUrl,
        $videoExt,
        $videoWidth,
        $videoHeight,
        $videoSize,
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        $bid = '',
        $useYidun = null,
        $returnMsgid = false
    ) {
        $videoDur = intval($videoDur);
        $videoWidth = intval($videoWidth);
        $videoHeight = intval($videoHeight);
        $videoSize = intval($videoSize);

        if (!$videoDur) {
            throw new YunXinArgException('视频时长不能为0！');
        }
        if (!$videoWidth || $videoHeight) {
            throw new YunXinArgException('视频宽度和高度不能为0！');
        }
        if (!$videoSize) {
            throw new YunXinArgException('视频文件尺寸不能为0！');
        }

        $body = json_encode([
            "dur" => $videoDur,   // 视频name
            "md5" => $videoMD5,    // 视频文件md5
            "url" => $videoUrl,    // 生成的url
            "ext" => $videoExt,    // 视频后缀
            "w" => $videoWidth,    // 宽
            "h" => $videoHeight,    // 高
            "size" => $videoSize    // 视频大小
        ]);

        $res = $this->sendBatchMsg(
            $accidFrom,
            $accidsTo,
            self::CHAT_TYPE_VIDEO,
            $body,
            $option,
            $pushContent,
            $payload,
            $ext,
            $bid,
            $useYidun,
            $returnMsgid
        );
        return $res;
    }


    /**
     * 发送批量地理位置消息
     * @param $accidFrom
     * @param  array  $accidsTo
     * @param $title
     * @param $lng
     * @param $lat
     * @param $option
     * @param $pushContent
     * @param $payload
     * @param $ext
     * @param $bid
     * @param $useYidun
     * @param $returnMsgid
     * @return array
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function sendPositionBatchMsg(
        $accidFrom,
        array $accidsTo,
        $title,
        $lng,
        $lat,
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        $bid = '',
        $useYidun = null,
        $returnMsgid = false
    ) {

        $body = json_encode([
            'title' => $title,
            'lng' => $lng, // 经度
            'lat' => $lat, // 纬度
        ]);

        $res = $this->sendBatchMsg(
            $accidFrom,
            $accidsTo,
            self::CHAT_TYPE_POSITION,
            $body,
            $option,
            $pushContent,
            $payload,
            $ext,
            $bid,
            $useYidun,
            $returnMsgid
        );
        return $res;
    }

    /**
     * 发送文件消息
     * @param $accidFrom
     * @param  array  $accidsTo
     * @param $fileName
     * @param $fileMD5
     * @param $fileUrl
     * @param $fileExt
     * @param $fileSize
     * @param  string  $option
     * @param  string  $pushContent
     * @param  array  $payload
     * @param  string  $ext
     * @param  string  $bid
     * @param $useYidun
     * @param  bool  $returnMsgid
     * @return array
     * @throws YunXinArgException
     * @throws YunXinBusinessException
     * @throws YunXinInnerException
     * @throws YunXinNetworkException
     */
    public function sendFileBatchMsg(
        $accidFrom,
        array $accidsTo,
        $fileName,
        $fileMD5,
        $fileUrl,
        $fileExt,
        $fileSize,
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        $bid = '',
        $useYidun = null,
        $returnMsgid = false
    ) {
        $fileSize = intval($fileSize);

        if (!$fileSize) {
            throw new YunXinArgException('文件尺寸不能为0！');
        }

        $body = json_encode([
            "name" => $fileName,   // 文件name
            "md5" => $fileMD5,    // 文件md5
            "url" => $fileUrl,    // 生成的url
            "ext" => $fileExt,    // 文件后缀
            "size" => $fileSize    // 文件大小
        ]);

        $res = $this->sendBatchMsg(
            $accidFrom,
            $accidsTo,
            self::CHAT_TYPE_FILE,
            $body,
            $option,
            $pushContent,
            $payload,
            $ext,
            $bid,
            $useYidun,
            $returnMsgid
        );
        return $res;
    }

    /**
     * 发送自定义消息
     * @param $accidFrom
     * @param  array  $accidsTo
     * @param  array  $arr
     * @param  string  $option
     * @param  string  $pushContent
     * @param  array  $payload
     * @param  string  $ext
     * @param  string  $bid
     * @param $useYidun
     * @param  bool  $returnMsgid
     * @return array
     * @throws YunXinArgException
     * @throws YunXinBusinessException
     * @throws YunXinInnerException
     * @throws YunXinNetworkException
     */
    public function sendCustomBatchMsg(
        $accidFrom,
        array $accidsTo,
        array $arr,
        $option = '',
        $pushContent = '',
        $payload = [],
        $ext = '',
        $bid = '',
        $useYidun = null,
        $returnMsgid = false
    ) {

        $res = $this->sendBatchMsg(
            $accidFrom,
            $accidsTo,
            self::CHAT_TYPE_CUSTOM,
            json_encode($arr),
            $option,
            $pushContent,
            $payload,
            $ext,
            $bid,
            $useYidun,
            $returnMsgid
        );

        return $res;
    }

    /**
     * 验证参数
     * @param $from
     * @param $msgType
     * @param $attachStr
     * @param $pushContent
     * @param $payload
     * @param $save
     * @throws YunXinArgException
     */
    private function verifyAttachMsg(
        $from,
        $msgType,
        $attachStr,
        $pushContent,
        $payload,
        $save
    ) {
        $msgLegalTypes = [self::CHAT_ONT_TO_ONE, self::CHAT_ONT_TO_GROUP];
        $saveLegalTypes = [1, 2];
        $msgType = intval($msgType);

        if (empty($from)) {
            throw new YunXinArgException('发送者id不能为空！');
        }
        if (strlen($from) > self::ACCID_LEGAL_LENGTH) {
            throw new YunXinArgException('发送者id超过限制！');
        }
        if (!in_array($msgType, $msgLegalTypes)) {
            throw new YunXinArgException('msgtype不合法！');
        }
        if (!in_array($save, $saveLegalTypes)) {
            throw new YunXinArgException('save类型不合法！');
        }
        if (empty($attachStr)) {
            throw new YunXinArgException('attach内容不能为空！');
        }
        if (strlen($pushContent) > self::CHAT_ATTACH_MSG_PUSH_CONTENT_LIMIT) {
            throw new YunXinArgException('推送内容不超过500字符！');
        }

        if (strlen($attachStr) > self::CHAT_ATTACH_MSG_ATTACH_LIMIT) {
            throw new YunXinArgException('attach内容最大长度4096字符！');
        }
        if (strlen($payload) > self::CHAT_ATTACH_MSG_PAYLOAD_LIMIT) {
            throw new YunXinArgException('payload不超过2k字符！');
        }
    }

    /**
     * 发送自定义系统通知
     * @param $from
     * @param $msgType
     * @param $to
     * @param  array  $attach
     * @param $pushContent
     * @param $payload
     * @param $sound
     * @param $save
     * @param $option
     * @return array
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function sendAttachMsg(
        $from,
        $msgType,
        $to,
        array $attach,
        $pushContent = '',
        $payload = [],
        $sound = '',
        $save = 2,
        $option = ''
    ) {
        $attachStr = '';
        if ($attach) {
            $attachStr = json_encode($attach);
        }
        $save = intval($save);
        if (!$save) {
            $save = 2;
        }
        $this->verifyAttachMsg($from, $msgType,
            $attachStr, $pushContent, $payload, $save);
        if (!$to || !is_string($to)) {
            throw new YunXinArgException('接受者id不能为空！');
        }
        if (strlen($to) > self::ACCID_LEGAL_LENGTH) {
            throw new YunXinArgException('接受者id超过限制！');
        }


        $res = $this->sendRequest('msg/sendAttachMsg.action', [
            'from' => $from,
            'msgtype' => $msgType,
            'to' => $to,
            'attach' => $attachStr,
            'pushcontent' => $pushContent,
            'payload' => $payload,
            'sound' => $sound,
            'save' => $save,
            'option' => $option,
        ]);
        return $res;
    }


    /**
     * 批量发送自定义系统通知
     * @param $from
     * @param  array  $toAccids
     * @param  array  $attach
     * @param  string  $pushContent
     * @param  array  $payload
     * @param  string  $sound
     * @param  int  $save
     * @param  string  $option
     * @return array
     * @throws YunXinArgException
     * @throws YunXinBusinessException
     * @throws YunXinInnerException
     * @throws YunXinNetworkException
     */
    public function sendAttachBatchMsg(
        $from,
        array $toAccids,
        array $attach,
        $pushContent = '',
        $payload = [],
        $sound = '',
        $save = 2,
        $option = ''
    ) {
        $attachStr = '';
        if ($attach) {
            $attachStr = json_encode($attach);
        }
        $save = intval($save);
        if (!$save) {
            $save = 2;
        }
        $this->verifyAttachMsg($from, self::CHAT_ONT_TO_ONE,
            $attachStr, $pushContent, $payload, $save);
        if (empty($toAccids)) {
            throw new YunXinArgException('接受者id组不能为空！');
        }

        $res = $this->sendRequest('msg/sendBatchAttachMsg.action', [
            'fromAccid' => $from,
            'toAccids' => json_encode($toAccids),
            'attach' => $attachStr,
            'pushcontent' => $pushContent,
            'payload' => $payload,
            'sound' => $sound,
            'save' => $save,
            'option' => $option,
        ]);
        return $res;
    }


    /**
     * 文件上传
     * @param  string  $content
     * @param  string  $type
     * @param  bool  $isHttps
     * @param  int  $expireSec
     * @param  string  $tag
     * @return array
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function upload($content, $type, $isHttps = false, $expireSec = null, $tag = '')
    {
        if ($expireSec) {
            $expireSec = intval($expireSec);
        }
        if ($expireSec > 0 && $expireSec < self::ONE_DAY_SECONDS) {
            throw new YunXinArgException('文件过期时间必须大于等于86400！');
        }
        $data = [
            'content' => $content,
            'type' => $type,
            'ishttps' => $isHttps,
            'tag' => $tag,
        ];
        if ($expireSec > 0) {
            $data['expireSec'] = $expireSec;
        }

        $res = $this->sendRequest('msg/upload.action', $data);
        return $res;
    }


    /**
     * 消息撤回
     * @param  string  $deleteMsgid
     * @param  int  $timetag
     * @param  int  $type
     * @param $from
     * @param $to
     * @param $msg
     * @param $ignoreTime
     * @param  string  $pushContent
     * @param $payload
     * @return array
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function recallMsg(
        $deleteMsgid,
        $timetag,
        $type,
        $from,
        $to,
        $msg,
        $ignoreTime,
        $pushContent,
        $payload
    ) {
        $typesLegal = [self::RECALL_TYPE_ONE_TO_ONE, self::RECALL_TYPE_ONE_TO_GROUP];
        $type = intval($type);

        if (empty($deleteMsgid)) {
            throw new YunXinArgException('撤回msg id不能为空！');
        }
        if (empty($timetag)) {
            throw new YunXinArgException('撤回msg创建时间不能为空！');
        }
        if (!in_array($type, $typesLegal)) {
            throw new YunXinArgException('撤回type错误！');
        }
        if (strlen($pushContent) > self::CHAT_ATTACH_MSG_PUSH_CONTENT_LIMIT) {
            throw new YunXinArgException('推送内容不超过500字符！');
        }

        $res = $this->sendRequest('msg/recall.action', [
            'deleteMsgid' => $deleteMsgid,
            'timetag' => $timetag,
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'msg' => $msg,
            'ignoreTime' => $ignoreTime,
            'pushcontent' => $pushContent,
            'payload' => $payload,
        ]);
        return $res;
    }

    /**
     * 发送广播消息
     * @param $body
     * @param $from
     * @param  bool  $isOffline
     * @param $ttl
     * @param  array  $targetOs
     * @return mixed
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function broadcastMsg($body, $from, $isOffline = false, $ttl, array $targetOs)
    {
        if (empty($body)) {
            throw new YunXinArgException('body不能为空！');
        }
        if (empty($from)) {
            throw new YunXinArgException('发送者id不能为空！');
        }

        $data = [
            'body' => $body,
            'from' => $from,
            'isOffline' => $isOffline,
            'ttl' => $ttl,
        ];
        if ($targetOs) {
            $data['targetOs'] = json_encode($targetOs);
        }
        $res = $this->sendRequest('msg/broadcastMsg.action', $data);
        return $res['msg'];
    }
}