<?php
// Session Start: Initializes or resumes a session.
session_start();

// Authentication Check: Checks if the user is logged in.
$is_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

// If the user is not logged in, redirect them to the login page.
if (!$is_logged_in) {
    header("Location: index.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation with PDF & Data Actions</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        /* Custom styles for the invoice, combining and prioritizing ztemplate.html's look */
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }

        /* The main content area to be converted to PDF */
        .invoice-document {
            max-width: 1000px;
            /* Layout width from ztemplate.html */
            margin: 2rem auto;
            padding: 40px;
            background-color: white;
            border: 1px solid #dee2e6;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
        }

        .company-logo {
            width: 50px;
            height: 50px;
        }

        /* Using text-purple from ztemplate.html for consistency */
        .text-purple {
            color: #883cab;
        }

        .invoice-table thead th {
            background-color: #883cab;
            color: white;
            font-weight: bold;
            padding: 12px 8px;
            text-align: center;
            border: 1px solid #883cab;
            font-size: 14px;
        }

        .invoice-table tbody td,
        .invoice-table tfoot td {
            /* Added tfoot td from quote.html for consistent styling */
            padding: 8px;
            border: 1px solid #dee2e6;
            font-size: 13px;
            vertical-align: middle;
        }

        /* Notes section style from quote.html */
        .notes-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            font-size: 13px;
            line-height: 1.4;
        }

        /* Floating Action Buttons Container (from ztemplate.html) */
        .fab-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            /* Aligns buttons vertically */
            gap: 15px;
            /* Adds space between buttons */
        }

        /* This CSS creates a floating action button styled for a back or navigation icon.
 * It uses a neutral gray color to indicate a non-destructive action.
 */
        .back-fab {
            /* Basic dimensions and shape, based on the user's provided example */
            width: 45px;
            height: 45px;
            border-radius: 50%;

            /* A neutral gray color for the back button */
            background-color: #6c757d;
            color: white;
            /* For the icon or text inside */
            border: none;

            /* Centering the icon or text inside the button */
            display: flex;
            justify-content: center;
            align-items: center;

            /* Font size for the icon or text */
            font-size: 20px;

            /* Adding a shadow for a "floating" effect */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            cursor: pointer;

            /* Smooth transitions for hover effects */
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        /* Hover and active states for user interaction, adapted from the example */
        .back-fab:hover {
            /* Slightly increase size and shadow on hover */
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
        }

        .back-fab:active {
            /* Slight press-down effect */
            transform: scale(0.95);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* This CSS creates a floating action button styled to look like the WhatsApp icon.
        * It is based on the `.fab` example provided, with changes to the color scheme.*/
        .whatsapp-fab {
            /* Basic dimensions and shape */
            width: 45px;
            height: 45px;
            border-radius: 50%;
            /* WhatsApp's official green color */
            background-color: #25D366;
            color: white;
            /* For the icon */
            border: none;
            /* Centering the icon inside */
            display: flex;
            justify-content: center;
            align-items: center;
            /* Using a larger font size for the icon */
            font-size: 20px;
            /* Adding a shadow for a "floating" effect */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            cursor: pointer;
            /* Smooth transitions for hover effects */
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        /* Hover and active states for user interaction */
        .whatsapp-fab:hover {
            /* Slightly increase size and shadow on hover */
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
        }

        .whatsapp-fab:active {
            /* Slight press-down effect */
            transform: scale(0.95);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* This CSS creates a floating action button styled for a PDF icon.
 * It uses a deep red color commonly associated with PDF files.
 */
        .pdf-fab {
            /* Basic dimensions and shape, based on the user's provided example */
            width: 45px;
            height: 45px;
            border-radius: 50%;

            /* A red color commonly used for PDF icons */
            background-color: #dc2f2e;
            color: white;
            /* For the icon or text inside */
            border: none;

            /* Centering the icon or text inside the button */
            display: flex;
            justify-content: center;
            align-items: center;

            /* Font size for the icon or text */
            font-size: 20px;

            /* Adding a shadow for a "floating" effect */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            cursor: pointer;

            /* Smooth transitions for hover effects */
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        /* Hover and active states for user interaction, adapted from the example */
        .pdf-fab:hover {
            /* Slightly increase size and shadow on hover */
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
        }

        .pdf-fab:active {
            /* Slight press-down effect */
            transform: scale(0.95);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* This CSS creates a floating action button styled for a JSON icon.
 * It uses a deep blue color commonly associated with JSON files.
 */
        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-20px);
            }

            60% {
                transform: translateY(-10px);
            }

            75% {
                transform: translateY(-5px);
            }
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
                box-shadow: #5ea6e5ff;
            }

            50% {
                /* In the middle of the animation, the shadow expands and fades out */
                box-shadow: 0 0 0 15px #a5c5e1ff;
            }
        }

        .json-fab {
            /* Basic dimensions and shape */
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: #3e75a6;
            color: white;
            border: none;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            cursor: pointer;

            /* Apply the bounce animation */
            animation: pulse-shadow 3s ease-in-out infinite;
            /*
              NOTE: We've removed the 'transform' transition from the original
              styles because the animation will now control the transform property.
              The hover and active effects still work because the animation
              is not a transition.
            */
            transition: box-shadow 0.2s ease-in-out;
        }

        /* Hover and active states */
        .json-fab:hover {
            /* The transform is now controlled by the bounce animation, so we
               will need to override it here to make the hover effect work as expected */
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
            /* To make sure the hover effect is not overridden by the animation,
               you might need to pause it on hover */
            animation-play-state: paused;
        }

        .json-fab:active {
            transform: scale(0.95);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            animation-play-state: paused;
        }

        /* This CSS creates a floating action button styled for a trash or delete icon.
 * It uses a dark gray color, which is a common choice for this type of action.
 */
        .trash-fab {
            /* Basic dimensions and shape, based on the user's provided example */
            width: 45px;
            height: 45px;
            border-radius: 50%;

            /* A dark gray color for the trash icon */
            background-color: #6c757d;
            color: white;
            /* For the icon or text inside */
            border: none;

            /* Centering the icon or text inside the button */
            display: flex;
            justify-content: center;
            align-items: center;

            /* Font size for the icon or text */
            font-size: 20px;

            /* Adding a shadow for a "floating" effect */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            cursor: pointer;

            /* Smooth transitions for hover effects */
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        /* Hover and active states for user interaction, adapted from the example */
        .trash-fab:hover {
            /* Slightly increase size and shadow on hover */
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
        }

        .trash-fab:active {
            /* Slight press-down effect */
            transform: scale(0.95);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* Styling for the circular floating action buttons (from ztemplate.html) */
        .fab {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: #883cab;
            /* Purple background */
            color: white;
            border: none;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            cursor: pointer;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        /* Hover effect for the buttons */
        .fab:hover {
            transform: scale(1.1);
            /* Zoom effect */
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
        }

        /* Style for editable cells */
        .editable-cell {
            cursor: text;
        }

        .editable-cell:focus {
            outline: 1px solid #883cab;
            /* Highlight with purple border on focus */
            background-color: #e6f7ff;
            /* Light blue background on focus */
        }

        /* PDF-specific styles to hide columns and adjust layout */
        .hide-for-pdf {
            display: none !important;
        }

        /* --- START: PDF Specific Print Styles for Tables (from quote.html) --- */
        @media print {
            .invoice-table {
                width: 100% !important;
                border-collapse: collapse;
            }

            .invoice-table thead {
                display: table-header-group;
            }

            .invoice-table tfoot {
                display: table-footer-group;
                page-break-before: avoid;
            }

            .invoice-table tbody tr {
                page-break-inside: avoid;
            }

            .invoice-table td,
            .invoice-table th {
                white-space: normal;
                word-wrap: break-word;
            }

            .html2pdf__page-break {
                height: 0;
                page-break-before: always;
            }
        }

        /* --- END: PDF Specific Print Styles for Tables --- */

        /* Custom Modal Styles (for confirmation messages instead of alert/confirm) */
        .custom-modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgba(0, 0, 0, 0.4);
            /* Black w/ opacity */
            justify-content: center;
            align-items: center;
        }

        .custom-modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .custom-modal-content button {
            background-color: #883cab;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .custom-modal-content button:hover {
            background-color: #6a2e85;
        }
    </style>
</head>

<body>
    <div class="invoice-document" id="element-to-print">
        <header class="d-flex justify-content-between align-items-center pb-4 mb-4 border-bottom">
            <div>
                <img src="assets/img/main.png" alt="Company Logo" class="company-logo rounded-circle">
                <h2 class="d-inline-block align-middle ms-3 mb-0 fw-bold">Quotation</h2>
            </div>
            <div class="text-end">
                <h4 class="fw-bolder text-purple mb-1">InControls Automation Services</h4>
                <p class="text-muted mb-0"><a class="link-offset-2 link-underline link-underline-opacity-0"
                        href="mailto:info@incontrolsautomation.com?&subject=Quotation Product Enquiry&body=Please attach the quotation PDF for specific enquiry related to it"
                        target="_top" style="color: inherit" onMouseOver="this.style.color='#883cab'"
                        onMouseOut="this.style.color='inherit'">info@incontrolsautomation.com</a></p>
            </div>
        </header>

        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="fw-bold">Quote To:</h5>
                <p class="mb-0 fw-bold">Company Name: <span id="displayCompanyName" class="fw-normal"></span></p>
                <p class="mb-0 fw-bold">Client Name: <span id="displayContactPerson" class="fw-normal"></span></p>
                <p class="mb-0 fw-bold">Email: <span id="displayEmail" class="fw-normal"></span></p>
                <p class="mb-0 fw-bold">Mobile: <span id="displayMobileNumber" class="fw-normal"></span></p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <p class="mb-1 fw-bold">Quote #: <span id="invoiceNumber" class="fw-normal"></span></p>
                <p class=" mb-0 fw-bold"><span id="currentDate" class="fw-normal"></span></p>
            </div>
        </div>

        <div id="quotationDetails">
            <div class="table-responsive">
                <table class="table table-bordered invoice-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 10%;">Sr. No.</th>
                            <th class="text-start" style="width: 50%;" data-pdf-width="55%">Product Name</th>
                            <th class="text-center" style="width: 10%;" data-pdf-width="20%">Part No.</th>
                            <th class="text-center" style="width: 10%;" data-pdf-width="15%">Quantity</th>
                            <th class="text-center" style="width: 10%;" id="th-unit-price">Unit Price</th>
                            <th class="text-center" style="width: 10%;" id="th-subtotal">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="quotationTableBody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end fw-bold" id="td-total-quantity-colspan">Total Quantity:</td>
                            <td class="text-center fw-bold" id="totalquantity"></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end fw-bold" id="td-grand-total-colspan">Grand Total:</td>
                            <td class="text-center fw-bold" id="grandTotal"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- <div id="emptyQuotationMessage" class="alert alert-info" style="display: none;">
            <p class="mb-0 text-center">No products selected for quotation. Please go back to the <a href="index.php"
                    class="alert-link">Product Selection Page</a> to select products.</p>
        </div> -->

        <div class="row mt-4">
            <div class="col-12">
                <div class="notes-section">
                    <p class="mb-2"><strong>Note:</strong> ALL MATERIAL IS CONSIDERED AS PER PRELIMINARY INSPECTION AND
                        INPUTS, INCASE OF ANY CHANGES IN DESIGN WILL BE DONE AFTER APPROVAL AND REVISED COST
                        IMPLICATIONS IF ANY.</p>
                </div>
            </div>
        </div>

        <footer class="mt-5 text-center text-muted">
            <p>Thank you for your business!</p>
            <p class="small">D203, Yashosthan Society Kondhwa Budruk | Pune, MH | 411048 | <a
                    class="link-offset-2 link-underline link-underline-opacity-0"
                    href="mailto:info@incontrolsautomation.com?&subject=Quotation Product Enquiry&body=Please attach the quotation PDF for specific enquiry related to it"
                    target="_top" style="color: inherit" onMouseOver="this.style.color='#883cab'"
                    onMouseOut="this.style.color='inherit'">info@incontrolsautomation.com</a>
            </p>
        </footer>
    </div>

    <div class="fab-container" id="fab-container">
        <button class="back-fab" onclick="goBack()" title="Go Back"><i class="bi bi-arrow-left"></i></button>
        <button class="whatsapp-fab" onclick="sharePdf()" title="Share as PDF"><i class="bi bi-whatsapp"></i></button>
        <button class="pdf-fab" onclick="downloadPdfAndJson()" title="Download as PDF and JSON"><i
                class="bi bi-file-pdf"></i></button>

        <!-- Only Upload JSON button remains -->
        <button class="json-fab" onclick="document.getElementById('uploadJsonInput').click()"
            title="Upload Data from JSON"><i class="bi bi-filetype-json"></i></button>

        <input type="file" id="uploadJsonInput" accept=".json" style="display: none;"
            onchange="uploadQuotationData(event)">
        <button class="trash-fab" onclick="showClearConfirmation()" title="Clear Content"><i
                class="bi bi-trash"></i></button>
    </div>

    <!-- Custom Confirmation Modal -->
    <div id="confirmationModal" class="custom-modal">
        <div class="custom-modal-content">
            <p id="modalMessage">Are you sure you want to clear all table contents and reset the quotation?</p>
            <button id="modalConfirmBtn">Yes</button>
            <button id="modalCancelBtn">No</button>
        </div>
    </div>

    <script>
        /**
         * Generates a unique invoice number based on the current date and time.
         * @returns {string} The formatted invoice number.
         */
        function generateInvoiceNumber() {
            const now = new Date();
            const dd = String(now.getDate()).padStart(2, '0');
            const mm = String(now.getMonth() + 1).padStart(2, '0');
            const yy = String(now.getFullYear()).slice(-2);
            return `INC_${dd}${mm}${yy}${now.getHours()}${now.getMinutes()}${now.getSeconds()}`;
        }

        /**
         * Core logic to load data from sessionStorage and populate the page.
         * Also sets up event listeners for editable cells.
         */
        document.addEventListener('DOMContentLoaded', function () {
            // Initial load of data from sessionStorage
            loadAndPopulateData();

            // Set current date
            const dateElement = document.getElementById('currentDate');
            const today = new Date();
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            dateElement.innerHTML = `<b>Date:</b> ${today.getDate()} ${monthNames[today.getMonth()]}, ${today.getFullYear()}`;
        });

        /**
         * Loads contact and product data from sessionStorage and populates the page.
         * This function is called on DOMContentLoaded and after successful JSON upload/clear.
         */
        function loadAndPopulateData() {
            let selectedProducts = [];
            let contactInfo = {};
            let invoiceNumberToDisplay = '';

            // Safely parse selectedProducts from sessionStorage
            try {
                const selectedProductsJSON = sessionStorage.getItem('selectedProducts');
                if (selectedProductsJSON) {
                    selectedProducts = JSON.parse(selectedProductsJSON);
                }
            } catch (e) {
                console.error("Error parsing selectedProducts from sessionStorage:", e);
                sessionStorage.removeItem('selectedProducts'); // Clear corrupted data
                selectedProducts = []; // Reset to empty array
            }

            // Safely parse contactInfo from sessionStorage
            try {
                const contactInfoJSON = sessionStorage.getItem('contactInfo');
                if (contactInfoJSON) {
                    contactInfo = JSON.parse(contactInfoJSON);
                }
            } catch (e) {
                console.error("Error parsing contactInfo from sessionStorage:", e);
                sessionStorage.removeItem('contactInfo'); // Clear corrupted data
                contactInfo = {}; // Reset to empty object
            }

            // Determine the invoice number to display
            const uploadedInvoiceNumber = sessionStorage.getItem('uploadedInvoiceNumber');

            if (uploadedInvoiceNumber) {
                invoiceNumberToDisplay = uploadedInvoiceNumber;
            } else if (contactInfo.invoiceNumber) { // Check if invoice number is part of contactInfo
                invoiceNumberToDisplay = contactInfo.invoiceNumber;
            } else {
                // Only generate a new invoice number if none exists from upload or contact info
                invoiceNumberToDisplay = generateInvoiceNumber();
            }

            const quotationTableBody = document.getElementById('quotationTableBody');
            const totalQuantityDisplay = document.getElementById('totalquantity');
            const grandTotalDisplay = document.getElementById('grandTotal');
            const quotationDetails = document.getElementById('quotationDetails');
            //const emptyQuotationMessage = document.getElementById('emptyQuotationMessage');

            const displayCompanyName = document.getElementById('displayCompanyName');
            const displayContactPerson = document.getElementById('displayContactPerson');
            const displayEmail = document.getElementById('displayEmail');
            const displayMobileNumber = document.getElementById('displayMobileNumber');
            const invoiceNumberElement = document.getElementById('invoiceNumber');


            // Display contact information
            displayCompanyName.textContent = contactInfo.companyName || 'N/A';
            displayContactPerson.textContent = contactInfo.contactPerson || 'N/A';
            displayEmail.textContent = contactInfo.email || 'N/A';
            displayMobileNumber.textContent = contactInfo.mobileNumber || 'N/A';

            // Set the determined invoice number
            invoiceNumberElement.textContent = invoiceNumberToDisplay;


            // Define the number of default rows
            const NUM_DEFAULT_ROWS = 20;

            quotationTableBody.innerHTML = ''; // Clear existing rows

            // if (selectedProducts.length === 0) {
            //     quotationDetails.style.display = 'none';
            //     emptyQuotationMessage.style.display = 'block';
            // } else {
            //     quotationDetails.style.display = 'block';
            //     emptyQuotationMessage.style.display = 'none';
            // }

            // Determine the number of rows to iterate over (at least NUM_DEFAULT_ROWS or more if products exceed it)
            const rowsToGenerate = Math.max(selectedProducts.length, NUM_DEFAULT_ROWS);

            for (let i = 0; i < rowsToGenerate; i++) {
                const row = quotationTableBody.insertRow();
                const item = selectedProducts[i]; // Get item at current index

                const productName = item ? item.name : '';
                const partNo = item ? item.partNo : '';
                const quantity = item ? (Number(item.quantity) || '') : ''; // Keep as string if 0, for empty cell
                const unitPrice = item ? (Number(item.price) || '') : ''; // Keep as string if 0, for empty cell

                // Determine if Sr. No. should be displayed
                const displaySrNo = (productName.trim() !== '' && (parseFloat(quantity) || 0) > 0) ? (i + 1) + '.' : '';

                row.innerHTML = `
                    <td class="text-center">${displaySrNo}</td>
                    <td contenteditable="true" class="editable-cell" data-field="productName">${productName}</td>
                    <td class="text-center" data-field="partNo">${partNo}</td>
                    <td class="text-center editable-cell" contenteditable="true" data-field="quantity">${quantity}</td>
                    <td class="text-center editable-cell" contenteditable="true" data-field="unitPrice">${unitPrice !== '' ? unitPrice.toFixed(2) : ''}</td>
                    <td class="text-center" data-field="subtotal"></td>
                `;
            }
            attachEventListenersToEditableCells();
            recalculateTotals(); // Initial calculation after populating
        }

        /**
         * Attaches 'input' event listeners to all editable cells for live recalculation.
         */
        function attachEventListenersToEditableCells() {
            const quotationTableBody = document.getElementById('quotationTableBody');
            const editableCells = quotationTableBody.querySelectorAll('[contenteditable="true"]');
            editableCells.forEach(cell => {
                cell.removeEventListener('input', handleCellInput); // Prevent duplicate listeners
                cell.removeEventListener('blur', handleCellBlur); // Prevent duplicate listeners
                cell.addEventListener('input', handleCellInput);
                cell.addEventListener('blur', handleCellBlur);
            });
        }

        /**
         * Handles input event for editable cells to update Sr. No. and recalculate totals.
         * @param {Event} event The input event.
         */
        function handleCellInput(event) {
            const row = event.target.closest('tr');
            const productNameCell = row.querySelector('[data-field="productName"]');
            const quantityCell = row.querySelector('[data-field="quantity"]');
            const srNoCell = row.querySelector('td:first-child'); // First td is Sr. No.

            const productName = productNameCell.textContent.trim();
            const quantity = parseFloat(quantityCell.textContent) || 0;

            // Update Sr. No. visibility based on product name and quantity
            if (productName !== '' && quantity > 0) {
                // Find the index of the current row within the tbody
                const rowIndex = Array.from(document.getElementById('quotationTableBody').children).indexOf(row);
                srNoCell.textContent = (rowIndex + 1) + '.';
            } else {
                srNoCell.textContent = '';
            }
            recalculateTotals(); // Always recalculate totals on any input
        }

        /**
         * Handles blur event for editable cells to format numbers.
         * @param {Event} event The blur event.
         */
        function handleCellBlur(event) {
            const field = event.target.dataset.field;
            if (field === 'quantity' || field === 'unitPrice') {
                let value = parseFloat(event.target.textContent);
                if (isNaN(value)) {
                    value = ''; // Clear if not a valid number
                } else if (field === 'unitPrice') {
                    value = value.toFixed(2); // Format unit price to 2 decimal places
                }
                event.target.textContent = value;
            }
        }

        /**
         * Recalculates subtotals for each row and updates the grand totals.
         */
        function recalculateTotals() {
            let totalQuantity = 0;
            let grandTotal = 0;

            const quotationTableBody = document.getElementById('quotationTableBody');
            const totalQuantityDisplay = document.getElementById('totalquantity');
            const grandTotalDisplay = document.getElementById('grandTotal');

            const rows = quotationTableBody.querySelectorAll('tr');
            rows.forEach(row => {
                const productNameCell = row.querySelector('[data-field="productName"]');
                const quantityCell = row.querySelector('[data-field="quantity"]');
                const unitPriceCell = row.querySelector('[data-field="unitPrice"]');
                const subtotalCell = row.querySelector('[data-field="subtotal"]');

                const productName = productNameCell.textContent.trim();
                let quantity = parseFloat(quantityCell.textContent) || 0;
                let unitPrice = parseFloat(unitPriceCell.textContent) || 0;

                // Ensure quantity and unitPrice are non-negative
                quantity = Math.max(0, quantity);
                unitPrice = Math.max(0, unitPrice);

                let subtotal = 0;
                // Calculate subtotal only if Product Name and Quantity are available and valid
                if (productName !== '' && quantity > 0) {
                    subtotal = quantity * unitPrice;
                }

                // Update subtotal cell
                if (subtotalCell) {
                    subtotalCell.textContent = subtotal === 0 ? '' : subtotal.toFixed(2);
                }

                totalQuantity += quantity;
                grandTotal += subtotal;
            });

            totalQuantityDisplay.textContent = totalQuantity;
            grandTotalDisplay.textContent = `₹${grandTotal.toFixed(2)}`;
        }

        /**
         * Adjusts the table layout for PDF generation (hides Unit Price and Subtotal columns).
         */
        window.prepareForPdf = function () { // Made global
            const thUnitPrice = document.getElementById('th-unit-price');
            const thSubtotal = document.getElementById('th-subtotal');
            const tdTotalQuantityColspan = document.getElementById('td-total-quantity-colspan');
            const tdGrandTotalColspan = document.getElementById('td-grand-total-colspan');
            const thProductName = document.querySelector('th[data-pdf-width="55%"]');
            const thPartNo = document.querySelector('th[data-pdf-width="20%"]');
            const thQuantity = document.querySelector('th[data-pdf-width="15%"]');
            const quotationTableBody = document.getElementById('quotationTableBody');


            if (thUnitPrice) thUnitPrice.classList.add('hide-for-pdf');
            if (thSubtotal) thSubtotal.classList.add('hide-for-pdf');

            // Hide corresponding td elements in tbody
            quotationTableBody.querySelectorAll('tr').forEach(row => {
                const unitPriceTd = row.querySelector('[data-field="unitPrice"]');
                const subtotalTd = row.querySelector('[data-field="subtotal"]');
                if (unitPriceTd) unitPriceTd.classList.add('hide-for-pdf');
                if (subtotalTd) subtotalTd.classList.add('hide-for-pdf');
            });

            // Adjust colspan for total rows in tfoot
            if (tdTotalQuantityColspan) tdTotalQuantityColspan.setAttribute('colspan', '3');
            if (tdGrandTotalColspan) tdGrandTotalColspan.setAttribute('colspan', '3');

            // Adjust widths of remaining headers to match quote2main.html
            if (thProductName && thProductName.dataset.pdfWidth) thProductName.style.width = thProductName.dataset.pdfWidth;
            if (thPartNo && thPartNo.dataset.pdfWidth) thPartNo.style.width = thPartNo.dataset.pdfWidth;
            if (thQuantity && thQuantity.dataset.pdfWidth) thQuantity.style.width = thQuantity.dataset.pdfWidth;
        }

        /**
         * Restores the table layout after PDF generation.
         */
        window.restoreLayout = function () { // Made global
            const thUnitPrice = document.getElementById('th-unit-price');
            const thSubtotal = document.getElementById('th-subtotal');
            const tdTotalQuantityColspan = document.getElementById('td-total-quantity-colspan');
            const tdGrandTotalColspan = document.getElementById('td-grand-total-colspan');
            const thProductName = document.querySelector('th[data-pdf-width="55%"]');
            const thPartNo = document.querySelector('th[data-pdf-width="20%"]');
            const thQuantity = document.querySelector('th[data-pdf-width="15%"]');
            const quotationTableBody = document.getElementById('quotationTableBody');

            if (thUnitPrice) thUnitPrice.classList.remove('hide-for-pdf');
            if (thSubtotal) thSubtotal.classList.remove('hide-for-pdf');

            // Show corresponding td elements in tbody
            quotationTableBody.querySelectorAll('tr').forEach(row => {
                const unitPriceTd = row.querySelector('[data-field="unitPrice"]');
                const subtotalTd = row.querySelector('[data-field="subtotal"]');
                if (unitPriceTd) unitPriceTd.classList.remove('hide-for-pdf');
                if (subtotalTd) subtotalTd.classList.remove('hide-for-pdf');
            });

            // Restore colspan for total rows in tfoot
            if (tdTotalQuantityColspan) tdTotalQuantityColspan.setAttribute('colspan', '5');
            if (tdGrandTotalColspan) tdGrandTotalColspan.setAttribute('colspan', '5');

            // Restore original widths of headers
            if (thProductName) thProductName.style.width = '50%';
            if (thPartNo) thPartNo.style.width = '10%';
            if (thQuantity) thQuantity.style.width = '10%';
        }


        /**
         * Navigates back to the index.html page.
         */
        function goBack() {
            window.location.href = 'index.php';
        }

        /**
         * Generates a timestamped filename base for files.
         * Format: Incontrols_DDMMYYYY_HHMMSS
         * @returns {string} The formatted filename base.
         */
        function generateFilenameBase() {
            const now = new Date();
            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            return `Incontrols_${day}${month}${year}_${hours}${minutes}${seconds}`;
        }

        /**
         * Downloads the current contact information and table data as a JSON file.
         * This function is now called internally by downloadPdfAndJson().
         */
        function downloadJsonDataInternal(baseFilename) {
            const contactInfo = JSON.parse(sessionStorage.getItem('contactInfo')) || {};
            const invoiceNumber = document.getElementById('invoiceNumber').textContent; // Get current invoice number
            const tableData = [];
            const rows = document.querySelectorAll('#quotationTableBody tr');

            rows.forEach(row => {
                const productName = row.querySelector('[data-field="productName"]').textContent.trim();
                const partNo = row.querySelector('[data-field="partNo"]').textContent.trim();
                const quantity = parseFloat(row.querySelector('[data-field="quantity"]').textContent) || 0;
                const unitPrice = parseFloat(row.querySelector('[data-field="unitPrice"]').textContent) || 0;

                if (productName !== '' || quantity > 0 || unitPrice > 0) {
                    tableData.push({
                        productName: productName,
                        partNo: partNo,
                        quantity: quantity,
                        unitPrice: unitPrice
                    });
                }
            });

            const dataToSave = {
                invoiceNumber: invoiceNumber, // Include invoice number
                contactInfo: contactInfo,
                products: tableData
            };

            const filename = `${baseFilename}.json`;
            const jsonString = JSON.stringify(dataToSave, null, 2);
            const blob = new Blob([jsonString], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        /**
         * Handles the PDF and JSON download process simultaneously.
         */
        async function downloadPdfAndJson() {
            const fabContainer = document.getElementById('fab-container');
            const elementToPrint = document.getElementById('element-to-print');
            const baseFilename = generateFilenameBase(); // Base filename for both PDF and JSON

            fabContainer.style.display = 'none'; // Hide buttons during PDF generation
            prepareForPdf(); // Apply PDF-specific layout changes

            try {
                // --- PDF Generation ---
                const canvas = await html2canvas(elementToPrint, {
                    scale: 2, // Higher scale for better resolution
                    useCORS: true, // Use CORS if images are from different origins
                    letterRendering: true // Attempts to improve text rendering
                });

                const imgData = canvas.toDataURL('image/jpeg');
                const { jsPDF } = window.jspdf; // Access jsPDF constructor
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'px',
                    format: [canvas.width, canvas.height] // Set PDF format to canvas dimensions
                });

                pdf.addImage(imgData, 'JPEG', 0, 0, canvas.width, canvas.height); // Add image to PDF
                pdf.save(`${baseFilename}.pdf`); // Save the PDF

                // --- JSON Download (after PDF is initiated) ---
                downloadJsonDataInternal(baseFilename);

            } catch (error) {
                console.error("Download failed:", error);
                console.warn("Could not generate files. Please try again.");
            } finally {
                fabContainer.style.display = 'flex'; // Show buttons again
                restoreLayout(); // Restore original layout
            }
        }


        /**
         * Handles sharing the PDF using the Web Share API with a download fallback.
         */
        async function sharePdf() {
            const fabContainer = document.getElementById('fab-container');
            const elementToPrint = document.getElementById('element-to-print');
            const filename = generateFilenameBase() + '.pdf'; // Only PDF for sharing

            fabContainer.style.display = 'none'; // Hide buttons during PDF generation
            prepareForPdf(); // Apply PDF-specific layout changes
            try {
                const canvas = await html2canvas(elementToPrint, {
                    scale: 2,
                    useCORS: false, // Set to false as per quote.html, adjust if external images are used
                    letterRendering: true
                });

                const imgData = canvas.toDataURL('image/jpeg');
                const { jsPDF } = window.jspdf; // Access jsPDF constructor
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'px',
                    format: [canvas.width, canvas.height]
                });

                pdf.addImage(imgData, 'JPEG', 0, 0, canvas.width, canvas.height);

                const blob = pdf.output('blob'); // Get PDF as a Blob
                const file = new File([blob], filename, { type: 'application/pdf' });

                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        files: [file],
                        title: 'Quotation PDF',
                        text: `Here is the quotation: ${filename}`,
                    });
                } else {
                    // Fallback to download if sharing is not supported
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            } catch (error) {
                console.error("PDF sharing failed:", error);
                // Use a custom message box or console warning instead of alert()
                console.warn("Sharing failed. Please try downloading instead.");
            } finally {
                fabContainer.style.display = 'flex'; // Show buttons again
                restoreLayout(); // Restore original layout
            }
        }

        /**
         * Uploads a JSON file to repopulate the contact information and table data.
         * @param {Event} event The change event from the file input.
         */
        function uploadQuotationData(event) {
            const file = event.target.files[0];
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                try {
                    const uploadedData = JSON.parse(e.target.result);
                    if (uploadedData.contactInfo && uploadedData.products) {
                        // Update sessionStorage for contact info
                        sessionStorage.setItem('contactInfo', JSON.stringify(uploadedData.contactInfo));

                        // Store products data directly to simulate selection
                        // Map the uploaded JSON structure to what populateQuotationTable expects
                        sessionStorage.setItem('selectedProducts', JSON.stringify(uploadedData.products.map(item => ({
                            name: item.productName,
                            partNo: item.partNo,
                            quantity: item.quantity,
                            price: item.unitPrice // Ensure 'price' is used as expected by existing logic
                        }))));

                        // If an invoice number is present in the uploaded data, store it
                        if (uploadedData.invoiceNumber) {
                            sessionStorage.setItem('uploadedInvoiceNumber', uploadedData.invoiceNumber);
                        } else {
                            sessionStorage.removeItem('uploadedInvoiceNumber'); // Clear if not present
                        }

                        // Reload the page to apply changes
                        location.reload();
                    } else {
                        console.error("Invalid JSON structure. Expected 'contactInfo' and 'products' properties.");
                        showModal("Invalid JSON file. Please ensure it contains 'contactInfo' and 'products' data.", false);
                    }
                } catch (error) {
                    console.error("Error parsing JSON file:", error);
                    showModal("Error reading JSON file. Please ensure it's a valid JSON format.", false);
                }
            };
            reader.readAsText(file);
            // Reset the file input value to allow re-uploading the same file
            event.target.value = '';
        }

        /**
         * Displays a custom confirmation modal.
         */
        function showClearConfirmation() {
            const modal = document.getElementById('confirmationModal');
            const confirmBtn = document.getElementById('modalConfirmBtn');
            const cancelBtn = document.getElementById('modalCancelBtn');
            const modalMessage = document.getElementById('modalMessage');

            modalMessage.textContent = "Are you sure you want to clear all quotation data, including contact info and quotation number?";
            modal.style.display = 'flex'; // Show the modal

            // Remove previous listeners to prevent duplicates
            confirmBtn.onclick = null;
            cancelBtn.onclick = null;

            confirmBtn.onclick = function () {
                modal.style.display = 'none'; // Hide modal
                clearTableContents(); // Proceed with clearing
            };

            cancelBtn.onclick = function () {
                modal.style.display = 'none'; // Hide modal
            };
        }

        /**
         * Displays a custom message modal (for errors/warnings).
         * @param {string} message The message to display.
         * @param {boolean} isConfirm If true, shows confirm/cancel buttons; otherwise, just an OK button.
         */
        function showModal(message, isConfirm = false) {
            const modal = document.getElementById('confirmationModal');
            const confirmBtn = document.getElementById('modalConfirmBtn');
            const cancelBtn = document.getElementById('modalCancelBtn');
            const modalMessage = document.getElementById('modalMessage');

            modalMessage.textContent = message;
            modal.style.display = 'flex';

            if (isConfirm) {
                confirmBtn.style.display = 'inline-block';
                cancelBtn.style.display = 'inline-block';
            } else {
                confirmBtn.style.display = 'inline-block'; // Use confirm as OK
                confirmBtn.textContent = 'OK';
                cancelBtn.style.display = 'none';
            }

            confirmBtn.onclick = function () {
                modal.style.display = 'none';
                if (!isConfirm) { // Reset text if it was an "OK" button
                    confirmBtn.textContent = 'Yes';
                }
            };
            cancelBtn.onclick = function () {
                modal.style.display = 'none';
            };
        }


        /**
         * Clears all content from the quotation table, contact info, and resets the quotation number.
         * Also clears relevant sessionStorage items.
         */
        function clearTableContents() {
            sessionStorage.removeItem('selectedProducts');
            sessionStorage.removeItem('contactInfo'); // Clear contact info
            sessionStorage.removeItem('uploadedInvoiceNumber'); // Clear any uploaded invoice number

            // Reload the page to re-render with empty data and a newly generated invoice number
            location.reload();
        }
    </script>
</body>

</html>