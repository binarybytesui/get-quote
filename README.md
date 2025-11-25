# 3.Quote-Final

Project: Quote-Final — a small PHP/HTML/JS quoting application with separate `admin` and `users` areas. Intended to run on a local LAMP stack (XAMPP) in `htdocs`.

## Overview

This repository implements a simple quoting workflow where site visitors (Users) can request quotes and Admins can review and manage those requests. The codebase is organized into two parallel folders (`admin/` and `users/`) plus supporting folders for credentials and assets.



### Full file tree (concise)
This is a compact map of the repository layout as found during the scan. Use this as a quick reference when navigating the project.

get-quote/
│
├── public/
│   ├── admin/
│   │   ├── views/
│   │   │     ├── index.php
│   │   │     ├── quote.php
│   │   │     └── review.php
│   │   ├── api/
│   │   │     ├── auth.php
│   │   │     └── get-products.php
│   │   └── assets/
│   │         ├── css/
│   │         ├── js/
│   │         └── img/
│   │
│   ├── user/
│   │   ├── views/
│   │   │     ├── index.html
│   │   │     └── quote.html
│   │   ├── api/
│   │   │     └── get-products.php
│   │   └── assets/
│   │         ├── css/
│   │         ├── js/
│   │         └── img/
│   │
│   └── index.php   (router in future)
│
├── src/
│   ├── database/
│   │     ├── connection.php
│   │     └── connection-status.php
│   ├── scripts/
│   │     └── import_products.php
│   ├── security/
│   │     ├── credentials.json
│   │     └── generate_password.php
│   ├── helpers/
│   ├── models/
│   └── services/
│
└── config/
    ├── env.php
    ├── cors.php
    └── app.php
