@php
    $editing = isset($assessment);
    $assessmentType = old('assessment_type', $assessment->assessment_type ?? 'general');
    $initialQuestions = old('questions', $questionsData);
@endphp
<div class="mx-auto">
    <a href="{{ route('assessments.index') }}" class="text-xl font-bold text-indigo-600 hover:text-indigo-700">← Back to assessments</a>
    <div class="mt-5">
        <p class="mb-3 text-xl font-bold uppercase tracking-[0.16em] text-indigo-600">Assessment builder</p>
        <h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">{{ $editing ? 'Edit student assessment' : 'Create a student assessment' }}</h1>
        <p class="mt-3 text-slate-500">{{ $editing ? 'Update the details and question set for this student assessment.' : 'Build a general reflection or a balanced RIASEC interest reflection.' }}</p>
    </div>
    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-xl text-rose-800" role="alert">
            <p class="font-bold">Please review the form:</p>
            <ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <form method="POST" action="{{ $editing ? route('assessments.update', $assessment) : route('assessments.store') }}" class="mt-8 space-y-6" id="assessment-form">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif
        <section class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="font-display text-xl font-bold text-slate-900">Assessment details</h2>
                <p class="mt-1 text-xl text-slate-500">Choose the format students will complete.</p>
            </div>
            <div class="space-y-5">
                <div>
                    <label for="title" class="mb-2 block text-xl font-semibold text-slate-700">Assessment title <span class="text-rose-500">*</span></label>
                    <input id="title" name="title" maxlength="160" required value="{{ old('title', $assessment->title ?? '') }}" placeholder="e.g. Career interests check-in" class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="description" class="mb-2 block text-xl font-semibold text-slate-700">Introduction <span class="font-normal text-slate-400">(optional)</span></label>
                    <textarea id="description" name="description" rows="3" maxlength="2000" placeholder="Tell students what they will reflect on and how their answers will be used." class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">{{ old('description', $assessment->description ?? '') }}</textarea>
                </div>
                <div>
                    <label for="assessment_type" class="mb-2 block text-xl font-semibold text-slate-700">Assessment type</label>
                    <select id="assessment_type" name="assessment_type" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                        <option value="general" @selected($assessmentType === 'general')>General assessment</option>
                        <option value="riasec" @selected($assessmentType === 'riasec')>RIASEC interest reflection</option>
                    </select>
                    @if ($hasSubmissions)
                        <input type="hidden" name="assessment_type" value="{{ $assessment->assessment_type }}">
                    @endif
                    <p class="mt-2 text-sm text-slate-500">RIASEC items ask how much a student would enjoy an activity, not which of six choices they prefer. Results are exploratory and are not a validated career test or diagnosis.</p>
                </div>
                <div>
                    <label for="status" class="mb-2 block text-xl font-semibold text-slate-700">Save as</label>
                    <select id="status" name="status" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                        <option value="draft" @selected(old('status', $assessment->status ?? 'draft') === 'draft')>Draft — keep working on it</option>
                        <option value="published" @selected(old('status', $assessment->status ?? 'draft') === 'published')>Published — ready for student use</option>
                    </select>
                    <p class="mt-2 text-sm text-slate-500">Published assessments will appear in the student portal.</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="font-display text-xl font-bold text-slate-900">Questions</h2>
                    <p id="question-guidance" class="mt-1 text-xl text-slate-500"></p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" id="load-riasec-starter" class="hidden rounded-xl border border-indigo-200 px-4 py-2.5 text-xl font-bold text-indigo-700 transition hover:bg-indigo-50">Load 24 RIASEC starter activities</button>
                    <button type="button" id="add-question" class="rounded-xl border border-indigo-200 px-4 py-2.5 text-xl font-bold text-indigo-700 transition hover:bg-indigo-50">+ Add question</button>
                </div>
            </div>
            @if ($hasSubmissions)
                <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">This assessment has student responses. Its questions and assessment type cannot be changed, so existing answers stay connected to the questions students saw. Create a new assessment to use a different question set.</p>
            @endif
            <div id="questions" class="mt-6 space-y-5"></div>
            <p id="riasec-balance-note" class="mt-4 hidden text-sm leading-6 text-slate-500">For a balanced RIASEC profile, include at least three activities for each interest area. The starter set provides four original activities per area.</p>
        </section>
        <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">
            <a href="{{ route('assessments.index') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-center font-bold text-slate-600 hover:bg-slate-50">Cancel</a>
            <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">{{ $editing ? 'Save changes' : 'Save assessment' }}</button>
        </div>
    </form>
