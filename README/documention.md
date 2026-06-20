# Laravel Barcode Management System — Codex Prompts (Phase 1–13)

> **Instructions:** Copy each phase prompt exactly as written and paste it into Codex. Each prompt is self-contained and builds on the previous phase. Do not skip phases.

---

## PHASE 1: Basic Setup

```
Create a new Laravel 12 project with the following setup:

1. Initialize a Laravel 12 project named "barcode-system".
2. Configure `.env` file with MySQL database connection:
   - DB_DATABASE=barcode_system
   - DB_USERNAME=root
   - DB_PASSWORD= (empty or as needed)
3. Install and configure the following packages:
   - picqer/php-barcode-generator (for barcode generation)
   - Laravel Sanctum (for API token authentication)
   - Run: composer require picqer/php-barcode-generator
   - Run: php artisan install:api (for Sanctum)
4. Set up Bootstrap 5 via CDN in a base Blade layout file at resources/views/layouts/app.blade.php. Include Bootstrap 5 CSS and JS CDN links.
5. Create a base API response helper or trait at app/Traits/ApiResponseTrait.php with methods: successResponse($data, $message, $statusCode) and errorResponse($message, $statusCode).
6. Set APP_URL in .env to http://localhost:8000
7. Set up CORS configuration in config/cors.php to allow all origins for API routes (for Flutter app compatibility).
8. Run php artisan key:generate.
9. Create the MySQL database named barcode_system.
10. Add a README.md with project setup instructions.

Use Laravel 12 best practices and standard folder structure. All APIs must be RESTful under /api/v1/ prefix.
```

---

## PHASE 2: Models and Migrations Setup

```
In the existing Laravel 12 barcode-system project, create the following database migrations, models, and factories. Follow Laravel coding standards strictly.

### Tables to create:

1. **users** (modify existing):
   - id, name, email, password, role (enum: 'admin', 'user', default 'user'), email_verified_at, remember_token, timestamps, softDeletes

2. **products**:
   - id, name (string), description (text, nullable), sku (string, unique, nullable), price (decimal 10,2, nullable), category (string, nullable), brand (string, nullable), unit (string, nullable), stock_quantity (integer, default 0), meta (json, nullable), is_active (boolean, default true), created_by (FK users.id, nullable), timestamps, softDeletes

3. **barcode_generations**:
   - id, user_id (FK users.id), product_id (FK products.id, nullable), unique_code (string, unique), barcode_format (enum: 'code128', 'qrcode', 'code39', 'ean13', default 'code128'), barcode_data (text — stores the raw text encoded in barcode), barcode_image_path (string, nullable — stored file path), custom_label (string, nullable), is_active (boolean, default true), timestamps, softDeletes

4. **scan_logs**:
   - id, scanned_by (FK users.id, nullable), barcode_generation_id (FK barcode_generations.id, nullable), unique_code (string), raw_scan_data (text), scan_result (enum: 'success', 'invalid'), product_data_snapshot (json, nullable — snapshot of product at time of scan), ip_address (string, nullable), user_agent (string, nullable), timestamps

5. **password_reset_tokens** (use Laravel default if not exists, else confirm it has email, token, created_at).

### For each table:
- Create Migration file.
- Create Eloquent Model with $fillable, $casts, relationships, and SoftDeletes where applicable.
- Add relationships:
  - User hasMany BarcodeGenerations, hasMany ScanLogs
  - BarcodeGeneration belongsTo User, belongsTo Product
  - ScanLog belongsTo User, belongsTo BarcodeGeneration
  - Product hasMany BarcodeGenerations

### Also:
- Create a database seeder that creates 1 admin user: email=admin@barcode.com, password=Admin@123, role=admin.
- Run: php artisan migrate --seed

Use Laravel 12 standards. Add model docblocks. Use $casts for json and enum fields.
```

---

## PHASE 3: Landing Page UI

