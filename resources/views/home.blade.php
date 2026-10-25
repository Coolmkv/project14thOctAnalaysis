@extends('layouts.app')

@section('title', 'Career exploration for students')

@section('content')
<div class="space-y-20 pb-8 sm:space-y-24">
    <section class="relative isolate overflow-hidden rounded-[2rem] bg-[#171943] text-white shadow-xl shadow-indigo-950/10">
        <div class="absolute -right-24 -top-32 -z-10 h-96 w-96 rounded-full bg-indigo-500/30 blur-3xl" aria-hidden="true"></div>
        <div class="absolute -bottom-48 left-1/3 -z-10 h-96 w-96 rounded-full bg-teal-400/10 blur-3xl" aria-hidden="true"></div>
        <div class="grid items-center gap-10 px-6 py-10 sm:px-10 sm:py-14 lg:grid-cols-[1fr_0.9fr] lg:gap-8 lg:px-16 lg:py-16">
            <div class="max-w-xl">
                <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3.5 py-2 text-xs font-bold uppercase tracking-[0.16em] text-indigo-100">
                    <span class="h-2 w-2 rounded-full bg-teal-300" aria-hidden="true"></span>
                    A space to explore what’s next
                </p>
                <h1 class="mt-6 font-display text-4xl font-extrabold leading-[1.1] tracking-tight sm:text-5xl lg:text-[3.5rem]">Your future is yours to explore.</h1>
                <p class="mt-5 max-w-lg text-base leading-7 text-indigo-100 sm:text-lg sm:leading-8">Get curious about what you enjoy, see patterns in your interests, and choose a next step that feels right for you.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="rounded-xl bg-white px-6 py-3.5 text-center font-bold text-indigo-800 shadow-lg shadow-black/10 transition hover:bg-indigo-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Start exploring</a>
                    <a href="{{ route('login') }}" class="rounded-xl border border-white/25 bg-white/5 px-6 py-3.5 text-center font-bold text-white transition hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Student sign in</a>
                </div>
                <p class="mt-6 text-sm text-indigo-200">A counselor? <a href="{{ route('counselor.login') }}" class="font-bold text-white underline decoration-indigo-300 underline-offset-4 hover:decoration-white">Sign in to your workspace</a></p>
                <p class="mt-2 text-sm text-indigo-200">Administrator? <a href="{{ route('admin.login') }}" class="font-bold text-white underline decoration-indigo-300 underline-offset-4 hover:decoration-white">Admin sign in</a></p>
                <p class="mt-2 text-sm text-indigo-200">Managing a school or institute? <a href="{{ route('group-admin.login') }}" class="font-bold text-white underline decoration-indigo-300 underline-offset-4 hover:decoration-white">Group admin sign in</a></p>
            </div>

            <div class="mx-auto w-full max-w-lg" role="img" aria-label="Illustration showing six RIASEC interest themes connecting to a student’s next step">
                <svg viewBox="0 0 520 420" class="h-auto w-full" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <defs>
                        <linearGradient id="path-glow" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#A5B4FC"/>
                            <stop offset="1" stop-color="#5EEAD4"/>
                        </linearGradient>
                        <filter id="card-shadow" x="-30%" y="-30%" width="160%" height="170%">
                            <feDropShadow dx="0" dy="10" stdDeviation="12" flood-color="#080A2E" flood-opacity=".22"/>
                        </filter>
                    </defs>
                    <circle cx="263" cy="204" r="166" fill="#fff" fill-opacity=".035"/>
                    <circle cx="263" cy="204" r="126" fill="none" stroke="#fff" stroke-opacity=".1" stroke-dasharray="3 9"/>
                    <path d="M111 203c45-70 82 76 126 8 36-56 65 63 111 1 27-37 48-36 72-14" fill="none" stroke="url(#path-glow)" stroke-width="4" stroke-linecap="round" stroke-dasharray="1 0"/>
                    <circle cx="111" cy="203" r="7" fill="#A5B4FC"/>
                    <circle cx="237" cy="211" r="7" fill="#C4B5FD"/>
                    <circle cx="348" cy="212" r="7" fill="#5EEAD4"/>
                    <circle cx="420" cy="198" r="7" fill="#5EEAD4"/>

                    <g filter="url(#card-shadow)">
                        <rect x="183" y="136" width="160" height="132" rx="24" fill="#fff"/>
                        <circle cx="263" cy="177" r="22" fill="#EEF2FF"/>
                        <path d="M254 179l7 7 13-17" fill="none" stroke="#4F46E5" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <text x="263" y="222" text-anchor="middle" fill="#171943" font-family="sans-serif" font-size="15" font-weight="700">Your interests</text>
                        <text x="263" y="244" text-anchor="middle" fill="#64748B" font-family="sans-serif" font-size="12">A starting point, not a label</text>
                    </g>

                    <g filter="url(#card-shadow)">
                        <rect x="23" y="161" width="117" height="82" rx="18" fill="#fff"/>
                        <circle cx="49" cy="190" r="14" fill="#E0E7FF"/>
                        <text x="49" y="195" text-anchor="middle" fill="#4338CA" font-family="sans-serif" font-size="13" font-weight="800">R</text>
                        <text x="70" y="194" fill="#1E293B" font-family="sans-serif" font-size="12" font-weight="700">Explore</text>
                        <text x="42" y="222" fill="#64748B" font-family="sans-serif" font-size="11">What you enjoy</text>
                    </g>
                    <g filter="url(#card-shadow)">
                        <rect x="358" y="52" width="139" height="82" rx="18" fill="#fff"/>
                        <circle cx="385" cy="81" r="14" fill="#CCFBF1"/>
                        <text x="385" y="86" text-anchor="middle" fill="#0F766E" font-family="sans-serif" font-size="13" font-weight="800">6</text>
                        <text x="377" y="112" fill="#64748B" font-family="sans-serif" font-size="11">RIASEC interest themes</text>
                    </g>
                    <g filter="url(#card-shadow)">
                        <rect x="351" y="282" width="146" height="82" rx="18" fill="#fff"/>
                        <circle cx="379" cy="311" r="14" fill="#FEF3C7"/>
                        <path d="M379 302v10l7 4" fill="none" stroke="#B45309" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <text x="403" y="315" fill="#1E293B" font-family="sans-serif" font-size="12" font-weight="700">Take a next step</text>
                        <text x="370" y="343" fill="#64748B" font-family="sans-serif" font-size="11">Reflect or talk it through</text>
                    </g>
                    <circle cx="127" cy="89" r="4" fill="#C4B5FD" fill-opacity=".8"/>
                    <circle cx="445" cy="237" r="5" fill="#5EEAD4" fill-opacity=".8"/>
                    <circle cx="163" cy="324" r="4" fill="#fff" fill-opacity=".55"/>
                    <path d="M113 290l4 8 8 4-8 4-4 8-4-8-8-4 8-4 4-8z" fill="#FCD34D" fill-opacity=".9"/>
                    <path d="M284 63l3 6 6 3-6 3-3 6-3-6-6-3 6-3 3-6z" fill="#C4B5FD" fill-opacity=".8"/>
                </svg>
            </div>
        </div>
    </section>

    <section aria-labelledby="how-it-works">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-600">A simple place to begin</p>
            <h2 id="how-it-works" class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">How career exploration works</h2>
            <p class="mt-4 leading-7 text-slate-600">No need to have it all figured out. Use each step to learn more about yourself and your options.</p>
        </div>
        <div class="mt-10 grid gap-4 md:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 font-display text-lg font-extrabold text-indigo-700">01</span>
                <h3 class="mt-5 font-display text-lg font-bold text-slate-950">Notice what interests you</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">Answer reflection questions about activities, topics, and ways of working that appeal to you.</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 font-display text-lg font-extrabold text-teal-700">02</span>
                <h3 class="mt-5 font-display text-lg font-bold text-slate-950">Look for patterns</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">Use RIASEC themes to organize your interests and discover directions worth exploring.</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 font-display text-lg font-extrabold text-amber-700">03</span>
                <h3 class="mt-5 font-display text-lg font-bold text-slate-950">Choose a next step</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">Learn more, try a small activity, or bring your reflections to a counselor.</p>
            </article>
        </div>
    </section>

    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm" aria-labelledby="riasec-heading">
        <div class="grid gap-8 p-6 sm:p-10 lg:grid-cols-[0.78fr_1.22fr] lg:gap-12 lg:p-12">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-600">A framework for reflection</p>
                <h2 id="riasec-heading" class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-950">Meet the six RIASEC themes</h2>
                <p class="mt-4 leading-7 text-slate-600">RIASEC groups career interests into six broad themes. You may relate to several of them, and your interests can change as you learn and grow.</p>
                <a href="{{ route('login') }}" class="mt-6 inline-flex items-center gap-2 font-bold text-indigo-700 hover:text-indigo-900">
                    Explore your interests <span aria-hidden="true">→</span>
                </a>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <article class="rounded-xl bg-indigo-50/80 p-4">
                    <div class="flex items-center gap-2"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-sm font-extrabold text-indigo-700">R</span><h3 class="font-bold text-slate-900">Realistic</h3></div>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Hands-on activities and practical problem-solving.</p>
                </article>
                <article class="rounded-xl bg-sky-50/80 p-4">
                    <div class="flex items-center gap-2"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-100 text-sm font-extrabold text-sky-700">I</span><h3 class="font-bold text-slate-900">Investigative</h3></div>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Asking questions, researching, and analyzing ideas.</p>
                </article>
                <article class="rounded-xl bg-fuchsia-50/80 p-4">
                    <div class="flex items-center gap-2"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-fuchsia-100 text-sm font-extrabold text-fuchsia-700">A</span><h3 class="font-bold text-slate-900">Artistic</h3></div>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Creating, designing, and expressing original ideas.</p>
                </article>
                <article class="rounded-xl bg-emerald-50/80 p-4">
                    <div class="flex items-center gap-2"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-sm font-extrabold text-emerald-700">S</span><h3 class="font-bold text-slate-900">Social</h3></div>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Helping, teaching, and connecting with people.</p>
                </article>
                <article class="rounded-xl bg-amber-50/80 p-4">
                    <div class="flex items-center gap-2"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-sm font-extrabold text-amber-700">E</span><h3 class="font-bold text-slate-900">Enterprising</h3></div>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Taking initiative, leading, and moving ideas forward.</p>
                </article>
                <article class="rounded-xl bg-slate-100/80 p-4">
                    <div class="flex items-center gap-2"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-200 text-sm font-extrabold text-slate-700">C</span><h3 class="font-bold text-slate-900">Conventional</h3></div>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Organizing information and working with clear systems.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="rounded-[2rem] bg-indigo-50 px-6 py-9 text-center sm:px-10 sm:py-12" aria-labelledby="start-heading">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Your next step can be small</p>
        <h2 id="start-heading" class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-950">Start with what you’re curious about.</h2>
        <p class="mx-auto mt-4 max-w-2xl leading-7 text-slate-600">Explore on your own, or use your reflections to start a conversation with a counselor. There’s no single right path.</p>
        <a href="{{ route('register') }}" class="mt-7 inline-flex rounded-xl bg-indigo-600 px-6 py-3.5 font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700">Create a student account</a>
        <p class="mt-5 text-xs leading-5 text-slate-500">RIASEC is a reflection framework—not a diagnosis, a definitive fit, or a prediction of career outcomes.</p>
    </section>
</div>
@endsection
