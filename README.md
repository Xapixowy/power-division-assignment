# Power Division Assignment

REST API for charging and topping up a user's account.

## Setup

1. Clone the repository

```bash
   git clone https://github.com/Xapixowy/power-division-assignment.git
```

2. Copy the environment file

```bash
   cp .env.example .env
```

3. Fill in `.env`:
    - `APP_KEY` — generate after starting containers (step 5)
    - `DB_PASSWORD` — any password, e.g. `secret`
    - `API_KEY` — any key, e.g. `my-secret-key`
4. Install dependencies

```bash
   docker run --rm -v $(pwd):/app composer install
```

5. Start containers

```bash
   ./vendor/bin/sail up -d
```

6. Generate application key

```bash
   ./vendor/bin/sail artisan key:generate
```

7. Run migrations and seeder

```bash
   ./vendor/bin/sail artisan migrate --seed
```

App available at http://localhost.

Seeder creates a test user:

- **Email:** test@test.pl
- **User ID:** 1
- **Account balance:** 20000 grosze (200.00 PLN)

## Endpoint

```
POST /api/v1/users/{user_id}/balance
```

**Headers**

| Header         | Value              |
|----------------|--------------------|
| `X-API-KEY`    | value from `.env`  |
| `Content-Type` | `application/json` |

**Body**

```json
{
    "amount": 500,
    "type": "credit"
}
```

- `amount` — amount in grosze (int, min: 1)
- `type` — `credit` (top-up) or `debit` (charge)

**Response 200**

```json
{
    "data": {
        "user_id": 1,
        "balance": 20500,
        "last_transaction_at": "2026-06-21T22:00:00.000000Z"
    }
}
```

**Errors**

| Status | Description                                                           |
|--------|-----------------------------------------------------------------------|
| `401`  | Missing or invalid API key                                            |
| `404`  | User not found                                                        |
| `422`  | Validation error (invalid type, insufficient balance, missing fields) |

**Run tests**

```bash
./vendor/bin/sail artisan test
```

## Technical decisions

- **PostgreSQL as source of truth** — balances and transactions stored in Postgres. `SELECT FOR UPDATE` (
  `lockForUpdate()`) eliminates race conditions on concurrent requests.
- **Redis as cache store** — configured and ready for use (rate limiting, cache).
- **Static API key authentication** — `X-API-KEY` header. Deliberate decision — the task is about payment logic, not a
  user authorization system.
- **No facades** — dependency injection via constructor (`DatabaseManager`).
- **`sleep(5)`** — simulates external payment gateway processing time.
- **Amount in grosze** — the entire stack operates on grosze (int). Eliminates floating-point arithmetic issues.
- **Soft deletes** — users and accounts are never permanently deleted. Transactions are immutable (append-only).
