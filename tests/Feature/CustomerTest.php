<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
  
    public function test_apply_customer_loan()
    {
        $response = $this->post('/api/v1/customer-loan/apply', ["customerId" => 20, "amount" => 10000, "term" => 3]);

        $response->assertStatus(200);
    }

    /* public function test_customer_get_loan_status()
    {
        $response = $this->get('/api/v1/customer-loan/getLoanStatus?customerId=117&viewPolicyCheck=true');
        $response->assertStatus(200);
    } */

    public function test_admin_get_loan_application()
    {
        $response = $this->get('/api/v1/admin/getLoanApplications?adminId=1');

        $response->assertStatus(200);
    }

    public function test_admin_approve_loan()
    {
        $response = $this->post('/api/v1/admin/approveLoan', ["applicationId" => 19, "adminId"=> 1]);

        $response->assertStatus(200);
    }

    public function test_payment_pay()
    {
        $response = $this->post('/api/v1/payment/payNow', ["applicationId" => 19, "customerId"=> 20, "amount"=> 3333.34]);

        $response->assertStatus(200);
    }
}
