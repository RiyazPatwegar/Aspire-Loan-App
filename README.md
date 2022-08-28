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
   
   customerId : Any numeric integer value
   amount : Any numeric integer / float value
   term : any numeric interger value
   
End Point :

http://127.0.0.1:8000/api/v1/customer-loan/apply

Request Payload :

{
    "customerId" : 1,
    "amount" : 10000,
    "term" : 1
}

Response : 

{
    "code": 200,
    "status": "success",
    "message": "Loan has been applied successfully",
    "data": {
        "application_id": 1
    }
}

2) Admin Can View All Loan Applications

adminId : Any numeric integer value

End Point :

http://127.0.0.1:8000/api/v1/admin/getLoanApplications

Request Payload :

{
    "adminId": 1
}

Response : 

{
    "code": 200,
    "status": "success",
    "message": "success",
    "data": [
        {
            "id": 1,
            "customer_id": 1,
            "applied_at": "2022-08-27 05:56:49",
            "amount": 10000,
            "term": 3,
            "application_status": "PENDING",
            "loan_status": "PENDING",
            "approved_by": 0,
            "approved_at": null,
            "schedule": []
        }
    ]
}

3) Admin Approve Loan

applicationId :   'id' field recived from response 'admin/getLoanApplications'
adminId :      Any numeric integer value

End Point :

http://127.0.0.1:8000/api/v1/admin/approveLoan

Request Payload :

{
    "applicationId": 9,
    "adminId": 1
}


Response : 

{
    "code": 200,
    "status": "success",
    "message": "Application has been approved"
}
