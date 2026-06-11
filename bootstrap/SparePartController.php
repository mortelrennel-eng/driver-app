<?php

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount)
    {
        return '₱' . number_format((float)$amount, 2);
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'M d, Y')
    {
        if (empty($date)) return 'N/A';
        return date($format, strtotime($date));
    }
}

if (!function_exists('formatDateTime')) {
    function formatDateTime($datetime, $format = 'M d, Y h:i A')
    {
        if (empty($datetime)) return 'N/A';
        return date($format, strtotime($datetime));
    }
}

if (!function_exists('slugify')) {
    function slugify($text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return $text ?: 'n-a';
    }
}

if (!function_exists('truncate')) {
    function truncate($text, $length = 50, $suffix = '...')
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $suffix;
    }
}

if (!function_exists('statusBadge')) {
    function statusBadge(string $status): string
    {
        return match (strtolower($status)) {
            'active', 'completed', 'approved', 'paid'  => 'bg-green-100 text-green-800',
            'maintenance', 'in_progress', 'pending'     => 'bg-yellow-100 text-yellow-800',
            'coding', 'denied', 'cancelled'             => 'bg-red-100 text-red-800',
            'retired', 'expired'                        => 'bg-gray-100 text-gray-600',
            default                                     => 'bg-blue-100 text-blue-800',
        };
    }
}

