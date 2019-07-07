<?php
/**
 * User: NiZerin
 * Date: 18-12-17
 * Time: 上午11:16
 */

namespace YunXinHelper\Api;


use GuzzleHttp\Exception\GuzzleException;
use YunXinHelper\Exception\YunXinArgException;
use YunXinHelper\Exception\YunXinBusinessException;
use YunXinHelper\Exception\YunXinInnerException;
use YunXinHelper\Exception\YunXinNetworkException;

class ChatRoom extends Base
{

    const CHAT_ROOM_NAME_LIMIT = 128;
    const CHAT_ROOM_ANNOUNCEMENT_LIMIT = 4096;
    const CHAT_ROOM_BROADCASTURL_LIMIT = 1024;
    const CHAT_ROOM_EXT_LIMIT = 4096;
    const CHAT_ROOM_NOTIFY_EXT_LIMIT = 2048;

    const QUEUE_LEVEL_ALL = 0;
    const QUEUE_LEVEL_ADMIN = 1;

    const MEMBER_ROLE_ADMIN = 1; // 管理员
    const MEMBER_ROLE_COMMON = 2; // 普通等级用户
    const MEMBER_ROLE_BLACKLIST = -1; // 黑名单用户
    const MEMBER_ROLE_GAG = -2; // 禁言用户


    const CLIENT_TYPE_WEBLINK = 1; // weblink
    const CLIENT_TYPE_COMMONLINK = 2; // commonlink
    const CLIENT_TYPE_WECHATLINK = 3; // wechatlink


    const CHAT_ROOM_ATTACH_LIMIT = 4096;


    const CHAT_ROOM_SKIP_HISTORY = 1;
    const CHAT_ROOM_SKIP_NON_HISTORY = 0;


    const CHAT_ROOM_QUEUE_KEY_LIMIT = 128;
    const CHAT_ROOM_QUEUE_VALUE_LIMIT = 4096;


    const CHAT_ROOM_MEMBER_TYPE_FIXED = 0;
    const CHAT_ROOM_MEMBER_TYPE_NON_FIXED = 1;
    const CHAT_ROOM_MEMBER_TYPE_FIXED_ONLINE = 2;


    /**
     * 创建聊天室
     * @param $creator
     * @param $name
     * @param  string  $announcement
     * @param  string  $broadcasturl
     * @param  string  $ext
     * @param  int  $queuelevel
     * @return array
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function create($creator, $name, $announcement = '', $broadcasturl = '', $ext = '', $queuelevel = 0)
    {
        if (!$creator || !is_string($creator)) {
            throw new YunXinArgException('创建者id不能为空！');
        }
        if (strlen($creator) > self::ACCID_LEGAL_LENGTH) {
            throw new YunXinArgException('创建者id超过限制！');
        }
        if (!$name) {
            throw new YunXinArgException('聊天室名称不能为空！');
        }
        if (strlen($name) > self::CHAT_ROOM_NAME_LIMIT) {
            throw new YunXinArgException('聊天室公告超过限制！');
        }
        if (strlen($announcement) > self::CHAT_ROOM_ANNOUNCEMENT_LIMIT) {
            throw new YunXinArgException('聊天室公告超过限制！');
        }
        if (strlen($broadcasturl) > self::CHAT_ROOM_BROADCASTURL_LIMIT) {
            throw new YunXinArgException('聊天室直播地址超过限制！');
        }
        if (strlen($ext) > self::CHAT_ROOM_EXT_LIMIT) {
            throw new YunXinArgException('聊天室扩展字段超过限制！');
        }
        $levelLegalArr = [self::QUEUE_LEVEL_ALL, self::QUEUE_LEVEL_ADMIN];
        if (!in_array($queuelevel, $levelLegalArr)) {
            throw new YunXinArgException('聊天室queuelevel参数不合法');
        }

        $res = $this->sendRequest('chatroom/create.action', [
            'creator' => $creator,
            'name' => $name,
            'announcement' => $announcement,
            'broadcasturl' => $broadcasturl,
            'ext' => $ext,
            'queuelevel' => $queuelevel,
        ]);
        return $res['chatroom'];
    }


    /**
     * 查询聊天室信息
     * @param $roomId
     * @param  bool  $needOnlineUserCount
     * @return mixed
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function get($roomId, $needOnlineUserCount = false)
    {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }

        $res = $this->sendRequest('chatroom/get.action', [
            'roomid' => $roomId,
            'needOnlineUserCount' => $this->bool2String($needOnlineUserCount),
        ]);
        return $res['chatroom'];
    }

    /**
     * 批量查询聊天室信息
     * @param  array  $roomIds
     * @param  bool  $needOnlineUserCount
     * @return mixed
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinInnerException
     * @throws YunXinNetworkException
     */
    public function getBatch(array $roomIds, $needOnlineUserCount = false)
    {
        if (empty($roomIds)) {
            throw new YunXinArgException('房间id列表不能为空！');
        }

        $res = $this->sendRequest('chatroom/getBatch.action', [
            'roomids' => json_encode($roomIds),
            'needOnlineUserCount' => $this->bool2String($needOnlineUserCount),
        ]);
        return $res;
    }

