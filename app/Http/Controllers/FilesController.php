<?php

namespace App\Http\Controllers;

use App\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FilesController extends Controller
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

    /**
     * Get a validator for an incoming file upload request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'file' => 'required|file|max:20000000',
            'disk' => 'required|string|in:private,public',
            'filename' => 'nullable|string|max:255',
        ]);
    }

    /**
     * Fetch files.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\file $file
     * @return \Illuminate\Http\Response
     */
    public function fetch(Request $request, $disk = null, File $file = null)
    {
    	if ($file) {
    		return $file->load('uploaded_by');
    	}

    	elseif ($disk) {
    		return File::fromDisk($disk)->load('uploaded_by')->paginate(20);
    	}

    	return File::with('uploaded_by')->paginate(20);
    }

    /**
     * Store a file upload.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validator($request->all())->validate();

        $path = $request->file('file')->store('files', $request->input('disk'));

        $originalFilename = $request->has('filename')
        	? $request->input('filename')
        	: pathinfo($request->file('file')->getClientOriginalName())['filename'];

        $pathInfo = pathinfo($path);

        $file = File::create([
        	'uploaded_by' => $request->user()->id,
        	'original_filename' => $originalFilename,
        	'basename' => $pathInfo['basename'],
        	'disk' => $request->input('disk'),
        	'path' => $path,
        	'filename' => $pathInfo['filename'],
        	'extension' => $pathInfo['extension'],
        	'mime_type' => $request->file('file')->getMimeType(),
        	'size' => Storage::disk($request->input('disk'))->size($path),
        	'url' => Storage::disk($request->input('disk'))->url($path),
        ]);

        return $file;
    }
}
