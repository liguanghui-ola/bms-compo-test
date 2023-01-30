<?php

namespace Imee\Compo\Broker\Service\BanBan;

use Imee\Compo\Broker\Service\BaseJoinService;

class BanBanJoinService extends BaseJoinService
{
    protected  $operator = ["HasGs"];
    protected function join(array $params): array
    {
        if ($params["uid"] == 2) {
            return [false,"加入公会失败"];
        }
        return  [true,["join"=>2222]];
    }

    protected function afterJoin(array $params, array $data): array
    {

        return  [true,"执行afterJoin完毕"];
    }
}