    /**
     * 更新聊天室
     * @param $roomId
     * @param  null  $name
     * @param  null  $announcement
     * @param  null  $broadcasturl
     * @param  null  $ext
     * @param  bool  $needNotify
     * @param  null  $notifyExt
     * @param  null  $queuelevel
     * @return mixed
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinInnerException
     * @throws YunXinNetworkException
     */
    public function update(
        $roomId,
        $name = null,
        $announcement = null,
        $broadcasturl = null,
        $ext = null,
        $needNotify = true,
        $notifyExt = null,
        $queuelevel = null
    ) {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        if (strlen($name) > self::CHAT_ROOM_NAME_LIMIT) {
            throw new YunXinArgException('聊天室公告超过限制！');
        }
        if (strlen($announcement) > self::CHAT_ROOM_ANNOUNCEMENT_LIMIT) {
            throw new YunXinArgException('聊天室公告超过限制！');
        }
        if (strlen($broadcasturl) > self::CHAT_ROOM_BROADCASTURL_LIMIT) {
            throw new YunXinArgException('聊天室直播地址超过限制！');
        }
        if (strlen($ext) > self::CHAT_ROOM_EXT_LIMIT) {
            throw new YunXinArgException('聊天室扩展字段超过限制！');
        }
        $levelLegalArr = [self::QUEUE_LEVEL_ALL, self::QUEUE_LEVEL_ADMIN];
        if (is_numeric($queuelevel) && !in_array($queuelevel, $levelLegalArr)) {
            throw new YunXinArgException('聊天室queuelevel参数不合法');
        }
        if (strlen($notifyExt) > self::CHAT_ROOM_NOTIFY_EXT_LIMIT) {
            throw new YunXinArgException('聊天室通知事件扩展字段超过限制！');
        }
        $data = [
            'roomid' => $roomId,
        ];
        if (isset($name)) {
            $data['name'] = $name;
        }
        if (isset($announcement)) {
            $data['announcement'] = $announcement;
        }
        if (isset($broadcasturl)) {
            $data['broadcasturl'] = $broadcasturl;
        }
        if (isset($ext)) {
            $data['ext'] = $ext;
        }
        if (isset($needNotify)) {
            $data['needNotify'] = $this->bool2String($needNotify);
        }
        if (isset($notifyExt)) {
            $data['notifyExt'] = $notifyExt;
        }
        if (isset($queuelevel)) {
            $data['queuelevel'] = $queuelevel;
        }

        $res = $this->sendRequest('chatroom/update.action', $data);
        return $res['chatroom'];
    }


    /**
     * 修改聊天室开/关闭状态
     * @param $roomId
     * @param $operator
     * @param $valid
     * @return mixed
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinInnerException
     * @throws YunXinNetworkException
     */
    public function toggleCloseStatus($roomId, $operator, $valid)
    {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        if (!is_bool($valid)) {
            throw new YunXinArgException('设置房间状态valid参数应该为bool类型！');
        }

        $res = $this->sendRequest('chatroom/toggleCloseStat.action', [
            'roomid' => $roomId,
            'operator' => $operator,
            'valid' => $this->bool2String($valid),
        ]);
        return $res['desc'];
    }


