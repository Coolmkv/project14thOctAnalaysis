@if (session('temporary_password'))
    <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-950" role="status">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold">Temporary student password — shown once</h2>
                <p class="mt-1 text-sm">Share securely with this batch. Students must change it at first sign-in.</p>
            </div>
            <code class="inline-block rounded-lg border border-amber-200 bg-white px-4 py-2 font-mono text-lg font-bold">{{ session('temporary_password') }}</code>
        </div>
    </section>
@endif
