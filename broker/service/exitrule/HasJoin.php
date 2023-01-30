<?php


namespace Imee\Package\Broker\Service\ExitRule;


use Imee\Package\Broker\Model\XsBroker;
use Imee\Package\Broker\Model\XsBrokerUser;
use Imee\Package\Broker\Service\RuleInterface;

/**
 * 判断用户是否已加入了公会
 */
class HasJoin implements RuleInterface
{

    public function rule(array $brokerInfo, int $uid, array $ext): array
    {
        // 找到审核通过或者待审核的
        $userBrokerInfo = XsBrokerUser::findOneByWhere(
            [
                ["uid", "=", $uid],
                ["state", "<=", XsBrokerUser::STATE_PASS],
            ]
        );
        if (empty($userBrokerInfo)) {
            return [false, '操作有误,请联系管理员'];
        }
        if ($userBrokerInfo['exit'] == 1) {
            return [false, "你已经提交了退会申请请耐心等待"];
        }
        if ($userBrokerInfo["state"] == 0) {
            return [false, "操作有误,请联系管理员"];
        }
        if ( ($userBrokerInfo['types'] == 'live' || $userBrokerInfo['types'] == 'talk') && time() - intval($userBrokerInfo['dateline']) <= 30 * 86400) {
            return [false,'直播公会入会不满30天无法申请退会及强制退会'];
        }

        return [true, ""];
    }
}