<?php

use Illuminate\Support\Facades\Lang;

__('Short message');

Lang::get('Short message');
Illuminate\Support\Facades\Lang::get('Short message');

trans('Short message');

ngettext('One cat', 'Few cats', rand(0, 10));

trans_choice('goods.lamp', rand(0, 10));
trans_choice('One lamp|Few lamps', rand(0, 10));

__('short.message');