<?php
// /src/helpers/price.php will update the price calculation logic
function calculatePrice($mainPrice, $discountPercent) {
    $main = (float) $mainPrice;
    $discount = (float) $discountPercent;
    if ($main <= 0) return 0.00;
    $price = $main - ($main * ($discount / 100));
    // Keep two decimal places like your schema decimal(12,2)
    return round($price, 2);
}
