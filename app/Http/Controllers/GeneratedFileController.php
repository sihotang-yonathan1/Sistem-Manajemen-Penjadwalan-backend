<?php

namespace App\Http\Controllers;

use App\Models\GeneratedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class GeneratedFileController extends Controller
{
    // Get all generated files
    public function getAllFiles()
    {
        $files = GeneratedFile::orderBy('created_at', 'desc')->get();
        
        $transformedFiles = $files->map(function($file) {
            return [
                'id' => (string)$file->id,
                'name' => $file->name,
                'date' => $file->created_at->format('Y-m-d H:i:s'),
                'size' => $file->size,
                'status' => $file->status,
                'description' => $file->description
            ];
        });
        
        return response()->json($transformedFiles);
    }
    
    // Get file details
    public function getFileDetails($id)
    {
        $file = GeneratedFile::findOrFail($id);
        
        return response()->json([
            'id' => (string)$file->id,
            'name' => $file->name,
            'date' => $file->created_at->format('Y-m-d H:i:s'),
            'size' => $file->size,
            'status' => $file->status,
            'description' => $file->description
        ]);
    }
    
    // Download file
    public function downloadFile($id)
    {
        $file = GeneratedFile::findOrFail($id);
        
        if (!Storage::exists($file->file_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }
        
        return Storage::download($file->file_path, $file->name);
    }
    
    // Delete file
    public function deleteFile($id)
    {
        $file = GeneratedFile::findOrFail($id);
        
        // Delete the physical file if it exists
        if (Storage::exists($file->file_path)) {
            Storage::delete($file->file_path);
        }
        
        // Delete the database record
        $file->delete();
        
        return response()->json(['message' => 'File deleted successfully']);
    }
}
