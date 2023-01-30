<?php

namespace Imee\Package\Broker\Service\Pt\JoinRule;

use Imee\Package\Broker\Model\XsUserProfile;
use Imee\Package\Broker\Service\RuleInterface;


/**
 * AppId是否相同，不支持不同APP的公会申请
 */
class AppId implements RuleInterface
{
    public function rule(array $brokerInfo, int $uid, array $ext): array
    {
        $userInfo = XsUserProfile::findOne($uid);
        if (!$userInfo || !isset($userInfo['app_id'])) {
            return [false, '用户信息不存在'];
        }
        $appId = $brokerInfo["app_id"] ?? 0;
        if ($appId != $userInfo["app_id"]) {
            return [false, "不支持不同APP的公会申请"];
        }
        return [true, ""];
    }


}