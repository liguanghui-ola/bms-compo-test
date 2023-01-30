<?php

namespace Imee\Package\Broker\Service\JoinRule;

use Imee\Package\Broker\Model\XsBroker;
use Imee\Package\Broker\Model\XsstBrokerUserExit;
use Imee\Package\Broker\Service\RuleInterface;

/**
 * 距离退出上一个公会还不足两个月，不能加入娱乐或者陪玩的公会！
 */
class ExitTime implements RuleInterface
{
    const  EXIT_MONTH = 60 * 24 * 3600;

    public function rule(array $brokerInfo, int $uid, array $ext): array
    {
        $field = "state,dateline";
        $condition = [
            ["uid", "=", $uid],
            ["state", "in", ["success", "auto_exit", "auto_force_exit", "sign_force_exit", "formal_force_exit"]]
        ];
        // 获取上次退出的公会
        $userExit = XsstBrokerUserExit::getListByWhere($condition, $field, "dateline desc", 1);
        if (!empty($userExit)) {
            $userExitInfo = $userExit[0];
            $exitBroker = XsBroker::findOneByWhere([["bid", "=", $userExitInfo['bid']]]);
            if ((in_array($exitBroker['types'], ["live", "talk"])) && (in_array($brokerInfo['types'], ["play", "fun"])) && time() < ($userExitInfo['dateline'] + self::EXIT_MONTH)) {
                return [false, "距离退出上一个公会还不足两个月，不能加入娱乐或者陪玩的公会！"];
            }
        }
        return [true, ""];
    }
}
