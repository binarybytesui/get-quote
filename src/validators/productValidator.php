<?php
// /src/validators/productValidator.php
function validateProduct(array $data) {
    // category and name must be non-empty
    if (!isset($data['category']) || trim($data['category']) === '') {
        return "Category is required";
    }
    if (!isset($data['name']) || trim($data['name']) === '') {
        return "Product name is required";
    }
    if (!isset($data['mainPrice']) || $data['mainPrice'] === '' ) {
        return "Main price is required";
    }
    if (!is_numeric($data['mainPrice']) || $data['mainPrice'] < 0) {
        return "Main price is invalid";
    }
    if (isset($data['discountPercent']) && (!is_numeric($data['discountPercent']) || $data['discountPercent'] < 0)) {
        return "Discount percent is invalid";
    }
    // OK
    return true;
}
