<?php

namespace App\Tags;

use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Tags\Tags;

class Orders extends Tags
{
    /**
     * The {{ orders }} tag.
     *
     * @return string|array
     */
    public function index()
    {
      $entries = Collection::find("orders")
      ->queryEntries()
      ->where("is_paid", true)
      ->get();
      return $entries->sortBy("customer.last_name");
    }

    /**
     * The {{ orders:example }} tag.
     *
     * @return string|array
     */
    public function example()
    {
        //
    }
}
