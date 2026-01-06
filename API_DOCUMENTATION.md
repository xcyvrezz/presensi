# Absensi MIFARE API Documentation v2.0

## Base URL
```
http://localhost:8000/api/v2
```

## Authentication

All API endpoints (except token generation) require authentication using Bearer tokens.

### Generate API Token

**Endpoint:** `POST /auth/token`

**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "password",
  "token_name": "My Application",
  "abilities": ["*"],
  "expires_in_days": 30
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "API token generated successfully.",
  "data": {
    "token": "abcd1234efgh5678...",
    "token_id": 1,
    "name": "My Application",
    "expires_at": "2025-01-13T10:00:00.000000Z"
  }
}
```

**Usage:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  http://localhost:8000/api/v2/students
```

---

## Authentication Management

### Verify Token
**Endpoint:** `GET /auth/verify`

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "message": "Token is valid.",
  "data": {
    "user": {
      "id": 1,
      "name": "Administrator",
      "email": "admin@example.com",
      "role": "admin"
    },
    "token_name": "My Application",
    "abilities": ["*"]
  }
}
```

### List User Tokens
**Endpoint:** `GET /auth/tokens`

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "My Application",
      "abilities": ["*"],
      "last_used_at": "2025-12-13T10:30:00.000000Z",
      "usage_count": 45,
      "is_active": true,
      "expires_at": "2025-01-13T10:00:00.000000Z",
      "created_at": "2025-12-13T09:00:00.000000Z"
    }
  ]
}
```

### Revoke Token
**Endpoint:** `POST /auth/revoke`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "token_id": 1
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "API token revoked successfully."
}
```

---

## Students

### Get All Students
**Endpoint:** `GET /students`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `class_id` (optional): Filter by class ID
- `department_id` (optional): Filter by department ID
- `is_active` (optional): Filter by active status (true/false)
- `search` (optional): Search by name or NIS
- `limit` (optional): Limit results (1-100, default: 50)

**Example Request:**
```bash
GET /api/v2/students?class_id=5&limit=10
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nis": "2024001",
      "full_name": "John Doe",
      "email": "john@example.com",
      "phone": "08123456789",
      "gender": "L",
      "date_of_birth": "2007-05-15",
      "address": "Jl. Example No. 123",
      "class": {
        "id": 5,
        "name": "XII RPL 1",
        "department": {
          "id": 2,
          "name": "Rekayasa Perangkat Lunak",
          "code": "RPL"
        }
      },
      "is_active": true,
      "enrollment_year": 2024
    }
  ],
  "count": 1
}
```

### Get Student by ID
**Endpoint:** `GET /students/{id}`

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nis": "2024001",
    "nisn": "9876543210",
    "full_name": "John Doe",
    "email": "john@example.com",
    "phone": "08123456789",
    "gender": "L",
    "date_of_birth": "2007-05-15",
    "place_of_birth": "Jakarta",
    "address": "Jl. Example No. 123",
    "parent_name": "Jane Doe",
    "parent_phone": "08198765432",
    "class": {
      "id": 5,
      "name": "XII RPL 1",
      "department": {
        "id": 2,
        "name": "Rekayasa Perangkat Lunak",
        "code": "RPL"
      }
    },
    "is_active": true,
    "enrollment_year": 2024,
    "nfc_uid": "ABC123DEF456"
  }
}
```

