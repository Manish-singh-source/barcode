# API Documentation

This document covers the JSON API routes defined in `routes/api.php`.

## Base URL

```text
/api
```

If your app runs locally on XAMPP, the full base URL is usually:

```text
http://127.0.0.1:8000/api
```

or:

```text
http://localhost/barcode/public/api
```

## Common Response Format

Successful responses use this wrapper:

```json
{
  "success": true,
  "message": "Success",
  "data": {}
}
```

Error responses use this wrapper:

```json
{
  "success": false,
  "message": "Something went wrong"
}
```

Laravel validation errors may return the framework default JSON structure, typically:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["The field name field is required."]
  }
}
```

## Authentication

Protected endpoints require a Sanctum bearer token:

```text
Authorization: Bearer YOUR_TOKEN
```

---

## Auth APIs

### 1) Register

`POST /api/auth/register`

Creates a new user and returns an access token.

#### Request payload

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### curl

```bash
curl -X POST "http://127.0.0.1:8000/api/auth/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### Success response

`201 Created`

```json
{
  "success": true,
  "message": "Registration successful.",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "last_login_at": "2026-06-20T10:00:00.000000Z"
    },
    "token": "1|laravel_sanctum_token_value"
  }
}
```

#### Error response

`422 Unprocessable Entity`

```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

### 2) Login

`POST /api/auth/login`

Authenticates a user and returns a Sanctum token.

#### Request payload

```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

#### curl

```bash
curl -X POST "http://127.0.0.1:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### Success response

```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "last_login_at": "2026-06-20T10:00:00.000000Z"
    },
    "token": "1|laravel_sanctum_token_value",
    "role": "user"
  }
}
```

#### Error response

`401 Unauthorized`

```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

### 3) Forgot Password

`POST /api/auth/forgot-password`

Sends a password reset link to the user email.

#### Request payload

```json
{
  "email": "john@example.com"
}
```

#### curl

```bash
curl -X POST "http://127.0.0.1:8000/api/auth/forgot-password" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com"
  }'
```

#### Success response

```json
{
  "success": true,
  "message": "Reset link sent successfully.",
  "data": null
}
```

#### Error response

`422 Unprocessable Entity`

```json
{
  "success": false,
  "message": "We can't find a user with that email address."
}
```

---

### 4) Reset Password

`POST /api/auth/reset-password`

Resets the password using the token sent by email.

#### Request payload

```json
{
  "token": "reset-token-from-email",
  "email": "john@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

#### curl

```bash
curl -X POST "http://127.0.0.1:8000/api/auth/reset-password" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "token": "reset-token-from-email",
    "email": "john@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

#### Success response

```json
{
  "success": true,
  "message": "Password reset successfully.",
  "data": null
}
```

#### Error response

`422 Unprocessable Entity`

```json
{
  "success": false,
  "message": "This password reset token is invalid."
}
```

---

### 5) Logout

`POST /api/auth/logout`

Deletes the current Sanctum access token.

#### curl

```bash
curl -X POST "http://127.0.0.1:8000/api/auth/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Success response

```json
{
  "success": true,
  "message": "Logged out successfully.",
  "data": null
}
```

---

### 6) Me

`GET /api/auth/me`

Returns the authenticated user profile.

#### curl

```bash
curl -X GET "http://127.0.0.1:8000/api/auth/me" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Success response

```json
{
  "success": true,
  "message": "Authenticated user retrieved successfully.",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "last_login_at": "2026-06-20T10:00:00.000000Z"
    }
  }
}
```

---

## Scan APIs

### 7) Scan Barcode

`POST /api/scan`

Scans a barcode by `unique_code` and logs the scan.

#### Request payload

```json
{
  "unique_code": "BCABC123456789"
}
```

#### curl

```bash
curl -X POST "http://127.0.0.1:8000/api/scan" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "unique_code": "BCABC123456789"
  }'
```

#### Success response

```json
{
  "success": true,
  "message": "Barcode scanned successfully.",
  "data": {
    "valid": true,
    "unique_code": "BCABC123456789",
    "barcode_format": "code128",
    "custom_label": "Sample Item",
    "barcode_image_url": "http://localhost/storage/barcodes/BCABC123456789.png",
    "product_name": "Sample Item",
    "product": {
      "id": null,
      "name": "Sample Item",
      "sku": null,
      "description": null,
      "price": null,
      "brand": null,
      "category": null,
      "unit": null,
      "stock_quantity": null,
      "raw": "Sample Item"
    },
    "scanned_at": "2026-06-20T10:00:00.000000Z"
  }
}
```

