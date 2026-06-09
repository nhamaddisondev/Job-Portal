<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Document</title>
</head>
<body>
    <main class="bg-gray-50">
    <section class="mx-auto w-full max-w-screen-xl px-4 py-12 md:py-16">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
            <div>
                <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Contact Us</p>
                <h1 class="mt-3 text-4xl font-bold tracking-normal text-gray-900 md:text-5xl">Get in touch</h1>
                <p class="mt-4 max-w-xl text-lg leading-8 text-gray-600">
                    Have a question about jobs, companies, applications, or your account? Send us a message and our team will help you find the right next step.
                </p>

                <div class="mt-8 space-y-5">
                    <div class="rounded-md border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-gray-900">Email</p>
                        <a href="mailto:support@onlinejobportal.com" class="mt-1 inline-block text-sky-600 hover:text-sky-700">
                            support@onlinejobportal.com
                        </a>
                    </div>

                    <div class="rounded-md border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-gray-900">Phone</p>
                        <a href="tel:+11234567890" class="mt-1 inline-block text-sky-600 hover:text-sky-700">
                            +1 (123) 456-7890
                        </a>
                    </div>

                    <div class="rounded-md border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-gray-900">Office</p>
                        <p class="mt-1 text-gray-600">
                            123 Career Street, New York, NY 10001
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm md:p-8">
                <form action="" method="post" class="space-y-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full name</label>
                            <input type="text" id="name" name="name" required class="mt-2 block w-full rounded-md border border-gray-300 px-4 py-3 text-gray-900 shadow-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                            <input type="email" id="email" name="email" required class="mt-2 block w-full rounded-md border border-gray-300 px-4 py-3 text-gray-900 shadow-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500">
                        </div>
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                        <input type="text" id="subject" name="subject" required class="mt-2 block w-full rounded-md border border-gray-300 px-4 py-3 text-gray-900 shadow-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500">
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea id="message" name="message" rows="6" required class="mt-2 block w-full resize-y rounded-md border border-gray-300 px-4 py-3 text-gray-900 shadow-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500"></textarea>
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 md:w-auto">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </section>
</main>

</body>
</html>