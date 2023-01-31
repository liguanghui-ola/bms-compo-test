<?php

class Broker
{
    const PROJECT_PT_ADMIN = "pt-admin";

    public function join(int $uid, $bid, array $ext = []): array
    {
        $project = $ext["project_name"];
        switch ($project) {
            case self::PROJECT_PT_ADMIN:
                return [true,"测试"];
            default:
                return [false,"项目不存在"];
        }
    }


}