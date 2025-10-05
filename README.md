# 🐘 SIA PHP API Testing

This repository contains a simple PHP API for **local development and REST API experimentation**.
It allows you to test basic HTTP methods — **GET, POST, PUT, DELETE** — using **Postman** or **cURL**.

---

## 🧩 Setup Instructions

Follow these steps carefully to get started:

### 🪄 Step 1: Clone the Repository

Clone this repository into your local folder:

```bash
git clone https://github.com/jeremymesinas/sia-php-api
```

### 🏷️ Step 2: Rename the Folder

Rename the cloned folder to:

```
v1
```

### 📂 Step 3: Move to XAMPP Directory

Cut and paste the `v1` folder into your XAMPP `htdocs` directory:

```
C:\xampp\htdocs
```

### ⚙️ Step 4: Start XAMPP Services

Open **XAMPP Control Panel** and start the following:

* ✅ Apache
* ✅ MySQL

Then go to your browser and open:

```
http://localhost/phpmyadmin
```

### 🗃️ Step 5: Create the Database

In **phpMyAdmin**, create a new database named:

```
PRODUCTSCJDM
```

### 📥 Step 6: Import SQL File

Import the `productscjdm.sql` file into the `PRODUCTSCJDM` database.

### 🧪 Step 7: Test the API

Open **Postman** and test the API using this endpoint:

```
http://localhost/v1/controller/product.php
```

Try different HTTP methods:

* **GET**
* **POST**
* **PUT**
* **DELETE**

Make sure to include:

* URL parameters
* Request body contents

> 💡 **Note:** This only works with the **Postman desktop app**, not the browser version.
> Alternatively, you can also use **curl** or **curl.exe** commands to test the API.

# 🚀 Thank you!

---

## 🎉 Thank You!

---


