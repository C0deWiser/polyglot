<?php

use Illuminate\Support\Facades\Lang;

__('Short message');

/* The message will be shown at test page only. */
echo gettext('Hello world.');

gettext('May');
pgettext('Month', 'May');

ngettext('One cat', 'Few cats', rand(0, 10));

npgettext('Test context', 'One cat', 'Few cats', rand(0, 10));

trans_choice('short.plural', rand(0, 10));
trans_choice('One lamp|Few lamps', rand(0, 10));

__('short.message');
__('short.long.message');