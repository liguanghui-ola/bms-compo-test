<?php

namespace Imee\Compo\Broker\Service\JoinRule;


use Imee\Compo\Broker\Model\BmsSignAddBrokerLog;
use Imee\Compo\Broker\Service\RuleInterface;

/**
 * 先同意协议
 */
class Agreement implements RuleInterface
{


    public function rule(array $brokerInfo, int $uid,array $ext): array
    {
        $isSign = BmsSignAddBrokerLog::findOneByWhere(
            [
                ["uid", "=", $uid],
                ["bid", "=", $brokerInfo["bid"]]
            ]
        );
        if (!$isSign || $isSign["op_status"] != BmsSignAddBrokerLog::STATUS_AGREE) {
            return [false, '必须先同意加入公会协议！'];
        }
        return [true, ""];
    }
}