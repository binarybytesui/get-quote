<?php
// /src/helpers/price.php

function calculatePrice($mainPrice, $discountPercent, $labourCharges = 0, $wireCost = 0) {

    $main = (float) $mainPrice;
    $discount = (float) $discountPercent;
    $labour = (float) $labourCharges;
    $wire = (float) $wireCost;

    // Step 1: apply discount
    $discountAmount = $main * ($discount / 100);
    $basePrice = $main - $discountAmount;

    // Step 2: add labour & wire
    $total = $basePrice + $labour + $wire;

    return round($total, 2);
}
