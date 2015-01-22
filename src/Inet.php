<?php

namespace Foolz\Inet;

/**
 * Converts IPs from presentation to decimal and back
 */
class Inet
{
    /**
     * Converts the presentation IP to decimal IP
     * 
     * Tries to fall back to Math_Biginteger if available
     * Provides no validation, you can use filter_var($ip, FILTER_VALIDATE_IP) if needed
     * 
     * @param string $ip_address Presentation IP
     * @return string Decimal IP
     */
    public static function ptod($ip_address)
    {
        $fallback = false;

        if (!function_exists('bcadd')) {
            $fallback = true;
        }

        // IPv4 address
        if (strpos($ip_address, ':') === false && strpos($ip_address, '.') !== false) {
            $ip_address = '::'.$ip_address;
        }

        // IPv6 address
        if (strpos($ip_address, ':') !== false) {
            $network = inet_pton($ip_address);
            $parts = unpack('N*', $network);

            foreach ($parts as &$part) {
                if ($part < 0) {
                    if ($fallback) {
                        $part = bcadd((string) $part, '4294967296');
                    } else {
                        $part = new Math_Biginteger($part);
                        $magic = new Math_Biginteger('4294967296');
                        $part = $part->add($magic)->toString();
                    }
                }

                if (!is_string($part)) {
                    $part = (string) $part;
                }
            }

            $decimal = $parts[4];
            if (!$fallback) {
                $decimal = bcadd($decimal, bcmul($parts[3], '4294967296'));
                $decimal = bcadd($decimal, bcmul($parts[2], '18446744073709551616'));
                $decimal = bcadd($decimal, bcmul($parts[1], '79228162514264337593543950336'));
            } else {
                $parts_big = array();
                $parts_big_mul = array();

                $decimal = new Math_Biginteger($decimal);

                $parts_big[3] = new Math_Biginteger($parts[3]);
                $parts_big_mul[3] = new Math_Biginteger('4294967296');
                $parts_mul = $parts_big[3]->multiply($parts_big_mul[3]);
                $decimal = $parts_mul->add($decimal);

                $parts_big[2] = new Math_Biginteger($parts[2]);
                $parts_big_mul[2] = new Math_Biginteger('18446744073709551616');
                $parts_mul = $parts_big[2]->multiply($parts_big_mul[2]);
                $decimal = $parts_mul->add($decimal);

                $parts_big[1] = new Math_Biginteger($parts[1]);
                $parts_big_mul[1] = new Math_Biginteger('79228162514264337593543950336');
                $parts_mul = $parts_big[1]->multiply($parts_big_mul[1]);
                $decimal = $parts_mul->add($decimal);

                $decimal = $decimal->toString();
            }

            return $decimal;
        }

        // Decimal address
        return $ip_address;
    }


    /**
     * Converts the decimal IP to presentation IP
     * 
     * Tries to fall back to Math_Biginteger if available
     * Provides no validation, you can use filter_var($ip, FILTER_VALIDATE_IP) if needed
     * 
     * @param string $ip_address Decimal IP
     * @return string Presentation IP
     */
    public static function dtop($decimal)
    {
        // fallback since BC Math is something to add at compile time
        $fallback = false;

        if (!extension_loaded('bcmath')) {
            $fallback = true;
        }

        // IPv4 or IPv6 format
        if (strpos($decimal, ':') !== false || strpos($decimal, '.') !== false) {
            return $decimal;
        }

        // Decimal format
        if (!$fallback) {
            $parts = array();
            $parts[1] = bcdiv($decimal, '79228162514264337593543950336', 0);
            $decimal = bcsub($decimal, bcmul($parts[1], '79228162514264337593543950336'));
            $parts[2] = bcdiv($decimal, '18446744073709551616', 0);
            $decimal = bcsub($decimal, bcmul($parts[2], '18446744073709551616'));
            $parts[3] = bcdiv($decimal, '4294967296', 0);
            $decimal = bcsub($decimal, bcmul($parts[3], '4294967296'));
            $parts[4] = $decimal;
        } else {
            $parts_big = array();
            $parts_big_mul = array();

            $decimal = new Math_Biginteger($decimal);

            $parts_big_mul[1] = new Math_Biginteger('79228162514264337593543950336');
            list($parts_big[1]) = $decimal->divide($parts_big_mul[1]);
            $decimal = $decimal->subtract($parts_big[1]->multiply($parts_big_mul[1]));

            $parts_big_mul[2] = new Math_Biginteger('18446744073709551616');
            list($parts_big[2]) = $decimal->divide($parts_big_mul[2]);
            $decimal = $decimal->subtract($parts_big[2]->multiply($parts_big_mul[2]));

            $parts_big_mul[3] = new Math_Biginteger('4294967296');
            list($parts_big[3]) = $decimal->divide($parts_big_mul[3]);
            $decimal = $decimal->subtract($parts_big[3]->multiply($parts_big_mul[3]));

            $decimal = $decimal->toString();
            $parts_big[] = $decimal;

            $parts = $parts_big;
        }

        foreach ($parts as &$part) {
            if (!$fallback) {
                if (bccomp($part, '2147483647') == 1) {
                    $part = bcsub($part, '4294967296');
                }

                $part = (int) $part;
            } else {
                $part = new Math_Biginteger($part);

                if ($part->compare($parts_big_mul[3])) {
                    $part = $part->subtract($parts_big_mul[3]);
                }

                $part = (int) $part->toString();
            }
        }

        $network = pack('N4', $parts[1], $parts[2], $parts[3], $parts[4]);
        $ip_address = inet_ntop($network);

        // Turn IPv6 to IPv4 if it's IPv4
        if (preg_match('/^::\d+.\d+.\d+.\d+$/', $ip_address)) {
            return substr($ip_address, 2);
        }

        return $ip_address;
    }
}