    /**
     * 设置聊天室内用户角色
     * @param $roomId
     * @param $operator
     * @param $target
     * @param $opt
     * @param $optValue
     * @param  string  $notifyExt
     * @return mixed
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function setMemberRole($roomId, $operator, $target, $opt, $optValue, $notifyExt = '')
    {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        if (!$operator || !is_string($operator)) {
            throw new YunXinArgException('操作者id不能为空！');
        }
        if (!$target || !is_string($target)) {
            throw new YunXinArgException('被操作者id不能为空！');
        }
        $optLegalArr = [
            self::MEMBER_ROLE_ADMIN,
            self::MEMBER_ROLE_COMMON,
            self::MEMBER_ROLE_BLACKLIST,
            self::MEMBER_ROLE_GAG
        ];
        if (!in_array($opt, $optLegalArr)) {
            throw new YunXinArgException('opt参数不合法');
        }
        if (!is_bool($optValue)) {
            throw new YunXinArgException('optValue参数应该为bool类型！');
        }

        if (strlen($notifyExt) > self::CHAT_ROOM_NOTIFY_EXT_LIMIT) {
            throw new YunXinArgException('聊天室通知事件扩展字段超过限制！');
        }

        $res = $this->sendRequest('chatroom/setMemberRole.action', [
            'roomid' => $roomId,
            'operator' => $operator,
            'target' => $target,
            'opt' => $opt,
            'optvalue' => $this->bool2String($optValue),
            'notifyExt' => $notifyExt,
        ]);
        return $res['desc'];
    }


    public function requestAddr($roomId, $accid, $clientType = 1, $clientIP = '')
    {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        if (!$accid || !is_string($accid)) {
            throw new YunXinArgException('进入聊天室账号id不能为空！');
        }
        if (strlen($accid) > self::ACCID_LEGAL_LENGTH) {
            throw new YunXinArgException('进入聊天室账号id超过限制！');
        }

        $clientTypeLegalArr = [
            self::CLIENT_TYPE_WEBLINK,
            self::CLIENT_TYPE_COMMONLINK,
            self::CLIENT_TYPE_WECHATLINK,
        ];
        if (!in_array($clientType, $clientTypeLegalArr)) {
            throw new YunXinArgException('clientType参数不合法');
        }
        $res = $this->sendRequest('chatroom/requestAddr.action', [
            'roomid' => $roomId,
            'accid' => $accid,
            'clienttype' => $clientType,
            'clientip' => $clientIP,
        ]);
        return $res['addr'];
    }


    private function sendMsg(
        $roomId,
        $msgId,
        $fromAccid,
        $msgType,
        $resendFlag = null,
        $attach = '',
        array $ext,
        $antispam = 'false',
        array $antispamCustom = [],
        $skipHistory = 0,
        $bid = null,
        $highPriority = false,
        $useYidun = null,
        $needHighPriorityMsgResend = true
    ) {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        if (!$msgId) {
            throw new YunXinArgException('msgid不能为空！');
        }
        if (!$fromAccid || !is_string($fromAccid)) {
            throw new YunXinArgException('发送者id不能为空！');
        }
        if (strlen($fromAccid) > self::ACCID_LEGAL_LENGTH) {
            throw new YunXinArgException('发送者id超过限制！');
        }
        if (strlen($attach) > self::CHAT_ROOM_ATTACH_LIMIT) {
            throw new YunXinArgException('消息内容超过限制！');
        }
        $extStr = '';
        if ($ext) {
            $extStr = json_encode($ext);
        }
        if (strlen($extStr) > self::CHAT_ROOM_EXT_LIMIT) {
            throw new YunXinArgException('消息扩展字段超过限制！');
        }
        $antispamCustomStr = '';
        if ($antispamCustom) {
            $antispamCustomStr = json_encode($antispamCustom);
        }
        if ($antispamCustomStr > 5000) {
            throw new YunXinArgException('自定义的反垃圾检测内容超过限制！');
        }
        $skipHistoryLegalTypes = [self::CHAT_ROOM_SKIP_NON_HISTORY, self::CHAT_ROOM_SKIP_HISTORY];
        if (!in_array($skipHistory, $skipHistoryLegalTypes)) {
            throw new YunXinArgException('skipHistory参数不合法');
        }
        if (!is_bool($highPriority)) {
            throw new YunXinArgException('highPriority参数应该为bool');
        }
        if (!is_bool($needHighPriorityMsgResend)) {
            throw new YunXinArgException('needHighPriorityMsgResend参数应该为bool');
        }


        $res = $this->sendRequest('chatroom/sendMsg.action', [
            'roomid' => $roomId,
            'msgId' => $msgId,
            'fromAccid' => $fromAccid,
            'msgType' => $msgType,
            'resendFlag' => $resendFlag,
            'attach' => $attach,
            'ext' => $extStr,
            'antispam' => $antispam,
            'antispamCustom' => $antispamCustomStr,
            'skipHistory' => $skipHistory,
            'bid' => $bid,
            'highPriority' => $this->bool2String($highPriority),
            'useYidun' => $useYidun,
            'needHighPriorityMsgResend' => $this->bool2String($needHighPriorityMsgResend),
        ]);
        return $res['desc'];
    }


    /**
     * 发送文本消息
     * @param $roomId
     * @param $msgId
     * @param $fromAccid
     * @param $text
     * @param  null  $resendFlag
     * @param  array  $ext
     * @param  string  $antispam
     * @param  array  $antispamCustom
     * @param  int  $skipHistory
     * @param  null  $bid
     * @param  bool  $highPriority
     * @param  null  $useYidun
     * @param  bool  $needHighPriorityMsgResend
     * @return mixed
     * @throws YunXinArgException
     */
    public function sendTextMsg(
        $roomId,
        $msgId,
        $fromAccid,
        $text,
        $resendFlag = null,
        array $ext = [],
        $antispam = 'false',
        array $antispamCustom = [],
        $skipHistory = 0,
        $bid = null,
        $highPriority = false,
        $useYidun = null,
        $needHighPriorityMsgResend = true
    ) {
        if (!$text) {
            throw new YunXinArgException('文本消息内容不能为空！');
        }

        $body = json_encode($text, 256);

        return $this->sendMsg(
            $roomId,
            $msgId,
            $fromAccid,
            self::CHAT_TYPE_TEXT,
            $resendFlag,
            $body,
            $ext,
            $antispam,
            $antispamCustom,
            $skipHistory,
            $bid,
            $highPriority,
            $useYidun,
            $needHighPriorityMsgResend
        );
    }

