<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

/**
 * @see https://www.koordinaten-umrechner.de/decimal/51.388923,6.642949
 */
class GPS
{
    protected string $latitudeDirections = 'N';

    protected int $latitudeHours = 0;

    protected int $latitudeMinutes = 0;

    protected float $latitudeSeconds = 0;

    protected string $longitudeDirection = 'E';

    protected int $longitudeHours = 0;

    protected int $longitudeMinutes = 0;

    protected float $longitudeSeconds = 0;

    public function __construct(string $position)
    {
        $position = preg_replace("/[^NSOEW0-9\-\+\.\"\'\s]/", '', $position);
        if (preg_match('/^([\-\+]{0,1}\d{1,2})\.(\d{1,}) ([\-\+]{0,1}\d{1,3})\.(\d{1,})$/', $position, $match)) {
            $this->latitudeDirections = ($match[1] < 0) ? 'S' : 'N';
            $this->latitudeHours = (int)$match[1];
            $this->latitudeHours = $this->latitudeHours < 0 ? $this->latitudeHours * -1 : $this->latitudeHours;
            $tmp = (float)('0.' . $match[2]) * 60;
            $this->latitudeMinutes = (int)$tmp;
            $this->latitudeSeconds = (float)($tmp - (int)$tmp) * 60;
            $this->longitudeDirection = ($match[3] < 0) ? 'W' : 'E';
            $this->longitudeHours = (int)$match[3];
            $this->longitudeHours = $this->longitudeHours < 0 ? $this->longitudeHours * -1 : $this->longitudeHours;
            $tmp = (float)('0.' . $match[4]) * 60;
            $this->longitudeMinutes = (int)$tmp;
            $this->longitudeSeconds = (float)($tmp - (int)$tmp) * 60;
        // dd mm.mmm
        } elseif (preg_match(
            '/^(N|S) (\d{1,2}) (\d{1,2})\.(\d{1,6}) (O|E|W) (\d{1,3}) (\d{1,2})\.(\d{1,6})$/i',
            $position,
            $match
        )) {
            if ($match[5] == 'O') {
                $match[5] = 'E';
            }
            $this->latitudeDirections = strtoupper($match[1]);
            $this->latitudeHours = (int)$match[2];
            $this->latitudeMinutes = (int)$match[3];
            $this->latitudeSeconds = (float)((int)$match[4] / 1000000 * 60);
            $this->longitudeDirection = strtoupper($match[5]);
            $this->longitudeHours = (int)$match[6];
            $this->longitudeMinutes = (int)$match[7];
            $this->longitudeSeconds = (float)((int)$match[8] / 1000000 * 60);
        } elseif (preg_match(
            '/^(N|S)\s{0,1}(\d{1,2}) (\d{1,2}) (\d{1,2}) (E|O|W)\s{0,1}(\d{1,2}) (\d{1,2}) (\d{1,2})$/',
            $position,
            $match
        )) {
            if ($match[5] == 'O') {
                $match[5] = 'E';
            }
            $this->latitudeDirections = strtoupper($match[1]);
            $this->latitudeHours = (int)$match[2];
            $this->latitudeMinutes = (int)$match[3];
            $this->latitudeSeconds = (int)$match[4];
            $this->longitudeDirection = strtoupper($match[5]);
            $this->longitudeHours = (int)$match[6];
            $this->longitudeMinutes = (int)$match[7];
            $this->longitudeSeconds = (int)$match[8];
        } elseif (preg_match(
            '/^(N|S) (\d{1,2}) (\d{1,2})\' (\d{1,2}.\d{1,4})["|\'\'] (E|O|W) (\d{1,3}) (\d{1,2})\' (\d{1,2}.\d{1,4})["|\'\']$/i',
            $position,
            $match
        )) {
            if ($match[6] == 'O') {
                $match[6] = 'E';
            }
            $this->latitudeDirections = strtoupper($match[1]);
            $this->latitudeHours = (int)$match[2];
            $this->latitudeMinutes = (int)$match[3];
            $this->latitudeSeconds = (float)$match[4];
            $this->longitudeDirection = strtoupper($match[5]);
            $this->longitudeHours = (int)$match[6];
            $this->longitudeMinutes = (int)$match[7];
            $this->longitudeSeconds = (float)$match[8];
        }
    }

    public function getGPS(): string
    {
        return sprintf(
            '%s %s° %s.%s %s %s° %s.%s',
            $this->latitudeDirections,
            $this->latitudeHours,
            $this->latitudeMinutes,
            round($this->latitudeSeconds / 60 * 1000000),
            $this->longitudeDirection,
            $this->longitudeHours,
            $this->longitudeMinutes,
            round($this->longitudeSeconds / 60 * 1000000)
        );
    }

    public function getLatitude(): float
    {
        $result = $this->latitudeDirections == 'N' ? '' : '-';
        $result .= round($this->latitudeHours + ($this->latitudeMinutes / 60) + (($this->latitudeSeconds) / 3600), 6);
        return (float)$result;
    }

    public function getLongitude(): float
    {
        $result = $this->longitudeDirection == 'W' ? '-' : '';
        $result .= round(
            $this->longitudeHours + ($this->longitudeMinutes / 60) + (($this->longitudeSeconds) / 3600),
            6
        );
        return (float)$result;
    }

    public function getWGS84Degree(): string
    {
        return sprintf(
            '%s %s %s %s',
            $this->latitudeDirections,
            number_format($this->latitudeHours + $this->latitudeMinutes / 60 + $this->latitudeSeconds / 3600, 6),
            $this->longitudeDirection,
            number_format($this->longitudeHours + $this->longitudeMinutes / 60 + $this->longitudeSeconds / 3600, 6)
        );
    }

    public function getWGS84Seconds(): string
    {
        // dd° mm' ss.s"
        return sprintf(
            '%s %s° %s\' %s" %s %s° %s\' %s"',
            $this->latitudeDirections,
            $this->latitudeHours,
            $this->latitudeMinutes,
            number_format($this->latitudeSeconds, 3),
            $this->longitudeDirection,
            $this->longitudeHours,
            $this->longitudeMinutes,
            number_format($this->longitudeSeconds, 3)
        );
    }

    public function distance(GPS|string $position, string $unit = 'k'): float
    {
        if (!in_array($unit, ['k', 'm', 'n'])) {
            throw new \InvalidArgumentException('Unit must be a value of: k(ilometer), m(iles), n(autical miles)');
        }
        $position = $position instanceof self ? $position : new self($position);
        $latitudeA = $this->getLatitude();
        $longitudeA = $this->getLongitude();
        $latitudeB = $position->getLatitude();
        $longitudeB = $position->getLongitude();

        if (($latitudeA == $latitudeB) && ($longitudeA == $longitudeB)) {
            return 0;
        }

        $latFrom = deg2rad($latitudeA);
        $lonFrom = deg2rad($longitudeA);
        $latTo = deg2rad($latitudeB);
        $lonTo = deg2rad($longitudeB);
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        $angle = 2 * asin(sqrt((sin($latDelta / 2) ** 2) + cos($latFrom) * cos($latTo) * (sin($lonDelta / 2) ** 2)));
        $km = round($angle * 6371, 2);

        $unit = strtolower($unit);
        if ($unit === 'm') {
            return round($km * 0.621371, 2);
        }
        if ($unit === 'n') {
            return round($km * 0.539957, 2);
        }
        return $km;
    }
}
