@extends('layouts.app')

@section('content')
    @forelse ($sections as $section)
        <x-dynamic-component
            :component="'landing.'.$section->type->value"
            :content="$section->content ?? []"
        />
    @empty
        <section class="w-full bg-white-brand py-32 text-center text-black-brand" data-nav-theme="light">
            <div class="mx-auto max-w-layout px-5">
                <h1 class="text-3xl font-thin uppercase tracking-[0.12em]">{{ $page->title }}</h1>
                <p class="mt-4 text-sm text-black-brand/55">Секції ще не додано. Налаштуйте їх в адмінці.</p>
            </div>
        </section>
    @endforelse

    @if ($page->show_footer)
        <x-blocks.footer :catalogs="$catalogs" :content="$footerContent" />
    @endif
@endsection
