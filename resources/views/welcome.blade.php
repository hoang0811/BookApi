<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookstore API Documentation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }

        h1 {
            text-align: center;
            margin-bottom: 40px;
        }

        .section {
            margin-bottom: 40px;
        }

        .section h2 {
            background: #007BFF;
            color: #fff;
            padding: 10px;
            border-radius: 5px;
        }

        .endpoint {
            margin-bottom: 15px;
        }

        .endpoint h3 {
            cursor: pointer;
            margin: 10px 0;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .endpoint h3:hover {
            background: #e8e8e8;
        }

        .details {
            display: none;
            margin-left: 20px;
            padding-left: 20px;
        }

        pre {
            background: #272822;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 8px;
            font-family: Consolas, "Courier New", monospace;
            overflow-x: auto;
        }

        code {
            background: #f4f4f4;
            padding: 2px 4px;
            border-radius: 4px;
            font-family: Consolas, "Courier New", monospace;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Bookstore API Documentation</h1>
        <p><strong>Base URL:</strong> <code>https://backend.vothanhhoang.online/api</code></p>

        <!-- Authentication Section -->
        <div class="section">
            <h2>Authentication</h2>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">1. Register</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>POST /auth/register</code></p>
                    <p><strong>Description:</strong> Register a new user.</p>
                    <h4>Request Body</h4>
                    <pre>{
                        "name": "string",
                        "email": "string",
                        "password": "string",
                        "c_password": "string"
                    }</pre>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>...Response Example...</pre>
                    <p class="error">Validation Error (422):</p>
                    <pre>...Error Example...</pre>
                </div>
            </div>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">2. Login</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>POST /auth/login</code></p>
                    <p><strong>Description:</strong> Authenticate user and return a token.</p>
                    <h4>Request Body</h4>
                    <pre>{
  "email": "string",
  "password": "string"
}</pre>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>...Response Example...</pre>
                    <p class="error">Unauthorized (401):</p>
                    <pre>...Error Example...</pre>
                </div>
            </div>
        </div>
        <div class="section">
            <h2>Address Management</h2>
    
            <!-- List Addresses -->
            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">1. List Addresses</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>GET /addresses</code></p>
                    <p><strong>Description:</strong> Retrieve a list of all addresses for the authenticated user.</p>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>{
      "success": true,
      "data": [
        {
          "id": 1,
          "name": "John Doe",
          "phone": "123456789",
          "email": "john@example.com",
          "district_id": 2,
          "ward_id": 1,
          "province_id": 3,
          "street": "123 Main Street",
          "address_type": "Home",
          "is_default": true
        }
      ]
    }</pre>
                </div>
            </div>
    
            <!-- Create Address -->
            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">2. Create Address</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>POST /addresses</code></p>
                    <p><strong>Description:</strong> Create a new address for the authenticated user.</p>
                    <h4>Request</h4>
                    <pre>{
      "name": "John Doe",
      "phone": "123456789",
      "email": "john@example.com",
      "district_id": 2,
      "ward_id": 1,
      "province_id": 3,
      "street": "123 Main Street",
      "address_type": "Home",
      "is_default": true
    }</pre>
                    <h4>Response</h4>
                    <p class="success">Success (201):</p>
                    <pre>{
      "success": true,
      "data": {
        "id": 1,
        "name": "John Doe",
        "phone": "123456789",
        "email": "john@example.com",
        "district_id": 2,
        "ward_id": 1,
        "province_id": 3,
        "street": "123 Main Street",
        "address_type": "Home",
        "is_default": true
      }
    }</pre>
                </div>
            </div>
    
            <!-- Show Address -->
            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">3. Show Address</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>GET /addresses/{id}</code></p>
                    <p><strong>Description:</strong> Retrieve the details of a specific address.</p>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>{
      "success": true,
      "data": {
        "id": 1,
        "name": "John Doe",
        "phone": "123456789",
        "email": "john@example.com",
        "district_id": 2,
        "ward_id": 1,
        "province_id": 3,
        "street": "123 Main Street",
        "address_type": "Home",
        "is_default": true
      }
    }</pre>
                </div>
            </div>
    
            <!-- Update Address -->
            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">4. Update Address</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>PUT /addresses/{id}</code></p>
                    <p><strong>Description:</strong> Update a specific address.</p>
                    <h4>Request</h4>
                    <pre>{
      "phone": "987654321"
    }</pre>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>{
      "success": true,
      "data": {
        "id": 1,
        "name": "John Doe",
        "phone": "987654321",
        "email": "john@example.com",
        "district_id": 2,
        "ward_id": 1,
        "province_id": 3,
        "street": "123 Main Street",
        "address_type": "Home",
        "is_default": true
      }
    }</pre>
                </div>
            </div>
    
            <!-- Delete Address -->
            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">5. Delete Address</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>DELETE /addresses/{id}</code></p>
                    <p><strong>Description:</strong> Delete a specific address.</p>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>{
      "message": "Địa chỉ đã được xóa thành công"
    }</pre>
                </div>
            </div>
        </div>
    
<div class="section">
    <h2>Cart Management</h2>

    <!-- 1. View Cart -->
    <div class="endpoint">
        <h3 onclick="toggleDetails(this)">1. View Cart</h3>
        <div class="details">
            <p><strong>Endpoint:</strong> <code>GET /cart</code></p>
            <p><strong>Description:</strong> Retrieve the current user's cart details, including items, discount, shipping fee, and total price.</p>
            <h4>Response</h4>
            <p class="success">Success (200):</p>
            <pre>
{
    "cart": {...},
    "discount": 10000,
    "shipping": 30000,
    "total": 130000
}
            </pre>
            <p class="error">Cart Not Found (404):</p>
            <pre>{"message": "Cart not found."}</pre>
        </div>
    </div>

    <!-- 2. Add Item to Cart -->
    <div class="endpoint">
        <h3 onclick="toggleDetails(this)">2. Add Item to Cart</h3>
        <div class="details">
            <p><strong>Endpoint:</strong> <code>POST /cart/add</code></p>
            <p><strong>Description:</strong> Add a book to the cart or update the quantity if the book already exists in the cart.</p>
            <h4>Request Body</h4>
            <pre>
{
    "book_id": "integer",
    "quantity": "integer"
}
            </pre>
            <h4>Response</h4>
            <p class="success">Success (200):</p>
            <pre>{"message": "Item added to cart successfully.", "cart": {...}}</pre>
            <p class="error">Validation Error (422):</p>
            <pre>{"book_id": ["The book_id field is required."]}</pre>
        </div>
    </div>

    <!-- 3. Apply Discount -->
    <div class="endpoint">
        <h3 onclick="toggleDetails(this)">3. Apply Discount</h3>
        <div class="details">
            <p><strong>Endpoint:</strong> <code>POST /cart/apply-coupon</code></p>
            <p><strong>Description:</strong> Apply a discount coupon to the cart.</p>
            <h4>Request Body</h4>
            <pre>
{
    "code": "string"
}
            </pre>
            <h4>Response</h4>
            <p class="success">Success (200):</p>
            <pre>{"discount": 10000}</pre>
            <p class="error">Validation Error (422):</p>
            <pre>{"code": ["The code field is required."]}</pre>
        </div>
    </div>

    <!-- 4. Remove Discount -->
    <div class="endpoint">
        <h3 onclick="toggleDetails(this)">4. Remove Discount</h3>
        <div class="details">
            <p><strong>Endpoint:</strong> <code>DELETE /cart/remove-coupon</code></p>
            <p><strong>Description:</strong> Remove any applied discount coupon from the cart.</p>
            <h4>Response</h4>
            <p class="success">Success (200):</p>
            <pre>{"message": "Discount removed successfully."}</pre>
        </div>
    </div>

    <!-- 5. Update Item Quantity -->
    <div class="endpoint">
        <h3 onclick="toggleDetails(this)">5. Update Item Quantity</h3>
        <div class="details">
            <p><strong>Endpoint:</strong> <code>PUT /cart/increase-quantity/{rowId}</code></p>
            <p><strong>Description:</strong> Increase the quantity of a specific item in the cart.</p>
            <h4>Response</h4>
            <p class="success">Success (200):</p>
            <pre>{"message": "Item quantity updated successfully.", "cart": {...}}</pre>
            <p class="error">Item Not Found (404):</p>
            <pre>{"message": "Item not found in cart."}</pre>
        </div>
    </div>

    <!-- 6. Remove Item from Cart -->
    <div class="endpoint">
        <h3 onclick="toggleDetails(this)">6. Remove Item from Cart</h3>
        <div class="details">
            <p><strong>Endpoint:</strong> <code>DELETE /cart/remove/{rowId}</code></p>
            <p><strong>Description:</strong> Remove a specific item from the cart.</p>
            <h4>Response</h4>
            <p class="success">Success (200):</p>
            <pre>{"message": "Item removed successfully.", "cart": {...}}</pre>
            <p class="error">Item Not Found (404):</p>
            <pre>{"message": "Item not found in cart."}</pre>
        </div>
    </div>

    <!-- 7. Clear Cart -->
    <div class="endpoint">
        <h3 onclick="toggleDetails(this)">7. Clear Cart</h3>
        <div class="details">
            <p><strong>Endpoint:</strong> <code>DELETE /cart/clear</code></p>
            <p><strong>Description:</strong> Clear all items from the user's cart.</p>
            <h4>Response</h4>
            <p class="success">Success (200):</p>
            <pre>{"message": "Cart cleared successfully."}</pre>
        </div>
    </div>
</div>
       <div class="section">
    <h2>Book Management</h2>

    <div class="endpoint">
        <h3 onclick="toggleDetails(this)">1. List Books</h3>
        <div class="details">
            <p><strong>Endpoint:</strong> <code>GET /books</code></p>
            <p><strong>Description:</strong> Retrieve a list of all books.</p>
            <h4>Response</h4>
            <p class="success">Success (200):</p>
            <pre>
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "Book Title",
      "authors": ["Author 1", "Author 2"],
      ...
    }
  ]
}
            </pre>
        </div>
    </div>

    <div class="endpoint">
        <h3 onclick="toggleDetails(this)">2. Create Book</h3>
        <div class="details">
            <p><strong>Endpoint:</strong> <code>POST /books</code></p>
            <p><strong>Description:</strong> Create a new book.</p>
            <h4>Request Body</h4>
            <pre>
{
  "title": "string",
  "isbn": "string",
  "publisher_id": "integer",
  "translator_id": "integer (nullable)",
  "authors": ["integer", "integer"], // Array of author IDs
  "category_id": "integer",
  "cover_type_id": "integer",
  "genre_id": "integer",
  "language_id": "integer",
  "image": "image_file",
  "images": ["image_file1", "image_file2"],
  "description": "string",
  "quantity": "integer",
  "original_price": "numeric",
  "discount_price": "numeric (nullable)",
  "published_year": "integer",
  "number_pages": "integer",
  "size": "string",
  "weight": "numeric",
  "status": "string"
}
            </pre>
            <h4>Response</h4>
            <p class="success">Success (201):</p>
            <pre>
{
  "success": true,
  "message": "Book created successfully",
  "data": {
    "id": 1,
    "title": "Book Title",
    "authors": ["Author 1", "Author 2"],
    ...
  }
}
            </pre>
        </div>
    </div>

    <div class="endpoint">
        <h3 onclick="toggleDetails(this)">3. Get Book</h3>
        <div class="details">
            <p><strong>Endpoint:</strong> <code>GET /books/{id}</code></p>
            <p><strong>Description:</strong> Retrieve a specific book by ID.</p>
            <h4>Response</h4>
            <p class="success">Success (200):</p>
            <pre>
{
  "success": true,
  "message": "Detail Data Book",
  "data": {
    "id": 1,
    "title": "Book Title",
    "authors": ["Author 1", "Author 2"],
    ...
  }
}
            </pre>
        </div>
    </div>

    <div class="endpoint">
        <h3 onclick="toggleDetails(this)">4. Update Book</h3>
        <div class="details">
            <p><strong>Endpoint:</strong> <code>PUT /books/{id}</code></p>
            <p><strong>Description:</strong> Update an existing book.</p>
            <h4>Request Body</h4>
            <pre>
{
  "title": "string",
  "isbn": "string",
  "publisher_id": "integer",
  "translator_id": "integer (nullable)",
  "authors": ["integer", "integer"], // Array of author IDs
  "category_id": "integer",
  "cover_type_id": "integer",
  "genre_id": "integer",
  "language_id": "integer",
  "image": "image_file",
  "images": ["image_file1", "image_file2"],
  "description": "string",
  "quantity": "integer",
  "original_price": "numeric",
  "discount_price": "numeric (nullable)",
  "published_year": "integer",
  "number_pages": "integer",
  "size": "string",
  "weight": "numeric",
  "status": "string"
}
            </pre>
            <h4>Response</h4>
            <p class="success">Success (200):</p>
            <pre>
{
  "success": true,
  "message": "Book updated successfully",
  "data": {
    "id": 1,
    "title": "Updated Book Title",
    "authors": ["Author 1", "Author 3"],
    ...
  }
}
            </pre>
        </div>
    </div>

    <div class="endpoint">
        <h3 onclick="toggleDetails(this)">5. Delete Book</h3>
        <div class="details">
            <p><strong>Endpoint:</strong> <code>DELETE /books/{id}</code></p>
            <p><strong>Description:</strong> Delete a specific book by ID.</p>
            <h4>Response</h4>
            <p class="success">Success (200):</p>
            <pre>
{
  "success": true,
  "message": "Book deleted successfully",
  "data": null
}
            </pre>
        </div>
    </div>
