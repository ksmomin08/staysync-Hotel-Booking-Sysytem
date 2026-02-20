<?php
//room detail
//minimum amount
$room_name = "Standard Room";
$room_price = 400;
$currency = "INR";

/* stripe API  configuration
 * remember to switch to your live publishable and secret key in production
 * 
 */

define('STRIPE_PUBLISHABLE_KEY', 'pk_test_51T2QftIzWibxiHBxoj9L0Qxt5Lz31WuZeal19YWsGNQE3piYIhvyYpMI8lifGgRbAl6vHJtlPae0ebXqycniXIWh00xDDIZMiV');
define('STRIPE_SECRET_KEY',' sk_test_51T2QftIzWibxiHBxCybIwGtLm3IIoUKo4efgjsHObtdGNbS7UwBmghbkFYqeld0mOYlLazONTwc5Fb6tEeAE6g63007QErQ7kY');

//DATABSE CONFINGURATION

define('DB_HOST', 'local host');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'staysync');
?>