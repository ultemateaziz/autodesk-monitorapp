<?php

namespace App\Services;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;

class GeoIPService
{
    private ?Reader $reader = null;

    private function getReader(): ?Reader
    {
        if ($this->reader) {
            return $this->reader;
        }

        $path = storage_path('app/geoip/GeoLite2-Country.mmdb');

        if (! file_exists($path)) {
            return null;
        }

        try {
            $this->reader = new Reader($path);
        } catch (InvalidDatabaseException) {
            return null;
        }

        return $this->reader;
    }

    public function getCountry(string $ip): string
    {
        // Cloudflare header takes priority — zero cost, instant
        if (request()->hasHeader('CF-IPCountry')) {
            $code = request()->header('CF-IPCountry');
            if ($code && $code !== 'XX') {
                return strtoupper($code);
            }
        }

        $reader = $this->getReader();

        if (! $reader) {
            return 'UNKNOWN';
        }

        // Local loopback / private IPs won't resolve — return UNKNOWN gracefully
        if (in_array($ip, ['127.0.0.1', '::1']) || $this->isPrivateIp($ip)) {
            return 'LOCAL';
        }

        try {
            $record = $reader->country($ip);
            return strtoupper($record->country->isoCode ?? 'UNKNOWN');
        } catch (AddressNotFoundException) {
            return 'UNKNOWN';
        } catch (\Exception) {
            return 'UNKNOWN';
        }
    }

    public function getCountryName(string $ip): string
    {
        $reader = $this->getReader();

        if (! $reader) {
            return 'Unknown';
        }

        if (in_array($ip, ['127.0.0.1', '::1']) || $this->isPrivateIp($ip)) {
            return 'Local';
        }

        try {
            $record = $reader->country($ip);
            return $record->country->name ?? 'Unknown';
        } catch (\Exception) {
            return 'Unknown';
        }
    }

    private function isPrivateIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }

    public function isAvailable(): bool
    {
        return $this->getReader() !== null;
    }
}
