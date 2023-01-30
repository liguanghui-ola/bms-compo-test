<?php

namespace Imee\Package\Broker\Service\Banban\JoinRule;

use Imee\Package\Broker\Model\XsBroker;
use Imee\Package\Broker\Model\XsstRiskMoneyGiftOp;
use Imee\Package\Broker\Model\XsstBrokerUserExit;
use Imee\Package\Broker\Service\RuleInterface;


/**
 * 风控
 */
class Risk implements RuleInterface
{


    public function rule(array $brokerInfo, int $uid, array $ext): array
    {
        $bid = $brokerInfo["bid"];
        $where = [
            ["uid", "=", $uid]
        ];
        $riskInfo = XsstRiskMoneyGiftOp::findOneByWhere($where);
        if ($riskInfo["state"] == XsstRiskMoneyGiftOp::STATE_EXCEPTION) {
            return [false, "您的账号存在风险，已被限制加入公会"];
        }
        // 被风控的账号解除风控后,防止恶意退出，
        if ($riskInfo["state"] == XsstRiskMoneyGiftOp::STATE_NORMAL && $riskInfo["bid"] > 0) {
            // 找到退出公会信息
            $riskBroker = XsBroker::findOneByWhere([["bid", "=", $riskInfo["bid"]]]);
            if (!empty($riskBroker) && $riskBroker["deleted"] == 0 && $riskInfo["bid"] != $bid) {
                $exitLog = $this->getExitLog($riskInfo['updatetime'], $uid, $riskInfo['bid']);
                // 没有退会记录
                if (!$exitLog) {
                    return [false, '您所在的公会ID为' . $riskInfo['bid'] . '，请搜索该公会ID加入'];
                }
            }
        }

        return [true, ""];
    }

    private function getExitLog($hasRiskMoneyTime, $uid, $bid)
    {
        //风控后，是否有原公会的退会记录
        $userExit = XsstBrokerUserExit::findFirst(array(
            "uid=:uid: and state in ('success','auto_exit','auto_force_exit','sign_force_exit','formal_force_exit','creater_remove','admin_remove','white_remove')",
            "order" => 'dateline desc',
            "bind" => array("uid" => $uid)
        ));
        if ($userExit) {
            if ($userExit->bid != $bid)
                return true;
            if ($userExit->dateline > $hasRiskMoneyTime) {
                return true;
            }
        }
        return false;
    }

}