    /**
     * 发送提醒消息
     * @param $roomId
     * @param $msgId
     * @param $fromAccid
     * @param $text
     * @param  null  $resendFlag
     * @param  array  $ext
     * @param  string  $antispam
     * @param  array  $antispamCustom
     * @param  int  $skipHistory
     * @param  null  $bid
     * @param  bool  $highPriority
     * @param  null  $useYidun
     * @param  bool  $needHighPriorityMsgResend
     * @return mixed
     * @throws YunXinArgException
     */
    public function sendTipsMsg(
        $roomId,
        $msgId,
        $fromAccid,
        $text,
        $resendFlag = null,
        array $ext = [],
        $antispam = 'false',
        array $antispamCustom = [],
        $skipHistory = 0,
        $bid = null,
        $highPriority = false,
        $useYidun = null,
        $needHighPriorityMsgResend = true
    ) {
        if (!$text) {
            throw new YunXinArgException('提醒消息内容不能为空！');
        }

        $body = json_encode($text, 256);

        return $this->sendMsg(
            $roomId,
            $msgId,
            $fromAccid,
            self::CHAT_TYPE_TIPS,
            $resendFlag,
            $body,
            $ext,
            $antispam,
            $antispamCustom,
            $skipHistory,
            $bid,
            $highPriority,
            $useYidun,
            $needHighPriorityMsgResend
        );
    }

