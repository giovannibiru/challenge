<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadRequest;
use App\Jobs\ProcessImport;
use App\Models\Parsed;
use Carbon\Carbon;

class MainController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function uploadFile(UploadRequest $request)
    {
        $file = $request->file;
        $extension = strtolower($file->getClientOriginalExtension());
        $name = 'original.' . $extension;
        $result = $file->move(public_path('uploads'), $name);
        $parsed = null;
        if($result) {
            $parsed = new Parsed;
            $parsed->originalPath = 'uploads/' . $name;
            $parsed->status = 0;
            $parsed->save();
        }

        ProcessImport::dispatch($parsed->id);

        $notifications = [
            'status' => 'Import Process Started!'
        ];
        return \redirect()->route('index')->with($notifications);
    }

    public static function checkAge($dob)
    {
        if(is_null($dob)) {
            return true;
        }
        else {
            $carbonDOB = null;
            // $dob = '1955-12-05 00:00:00';
            try {
                $carbonDOB = Carbon::create($dob);
            } catch (\Throwable $th) {
                $carbonDOB = Carbon::createFromFormat('d/m/Y', $dob);
            }
            $now = Carbon::now();
            $ageYears = $carbonDOB->diffInYears($now);
            return $ageYears >= 18 && $ageYears <= 65 ? true : false;
        }
    }
}
