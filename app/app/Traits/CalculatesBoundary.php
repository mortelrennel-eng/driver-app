<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait CalculatesBoundary
{
    /**
     * Calculate the effective boundary rate for a unit or driver based on a given date, 
     * year brackets, and coding rules.
     */
    public function getCurrentPricing($unit_data, $rules = null, $date = null)
    {
        if (!$rules) {
            $rules = DB::table('boundary_rules')->get();
        }

        // Support both Eloquent objects and raw DB objects (array-like or object-like)
        $year = (int) data_get($unit_data, 'year', data_get($unit_data, 'assigned_unit_year', 0));
        $customRate = (float) data_get($unit_data, 'boundary_rate', data_get($unit_data, 'daily_boundary_target', 0));
        $plate = data_get($unit_data, 'plate_number');
        
        $coding_day = data_get($unit_data, 'coding_day', data_get($unit_data, 'assigned_coding_day'));
        if (!$coding_day && $plate) {
            $coding_day = $this->deriveCodingDay($plate);
        }
        
        $timestamp = $date ? strtotime($date) : time();
        $dayName = date('l', $timestamp);
        $is_coding = $coding_day && (strtolower($dayName) === strtolower($coding_day));
        
        // Find matching rule by year
        $rule = $rules->where('start_year', '<=', $year)->where('end_year', '>=', $year)->first();
        
        // Base rate priority: Custom -> Rule -> Default
        $base = $customRate > 0 ? $customRate : ($rule ? (float)$rule->regular_rate : 1100);
        $final = $base;
        $label = 'Regular Rate';
        $type = 'regular';

        if ($is_coding) {
            if ($rule) {
                $final = (float) $rule->coding_rate > 0 ? (float) $rule->coding_rate : ($base / 2);
            } else {
                $final = $base / 2;
            }
            $label = 'Coding Rate';
            $type = 'coding';
        } elseif ($dayName === 'Saturday') {
            $discount = $rule ? (float)$rule->sat_discount : 100;
            $final = $base - $discount;
            $label = 'Saturday Discount';
            $type = 'discount';
        } elseif ($dayName === 'Sunday') {
            $discount = $rule ? (float)$rule->sun_discount : 200;
            $final = $base - $discount;
            $label = 'Sunday Discount';
            $type = 'discount';
        }

        return [
            'rate' => $final,
            'label' => $label,
            'type' => $type,
            'base' => $base,
            'coding_day' => $coding_day
        ];
    }

    /**
     * Calculate the theoretical maximum expected boundary for a full month
     * taking into account coding and weekends for every single day.
     */
    public function getMonthlyExpectedTarget($unit_data, $month = null, $year = null)
    {
        $month = $month ?: date('m');
        $year = $year ?: date('Y');
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        $rules = DB::table('boundary_rules')->get();
        $totalTarget = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $pricing = $this->getCurrentPricing($unit_data, $rules, $date);
            $totalTarget += $pricing['rate'];
        }

        return $totalTarget;
    }

    /**
     * Determine coding day from plate number (last digit)
     */
    private function deriveCodingDay($plate)
    {
        if (empty($plate)) return null;
        
        $last_char = substr(trim($plate), -1);
        if (!is_numeric($last_char)) {
            // Try to find the last numeric character if the plate ends in a letter
            preg_match_all('/\d/', $plate, $matches);
            if (!empty($matches[0])) {
                $last_char = end($matches[0]);
            } else {
                return null;
            }
        }

        $last_digit = (int) $last_char;
        $mapping = [
            'Monday' => [1, 2],
            'Tuesday' => [3, 4],
            'Wednesday' => [5, 6],
            'Thursday' => [7, 8],
            'Friday' => [9, 0],
        ];

        foreach ($mapping as $day => $digits) {
            if (in_array($last_digit, $digits)) return $day;
        }

        return null;
    }
}
