<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use Illuminate\Http\Request;

class ExpenseTypeController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage expense type')) {
            $types = ExpenseType::where('parent_id', '=', parentId())->orderBy('id', 'desc')->get();
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        return view('expense_type.index', compact('types'));
    }


    public function create()
    {
        return view('expense_type.create');
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create expense type')) {
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
            $ExpenseType = new ExpenseType();
            $ExpenseType->type = $request->type;
            $ExpenseType->parent_id = parentId();
            $ExpenseType->save();
            return redirect()->route('expense-type.index')->with('success', __('Expense type successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show(ExpenseType $ExpenseType)
    {
        //
    }


    public function edit(ExpenseType $ExpenseType)
    {
        return view('expense_type.edit', compact('ExpenseType'));
    }


    public function update(Request $request, ExpenseType $ExpenseType)
    {
        if (\Auth::user()->can('edit expense type')) {
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
            $ExpenseType->type = $request->type;
            $ExpenseType->save();
            return redirect()->route('expense-type.index')->with('success', __('Expense type successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function destroy(ExpenseType $ExpenseType)
    {
        if (\Auth::user()->can('delete expense type')) {
            $ExpenseType->delete();
            return redirect()->route('expense-type.index')->with('success', __('Expense type successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
