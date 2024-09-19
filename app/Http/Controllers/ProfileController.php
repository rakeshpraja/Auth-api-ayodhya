<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UploadedFile;

class ProfileController extends Controller
{
    public function fileForm()
    {
        return view('file_upload');
    }
    public function upload(Request $request)
    {

        $request->validate([
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:5242880',
        ], [
            'file.required' => 'Please upload a file.',
            'file.mimes' => 'Only JPG, JPEG, PNG, and PDF files are allowed.',
            'file.max' => 'File size cannot exceed 5MB.',
        ]);


        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
            $filePath = $file->storeAs('uploads', $fileName);

            $upload = UploadedFile::create([
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_extension' => $fileExtension,
                'file_size' => $fileSize,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded and stored successfully.',
                'file' => $upload
            ]);
        }

        return response()->json(['success' => false, 'message' => 'File upload failed'], 500);
    }
}
