## Foolz\Inet

This [Composer](http://getcomposer.org) package provides two functions that allow converting between IP Address and IP Decimal. It works for both IPv4 and IPv6.

Based on the [answer](http://stackoverflow.com/a/1271123/644504) to "[How to convert IPv6 from binary for storage in MySQL?](http://stackoverflow.com/questions/1120371/how-to-convert-ipv6-from-binary-for-storage-in-mysql)" on StackOverflow by [Sander Marechal](http://stackoverflow.com/users/103202/sander-marechal). Content posted on StackOverflow is under the [CC BY-SA 3.0 License](http://creativecommons.org/licenses/by-sa/3.0/).

#### Requirements

* [BCMath](http://php.net/manual/en/book.bc.php)
* PHP 5.3+

#### Usage

```php
$decimal_ip = \Foolz\Inet\Inet::ptod($ip);
$ip = \Foolz\Inet\Inet::dtop($decimal_ip);
```

#### Storage on MySQL Databases

You should use a `DECIMAL(39,0)` column to store the entire IP Decimal.
