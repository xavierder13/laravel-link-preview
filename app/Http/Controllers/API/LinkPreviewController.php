<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Validator;
use DB;
use Auth;
use App\LinkPreview;
use Spatie\Browsershot\Browsershot;
use Carbon\Carbon;

class LinkPreviewController extends Controller
{
    public function index()
    {       
        $link_previews = LinkPreview::all();
        
        return response()->json(['link_previews' => $link_previews], 200);
    }

    public function getLinkPreview()
    {
        $link_previews = LinkPreview::all();
        
        return response()->json(['link_previews' => $link_previews], 200);
    }

    public function create()
    {
        
    }

    public function store(Request $request)
    {   
     
        $rules = [
            'title.required' => 'Please enter title',
            'url.required' => 'Please enter url',
            'description.required' => 'Please enter description',
        ];

        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'url' => 'required',
            'description' => 'required',
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $file_extension = "";
        $file_date = "";
        $file_name = "";
        $file_path = "";
        $file = $request->file;

        $link_preview = new LinkPreview();
        $link_preview->title = $request->get('title');
        $link_preview->url = $request->get('url');
        $link_preview->description = $request->get('description');
 
        try {
            if($request->file('file')) // if update with new file upload
            {   
                $file_extension = $file->getClientOriginalExtension();

                $validator = Validator::make(
                    [   
                        'file_ext' => strtolower($file_extension),
                        'file' => $file,
                    ],
                    [
                        'file_ext' => 'in:jpeg,jpg,png',
                        'file' => 'max: 20800'
                    ], 
                    [
                        'file_ext.in' => 'File type must be jpeg, jpg, png.',
                        'file.max' => 'File size maximum is 20MB,'
                    ]
                );  
                
                if($validator->fails())
                {
                    return response()->json(['error' => $validator->errors()], 200);
                } 
                
                $file_extension = $file->getClientOriginalExtension();
                $file_date = Carbon::now()->format('Y-m-d');
                $file_name = time().$file->getClientOriginalName();
                $file_path = '/wysiwyg/link_preview_images/' . $file_date;
                $file->move(public_path() . $file_path, $file_name);

                $link_preview->file_name = $file_name;
                $link_preview->file_type = $file_extension;
                $link_preview->file_path = $file_path;
    
            }
            
            $link_preview->save();

            return response()->json(['success' => 'Record has successfully added', 'link_preview' => $link_preview], 200);
          
        } catch (\Exception $e) {
        
            return response()->json(['error' => $e->getMessage()], 200);
        }

        // $file_type = 'jpg';
        // $file_name = time() . '.' . $file_type;
        // $file_path = '/img';

        // try {
        //     Browsershot::url($request->url)->save('img/'.$file_name);
        // } catch (\Throwable $th) {
        //     return response()->json(['error' => $th], 200);
        // }

        // Browsershot::url($request->url)->save("link_preview/img/" . $file_name);

    }


    public function edit(Request $request)
    {   
        $link_preview_id = $request->get('link_preview_id');

        $link_preview = LinkPreview::find($link_preview_id);

        //if record is empty then display error page
        if(empty($link_preview->id))
        {
            return abort(404, 'Not Found');
        }
        
        // return view('pages.service.edit', compact('service'));
        return response()->json($link_preview, 200);

    }


    public function update(Request $request, $link_preview_id)
    {   
        $rules = [
            'title.required' => 'Please enter title',
            'url.required' => 'Please enter url',
            'description.required' => 'Please enter description',
        ];

        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'url' => 'required',
            'description' => 'required',
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $link_preview = LinkPreview::find($link_preview_id);

        if($link_preview->url <> $request->url)
        {
            $file_type = 'jpg';
            $file_name = time() . '.' . $file_type;
            $file_path = '/img';
    
            // try {
            //     Browsershot::url($request->url)->save('img/'.$file_name);
                
            //     $path = public_path() .  $link_preview->file_path . "/" . $link_preview->file_name;
            //     unlink($path);

            //     $link_preview->file_name = $file_name;
            //     $link_preview->file_type = $file_type;
            //     $link_preview->file_path = $file_path;
                
            // } catch (\Throwable $th) {
            //     return response()->json(['error' => $th], 200);
            // }
        }

        // $directoryPath = public_path($file_path);
        // $path = public_path() .  $link_preview->file_path . "/" . $link_preview->file_name;
        
        // if (File::isDirectory($directoryPath)) {
        //     unlink($path);
        // }

        //if record is empty then display error page
        if(empty($link_preview->id))
        {
            return abort(404, 'Not Found');
        }

        $link_preview->title = $request->get('title');
        $link_preview->url = $request->get('url');
        $link_preview->description = $request->get('description');
        $link_preview->save();

        return response()->json([
            'success' => 'Record has been updated',
            'link_preview' => $link_preview ]
        );
    }

    public function delete(Request $request)
    {   
        $link_preview_id = $request->get('link_preview_id');
        $link_preview = LinkPreview::find($link_preview_id);
        $file_path = $link_preview->file_path;
        $file_name = $link_preview->file_name;
        //if record is empty then display error page
        if(empty($link_preview->id))
        {
            return abort(404, 'Not Found');
        }

        $link_preview->delete();

        $directoryPath = public_path($file_path);
        $path = public_path() .  $file_path . "/" . $file_name;
        
        if (File::isDirectory($directoryPath)) {
            unlink($path);
        }

        return response()->json(['success' => 'Record has been deleted'], 200);
    }

    public function img_upload(Request $request)
    {   
        $file = $request->file;
        $link_preview = LinkPreview::find($request->link_preview_id);
        try {
            if($request->file('file')) // if update with new file upload
            {   
                $file_extension = $file->getClientOriginalExtension();

                $validator = Validator::make(
                    [   
                        'file_ext' => strtolower($file_extension),
                        'file' => $file,
                    ],
                    [
                        'file_ext' => 'in:jpeg,jpg,png',
                        'file' => 'max: 20800'
                    ], 
                    [
                        'file_ext.in' => 'File type must be jpeg, jpg, png.',
                        'file.max' => 'File size maximum is 20MB,'
                    ]
                );  
                
                if($validator->fails())
                {
                    return response()->json(['error' => $validator->errors()], 200);
                } 
                
                $file_extension = $file->getClientOriginalExtension();
                $file_date = Carbon::now()->format('Y-m-d');
                $file_name = time().$file->getClientOriginalName();
                $file_path = '/wysiwyg/link_preview_images/' . $file_date;
                $file->move(public_path() . $file_path, $file_name);

                $link_preview->file_name = $file_name;
                $link_preview->file_type = $file_extension;
                $link_preview->file_path = $file_path;
    
            }
            
            $link_preview->save();

            return response()->json(['success' => 'Record has successfully added', 'link_preview' => $link_preview], 200);
          
        } catch (\Exception $e) {
        
            return response()->json(['error' => $e->getMessage()], 200);
        }
        
        
    }

    public function img_delete(Request $request) 
    {
        $link_preview = LinkPreview::find($request->link_preview_id);

        //if record is empty then display error page
        if(empty($link_preview->id))
        {
            return abort(404, 'Not Found');
        }

        $directoryPath = public_path($link_preview->file_path);
        $path = public_path() .  $link_preview->file_path. "/" . $link_preview->file_name;

        $link_preview->file_name = "";
        $link_preview->file_type = "";
        $link_preview->file_path = "";
        $link_preview->save();

        if (File::isDirectory($directoryPath)) {
            unlink($path);
        }

        return response()->json(['success' => 'Image has been deleted'], 200);
    }
}
