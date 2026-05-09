<?php

namespace App\Services;

use Carbon\Carbon;

class CodingService
{
    /**
     * Standard Coding Hours: 7:00 AM - 10:00 AM and 5:00 PM - 8:00 PM
     * Window Hours: 10:01 AM - 4:59 PM (Standard)
     * Makati Rule: No window hours (7:00 AM - 7:00 PM)
     */
    
    // Makati Polygon (Approximate Central Makati Area)
    protected static $makatiPolygon = [
        ['lat' => 14.5670, 'lng' => 121.0000], // North West (near Pasig River)
        ['lat' => 14.5650, 'lng' => 121.0450], // North East (near BGC border)
        ['lat' => 14.5350, 'lng' => 121.0400], // South East (near SLEX/Skyway)
        ['lat' => 14.5380, 'lng' => 121.0100], // South West (near Pasay border)
    ];

    // Major Thoroughfares (Polylines)
    protected static $majorRoads = [
        'EDSA' => [
            ['lat' => 14.6575, 'lng' => 121.0039], // Monumento
            ['lat' => 14.6349, 'lng' => 121.0331], // Quezon Ave
            ['lat' => 14.6186, 'lng' => 121.0506], // Cubao
            ['lat' => 14.5880, 'lng' => 121.0560], // Ortigas
            ['lat' => 14.5540, 'lng' => 121.0240], // Makati (Ayala)
            ['lat' => 14.5370, 'lng' => 121.0000], // Pasay (Rotonda)
        ],
        'C5' => [
            ['lat' => 14.6850, 'lng' => 121.0400], // Mindanao Ave
            ['lat' => 14.6300, 'lng' => 121.0750], // Libis
            ['lat' => 14.5600, 'lng' => 121.0650], // Bagong Ilog
            ['lat' => 14.5200, 'lng' => 121.0480], // Taguig
            ['lat' => 14.4800, 'lng' => 121.0450], // SLEX Interchange
        ],
        'Roxas Boulevard' => [
            ['lat' => 14.5900, 'lng' => 120.9750], // Manila
            ['lat' => 14.5500, 'lng' => 120.9850], // Pasay
            ['lat' => 14.5200, 'lng' => 120.9920], // Paranaque
        ]
    ];

    /**
     * Check if a unit is currently in a coding violation status.
     */
    public function checkViolation($plateNumber, $lat, $lng)
    {
        $now = Carbon::now('Asia/Manila');
        $dayOfWeek = $now->dayOfWeek; // 1 (Mon) to 5 (Fri)
        
        // No coding on Sat (6) and Sun (0)
        if ($dayOfWeek == 0 || $dayOfWeek == 6) {
            return null;
        }

        // 1. Check if today is the unit's coding day based on last digit
        $lastDigit = (int) substr($plateNumber, -1);
        $codingDay = $this->getCodingDayForDigit($lastDigit);

        if ($dayOfWeek !== $codingDay) {
            return null;
        }

        // 2. Identify Location Type (Makati vs Major Roads)
        $inMakati = $this->isInsidePolygon($lat, $lng, self::$makatiPolygon);
        $roadName = $this->getNearbyMajorRoad($lat, $lng);

        if (!$inMakati && !$roadName) {
            return null; // Not in a restricted area
        }

        // 3. Time Logic
        $timeStr = $now->format('H:i');
        
        if ($inMakati) {
            // Makati Rule: 7:00 AM to 7:00 PM (No window)
            if ($timeStr >= '07:00' && $timeStr < '19:00') {
                return [
                    'type' => 'Makati No Window',
                    'location' => 'Makati Area',
                    'time' => $now
                ];
            }
        } else {
            // Standard MMDA Rule: 7-10am and 5-8pm
            $isMorningCoding = ($timeStr >= '07:00' && $timeStr < '10:00');
            $isEveningCoding = ($timeStr >= '17:00' && $timeStr < '20:00');
            
            if ($isMorningCoding || $isEveningCoding) {
                return [
                    'type' => 'Standard Coding',
                    'location' => $roadName,
                    'time' => $now
                ];
            }
        }

        return null;
    }

    protected function getCodingDayForDigit($digit)
    {
        // 1-2 Mon (1), 3-4 Tue (2), 5-6 Wed (3), 7-8 Thu (4), 9-0 Fri (5)
        if ($digit == 1 || $digit == 2) return 1;
        if ($digit == 3 || $digit == 4) return 2;
        if ($digit == 5 || $digit == 6) return 3;
        if ($digit == 7 || $digit == 8) return 4;
        if ($digit == 9 || $digit == 0) return 5;
        return null;
    }

    protected function isInsidePolygon($lat, $lng, $polygon)
    {
        $inside = false;
        $numVertices = count($polygon);
        for ($i = 0, $j = $numVertices - 1; $i < $numVertices; $j = $i++) {
            if ((($polygon[$i]['lat'] > $lat) != ($polygon[$j]['lat'] > $lat)) &&
                ($lng < ($polygon[$j]['lng'] - $polygon[$i]['lng']) * ($lat - $polygon[$i]['lat']) / ($polygon[$j]['lat'] - $polygon[$i]['lat']) + $polygon[$i]['lng'])) {
                $inside = !$inside;
            }
        }
        return $inside;
    }

    protected function getNearbyMajorRoad($lat, $lng)
    {
        $threshold = 0.001; // Approx 100 meters
        foreach (self::$majorRoads as $name => $points) {
            if ($this->isNearPolyline($lat, $lng, $points, $threshold)) {
                return $name;
            }
        }
        return null;
    }

    protected function isNearPolyline($lat, $lng, $points, $threshold)
    {
        for ($i = 0; $i < count($points) - 1; $i++) {
            $dist = $this->distanceToSegment($lat, $lng, $points[$i], $points[$i+1]);
            if ($dist < $threshold) return true;
        }
        return false;
    }

    protected function distanceToSegment($lat, $lng, $p1, $p2)
    {
        // Simple 2D point-to-line segment distance
        $x = $lng; $y = $lat;
        $x1 = $p1['lng']; $y1 = $p1['lat'];
        $x2 = $p2['lng']; $y2 = $p2['lat'];

        $dx = $x2 - $x1;
        $dy = $y2 - $y1;

        if ($dx == 0 && $dy == 0) {
            return sqrt(pow($x - $x1, 2) + pow($y - $y1, 2));
        }

        $t = (($x - $x1) * $dx + ($y - $y1) * $dy) / ($dx * $dx + $dy * $dy);

        if ($t < 0) {
            return sqrt(pow($x - $x1, 2) + pow($y - $y1, 2));
        } elseif ($t > 1) {
            return sqrt(pow($x - $x2, 2) + pow($y - $y2, 2));
        }

        $closestX = $x1 + $t * $dx;
        $closestY = $y1 + $t * $dy;

        return sqrt(pow($x - $closestX, 2) + pow($y - $closestY, 2));
    }
}
