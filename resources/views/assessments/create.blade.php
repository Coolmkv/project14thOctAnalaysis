@extends('layouts.app')

@section('title', 'Create assessment')

@section('content')
<div class="mx-auto max-w-4xl">
    <a href="{{ route('assessments.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">← Back to assessments</a>
    <div class="mt-5"><p class="mb-3 text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Assessment builder</p><h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Create a student assessment</h1><p class="mt-3 text-slate-500">Start with a clear purpose, then add questions that invite honest reflection.</p></div>
    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert"><p class="font-bold">Please review the form:</p><ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form method="POST" action="{{ route('assessments.store') }}" class="mt-8 space-y-6" id="assessment-form">
        @csrf
        <section class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
            <div class="mb-6"><h2 class="font-display text-xl font-bold text-slate-900">Assessment details</h2><p class="mt-1 text-sm text-slate-500">Give students context for why they're answering these questions.</p></div>
            <div class="space-y-5">
                <div><label for="title" class="mb-2 block text-sm font-semibold text-slate-700">Assessment title <span class="text-rose-500">*</span></label><input id="title" name="title" maxlength="160" required value="{{ old('title') }}" placeholder="e.g. Career interests check-in" class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"></div>
                <div><label for="description" class="mb-2 block text-sm font-semibold text-slate-700">Introduction <span class="font-normal text-slate-400">(optional)</span></label><textarea id="description" name="description" rows="3" maxlength="2000" placeholder="Tell students what they'll reflect on and how their answers will be used." class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">{{ old('description') }}</textarea></div>
                <div><label for="status" class="mb-2 block text-sm font-semibold text-slate-700">Save as</label><select id="status" name="status" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"><option value="draft" @selected(old('status', 'draft') === 'draft')>Draft — keep working on it</option><option value="published" @selected(old('status') === 'published')>Published — ready for student use</option></select><p class="mt-2 text-xs text-slate-500">Published assessments are saved as ready, but student delivery is not part of this builder yet.</p></div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><h2 class="font-display text-xl font-bold text-slate-900">Questions</h2><p class="mt-1 text-sm text-slate-500">Add multiple-choice prompts with 2–8 answer choices.</p></div><button type="button" id="add-question" class="rounded-xl border border-indigo-200 px-4 py-2.5 text-sm font-bold text-indigo-700 transition hover:bg-indigo-50">+ Add question</button></div>
            <div id="questions" class="mt-6 space-y-5"></div>
            <p class="mt-4 text-xs leading-5 text-slate-400">Keep questions welcoming and non-judgmental. This tool does not score answers or diagnose student aptitude.</p>
        </section>
        <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row"><a href="{{ route('assessments.index') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-center font-bold text-slate-600 hover:bg-slate-50">Cancel</a><button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">Save assessment</button></div>
    </form>
</div>
<script>
(() => {
    const container = document.getElementById('questions');
    let questionCount = 0;

    const addChoice = (list, questionIndex, value = '') => {
        if (list.children.length >= 8) return;
        const choiceIndex = list.children.length;
        const row = document.createElement('div');
        row.className = 'flex gap-2';
        const input = document.createElement('input');
        input.name = `questions[${questionIndex}][choices][${choiceIndex}]`;
        input.value = value;
        input.maxLength = 160;
        input.required = true;
        input.placeholder = `Answer choice ${choiceIndex + 1}`;
        input.className = 'min-w-0 flex-1 rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100';
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.textContent = 'Remove';
        remove.className = 'rounded-lg px-3 text-xs font-bold text-slate-400 hover:bg-rose-50 hover:text-rose-600';
        remove.addEventListener('click', () => {
            if (list.children.length <= 2) return;
            row.remove();
            [...list.children].forEach((choice, index) => {
                const field = choice.querySelector('input');
                field.name = `questions[${questionIndex}][choices][${index}]`;
                field.placeholder = `Answer choice ${index + 1}`;
            });
        });
        row.append(input, remove);
        list.append(row);
    };

    const addQuestion = (prompt = '', choices = ['', '']) => {
        if (container.children.length >= 30) return;
        const index = questionCount++;
        const card = document.createElement('article');
        card.className = 'rounded-xl border border-slate-200 bg-slate-50/70 p-5';
        const heading = document.createElement('div');
        heading.className = 'mb-4 flex items-center justify-between';
        const title = document.createElement('h3');
        title.className = 'text-sm font-bold text-slate-800';
        title.textContent = `Question ${container.children.length + 1}`;
        const removeQuestion = document.createElement('button');
        removeQuestion.type = 'button';
        removeQuestion.textContent = 'Remove question';
        removeQuestion.className = 'text-xs font-bold text-slate-400 hover:text-rose-600';
        removeQuestion.addEventListener('click', () => {
            card.remove();
            [...container.children].forEach((item, position) => item.querySelector('h3').textContent = `Question ${position + 1}`);
        });
        heading.append(title, removeQuestion);
        const label = document.createElement('label');
        label.className = 'mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500';
        label.textContent = 'Question prompt';
        const promptInput = document.createElement('textarea');
        promptInput.name = `questions[${index}][prompt]`;
        promptInput.value = prompt;
        promptInput.rows = 2;
        promptInput.maxLength = 1000;
        promptInput.required = true;
        promptInput.placeholder = 'What kinds of activities make you feel curious or energized?';
        promptInput.className = 'w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100';
        const choiceHeading = document.createElement('p');
        choiceHeading.className = 'mb-2 mt-4 text-xs font-bold uppercase tracking-wide text-slate-500';
        choiceHeading.textContent = 'Answer choices';
        const list = document.createElement('div');
        list.className = 'space-y-2';
        const add = document.createElement('button');
        add.type = 'button';
        add.textContent = '+ Add answer choice';
        add.className = 'mt-3 text-xs font-bold text-indigo-600 hover:text-indigo-700';
        add.addEventListener('click', () => addChoice(list, index));
        label.htmlFor = promptInput.id = `question-${index}`;
        card.append(heading, label, promptInput, choiceHeading, list, add);
        container.append(card);
        choices.forEach(choice => addChoice(list, index, choice));
    };

    document.getElementById('add-question').addEventListener('click', () => addQuestion());
    const oldQuestions = @json(old('questions', []));
    if (Object.keys(oldQuestions).length) {
        Object.values(oldQuestions).forEach(question => addQuestion(question.prompt ?? '', Object.values(question.choices ?? [])));
    } else {
        addQuestion();
    }
})();
</script>
@endsection