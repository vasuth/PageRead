# ReadPage

ReadPage is a test application to collect information - name, price and description of selected items - from Sainsbury's grocery site. It returns with a JSON array of the collected data.

## Usage

From command line run `php -f ReadPage.php` or load the code into a web browser with a running web server and PHP.

## About the code

The code uses cURL to read the webpage and DOMDocument to access data. XAMPP v7.3.4 with components e.g.

  - PHP 7.3.4
  - Apache 2.4.39
  - MariaDB 10.1.38
  - Perl 5.16.3.1
  - OpenSSL 1.1.0g
  - phpMyAdmin 4.8.5

was used during development. The code can be tested with different initial webpages specified in the `pagename` variable at the beginning of the PHP file. 
