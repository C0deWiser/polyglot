<?php

use Illuminate\Support\Facades\Lang;

__('Short message');

Lang::get('Short message');
Illuminate\Support\Facades\Lang::get('Short message');

pgettext('Test context', 'Short message');

ngettext('One cat', 'Few cats', rand(0, 10));

trans_choice('short.plural', rand(0, 10));
trans_choice('One lamp|Few lamps', rand(0, 10));

__('short.message');