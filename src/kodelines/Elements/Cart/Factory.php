<?php

declare(strict_types=1);

namespace Elements\Cart;

use Slim\Routing\RouteCollectorProxy;
use Kodelines\Interfaces\FactoryInterface;

class Factory implements FactoryInterface
{

  /**
   * Chiamate standard per tutte le api
   *
   * @param RouteCollectorProxy $api
   * @return RouteCollectorProxy
   */
  public static function loadRoutes(RouteCollectorProxy $app): RouteCollectorProxy {

   
    /**
     * Carrello
     */
    $app->get('/cart', \Elements\Cart\Controllers\CartController::class . ':get')->setName('cart.get');

    $app->post('/cart', \Elements\Cart\Controllers\CartController::class . ':create')->setName('cart.create');

    $app->put('/cart', \Elements\Cart\Controllers\CartController::class . ':update')->setName('cart.update');

    $app->get('/cart/full', \Elements\Cart\Controllers\CartController::class . ':fullGet')->setName('cart.fullGet');

    $app->get('/cart/products', \Elements\Cart\Controllers\CartController::class . ':products')->setName('cart.products');

    $app->post('/cart/product', \Elements\Cart\Controllers\ProductsController::class . ':add')->setName('cart.product.add');

    $app->get('/cart/product/{id}', \Elements\Cart\Controllers\ProductsController::class . ':get')->setName('cart.products.get');

    $app->put('/cart/product/{id}', \Elements\Cart\Controllers\ProductsController::class . ':edit')->setName('cart.products.edit');

    $app->delete('/cart/product/{id}', \Elements\Cart\Controllers\ProductsController::class . ':delete')->setName('cart.products.delete');

    $app->post('/cart/coupon', \Elements\Cart\Controllers\CouponController::class . ':apply')->setName('cart.coupon.apply');

    $app->delete('/cart/coupon', \Elements\Cart\Controllers\CouponController::class . ':reset')->setName('cart.coupon.reset');

    $app->post('/cart/save', \Elements\Cart\Controllers\CartController::class . ':save')->setName('cart.save');

    $app->post('/cart/shipping', \Elements\Cart\Controllers\ShippingController::class . ':create')->setName('cart.shipping.create');

    $app->get('/cart/{id:[0-9]+}/shipping', \Elements\Cart\Controllers\ShippingController::class . ':getLast')->setName('cart.shipping.get.id');

    $app->get('/cart/shipping', \Elements\Cart\Controllers\ShippingController::class . ':getLast')->setName('cart.shipping.get');

    $app->get('/cart/shipping/rules', \Elements\Cart\Controllers\ShippingController::class . ':rules')->setName('cart.shipping.rules');

    $app->post('/cart/shipping/rules', \Elements\Cart\Controllers\ShippingController::class . ':rules')->setName('cart.shipping.rules.post');

    $app->post('/cart/payment/complete/{method}', \Elements\Cart\Controllers\PaymentController::class . ':complete')->setName('cart.payment.complete');

    $app->post('/cart/payment/create/{method}', \Elements\Cart\Controllers\PaymentController::class . ':start')->setName('cart.payment.create');

    $app->post('/cart/payment/webhook/paypal', \Elements\Cart\Controllers\WebHookController::class . ':paypal')->setName('cart.webhook.paypal');

    $app->post('/cart/payment/webhook/stripe', \Elements\Cart\Controllers\WebHookController::class . ':stripe')->setName('cart.webhook.stripe');

    $app->post('/cart/payment/webhook/usa', \Elements\Cart\Controllers\WebHookController::class . ':usa')->setName('cart.webhook.usa');

    $app->post('/cart/payment/check/{method}', \Elements\Cart\Controllers\PaymentController::class . ':check')->setName('cart.payment.check');


    //Alias chiamata put per wordpress
    $app->post('/cart/product/{id}/update', \Elements\Cart\Controllers\CartController::class . ':edit')->setName('cart.products.edit.alias');

    //Alias chiamata delete per wordpress
    $app->post('/cart/product/{id}/delete', \Elements\Cart\Controllers\CartController::class . ':delete')->setName('cart.products.delete.alias');



    return $app;
  }
 
}

?>