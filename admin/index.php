<?php
// Session Start: Initializes or resumes a session.
session_start();

// Authentication Check: Checks if the user is logged in.
$is_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

// Username Handling: Retrieves the username or sets it to 'Guest'.
$admin_username = $is_logged_in ? htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') : 'Guest';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Morning Blush Theme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #fff0f5;
            /* A very light blush pink background */
            color: #343a40;
            /* Dark text for readability */
        }

        .navbar {
            background: linear-gradient(90deg, #d8bfd8, #e6b9cf) !important;
            /* Soft purple to pink gradient */
            box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
        }

        .navbar-brand {
            color: #495057 !important;
            font-weight: 700;
        }

        .navbar-nav .nav-link {
            color: #495057 !important;
            font-weight: 500;
        }

        .nav-link.active {
            font-weight: 700;
            color: #212529 !important;
            border-bottom: 2px solid #212529;
        }

        .card-header {
            background: linear-gradient(90deg, #d8bfd8, #e6b9cf) !important;
            color: #495057;
            font-weight: 600;
        }

        .card-header .btn-link {
            width: 100%;
            text-align: left;
            padding: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--md-on-primary);
        }

        .card-header .bi {
            color: #495057 !important;
        }

        /* Custom padding for the main content area */
        .py-5 {
            padding-top: 5rem !important;
            padding-bottom: 3rem !important;
        }

        /* Custom styles for the login page */
        .login-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
        }

        /* Custom media query for devices below 480px */
        @media (max-width: 479.98px) {
            .navbar-nav {
                flex-direction: column !important;
            }

            .navbar-nav .nav-item {
                width: 100%;
                margin-left: 0 !important;
            }

            .navbar-nav .nav-link {
                padding: 0.5rem 1rem;
            }

            .mobile {
                margin-bottom: 0.5rem;
            }
        }

        @media (min-width: 480px) and (max-width: 767px) {
            .custom-margin-480-767 {
                margin-bottom: 0.5rem;
            }
        }

        /* Styles from index.html, modified to match the index.php theme */
        .product-card {
            background-color: #fff;
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, .15);
        }

        .product-card .card-header {
            background: linear-gradient(90deg, #d8bfd8, #e6b9cf) !important;
            color: #495057;
            border-bottom: none;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            font-weight: 600;
        }

        .card-header h5 .btn-link {
            color: #495057;
        }

        .badge-and-icon-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-get-quote,
        .btn-add-product {
            background-color: #e6b9cf;
            border-color: #e6b9cf;
            color: #212529;
        }

        .btn-get-quote:hover,
        .btn-add-product:hover {
            background-color: #d8bfd8;
            border-color: #d8bfd8;
            transform: translateY(-2px);
        }

        .text-title-black {
            color: #495057;
            font-weight: bold;
        }

        .company-logo {
            width: 40px;
            height: 40px;
        }

        .header-title-disp {
            max-width: 1280px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
        }

        .form-control {
            font-size: .9rem;
        }


        .form-control.is-invalid {
            border-color: #ce8fadff;
            padding-right: calc(1.5em + .75rem);
            background-repeat: no-repeat;
            background-position: right calc(.375em + .1875rem) center;
            background-size: calc(.75em + .375rem) calc(.75em + .375rem);
        }

        .form-control:focus {
            border-color: #e6b9cf;
            box-shadow: 0 0 0 0.25rem rgba(255, 200, 226, 0.25);
        }

        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: .25rem;
            font-size: .7rem;
            color: #d4699cff;
        }

        /* New styles for product item rows */
        .product-item-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .product-item-row .product-input-col {
            flex-grow: 1;
            position: relative;
        }

        .product-item-row .quantity-input-col {
            flex-shrink: 0;
            width: 80px;
        }

        .remove-product-btn-col {
            flex-shrink: 0;
            width: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .remove-product-btn {
            background: none;
            border: none;
            color: #d58694ff;
            /* Keep the error color */
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0;
            transition: color 0.2s ease;
        }

        .remove-product-btn:hover {
            color: #ec7a7aff;
        }

        .product-suggestions {
            position: absolute;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-top: none;
            border-radius: 0 0 0.5rem 0.5rem;
            background-color: #fff;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: none;
            padding: 0;
            list-style: none;
        }

        .product-suggestions .list-group-item {
            cursor: pointer;
            /*padding: 0.6rem .8rem;*/
            border: none;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            color: #212121;
            transition: background-color 0.2s ease;
        }

        .product-suggestions .list-group-item:hover,
        .product-suggestions .list-group-item.active {
            background-color: #d8bfd8;
            color: #495057;
        }

        /* Force dropdown to open upwards */
        .product-suggestions.open-upwards {
            top: auto;
            /* Override default 'top' */
            bottom: 100%;
            /* Position above the input, 100% of input height */
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            /* Add top border */
            border-bottom: none;
            /* Remove bottom border */
            border-radius: 0.5rem 0.5rem 0 0;
            /* Adjust border-radius for top */
            transform: translateY(-2px);
            /* Small adjustment to prevent visual gap */
        }

        /* Target the carousel items when they transition */
        .carousel-fade .carousel-item {
            transition: opacity 1s ease-in-out;
            /* Adjust '1s' for desired smoothness */
        }

        /* Ensure the next/prev items also transition smoothly */
        .carousel-fade .carousel-item-next.carousel-item-start,
        .carousel-fade .carousel-item-prev.carousel-item-end {
            transition: opacity 1s ease-in-out;
            /* Match the duration */
        }

        /* Ensure the active item transitions out smoothly */
        .carousel-fade .active.carousel-item-end,
        .carousel-fade .active.carousel-item-start {
            transition: opacity 1s ease-in-out;
            /* Match the duration */
        }

        /* Style for the product/quantity titles */
        .product-titles-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
            /* Space between titles and first product row */
            font-weight: 600;
            color: var(--md-on-background);
            font-size: 0.95rem;
            padding-left: 0.5rem;
            /* Align with input fields */
            padding-right: 0.5rem;
        }

        .product-titles-row .product-title-col {
            flex-grow: 1;
        }

        .product-titles-row .quantity-title-col {
            flex-shrink: 0;
            width: 80px;
            /* Match quantity input width */
            text-align: center;
        }

        .text-light-header {
            color: #495057;
        }

        .custom-badge-size {
            font-size: 1rem;
            padding: .25em .6em;
        }

        /* The main class for our button */
        .btn-pulse {
            /* This animation property makes the button's box-shadow pulse continuously */
            animation: pulse-shadow 2s infinite ease-in-out;
        }

        /* The keyframes for the pulse animation */
        @keyframes pulse-shadow {

            0%,
            100% {
                /* At the start and end, the shadow is small and more opaque */
                box-shadow: 0 0 0 0 rgba(230, 185, 207, 0.7);
            }

            50% {
                /* In the middle of the animation, the shadow expands and fades out */
                box-shadow: 0 0 0 15px rgba(230, 185, 207, 0);
            }
        }
    </style>
