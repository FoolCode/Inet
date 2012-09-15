## Foolz\Inet

This [Composer](http://getcomposer.org) package contains a class two static functions to convert IPs from presentation to decimal. It works both for IPv6 and IPv4.

This is especially useful to store 

This package is based on an answer [this question on StackOverflow](http://stackoverflow.com/questions/1120371/how-to-convert-ipv6-from-binary-for-storage-in-mysql).


#### Requirements

* [BCMath](http://php.net/manual/en/book.bc.php) (preferred) or [Math_BigInteger](http://pear.php.net/reference/Math_BigInteger-1.0.0RC2/Math_BigInteger/Math_BigInteger.html) (untested)
* PHP 5.3+


#### Usage

	$decimal_ip = \Foolz\Inet\Inet::ptod($ip);

	$ip = \Foolz\Inet\Inet::dtop($decimal_ip);

#### Store it in MySQL

Instead of using a `varchar`, create a `decimal (39,0)` column that will store the entire length of the number.