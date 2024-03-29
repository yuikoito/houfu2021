<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function store(Request $request)
    {
        $user = User::where('unique_id', $request->input('user'))->firstOrFail();
        $question = new Question();
        $question->user_id = Auth::id();
        $question->received_user_id = $user->id;
        $question->body = $request->input('body');
        $question->save();

        session()->flash('success', '抱負を投稿しました。');
        return redirect("users/{$user->unique_id}");
    }

    public function ogp($id)
    {
        $question = Question::findOrFail($id);
        $image = $question->generateOgp();
        return response($image, 200)
            ->header('Content-Type', 'image/png');
    }

    public function show($id)
    {
        $question = Question::findOrFail($id);
        return view('question.show', compact('question'));
    }
}
