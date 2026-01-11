<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage document type')) {
            $types = DocumentType::where('parent_id', '=', parentId())->orderBy('id', 'desc')->get();
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        return view('document_type.index', compact('types'));
    }


    public function create()
    {
        return view('document_type.create');
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create document type')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'type' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $DocumentType = new DocumentType();
            $DocumentType->type = $request->type;
            $DocumentType->parent_id = parentId();
            $DocumentType->save();
            return redirect()->route('document-type.index')->with('success', __('Document type successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show(DocumentType $DocumentType)
    {
        //
    }


    public function edit(DocumentType $DocumentType)
    {
        return view('document_type.edit', compact('DocumentType'));
    }


    public function update(Request $request, DocumentType $DocumentType)
    {
        if (\Auth::user()->can('edit document type')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'type' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $DocumentType->type = $request->type;
            $DocumentType->save();
            return redirect()->route('document-type.index')->with('success', __('Document type successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function destroy(DocumentType $DocumentType)
    {
        if (\Auth::user()->can('delete document type')) {
            $DocumentType->delete();
            return redirect()->route('document-type.index')->with('success', __('Document type successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
