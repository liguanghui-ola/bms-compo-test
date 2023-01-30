<?php

namespace Imee\Package\Broker\Service\Pt;

abstract class PtBaseJoinService
{
    protected $operator = [];

    public function __construct()
    {

    }
    //前置钩子

    /**
     * @throws Exception
     */
    protected function checkAllRule(array $brokerInfo, int $uid, array $ext): array
    {
        foreach ($this->operator as $class) {
            $class = "\Imee\Package\Broker\Service\JoinRule\\" . $class;
            if (!class_exists($class)) {
                throw new \Exception($class . "not found");
            }
            $obj = new $class;
            list($r, $msg) = $obj->rule($brokerInfo, $uid, $ext);
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
        $ext = $params["ext"] ?? [];// 扩展字段，暂时没用
        $brokerInfo = $params["brokerInfo"];
        // 检查是否符合规则
        list($r, $msg) = $this->checkAllRule($brokerInfo, $uid, $ext);
        if (!$r) {
            return [false, $msg];
        }
        list($r, $data) = $this->join($params);
        if (!$r) {
            // todo 日志
            return [false, $data];
        }
        $this->afterJoin($params, $data);
        return [true, "加入成功"];
    }


}