</div>
<script>
(() => {
    const container = document.getElementById('questions');
    const typeSelect = document.getElementById('assessment_type');
    const starterQuestions = {{ \Illuminate\Support\Js::from($starterQuestions) }};
    const domains = {{ \Illuminate\Support\Js::from(\App\Support\RiaSecScorer::DOMAIN_DETAILS) }};
    const initialQuestions = {{ \Illuminate\Support\Js::from($initialQuestions) }};
    const questionEditingLocked = {{ $hasSubmissions ? 'true' : 'false' }};
    const legacyRiaSecLocked = questionEditingLocked
        && typeSelect.value === 'riasec'
        && initialQuestions.some(question => !question.riasec_domain);
    let questionCount = 0;

    const renumber = () => {
        [...container.children].forEach((card, index) => {
            card.querySelector('[data-question-title]').textContent = `Question ${index + 1}`;
        });
    };

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
        input.className = 'min-w-0 flex-1 rounded-lg border border-slate-200 px-3 py-2.5 text-xl outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100';
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.textContent = 'Remove';
        remove.className = 'rounded-lg px-3 text-sm font-bold text-slate-400 hover:bg-rose-50 hover:text-rose-600';
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

    const addQuestion = ({prompt = '', choices = ['', ''], riasec_domain = ''} = {}) => {
        if (container.children.length >= 30) return;
        const index = questionCount++;
        const card = document.createElement('article');
        card.className = 'rounded-xl border border-slate-200 bg-slate-50/70 p-5';

        const heading = document.createElement('div');
        heading.className = 'mb-4 flex items-center justify-between';
        const title = document.createElement('h3');
        title.dataset.questionTitle = 'true';
        title.className = 'text-xl font-bold text-slate-800';
        const removeQuestion = document.createElement('button');
        removeQuestion.type = 'button';
        removeQuestion.textContent = 'Remove question';
        removeQuestion.className = 'text-sm font-bold text-slate-400 hover:text-rose-600';
        removeQuestion.addEventListener('click', () => {
            card.remove();
            renumber();
        });
        heading.append(title, removeQuestion);

        const promptLabel = document.createElement('label');
        promptLabel.className = 'mb-2 block text-sm font-bold uppercase tracking-wide text-slate-500';
        promptLabel.textContent = 'Activity prompt';
        const promptInput = document.createElement('textarea');
        promptInput.name = `questions[${index}][prompt]`;
        promptInput.value = prompt;
        promptInput.rows = 2;
        promptInput.maxLength = 1000;
        promptInput.required = true;
        promptInput.placeholder = 'Describe one specific activity a student can imagine doing.';
        promptInput.className = 'w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xl outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100';
        promptLabel.htmlFor = promptInput.id = `question-${index}`;

        const generalChoices = document.createElement('div');
        generalChoices.className = 'general-choices mt-4';
        const choicesLabel = document.createElement('p');
        choicesLabel.className = 'mb-2 text-sm font-bold uppercase tracking-wide text-slate-500';
        choicesLabel.textContent = 'Answer choices';
        const choicesList = document.createElement('div');
        choicesList.className = 'space-y-2';
        const addChoiceButton = document.createElement('button');
        addChoiceButton.type = 'button';
        addChoiceButton.textContent = '+ Add answer choice';
        addChoiceButton.className = 'mt-3 text-sm font-bold text-indigo-600 hover:text-indigo-700';
        addChoiceButton.addEventListener('click', () => addChoice(choicesList, index));
        generalChoices.append(choicesLabel, choicesList, addChoiceButton);

        const domainWrap = document.createElement('div');
        domainWrap.className = 'riasec-domain mt-4 hidden';
        const domainLabel = document.createElement('label');
        domainLabel.className = 'mb-2 block text-sm font-bold uppercase tracking-wide text-slate-500';
        domainLabel.textContent = 'RIASEC interest area';
        domainLabel.htmlFor = `question-domain-${index}`;
        const domainSelect = document.createElement('select');
        domainSelect.name = `questions[${index}][riasec_domain]`;
        domainSelect.id = `question-domain-${index}`;
        domainSelect.className = 'w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xl outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100';
        domainSelect.required = true;
        const blankOption = document.createElement('option');
        blankOption.value = '';
        blankOption.textContent = 'Choose an interest area';
        domainSelect.append(blankOption);
        Object.entries(domains).forEach(([code, details]) => {
            const option = document.createElement('option');
            option.value = code;
            option.textContent = `${details.label} (${code})`;
            domainSelect.append(option);
        });
        domainSelect.value = riasec_domain;
        const scaleHint = document.createElement('p');
        scaleHint.className = 'mt-2 text-sm text-slate-500';
        scaleHint.textContent = 'Students rate how much they would enjoy this activity on the same five-point scale.';
        domainWrap.append(domainLabel, domainSelect, scaleHint);

        card.append(heading, promptLabel, promptInput, generalChoices, domainWrap);
        container.append(card);
        choices.forEach(choice => addChoice(choicesList, index, choice));
        renumber();
    };

    const setType = () => {
        const legacyRiaSec = legacyRiaSecLocked;
        const riasec = typeSelect.value === 'riasec' && !legacyRiaSec;
        document.getElementById('question-guidance').textContent = questionEditingLocked
            ? 'These questions are read-only because students have already responded.'
            : riasec
                ? 'Use one activity per prompt. Students rate each activity separately from not interested to extremely interested.'
                : 'Add multiple-choice prompts with 2–8 answer choices.';
        document.getElementById('load-riasec-starter').classList.toggle('hidden', !riasec);
        document.getElementById('riasec-balance-note').classList.toggle('hidden', !riasec);

        [...container.children].forEach(card => {
            const choices = card.querySelector('.general-choices');
            const domain = card.querySelector('.riasec-domain');
            choices.classList.toggle('hidden', riasec);
            domain.classList.toggle('hidden', !riasec);
            choices.querySelectorAll('input').forEach(input => {
                input.disabled = riasec;
                input.required = !riasec;
            });
            domain.querySelector('select').disabled = !riasec;
            domain.querySelector('select').required = riasec;
        });

        if (questionEditingLocked) {
            typeSelect.disabled = true;
            document.getElementById('load-riasec-starter').disabled = true;
            document.getElementById('add-question').disabled = true;
            container.querySelectorAll('input, textarea, select, button').forEach(control => {
                control.disabled = true;
            });
        }
    };

    document.getElementById('add-question').addEventListener('click', () => addQuestion());
    document.getElementById('load-riasec-starter').addEventListener('click', () => {
        if (container.children.length && !window.confirm('Replace the questions currently in this form with the 24 RIASEC starter activities?')) return;
        container.replaceChildren();
        questionCount = 0;
        starterQuestions.forEach(question => addQuestion(question));
        setType();
    });
    typeSelect.addEventListener('change', setType);

    if (Array.isArray(initialQuestions) && initialQuestions.length) {
        initialQuestions.forEach(question => addQuestion({
            prompt: question.prompt ?? '',
            choices: typeSelect.value === 'riasec' && !legacyRiaSecLocked
                ? ['', '']
                : Object.values(question.choices ?? ['', '']),
            riasec_domain: question.riasec_domain ?? '',
        }));
    } else {
        addQuestion();
    }
    setType();
})();
</script>
