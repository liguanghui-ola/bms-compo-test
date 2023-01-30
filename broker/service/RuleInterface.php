<?php

namespace Imee\Compo\Broker\Service;
interface  RuleInterface
{
    public function rule(array $brokerInfo, int $uid, array $ext): array;
}


