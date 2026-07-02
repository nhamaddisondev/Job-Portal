<?php
require 'config/config.php';
require 'includes/header.php';
?>

<main class="bg-slate-50">
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-screen-xl px-4 py-14">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">About Online Job Portal</p>
            <h1 class="mt-3 max-w-3xl text-4xl font-bold text-slate-950 md:text-5xl">A practical place for local hiring</h1>
            <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600">
                Online Job Portal helps job seekers discover approved openings and gives employers a focused place to publish roles, review applicants, and manage hiring activity.
            </p>
        </div>
    </section>

    <section class="mx-auto grid max-w-screen-xl gap-6 px-4 py-12 md:grid-cols-3">
        <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-950">For job seekers</h2>
            <p class="mt-3 leading-7 text-slate-600">Search current openings, save jobs, apply with your profile, and keep track of application progress.</p>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-950">For employers</h2>
            <p class="mt-3 leading-7 text-slate-600">Post roles, manage listings, review applicants, and connect with candidates from one dashboard.</p>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-950">For admins</h2>
            <p class="mt-3 leading-7 text-slate-600">Review pending jobs, organize categories and regions, and keep the portal clean and trustworthy.</p>
        </article>
    </section>
</main>

<?php require 'includes/footer.php'; ?>
