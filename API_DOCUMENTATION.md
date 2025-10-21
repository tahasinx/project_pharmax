# PharmaCare Modern API Documentation

## Overview
PharmaCare Modern provides a comprehensive REST API for managing pharmacy operations including medicines, customers, invoices, stock management, and more.

## Base URL
```
https://your-domain.com/api
```

## Authentication
All API endpoints require authentication. Include the authentication token in the request headers:

```
Authorization: Bearer {your-token}
```

## Rate Limiting
- **API Endpoints**: 60 requests per minute
- **POS Endpoints**: 120 requests per minute
- **Default**: 100 requests per minute

## Response Format
All API responses follow a consistent JSON format:

### Success Response
```json
{
  "success": true,
  "data": {...},
  "message": "Operation completed successfully"
}
```

### Error Response
```json
{
  "success": false,
  "error": "Error message",
  "code": 400
}
```

## Endpoints

### Medicines

#### Get All Medicines
```http
GET /api/medicines
```

**Query Parameters:**
- `page` (integer): Page number for pagination
- `per_page` (integer): Number of items per page (default: 15)
- `search` (string): Search term for medicine name
- `category_id` (integer): Filter by category
- `manufacturer_id` (integer): Filter by manufacturer
- `status` (boolean): Filter by status

**Response:**
```json
{
  "success": true,
  "data": {
    "medicines": [
      {
        "id": 1,
        "product_id": "MED001",
        "name": "Paracetamol",
        "generic_name": "Acetaminophen",
        "strength": "500mg",
        "box_size": 10,
        "price": 25.50,
        "manufacturer_price": 20.00,
        "category": {
          "id": 1,
          "name": "Pain Relief"
        },
        "manufacturer": {
          "id": 1,
          "name": "Pfizer"
        },
        "total_stock": 150,
        "is_low_stock": false,
        "status": true,
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 15,
      "total": 75
    }
  }
}
```

#### Create Medicine
```http
POST /api/medicines
```

**Request Body:**
```json
{
  "name": "Paracetamol",
  "category_id": 1,
  "manufacturer_id": 1,
  "generic_name": "Acetaminophen",
  "strength": "500mg",
  "box_size": 10,
  "price": 25.50,
  "manufacturer_price": 20.00,
  "unit": "tablet",
  "details": "Pain relief medicine",
  "status": true
}
```

#### Update Medicine
```http
PUT /api/medicines/{id}
```

#### Delete Medicine
```http
DELETE /api/medicines/{id}
```

#### Search Medicines
```http
GET /api/medicines/search?q={search_term}
```

### Customers

#### Get All Customers
```http
GET /api/customers
```

#### Create Customer
```http
POST /api/customers
```

**Request Body:**
```json
{
  "name": "John Doe",
  "mobile": "1234567890",
  "email": "john@example.com",
  "address": "123 Main St",
  "city": "New York",
  "state": "NY",
  "zip": "10001",
  "country": "USA"
}
```

#### Search Customers
```http
GET /api/customers/search?q={search_term}
```

### Invoices

#### Get All Invoices
```http
GET /api/invoices
```

#### Create Invoice
```http
POST /api/invoices
```

**Request Body:**
```json
{
  "customer_id": 1,
  "date": "2025-01-01",
  "payment_type": "cash",
  "paid_amount": 50.00,
  "due_amount": 0.00,
  "total_amount": 50.00,
  "total_tax": 5.00,
  "total_discount": 0.00,
  "items": [
    {
      "medicine_id": 1,
      "quantity": 2,
      "rate": 25.00,
      "discount": 0,
      "batch_id": "BATCH001"
    }
  ],
  "send_sms": false,
  "send_email": false
}
```

#### Get Invoice Details
```http
GET /api/invoices/{id}
```

#### Print Invoice
```http
GET /api/invoices/{id}/print
```

### Stock Management

#### Get All Stock
```http
GET /api/stocks
```

#### Create Stock Entry
```http
POST /api/stocks
```

**Request Body:**
```json
{
  "medicine_id": 1,
  "batch_number": "BATCH001",
  "expiry_date": "2025-12-31",
  "quantity": 100,
  "min_stock_level": 10,
  "max_stock_level": 500,
  "purchase_price": 20.00,
  "selling_price": 25.00,
  "supplier": "Test Supplier",
  "notes": "Stock entry notes"
}
```

#### Get Stock Reports
```http
GET /api/stocks/reports
```

