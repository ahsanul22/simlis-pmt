<?php

namespace App\Enums;

enum DeviceCategory: string
{
    case FIREWALL = 'firewall';
    case WEB_GATEWAY = 'web_gateway';
    case SWITCH = 'switch';
    case ROUTER = 'router';
    case ACCESS_POINT = 'access_point';
    case SERVER = 'server';
    case STORAGE = 'storage';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FIREWALL => 'Firewall / NGFW',
            self::WEB_GATEWAY => 'Internet Access Gateway',
            self::SWITCH => 'Switch',
            self::ROUTER => 'Router',
            self::ACCESS_POINT => 'Access Point',
            self::SERVER => 'Server',
            self::STORAGE => 'Storage',
            self::OTHER => 'Lainnya',
        };
    }
}
