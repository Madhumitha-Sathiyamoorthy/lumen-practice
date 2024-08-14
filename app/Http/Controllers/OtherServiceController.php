<?php

namespace App\Http\Controllers;

use App\Models\XMLRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class OtherServiceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    public function xmlRequest(Request $request)
    {
        try {
            $data = simplexml_load_string($request->getContent());
            $encode = json_encode($data);
            $decode = json_decode($encode, true);
            foreach ($decode['employee'] as $employee) {
                $company = new XMLRequest();
                $company->firstname = $employee['firstname'];
                $company->lastname = $employee['lastname'];
                $company->designation = $employee['designation'];
                $company->salary = $employee['salary'];
                $company->save();
            }
            return response()->json(['Status' => 'Success', "Message" => "Data Saved Successfully!"], 200);
        } catch (\Throwable $e) {
            \Log::error("XML Request Failed --->" . $e->getMessage());
        }
    }

    public function saveFileFormat(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'message' => 'required'
            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }
            ;
            $modMsg = wordwrap($request->message, 20, "\n");
            Storage::put('message.txt', $modMsg);
            $file = storage_path() . "/app/message.txt";
            $headers = [
                'Content-Type' => 'application/txt',
            ];  
            return response()->download($file, 'message.txt', $headers);
        } catch (\Throwable $e) {
            \Log::error("saveFileFormat Request Failed --->" . $e->getMessage());
        }
    }
}
