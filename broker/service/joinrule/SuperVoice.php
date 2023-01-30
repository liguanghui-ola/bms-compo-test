<?php

namespace Imee\Compo\Broker\Service\JoinRule;

use Imee\Compo\Broker\Service\RuleInterface;

/**
 * 王牌公会限制
 */
class SuperVoice implements RuleInterface
{
    const  SUPER_VOICE_TYPE = "super_voice";

    public function rule(array $brokerInfo, int $uid, array $ext): array
    {
        if ($brokerInfo["types"] == self::SUPER_VOICE_TYPE) {
            return [false, "直播公会入会不满30天无法申请退会及强制退会"];
        }
        return [true, ""];
    }
}