```
In the existing Laravel 12 barcode-system project, create the public landing page UI. This is the main page loaded when the app is accessed without login.

### Route:
- GET / → LandingController@index → view: landing.index

### Create LandingController at app/Http/Controllers/LandingController.php.

### View: resources/views/landing/index.blade.php
Extend layouts/app.blade.php.

### Page sections (in order):

1. **Navbar**:
   - App logo/name "BarcodeMS" on left.
   - Right side: "Login" button (outline) and "Register" button (filled primary).
   - Bootstrap 5 navbar, responsive.

2. **Hero Section**:
   - Title: "Scan Any Barcode Instantly"
   - Subtitle: "No login required. Point your camera and get product details in seconds."
   - Two CTA buttons: "Start Scanning ↓" (scrolls to scanner) and "Admin Login" (links to /login).

3. **Barcode Scanner Section** (id="scanner-section"):
   - Camera live preview using HTML5 getUserMedia (video element).
   - "Start Camera" button and "Stop Camera" button.
   - Use the open-source library **html5-qrcode** (include via CDN: https://unpkg.com/html5-qrcode).
   - Support scanning Code 128, QR Code, Code 39.
   - **Manual Input**: A text input field labeled "Or enter barcode manually" with a "Lookup" button.
   - **Upload File**: A file input labeled "Or upload an image of the barcode" with a "Scan File" button.
   - On successful scan: show a result card below with: Unique Code, Product Name, Description, SKU, Price, Brand, Category — fetched via AJAX GET /api/v1/scan/{unique_code}.
   - If barcode not found: show red alert "Invalid barcode. No product found for this code."
   - **Copy Button**: A "Copy" button next to the result that copies the result text to clipboard.

4. **Scan History Section**:
   - Show last 10 scans stored in browser localStorage under key "scan_history".
   - Display as a list: each row shows unique code, timestamp, and two buttons: "Copy" and "Delete".
   - "Clear All" button to clear entire history.
   - If no history: show "No scan history yet."

5. **Footer**: Simple footer with app name and year.

### JavaScript behavior:
- After each successful scan (camera, manual, or file upload), save {unique_code, result_text, timestamp} to localStorage scan_history array (max 20 items, newest first).
- Refresh the scan history list on the page after each scan.

### Styling: Use Bootstrap 5. Clean, professional look. Dark navbar. Scanner section with a prominent camera box (border, rounded corners). Result card with icon and clean data rows.

Create the route in routes/web.php. Do not require authentication for this page.
```

---

## PHASE 4: Login, Register and Forgot Password UI

```
In the existing Laravel 12 barcode-system project, create the following Auth UI pages. These are Blade view pages only (no backend logic yet). Follow Bootstrap 5 styling.

### Routes (add to routes/web.php, no auth middleware):
- GET /login → AuthController@loginForm → view: auth.login
- GET /register → AuthController@registerForm → view: auth.register
- GET /forgot-password → AuthController@forgotForm → view: auth.forgot

### Create AuthController at app/Http/Controllers/AuthController.php with methods:
loginForm(), registerForm(), forgotForm() — each just returns the corresponding view.

### Layout:
Create resources/views/layouts/auth.blade.php — a centered card layout (vertically and horizontally centered on page) with Bootstrap 5. Dark background (#1a1a2e). White card with shadow. App name "BarcodeMS" as heading above the card.

### Views:

#### 1. resources/views/auth/login.blade.php
- Extends layouts/auth.blade.php
- Card title: "Welcome Back"
- Fields:
  - Email (type=email, id=email, required)
  - Password (type=password, id=password, required) with show/hide toggle eye icon
- "Remember Me" checkbox
- Primary button: "Login" (full width)
- Links below card: "Forgot Password?" → /forgot-password | "Don't have an account? Register" → /register
- Display error alert div (id=loginError, hidden by default) for API errors.

#### 2. resources/views/auth/register.blade.php
- Extends layouts/auth.blade.php
- Card title: "Create Account"
- Fields:
  - Full Name (type=text, id=name, required)
  - Email (type=email, id=email, required)
  - Password (type=password, id=password, required) with show/hide toggle
  - Confirm Password (type=password, id=password_confirmation, required) with show/hide toggle
- Primary button: "Register" (full width)
- Link below: "Already have an account? Login" → /login
- Display error alert div (id=registerError, hidden by default).

#### 3. resources/views/auth/forgot.blade.php
- Extends layouts/auth.blade.php
- Card title: "Reset Password"
- Subtitle: "Enter your email and we'll send you a reset link."
- Fields:
  - Email (type=email, id=email, required)
- Primary button: "Send Reset Link" (full width)
- Success alert div (id=forgotSuccess, hidden by default) in green.
- Error alert div (id=forgotError, hidden by default) in red.
- Link: "Back to Login" → /login

### No form action attributes — all forms will be submitted via JavaScript fetch (API calls added in Phase 5). Add id to each form: id=loginForm, id=registerForm, id=forgotForm.

### Include Bootstrap 5 Icons CDN for eye toggle icons.
```

---

## PHASE 5: Login, Register and Forgot Password APIs

