````markdown
# ClickPesa Laravel Integration

A simple Laravel package to integrate with the [ClickPesa](https://clickpesa.com) payment platform.

Supports:
- ✅ Token Authorization
- ✅ USSD Checkout
- ✅ Card Payment
- ✅ Payment Status Query
- ✅ Wallet Balance Retrieval

---

## 📦 Installation

```bash
composer require emilkitua/clickpesa
````

If you're installing from a local path or custom repository, configure it in your project's `composer.json`:

```json
"repositories": [
  {
    "type": "path",
    "url": "./packages/EmilKitua/ClickPesa"
  }
]
```

Then run:

```bash
composer require emilkitua/clickpesa:dev-main
```

---

## ⚙️ Configuration

Publish the config file:

```bash
php artisan vendor:publish --provider="EmilKitua\ClickPesa\ClickPesaServiceProvider"
```

Then add the following to your `.env`:

```env
CLICKPESA_CLIENT_ID=your-client-id
CLICKPESA_CLIENT_SECRET=your-client-secret
CLICKPESA_BASE_URL=https://api.clickpesa.com
```

---

## 🧠 Usage

Inject and use the `ClickPesa` service in your controller, job, or anywhere:

```php
use EmilKitua\ClickPesa\ClickPesa;

public function pay(ClickPesa $clickPesa)
{
    // Initiate USSD Checkout
    $response = $clickPesa->initiateUSSD([
        'phone' => '255712345678',
        'amount' => 1000,
        'currency' => 'TZS',
        'reference' => 'ORDER123',
        'callback_url' => route('clickpesa.callback'),
    ]);

    return response()->json($response);
}
```

Other available methods:

```php

$clickPesa->initiateUSSD([...]);

$clickPesa->initiateCardPayment([...]);

$clickPesa->queryStatus($transactionId);

$clickPesa->getBalance();
```

🔔 Webhook Handling
ClickPesa supports callbacks for transaction updates.

Define a route:

```php
Route::post('/webhooks/clickpesa', [ClickPesaWebhookController::class, 'handle']);
```

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClickPesaWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();

        Log::info('ClickPesa Webhook:', $data);

        // Handle based on status, transaction_id, etc.
        // Update your local DB accordingly.

        return response()->json(['status' => 'received'], 200);
    }
}
```

🧪 Testing
You can mock responses for testing without hitting the real API.

Example PHPUnit Mock:
```php
use Illuminate\Support\Facades\Http;

public function testUssdCheckout()
{
    Http::fake([
        'https://api.clickpesa.com/oauth/token' => Http::response([
            'access_token' => 'test_token',
            'expires_in' => 3600
        ]),
        'https://api.clickpesa.com/ussd-checkout' => Http::response([
            'transaction_id' => 'TX123456',
            'status' => 'pending',
        ]),
    ]);

    $response = app(ClickPesa::class)->initiateUSSD([
        'phone' => '255712345678',
        'amount' => 500,
        'currency' => 'TZS',
        'reference' => 'TESTREF',
        'callback_url' => 'https://yourdomain.com/callback'
    ]);

    $this->assertEquals('TX123456', $response['transaction_id']);
}
```

---
🌐 Real ClickPesa API Endpoints
| Feature              | Endpoint                     | Method |
| -------------------- | ---------------------------- | ------ |
| Token Authentication | `/oauth/token`               | POST   |
| USSD Checkout        | `/ussd-checkout`             | POST   |
| Card Payment         | `/card-checkout`             | POST   |
| Payment Status       | `/payments/{transaction_id}` | GET    |
| Wallet Balance       | `/wallet/balance`            | GET    |

Refer to ClickPesa API docs for full request/response formats.

---

## Database

This package creates a table named `clickpesa_payments` to store payment transactions.

### Table Structure: `clickpesa_payments`

| Column           | Type             | Description                                  |
|------------------|------------------|----------------------------------------------|
| id               | bigint (PK)      | Auto-increment primary key                    |
| reference_id     | string, unique   | Internal reference ID for the payment        |
| external_id      | string, nullable | ClickPesa transaction ID (from API response)|
| payment_method   | enum ('ussd','card') | Payment method used                         |
| phone_number     | string, nullable | Phone number used for USSD or card payments  |
| card_number_masked| string, nullable | Masked card number (only for card payments)  |
| amount           | decimal(15,2)    | Payment amount                                |
| currency         | string (default 'TZS') | Currency code                              |
| status           | enum            | Current status: pending, processing, successful, failed, cancelled |
| status_detail    | string, nullable | Additional status info or error messages     |
| request_payload  | json, nullable   | JSON payload sent to ClickPesa API           |
| response_payload | json, nullable   | JSON response from ClickPesa API              |
| paid_at          | timestamp, nullable | When payment was completed                  |
| created_at       | timestamp        | When request was created                       |
| updated_at       | timestamp        | When record was last updated                   |

The table helps track payment requests, responses, and status updates.

---

## Usage

When initiating a USSD or card payment, you can store the payment details automatically via the provided `TransactionService`:

```php
use EmilKitua\ClickPesa\Models\ClickPesaPayment;
use EmilKitua\ClickPesa\Services\TransactionService;

// Example data for initiating payment
$data = [
    'reference_id' => 'ORDER12345',
    'payment_method' => 'ussd', // or 'card'
    'phone_number' => '255712345678',
    'amount' => 10000,
    'currency' => 'TZS',
    'request_payload' => $requestPayloadJson,  // API request data as JSON string or array
];

// Store payment request in DB
TransactionService::createPayment($data);
php
Copy
Edit

---
## 📁 Folder Structure

```
ClickPesa/
├── src/
│   ├── ClickPesa.php
│   ├── ClickPesaServiceProvider.php
│   ├── Config/clickpesa.php
│   └── Http/Clients/ClickPesaClient.php
├── composer.json
└── README.md
```

---

## ✅ To Do
* [✅] Token Authentication
* [✅] USSD/Card Initiation
* [✅] Status Query
* [✅] Balance Retrieval
* [✅] Webhook suppory

---

## 📝 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

```

