<?php

namespace Imee\Package\Broker\Service\BanBan\Base;

abstract class BaseExitService
{
    protected $operator = ["Age", "Time", "IsHost"];

    public function __construct()
    {

    }
    //前置钩子

    /**
     * @throws Exception
     */
    protected function checkAllRule($brokerInfo, $uid): array
    {
        foreach ($this->operator as $class) {
            $class = "\Imee\Package\Broker\Service\Rule\\".$class . "Rule";

            if (!class_exists($class)) {
                throw new \Exception($class . "not found");
            }
            $obj = new $class;
            list($r, $msg) = $obj->rule($brokerInfo, $uid);
            if (!$r) {
                return [$r, $msg];
            }
        }
        return [true, ""];

    }


    // 加入之前
    abstract protected function join(array $params): array;

    // 加入之后
    abstract protected function afterJoin(array $params, array $data): array;

    // 组合调用

    /**
     * @throws Exception
     */
    public function handle(array $params): array
    {
        $uid = $params["uid"];
        $adminId = $params["adminId"];
        $brokerInfo = $params["brokerInfo"];
        // 检查是否符合规则
        list($r, $msg) = $this->checkAllRule($brokerInfo, $uid);
        if (!$r) {
            return [false, $msg];
        }
        // 加入逻辑 todo自定义通用逻辑

        list($r, $data) = $this->join($params);
        if (!$r) {
            return [false, $data];
        }
        $this->afterJoin($params, $data);
        return [true, "加入成功"];
    }


}