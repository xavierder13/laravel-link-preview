<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use Auth;
use App\LinkPreview;
use Spatie\Browsershot\Browsershot;

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
        $file_type = 'jpg';
        $file_name = time() . '.' . $file_type;
        $file_path = '/img';

        try {
            Browsershot::url($request->url)->save('img/'.$file_name);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th], 200);
        }

        // Browsershot::url($request->url)->save("link_preview/img/" . $file_name);

        $link_preview = new LinkPreview();
        $link_preview->title = $request->get('title');
        $link_preview->url = $request->get('url');
        $link_preview->description = $request->get('description');
        $link_preview->file_name = $file_name;
        $link_preview->file_type = $file_type;
        $link_preview->file_path = $file_path;
        $link_preview->save();

        return response()->json(['success' => 'Record has successfully added', 'link_preview' => $link_preview], 200);
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
    
            try {
                Browsershot::url($request->url)->save('img/'.$file_name);
                
                $path = public_path() .  $link_preview->file_path . "/" . $link_preview->file_name;
                unlink($path);

                $link_preview->file_name = $file_name;
                $link_preview->file_type = $file_type;
                $link_preview->file_path = $file_path;
                
            } catch (\Throwable $th) {
                return response()->json(['error' => $th], 200);
            }
        }

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

        $path = public_path() .  $file_path . "/" . $file_name;
        unlink($path);

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}