```
In the existing Laravel 12 barcode-system project, create all authentication APIs and connect them to the existing UI pages from Phase 4.

### API Routes (add to routes/api.php under prefix api/v1):
- POST /api/v1/auth/register
- POST /api/v1/auth/login
- POST /api/v1/auth/forgot-password
- POST /api/v1/auth/reset-password
- POST /api/v1/auth/logout (requires Sanctum auth middleware)
- GET  /api/v1/auth/me (requires Sanctum auth middleware)

### Create app/Http/Controllers/Api/V1/AuthController.php

### Implement each method:

#### register(Request $request):
- Validate: name (required, string, max 255), email (required, email, unique:users), password (required, min 8, confirmed)
- Create user with role='user'
- Return success response with user data and token (createToken)
- Use ApiResponseTrait

#### login(Request $request):
- Validate: email, password
- Attempt auth with Auth::attempt
- If fail: return errorResponse('Invalid credentials', 401)
- If success: create Sanctum token, return user data + token + role
- Use ApiResponseTrait

#### forgotPassword(Request $request):
- Validate: email (required, email, exists:users,email)
- Use Password::sendResetLink($request->only('email'))
- Return success or error response
- Configure mail in .env to use log driver for testing (MAIL_MAILER=log)

#### resetPassword(Request $request):
- Validate: token, email, password (min 8, confirmed)
- Use Password::reset()
- Return success or error

#### logout(Request $request):
- Revoke current token: $request->user()->currentAccessToken()->delete()
- Return success response

#### me(Request $request):
- Return authenticated user data

### Connect to UI (update the Blade views from Phase 4):

#### Login page JS:
- On #loginForm submit (preventDefault), POST to /api/v1/auth/login with {email, password}
- On success: store token in localStorage as 'auth_token', store user as 'auth_user' (JSON)
- Redirect to /dashboard
- On error: show #loginError with message

#### Register page JS:
- On #registerForm submit, POST to /api/v1/auth/register
- On success: store token + user in localStorage, redirect to /dashboard
- On error: show #registerError with message (handle validation errors array)

#### Forgot page JS:
- On #forgotForm submit, POST to /api/v1/auth/forgot-password
- On success: show #forgotSuccess "Reset link sent! Check your email."
- On error: show #forgotError

### All API calls must include:
- Header: 'Accept': 'application/json'
- Header: 'Content-Type': 'application/json'

### Update routes/web.php:
- GET /dashboard → redirect to /dashboard (actual page in Phase 6)
- Add a blade partial resources/views/partials/auth-check.blade.php that includes JS checking localStorage 'auth_token'. If visiting /login or /register while token exists, redirect JS to /dashboard.

Create a reusable JS function setAuthHeaders() that reads token from localStorage and adds Authorization: Bearer {token} header to all API fetch calls.
```

---

## PHASE 6: Dashboard Page

```
In the existing Laravel 12 barcode-system project, create the admin dashboard page with sidebar layout.

### Route:
- GET /dashboard → DashboardController@index → view: admin.dashboard
- This page is for logged-in admins only. Auth is checked via JS (localStorage token). If no token, redirect to /login.

### Create app/Http/Controllers/DashboardController.php (web controller, returns view only).

### Dashboard Layout:
Create resources/views/layouts/admin.blade.php — a full admin layout with:
- Fixed left sidebar (width 250px, dark #1e293b background)
- Main content area (remaining width, light gray background)
- Top header bar (white, shadow, shows user name and logout button on right)

### Sidebar items (with Bootstrap Icons):
- 🏠 Dashboard → /dashboard
- ➕ Generate Barcode → /barcodes/generate
- 📋 Barcodes List → /barcodes
- 📷 Scanner → /scanner
- 🚪 Logout (button, triggers API logout)

Active state styling for current route.

### View: resources/views/admin/dashboard.blade.php
Extends layouts/admin.blade.php

### Dashboard Stats Cards (top row, 4 cards):
1. Total Barcodes Generated — GET /api/v1/dashboard/stats
2. Total Scans Today — same API
3. Total Products — same API  
4. Active Users — same API
Use Bootstrap card with icon, large number, and label. Load via AJAX on page load.

### Recent Generated Barcodes Table:
- Last 10 barcodes — GET /api/v1/dashboard/recent-barcodes
- Columns: #, Unique Code, Format, Label, Created At, Actions (View)
- Load via AJAX on page load. Show loading spinner while loading.

### User Login Activity Section:
- Show current user info: Name, Email, Role, Last Login (from API /api/v1/auth/me).

### Create API Controller: app/Http/Controllers/Api/V1/DashboardController.php
Routes (add to routes/api.php, protected by auth:sanctum):
- GET /api/v1/dashboard/stats → returns {total_barcodes, scans_today, total_products, active_users}
- GET /api/v1/dashboard/recent-barcodes → returns last 10 barcode_generations with user and product (paginated, per_page=10)

### JS on dashboard page:
- On page load: check localStorage auth_token. If missing, redirect to /login.
- Load stats and recent barcodes via fetch with Authorization header.
- Logout button: POST /api/v1/auth/logout → clear localStorage → redirect /login.
- Display user name in header from localStorage auth_user.

Use Bootstrap 5 for all styling. Cards with colorful left borders (blue, green, orange, purple).
```

