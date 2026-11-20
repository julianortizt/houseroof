<?php

return [
    'shipping_cost' => (float) env('COMMERCE_SHIPPING_COST', 15.00),
    'tax_rate' => (float) env('COMMERCE_TAX_RATE', 0.21),
];
