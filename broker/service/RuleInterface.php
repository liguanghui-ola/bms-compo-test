<?php

namespace Imee\Package\Broker\Service;
interface  RuleInterface
{
    public function rule(array $brokerInfo, int $uid, array $ext): array;
}