</head>

<body>

    <?php if (!$is_logged_in): ?>
        <div class="container login-container">
            <div class="card login-card shadow-lg">
                <div class="card-header text-center">
                    <h3 class="mb-0">Admin Login</h3>
                </div>
                <div class="card-body p-4">
                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"
                            style="background-color: #e6b9cf; border-color: #e6b9cf;">Login</button>
                        <div id="loginMessage" class="mt-3 text-center"></div>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <nav class="navbar navbar-expand-sm navbar-light fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    Admin Panel
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link active d-flex align-items-center" href="#" id="instantQuoteLink">
                                <i class="bi bi-lightning-charge-fill me-2"></i> Enquiry Form
                            </a>
                        </li>
                        <li class="nav-item ms-3">
                            <a class="nav-link d-flex align-items-center" href="review.php" id="updateQuoteLink"
                                target="_blank">
                                <i class="bi bi-pencil-square me-2"></i> Edit Quote
                            </a>
                        </li>
                    </ul>
                    <ul class="navbar-nav">
                        <li class="nav-item d-flex align-items-center">
                            <span class="nav-link disabled me-2">Logged in as: <?php echo $admin_username; ?></span>
                            <a class="nav-link d-flex align-items-center" href="#" id="logoutButton">
                                <span class="me-2">Logout</span>
                                <i class="bi bi-box-arrow-right fs-5"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container-fluid mt-2 py-5">
            <div class="container">
                <div class="card product-card mb-3">
                    <div class="card-header text-center text-light-header">
                        <h5 class="mb-0">Company and Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <form id="customForm" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="text" id="companyName" class="form-control"
                                        placeholder="Enter your company name" minlength="5" maxlength="50" required>
                                    <div class="invalid-feedback">
                                        Company name is required (5-50 characters).
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" id="contactPerson" class="form-control"
                                        placeholder="Enter contact person's name" pattern="[A-Za-z\s]+"
                                        title="Only alphabets and spaces allowed" minlength="5" maxlength="25" required>
                                    <div class="invalid-feedback">
                                        Contact person's name is required (5-25 characters, alphabets and spaces only).
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <input type="email" id="email" class="form-control"
                                        placeholder="Enter email name@example.com" autocomplete="email" required>
                                    <div class="invalid-feedback">
                                        A valid email address is required.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text" id="inputGroupPrepend">+91</span>
                                        <input type="tel" id="number" class="form-control"
                                            placeholder="Enter your Mobile Number" maxlength="10" minlength="10"
                                            pattern="[0-9]{10}" inputmode="numeric" autocomplete="tel"
                                            title="Enter a 10-digit mobile number without country code"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                                        <div class="invalid-feedback">
                                            A 10-digit mobile number is required.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <form id="productSelectionForm" novalidate>
                    <div class="card-header text-light-header mb-3" style="border-radius: 10px; padding: 0.5rem 1rem;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Select Your Products</h5>
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="mb-0">Total Items:</h6>
                                <span class="badge rounded-pill bg-light text-dark custom-badge-size"
                                    id="totalItemsBadge"></span>
                            </div>
                        </div>
                    </div>
                    <div class=" row mb-2">
                        <div class="col-md-6 d-flex mobile custom-margin-480-767">
                            <div class="card product-card flex-fill">
                                <div class="card-header">
                                    <h5 class="mb-0" id="category-title-A">
                                        <button class="btn btn-link text-decoration-none text-light-header" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseCategoryA"
                                            aria-expanded="false" aria-controls="collapseCategoryA">
                                            Sockets
                                            <div class="badge-and-icon-wrapper">
                                                <!-- <span class="badge bg-secondary" id="badge-A"></span> -->
                                                <span class="badge rounded-pill bg-light text-dark" id="badge-A"></span>
                                                <i class="bi bi-chevron-down"></i>
                                            </div>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseCategoryA" class="collapse">
                                    <img src="assets/img/sockets.png" class="img-fluid d-block mx-auto mt-2" alt="socket"
                                        style="width: 40%;">
                                    <div class="card-body">
                                        <div id="product-list-A">
                                            <!-- Product dropdowns will be added here -->
                                        </div>
                                        <button type="button" class="btn btn-add-product mx-auto d-block mt-3"
                                            data-category-key="sockets"> <i class="bi bi-plus-circle me-2"></i> Add Product
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex">
                            <div class="card product-card flex-fill">
                                <div class="card-header">
                                    <h5 class="mb-0" id="category-title-B">
                                        <button class="btn btn-link text-decoration-none text-light-header" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseCategoryB"
                                            aria-expanded="false" aria-controls="collapseCategoryB">
                                            MCB
                                            <div class="badge-and-icon-wrapper">
                                                <!-- <span class="badge bg-secondary" id="badge-B"></span> -->
                                                <span class="badge rounded-pill bg-light text-dark" id="badge-B"></span>
                                                <i class="bi bi-chevron-down"></i>
                                            </div>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseCategoryB" class="collapse">
                                    <img src="assets/img/mcb.png" class="img-fluid d-block mx-auto mt-2" alt="mcb"
                                        style="width: 40%;">
                                    <div class="card-body">
                                        <div id="product-list-B">
                                        </div>
                                        <button type="button" class="btn btn-add-product mx-auto d-block mt-3"
                                            data-category-key="mcb_data"><i class="bi bi-plus-circle me-2"></i> Add Product
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 d-flex mobile custom-margin-480-767">
                            <div class="card product-card flex-fill">
                                <div class="card-header">
                                    <h5 class="mb-0" id="category-title-C">
                                        <button class="btn btn-link text-decoration-none text-light-header" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseCategoryC"
                                            aria-expanded="false" aria-controls="collapseCategoryC">
                                            RCCB
                                            <div class="badge-and-icon-wrapper">
                                                <!-- <span class="badge bg-secondary" id="badge-C"></span> -->
                                                <span class="badge rounded-pill bg-light text-dark" id="badge-C"></span>
                                                <i class="bi bi-chevron-down"></i>
                                            </div>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseCategoryC" class="collapse">
                                    <img src="assets/img/rccb.png" class="img-fluid d-block mx-auto mt-2" alt="rccb"
                                        style="width: 40%;">
                                    <div class="card-body">
                                        <div id="product-list-C">
                                        </div>
                                        <button type="button" class="btn btn-add-product mx-auto d-block mt-3"
                                            data-category-key="rccb_data"><i class="bi bi-plus-circle me-2"></i> Add Product
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex mt-md-0">
                            <div class="card product-card flex-fill">
                                <div class="card-header">
                                    <h5 class="mb-0" id="category-title-D">
                                        <button class="btn btn-link text-decoration-none text-light-header" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseCategoryD"
                                            aria-expanded="false" aria-controls="collapseCategoryD">
                                            Connectors
                                            <div class="badge-and-icon-wrapper">
                                                <!-- <span class="badge bg-secondary" id="badge-D"></span> -->
                                                <span class="badge rounded-pill bg-light text-dark" id="badge-D"></span>
                                                <i class="bi bi-chevron-down"></i>
                                            </div>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseCategoryD" class="collapse">
                                    <img src="assets/img/connectors.png" class="img-fluid d-block mx-auto mt-2"
                                        alt="connectors" style="width: 40%;">
                                    <div class="card-body">
                                        <div id="product-list-D">
                                        </div>
                                        <button type="button" class="btn btn-add-product mx-auto d-block mt-3"
                                            data-category-key="connectors"> <i class="bi bi-plus-circle me-2"></i> Add
                                            Product
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 d-flex mobile custom-margin-480-767">
                            <div class="card product-card flex-fill">
                                <div class="card-header">
                                    <h5 class="mb-0" id="category-title-E">
                                        <button class="btn btn-link text-decoration-none text-light-header" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseCategoryE"
                                            aria-expanded="false" aria-controls="collapseCategoryE">
                                            Plugs
                                            <div class="badge-and-icon-wrapper">
                                                <!-- <span class="badge bg-secondary" id="badge-E"></span> -->
                                                <span class="badge rounded-pill bg-light text-dark" id="badge-E"></span>
                                                <i class="bi bi-chevron-down"></i>
                                            </div>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseCategoryE" class="collapse">
                                    <img src="assets/img/plugs.png" class="img-fluid d-block mx-auto mt-2" alt="plugs"
                                        style="width: 40%;">
                                    <div class="card-body">
                                        <div id="product-list-E">
                                        </div>
                                        <button type="button" class="btn btn-add-product mx-auto d-block mt-3"
                                            data-category-key="plugs"><i class="bi bi-plus-circle me-2"></i> Add Product
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex mt-md-0">
                            <div class="card product-card flex-fill">
                                <div class="card-header">
                                    <h5 class="mb-0" id="category-title-F">
                                        <button class="btn btn-link text-decoration-none text-light-header" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseCategoryF"
                                            aria-expanded="false" aria-controls="collapseCategoryF">
                                            Inlets
                                            <div class="badge-and-icon-wrapper">
                                                <!-- <span class="badge bg-secondary" id="badge-F"></span> -->
                                                <span class="badge rounded-pill bg-light text-dark" id="badge-F"></span>
                                                <i class="bi bi-chevron-down"></i>
                                            </div>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseCategoryF" class="collapse">
                                    <img src="assets/img/inlet.png" class="img-fluid d-block mx-auto mt-2" alt="inlet"
                                        style="width: 40%;">
                                    <div class="card-body">
                                        <div id="product-list-F">
                                        </div>
                                        <button type="button" class="btn btn-add-product mx-auto d-block mt-3"
                                            data-category-key="inlets"> <i class="bi bi-plus-circle me-2"></i> Add Product
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 d-flex mobile custom-margin-480-767">
                            <div class="card product-card flex-fill">
                                <div class="card-header">
                                    <h5 class="mb-0" id="category-title-G">
                                        <button class="btn btn-link text-decoration-none text-light-header" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseCategoryG"
                                            aria-expanded="false" aria-controls="collapseCategoryG">
                                            Indicators
                                            <div class="badge-and-icon-wrapper">
                                                <!-- <span class="badge bg-secondary" id="badge-G"></span> -->
                                                <span class="badge rounded-pill bg-light text-dark" id="badge-G"></span>
                                                <i class="bi bi-chevron-down"></i>
                                            </div>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseCategoryG" class="collapse">
                                    <img src="assets/img/led.png" class="img-fluid d-block mx-auto mt-2" alt="led"
                                        style="width: 40%;">
                                    <div class="card-body">
                                        <div id="product-list-G">
                                        </div>
                                        <button type="button" class="btn btn-add-product mx-auto d-block mt-3"
                                            data-category-key="indicators"> <i class="bi bi-plus-circle me-2"></i> Add
                                            Product
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex mt-md-0">
                            <div class="card product-card flex-fill">
                                <div class="card-header">
                                    <h5 class="mb-0" id="category-title-H">
                                        <button class="btn btn-link text-decoration-none text-light-header" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseCategoryH"
                                            aria-expanded="false" aria-controls="collapseCategoryH">
                                            Accessories
                                            <div class="badge-and-icon-wrapper">
                                                <!-- <span class="badge bg-secondary" id="badge-H"></span> -->
                                                <span class="badge rounded-pill bg-light text-dark" id="badge-H"></span>
                                                <i class="bi bi-chevron-down"></i>
                                            </div>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseCategoryH" class="collapse">
                                    <img src="assets/img/accessories.png" class="img-fluid d-block mx-auto mt-2"
                                        alt="accessories" style="width: 40%;">
                                    <div class="card-body">
                                        <div id="product-list-H">
                                        </div>
                                        <button type="button" class="btn btn-add-product mx-auto d-block mt-3"
                                            data-category-key="accessories"> <i class="bi bi-plus-circle me-2"></i> Add
                                            Product
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 d-flex mobile custom-margin-480-767">
                            <div class="card product-card flex-fill">
                                <div class="card-header">
                                    <h5 class="mb-0" id="category-title-I">
                                        <button class="btn btn-link text-decoration-none text-light-header" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseCategoryI"
                                            aria-expanded="false" aria-controls="collapseCategoryI">
                                            Board Materials
                                            <div class="badge-and-icon-wrapper">
                                                <!-- <span class="badge bg-secondary" id="badge-I"></span> -->
                                                <span class="badge rounded-pill bg-light text-dark" id="badge-I"></span>
                                                <i class="bi bi-chevron-down"></i>
                                            </div>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseCategoryI" class="collapse">
                                    <div id="productImageCarousel" class="carousel slide carousel-fade"
                                        data-bs-ride="carousel" data-bs-interval="4000" data-bs-pause="false">
                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <img src="assets/img/board_material1.png" class="d-block w-100 mx-auto mt-2"
                                                    alt="Board Material 1" style="width: 40%; max-width: 40%;">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="assets/img/board_material2.png" class="d-block w-100 mx-auto mt-2"
                                                    alt="Board Material 2" style="width: 40%; max-width: 40%;">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="assets/img/board_material3.png" class="d-block w-100 mx-auto mt-2"
                                                    alt="Board Material 3" style="width: 40%; max-width: 40%;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="product-list-I">
                                        </div>
                                        <button type="button" class="btn btn-add-product mx-auto d-block mt-3"
                                            data-category-key="board_materials"> <i class="bi bi-plus-circle me-2"></i> Add
                                            Product
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex mt-md-0">
                            <div class="card product-card flex-fill">
                                <div class="card-header">
                                    <h5 class="mb-0" id="category-title-J">
                                        <button class="btn btn-link text-decoration-none text-light-header" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseCategoryJ"
                                            aria-expanded="false" aria-controls="collapseCategoryJ">
                                            Add-Ons
                                            <div class="badge-and-icon-wrapper">
                                                <!-- <span class="badge bg-secondary" id="badge-J"></span> -->
                                                <span class="badge rounded-pill bg-light text-dark" id="badge-J"></span>
                                                <i class="bi bi-chevron-down"></i>
                                            </div>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseCategoryJ" class="collapse">
                                    <img src="assets/img/add-ons.png" class="img-fluid d-block mx-auto mt-2" alt="add-ons"
                                        style="width: 40%;">
                                    <div class="card-body">
                                        <div id="product-list-J">
                                            <!-- Product dropdowns will be added here -->
                                        </div>
                                        <button type="button" class="btn btn-add-product mx-auto d-block mt-3"
                                            data-category-key="Add-Ons"> <i class="bi bi-plus-circle me-2"></i> Add Product
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-get-quote btn-pulse rounded-pill mt-4"><i
                                class="bi bi-send me-2"></i>Get
                            Quote
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        let productData = {}; // Declare productData globally but initialize after fetch

        // Object to keep track of selected products by category and input element (to handle duplicates across dynamically added rows)
        // Structure: { 'categoryKey': Set<ProductName> }
        const selectedProductsInCategories = {};

        // To keep track of the number of rows per category for unique indexing
        const rowCounts = {};

        // Mapping for category keys to their display titles and product list container IDs
        const categoryMap = {
            "sockets": { title: "Sockets", listIdSuffix: "A", collapseId: "collapseCategoryA" },
            "mcb_data": { title: "MCB Data", listIdSuffix: "B", collapseId: "collapseCategoryB" },
            "rccb_data": { title: "RCCB Data", listIdSuffix: "C", collapseId: "collapseCategoryC" },
            "connectors": { title: "Connectors", listIdSuffix: "D", collapseId: "collapseCategoryD" },
            "plugs": { title: "Plugs", listIdSuffix: "E", collapseId: "collapseCategoryE" },
            "inlets": { title: "Inlets", listIdSuffix: "F", collapseId: "collapseCategoryF" },
            "indicators": { title: "Indicators", listIdSuffix: "G", collapseId: "collapseCategoryG" },
            "accessories": { title: "Accessories", listIdSuffix: "H", collapseId: "collapseCategoryH" },
            "board_materials": { title: "Board Materials", listIdSuffix: "I", collapseId: "collapseCategoryI" },
            "Add-Ons": { title: "Add-Ons", listIdSuffix: "J", collapseId: "collapseCategoryJ" }
        };

        /**
         * Updates the quantity badge for a given category.
         * @param {string} categoryKey The key of the product category.
         */
        function updateCategoryQuantityBadge(categoryKey) {
            const listIdSuffix = categoryMap[categoryKey]?.listIdSuffix;
            if (!listIdSuffix) return;

            const quantityInputs = document.querySelectorAll(`#product-list-${listIdSuffix} .quantity-input`);
            let totalQuantity = 0;
            quantityInputs.forEach(input => {
                const quantity = parseInt(input.value, 10);
                if (!isNaN(quantity) && quantity > 0) {
                    totalQuantity += quantity;
                }
            });

            const badgeElement = document.getElementById(`badge-${listIdSuffix}`);
            if (badgeElement) {
                if (totalQuantity > 0) {
                    badgeElement.textContent = totalQuantity;
                    badgeElement.style.display = 'inline-block';
                } else {
                    badgeElement.textContent = '';
                    badgeElement.style.display = 'none';
                }
            }
            updateTotalItemsBadge(); // Call here to update the overall total when a category badge changes
        }

        /**
        * Updates the total items badge across all categories.
        */
        function updateTotalItemsBadge() {
            let totalItems = 0;
            for (const key in categoryMap) {
                const listIdSuffix = categoryMap[key].listIdSuffix;
                const quantityInputs = document.querySelectorAll(`#product-list-${listIdSuffix} .quantity-input`);
                quantityInputs.forEach(input => {
                    const quantity = parseInt(input.value, 10); // Get the quantity of each product
                    if (!isNaN(quantity) && quantity > 0) {
                        totalItems += quantity; // Add the actual quantity to totalItems
                    }
                });
            }
            const totalItemsBadge = document.getElementById('totalItemsBadge');
            if (totalItemsBadge) {
                totalItemsBadge.textContent = totalItems;
            }
        }

        /**
         * Populates the product lists with an initial row and sets up "Add Product" buttons.
         */
        function populateProductLists() {
            // Update card headers with actual category names and initialize row counts
            for (const key in categoryMap) {
                const suffix = categoryMap[key].listIdSuffix;
                const titleElement = document.getElementById(`category-title-${suffix}`);
                if (titleElement) {
                    const button = titleElement.querySelector('.btn-link');
                    if (button) {
                        const textNode = Array.from(button.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
                        if (textNode) {
                            textNode.textContent = categoryMap[key].title + ' ';
                        } else {
                            button.prepend(categoryMap[key].title + ' ');
                        }
                    }
                }
                // Initialize selectedProductsInCategories set for each category
                selectedProductsInCategories[key] = new Set();
                rowCounts[key] = 0; // Initialize row count for each category
                updateCategoryQuantityBadge(key); // Initialize badges   s
            }

            // Add event listeners for "Add Product" buttons
            document.querySelectorAll('.btn-add-product').forEach(button => {
                button.addEventListener('click', (e) => {
                    const categoryKey = e.target.getAttribute('data-category-key');
                    addNewProductRow(categoryKey);
                });
            });
        }

        /**
         * Adds a new product selection row to the specified category's product list.
         * @param {string} categoryKey The key of the product category (e.g., 'sockets', 'mcb_data').
         */
        function addNewProductRow(categoryKey) {
            const listIdSuffix = categoryMap[categoryKey] ? categoryMap[categoryKey].listIdSuffix : null;
            if (!listIdSuffix) {
                console.warn(`No product list ID suffix found for category: ${categoryKey}. Cannot add row.`);
                return;
            }

            const productListContainer = document.getElementById(`product-list-${listIdSuffix}`);
            const rowIndex = rowCounts[categoryKey]++; // Get current count and increment for next row

            // Add titles only if this is the first product row being added for this category
            if (rowIndex === 0) {
                const titlesRow = document.createElement('div');
                titlesRow.classList.add('product-titles-row');

                const productTitleCol = document.createElement('div');
                productTitleCol.classList.add('product-title-col');
                productTitleCol.textContent = 'Product Name';

                const quantityTitleCol = document.createElement('div');
                quantityTitleCol.classList.add('quantity-title-col');
                quantityTitleCol.textContent = 'Quantity';

                const removeTitlePlaceholderCol = document.createElement('div'); // New placeholder for alignment
                removeTitlePlaceholderCol.classList.add('remove-product-btn-col'); // Use the same class for consistent width

                titlesRow.appendChild(productTitleCol);
                titlesRow.appendChild(quantityTitleCol);
                titlesRow.appendChild(removeTitlePlaceholderCol); // Append placeholder
                productListContainer.prepend(titlesRow); // Add titles before any product rows
            }


            const productRowDiv = document.createElement('div');
            productRowDiv.classList.add('product-item-row');
            productRowDiv.setAttribute('data-row-id', `${categoryKey}-${rowIndex}`); // Unique ID for the row

            // Product Input Column
            const productInputCol = document.createElement('div');
            productInputCol.classList.add('product-input-col');

            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.classList.add('form-control', 'product-search-input');
            searchInput.placeholder = 'Search product...';
            searchInput.setAttribute('data-category-key', categoryKey);
            searchInput.setAttribute('data-row-index', rowIndex); // Unique index for this row
            searchInput.autocomplete = "off";
            searchInput.setAttribute('data-original-product', ''); // To track initially selected product for this input

            const hiddenProductNameInput = document.createElement('input');
            hiddenProductNameInput.type = 'hidden';
            hiddenProductNameInput.name = `${categoryKey}-product-${rowIndex}`;
            hiddenProductNameInput.classList.add('selected-product-name');

            const hiddenProductPriceInput = document.createElement('input');
            hiddenProductPriceInput.type = 'hidden';
            hiddenProductPriceInput.name = `${categoryKey}-price-${rowIndex}`;
            hiddenProductPriceInput.classList.add('selected-product-price');

            const hiddenProductPartNoInput = document.createElement('input');
            hiddenProductPartNoInput.type = 'hidden';
            hiddenProductPartNoInput.name = `${categoryKey}-partNo-${rowIndex}`;
            hiddenProductPartNoInput.classList.add('selected-product-partNo');

            const suggestionsDiv = document.createElement('div');
            suggestionsDiv.classList.add('list-group', 'product-suggestions');

            productInputCol.appendChild(searchInput);
            productInputCol.appendChild(hiddenProductNameInput);
            productInputCol.appendChild(hiddenProductPriceInput);
            productInputCol.appendChild(hiddenProductPartNoInput);
            productInputCol.appendChild(suggestionsDiv);
            productRowDiv.appendChild(productInputCol);

            // Quantity Input Column
            const quantityInputCol = document.createElement('div');
            quantityInputCol.classList.add('quantity-input-col');

            const quantityInput = document.createElement('input');
            quantityInput.type = 'number';
            quantityInput.name = `${categoryKey}-quantity-${rowIndex}`;
            quantityInput.id = `${categoryKey}-quantity-${rowIndex}`;
            quantityInput.min = '0';
            quantityInput.value = '0';
            quantityInput.style.textAlign = 'center';
            quantityInput.placeholder = 'Qty';
            quantityInput.classList.add('form-control', 'quantity-input');
            quantityInputCol.appendChild(quantityInput);
            productRowDiv.appendChild(quantityInputCol);

            // Always add a remove button for any dynamically added row
            const removeBtnCol = document.createElement('div');
            removeBtnCol.classList.add('remove-product-btn-col'); // Apply the new class here
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.classList.add('remove-product-btn');
            removeButton.innerHTML = '<i class="bi bi-trash3-fill"></i>';
            removeButton.title = 'Remove product';
            removeButton.addEventListener('click', () => {
                // Remove product from tracking if it was selected
                const productName = searchInput.getAttribute('data-original-product');
                if (productName) {
                    selectedProductsInCategories[categoryKey].delete(productName);
                }
                productRowDiv.remove();
                updateCategoryQuantityBadge(categoryKey); // Update the badge after removal

                // If no more product rows exist, remove the titles row
                if (productListContainer.querySelectorAll('.product-item-row').length === 0) {
                    const titlesRowElement = productListContainer.querySelector('.product-titles-row');
                    if (titlesRowElement) {
                        titlesRowElement.remove();
                    }
                    rowCounts[categoryKey] = 0; // Reset row count if all rows are removed
                }
            });
            removeBtnCol.appendChild(removeButton);
            productRowDiv.appendChild(removeBtnCol);


            productListContainer.appendChild(productRowDiv);

            // Add clear/set 0 on focus/blur for quantity and update badge
            quantityInput.addEventListener('focus', function () {
                if (this.value === '0') {
                    this.value = '';
                }
            });
            quantityInput.addEventListener('blur', function () {
                if (this.value.trim() === '' || isNaN(parseInt(this.value, 10))) {
                    this.value = '0';
                }
                updateCategoryQuantityBadge(categoryKey); // Update badge on blur
            });
            quantityInput.addEventListener('input', function () {
                updateCategoryQuantityBadge(categoryKey); // Update badge on input change
            });


            // Re-bind event listeners for the newly created searchInput
            addSearchInputEventListeners(searchInput, hiddenProductNameInput, hiddenProductPriceInput, hiddenProductPartNoInput, suggestionsDiv);
            updateTotalItemsBadge(); // Call when a new product row is added
        }

        /**
         * Adds event listeners for product search input (keyup, focus, blur, click on suggestions).
         * @param {HTMLElement} searchInput The product search input element.
         * @param {HTMLElement} hiddenProductNameInput Hidden input for product name.
         * @param {HTMLElement} hiddenProductPriceInput Hidden input for product price.
         * @param {HTMLElement} hiddenProductPartNoInput Hidden input for product part number.
         * @param {HTMLElement} suggestionsDiv The suggestions container div.
         */
        function addSearchInputEventListeners(searchInput, hiddenProductNameInput, hiddenProductPriceInput, hiddenProductPartNoInput, suggestionsDiv) {
            searchInput.addEventListener('keyup', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                const currentCategory = e.target.getAttribute('data-category-key');
                const originalProduct = searchInput.getAttribute('data-original-product');
                suggestionsDiv.innerHTML = '';

                // Clear hidden fields if input is cleared by user
                if (searchTerm.length === 0) {
                    // If a product was previously selected, remove it from tracking
                    if (originalProduct) {
                        selectedProductsInCategories[currentCategory].delete(originalProduct);
                    }
                    hiddenProductNameInput.value = '';
                    hiddenProductPriceInput.value = '';
                    hiddenProductPartNoInput.value = '';
                    searchInput.setAttribute('data-original-product', ''); // Clear original product
                }

                if (searchTerm.length > 0) {
                    const currentCategoryProducts = productData[currentCategory] || [];
                    const filteredProducts = currentCategoryProducts.filter(product => {
                        // Show product if it matches search term AND is not already selected in this category
                        // OR if it's the product currently selected in THIS specific input field.
                        const isAlreadySelected = selectedProductsInCategories[currentCategory].has(product.name);
                        const isCurrentInputProduct = (product.name === originalProduct);
                        return product.name.toLowerCase().includes(searchTerm) && (!isAlreadySelected || isCurrentInputProduct);
                    });

                    if (filteredProducts.length > 0) {
                        filteredProducts.forEach(product => {
                            const suggestionItem = document.createElement('a');
                            suggestionItem.href = '#';
                            suggestionItem.classList.add('list-group-item', 'list-group-item-action');
                            suggestionItem.textContent = product.name;
                            suggestionItem.setAttribute('data-product-name', product.name);
                            suggestionItem.setAttribute('data-product-price', product.price);
                            suggestionItem.setAttribute('data-product-partNo', product.partNo);

                            suggestionItem.addEventListener('mousedown', (event) => {
                                event.preventDefault(); // Prevent blur from hiding suggestions immediately

                                const newProductName = product.name;
                                const oldProductName = searchInput.getAttribute('data-original-product');

                                // If a different product was previously selected in this input, remove it from tracking
                                if (oldProductName && oldProductName !== newProductName) {
                                    selectedProductsInCategories[currentCategory].delete(oldProductName);
                                }

                                searchInput.value = newProductName;
                                hiddenProductNameInput.value = newProductName;
                                hiddenProductPriceInput.value = product.price;
                                hiddenProductPartNoInput.value = product.partNo;
                                searchInput.setAttribute('data-original-product', newProductName); // Update original product
                                selectedProductsInCategories[currentCategory].add(newProductName); // Track new selection
                                suggestionsDiv.style.display = 'none';
                            });
                            suggestionsDiv.appendChild(suggestionItem);
                        });
                        suggestionsDiv.style.display = 'block';
                        // Always add 'open-upwards' when displaying suggestions based on keyup
                        suggestionsDiv.classList.add('open-upwards');
                    } else {
                        suggestionsDiv.style.display = 'none';
                        suggestionsDiv.classList.remove('open-upwards'); // Ensure it's removed if no suggestions
                    }
                } else {
                    suggestionsDiv.style.display = 'none';
                    suggestionsDiv.classList.remove('open-upwards'); // Ensure it's removed if no search term
                }
            });

            searchInput.addEventListener('focus', (e) => {
                const currentCategory = e.target.getAttribute('data-category-key');
                const originalProduct = searchInput.getAttribute('data-original-product');
                suggestionsDiv.innerHTML = '';

                const currentCategoryProducts = productData[currentCategory] || [];
                const productsToShow = currentCategoryProducts.filter(product => {
                    const isAlreadySelected = selectedProductsInCategories[currentCategory].has(product.name);
                    const isCurrentInputProduct = (product.name === originalProduct);
                    return !isAlreadySelected || isCurrentInputProduct;
                });

                if (productsToShow.length > 0) {
                    productsToShow.forEach(product => {
                        const suggestionItem = document.createElement('a');
                        suggestionItem.href = '#';
                        suggestionItem.classList.add('list-group-item', 'list-group-item-action');
                        suggestionItem.textContent = product.name;
                        suggestionItem.setAttribute('data-product-name', product.name);
                        suggestionItem.setAttribute('data-product-price', product.price);
                        suggestionItem.setAttribute('data-product-partNo', product.partNo);
                        suggestionItem.addEventListener('mousedown', (event) => {
                            event.preventDefault();

                            const newProductName = product.name;
                            const oldProductName = searchInput.getAttribute('data-original-product');

                            if (oldProductName && oldProductName !== newProductName) {
                                selectedProductsInCategories[currentCategory].delete(oldProductName);
                            }

                            searchInput.value = newProductName;
                            hiddenProductNameInput.value = newProductName;
                            hiddenProductPriceInput.value = product.price;
                            hiddenProductPartNoInput.value = product.partNo;
                            searchInput.setAttribute('data-original-product', newProductName);
                            selectedProductsInCategories[currentCategory].add(newProductName);
                            suggestionsDiv.style.display = 'none';
                        });
                        suggestionsDiv.appendChild(suggestionItem);
                    });
                    suggestionsDiv.style.display = 'block';
                    // Always add 'open-upwards' when displaying suggestions based on focus
                    suggestionsDiv.classList.add('open-upwards');
                } else {
                    suggestionsDiv.classList.remove('open-upwards'); // Ensure it's removed if no suggestions
                }

                // Removed the conditional logic for 'open-upwards' as it will always be applied now.
            });

            searchInput.addEventListener('blur', () => {
                setTimeout(() => {
                    suggestionsDiv.style.display = 'none';
                    // Always remove 'open-upwards' when suggestions are hidden
                    suggestionsDiv.classList.remove('open-upwards');
                    const currentCategory = searchInput.getAttribute('data-category-key');
                    const currentInputValue = searchInput.value.trim();
                    const originalProduct = searchInput.getAttribute('data-original-product');

                    const isValidProduct = productData[currentCategory] && productData[currentCategory].some(p => p.name === currentInputValue);

                    // Case 1: Input is cleared or doesn't match a valid product
                    if (!isValidProduct) {
                        // If something was previously selected in this field, remove it from tracking
                        if (originalProduct) {
                            selectedProductsInCategories[currentCategory].delete(originalProduct);
                        }
                        searchInput.value = ''; // Clear display value
                        hiddenProductNameInput.value = '';
                        hiddenProductPriceInput.value = '';
                        hiddenProductPartNoInput.value = '';
                        searchInput.setAttribute('data-original-product', '');
                    }
                    // Case 2: Input matches a valid product, and it's different from the originally selected one
                    else if (currentInputValue !== originalProduct) {
                        // If there was an old product, remove it from tracking
                        if (originalProduct) {
                            selectedProductsInCategories[currentCategory].delete(originalProduct);
                        }
                        // Add the new valid product to tracking
                        selectedProductsInCategories[currentCategory].add(currentInputValue);
                        searchInput.setAttribute('data-original-product', currentInputValue);
                        // Find the product details to update hidden inputs if the user typed and it's a valid product
                        const selectedProductDetails = productData[currentCategory].find(p => p.name === currentInputValue);
                        if (selectedProductDetails) {
                            hiddenProductNameInput.value = selectedProductDetails.name;
                            hiddenProductPriceInput.value = selectedProductDetails.price;
                            hiddenProductPartNoInput.value = selectedProductDetails.partNo;
                        }
                    }
                    // Case 3: Input matches the original product, do nothing (already tracked correctly)
                }, 150);
            });
        }


        document.addEventListener('DOMContentLoaded', () => {

            // --- Load products from MySQL instead of JSON ---
            function groupByCategory(data) {
                const grouped = {};
                data.forEach(item => {
                    if (!grouped[item.category]) {
                        grouped[item.category] = [];
                    }
                    grouped[item.category].push(item);
                });
                return grouped;
            }

            fetch('api/get-products.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    productData = groupByCategory(data);
                    populateProductLists();
                    initializeCollapseListeners();
                    updateTotalItemsBadge();
                })
                .catch(error => {
                    console.error('Error fetching product data:', error);
                });

            const instantQuoteLink = document.getElementById('instantQuoteLink');
            const updateQuoteLink = document.getElementById('updateQuoteLink');

            const instantQuoteContent = document.getElementById('instantQuoteContent');
            const updateQuoteContent = document.getElementById('updateQuoteContent');

            const navbarCollapse = document.getElementById('navbarSupportedContent');
            // --- Common Elements ---
            const loginForm = document.getElementById('loginForm');
            const loginMessage = document.getElementById('loginMessage');
            const logoutButton = document.getElementById('logoutButton');

            // --- Helper Function for AJAX Requests ---
            async function fetchData(url, data, method = 'POST') {
                try {
                    const options = {
                        method: method,
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams(data).toString()
                    };

                    const response = await fetch(url, options);
                    if (!response.ok) {
                        const errorText = await response.text();
                        throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Fetch error:', error);
                    return { success: false, message: `An error occurred: ${error.message}` };
                }
            }
            // --- Login Form Logic ---
            if (loginForm) {
                loginForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const username = loginForm.username.value;
                    const password = loginForm.password.value;

                    loginMessage.textContent = 'Logging in...';
                    loginMessage.className = 'mt-3 text-center text-info';

                    const result = await fetchData('api/auth.php', {
                        action: 'login',
                        username: username,
                        password: password
                    });

                    if (result.success) {
                        loginMessage.textContent = result.message;
                        loginMessage.className = 'mt-3 text-center text-success';
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        loginMessage.textContent = result.message;
                        loginMessage.className = 'mt-3 text-center text-danger';
                    }
                });
            }

            function updateActiveState(activeLink, otherLink) {
                if (activeLink) activeLink.classList.add('active');
                if (otherLink) otherLink.classList.remove('active');
            }
            // --- Logout Button Logic ---
            if (logoutButton) {
                logoutButton.addEventListener('click', async () => {
                    // Using a custom modal for confirmation would be better than confirm()
                    if (confirm('Are you sure you want to log out?')) {
                        const result = await fetchData('api/auth.php', { action: 'logout' });
                        if (result.success) {
                            // Using a custom notification would be better than alert()
                            alert(result.message);
                            window.location.href = 'index.php';
                        } else {
                            alert('Logout failed: ' + result.message);
                        }
                    }
                });
            }


            function handleNavLinkClick(e, contentToShow, contentToHide, activeLink, otherLink) {
                e.preventDefault();
                updateActiveState(activeLink, otherLink);
                contentToShow.style.display = 'block';
                contentToHide.style.display = 'none';

                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(navbarCollapse, { toggle: false });
                if (navbarCollapse.classList.contains('show')) {
                    bsCollapse.hide();
                }
            }
        });

        // Function to initialize collapse event listeners for all categories
        function initializeCollapseListeners() {
            for (const key in categoryMap) {
                const collapseId = categoryMap[key].collapseId;
                const collapseElement = document.getElementById(collapseId);

                const toggleButton = collapseElement.previousElementSibling.querySelector('.btn-link');
                const icon = toggleButton ? toggleButton.querySelector('i') : null;

                if (collapseElement && toggleButton && icon) {
                    if (collapseElement.classList.contains('show')) {
                        icon.classList.remove('bi-chevron-down');
                        icon.classList.add('bi-chevron-up');
                        toggleButton.setAttribute('aria-expanded', 'true');
                    } else {
                        icon.classList.remove('bi-chevron-up');
                        icon.classList.add('bi-chevron-down');
                        toggleButton.setAttribute('aria-expanded', 'false');
                    }

                    collapseElement.addEventListener('show.bs.collapse', function () {
                        icon.classList.remove('bi-chevron-down');
                        icon.classList.add('bi-chevron-up');
                        toggleButton.setAttribute('aria-expanded', 'true');
                    });

                    collapseElement.addEventListener('hide.bs.collapse', function () {
                        icon.classList.remove('bi-chevron-up');
                        icon.classList.add('bi-chevron-down');
                        toggleButton.setAttribute('aria-expanded', 'false');
                    });
                }
            }
        }


        // --- Company and Contact Information Form Validation ---
        const customForm = document.getElementById('customForm');
        customForm.addEventListener('submit', function (event) {
            event.preventDefault();
            event.stopPropagation();

            let isValid = true;
            const formInputs = customForm.querySelectorAll('input');

            formInputs.forEach(input => {
                if (!input.checkValidity()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                customForm.querySelector('.is-invalid')?.focus();
            }
        });

        // Add blur event listeners for immediate validation feedback on customForm inputs
        document.getElementById('companyName').addEventListener('blur', function () { this.checkValidity() ? this.classList.remove('is-invalid') : this.classList.add('is-invalid'); });
        document.getElementById('contactPerson').addEventListener('blur', function () { this.checkValidity() ? this.classList.remove('is-invalid') : this.classList.add('is-invalid'); });
        document.getElementById('email').addEventListener('blur', function () { this.checkValidity() ? this.classList.remove('is-invalid') : this.classList.add('is-invalid'); });
        document.getElementById('number').addEventListener('blur', function () { this.checkValidity() ? this.classList.remove('is-invalid') : this.classList.add('is-invalid'); });
        document.getElementById('companyName').addEventListener('input', function () { this.checkValidity() ? this.classList.remove('is-invalid') : this.classList.add('is-invalid'); });
        document.getElementById('contactPerson').addEventListener('input', function () { this.checkValidity() ? this.classList.remove('is-invalid') : this.classList.add('is-invalid'); });
        document.getElementById('email').addEventListener('input', function () { this.checkValidity() ? this.classList.remove('is-invalid') : this.classList.add('is-invalid'); });
        document.getElementById('number').addEventListener('input', function () { this.checkValidity() ? this.classList.remove('is-invalid') : this.classList.add('is-invalid'); });


        // --- Product Selection Form Validation and Submission ---
        document.getElementById('productSelectionForm').addEventListener('submit', function (event) {
            event.preventDefault(); // Prevent default form submission
            event.stopPropagation(); // Stop event propagation

            // First, validate the "Company and Contact Information" form
            let customFormIsValid = true;
            const customFormInputs = customForm.querySelectorAll('input');
            customFormInputs.forEach(input => {
                if (!input.checkValidity()) {
                    input.classList.add('is-invalid');
                    customFormIsValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            if (!customFormIsValid) {
                showCustomMessageBox('Please fill out all required company and contact information correctly.');
                customForm.querySelector('.is-invalid')?.focus(); // Scroll to the first invalid contact field
                return; // Stop submission if contact form is invalid
            }


            const selectedProducts = [];
            let productSelectionIsValid = true; // Flag for overall product selection validity
            let totalProductsSelected = 0; // To count valid product selections

            // Clear previous invalid states from product and quantity inputs
            document.querySelectorAll('.product-search-input.is-invalid').forEach(input => input.classList.remove('is-invalid'));
            document.querySelectorAll('.quantity-input.is-invalid').forEach(input => input.classList.remove('is-invalid'));

            // Re-initialize category product check for this submission
            for (const key in selectedProductsInCategories) {
                selectedProductsInCategories[key].clear();
            }

            const allProductSearchInputs = document.querySelectorAll('.product-search-input');
            let firstInvalidProductElement = null;

            allProductSearchInputs.forEach(searchInput => {
                const categoryKey = searchInput.getAttribute('data-category-key');
                const rowIndex = searchInput.getAttribute('data-row-index');

                // Get the corresponding hidden product name input and quantity input
                // We're querying based on name attribute which includes categoryKey and rowIndex
                const hiddenProductNameInput = document.querySelector(`input[name="${categoryKey}-product-${rowIndex}"]`);
                const hiddenProductPriceInput = document.querySelector(`input[name="${categoryKey}-price-${rowIndex}"]`);
                const hiddenProductPartNoInput = document.querySelector(`input[name="${categoryKey}-partNo-${rowIndex}"]`);
                const quantityInput = document.getElementById(`${categoryKey}-quantity-${rowIndex}`);


                const productName = hiddenProductNameInput ? hiddenProductNameInput.value.trim() : '';
                const productPrice = hiddenProductPriceInput ? parseFloat(hiddenProductPriceInput.value) : 0;
                const productPartNo = hiddenProductPartNoInput ? hiddenProductPartNoInput.value.trim() : '';
                const quantity = parseInt(quantityInput.value, 10);

                const isProductSelected = productName !== '';
                const isQuantityValid = !isNaN(quantity) && quantity > 0;

                // Scenario 1: Product selected, but quantity is invalid (0 or less, or not a number)
                if (isProductSelected && !isQuantityValid) {
                    searchInput.classList.add('is-invalid');
                    quantityInput.classList.add('is-invalid');
                    productSelectionIsValid = false;
                    if (!firstInvalidProductElement) firstInvalidProductElement = searchInput;
                }
                // Scenario 2: Quantity entered, but no product selected
                else if (!isProductSelected && isQuantityValid) {
                    searchInput.classList.add('is-invalid');
                    quantityInput.classList.add('is-invalid');
                    productSelectionIsValid = false;
                    if (!firstInvalidProductElement) firstInvalidProductElement = searchInput;
                }
                // Scenario 3: Both product and quantity are valid
                else if (isProductSelected && isQuantityValid) {
                    // Check for duplicates within the current category only among the *submitted* valid selections
                    if (selectedProductsInCategories[categoryKey].has(productName)) {
                        searchInput.classList.add('is-invalid');
                        productSelectionIsValid = false;
                        if (!firstInvalidProductElement) firstInvalidProductElement = searchInput;
                    } else {
                        selectedProductsInCategories[categoryKey].add(productName);
                        selectedProducts.push({
                            category: categoryKey,
                            name: productName,
                            quantity: quantity,
                            price: productPrice,
                            partNo: productPartNo
                        });
                        totalProductsSelected++;
                        searchInput.classList.remove('is-invalid');
                        quantityInput.classList.remove('is-invalid');
                    }
                }
                // Scenario 4: Both are empty/zero (valid "no selection" state)
                else {
                    searchInput.classList.remove('is-invalid');
                    quantityInput.classList.remove('is-invalid');
                }
            });


            // After iterating through all products, check overall product selection validity
            if (!productSelectionIsValid) {
                if (firstInvalidProductElement) {
                    firstInvalidProductElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalidProductElement.focus();
                }
                showCustomMessageBox('Please ensure all selected products have a quantity greater than 0, all quantities correspond to a selected product, and there are no duplicate products within the same category.');
                return; // Stop form submission
            }

            // Final check: ensure at least one product is selected overall
            if (totalProductsSelected === 0) {
                showCustomMessageBox('Please select at least one product and specify a quantity greater than 0.');
                document.querySelector('.product-search-input')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                document.querySelector('.product-search-input')?.focus();
                return; // Stop form submission
            }

            // If all validations pass, proceed with submission
            sessionStorage.setItem('selectedProducts', JSON.stringify(selectedProducts));

            // Also store contact info in session storage
            const contactInfo = {
                companyName: document.getElementById('companyName').value,
                contactPerson: document.getElementById('contactPerson').value,
                email: document.getElementById('email').value,
                mobileNumber: document.getElementById('number').value
            };
            sessionStorage.setItem('contactInfo', JSON.stringify(contactInfo));

            //window.location.href = 'x2.html';
            window.open('quote.php', '_blank');
        });

        /**
         * Creates and displays a custom message box instead of alert().
         * @param {string} message The message to display.
         */
        function showCustomMessageBox(message) {
            let messageBox = document.getElementById('customMessageBox');
            if (!messageBox) {
                messageBox = document.createElement('div');
                messageBox.id = 'customMessageBox';
                messageBox.style.cssText = `
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background-color: #f1ccde;;
                    border: 1px solid var(--md-primary-dark);
                    border-radius: 0.75rem;
                    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
                    padding: 20px;
                    z-index: 9999;
                    text-align: center;
                    max-width: 50%;
                    font-family: 'Roboto', 'Inter', sans-serif;
                    color: var(--md-on-surface);
                `;

                const messageText = document.createElement('p');
                messageText.textContent = message;
                messageText.style.marginBottom = '15px';
                messageText.style.fontSize = '0.9rem';

                const closeButton = document.createElement('button');
                closeButton.textContent = 'OK';
                closeButton.style.cssText = `
                    background-color: #e6b0caff;
                    color: var(--md-on-primary);
                    border: none;
                    border-radius: 0.5rem;
                    padding: 5px 12px;
                    cursor: pointer;
                    font-size: 1rem;
                    font-weight: 500;
                    transition: background-color 0.2s ease;
                `;
                closeButton.onmouseover = () => closeButton.style.backgroundColor = '#e6b0caff;';
                closeButton.onmouseout = () => closeButton.style.backgroundColor = '#e6b0caff;';
                closeButton.onclick = () => messageBox.remove();

                messageBox.appendChild(messageText);
                messageBox.appendChild(closeButton);
                document.body.appendChild(messageBox);
            } else {
                messageBox.querySelector('p').textContent = message;
            }
        }
    </script>
</body>

</html>