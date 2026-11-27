<?php

namespace App\Services;

use App\Models\Badge;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

final class BadgeQrCodeService
{
    public function make(Badge $badge, int $size = 260): QrCode
    {
        return new QrCode(
            data: (string) $badge->qr_token,
            size: $size,
            margin: 10,
        );
    }

    public function svg(Badge $badge, int $size = 260): string
    {
        return (new SvgWriter())->write($this->make($badge, $size))->getString();
    }

    public function dataUri(Badge $badge, int $size = 260): string
    {
        return (new SvgWriter())->write($this->make($badge, $size))->getDataUri();
    }
}
