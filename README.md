# Aspire-Loan-App (Rest API)
Customer Loan Application - Admin Approval - Payment Schedule - Customer Repayment According to Schedule

# Install
composer install

Go to project directory and run above command 

# Database Configuration

DB_CONNECTION=mysql

DB_HOST=127.0.0.1

DB_PORT=3306

DB_DATABASE=aspire-rest-api

DB_USERNAME=root

DB_PASSWORD=

# Start Application
php artisan serve

In my case project running on http://127.0.0.1:8000
--------------------------------------------------------------------------------------------------------------------------------------------------------------------

# API List 

1) Customer Apply Loan

http://127.0.0.1:8000/api/v1/customer-loan/apply


2) Admin Can View Loan Applications

http://127.0.0.1:8000/api/v1/admin/getLoanApplications


3) Admin Approve Loan

http://127.0.0.1:8000/api/v1/admin/approveLoan


4) Customer Check View Policy and get Loan Details

http://127.0.0.1:8000/api/v1/customer-loan/getLoanStatus


5) Customer Payment against Schedule

http://127.0.0.1:8000/api/v1/payment/payNow



Please Find Postman Collection Attachment Here [Aspire Assignment.postman_collection.zip](https://github.com/RiyazPatwegar/Aspire-Loan-App/files/9439474/Aspire.Assignment.postman_collection.zip)

