<?php

namespace Foolz\Inet;

/**
 * Converts IPs from presentation to decimal and back
 */
class Inet
{
    /**
     * Converts an IP Address to IP Decimal
     *
     * @param string $ip_address IP Address
     * @return string IP Decimal
     */
    public static function ptod($ip_address)
    {
        // IPv4 Address
        if (strpos($ip_address, ':') === false && strpos($ip_address, '.') !== false) {
            $ip_address = '::'.$ip_address;
        }

        // IPv6 Address
        if (strpos($ip_address, ':') !== false) {
            $network = inet_pton($ip_address);
            $parts = unpack('N*', $network);

            foreach ($parts as &$part) {
                if ($part < 0) {
                    $part = (string) bcadd((string) $part, '4294967296');
                }

                if (!is_string($part)) {
                    $part = (string) $part;
                }
            }

            $decimal = $parts[4];
            $decimal = bcadd($decimal, bcmul($parts[3], '4294967296'));
            $decimal = bcadd($decimal, bcmul($parts[2], '18446744073709551616'));
            $decimal = bcadd($decimal, bcmul($parts[1], '79228162514264337593543950336'));

            return $decimal;
        }

        // Decimal IP Address
        return $ip_address;
    }

    /**
     * Converts an IP Decimal to IP Address
     *
     * @param string $ip_address IP Decimal
     * @return string IP Address
     */
    public static function dtop($decimal)
    {
        // IPv4 or IPv6
        if (strpos($decimal, ':') !== false || strpos($decimal, '.') !== false) {
            return $decimal;
        }

        // Decimal
        $parts = array();
        $parts[1] = bcdiv($decimal, '79228162514264337593543950336', 0);
        $decimal = bcsub($decimal, bcmul($parts[1], '79228162514264337593543950336'));
        $parts[2] = bcdiv($decimal, '18446744073709551616', 0);
        $decimal = bcsub($decimal, bcmul($parts[2], '18446744073709551616'));
        $parts[3] = bcdiv($decimal, '4294967296', 0);
        $decimal = bcsub($decimal, bcmul($parts[3], '4294967296'));
        $parts[4] = $decimal;

        foreach ($parts as &$part) {
            if (bccomp($part, '2147483647') == 1) {
                $part = bcsub($part, '4294967296');
            }

            $part = (int) $part;
        }

        $network = pack('N4', $parts[1], $parts[2], $parts[3], $parts[4]);
        $ip_address = inet_ntop($network);

        if (preg_match('/^::\d+.\d+.\d+.\d+$/', $ip_address)) {
            return substr($ip_address, 2);
        }

        return $ip_address;
    }
}