#### Error response

`404 Not Found`

```json
{
  "success": false,
  "message": "Invalid barcode. No product found."
}
```

---

### 8) Scan Barcode by GET

`GET /api/scan/{unique_code}`

Same scan behavior as the POST endpoint, but the barcode code is passed in the URL.

#### curl

```bash
curl -X GET "http://127.0.0.1:8000/api/scan/BCABC123456789" \
  -H "Accept: application/json"
```

#### Success response

Same as `POST /api/scan`.

#### Error response

Same as `POST /api/scan`.

---

### 9) Scan History

`GET /api/scan/history`

Returns scan history for the authenticated user.

#### Query params

```text
per_page=15
```

#### curl

```bash
curl -X GET "http://127.0.0.1:8000/api/scan/history?per_page=15" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Success response

```json
{
  "success": true,
  "message": "Scan history loaded successfully.",
  "data": {
    "data": [
      {
        "unique_code": "BCABC123456789",
        "scan_result": "success",
        "created_at": "2026-06-20T10:00:00.000000Z",
        "product_data_snapshot": {
          "unique_code": "BCABC123456789",
          "barcode_format": "code128",
          "custom_label": "Sample Item",
          "product": {
            "id": null,
            "name": "Sample Item",
            "sku": null,
            "description": null,
            "price": null,
            "brand": null,
            "category": null,
            "unit": null,
            "stock_quantity": null,
            "raw": "Sample Item"
          }
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
}
```

---

## Barcode APIs

### 10) List Barcodes

`GET /api/barcodes`

Returns a DataTables-style listing of barcodes.

#### Query params used by the controller

```text
draw
start
length
search[value]
order[0][column]
order[0][dir]
```

#### curl

```bash
curl -X GET "http://127.0.0.1:8000/api/barcodes?draw=1&start=0&length=10&search[value]=Sample&order[0][column]=5&order[0][dir]=desc" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Success response

```json
{
  "draw": 1,
  "recordsTotal": 25,
  "recordsFiltered": 3,
  "data": [
    {
      "id": 1,
      "row_number": 1,
      "unique_code": "BCABC123456789",
      "barcode_format": "code128",
      "custom_label": "Sample Item",
      "barcode_data": "Sample Item",
      "product_name": "Sample Item",
      "user_name": "John Doe",
      "barcode_image_url": "http://localhost/storage/barcodes/BCABC123456789.png",
      "created_at": "2026-06-20 10:00"
    }
  ]
}
```

---

### 11) Show Barcode

`GET /api/barcodes/{id}`

Returns full barcode details.

#### curl

```bash
curl -X GET "http://127.0.0.1:8000/api/barcodes/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Success response

```json
{
  "success": true,
  "message": "Barcode loaded successfully.",
  "data": {
    "id": 1,
    "unique_code": "BCABC123456789",
    "barcode_format": "code128",
    "barcode_data": "Sample Item",
    "custom_label": "Sample Item",
    "barcode_image_url": "http://localhost/storage/barcodes/BCABC123456789.png",
    "barcode_image_path": "barcodes/BCABC123456789.png",
    "barcode_svg": "<svg>...</svg>",
    "is_active": true,
    "product": {
      "id": null,
      "name": "Sample Item",
      "sku": null,
      "description": null,
      "price": null,
      "brand": null,
      "category": null,
      "unit": null,
      "stock_quantity": null,
      "raw": "Sample Item"
    },
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "created_at": "2026-06-20T10:00:00.000000Z",
    "updated_at": "2026-06-20T10:00:00.000000Z",
    "scan_count": 5,
    "last_scanned_at": "2026-06-20T10:15:00.000000Z"
  }
}
```

#### Error response

`404 Not Found`

```json
{
  "message": "No query results for model [App\\Models\\BarcodeGeneration] 1"
}
```

---

### 12) Check Duplicate Barcode Data

`GET /api/barcodes/check-duplicate?data=...`

Checks whether barcode data already exists.

#### Query params

```text
data=Sample Item
```

#### curl

```bash
curl -X GET "http://127.0.0.1:8000/api/barcodes/check-duplicate?data=Sample%20Item" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Success response

```json
{
  "success": true,
  "message": "Duplicate check completed.",
  "data": {
    "exists": true,
    "count": 2
  }
}
```

#### Error response

`422 Unprocessable Entity`

```json
{
  "message": "The data field is required.",
  "errors": {
    "data": ["The data field is required."]
  }
}
```

---

### 13) Generate Barcode

`POST /api/barcodes/generate`

Generates a new barcode image and stores it in public storage.

#### Request payload

```json
{
  "barcode_data": "Sample Item",
  "barcode_format": "code128",
  "custom_label": "Kitchen Sample"
}
```

#### Supported formats

```text
code128
qrcode
code39
ean13
```

#### curl

```bash
curl -X POST "http://127.0.0.1:8000/api/barcodes/generate" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "barcode_data": "Sample Item",
    "barcode_format": "code128",
    "custom_label": "Kitchen Sample"
  }'
```

#### Success response

`201 Created`

```json
{
  "success": true,
  "message": "Barcode generated successfully.",
  "data": {
    "unique_code": "BCABC123456789",
    "barcode_format": "code128",
    "barcode_image_base64": "iVBORw0KGgoAAAANSUhEUgAA...",
    "barcode_svg": "<svg>...</svg>",
    "barcode_image_url": "http://localhost/storage/barcodes/BCABC123456789.png",
    "custom_label": "Kitchen Sample",
    "created_at": "2026-06-20T10:00:00.000000Z"
  }
}
```

#### Error response

`422 Unprocessable Entity`

```json
{
  "message": "The selected barcode format is invalid.",
  "errors": {
    "barcode_format": ["The selected barcode format is invalid."]
  }
}
```

---

### 14) Update Barcode

`PUT /api/barcodes/{id}`

Updates the `custom_label` of an existing barcode.

#### Request payload

```json
{
  "custom_label": "Updated Label"
}
```

#### curl

```bash
curl -X PUT "http://127.0.0.1:8000/api/barcodes/1" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "custom_label": "Updated Label"
  }'
```

#### Success response

```json
{
  "success": true,
  "message": "Barcode updated successfully.",
  "data": {
    "id": 1,
    "unique_code": "BCABC123456789",
    "barcode_format": "code128",
    "custom_label": "Updated Label",
    "barcode_data": "Sample Item",
    "product_name": "Sample Item",
    "user_name": "John Doe",
    "barcode_image_url": "http://localhost/storage/barcodes/BCABC123456789.png",
    "created_at": "2026-06-20 10:00"
  }
}
```

---

### 15) Delete Barcode

`DELETE /api/barcodes/{id}`

Soft deletes a barcode.

#### curl

```bash
curl -X DELETE "http://127.0.0.1:8000/api/barcodes/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Success response

```json
{
  "success": true,
  "message": "Barcode deleted.",
  "data": null
}
```

---

## Dashboard APIs

These endpoints require an authenticated admin user.

### 16) Dashboard Stats

`GET /api/dashboard/stats`

#### curl

```bash
curl -X GET "http://127.0.0.1:8000/api/dashboard/stats" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

#### Success response

```json
{
  "success": true,
  "message": "Dashboard stats loaded.",
  "data": {
    "total_barcodes": 25,
    "scans_today": 8,
    "unique_barcode_data": 20,
    "active_users": 5
  }
}
```

#### Error response

`403 Forbidden`

```json
{
  "success": false,
  "message": "Forbidden"
}
```

---

### 17) Recent Barcodes

`GET /api/dashboard/recent-barcodes`

Returns the latest 10 barcodes with pagination metadata.

#### curl

```bash
curl -X GET "http://127.0.0.1:8000/api/dashboard/recent-barcodes" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

#### Success response

```json
{
  "success": true,
  "message": "Recent barcodes loaded.",
  "data": {
    "data": [
      {
        "id": 1,
        "unique_code": "BCABC123456789",
        "barcode_format": "code128",
        "custom_label": "Kitchen Sample",
        "product_name": "Sample Item",
        "barcode_data": "Sample Item",
        "created_at": "2026-06-20 10:00:00",
        "user": {
          "id": 1,
          "name": "John Doe"
        },
        "product": {
          "id": null,
          "name": "Sample Item",
          "sku": null,
          "description": null,
          "price": null,
          "brand": null,
          "category": null,
          "unit": null,
          "stock_quantity": null,
          "raw": "Sample Item"
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 10,
      "total": 1
    }
  }
}
```

#### Error response

`403 Forbidden`

```json
{
  "success": false,
  "message": "Forbidden"
}
```

---

## Notes

1. All protected routes require a valid Sanctum token.
2. `GET /api/scan/{unique_code}` does not require authentication, but it still logs the scan.
3. `GET /api/dashboard/*` endpoints are restricted to users whose role is `admin`.
4. `PUT /api/barcodes/{id}` only updates `custom_label`.
5. `POST /api/barcodes/generate` returns both base64 PNG data and SVG markup.

