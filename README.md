# 3.Quote-Final

Project: Quote-Final — a small PHP/HTML/JS quoting application with separate `admin` and `users` areas. Intended to run on a local LAMP stack (XAMPP) in `htdocs`.

## Overview

This repository implements a simple quoting workflow where site visitors (Users) can request quotes and Admins can review and manage those requests. The codebase is organized into two parallel folders (`admin/` and `users/`) plus supporting folders for credentials and assets.

## Quick Start

- **Requirements:** `XAMPP` (Apache + PHP). PHP 7.x or 8.x recommended.
- **Install:** copy the `3.Quote-Final` folder into your XAMPP `htdocs` directory.
- **Run:** start Apache in XAMPP and open `http://localhost/3.Quote-Final/users/index.html` for the public site or `http://localhost/3.Quote-Final/admin/index.php` for admin.


## Full Project Map (files & purpose)

- `admin/` : Admin-facing PHP pages and admin assets
  - `index.php` : Admin landing / dashboard (if present)
  - `quote.php` : Admin quote view/editor
  - `review.php` : Admin review listing
  - `api/`
    - `auth.php` : Admin authentication endpoint
  - `assets/`
    - `css/style.css` : Admin styles
    - `js/` : products.json file containing the all products in json format
    - `img/` : Admin images (logo, board_material4.png)

- `users/` : Public-facing static pages & APIs
  - `index.html` : Public landing page
  - `quote.html` : Quote submission form
  - `assets/`
    - `css/style.css` : public site styles
    - `js/` : products.json file containing the all products in json format
    - `img/` : images (logo, board_material4.png)

- `passwords/`
  - `credentials.json` : stored credentials (sensitive — keep out of VCS)
  - `generate_password.php` : password generator

## API Endpoints (quick reference)
- `admin/api/auth.php` — Admin login/authorization


## Where to view the README

After placing the project in XAMPP `htdocs`, open in your browser:

 - `http://localhost/GET-QUOTE/` (shows directory or default file)
 - `http://localhost/GET-QUOTE/users/index.html` (public site)

---
Generated: README for quick developer onboarding and safe maintenance.

## Enhanced Details

### Full file tree (concise)
This is a compact map of the repository layout as found during the scan. Use this as a quick reference when navigating the project.

```
GET-QUOTE/
├─ admin/
│  ├─ index.php
│  ├─ quote.php
│  ├─ review.php
│  ├─ api/
│  │  ├─ auth.php
│  │  └─ get-products.php
│  └─ assets/
│     ├─ css/
│     │  └─ style.css
│     ├─ json-old/
│     │  └─ products.json
│     └─ img/
│        ├─ main.png
│        ├─ led.png
│        ├─ board_material1.png
│        ├─ board_material2.png
│        ├─ board_material3.png
│        ├─ accessories.png
│        ├─ mcb.png
│        ├─ add-ons.png
│        ├─ connectors.png
│        ├─ sockets.png
│        ├─ rccb.png
│        ├─ plugs.png
│        └─ inlet.png
├─ users/
│  ├─ index.html
│  ├─ quote.html
│  ├─ api/
│  │  └─ get-products.php
│  └─ assets/
│     ├─ css/
│     │  └─ style.css
│     ├─ json-old/
│     │  └─ products.json
│     └─ img/
│        ├─ main.png
│        ├─ led.png
│        ├─ board_material1.png
│        ├─ board_material2.png
│        ├─ board_material3.png
│        ├─ accessories.png
│        ├─ mcb.png
│        ├─ add-ons.png
│        ├─ connectors.png
│        ├─ sockets.png
│        ├─ rccb.png
│        ├─ plugs.png
│        └─ inlet.png
├─ db/
│  ├─ connection-status.php
│  └─ connection.php
├─ scripts/
│  └─ import_products.php
├─ passwords/
│  ├─ credentials.json
│  └─ generate_password.php
└─ README.md
```