    /**
     * 发送图片消息
     * @param $roomId
     * @param $msgId
     * @param $fromAccid
     * @param $picName
     * @param $picMD5
     * @param $picUrl
     * @param $picExt
     * @param $picWidth
     * @param $picHeight
     * @param $picSize
     * @param  null  $resendFlag
     * @param  array  $ext
     * @param  string  $antispam
     * @param  array  $antispamCustom
     * @param  int  $skipHistory
     * @param  null  $bid
     * @param  bool  $highPriority
     * @param  null  $useYidun
     * @param  bool  $needHighPriorityMsgResend
     * @return mixed
     * @throws YunXinArgException
     */
    public function sendPictureMsg(
        $roomId,
        $msgId,
        $fromAccid,
        $picName,
        $picMD5,
        $picUrl,
        $picExt,
        $picWidth,
        $picHeight,
        $picSize,
        $resendFlag = null,
        array $ext = [],
        $antispam = 'false',
        array $antispamCustom = [],
        $skipHistory = 0,
        $bid = null,
        $highPriority = false,
        $useYidun = null,
        $needHighPriorityMsgResend = true
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


        return $this->sendMsg(
            $roomId,
            $msgId,
            $fromAccid,
            self::CHAT_TYPE_PICTURE,
            $resendFlag,
            $body,
            $ext,
            $antispam,
            $antispamCustom,
            $skipHistory,
            $bid,
            $highPriority,
            $useYidun,
            $needHighPriorityMsgResend
        );
    }

    /**
     * 发送语音消息
     * @param $roomId
     * @param $msgId
     * @param $fromAccid
     * @param  int  $audioDur
     * @param  string  $audioMD5
     * @param  string  $audioUrl
     * @param  string  $audioExt
     * @param  int  $audioSize
     * @param  null  $resendFlag
     * @param  array  $ext
     * @param  string  $antispam
     * @param  array  $antispamCustom
     * @param  int  $skipHistory
     * @param $bid
     * @param  bool  $highPriority
     * @param $useYidun
     * @param  bool  $needHighPriorityMsgResend
     * @return array
     * @throws YunXinArgException
     */
    public function sendAudioMsg(
        $roomId,
        $msgId,
        $fromAccid,
        $audioDur,
        $audioMD5,
        $audioUrl,
        $audioExt,
        $audioSize,
        $resendFlag = null,
        array $ext = [],
        $antispam = 'false',
        array $antispamCustom = [],
        $skipHistory = 0,
        $bid = null,
        $highPriority = false,
        $useYidun = null,
        $needHighPriorityMsgResend = true
    ) {
        $audioSize = intval($audioSize);

        if (!$audioDur) {
            throw new YunXinArgException('语音时长不能为0！');
        }
        if (!$audioSize) {
            throw new YunXinArgException('语音文件尺寸不能为0！');
        }
        if (!is_string($audioExt)) {
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
            $roomId,
            $msgId,
            $fromAccid,
            self::CHAT_TYPE_AUDIO,
            $resendFlag,
            $body,
            $ext,
            $antispam,
            $antispamCustom,
            $skipHistory,
            $bid,
            $highPriority,
            $useYidun,
            $needHighPriorityMsgResend
        );
        return $res;
    }

