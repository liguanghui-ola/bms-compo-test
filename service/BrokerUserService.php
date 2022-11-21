<?php

namespace Imee\Compo\Test\Service;
use Imee\Compo\Test\Model\XsBrokerUser;
class BrokerUserService
{
    public function test()
    {
       $res =  XsBrokerUser::getValueByUid(1);
       var_dump([$res,COMPO_PROXY_URL]);
    }
}
