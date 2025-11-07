<?php

namespace App\Helpers;

class MoneyHelper
{
    /**
     * Format số tiền - KHÔNG có phần thập phân
     */
    public static function format($amount)
    {
        return number_format((float)$amount, 0, ',', '.');
    }
    
    /**
     * Format với đơn vị VNĐ
     */
    public static function formatWithCurrency($amount)
    {
        return self::format($amount) . ' VNĐ';
    }
}
