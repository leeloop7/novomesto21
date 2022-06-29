<?php

use Statamic\Tags\Loader as TagLoader;
use Statamic\View\Antlers\Parser;

function tag(string $name, array $params = [])
{
    if ($pos = strpos($name, ':')) {
        $original_method = substr($name, $pos + 1);
        $method = Str::camel($original_method);
        $name = substr($name, 0, $pos);
    } else {
        $method = $original_method = 'index';
    }

    $tag = app(TagLoader::class)->load($name, [
        'parser'     => app(Parser::class),
        'params'     => $params,
        'content'    => '',
        'context'    => [],
        'tag'        => $name.':'.$original_method,
        'tag_method' => $original_method,
    ]);

    return $tag->$method();
}

function orders_count(string $type)
{
    return tag("collection:orders", ['locale' => 'default'])
        ->sum(fn($order) => isset($order->items[0]) && $order->items[0]["variant"]["variant"] === $type ? $order->items[0]["quantity"] : 0);
}
