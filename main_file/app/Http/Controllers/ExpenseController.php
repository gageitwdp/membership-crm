<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Member;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage expense')) {
            $expenses = Expense::where('parent_id', parentId())->orderBy('id', 'desc')->get();
            return view('expense.index', compact('expenses'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create expense')) {
            $types = ExpenseType::where('parent_id', parentId())->pluck('type', 'id');
            $types->prepend('Select Expense', '');
            return view('expense.create', compact('types'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create expense')) {
            $velidetor = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'date' => 'required',
                    'type' => 'required',
                    'amount' => 'required',
                ]
            );
            if ($velidetor->fails()) {
                $messages = $velidetor->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            if (!empty($request->receipt)) {
                $receiptFilenameWithExt = $request->file('receipt')->getClientOriginalName();
                $receiptFilename = pathinfo($receiptFilenameWithExt, PATHINFO_FILENAME);
                $receiptExtension = $request->file('receipt')->getClientOriginalExtension();
                $receiptFileName = $receiptFilename . '_' . time() . '.' . $receiptExtension;
                $dir = storage_path('upload/receipt');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('receipt')->storeAs('upload/receipt/', $receiptFileName);
            }

            $expense = new Expense();
            $expense->expense_id = $this->expenseNumber();
            $expense->title = $request->title;
            $expense->date = $request->date;
            $expense->type = $request->type;
            $expense->amount = $request->amount;
            $expense->receipt = !empty($request->receipt) ? $receiptFileName : '';
            $expense->notes = !empty($request->notes) ? $request->notes : '';
            $expense->parent_id = parentId();
            $expense->save();
            if ($expense) {
                return redirect()->route('expense.index')->with('success', __('Expense successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Something went wrong.'));
            }
        }
    }

    public function show(Expense $expense)
    {
        if (\Auth::user()->can('show expense')) {
            return view('expense.show', compact('expense'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function edit(Expense $expense)
    {
        if (\Auth::user()->can('edit expense')) {
            $types = ExpenseType::where('parent_id', parentId())->pluck('type', 'id');
            $types->prepend('Select Expense', '');
            return view('expense.edit', compact('expense', 'types'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function update(Request $request, Expense $expense)
    {
        if (\Auth::user()->can('edit expense')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'date' => 'required',
                    'type' => 'required',
                    'amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            if (!empty($request->receipt)) {
                $receiptFilenameWithExt = $request->file('receipt')->getClientOriginalName();
                $receiptFilename = pathinfo($receiptFilenameWithExt, PATHINFO_FILENAME);
                $receiptExtension = $request->file('receipt')->getClientOriginalExtension();
                $receiptFileName = $receiptFilename . '_' . time() . '.' . $receiptExtension;
                $dir = storage_path('upload/receipt');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('receipt')->storeAs('upload/receipt/', $receiptFileName);
                $expense->receipt = !empty($request->receipt) ? $receiptFileName : '';
            }


            $expense->title = $request->title;
            $expense->date = $request->date;
            $expense->type = $request->type;
            $expense->amount = $request->amount;
            $expense->notes = !empty($request->notes) ? $request->notes : '';
            $expense->save();
            return redirect()->route('expense.index')->with('success', __('Expense successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Something went wrong.'));
        }
    }

    public function destroy(Expense $expense)
    {
        if (\Auth::user()->can('delete expense')) {
            $expense->delete();
            return redirect()->route('expense.index')->with('success', __('Expense successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function expenseNumber()
    {
        $latest = Expense::where('parent_id', parentId())->latest()->first();
        if ($latest == null) {
            return 1;
        } else {
            return $latest->expense_id + 1;
        }
    }
}
