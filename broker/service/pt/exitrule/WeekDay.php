<?php

namespace Imee\Package\Broker\Service\Pt\ExitRule;

use Imee\Package\Broker\Service\RuleInterface;


/**
 *  周二周三不准退会
 */
class WeekDay implements RuleInterface
{
    public function rule(array $brokerInfo, int $uid, array $ext): array
    {
        if (in_array(date("w"), array(2, 3))) {
            return [false, '周二/周三禁止操作退出公会'];
        }
        return [true, ""];
    }

}