---

## PHASE 7: Generate Barcode Page UI

```
In the existing Laravel 12 barcode-system project, create the Generate Barcode page UI.

### Route:
- GET /barcodes/generate → BarcodeController@generateForm → view: admin.barcodes.generate

### View: resources/views/admin/barcodes/generate.blade.php
Extends layouts/admin.blade.php

### Page Layout (two-column on desktop, stacked on mobile):

#### Left Column — Input Form:
- Page title: "Generate Barcode"
- Form (id=generateBarcodeForm):
  - **Barcode Data** (textarea, id=barcodeData, rows=4, required):
    Label: "Barcode Content", Placeholder: "Enter product name, SKU, description, or any text..."
  - **Custom Label** (input text, id=customLabel):
    Label: "Human Readable Label (shown below barcode)", Placeholder: "e.g. PROD-001"
  - **Barcode Format** (select, id=barcodeFormat):
    Options: code128 (Code 128 — Default), qrcode (QR Code), code39 (Code 39), ean13 (EAN-13)
  - **Link to Product** (select, id=productId, optional):
    Label: "Link to Product (Optional)" — populated via AJAX GET /api/v1/products?per_page=100
  - Generate Barcode button (full width, primary, id=generateBtn)
  - Error alert (id=generateError, hidden)

#### Right Column — Barcode Preview:
- Card with title "Preview"
- Barcode image (id=barcodePreview, max-width 100%, hidden until generated)
- Below image: Human readable text (id=humanReadableText)
- Unique Code display: "Unique Code: XXXXXXX" (id=uniqueCodeDisplay, hidden until generated)
- Download buttons (hidden until generated):
  - "Download PNG" (id=downloadPng)
  - "Download SVG" (id=downloadSvg)
- "Generate another" link (resets form, hidden until generated)

### Duplicate Validation UI:
- On input in #barcodeData (debounce 800ms): call GET /api/v1/barcodes/check-duplicate?data={encoded_text}
- Show green checkmark "✓ Unique" or yellow warning "⚠ Similar data exists, a new unique code will still be generated."

### JS Behavior:
- On form submit (preventDefault): POST to /api/v1/barcodes/generate (Phase 8 API)
- Show loading spinner on button while waiting
- On success: display barcode image (base64 from API), show unique code, show download buttons
- Download PNG: trigger download of base64 image
- Download SVG: trigger download of SVG string from API response
- On error: show #generateError

### Styling: Clean two-panel layout. Preview card with dashed border when empty, solid border when barcode displayed. Loading skeleton for preview area.
```

---

## PHASE 8: APIs for Generate Barcode Functionality

