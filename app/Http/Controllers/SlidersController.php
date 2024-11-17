<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Exception;

class SlidersController extends Controller
{

    /**
     * Display a listing of the sliders.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $sliders = Slider::paginate(25);

        return view('sliders.index', compact('sliders'));
    }

    /**
     * Show the form for creating a new slider.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        
        
        return view('sliders.create');
    }

    /**
     * Store a new slider in the storage.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        
        $data = $this->getData($request);
        
        Slider::create($data);

        return redirect()->route('sliders.slider.index')
            ->with('success_message', 'Slider was successfully added.');
    }

    /**
     * Display the specified slider.
     *
     * @param int $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $slider = Slider::findOrFail($id);

        return view('sliders.show', compact('slider'));
    }

    /**
     * Show the form for editing the specified slider.
     *
     * @param int $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $slider = Slider::findOrFail($id);
        

        return view('sliders.edit', compact('slider'));
    }

    /**
     * Update the specified slider in the storage.
     *
     * @param int $id
     * @param Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function update($id, Request $request)
    {
        
        $data = $this->getData($request);
        
        $slider = Slider::findOrFail($id);
        $slider->update($data);

        return redirect()->route('sliders.slider.index')
            ->with('success_message', 'Slider was successfully updated.');  
    }

    /**
     * Remove the specified slider from the storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        try {
            $slider = Slider::findOrFail($id);
            $slider->delete();

            return redirect()->route('sliders.slider.index')
                ->with('success_message', 'Slider was successfully deleted.');
        } catch (Exception $exception) {

            return back()->withInput()
                ->withErrors(['unexpected_error' => 'Unexpected error occurred while trying to process your request.']);
        }
    }

    
    /**
     * Get the request's data from the request.
     *
     * @param Illuminate\Http\Request\Request $request 
     * @return array
     */
    protected function getData(Request $request)
    {
        $rules = [
                'name' => 'string|min:1|max:255|nullable',
            'text' => 'string|min:1|nullable',
            'image' => ['image','mimes:jpeg,png,jpg,gif,svg','max:2048','nullable','file'],
            'link' => 'string|min:1|nullable',
            'status' => 'string|min:1|nullable', 
        ];

                if ($request->route()->getAction()['as'] == 'sliders.slider.store' || $request->has('custom_delete_image')) {
            array_push($rules['image'], 'required');
        }
        $data = $request->validate($rules);

        if ($request->has('custom_delete_image')) {
            $data['image'] = null;
        }
        if ($request->hasFile('image')) {
            $data['image'] = $this->moveFile($request->file('image'));
        }



        return $data;
    }
  
    /**
     * Moves the attached file to the server.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return string
     */
    protected function moveFile($file)
    {
        if (!$file->isValid()) {
            return '';
        }
        
        $path = config('laravel-code-generator.files_upload_path', 'uploads');
        $saved = $file->store('public/' . $path, config('filesystems.default'));

        return substr($saved, 7);
    }

}