</div>


        <!-- Category Management Section -->
        <div class="section">
            <h2>Category Management</h2>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">1. List Categories</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>GET /categories</code></p>
                    <p><strong>Description:</strong> Retrieve a list of all categories.</p>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>...Response Example...</pre>
                </div>
            </div>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">2. Create Category</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>POST /categories</code></p>
                    <p><strong>Description:</strong> Create a new category.</p>
                    <h4>Request Body</h4>
                    <pre>{
  "name": "string"
}</pre>
                    <h4>Response</h4>
                    <p class="success">Success (201):</p>
                    <pre>...Response Example...</pre>
                    <p class="error">Validation Error (422):</p>
                    <pre>...Error Example...</pre>
                </div>
            </div>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">3. Get Category</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>GET /categories/{id}</code></p>
                    <p><strong>Description:</strong> Retrieve a specific category by ID.</p>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>...Response Example...</pre>
                    <p class="error">Not Found (404):</p>
                    <pre>...Error Example...</pre>
                </div>
            </div>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">4. Update Category</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>PUT /categories/{id}</code></p>
                    <p><strong>Description:</strong> Update an existing category.</p>
                    <h4>Request Body</h4>
                    <pre>{
  "name": "string"
}</pre>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>...Response Example...</pre>
                    <p class="error">Validation Error (422):</p>
                    <pre>...Error Example...</pre>
                </div>
            </div>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">5. Delete Category</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>DELETE /categories/{id}</code></p>
                    <p><strong>Description:</strong> Delete a specific category by ID.</p>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>...Response Example...</pre>
                    <p class="error">Cannot Delete (400):</p>
                    <pre>...Error Example...</pre>
                </div>
            </div>
        </div>
        <!-- Translator Management Section -->
        <div class="section">
            <h2>Translator Management</h2>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">1. List Translators</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>GET /translators</code></p>
                    <p><strong>Description:</strong> Retrieve a list of all translators.</p>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>[
  {
    "id": 1,
    "name": "John Doe"
  },
  {
    "id": 2,
    "name": "Jane Smith"
  }
]</pre>
                </div>
            </div>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">2. Create Translator</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>POST /translators</code></p>
                    <p><strong>Description:</strong> Create a new translator.</p>
                    <h4>Request Body</h4>
                    <pre>{
  "name": "string"
}</pre>
                    <h4>Response</h4>
                    <p class="success">Success (201):</p>
                    <pre>...Response Example...</pre>
                    <p class="error">Validation Error (422):</p>
                    <pre>...Error Example...</pre>
                </div>
            </div>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">3. Get Translator</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>GET /translators/{id}</code></p>
                    <p><strong>Description:</strong> Retrieve a specific translator by ID.</p>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>{
  "id": 1,
  "name": "John Doe"
}</pre>
                    <p class="error">Not Found (404):</p>
                    <pre>...Error Example...</pre>
                </div>
            </div>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">4. Update Translator</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>PUT /translators/{id}</code></p>
                    <p><strong>Description:</strong> Update an existing translator.</p>
                    <h4>Request Body</h4>
                    <pre>{
  "name": "string"
}</pre>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>...Response Example...</pre>
                    <p class="error">Validation Error (422):</p>
                    <pre>...Error Example...</pre>
                </div>
            </div>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">5. Delete Translator</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>DELETE /translators/{id}</code></p>
                    <p><strong>Description:</strong> Delete a specific translator by ID.</p>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>...Response Example...</pre>
                    <p class="error">Cannot Delete (400):</p>
                    <pre>...Error Example...</pre>
                </div>
            </div>
        </div>
        <div class="section">
            <h2>Publisher Management</h2>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">1. List Publishers</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>GET /publishers</code></p>
                    <p><strong>Description:</strong> Retrieve a list of all publishers.</p>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>...Response Example...</pre>
                </div>
            </div>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">2. Create Publisher</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>POST /publishers</code></p>
                    <p><strong>Description:</strong> Create a new publisher.</p>
                    <h4>Request Body</h4>
                    <pre>{
  "image": "image_file",
  "name": "string",
  "country": "string"
}</pre>
                    <h4>Response</h4>
                    <p class="success">Success (201):</p>
                    <pre>...Response Example...</pre>
                    <p class="error">Validation Error (422):</p>
                    <pre>...Error Example...</pre>
                </div>
            </div>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">3. Get Publisher</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>GET /publishers/{id}</code></p>
                    <p><strong>Description:</strong> Retrieve a specific publisher by ID.</p>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>...Response Example...</pre>
                    <p class="error">Not Found (404):</p>
                    <pre>...Error Example...</pre>
                </div>
            </div>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">4. Update Publisher</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>PUT /publishers/{id}</code></p>
                    <p><strong>Description:</strong> Update an existing publisher.</p>
                    <h4>Request Body</h4>
                    <pre>{
  "image": "image_file",
  "name": "string",
  "country": "string"
}</pre>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>...Response Example...</pre>
                    <p class="error">Validation Error (422):</p>
                    <pre>...Error Example...</pre>
                </div>
            </div>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">5. Delete Publisher</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>DELETE /publishers/{id}</code></p>
                    <p><strong>Description:</strong> Delete a specific publisher by ID.</p>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>...Response Example...</pre>
                    <p class="error">Cannot Delete (400):</p>
                    <pre>...Error Example...</pre>
                </div>
            </div>
        </div>
        <div class="section">
            <h2>Cover Type Management</h2>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">1. List Cover Types</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>GET /covertypes</code></p>
                    <p><strong>Description:</strong> Retrieve a list of all cover types.</p>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Hardcover"
    },
    {
      "id": 2,
      "name": "Paperback"
    }
  ]
}</pre>
                </div>
            </div>
        </div>
        <div class="section">
            <h2>Genre Management</h2>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">1. List Genres</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>GET /genres</code></p>
                    <p><strong>Description:</strong> Retrieve a list of all genres.</p>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Fiction"
    },
    {
      "id": 2,
      "name": "Non-fiction"
    }
  ]
}</pre>
                </div>
            </div>
        </div>
        <div class="section">
            <h2>Language Management</h2>

            <div class="endpoint">
                <h3 onclick="toggleDetails(this)">1. List Languages</h3>
                <div class="details">
                    <p><strong>Endpoint:</strong> <code>GET /languages</code></p>
                    <p><strong>Description:</strong> Retrieve a list of all languages.</p>
                    <h4>Response</h4>
                    <p class="success">Success (200):</p>
                    <pre>{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "English"
    },
    {
      "id": 2,
      "name": "Vietnamese"
    }
  ]
}</pre>
                </div>
            </div>
        </div>

    </div>



    </div>

    <script>
        function toggleDetails(element) {
            const details = element.nextElementSibling;
            if (details.style.display === "block") {
                details.style.display = "none";
            } else {
                details.style.display = "block";
            }
        }
    </script>
</body>

</html>