```
In the existing Laravel 12 barcode-system project, create all barcode generation APIs and connect them to the Generate Barcode page UI from Phase 7.

### API Routes (add to routes/api.php, protected by auth:sanctum):
- POST   /api/v1/barcodes/generate
- GET    /api/v1/barcodes/check-duplicate
- GET    /api/v1/products (for product dropdown)

### Create app/Http/Controllers/Api/V1/BarcodeController.php

#### generate(Request $request):
Validate:
- barcode_data: required, string, max 500
- barcode_format: required, in:code128,qrcode,code39,ean13
- custom_label: nullable, string, max 255
- product_id: nullable, exists:products,id

Logic:
1. Generate a unique_code: use format "BC" + strtoupper(uniqid()) — check uniqueness in barcode_generations table, regenerate if collision.
2. Use picqer/php-barcode-generator:
   - For code128, code39, ean13: use BarcodeGeneratorPNG and BarcodeGeneratorSVG
   - For qrcode: use a different approach — generate QR via a simple QR package or use endroid/qr-code. Actually, use BarcodeGeneratorPNG with TYPE_QRCODE if supported, else note it.
   - Actually: use `Picqer\Barcode\BarcodeGeneratorPNG` for PNG and `Picqer\Barcode\BarcodeGeneratorSVG` for SVG.
   - Encode the unique_code (NOT the full barcode_data) in the barcode image.
   - Map format string to Picqer constant: code128→TYPE_CODE_128, code39→TYPE_CODE_39, ean13→TYPE_EAN_13, qrcode→TYPE_QRCODE (if available, else TYPE_CODE_128).
3. Save PNG to storage/app/public/barcodes/{unique_code}.png using Storage::put.
4. Run php artisan storage:link if not done.
5. Save record to barcode_generations table.
6. Return: unique_code, barcode_format, barcode_image_base64 (base64_encode of PNG), barcode_svg (SVG string), barcode_image_url (public URL), custom_label, created_at.

#### checkDuplicate(Request $request):
- GET param: data (string)
- Check if any barcode_generation has barcode_data LIKE %{data}%
- Return: {exists: true/false, count: N}

#### index (for products list):
Create app/Http/Controllers/Api/V1/ProductController.php
- GET /api/v1/products: return paginated products list (id, name, sku). per_page from request, default 20.

### Connect to Phase 7 UI:
Update resources/views/admin/barcodes/generate.blade.php JS:
- On form submit: POST /api/v1/barcodes/generate with JSON body {barcode_data, barcode_format, custom_label, product_id}
- Include Authorization: Bearer {token} header
- On success: 
  - Set #barcodePreview src to "data:image/png;base64," + response.barcode_image_base64
  - Show #barcodePreview
  - Set #humanReadableText to response.custom_label or response.unique_code
  - Set #uniqueCodeDisplay text
  - Show download buttons
  - For "Download PNG": create anchor with href=data:image/png;base64,{base64}, download={unique_code}.png, click it
  - For "Download SVG": create blob from svg string, download as {unique_code}.svg
- On error: show error message

### Also load products on page load:
- GET /api/v1/products?per_page=100 → populate #productId select options as <option value="{id}">{name} ({sku})</option>

Run: php artisan storage:link
```

---

## PHASE 9: Barcodes List Page UI

```
In the existing Laravel 12 barcode-system project, create the Barcodes List page UI using DataTables.

### Route:
- GET /barcodes → BarcodeWebController@index → view: admin.barcodes.index

### Install DataTables via CDN (add to admin layout or this view):
- DataTables CSS: https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css
- DataTables JS: https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js
- DataTables Bootstrap5: https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js
- jQuery CDN (required for DataTables): https://code.jquery.com/jquery-3.7.1.min.js

### View: resources/views/admin/barcodes/index.blade.php
Extends layouts/admin.blade.php

### Page Elements:
1. **Page Header row**:
   - Left: "Generated Barcodes" h4 title
   - Right: "Generate New Barcode" button → /barcodes/generate

2. **DataTable** (id=barcodesTable):
Columns:
   - # (row number)
   - Unique Code (bold, monospace font)
   - Format (Badge: code128=primary, qrcode=success, code39=warning, ean13=info)
   - Custom Label
   - Linked Product (product name or "—")
   - Created At (formatted date)
   - Actions:
     - 👁 View button → /barcodes/{id}
     - ✏ Edit button (opens inline edit modal)
     - 🗑 Delete button (opens confirm modal)

3. **Edit Modal** (id=editBarcodeModal, Bootstrap modal):
   - Title: "Update Barcode"
   - Fields:
     - Custom Label (input text, id=editCustomLabel)
     - Link to Product (select, id=editProductId, nullable)
   - Buttons: "Cancel" and "Save Changes" (id=saveEditBtn)
   - Hidden input: id=editBarcodeId

4. **Delete Confirm Modal** (id=deleteBarcodeModal):
   - Body: "Are you sure you want to delete this barcode? This action cannot be undone."
   - Buttons: "Cancel" and "Delete" (danger, id=confirmDeleteBtn)
   - Hidden input: id=deleteBarcodeId

### DataTable Initialization (JS):
- Init #barcodesTable with:
  - processing: true
  - serverSide: true  
  - ajax: {url: '/api/v1/barcodes', headers: {Authorization: 'Bearer {token}', Accept: 'application/json'}, type: 'GET'}
  - columns mapped to API response fields
  - Render Actions column with View/Edit/Delete buttons
  - page length: 10

### Edit button click:
- Set editBarcodeId to row's id
- Populate modal fields from row data
- Show modal

### Delete button click:
- Set deleteBarcodeId to row's id
- Show confirm modal

### Loading: Show spinner overlay while table loads.

### Styling: Striped table, hover highlight. Action buttons as small icon buttons.
```

---

## PHASE 10: Barcodes List Page APIs

