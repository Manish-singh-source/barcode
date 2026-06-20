<?php

namespace App\Enums;

enum BarcodeFormat: string
{
    case Code128 = 'code128';
    case Qrcode = 'qrcode';
    case Code39 = 'code39';
    case Ean13 = 'ean13';
}
