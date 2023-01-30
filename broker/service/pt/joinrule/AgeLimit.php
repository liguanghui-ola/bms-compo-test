<?php

namespace Imee\Package\Broker\Service\Pt\JoinRule;

use Imee\Package\Broker\Model\XsUserIdcard;
use Imee\Package\Broker\Service\RuleInterface;
use Imee\Libs\Utility;


/**
 * 年龄检查,判断年龄是否处于 18-70岁
 */
class AgeLimit implements RuleInterface
{
    public function rule(array $brokerInfo, int $uid, array $ext): array
    {
        $idCard = XsUserIdcard::findOneByWhere(
            [
                ["uid", "=", $uid],
            ]
        );
        // todo 没有实名认证直接过了？
        if ($idCard && $idCard['cardnum']) {
            $age = Utility::get_age($idCard['cardnum']);
            if ($age < 18 || $age > 70)
                return [false, "由于政策限制，仅限18-70岁的用户加入公会！"];
        }

        return [true, ""];
    }

}