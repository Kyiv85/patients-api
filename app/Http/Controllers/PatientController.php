<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Patient;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PatientController extends Controller
{
    // Register patient
    public function register(Request $request)
    {
        //Validate info
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:patients,email',
            'phone_number' => 'required',
            'document_photo' => 'required',
        ]);

        //Check validations
        if ($validator->fails()) {
            throw new HttpResponseException(response()->json(['errors' => $validator->errors()], 422));
        }

        try {
            // Validate and store image
            $photo = $request->file('document_photo');
            $path = $photo->store('photos', 'public');

            //Create patient
            $patient = Patient::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone_number' => $request->input('phone_number'),
                'document_photo' => $path,
            ]);
            
        } catch (HttpResponseException $e) {
            return response()->json(['message' => 'Error saving patient information'], 401);
        }

        return response()->json(['message' => 'Patient registered successfully'], 200);
    }
}
