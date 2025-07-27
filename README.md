# News Manager API

A RESTful API for managing news and categories with hierarchical relationships.

## Features

- CRUD operations for news and categories
- Hierarchical category structure (categories can have multiple parents and children)
- Many-to-many relationship between news and categories
- Search and filter capabilities
- Authentication and authorization

## Installation

1. Install the module using Composer:

   ```bash
   composer require news/manger
   ```

2. Enable the module:

   ```bash
   bin/magento module:enable News_Manger
   ```

3. Run setup upgrade:

   ```bash
   bin/magento setup:upgrade
   ```

4. Deploy static content (if in production mode):
   ```bash
   bin/magento setup:static-content:deploy -f
   ```

## API Endpoints

### Categories

#### Get All Categories

```
GET /rest/V1/news/categories
```

#### Get Category by ID

```
GET /rest/V1/news/categories/:categoryId
```

#### Create Category

```
POST /rest/V1/news/categories
```

Request body:

```json
{
  "category": {
    "category_name": "Technology",
    "category_description": "News about technology",
    "status": 1,
    "parent_ids": [1, 2],
    "child_ids": [3, 4]
  }
}
```

#### Update Category

```
PUT /rest/V1/news/categories/:categoryId
```

#### Delete Category

```
DELETE /rest/V1/news/categories/:categoryId
```

#### Get Child Categories

```
GET /rest/V1/news/categories/:categoryId/children
```

#### Get Parent Categories

```
GET /rest/V1/news/categories/:categoryId/parents
```

#### Get News in Category

```
GET /rest/V1/news/categories/:categoryId/news
```

### News

#### Get All News

```
GET /rest/V1/news
```

#### Get News by ID

```
GET /rest/V1/news/:newsId
```

#### Create News

```
POST /rest/V1/news
```

Request body:

```json
{
  "news": {
    "news_title": "Breaking News",
    "news_content": "This is a breaking news article.",
    "news_status": 1,
    "category_ids": [1, 2]
  }
}
```

#### Update News

```
PUT /rest/V1/news/:newsId
```

#### Delete News

```
DELETE /rest/V1/news/:newsId
```

#### Get Categories for News

```
GET /rest/V1/news/:newsId/categories
```

#### Set Categories for News

```
POST /rest/V1/news/:newsId/categories
```

Request body:

```json
{
  "categoryIds": [1, 2, 3]
}
```

#### Remove Category from News

```
DELETE /rest/V1/news/:newsId/categories/:categoryId
```

## Authentication

All write operations (POST, PUT, DELETE) require admin authentication.

### Get Admin Token

```
POST /rest/V1/integration/admin/token
```

Request body:

```json
{
  "username": "admin_username",
  "password": "admin_password"
}
```

Include the received token in the `Authorization` header for authenticated requests:

```
Authorization: Bearer <token>
```

## Error Handling

Standard HTTP status codes are used to indicate success or failure:

- `200 OK` - Request successful
- `201 Created` - Resource created successfully
- `400 Bad Request` - Invalid request data
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `500 Internal Server Error` - Server error

## Examples

### Get all categories

```bash
curl -X GET "http://magento.local/rest/V1/news/categories" \
     -H "Content-Type: application/json"
```

### Create a new category

```bash
curl -X POST "http://magento.local/rest/V1/news/categories" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer <admin_token>" \
     -d '{"category": {"category_name":"Technology","category_description":"Tech news","status":1}}'
```

### Get news by ID

```bash
curl -X GET "http://magento.local/rest/V1/news/1" \
     -H "Content-Type: application/json"
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