    /**
     * 发送视频消息
     * @param $roomId
     * @param $msgId
     * @param $fromAccid
     * @param  int  $audioDur
     * @param  string  $audioMD5
     * @param  string  $audioUrl
     * @param  string  $audioExt
     * @param  int  $audioSize
     * @param  null  $resendFlag
     * @param  array  $ext
     * @param  string  $antispam
     * @param  array  $antispamCustom
     * @param  int  $skipHistory
     * @param $bid
     * @param  bool  $highPriority
     * @param $useYidun
     * @param  bool  $needHighPriorityMsgResend
     * @return array
     * @throws YunXinArgException
     */
    public function sendVideoMsg(
        $roomId,
        $msgId,
        $fromAccid,
        $videoDur,
        $videoMD5,
        $videoUrl,
        $videoExt,
        $videoSize,
        $resendFlag = null,
        array $ext = [],
        $antispam = 'false',
        array $antispamCustom = [],
        $skipHistory = 0,
        $bid = null,
        $highPriority = false,
        $useYidun = null,
        $needHighPriorityMsgResend = true
    ) {
        $videoSize = intval($videoSize);

        if (!$videoDur) {
            throw new YunXinArgException('语音时长不能为0！');
        }
        if (!$videoSize) {
            throw new YunXinArgException('语音文件尺寸不能为0！');
        }
        if (!is_string($videoExt)) {
            throw new YunXinArgException('语音文件后缀只能为acc！');
        }

        $body = json_encode([
            "dur" => $videoDur,   // 语音持续时长ms
            "md5" => $videoMD5,    // 语音文件的md5值
            "url" => $videoUrl,    // 生成的url
            "ext" => $videoExt,    // 语音消息格式，只能是aac格式
            "size" => $videoSize    // 语音文件大小
        ]);

        $res = $this->sendMsg(
            $roomId,
            $msgId,
            $fromAccid,
            self::CHAT_TYPE_VIDEO,
            $resendFlag,
            $body,
            $ext,
            $antispam,
            $antispamCustom,
            $skipHistory,
            $bid,
            $highPriority,
            $useYidun,
            $needHighPriorityMsgResend
        );
        return $res;
    }

    /**
     * @param $roomId
     * @param $operator
     * @param $target
     * @param $muteDuration
     * @param  bool  $needNotify
     * @param  string  $notifyExt
     * @return mixed
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinInnerException
     * @throws YunXinNetworkException
     */
    public function temporaryMute($roomId, $operator, $target, $muteDuration, $needNotify = true, $notifyExt = '')
    {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        if (!$operator || !is_string($operator)) {
            throw new YunXinArgException('操作者id不能为空！');
        }
        if (strlen($operator) > self::ACCID_LEGAL_LENGTH) {
            throw new YunXinArgException('操作者id超过限制！');
        }
        if (!$target || !is_string($target)) {
            throw new YunXinArgException('目标账号id不能为空！');
        }
        if (strlen($target) > self::ACCID_LEGAL_LENGTH) {
            throw new YunXinArgException('目标账号id超过限制！');
        }
        if (!is_int($muteDuration) || $muteDuration < 0) {
            throw new YunXinArgException('禁言秒数不合法！');
        }
        if (strlen($notifyExt) > self::CHAT_ROOM_NOTIFY_EXT_LIMIT) {
            throw new YunXinArgException('聊天室通知事件扩展字段超过限制！');
        }

        $res = $this->sendRequest('chatroom/temporaryMute.action', [
            'roomid' => $roomId,
            'operator' => $operator,
            'target' => $target,
            'muteDuration' => $muteDuration,
            'needNotify' => $needNotify ? 'true' : 'false',
            'notifyExt' => $notifyExt,
        ]);
        return $res['desc'];
    }