```
In the existing Laravel 12 barcode-system project, create all APIs for the Barcodes List page and connect them to the Phase 9 UI.

### API Routes (add to routes/api.php, protected by auth:sanctum):
- GET    /api/v1/barcodes              → index (list with DataTables server-side)
- PUT    /api/v1/barcodes/{id}         → update
- DELETE /api/v1/barcodes/{id}         → destroy (soft delete)

### In app/Http/Controllers/Api/V1/BarcodeController.php add:

#### index(Request $request):
Support DataTables server-side parameters: draw, start, length, search[value], order[0][column], order[0][dir].
Query barcode_generations with:
- Relationships: user, product
- Filter by search term (searches: unique_code, custom_label, product.name)
- Order by requested column
- Soft-deleted records excluded
- Return DataTables format: {draw, recordsTotal, recordsFiltered, data: [...]}
Each row data: id, unique_code, barcode_format, custom_label, product_id, product_name, user_name, barcode_image_url, created_at (formatted Y-m-d H:i)

#### update(Request $request, $id):
Validate:
- custom_label: nullable, string, max 255
- product_id: nullable, exists:products,id
Find barcode (not soft deleted, no ownership check for admin).
Update allowed fields only: custom_label, product_id.
Return updated record with success message.

#### destroy($id):
Find barcode. Soft delete (delete()). Return success message.
Do NOT hard delete. Use SoftDeletes.

### Connect to Phase 9 UI:

Update resources/views/admin/barcodes/index.blade.php JS:

**DataTable Ajax Setup:**
```javascript
ajax: {
  url: '/api/v1/barcodes',
  type: 'GET',
  beforeSend: function(xhr) {
    xhr.setRequestHeader('Authorization', 'Bearer ' + localStorage.getItem('auth_token'));
    xhr.setRequestHeader('Accept', 'application/json');
  }
}
```

**Edit Save (#saveEditBtn click):**
- PUT /api/v1/barcodes/{editBarcodeId} with {custom_label, product_id}
- On success: close modal, reload DataTable, show success toast
- On error: show error in modal

**Delete Confirm (#confirmDeleteBtn click):**
- DELETE /api/v1/barcodes/{deleteBarcodeId}
- On success: close modal, reload DataTable, show success toast "Barcode deleted."
- On error: show alert

**Toast notification:**
Add a Bootstrap toast at top-right corner (id=successToast) for success messages.

Also update the product select in edit modal: on modal open, fetch /api/v1/products?per_page=100 and populate options.
```

---

## PHASE 11: Barcode View Page UI and APIs

```
In the existing Laravel 12 barcode-system project, create the Barcode View/Detail page with its API.

### API Route (add to routes/api.php, protected by auth:sanctum):
- GET /api/v1/barcodes/{id} → show

#### show($id) in BarcodeController:
Find barcode_generation by id (with relationships: user, product).
Return: id, unique_code, barcode_format, barcode_data, custom_label, barcode_image_url, barcode_image_path, is_active, product (id, name, sku, description, price, brand, category), user (id, name, email), created_at, updated_at, scan_count (count of scan_logs for this barcode), last_scanned_at (latest scan_log created_at).

### Web Route:
- GET /barcodes/{id} → BarcodeWebController@show → view: admin.barcodes.show

### View: resources/views/admin/barcodes/show.blade.php
Extends layouts/admin.blade.php

### Page Layout:
**Top action bar:**
- "← Back to List" button → /barcodes
- Right: "Edit" button (opens edit modal), "Delete" button (danger, opens confirm modal)

**Two-column layout:**

Left Column (60%):
1. **Barcode Details Card:**
   - Barcode Image (large, centered, max-width 400px) — loaded from API
   - Below image: Human readable text (custom_label or unique_code, styled as monospace)
   - "Download PNG" and "Download SVG" buttons

2. **Encoded Information Card:**
   - Unique Code: (monospace badge)
   - Format: (badge)
   - Barcode Data (the raw encoded text, in a <pre> block)
   - Is Active: Yes/No badge
   - Created At, Updated At

Right Column (40%):
3. **Linked Product Card** (if product linked):
   - Product Name (bold)
   - SKU, Price, Brand, Category, Description
   - "View Product" placeholder link
   If no product: "No product linked" with muted text

4. **Scan Statistics Card:**
   - Total Scans: (large number)
   - Last Scanned At: (or "Never scanned")

5. **Generated By Card:**
   - User name and email

**Edit Modal** (id=editBarcodeModal — same as Phase 9 edit modal):
- Custom Label, Product ID fields
- Save via PUT /api/v1/barcodes/{id}

**Delete Confirm Modal:**
- Confirm delete, then DELETE /api/v1/barcodes/{id}, redirect to /barcodes on success

### JS on page load:
- Extract barcode ID from URL (window.location.pathname)
- Fetch GET /api/v1/barcodes/{id} with auth header
- Populate all fields from response
- If image URL exists, set img src
- Download PNG: fetch image and trigger download
- On delete success: window.location.href = '/barcodes'
- On edit save: reload page

### Styling: Clean detail page. Left column barcode image centered with subtle shadow. Stats in colored badges. Responsive stacking on mobile.
```

