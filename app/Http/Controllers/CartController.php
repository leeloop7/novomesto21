<?php

namespace App\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\BaseActionController;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Cart\DestroyRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Cart\IndexRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Cart\UpdateRequest;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as SitesSite;

class CartController extends BaseActionController
{
    use CartDriver;

    public function index(IndexRequest $request)
    {
        if (! $this->hasCart()) {
            return [];
        }

        return $this->getCart()->toResource();
    }

    public function update(UpdateRequest $request)
    {

        $cart = $this->getCart();
        $data = Arr::except($request->all(), ['_token', '_params', '_redirect', '_request']);

        foreach ($data as $key => $value) {
            if ($value === 'on') {
                $value = true;
            } elseif ($value === 'off') {
                $value = false;
            }
            $data[$key] = $value;
        }

        if($request->get('competitors')) {
            $competitors = array_map(fn($item) => ['cells' => array_values($item)], $request->get('competitors'));
            array_unshift($competitors, ['cells' => ['Name', 'Surname', 'Shirt size', 'Phone number', 'Birthyear', 'Gender', 'Best time', 'DP Vet' ]]);
            $data['competitors'] = $competitors;
        }

        if (isset($data['customer'])) {
            try {
                if ($cart->customer() && $cart->customer() !== null) {
                    $customer = $cart->customer();
                } elseif (isset($data['customer']['email']) && $data['customer']['email'] !== null) {
                    $customer = Customer::findByEmail($data['customer']['email']);
                } else {
                    throw new CustomerNotFound("Customer with ID [{$data['customer']}] could not be found.");
                }
            } catch (CustomerNotFound $e) {
                $customer = Customer::create([
                    'name'  => isset($data['customer']['name']) && isset($data['customer']['surname']) ?
                                    $data['customer']['name'] . ' ' . $data['customer']['surname'] : '',
                    'email' => $data['customer']['email'],
                    'published' => true,
                ], $this->guessSiteFromRequest()->handle());
            }

            if (is_array($data['customer'])) {
                $customer->data($data['customer'])->save();
            }

            $cart->data([
                'customer' => $customer->id,
            ])->save();

            unset($data['customer']);
        }

        if (isset($data['email'])) {
            try {
                if (isset($data['email']) && $data['email'] !== null) {
                    $customer = Customer::findByEmail($data['email']);
                } else {
                    throw new CustomerNotFound("Customer with ID [{$data['customer']}] could not be found.");
                }
            } catch (CustomerNotFound $e) {
                $customer = Customer::create([
                    'name'  => isset($data['name']) && isset($data['surname']) ?
                                    $data['name'] . ' ' . $data['surname'] : '',
                    'email' => $data['email'],
                    'published' => true,
                ], $this->guessSiteFromRequest()->handle());
            }

            $cart->data([
                'customer' => $customer->id,
            ])->save();

            unset($data['name']);
            unset($data['surname']);
            unset($data['email']);
        }

        if ($data !== null) {
            $cart->data($data);
        }

        $cart->save()
            ->recalculate();

        return $this->withSuccess($request, [
            'message' => __('simple-commerce.messages.cart_updated'),
            'cart'    => $cart->toResource(),
        ]);
    }

    public function destroy(DestroyRequest $request)
    {
        $this
            ->getCart()
            ->data([
                'items' => [],
            ])
            ->save()
            ->recalculate();

        return $this->withSuccess($request, [
            'message' => __('simple-commerce.messages.cart_deleted'),
            'cart'    => null,
        ]);
    }

    protected function guessSiteFromRequest(): SitesSite
    {
        if ($site = request()->get('site')) {
            return Site::get($site);
        }

        foreach (Site::all() as $site) {
            if (Str::contains(request()->url(), $site->url())) {
                return $site;
            }
        }

        if ($referer = request()->header('referer')) {
            foreach (Site::all() as $site) {
                if (Str::contains($referer, $site->url())) {
                    return $site;
                }
            }
        }

        return Site::current();
    }
}