    /**
     * 往聊天室有序队列中新加或更新元素
     * @param  int  $roomId
     * @param  string  $key
     * @param  string  $value
     * @param  string  $operator
     * @param  bool  $transient
     * @return mixed
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function queueOffer($roomId, $key, $value, $operator, $transient = false)
    {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        if (!$key) {
            throw new YunXinArgException('元素key不能为空！');
        }
        if (strlen($key) > self::CHAT_ROOM_QUEUE_KEY_LIMIT) {
            throw new YunXinArgException('元素key超过限制！');
        }
        if (!$value) {
            throw new YunXinArgException('元素value不能为空！');
        }
        if (strlen($value) > self::CHAT_ROOM_QUEUE_VALUE_LIMIT) {
            throw new YunXinArgException('元素value超过限制！');
        }

        $res = $this->sendRequest('chatroom/queueOffer.action', [
            'roomid' => $roomId,
            'key' => $key,
            'value' => $value,
            'operator' => $operator,
            'transient' => $transient,
        ]);
        return $res['desc'];
    }

    /**
     * 从队列中取出元素
     * @param  int  $roomId
     * @param $key
     * @return mixed
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function queuePoll($roomId, $key)
    {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        $res = $this->sendRequest('chatroom/queuePoll.action', [
            'roomid' => $roomId,
            'key' => $key,
        ]);
        return $res['desc'];
    }


    /**
     * 列出队列中所有元素
     * @param  int  $roomId
     * @return mixed
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function queueList($roomId)
    {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        $res = $this->sendRequest('chatroom/queueList.action', [
            'roomid' => $roomId,
        ]);
        return $res['desc'];
    }


    /**
     * 删除清理整个队列
     * @param $roomId
     * @return mixed
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function queueDrop($roomId)
    {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        $res = $this->sendRequest('chatroom/queueDrop.action', [
            'roomid' => $roomId,
        ]);
        return $res['desc'];
    }

    /**
     * 初始化队列
     * @param $roomId
     * @param $sizeLimit
     * @return mixed
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function queueInit($roomId, $sizeLimit)
    {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        if ($sizeLimit < 0 || $sizeLimit > 1000) {
            throw new YunXinArgException('队列大小不合法！');
        }
        $res = $this->sendRequest('chatroom/queueInit.action', [
            'roomid' => $roomId,
            'sizeLimit' => $sizeLimit,
        ]);
        return $res['desc'];
    }


    /**
     * 设置聊天室整体禁言状态（仅创建者和管理员能发言）
     * @param  int  $roomId
     * @param  string  $operator
     * @param  bool  $mute
     * @param  bool  $needNotify
     * @param $notifyExt
     * @return mixed
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function muteRoom($roomId, $operator, $mute, $needNotify = true, $notifyExt = '')
    {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        if (!$operator || !is_string($operator)) {
            throw new YunXinArgException('操作者id不能为空！');
        }
        if (strlen($operator) > self::ACCID_LEGAL_LENGTH) {
            throw new YunXinArgException('操作者id超过限制！');
        }
        if (!is_bool($mute)) {
            throw new YunXinArgException('参数mute为bool类型！');
        }
        if (!is_bool($needNotify)) {
            throw new YunXinArgException('参数needNotify为bool类型！');
        }
        if (strlen($notifyExt) > self::CHAT_ROOM_NOTIFY_EXT_LIMIT) {
            throw new YunXinArgException('聊天室通知事件扩展字段超过限制！');
        }

        $res = $this->sendRequest('chatroom/muteRoom.action', [
            'roomid' => $roomId,
            'operator' => $operator,
            'mute' => $this->bool2String($mute),
            'needNotify' => $this->bool2String($needNotify),
            'notifyExt' => $notifyExt,
        ]);
        return $res['desc'];
    }


    /**
     * 分页获取成员列表
     * @param  int  $roomId
     * @param  int  $type
     * @param  int  $endTime
     * @param  int  $limit
     * @return mixed
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function getMembersByPage($roomId, $type, $endTime, $limit)
    {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        $legalTypes = [
            self::CHAT_ROOM_MEMBER_TYPE_FIXED,
            self::CHAT_ROOM_MEMBER_TYPE_NON_FIXED,
            self::CHAT_ROOM_MEMBER_TYPE_FIXED_ONLINE
        ];
        if (!in_array($type, $legalTypes)) {
            throw new YunXinArgException('成员type不合法！');
        }
        if (!is_int($endTime)) {
            throw new YunXinArgException('endtime参数不合法！');
        }
        if (!is_int($limit)) {
            throw new YunXinArgException('获取成员条数不合法！');
        }
        if ($limit > 100) {
            throw new YunXinArgException('获取成员条数不能超过100条！');
        }
        $res = $this->sendRequest('chatroom/membersByPage.action', [
            'roomid' => $roomId,
            'type' => $type,
            'endtime' => $endTime,
            'limit' => $limit,
        ]);
        return $res['desc'];
    }


    /**
     * 批量获取在线成员信息
     * @param  int  $roomId
     * @param  array  $accids
     * @return mixed
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function queryOnlineMembers($roomId, array $accids)
    {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        if (count($accids) > 200) {
            throw new YunXinArgException('账号列表最多100条');
        }

        $res = $this->sendRequest('chatroom/queryMembers.action', [
            'roomid' => $roomId,
            'accids' => json_encode($accids)
        ]);
        return $res['desc'];
    }


    /**
     * 变更聊天室内的角色信息
     * @param  int  $roomId
     * @param  string  $accid
     * @param  bool  $save
     * @param  bool  $needNotify
     * @param  string  $notifyExt
     * @param  string  $nick
     * @param  string  $avator
     * @param  string  $ext
     * @return array
     * @throws YunXinArgException
     * @throws GuzzleException
     * @throws YunXinBusinessException
     * @throws YunXinNetworkException
     * @throws YunXinInnerException
     */
    public function updateMyRoomRole(
        $roomId,
        $accid,
        $save = false,
        $needNotify = true,
        $notifyExt = '',
        $nick = '',
        $avator = '',
        $ext = ''
    ) {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        if (!$accid || !is_string($accid)) {
            throw new YunXinArgException('进入聊天室账号id不能为空！');
        }
        if (strlen($accid) > self::ACCID_LEGAL_LENGTH) {
            throw new YunXinArgException('进入聊天室账号id超过限制！');
        }
        if (!is_bool($save)) {
            throw new YunXinArgException('参数save为bool类型！');
        }
        if (!is_bool($needNotify)) {
            throw new YunXinArgException('参数needNotify为bool类型！');
        }
        if (strlen($notifyExt) > self::CHAT_ROOM_NOTIFY_EXT_LIMIT) {
            throw new YunXinArgException('聊天室通知事件扩展字段超过限制！');
        }
        if (strlen($nick) > 64) {
            throw new YunXinArgException('聊天室昵称不超过64个字符！');
        }

        $res = $this->sendRequest('chatroom/updateMyRoomRole.action', [
            'roomid' => $roomId,
            'accid' => $accid,
            'save' => $save,
            'needNotify' => $needNotify,
            'notifyExt' => $notifyExt,
            'nick' => $nick,
            'avator' => $avator,
            'ext' => $ext,
        ]);
        return $res;
    }

