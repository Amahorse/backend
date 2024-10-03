<?php

declare(strict_types=1);

namespace Kodelines;

use Elements\Cart\Cart;
use Kodelines\Oauth\Token;

class Context {

	public static Config $config;

	public static Cart $cart;

	public static Token $token;

	public static array $parameters;
	
}

?>