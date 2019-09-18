# JTL-Shop

|**JTL-Shop** is a commercial open source shopsoftware designed for use with JTL-Wawi. |
|:-----------------:|
| ![Screenshot](https://images.jtl-software.de/shop4/shop_release_showcase.png "JTL-Shop 4") |

## System Requirements

**Apache**
 * Version 2.2 or 2.4
 * mod_rewrite module activated
 * .htaccess support (allowed to override options)
  
**Database** 
* MySQL or MariaDB >= v5.0

**PHP**
* Version 7.0 or 5.6 recommended. Support for php 5.4 and 5.5 will be dropped in JTL-Shop version 4.07. 
* PHP-Modules: 
 * [GD](http://php.net/manual/en/book.image.php)
 * [SimpleXML](http://php.net/manual/en/book.simplexml.php)
 * [ImageMagick + Imagick](http://php.net/manual/en/book.imagick.php)
 * [Curl](http://php.net/manual/en/book.curl.php)
 * [Iconv](http://php.net/manual/en/book.iconv.php)
 * [MBString](http://php.net/manual/en/book.mbstring.php)
 * [Tokenizer](http://php.net/manual/en/book.tokenizer.php)
 * [PDO (MySQL)](http://php.net/manual/en/book.pdo.php)
 * Optional: [IonCube Loader](https://www.ioncube.com/loaders.php) for some third-party plug-ins
* PHP Settings
 * `max_execution_time` >= 120s
 * `memory_limit` >= 128MB
 * `upload_max_filesize` >= 6MB
 * `allow_url_fopen` activated
 * `magic_quotes_runtime` deactivated (removed since php v7.0)

## Software boundaries
* See [Software boundaries and limits](http://jtl-url.de/limits) for details

## License 
* Proprietary, see [LICENSE.md](LICENSE.md) for further details

## Changelog
* See [CHANGELOG.md](CHANGELOG.md) for details

## Third party libraries
* Smarty (http://www.smarty.net/) - LGPL
* Guzzle - MIT
* imanee - MIT
* CKEditor - LGPL
* elFinder - BSD
* CodeMirror - MIT
* Minify
* NuSoap - LGPLv2
* PCLZip - LGPL
* PHPMailer - LGPL
* phpQuery - MIT
* xajax - BSD

### Frontend Libs
* jQuery + jQuery UI + various jQuery Scripts - MIT
* Bootstrap + Bootstrap-Scripts - MIT
* Photoswipe - MIT
* FileInput - BSD
* imgViewer - MIT
* typeAhead - MIT
* WaitForImages - MIT
* LESS Leaner CSS - Apache v2 License
* slick (https://github.com/kenwheeler/slick/) - MIT

## Related Links

* [JTL](https://www.jtl-software.de) - JTL-Software Homepage
* [JTL Userguide](http://guide.jtl-software.de) - Userguide
* [JTL Developer Documentation](http://docs.jtl-shop.de) - Developer Docs
* [JTL Community](http://forum.jtl-software.de) - JTL-Forum 
* [JTL Feedback](http://feedback.jtl-software.de) - Feature Proposals
* [JTL Shop4-Entwicklung](https://gitlab.jtl-software.de/jtlshop/shop4) - Gitlab 