---

## PHASE 12: Scanner Page UI for Admin

```
In the existing Laravel 12 barcode-system project, create the Scanner page for the admin dashboard. This is the same scanning functionality as the landing page but styled with the admin dashboard layout.

### Route:
- GET /scanner → ScannerController@index → view: admin.scanner.index

### Create app/Http/Controllers/ScannerController.php with index() method.

### View: resources/views/admin/scanner/index.blade.php
Extends layouts/admin.blade.php (admin sidebar layout, not the public layout).

### Page Content (mirror the landing page scanner section with admin styling):

**Page Header:**
- Title: "Barcode Scanner"
- Subtitle: "Scan barcodes to look up product details"

**Main Scanner Card** (Bootstrap card with shadow):

1. **Camera Scanner Section:**
   - Video preview element (id=cameraPreview, style: width 100%, max-height 300px, background #000, rounded corners)
   - Button row: "Start Camera" (success btn), "Stop Camera" (secondary btn, hidden initially)
   - Use html5-qrcode library (CDN: https://unpkg.com/html5-qrcode)
   - Scan success callback: call lookupBarcode(decodedText)
   - Show scanned text in input field after scan

2. **Manual Input Section** (separator with OR label):
   - Input group: text input (id=manualBarcodeInput, placeholder="Enter barcode or unique code") + "Lookup" button (id=manualLookupBtn)

3. **Upload File Section** (separator with OR label):
   - File input (id=uploadFileInput, accept="image/*") + "Scan File" button (id=scanFileBtn)
   - Use html5-qrcode Html5Qrcode.scanFile() method

**Result Card** (id=resultCard, hidden initially):
- Header: "Scan Result" with Copy All button (icon)
- Body: display scanned data rows (same as landing page: Unique Code, Product Name, SKU, Price, Brand, etc.)
- Alert for invalid: red alert "Invalid barcode — no product found"

**Scan History Card:**
- Title: "Scan History (This Session)"
- Use localStorage key "admin_scan_history"
- List with: Unique Code, timestamp, Copy button, Delete button
- "Clear All" button
- Empty state: "No scans yet in this session"

### JS function lookupBarcode(code):
- POST /api/v1/scan with {unique_code: code} and Authorization header
- On success: show result card with product data, add to scan history
- On failure (404): show invalid barcode alert, add to history with "Invalid" status

### Styling:
- Use admin color scheme (matches dashboard)
- Camera box with dashed border when inactive, solid border with scanning animation when active
- Result card with left green border on success, red on failure
- History items: subtle background, hover highlight

### Include html5-qrcode CDN in this view's @push('scripts') or directly in the view.
```

---

## PHASE 13: Scan APIs and Full Implementation

