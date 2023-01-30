<?php


namespace Imee\Compo\Broker\Service\JoinRule;


use Imee\Compo\Broker\Model\XsBroker;
use Imee\Compo\Broker\Model\XsBrokerUser;
use Imee\Compo\Broker\Service\RuleInterface;

/**
 * 判断用户是否已加入了公会（待审核｜已加入俩种）
 */
class HasJoin implements RuleInterface
{

    public function rule(array $brokerInfo, int $uid,array $ext): array
    {
        // 找到审核通过或者待审核的
        $userBrokerInfo = XsBrokerUser::findOneByWhere(
            [
                ["uid", "=", $uid],
                ["state", "<=", XsBrokerUser::STATE_PASS],
            ]
        );
        if (!empty($userBrokerInfo)) {
            // 已有公会
            if ($userBrokerInfo["state"] == XsBrokerUser::STATE_PASS) {
                $bHasBroker = XsBroker::findOneByWhere([
                    [
                        "bid" => $userBrokerInfo["bid"]
                    ]
                ]);
                return [false, "您已加入公会，会长ID" . $bHasBroker['creater']];
            }
            // 已有待审核的公会
            if ($userBrokerInfo["bid"] != $brokerInfo["bid"]) {
                return [false, '您已申请加入其他公会，请等待审核'];
            } else {
                return [false, '您已申请加入公会，请等待公会长审核'];
            }
        }
        return [true, ""];
    }
}