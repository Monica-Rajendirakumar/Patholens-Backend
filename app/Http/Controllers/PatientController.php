<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
    /**
     * Get all patient records.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $patients = Patient::orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $patients
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch patients: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch patient records.'
            ], 500);
        }
    }

    /**
     * Get a specific patient record.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $patient
        ]);
    }

    /**
     * Get patient history for a specific user.
     *
     * @param string $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserHistory($user_id)
    {
        try {
            $patients = Patient::where('user_id', $user_id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $patients,
                'count' => $patients->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch user history: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch patient history.'
            ], 500);
        }
    }

    /**
     * Store a new patient record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string',
            'patient_name' => 'required|string|max:255',
            'age' => 'required|integer|min:0|max:150',
            'gender' => 'required|in:male,female,other',
            'contact_number' => 'required|string|max:15',
            'diagnosising_image' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'result' => 'nullable|string|max:255',
            'confidence' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $imagePath = $this->handleImageUpload($request);

            $patient = Patient::create([
                'user_id' => $request->user_id,
                'patient_name' => $request->patient_name,
                'age' => $request->age,
                'gender' => $request->gender,
                'contact_number' => $request->contact_number,
                'diagnosising_image' => $imagePath,
                'result' => $request->result,
                'confidence' => $request->confidence,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Patient record created successfully.',
                'data' => $patient
            ], 201);

        } catch (\Exception $e) {
            Log::error('Patient creation failed: ' . $e->getMessage());

            if (isset($imagePath) && $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create patient record. Please try again.'
            ], 500);
        }
    }

    /**
     * Update existing patient record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'patient_name' => 'nullable|string|max:255',
            'age' => 'nullable|integer|min:0|max:150',
            'gender' => 'nullable|in:male,female,other',
            'contact_number' => 'nullable|string|max:15',
            'diagnosising_image' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'result' => 'nullable|string|max:255',
            'confidence' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $imagePath = $this->handleImageUpload($request);

            // Delete old image if new one uploaded
            if ($imagePath && $patient->diagnosising_image) {
                Storage::disk('public')->delete($patient->diagnosising_image);
            }

            $patient->update([
                'patient_name' => $request->patient_name ?? $patient->patient_name,
                'age' => $request->age ?? $patient->age,
                'gender' => $request->gender ?? $patient->gender,
                'contact_number' => $request->contact_number ?? $patient->contact_number,
                'diagnosising_image' => $imagePath ?? $patient->diagnosising_image,
                'result' => $request->result ?? $patient->result,
                'confidence' => $request->confidence ?? $patient->confidence,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Patient record updated successfully.',
                'data' => $patient
            ]);

        } catch (\Exception $e) {
            Log::error('Patient update failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update patient record.'
            ], 500);
        }
    }

    /**
     * Delete a patient record.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found'
            ], 404);
        }

        try {
            // Delete associated image
            if ($patient->diagnosising_image) {
                Storage::disk('public')->delete($patient->diagnosising_image);
            }

            $patient->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Patient record deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Patient deletion failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete patient record.'
            ], 500);
        }
    }

    /**
     * Handle image upload for diagnosis.
     *
     * @param Request $request
     * @return string|null
     */
    private function handleImageUpload(Request $request)
    {
        if (!$request->hasFile('diagnosising_image')) {
            return null;
        }

        $image = $request->file('diagnosising_image');
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        
        return $image->storeAs('diagnosis_images', $filename, 'public');
    }
}