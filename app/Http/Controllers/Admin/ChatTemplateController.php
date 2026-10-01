<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role !== 'admin') {
                abort(403, 'Unauthorized action. Template management is restricted to administrators.');
            }
            return $next($request);
        });
    }

    /**
     * Display listing of chat templates with category filtering
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'all');

        $query = ChatTemplate::with('creator')->orderBy('order_index', 'asc')->orderBy('id', 'asc');

        if (in_array($activeTab, ['on_progress', 'pending', 'closed', 'general'])) {
            $query->where('category', $activeTab);
        }

        $templates = $query->get();

        $counts = [
            'all'         => ChatTemplate::count(),
            'on_progress' => ChatTemplate::where('category', 'on_progress')->count(),
            'pending'     => ChatTemplate::where('category', 'pending')->count(),
            'closed'      => ChatTemplate::where('category', 'closed')->count(),
            'general'     => ChatTemplate::where('category', 'general')->count(),
        ];

        return view('admin.chat_templates.index', compact('templates', 'counts', 'activeTab'));
    }

    /**
     * Store new chat template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:100',
            'category'    => 'required|string|in:general,on_progress,pending,closed',
            'message'     => 'required|string|min:3',
            'order_index' => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ], [
            'title.required'    => 'Judul template wajib diisi.',
            'category.required' => 'Kategori status wajib dipilih.',
            'message.required'  => 'Isi pesan template wajib diisi.',
            'message.min'       => 'Isi pesan minimal 3 karakter.',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : true;
        $validated['order_index'] = $request->filled('order_index') ? (int)$request->input('order_index') : 0;

        ChatTemplate::create($validated);

        return redirect()->route('admin.chat-templates.index', ['tab' => $validated['category']])
            ->with('success', 'Template chat "' . $validated['title'] . '" berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified chat template
     */
    public function edit($id)
    {
        $template = ChatTemplate::findOrFail($id);
        return view('admin.chat_templates.edit', compact('template'));
    }

    /**
     * Update existing chat template
     */
    public function update(Request $request, $id)
    {
        $template = ChatTemplate::findOrFail($id);

        $validated = $request->validate([
            'title'       => 'required|string|max:100',
            'category'    => 'required|string|in:general,on_progress,pending,closed',
            'message'     => 'required|string|min:3',
            'order_index' => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ], [
            'title.required'    => 'Judul template wajib diisi.',
            'category.required' => 'Kategori status wajib dipilih.',
            'message.required'  => 'Isi pesan template wajib diisi.',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : false;
        $validated['order_index'] = $request->filled('order_index') ? (int)$request->input('order_index') : 0;

        $template->update($validated);

        return redirect()->route('admin.chat-templates.index', ['tab' => $validated['category']])
            ->with('success', 'Template chat "' . $template->title . '" berhasil diperbarui.');
    }

    /**
     * Delete chat template
     */
    public function destroy($id)
    {
        $template = ChatTemplate::findOrFail($id);
        $title = $template->title;
        $cat = $template->category;

        $template->delete();

        return redirect()->route('admin.chat-templates.index', ['tab' => $cat])
            ->with('success', 'Template chat "' . $title . '" berhasil dihapus.');
    }

    /**
     * Quick toggle active / inactive status
     */
    public function toggleStatus($id)
    {
        $template = ChatTemplate::findOrFail($id);
        $template->is_active = !$template->is_active;
        $template->save();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success'   => true,
                'is_active' => $template->is_active,
                'message'   => 'Status template diperbarui.',
            ]);
        }

        return redirect()->back()->with('success', 'Status template "' . $template->title . '" berhasil diubah.');
    }
}