**Response:**
```json
{
  "success": true,
  "data": {
    "low_stock_count": 5,
    "expired_count": 2,
    "expiring_soon_count": 8,
    "total_value": 50000.00,
    "total_medicines": 150,
    "stock_by_category": [
      {
        "category_name": "Pain Relief",
        "total_value": 15000.00,
        "medicine_count": 25
      }
    ]
  }
}
```

#### Get Stock Alerts
```http
GET /api/stocks/alerts
```

### External API Integration

#### MedEx Search
```http
GET /api/medex/search?q={search_term}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "12345",
      "name": "Paracetamol 500mg",
      "generic_name": "Acetaminophen",
      "strength": "500mg",
      "manufacturer": "Pfizer",
      "price": 25.50
    }
  ]
}
```

#### MedEx Product Details
```http
GET /api/medex/product/{id}
```

#### Store External Medicine
```http
POST /api/medex/store
```

**Request Body:**
```json
{
  "medex_id": "12345",
  "medex_name": "Paracetamol 500mg",
  "generic_name": "Acetaminophen",
  "strength": "500mg",
  "manufacturer": "Pfizer",
  "price": 25.50
}
```

### Reports

#### Sales Report
```http
GET /api/reports/sales
```

**Query Parameters:**
- `start_date` (date): Start date for report
- `end_date` (date): End date for report
- `medicine_id` (integer): Filter by medicine
- `customer_id` (integer): Filter by customer

#### Purchase Report
```http
GET /api/reports/purchases
```

#### Profit/Loss Report
```http
GET /api/reports/profit-loss
```

#### Customer Dues Report
```http
GET /api/reports/customer-dues
```

### Settings

#### Get Settings
```http
GET /api/settings
```

#### Update Settings
```http
PUT /api/settings
```

**Request Body:**
```json
{
  "company_name": "PharmaCare",
  "company_address": "123 Main St",
  "company_phone": "1234567890",
  "company_email": "info@pharmacare.com",
  "currency_symbol": "$",
  "currency_position": "before",
  "email_provider": "smtp",
  "smtp_host": "smtp.example.com",
  "smtp_port": 587,
  "smtp_username": "user@example.com",
  "smtp_password": "password",
  "sms_provider": "twilio",
  "twilio_sid": "your_sid",
  "twilio_token": "your_token",
  "twilio_from": "+1234567890"
}
```

#### Test Email Configuration
```http
POST /api/settings/test-email
```

#### Test SMS Configuration
```http
POST /api/settings/test-sms
```

### Backup Management

#### Create Backup
```http
POST /api/backup
```

#### Download Backup
```http
GET /api/backup/{backupName}/download
```

#### Restore Backup
```http
POST /api/backup/restore
```

**Request Body:**
```
FormData with backup file (ZIP format)
```

## Error Codes

| Code | Description |
|------|-------------|
| 400 | Bad Request - Invalid input data |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation errors |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error - Server error |

## Pagination

All list endpoints support pagination with the following parameters:
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

Response includes pagination metadata:
```json
{
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75,
    "from": 1,
    "to": 15
  }
}
```

## Filtering and Searching

Most endpoints support filtering and searching:
- `search`: Text search across relevant fields
- `status`: Filter by active/inactive status
- `date_from`/`date_to`: Date range filtering
- Specific field filters (e.g., `category_id`, `manufacturer_id`)

## Webhooks

PharmaCare supports webhooks for real-time notifications:

### Available Events
- `medicine.created`
- `medicine.updated`
- `medicine.deleted`
- `invoice.created`
- `invoice.paid`
- `stock.low`
- `stock.expired`

### Webhook Payload
```json
{
  "event": "medicine.created",
  "data": {
    "id": 1,
    "name": "Paracetamol",
    "price": 25.50
  },
  "timestamp": "2025-01-01T00:00:00Z"
}
```

## SDKs and Libraries

### JavaScript/Node.js
```bash
npm install pharmacare-api-client
```

```javascript
const PharmaCare = require('pharmacare-api-client');

const client = new PharmaCare({
  apiKey: 'your-api-key',
  baseUrl: 'https://your-domain.com/api'
});

// Get all medicines
const medicines = await client.medicines.list();
```

### PHP
```bash
composer require pharmacare/api-client
```

```php
use PharmaCare\ApiClient;

$client = new ApiClient([
    'api_key' => 'your-api-key',
    'base_url' => 'https://your-domain.com/api'
]);

// Get all medicines
$medicines = $client->medicines()->list();
```

## Support

For API support and questions:
- Email: api-support@pharmacare.com
- Documentation: https://docs.pharmacare.com
- Status Page: https://status.pharmacare.com
