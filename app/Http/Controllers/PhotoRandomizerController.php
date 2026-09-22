<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PhotoRandomizerController extends Controller
{
    public function randomize(Request $request)
    {
        try {
            $type = $request->input('type');
            
            if ($type === 'students') {
                $this->randomizeStudents();
            } elseif ($type === 'teachers') {
                $this->randomizeTeachers();
            } else {
                return response()->json(['success' => false, 'message' => 'Invalid type'], 400);
            }
            
            return response()->json(['success' => true, 'message' => 'Photos randomized successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    private function randomizeStudents()
    {
        $photosPath = public_path('Students');
        
        if (!File::exists($photosPath)) {
            throw new \Exception('Students folder not found');
        }
        
        $photos = File::files($photosPath);
        
        if (empty($photos)) {
            throw new \Exception('No photos found in Students folder');
        }
        
        $photoNames = array_map(fn($file) => $file->getFilename(), $photos);
        shuffle($photoNames);
        
        $students = Student::all();
        $photoIndex = 0;
        
        foreach ($students as $student) {
            $student->passport_photo = 'Students/' . $photoNames[$photoIndex % count($photoNames)];
            $student->save();
            $photoIndex++;
        }
    }
    
    private function randomizeTeachers()
    {
        $photosPath = public_path('teachers');
        
        if (!File::exists($photosPath)) {
            throw new \Exception('teachers folder not found');
        }
        
        $photos = File::files($photosPath);
        
        if (empty($photos)) {
            throw new \Exception('No photos found in teachers folder');
        }
        
        $photoNames = array_map(fn($file) => $file->getFilename(), $photos);
        shuffle($photoNames);
        
        $teachers = User::where('role', 'teacher')->get();
        $photoIndex = 0;
        
        foreach ($teachers as $teacher) {
            $teacher->profile_photo = 'teachers/' . $photoNames[$photoIndex % count($photoNames)];
            $teacher->save();
            $photoIndex++;
        }
    }
}
