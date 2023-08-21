<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\PatientController;
use App\Mail\ConfirmationEmail;
use Illuminate\Support\Facades\Mail;
use App\Models\Patient;

class PatientControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test patient registration.
     *
     * @return void
     */
    public function testPatientRegistration()
    {
        // Disable email sending during the test
        Mail::fake();

        // Prepare test data
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone_number' => '1234567890',
        ];

        // Create a fake image file for testing
        $file = UploadedFile::fake()->image('document.jpg');

        // Create a POST request with form data and file upload
        $response = $this->post('/api/register', $data + ['document_photo' => $file]);

        // Assert that the response has a successful status code
        $response->assertStatus(200);

        // Assert that the patient was created in the database
        $this->assertDatabaseHas('patients', [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
        ]);

        // Assert that an email was sent to the patient
        Mail::assertSent(ConfirmationEmail::class, function ($mail) use ($data) {
            return $mail->hasTo($data['email']);
        });

        // Clean up: Delete the created patient (if needed)
        $patient = Patient::where('email', $data['email'])->first();
        if ($patient) {
            $patient->delete();
        }
    }
}