### Get Student by NFC UID
**Endpoint:** `POST /students/nfc`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "nfc_uid": "ABC123DEF456"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nis": "2024001",
    "full_name": "John Doe",
    "class": {
      "id": 5,
      "name": "XII RPL 1"
    },
    "nfc_uid": "ABC123DEF456"
  }
}
```

---

## Attendance

### Get Attendance Records
**Endpoint:** `GET /attendance`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `student_id` (optional): Filter by student ID
- `class_id` (optional): Filter by class ID
- `start_date` (optional): Start date (YYYY-MM-DD)
- `end_date` (optional): End date (YYYY-MM-DD)
- `status` (optional): Filter by status (hadir, terlambat, izin, sakit, alpha, dispensasi)
- `limit` (optional): Limit results (1-100, default: 50)

**Example Request:**
```bash
GET /api/v2/attendance?student_id=1&start_date=2025-12-01&end_date=2025-12-13
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "student": {
        "id": 1,
        "name": "John Doe",
        "nis": "2024001"
      },
      "class": {
        "id": 5,
        "name": "XII RPL 1"
      },
      "date": "2025-12-13",
      "check_in_time": "2025-12-13T07:15:00.000000Z",
      "check_out_time": "2025-12-13T15:30:00.000000Z",
      "status": "terlambat",
      "late_minutes": 15,
      "percentage": 75,
      "check_in_method": "nfc",
      "check_out_method": "nfc",
      "notes": null
    }
  ],
  "count": 1
}
```

### Record Check-In
**Endpoint:** `POST /attendance/check-in`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "student_id": 1,
  "nfc_uid": "ABC123DEF456",
  "latitude": -6.200000,
  "longitude": 106.816666
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Check-in successful.",
  "data": {
    "id": 1,
    "student_name": "John Doe",
    "status": "terlambat",
    "check_in_time": "2025-12-13T07:15:00.000000Z",
    "is_late": true,
    "late_minutes": 15
  }
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Student already checked in today.",
  "data": null
}
```

### Record Check-Out
**Endpoint:** `POST /attendance/check-out`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "student_id": 1
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Check-out successful.",
  "data": {
    "id": 1,
    "student_name": "John Doe",
    "check_in_time": "2025-12-13T07:15:00.000000Z",
    "check_out_time": "2025-12-13T15:30:00.000000Z"
  }
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "No check-in record found or already checked out.",
  "data": null
}
```

### Get Attendance Statistics
**Endpoint:** `GET /attendance/statistics`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters (Required):**
- `start_date`: Start date (YYYY-MM-DD)
- `end_date`: End date (YYYY-MM-DD)

**Optional Parameters:**
- `student_id`: Filter by student ID
- `class_id`: Filter by class ID

**Example Request:**
```bash
GET /api/v2/attendance/statistics?start_date=2025-12-01&end_date=2025-12-13&student_id=1
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "total_records": 10,
    "hadir": 7,
    "terlambat": 2,
    "izin": 0,
    "sakit": 1,
    "alpha": 0,
    "dispensasi": 0,
    "total_present": 9,
    "average_late_minutes": 12.5,
    "attendance_percentage": 90.0
  }
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthorized. API token required."
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Student not found with provided NFC UID.",
  "data": null
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "Internal server error."
}
```

---

## Rate Limiting

Each API token has a rate limit of **60 requests per minute** by default. This can be configured per token when generating it.

---

## Best Practices

1. **Always use HTTPS in production**
2. **Store API tokens securely** - Never commit tokens to version control
3. **Set appropriate token expiration** - Use `expires_in_days` when generating tokens
4. **Revoke unused tokens** - Clean up old/unused API tokens regularly
5. **Use specific abilities** - Instead of `["*"]`, specify exact permissions needed
6. **Handle rate limits** - Implement exponential backoff for rate limit errors
7. **Log API usage** - Track token usage via the `/auth/tokens` endpoint

---

## Example Integration (JavaScript)

```javascript
const API_BASE_URL = 'http://localhost:8000/api/v2';
const API_TOKEN = 'your_token_here';

// Get students
async function getStudents(classId = null) {
  const url = new URL(`${API_BASE_URL}/students`);
  if (classId) url.searchParams.append('class_id', classId);

  const response = await fetch(url, {
    headers: {
      'Authorization': `Bearer ${API_TOKEN}`,
      'Accept': 'application/json'
    }
  });

  return await response.json();
}

// Record check-in
async function checkIn(studentId, nfcUid) {
  const response = await fetch(`${API_BASE_URL}/attendance/check-in`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${API_TOKEN}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      student_id: studentId,
      nfc_uid: nfcUid
    })
  });

  return await response.json();
}
```

---

## Support

For issues or questions, please contact the system administrator or check the GitHub repository.
