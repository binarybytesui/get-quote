<?php
// /src/helpers/sanitize.php
function cleanString($value) {
    if ($value === null) return "";
    return trim(filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
}

function cleanFloat($value) {
    if ($value === null || $value === "") return 0.0;
    return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

function cleanInt($value) {
    if ($value === null || $value === "") return 0;
    return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
}