    /**
     * 批量更新聊天室队列元素
     * @param $roomId
     * @param $operator
     * @param  array  $elements
     * @param  bool  $needNotify
     * @param  string  $notifyExt
     * @return mixed
     * @throws YunXinArgException
     * @throws YunXinBusinessException
     * @throws YunXinInnerException
     * @throws YunXinNetworkException
     * @throws GuzzleException
     */
    public function queueBatchUpdateElements($roomId, $operator, array $elements, $needNotify = true, $notifyExt = '')
    {
        if (!is_int($roomId)) {
            throw new YunXinArgException('房间id不合法！');
        }
        if (!$operator || !is_string($operator)) {
            throw new YunXinArgException('操作者id不能为空！');
        }
        if (strlen($operator) > self::ACCID_LEGAL_LENGTH) {
            throw new YunXinArgException('操作者id超过限制！');
        }
        if (!$elements) {
            throw new YunXinArgException('更新元素不能为空！');
        }
        if (count($elements) > 200) {
            throw new YunXinArgException('更新元素不能不能超过200个！');
        }
        if (!is_bool($needNotify)) {
            throw new YunXinArgException('参数needNotify为bool类型！');
        }
        if (strlen($notifyExt) > self::CHAT_ROOM_NOTIFY_EXT_LIMIT) {
            throw new YunXinArgException('聊天室通知事件扩展字段超过限制！');
        }

        $res = $this->sendRequest('chatroom/queueBatchUpdateElements.action', [
            'roomid' => $roomId,
            'operator' => $operator,
            'elements' => json_encode($elements),
            'needNotify' => $needNotify,
            'notifyExt' => $this->bool2String($notifyExt),
        ]);
        return $res['desc'];
    }

}