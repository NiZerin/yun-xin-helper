![https://yunxin.163.com/res/image/base/logo/logo-black@2x.png?v=5](https://yunxin.163.com/res/image/base/logo/logo-black@2x.png?v=5)

![](https://img.shields.io/packagist/dt/nizerin/yun-xin-helper.svg) ![](https://img.shields.io/packagist/v/nizerin/yun-xin-helper.svg) ![Packagist](https://img.shields.io/packagist/l/nizerin/yun-xin-helper.svg) [![codecov](https://codecov.io/gh/NiZerin/yun-xin-helper/branch/master/graph/badge.svg)](https://codecov.io/gh/NiZerin/yun-xin-helper)

# 安装

推荐使用 Composer：`composer require nizerin/yun-xin-helper`

# 使用
### 创建实例
```php
$appKey = '****'; // 网易云信分配的账号
$appSecrt = '****'; // 网易云信分配的密钥
$entrance = new \YunXinHelper\Entrance($appKey, $appSecrt);
```

### 用户
```php
# 创建用户
$entrance->user()->create($accid, $name, $icon);

# 用户基本信息更新
$entrance->user()->update($accid, $token);

# 封禁用户
$entrance->user()->block($accid);

# 解禁用户
$entrance->user()->unblock($accid);

# 更新用户名片
$entrance->user()->updateUserInfo($accid, $name, $icon);

# 批量获取用户名片
$entrance->user()->getUserInfos($accids);
```

### 消息功能
```php
# 文本消息
$entrance->chat()->sendTextMsg($accidFrom, $to, $open, $text);

# 图片消息
$entrance->chat()->sendPictureMsg($accidFrom, $to, $open,$picName, $picMD5, $picUrl, $picExt, $picWidth, $picHeight, $picSize);

# 批量文本消息
$entrance->chat()->sendTextBatchMsg($accidFrom, $accidsTo, $text);

# 发送自定义系统通知
$entrance->chat()->sendAttachMsg($from, CHAT::CHAT_ONT_TO_ONE, $to, $attach);
```

### 更多功能请查看 SRC
因为实在没时间写文档，后面我会逐渐完善，各位见谅