```
In the existing Laravel 12 barcode-system project, create all scanning APIs and fully integrate them into both the admin scanner page (Phase 12) and the public landing page (Phase 3).

### API Routes (add to routes/api.php):
- POST /api/v1/scan          → (public, no auth required) — scan by unique_code
- GET  /api/v1/scan/history  → (auth required) — get user's scan logs from DB
- GET  /api/v1/scan/{unique_code} → (public) — same as POST but via GET for convenience

### Create app/Http/Controllers/Api/V1/ScanController.php

#### scan(Request $request): [POST /api/v1/scan]
Validate: unique_code (required, string, max 100)
Logic:
1. Find barcode_generation where unique_code = request.unique_code (not soft deleted)
2. If not found:
   - Log to scan_logs: unique_code, raw_scan_data=request.unique_code, scan_result='invalid', ip_address, user_agent
   - Return errorResponse('Invalid barcode. No product found.', 404) with {valid: false}
3. If found:
   - Load product relationship
   - Build product_data_snapshot as JSON: {unique_code, barcode_format, custom_label, product: {name, sku, price, brand, category, description, unit, stock_quantity}}
   - Log to scan_logs: all fields, scan_result='success', scanned_by=Auth::id() (nullable)
   - Return successResponse({
       valid: true,
       unique_code: ...,
       barcode_format: ...,
       custom_label: ...,
       barcode_image_url: ...,
       product: {id, name, sku, description, price, brand, category, unit, stock_quantity} or null,
       scanned_at: now()
     })

#### scanByGet(Request $request, $unique_code): [GET /api/v1/scan/{unique_code}]
Same logic as scan() but unique_code from URL param.

#### history(Request $request): [GET /api/v1/scan/history, auth:sanctum]
Return paginated scan_logs for Auth::user() ordered by created_at DESC.
Include: unique_code, scan_result, created_at, product_data_snapshot.

### Update Public Landing Page (Phase 3 view):

In resources/views/landing/index.blade.php, update the JS:

**lookupBarcode(code) function:**
```javascript
async function lookupBarcode(code) {
  try {
    const response = await fetch('/api/v1/scan/' + encodeURIComponent(code), {
      headers: {'Accept': 'application/json'}
    });
    const data = await response.json();
    if (data.data && data.data.valid) {
      showResult(data.data);
      saveToHistory({code, result: formatResultText(data.data), timestamp: new Date().toISOString(), valid: true});
    } else {
      showInvalidMessage();
      saveToHistory({code, result: 'Invalid barcode', timestamp: new Date().toISOString(), valid: false});
    }
  } catch (e) {
    showInvalidMessage();
  }
}
```

**showResult(data):** Build and show result card HTML with all product fields. Include "Copy" button that copies formatted text.

**Copy All:** copy formatted text: "Code: X\nProduct: X\nSKU: X\nPrice: X\n..."

**Scan History (localStorage):**
- Key: 'scan_history'
- Save up to 20 items
- renderHistory(): rebuild history list from localStorage
- Delete item: remove by index, re-render
- Clear All: clear array, re-render

### Update Admin Scanner Page (Phase 12 view):

In resources/views/admin/scanner/index.blade.php, update JS:

**lookupBarcode(code) function:**
```javascript
async function lookupBarcode(code) {
  const token = localStorage.getItem('auth_token');
  const response = await fetch('/api/v1/scan', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({unique_code: code})
  });
  const data = await response.json();
  // Show result card, save to admin_scan_history in localStorage
}
```

All result display, copy, and history logic same as landing page but styled for admin.

### Final checks:
- Ensure CORS allows public /api/v1/scan/* routes (no auth header needed from external apps like Flutter).
- Add rate limiting to scan API: throttle:60,1 (60 requests per minute per IP).
- Add route middleware: Route::middleware('throttle:60,1')->group(fn() => ...) for scan routes.
- Ensure all API responses use consistent format from ApiResponseTrait: {success, message, data}.

### Flutter compatibility note:
All APIs under /api/v1/ use token-based auth (Sanctum). Scan APIs are public. Document the following endpoints in a comment block in routes/api.php:
// PUBLIC:  GET|POST /api/v1/scan/{unique_code}
// PRIVATE: POST /api/v1/auth/login, /register, /logout
// PRIVATE: GET /api/v1/barcodes, POST /api/v1/barcodes/generate, etc.
```

---

## Quick Reference — All API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | /api/v1/auth/register | No | Register new user |
| POST | /api/v1/auth/login | No | Login |
| POST | /api/v1/auth/forgot-password | No | Send reset link |
| POST | /api/v1/auth/reset-password | No | Reset password |
| POST | /api/v1/auth/logout | Yes | Logout |
| GET | /api/v1/auth/me | Yes | Get profile |
| GET | /api/v1/dashboard/stats | Yes | Dashboard stats |
| GET | /api/v1/dashboard/recent-barcodes | Yes | Recent barcodes |
| GET | /api/v1/barcodes | Yes | List barcodes (DataTables) |
| POST | /api/v1/barcodes/generate | Yes | Generate barcode |
| GET | /api/v1/barcodes/check-duplicate | Yes | Check duplicate |
| GET | /api/v1/barcodes/{id} | Yes | View barcode |
| PUT | /api/v1/barcodes/{id} | Yes | Update barcode |
| DELETE | /api/v1/barcodes/{id} | Yes | Soft delete barcode |
| GET | /api/v1/products | Yes | List products |
| GET | /api/v1/scan/{code} | No | Scan (GET) |
| POST | /api/v1/scan | No | Scan (POST) |
| GET | /api/v1/scan/history | Yes | Scan history |

---

*All phases build sequentially. Always run `php artisan migrate` and `php artisan storage:link` as needed after each phase. Admin credentials: admin@barcode.com / Admin@123*
