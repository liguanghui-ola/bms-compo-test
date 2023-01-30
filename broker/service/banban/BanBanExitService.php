<?php

namespace Imee\Compo\Broker\Service\BanBan;

use Imee\Compo\Broker\Model\XsBrokerUser;
use Imee\Compo\Broker\Service\BaseExitService;

class BanBanExitService extends BaseExitService
{
    protected function join(array $params): array
    {
        $userInfo = XsBrokerUser::findOne(47);
        return  [true,$userInfo];
    }

    protected function afterJoin(array $params, array $data): array
    {

        return  [true,"执行afterJoin完毕"];
    }
}
