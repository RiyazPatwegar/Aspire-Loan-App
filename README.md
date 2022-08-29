# Aspire-Loan-App (Rest API)
Customer Loan Application - Admin Approval - Payment Schedule - Customer Repayment According to Schedule

# Module Design Architecture
I have used 'nwidart/laravel-modules' package to divide application into modules pattern. All API related code has been placed into 'src/V1/'.
So that you still have an 'app/Http' part remains reserved, You can use it if you have an frontend application too in one project.

# Install
composer install

Go to project directory and run above command 

# Database Configuration : (.env)

DB_CONNECTION=mysql

DB_HOST=127.0.0.1

DB_PORT=3306

DB_DATABASE=aspire-rest-api

DB_USERNAME=root

DB_PASSWORD=

# Migration

Before run migration you have to configure local database : 'aspire-rest-api' as mentioned above

php artisan migrate

Required MySql Tables Get Created With Above Command

# Start Application
php artisan serve

Run above command within project folder to start the application. In my case project running on http://127.0.0.1:8000

--------------------------------------------------------------------------------------------------------------------------------------------------------------------

# API List 

1) Customer Apply Loan

http://127.0.0.1:8000/api/v1/customer-loan/apply

Features :
    
    - Customer can apply one loan application at a time and not multiple loan application.


2) Admin Can View Loan Applications

http://127.0.0.1:8000/api/v1/admin/getLoanApplications

Features :
    
    - Admin can view all loan applications.


3) Admin Approve Loan

http://127.0.0.1:8000/api/v1/admin/approveLoan

Features :
    
    - Admin can approve loan
    
    - Once loan get approved by Admin, Weekly repayment (EMI) get scheduled depends upon specified terms.


4) Customer Check View Policy and get Loan Details

http://127.0.0.1:8000/api/v1/customer-loan/getLoanStatus

Features :
    
    - Customer can only view loan and schedule details against it.


5) Customer Payment against Schedule

http://127.0.0.1:8000/api/v1/payment/payNow

Features :
    
    - Customer can not pay before loan approval from admin.
    
    - Customer should not pay less than scheduled amount.
    
    - Customer can pay more than scheduled amount. If customer pay more than scheduled amount, remaining amount will devide into rest of repayment schedule date.

    - Customer can pay whole amount on first scheduled payment date also.  In this case status for rest of schedule payment date will be 'NILL'. And loan status will becaome 'PAID';
        

# Postman Collection

Please Find Postman Collection Attachment Here [Aspire REST API Assignment.postman_collection.zip](https://github.com/RiyazPatwegar/Aspire-Loan-App/files/9439791/Aspire.REST.API.Assignment.postman_collection.zip)

# API Document

Please find API document here [API_Doc.zip](https://github.com/RiyazPatwegar/Aspire-Loan-App/files/9439780/API_Doc.zip)
